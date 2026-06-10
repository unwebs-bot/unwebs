<?php
/**
 * Google Tag Manager (GTM-5L4PSRCW)
 *
 * head 스니펫 + body noscript 출력. GA4·기타 태그는 GTM 웹UI 컨테이너에서 구성한다.
 * 운영(production) 환경에서만 출력 → 로컬·스테이징 트래픽이 분석 데이터에 섞이지 않음.
 *
 * 호출: header.php
 *   - uw_gtm_head()     : <head> 상단
 *   - uw_gtm_noscript() : <body> 직후 (wp_body_open 다음)
 *
 * 조정:
 *   - GTM ID 변경    : define('UW_GTM_ID', 'GTM-XXXXXXX');  (wp-config 또는 functions)
 *   - 로컬에서도 켜기 : add_filter('uw_gtm_enabled', '__return_true');
 */

if (!defined('ABSPATH')) exit;

/**
 * GTM 컨테이너 ID
 */
function uw_gtm_id()
{
    $id = defined('UW_GTM_ID') ? UW_GTM_ID : 'GTM-5L4PSRCW';
    return apply_filters('uw_gtm_id', $id);
}

/**
 * GTM 출력 여부 — 기본: 운영 환경 + ID 존재
 */
function uw_gtm_enabled()
{
    $enabled = (wp_get_environment_type() === 'production') && uw_gtm_id() !== '';
    return (bool) apply_filters('uw_gtm_enabled', $enabled);
}

/**
 * <head> 상단 스니펫
 */
function uw_gtm_head()
{
    if (!uw_gtm_enabled()) return;
    $id = uw_gtm_id();
    ?>
  <!-- Google Tag Manager -->
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','<?php echo esc_js($id); ?>');</script>
  <!-- End Google Tag Manager -->
    <?php
}

/**
 * <body> 직후 noscript (JS 비활성 사용자·크롤러 대응)
 */
function uw_gtm_noscript()
{
    if (!uw_gtm_enabled()) return;
    $id = uw_gtm_id();
    ?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr($id); ?>"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
    <?php
}
