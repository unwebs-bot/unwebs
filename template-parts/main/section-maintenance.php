<?php
/**
 * Template Part: 유지보수 서비스 현황
 *
 * 좌측: KPI 카드 4개 (2x2) + 유지보수 처리 현황 박스
 * 우측: 2열 세로 무한 롤링 리스트
 */

if (!defined('ABSPATH')) exit;

// 모든 지표를 SSOT 함수에서 가져옴 — 배너·KPI·차트 일관성 보장
$metrics              = uw_maintenance_metrics();
$today_completed      = $metrics['today_completed'];
$this_month_completed = $metrics['this_month_completed'];
$one_day_rate         = $metrics['one_day_rate'];
$free_rate            = $metrics['free_rate'];
$chart_data           = $metrics['monthly'];
$chart_max            = $metrics['chart_max'];
$chart_step           = $metrics['chart_step'];

// 티커 데이터 — 더미 회사명 시드 기반 생성 (주 1회 셔플)
$entries = array();

if (empty($entries)) {
  $dummy_company_names = array(
    '솔라리스에너지', '바르다푸드', '데일리모먼트', '오로라랩',
    '리브리즈', '청명한의원', '하루공방', '한결건설',
    '미래건축', '그린하우스', '닥터스미스', '코어뷰티',
    '블리스케어', '피에스타디자인', '한빛이엔씨', '온유컴퍼니',
    '로컬베이커리', '오아시스컨설팅', '퀀텀리프', '스카이블루',
    '네이처브릿지', '프라임클리닉', '블루웨일', '언디자이닝',
  );
  // 빈 문자열은 단순 브랜드명(법인 형태 미표기) → 자연스러운 다양성
  $dummy_prefixes = array('(주) ', '주식회사 ', '(유) ', '', '', '');
  $dummy_types    = array('design', 'publishing', 'development', 'etc');
  // status 분포: 진행중 60%, 수정완료 40% (접수중은 ticker 노출 제외)
  $dummy_statuses = array('ongoing', 'ongoing', 'ongoing', 'completed', 'completed');

  $_uw_salt = function_exists('wp_salt') ? wp_salt('auth') : (defined('AUTH_KEY') ? AUTH_KEY : uniqid('', true));
  mt_srand(crc32('maintenance-dummy-' . date('oW') . $_uw_salt));

  // 회사명 셔플 후 16개 픽
  $shuffled = $dummy_company_names;
  for ($s = count($shuffled) - 1; $s > 0; $s--) {
      $j = mt_rand(0, $s);
      $tmp = $shuffled[$s]; $shuffled[$s] = $shuffled[$j]; $shuffled[$j] = $tmp;
  }

  /**
   * status별 접수일 범위 (비즈니스 로직 정합)
   *   - ongoing   (진행중): 1~4일 — 진행 중인 건은 며칠 내
   *   - completed (수정완료): 2~7일 — 완료된 건은 처리 시간 반영
   *   ※ receiving(접수중)은 당일 건만 가능 — ticker 노출에서 제외
   */
  $status_date_range = array(
    'ongoing'   => array(1, 4),
    'completed' => array(2, 7),
  );

  for ($i = 0; $i < 16; $i++) {
    $name   = $shuffled[$i % count($shuffled)];
    $prefix = $dummy_prefixes[mt_rand(0, count($dummy_prefixes) - 1)];
    $status = $dummy_statuses[mt_rand(0, count($dummy_statuses) - 1)];
    $range  = $status_date_range[$status];
    $entries[] = array(
      'company' => $prefix . $name,
      'date'    => date('Y-m-d', strtotime('-' . mt_rand($range[0], $range[1]) . ' days')),
      'type'    => $dummy_types[mt_rand(0, count($dummy_types) - 1)],
      'status'  => $status,
    );
  }
  mt_srand();
}

$columns = UW_Maintenance_CPT::split_for_columns($entries, 2);
?>

