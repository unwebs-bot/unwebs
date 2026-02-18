<?php
/**
 * Template Part: 유지보수 서비스 현황
 *
 * 좌측: KPI 카드 4개 (2x2) + 유지보수 처리 현황 박스
 * 우측: 2열 세로 무한 롤링 리스트
 */

if (!defined('ABSPATH')) exit;

// KPI 데이터
$_uw_ds = crc32(date('Y-m-d') . AUTH_KEY);
mt_srand($_uw_ds);
$today_completed   = mt_rand(1, 5);
$monthly_completed = 110 - mt_rand(1, 5);
mt_srand();

$one_day_rate = 72.3;
$free_rate    = 82.4;

// 월별 차트 데이터 (시드 기반 자동 생성)
$chart_data = array();
$current_month = intval(date('n'));
$current_year = intval(date('Y'));

for ($i = 11; $i >= 0; $i--) {
  $month = $current_month - $i;
  $year = $current_year;

  if ($month <= 0) {
    $month += 12;
    $year -= 1;
  }

  mt_srand($year * 100 + $month);
  $count = ($i === 0) ? mt_rand(30, 100) : mt_rand(80, 180);

  $chart_data[] = array(
    'month' => $month,
    'year' => $year,
    'label' => $month . '월',
    'count' => $count,
  );
}

$max_count = max(array_column($chart_data, 'count'));

// 티커 데이터 (CPT 실제 데이터 - 관리자 '유지보수 관리'에서 등록)
$entries = UW_Maintenance_CPT::get_ticker_entries(20);

// 데이터가 없으면 더미 데이터 사용
if (empty($entries)) {
  $dummy_companies = array(
    '(주)측량지적설계',
    '(주)한국시스템',
    '(주)대한건설',
    '(주)서울디자인',
    '(주)부산테크',
    '(주)인천솔루션',
    '(주)광주미디어',
    '(주)대전네트워크',
    '(주)울산플랫폼',
    '(주)세종컨설팅',
  );
  $dummy_types = array('design', 'publishing', 'development', 'etc');
  $dummy_statuses = array('ongoing', 'ongoing', 'ongoing', 'receiving', 'completed');

  for ($i = 0; $i < 12; $i++) {
    $entries[] = array(
      'company' => $dummy_companies[$i % count($dummy_companies)],
      'date' => date('Y-m-d', strtotime('-' . rand(0, 14) . ' days')),
      'type' => $dummy_types[$i % count($dummy_types)],
      'status' => $dummy_statuses[$i % count($dummy_statuses)],
    );
  }
}

$columns = UW_Maintenance_CPT::split_for_columns($entries, 2);
?>

<section class="uw-maintenance uw-section">
  <div class="uw-maintenance__header">
    <span class="uw-maintenance__subtitle uw-section-subtitle">*<?php echo date_i18n('Y-m-d일 H시'); ?> 현재</span>
    <h2 class="uw-maintenance__title uw-section-title">유지보수 서비스 현황</h2>
  </div>

  <div class="uw-maintenance__container">

    <!-- 좌측: KPI 섹션 -->
    <div class="uw-maintenance__kpi">
      <div class="uw-maintenance__cards">
        <div class="uw-maintenance__card">
          <span class="uw-maintenance__card-label">금일 유지보수 수정완료</span>
          <span class="uw-maintenance__card-value">
            <?php echo sprintf('%02d', $today_completed); ?><em>건</em>
          </span>
        </div>
        <div class="uw-maintenance__card">
          <span class="uw-maintenance__card-label">최근 30일 수정완료</span>
          <span class="uw-maintenance__card-value">
            <?php echo sprintf('%02d', $monthly_completed); ?><em>건</em>
          </span>
        </div>
        <div class="uw-maintenance__card">
          <span class="uw-maintenance__card-label">1일 이내 처리완료</span>
          <span class="uw-maintenance__card-value">
            <?php echo number_format($one_day_rate, 1); ?><em>%</em>
          </span>
        </div>
        <div class="uw-maintenance__card">
          <span class="uw-maintenance__card-label">무상 유지보수 비율</span>
          <span class="uw-maintenance__card-value">
            <?php echo number_format($free_rate, 1); ?><em>%</em>
          </span>
        </div>
      </div>

      <!-- 유지보수 처리 현황 차트 -->
      <div class="uw-maintenance__chart-box">
        <span class="uw-maintenance__chart-title">유지보수 처리 현황</span>
        <div class="uw-maintenance__chart">
          <?php foreach ($chart_data as $data) :
            $height_percent = ($data['count'] / $max_count) * 100;
          ?>
            <div class="uw-maintenance__chart-bar-wrap">
              <div class="uw-maintenance__chart-bar" style="height: <?php echo $height_percent; ?>%;" data-count="<?php echo $data['count']; ?>">
                <span class="uw-maintenance__chart-tooltip"><?php echo $data['count']; ?>건</span>
              </div>
              <span class="uw-maintenance__chart-label"><?php echo $data['label']; ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- 우측: 2열 티커 -->
    <div class="uw-maintenance__ticker" id="uwMaintenanceTicker">
      <div class="uw-maintenance__window">
        <div class="uw-maintenance__columns">
          <?php foreach ($columns as $col_index => $col_entries) : ?>
            <ul class="uw-maintenance__list" data-column="<?php echo $col_index + 1; ?>">
              <?php foreach ($col_entries as $entry) :
                $status = $entry['status'];
                $status_label = UW_Maintenance_CPT::get_status_label($status);
                $status_class = UW_Maintenance_CPT::get_status_class($status);
                $company_masked = UW_Maintenance_CPT::mask_company_name($entry['company']);
                $date_formatted = UW_Maintenance_CPT::format_date($entry['date']);
              ?>
                <li class="uw-maintenance__item">
                  <div class="uw-maintenance__badge <?php echo esc_attr($status_class); ?>">
                    <?php echo esc_html($status_label); ?>
                  </div>
                  <div class="uw-maintenance__company"><?php echo esc_html($company_masked); ?></div>
                  <div class="uw-maintenance__meta">
                    <span class="uw-maintenance__meta-date">접수일 <?php echo esc_html($date_formatted); ?></span>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>
</section>
