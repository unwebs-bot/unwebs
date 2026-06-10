<?php
/**
 * 보안 강화 모듈 (2026-05-20 보안 검수 P0+P1 패치)
 *
 *  1. 보안 헤더: HSTS · Permissions-Policy · 보강
 *  2. xmlrpc.php 비활성화 (브루트포스 채널 차단)
 *  3. /readme.html · /license.txt · /wp-config-sample.php 404 차단
 *  4. WP 버전 노출 차단 (generator 메타)
 *  5. uw_inquiry 폼 honeypot 필드 + 서버 검증
 */

if (!defined('ABSPATH')) exit;


/* ==========================================================================
   1) 보안 헤더 추가
   ========================================================================== */
add_action('send_headers', 'uw_security_headers', 1);
function uw_security_headers()
{
    if (is_admin()) return;

    // HSTS — HTTPS 환경에서만 의미. 1년 + subdomain + preload.
    if (is_ssl()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }

    // Permissions-Policy — 불필요한 브라우저 API 차단
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=(), accelerometer=(), gyroscope=(), magnetometer=()');

    // 기존 X-Frame / X-Content / Referrer-Policy 는 WP 또는 nginx에서 설정되어 있음
    // 누락 시를 대비해 한 번 더 보강
    if (!headers_sent()) {
        if (!array_filter(headers_list(), function ($h) { return stripos($h, 'X-Content-Type-Options:') === 0; })) {
            header('X-Content-Type-Options: nosniff');
        }
        if (!array_filter(headers_list(), function ($h) { return stripos($h, 'Referrer-Policy:') === 0; })) {
            header('Referrer-Policy: strict-origin-when-cross-origin');
        }
    }
}


/* ==========================================================================
   2) xmlrpc.php 비활성화 + 강제 차단
   ========================================================================== */
add_filter('xmlrpc_enabled', '__return_false');
add_filter('wp_xmlrpc_methods', '__return_empty_array');
add_filter('xmlrpc_methods', '__return_empty_array');
add_filter('pre_update_option_enable_xmlrpc', '__return_false');
remove_action('wp_head', 'rsd_link');                  // RSD 링크 제거
remove_action('wp_head', 'wlwmanifest_link');          // Windows Live Writer 매니페스트 제거

// 핑백 헤더 제거
add_filter('xmlrpc_methods', function ($methods) {
    unset($methods['pingback.ping'], $methods['pingback.extensions.getPingbacks']);
    return $methods;
});

// /xmlrpc.php 자체 요청을 즉시 차단 (require 시점에 바로 검사 — action 훅 시점 이슈 회피)
if (isset($_SERVER['REQUEST_URI'])) {
    $uri = strtolower((string) parse_url((string) wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH));
    if (substr($uri, -strlen('/xmlrpc.php')) === '/xmlrpc.php') {
        @http_response_code(403);
        @header('Content-Type: text/plain; charset=utf-8');
        @header('X-Content-Type-Options: nosniff');
        echo 'xmlrpc disabled';
        exit;
    }
}


/* ==========================================================================
   3) readme.html / license.txt / wp-config-sample.php 등 404
   ========================================================================== */
add_action('template_redirect', 'uw_security_block_sensitive_paths', 1);
function uw_security_block_sensitive_paths()
{
    if (is_admin()) return;
    $uri = isset($_SERVER['REQUEST_URI'])
        ? parse_url(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])), PHP_URL_PATH)
        : '';
    $clean = strtolower(trim((string) $uri, '/'));

    $blocked = array(
        'readme.html',
        'license.txt',
        'wp-config-sample.php',
        'wp-content/debug.log',
    );
    if (in_array($clean, $blocked, true)) {
        status_header(404);
        nocache_headers();
        exit;
    }
}


/* ==========================================================================
   4) WP 버전 노출 차단 (generator 메타 + 스크립트/스타일의 ver 쿼리)
   ========================================================================== */
remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');

// 외부 리소스의 ver=X.Y.Z 쿼리 제거 (정적 자산엔 유지하고 싶다면 조건부 적용 가능)
// add_filter('style_loader_src', 'uw_security_remove_ver_query', 9999);
// add_filter('script_loader_src', 'uw_security_remove_ver_query', 9999);
// function uw_security_remove_ver_query($src) {
//     if (strpos($src, 'ver=') !== false) {
//         $src = remove_query_arg('ver', $src);
//     }
//     return $src;
// }


/* ==========================================================================
   5) uw_inquiry 폼 honeypot — 봇이 자동으로 채우는 hidden 필드
      필드명을 일반적인 이름(website)으로 위장. 값이 채워져 있으면 봇으로 판단.
   ========================================================================== */

// 폼 렌더 시 honeypot 필드 주입
add_action('uw_inquiry_form_before_submit', 'uw_security_honeypot_field');
if (!function_exists('uw_security_honeypot_field')) {
    function uw_security_honeypot_field()
    {
        ?>
        <div aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;" tabindex="-1">
            <label for="uw_hp_website">웹사이트 (입력하지 마세요)</label>
            <input type="text" id="uw_hp_website" name="uw_hp_website" value="" autocomplete="off" tabindex="-1">
        </div>
        <?php
    }
}

// 폼 제출 시 honeypot 검사 — 값이 있으면 silently drop (봇에게 성공 응답 위장)
add_action('wp_ajax_uw_inquiry_submit', 'uw_security_honeypot_check', 1);
add_action('wp_ajax_nopriv_uw_inquiry_submit', 'uw_security_honeypot_check', 1);
function uw_security_honeypot_check()
{
    if (!empty($_POST['uw_hp_website'])) {
        // 봇에게 성공으로 위장하여 재시도 방지 + 로깅
        if (function_exists('error_log') && defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[uw-security] Honeypot triggered: ' . substr(sanitize_text_field(wp_unslash($_POST['uw_hp_website'])), 0, 50));
        }
        wp_send_json_success(array('message' => '문의가 정상 접수되었습니다.'));
    }
}
