<?php
/**
 * Template Part: 실시간 프로젝트 현황 & 견적 안내 대시보드
 */

if (!defined('ABSPATH')) exit;

$project_data = uw_dashboard_get_projects();
$consult_data = uw_dashboard_get_consults();

// 데이터가 없으면 섹션 미표시
if (empty($project_data) && empty($consult_data)) return;
?>

<section class="main-dashboard-con cm-section">
  <div class="area">
    <div class="main-dashboard-wrap">

    <!-- 실시간 프로젝트 현황 -->
    <div class="main-dashboard-section" data-ticker="project">
      <h2 class="main-dashboard-tit">실시간 프로젝트 현황</h2>
      <div class="main-dashboard-window">
        <ul class="main-dashboard-list" id="cmDashboardProject">
          <?php foreach ($project_data as $item) : ?>
          <li class="main-dashboard-item">
            <div class="main-dashboard-item-left">
              <span class="main-dashboard-tag"><?php echo esc_html($item['type']); ?></span>
              <div class="main-dashboard-info">
                <span class="main-dashboard-item-tit"><?php echo esc_html($item['title']); ?></span>
                <span class="main-dashboard-date"><?php echo esc_html($item['date']); ?></span>
              </div>
            </div>
            <span class="main-dashboard-badge <?php echo uw_dashboard_status_class($item['status']); ?>"><?php echo esc_html($item['status']); ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <!-- 상담 및 견적 안내 현황 -->
    <div class="main-dashboard-section" data-ticker="consult">
      <h2 class="main-dashboard-tit">상담문의 현황</h2>
      <div class="main-dashboard-window">
        <ul class="main-dashboard-list" id="cmDashboardConsult">
          <?php foreach ($consult_data as $item) : ?>
          <li class="main-dashboard-item">
            <div class="main-dashboard-item-left">
              <span class="main-dashboard-tag"><?php echo esc_html($item['type']); ?></span>
              <div class="main-dashboard-info">
                <span class="main-dashboard-item-tit"><?php echo esc_html($item['title']); ?></span>
                <span class="main-dashboard-date"><?php echo esc_html($item['date']); ?></span>
              </div>
            </div>
            <span class="main-dashboard-badge <?php echo uw_dashboard_status_class($item['status']); ?>"><?php echo esc_html($item['status']); ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    </div>

    <div class="main-dashboard-footer">
      <span class="main-dashboard-footer-date">*<?php echo date_i18n('Y년 n월 j일'); ?> 기준</span>
    </div>
  </div>
</section>
