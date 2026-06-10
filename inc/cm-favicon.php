<?php
/**
 * Favicon — 루트 /favicon.ico 안정 경로 처리
 *
 * 구글·브라우저·크롤러는 관습적으로 루트 `/favicon.ico`를 먼저 확인한다.
 * 기본 WP는 이 요청에 빈 응답/리다이렉트(운영에선 302 HTML)를 줘서 지저분하므로,
 * `do_faviconico` 훅을 우선순위 1(코어 wp_favicon_request보다 먼저)로 가로채
 * 테마 favicon.png로 301 리다이렉트한다.
 *
 * head의 <link rel="icon">은 header.php에서 별도로 직접 출력 → 둘이 함께 동작.
 * 구글 검색결과 파비콘은 head link만으로도 충족되며, 이 처리는 안정 URL·호환성 보강.
 *
 * @link https://developers.google.com/search/docs/appearance/favicon-in-search
 */

if (!defined('ABSPATH')) exit;

add_action('do_faviconico', 'uw_serve_favicon', 1);
function uw_serve_favicon()
{
    $favicon = get_theme_file_uri('/assets/images/common/favicon.png');
    wp_redirect($favicon, 301);
    exit;
}
