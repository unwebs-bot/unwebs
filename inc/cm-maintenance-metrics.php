<?php
/**
 * CM Maintenance Metrics — 유지보수 지표 Single Source of Truth
 *
 * 배너 / KPI 카드 / 월별 차트 / (선택) 티커가 모두 이 함수에서 파생.
 * 시드 통일로 페이지 재로딩 시 값 일관성 보장.
 *
 * 시드 정책:
 *   - 주차 시드: 12개월 차트, 진행 중 ticket, 비율 → 한 주 동안 동일
 *   - 일 시드: 오늘 처리완료 → 매일 변동 (자연스러움)
 *   - 이번달 부분치: 일자 비례 계산 (시드 + 진행률)
 */

if (!defined('ABSPATH')) exit;

/**
 * 모든 유지보수 지표를 한 번에 반환 (정적 캐시)
 *
 * @return array{
 *   subscriptions:int,
 *   in_progress_revisions:int,
 *   monthly:array<int,array{month:int,year:int,label:string,count:int}>,
 *   this_month_completed:int,
 *   last_30d_completed:int,
 *   today_completed:int,
 *   one_day_rate:float,
 *   free_rate:float
 * }
 */
function uw_maintenance_metrics()
{
    static $cache = null;
    if ($cache !== null) return $cache;

    $salt      = function_exists('wp_salt') ? wp_salt('auth') : (defined('AUTH_KEY') ? AUTH_KEY : uniqid('', true));
    $week_seed = crc32('maintenance-w-' . date('oW') . $salt);
    $day_seed  = crc32('maintenance-d-' . date('Y-m-d') . $salt);

    // ---- 1) 6개월 처리완료 (주차 시드) — 무상/유상 분리 ----
    mt_srand($week_seed);

    $current_month = (int) date('n');
    $current_year  = (int) date('Y');
    $monthly       = array();

    for ($i = 5; $i >= 0; $i--) {
        $m = $current_month - $i;
        $y = $current_year;
        if ($m <= 0) { $m += 12; $y -= 1; }

        $total      = mt_rand(180, 720);                  // 편차 강조 (180~720건)
        $free_ratio = mt_rand(80, 92) / 100;              // 80~92% 무상
        $free       = (int) round($total * $free_ratio);
        $paid       = $total - $free;

        $monthly[] = array(
            'month' => $m,
            'year'  => $y,
            'label' => sprintf('%04d-%02d', $y, $m),
            'count' => $total,
            'free'  => $free,
            'paid'  => $paid,
        );
    }

    // ---- 2) 진행 중 수정 ticket (배너용) — KPI 이번달 처리완료 대비 80~120% ----
    // ※ 실제 산출은 this_month_completed 계산 후 (단계 5 끝)에서 수행, 임시값
    $in_progress_revisions = 0;

    // ---- 3) 정기 유지보수 구독 고객사 수 ----
    $subscriptions = 68; // 고정 — 실제 운영 시점에 옵션화 가능

    // ---- 4) 비율 (모집단: 최근 30일) ----
    $one_day_rate = round(70 + mt_rand(0, 50) / 10, 1); // 70.0 ~ 75.0
    $free_rate    = round(80 + mt_rand(0, 40) / 10, 1); // 80.0 ~ 84.0

    mt_srand();

    // ---- 5) 이번달 부분치 = 지난 5개월 평균 × (오늘 일자 / 이번달 총일수) ----
    $today_day            = (int) date('j');
    $days_in_month        = (int) date('t');
    $past_5_avg           = (int) round(array_sum(array_column(array_slice($monthly, 0, 5), 'count')) / 5);
    $progress_ratio       = $today_day / $days_in_month;
    $this_month_completed = (int) round($past_5_avg * $progress_ratio);

    // 이번달 무상/유상 비율 (지난달 비율 차용 → 자연스러움)
    $tm_free_ratio        = $monthly[4]['count'] > 0 ? $monthly[4]['free'] / $monthly[4]['count'] : 0.86;
    $this_month_free      = (int) round($this_month_completed * $tm_free_ratio);
    $this_month_paid      = $this_month_completed - $this_month_free;

    // 차트 마지막 막대를 이번달 진행률 값으로 덮어쓰기 (KPI와 100% 일치)
    $monthly[5]['count'] = $this_month_completed;
    $monthly[5]['free']  = $this_month_free;
    $monthly[5]['paid']  = $this_month_paid;

    // 배너 "수정진행" = KPI 이번달 처리완료 (사용자 요청 — 정확히 동일 수치)
    $in_progress_revisions = $this_month_completed;

    // ---- 6) 롤링 최근 30일 = 이번달 + 지난달의 (30 - 오늘일자)일치 ----
    $remaining_days     = max(0, 30 - $today_day);
    $last_month_partial = (int) round($monthly[4]['count'] * ($remaining_days / $days_in_month));
    $last_30d_completed = $this_month_completed + $last_month_partial;

    // ---- 8) 차트 Y축 max를 데이터에 맞춰 동적 계산 (5등분, 100단위 올림) ----
    $max_data  = max(array_column($monthly, 'count'));
    $step      = (int) (ceil(($max_data / 5) / 100) * 100);
    if ($step < 100) $step = 100;
    $chart_max = $step * 5;

    // ---- 7) 오늘 처리완료 (일 시드) ----
    mt_srand($day_seed);
    $today_completed = mt_rand(1, 5);
    mt_srand();

    $cache = array(
        'subscriptions'         => $subscriptions,
        'in_progress_revisions' => $in_progress_revisions,
        'monthly'               => $monthly,
        'this_month_completed'  => $this_month_completed,
        'last_30d_completed'    => $last_30d_completed,
        'today_completed'       => $today_completed,
        'one_day_rate'          => $one_day_rate,
        'free_rate'             => $free_rate,
        'chart_max'             => $chart_max,
        'chart_step'            => $step,
    );

    return $cache;
}