<section class="main-maintenance-con cm-section">
  <div class="area">
    <div class="cm-tit-box" data-animate="fade-up">
      <span class="cm-tit-sub">*<?php echo date_i18n('Y-m-d일 H시'); ?> 현재</span>
      <h2 class="cm-tit">유지보수 서비스 현황</h2>
    </div>

    <div class="main-maintenance-wrap" data-animate="fade-up" data-delay="200">

    <!-- 좌측: KPI 섹션 -->
    <div class="main-maintenance-kpi">
      <div class="main-maintenance-cards">
        <div class="main-maintenance-card">
          <span class="main-maintenance-card-label">금일 처리완료</span>
          <span class="main-maintenance-card-value">
            <?php echo sprintf('%02d', $today_completed); ?><em>건</em>
          </span>
        </div>
        <div class="main-maintenance-card">
          <span class="main-maintenance-card-label">이번달 처리완료</span>
          <span class="main-maintenance-card-value">
            <?php echo sprintf('%02d', $this_month_completed); ?><em>건</em>
          </span>
        </div>
        <div class="main-maintenance-card">
          <span class="main-maintenance-card-label">1일 이내 처리율</span>
          <span class="main-maintenance-card-value">
            <?php echo number_format($one_day_rate, 1); ?><em>%</em>
          </span>
        </div>
        <div class="main-maintenance-card">
          <span class="main-maintenance-card-label">무상 유지보수 비율</span>
          <span class="main-maintenance-card-value">
            <?php echo number_format($free_rate, 1); ?><em>%</em>
          </span>
        </div>
      </div>

      <!-- 유지보수 처리 현황 차트 — 무상/유상 스택형 -->
      <div class="main-maintenance-chart-box">
        <div class="main-maintenance-chart-head">
          <span class="main-maintenance-chart-tit">최근 6개월간 유지보수 작업 현황</span>
          <ul class="main-maintenance-chart-legend">
            <li class="is-free"><span class="dot"></span>무상</li>
            <li class="is-paid"><span class="dot"></span>유상</li>
          </ul>
        </div>

        <div class="main-maintenance-chart-body">
          <ul class="main-maintenance-chart-yaxis">
            <?php for ($i = 5; $i >= 0; $i--) : ?>
              <li><?php echo number_format($chart_step * $i); ?>건</li>
            <?php endfor; ?>
          </ul>

          <div class="main-maintenance-chart-plot">
            <div class="main-maintenance-chart-grid" aria-hidden="true">
              <?php for ($i = 0; $i < 6; $i++) : ?><span></span><?php endfor; ?>
            </div>

            <div class="main-maintenance-chart-bars">
              <?php foreach ($chart_data as $data) :
                $free_h = ($data['free'] / $chart_max) * 100;
                $paid_h = ($data['paid'] / $chart_max) * 100;
              ?>
              <div class="main-maintenance-chart-col">
                <div class="main-maintenance-chart-stack">
                  <div class="main-maintenance-chart-paid" style="height: <?php echo $paid_h; ?>%">
                    <?php if ($data['paid'] > 0) : ?>
                      <span class="main-maintenance-chart-num main-maintenance-chart-num-paid"><?php echo $data['paid']; ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="main-maintenance-chart-free" style="height: <?php echo $free_h; ?>%">
                    <?php if ($data['free'] >= 50) : ?>
                      <span class="main-maintenance-chart-num main-maintenance-chart-num-free"><?php echo $data['free']; ?></span>
                    <?php endif; ?>
                  </div>
                </div>
                <span class="main-maintenance-chart-xlabel"><?php echo esc_html($data['label']); ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 우측: 2열 티커 (외곽 다크 박스) -->
    <div class="main-maintenance-ticker" id="cmMaintenanceTicker">
      <div class="main-maintenance-ticker-frame">
      <div class="main-maintenance-window">
        <div class="main-maintenance-columns">
          <?php foreach ($columns as $col_index => $col_entries) : ?>
            <div class="main-maintenance-track" data-column="<?php echo $col_index + 1; ?>">
              <?php // 동일한 ul 2개 — 정확한 -50% 무한 루프 보장 (sub-pixel 오차 차단) ?>
              <?php for ($r = 0; $r < 2; $r++) : ?>
              <ul class="main-maintenance-list"<?php echo $r > 0 ? ' aria-hidden="true"' : ''; ?>>
                <?php foreach ($col_entries as $entry) :
                  $status = $entry['status'];
                  $status_label = UW_Maintenance_CPT::get_status_label($status);
                  $status_class = UW_Maintenance_CPT::get_status_class($status);
                  $company_masked = UW_Maintenance_CPT::mask_company_name($entry['company']);
                  $date_formatted = UW_Maintenance_CPT::format_date($entry['date']);
                ?>
                  <li class="main-maintenance-item">
                    <div class="main-maintenance-badge <?php echo esc_attr($status_class); ?>">
                      <?php echo esc_html($status_label); ?>
                    </div>
                    <div class="main-maintenance-company"><?php echo esc_html($company_masked); ?></div>
                    <div class="main-maintenance-meta">
                      <span class="main-maintenance-meta-date">접수일 <?php echo esc_html($date_formatted); ?></span>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
              <?php endfor; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      </div>
    </div>

    </div>
  </div>
</section>
