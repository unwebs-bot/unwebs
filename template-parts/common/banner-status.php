<?php
/**
 * Template Part: 메인페이지 상단 진행현황 배너
 *
 * 자동 생성 데이터(uw_banner_get_data) + 메인페이지에서만 노출.
 * 스크롤 시 배너만 숨김(헤더는 is-scrolled 상태로 유지).
 */

if (!defined('ABSPATH')) exit;

$banner = uw_banner_get_data();
$available_total_display = str_pad($banner['available_total'], 2, '0', STR_PAD_LEFT);
?>

<div class="main-status-banner-con">
  <div class="area">
    <div class="main-status-banner-wrap">

    <div class="main-status-banner-group">
      <span class="main-status-banner-badge">최근 1개월 현황</span>
      <div class="main-status-banner-data">
        <span class="main-status-banner-label">제작진행</span>
        <span class="main-status-banner-value"><?php echo esc_html($banner['production_count']); ?>건</span>
        <span class="main-status-banner-divider">|</span>
        <span class="main-status-banner-label">수정진행</span>
        <span class="main-status-banner-value"><?php echo esc_html($banner['revision_count']); ?>건</span>
        <span class="main-status-banner-divider">|</span>
        <span class="main-status-banner-label">정기 유지보수</span>
        <span class="main-status-banner-value"><?php echo esc_html($banner['maintenance_count']); ?>개사</span>
      </div>
    </div>

    <div class="main-status-banner-group">
      <span class="main-status-banner-badge"><?php echo esc_html($banner['quarter_text']); ?></span>
      <div class="main-status-banner-data">
        <span class="main-status-banner-label">프로젝트 진행가능 건수</span>
        <span class="main-status-banner-value"><?php echo esc_html($banner['available_count']); ?>건
        (<?php echo esc_html($banner['available_count']); ?>/<?php echo esc_html($available_total_display); ?>)</span>
      </div>
    </div>

    <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="main-status-banner-cta">
      <span class="main-status-banner-cta-txt">제작문의 <i class="main-status-banner-cta-icon xi-message-o"></i></span>
    </a>

    </div>
  </div>
</div>

