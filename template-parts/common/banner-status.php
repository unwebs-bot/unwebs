<?php
/**
 * Template Part: 메인페이지 상단 진행현황 배너
 */

if (!defined('ABSPATH')) exit;

$banner = uw_banner_get_data();

// 배너 비표시 설정이면 출력하지 않음
if (empty($banner['banner_visible'])) {
    return;
}

$available_total_display = str_pad($banner['available_total'], 2, '0', STR_PAD_LEFT);

// 쿼터 텍스트: 관리자 입력값이 있으면 사용, 없으면 현재 연월 자동 생성
$quarter_text = !empty($banner['quarter_text'])
    ? $banner['quarter_text']
    : date_i18n('Y') . '년 ' . date_i18n('n') . '월 쿼터';
?>

<div class="uw-status-banner">
  <div class="uw-status-banner__inner">

    <div class="uw-status-banner__group">
      <span class="uw-status-banner__badge">진행현황</span>
      <div class="uw-status-banner__data">
        <span class="uw-status-banner__label">제작진행</span>
        <span class="uw-status-banner__value"><?php echo esc_html($banner['production_count']); ?>건</span>
        <span class="uw-status-banner__divider">|</span>
        <span class="uw-status-banner__label">수정진행</span>
        <span class="uw-status-banner__value"><?php echo esc_html($banner['revision_count']); ?>건</span>
        <span class="uw-status-banner__divider">|</span>
        <span class="uw-status-banner__label">정기 유지보수</span>
        <span class="uw-status-banner__value"><?php echo esc_html($banner['maintenance_count']); ?>건</span>
      </div>
    </div>

    <div class="uw-status-banner__group">
      <span class="uw-status-banner__badge"><?php echo esc_html($quarter_text); ?></span>
      <div class="uw-status-banner__data">
        <span class="uw-status-banner__label">프로젝트 진행가능 건수</span>
        <span class="uw-status-banner__value"><?php echo esc_html($banner['available_count']); ?>건
        (<?php echo esc_html($banner['available_count']); ?>/<?php echo esc_html($available_total_display); ?>)</span>
      </div>
    </div>

    <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="uw-status-banner__cta">
      <span class="uw-status-banner__cta-text">제작문의 <i class="uw-status-banner__cta-icon xi-message-o"></i></span>
    </a>

  </div>
</div>
