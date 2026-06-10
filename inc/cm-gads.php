<?php
/**
 * Google Ads — 전역 사이트 태그(gtag.js) + 전환 헬퍼
 *
 * 전환 ID: AW-18228267880 / 전환 액션: 리드 양식 제출 (라벨 n2mbCJDBrbwcEOiW9fND)
 * 운영(production) 환경에서만 출력 → 로컬·스테이징 트래픽이 전환에 안 섞임.
 * cm-gtm.php · cm-naver-ads.php 와 동일 패턴.
 *
 * ── 설정 (필요 시 wp-config.php) ───────────────────────────
 *   define('UW_GADS_ID', 'AW-18228267880');                    // 전환 ID (기본값 내장)
 *   define('UW_GADS_CONVERSION_LABEL', 'n2mbCJDBrbwcEOiW9fND'); // 전환 라벨 (기본값 내장)
 *   // 값/통화 변경: add_filter('uw_gads_conversion_value', function(){ return 1.0; });
 *   //              add_filter('uw_gads_conversion_currency', function(){ return 'KRW'; });
 *   // 로컬에서도 테스트: add_filter('uw_gads_enabled', '__return_true');
 *
 * ── 발화 지점 ─────────────────────────────────────────────
 *   - 전역 태그 : 모든 페이지 <head>  (header.php → uw_gads_head)
 *   - 전환      : 문의 완료 페이지 도달 시 window.uwGadsConversion()  (page-inquiry-complete.php)
 *               sessionStorage 플래그로 실제 제출 도착 시에만 1회 발화(새로고침·직접접근 중복 방지)
 */

if (!defined('ABSPATH')) exit;

/** 전환 ID */
function uw_gads_id()
{
    $id = defined('UW_GADS_ID') ? UW_GADS_ID : 'AW-18228267880';
    return apply_filters('uw_gads_id', $id);
}

/** 전환 액션 라벨 (Google Ads 전환의 'AW-xxxx/라벨' 중 라벨 부분) */
function uw_gads_conversion_label()
{
    $label = defined('UW_GADS_CONVERSION_LABEL') ? UW_GADS_CONVERSION_LABEL : 'n2mbCJDBrbwcEOiW9fND';
    return apply_filters('uw_gads_conversion_label', $label);
}

/** 출력 여부 — 기본: 운영 환경 + ID 존재 */
function uw_gads_enabled()
{
    $enabled = (wp_get_environment_type() === 'production') && uw_gads_id() !== '';
    return (bool) apply_filters('uw_gads_enabled', $enabled);
}

/** <head>: 전역 사이트 태그(gtag.js) + 전환 헬퍼 정의 */
function uw_gads_head()
{
    if (!uw_gads_enabled()) return;
    $id       = uw_gads_id();
    $label    = uw_gads_conversion_label();
    $send_to  = $label !== '' ? $id . '/' . $label : $id;
    $value    = apply_filters('uw_gads_conversion_value', 1.0);
    $currency = apply_filters('uw_gads_conversion_currency', 'KRW');
    ?>
  <!-- Google tag (gtag.js) — Google Ads -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($id); ?>"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?php echo esc_js($id); ?>');
    // 문의 완료 페이지 도달 시 호출 → Google Ads 전환 기록 (리드 양식 제출)
    window.uwGadsConversion = function () {
      gtag('event', 'conversion', {
        'send_to': '<?php echo esc_js($send_to); ?>',
        'value': <?php echo floatval($value); ?>,
        'currency': '<?php echo esc_js($currency); ?>'
      });
    };
  </script>
  <!-- End Google tag -->
    <?php
}
