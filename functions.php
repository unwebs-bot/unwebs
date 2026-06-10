<?php
/**
 * Starter Theme Functions
 */

// Load config
require_once get_template_directory() . '/inc/config.php';

/**
 * Theme Setup
 */
add_action('after_setup_theme', 'starter_setup');
function starter_setup()
{
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('html5', array('search-form', 'gallery', 'caption'));
    register_nav_menu('header-menu', 'Header Menu');
}

/**
 * Get file version based on modification time (cache busting)
 *
 * @param string $file Relative file path from theme directory
 * @return string File modification timestamp or fallback version
 */
function starter_get_version($file)
{
    $file_path = get_template_directory() . $file;
    return file_exists($file_path) ? filemtime($file_path) : '1.0.0';
}

/**
 * Enqueue Assets
 */
add_action('wp_enqueue_scripts', 'starter_enqueue_assets');
function starter_enqueue_assets()
{
    // XEIcon
    wp_enqueue_style('xeicon', 'https://cdn.jsdelivr.net/gh/xpressengine/XEIcon@2.3.3/xeicon.min.css', array(), '2.3.3');

    // Web Fonts (Nohemi @font-face — 항상 캐시 버스팅 적용)
    wp_enqueue_style('uw-fonts', get_theme_file_uri('/assets/css/fonts.css'), array(), starter_get_version('/assets/css/fonts.css'));

    // Theme CSS (자동 버전 관리)
    wp_enqueue_style('theme-style', get_stylesheet_uri(), array('uw-fonts'), starter_get_version('/style.css'));
    wp_enqueue_style('main-style', get_theme_file_uri('/assets/css/style.css'), array('theme-style'), starter_get_version('/assets/css/style.css'));

    // JS (자동 버전 관리)
    wp_enqueue_script('header', get_theme_file_uri('/assets/js/header.js'), array(), starter_get_version('/assets/js/header.js'), true);
    wp_enqueue_script('footer', get_theme_file_uri('/assets/js/footer.js'), array(), starter_get_version('/assets/js/footer.js'), true);
    wp_enqueue_script('scroll-toggle', get_theme_file_uri('/assets/js/scroll-toggle.js'), array('header'), starter_get_version('/assets/js/scroll-toggle.js'), true);
    wp_enqueue_script('main', get_theme_file_uri('/assets/js/main.js'), array('header', 'footer'), starter_get_version('/assets/js/main.js'), true);

    // Dev Debug — ?debug=1 쿼리 또는 WP_DEBUG 시에만 로드
    if ((isset($_GET['debug']) && $_GET['debug'] === '1') || (defined('WP_DEBUG') && WP_DEBUG && isset($_GET['uwdebug']))) {
        wp_enqueue_style('uw-dev-debug', get_theme_file_uri('/assets/css/dev-debug.css'), array('main-style'), starter_get_version('/assets/css/dev-debug.css'));
        wp_enqueue_script('uw-dev-debug', get_theme_file_uri('/assets/js/dev-debug.js'), array(), starter_get_version('/assets/js/dev-debug.js'), true);
    }

    // Service 페이지 — 플로팅 섹션 탭 + materials 영상 토글
    if (is_page_template('page-service.php') || is_page('service')) {
        wp_enqueue_script('uw-service-tabs', get_theme_file_uri('/assets/js/service-tabs.js'), array(), starter_get_version('/assets/js/service-tabs.js'), true);
        wp_enqueue_script('uw-service-materials-video', get_theme_file_uri('/assets/js/service-materials-video.js'), array(), starter_get_version('/assets/js/service-materials-video.js'), true);
    }

    // Splitting.js — 전체 페이지 (cm-tit / cm-tit-sub / cm-tit-txt / main-visual-tit)
    wp_enqueue_script('splitting-js', 'https://unpkg.com/splitting/dist/splitting.min.js', array(), '1.1.0', true);
    wp_enqueue_script('uw-splitting-init', get_theme_file_uri('/assets/js/splitting-init.js'), array('splitting-js'), starter_get_version('/assets/js/splitting-init.js'), true);
}

/**
 * 서브페이지 공통 — body에 is-sub 클래스 자동 부착
 *
 * front-page 외 모든 페이지(서브, archive, single, blog 등)에 'is-sub' 추가.
 * CSS에서 `.is-sub .cm-main`에 헤더 높이만큼 padding-top을 줘 sticky 헤더와 첫 섹션의 시각 여백을 보정.
 */
add_filter('body_class', 'uw_body_is_sub_class');
function uw_body_is_sub_class($classes)
{
    if (!is_front_page()) {
        $classes[] = 'is-sub';
    }
    return $classes;
}

/**
 * Portfolio — 100% 코드 큐레이션 시스템 (2026-04-30)
 *
 * - 데이터 소스: `inc/cm-portfolio-main.php`
 * - URL: `/portfolio/{slug}/` → rewrite rule + query var → page-portfolio-case.php 템플릿
 * - 메인 캐러셀 카드 클릭 → 자체 single → 클라이언트 외부 사이트는 안에서 별도 버튼
 * - CPT 미사용 — 등록·DB 0개. 코드 수정 = 즉시 반영
 */
add_action('init', 'uw_portfolio_rewrite_rules');
function uw_portfolio_rewrite_rules()
{
    add_rewrite_rule('^portfolio/([^/]+)/?$', 'index.php?cm_port_slug=$matches[1]', 'top');
}

add_filter('query_vars', 'uw_portfolio_query_vars');
function uw_portfolio_query_vars($vars)
{
    $vars[] = 'cm_port_slug';
    return $vars;
}

add_filter('template_include', 'uw_portfolio_template_include');
function uw_portfolio_template_include($template)
{
    $slug = get_query_var('cm_port_slug');
    if (!$slug) return $template;

    if (function_exists('cm_get_portfolio_by_slug') && cm_get_portfolio_by_slug($slug)) {
        return get_template_directory() . '/page-portfolio-case.php';
    }
    // slug 매칭 안 되면 404
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    return get_query_template('404');
}

/**
 * Portfolio single CSS enqueue (커스텀 라우트에선 is_singular()가 false라 별도 분기)
 */
add_action('wp_enqueue_scripts', 'uw_portfolio_case_assets');
function uw_portfolio_case_assets()
{
    if (!get_query_var('cm_port_slug')) return;
    wp_enqueue_style(
        'uw-portfolio',
        get_theme_file_uri('/assets/css/cpt/portfolio/portfolio.css'),
        array('main-style'),
        starter_get_version('/assets/css/cpt/portfolio/portfolio.css')
    );
}

/**
 * Main Portfolio (공유 데이터 모듈) — 메인 섹션 + ItemList JSON-LD가 같은 함수 사용
 */
require_once get_template_directory() . '/inc/cm-portfolio-main.php';

// 포트폴리오 순서관리 (wp-admin 드래그&드롭)
if (is_admin()) {
    require_once get_template_directory() . '/inc/uw-portfolio-admin.php';
}

/**
 * Front Page — 메인 페이지 ItemList JSON-LD (네이버 캐러셀 후보)
 *
 * 결정 근거 (위키 [[네이버-캐러셀-listitem-적용]] 미해결 항목):
 *  - item 타입: Organization — 네이버 공식 캐러셀 예제가 Organization(계열사 6개) 사용 + 포트폴리오
 *    각 항목이 클라이언트 조직(회사·병원)명이라 의미론 부합. 공식 정합이 가장 방어적 (외부 교차검증 2026-06-09).
 *  - image: 세로형 4:5(1080×1350) card_image 우선 사용. 네이버 공식엔 비율 규정 없으나 실제 캐러셀
 *    카드가 세로~정사각이라 4:5 채택(2026-06-09). 썸네일 금지·300KB↓ 준수. 없으면 image fallback.
 *  - 항목 개수 6 미만이면 마크업 미출력 (네이버 "적으면 노출 거부" 룰).
 *  - 카피 중립: description은 업종·작업타입만. 포지셔닝(운영형태·기술스택 등) 미노출.
 *  - 마크업↔콘텐츠 일치: section-portfolio.php와 동일한 cm_get_main_portfolio_items() 사용.
 *  - 1페이지 = 1 ListItem: 메인에 ItemList 1개. Organization·BreadcrumbList는 별개 OK.
 */
add_action('wp_head', 'uw_main_portfolio_itemlist', 50);
function uw_main_portfolio_itemlist()
{
    if (!is_front_page()) return;
    if (!function_exists('cm_get_main_portfolio_items')) return;

    $items = cm_get_main_portfolio_items();
    if (empty($items)) return;

    $list     = array();
    $position = 1;
    $home_url = rtrim(home_url(), '/');

    foreach ($items as $item) {
        $url = isset($item['url']) ? trim((string) $item['url']) : '';
        // 캐러셀용 가로형(card_image) 우선, 없으면 기존 썸네일(image) fallback
        $image = !empty($item['card_image']) ? trim((string) $item['card_image'])
               : (isset($item['image']) ? trim((string) $item['image']) : '');
        // url·image 둘 다 있어야 ItemList에 포함 (불완전 데이터 자동 제외)
        if ($url === '' || $image === '') continue;

        $url = esc_url_raw($url, array('http', 'https'));
        if (!$url) continue;

        // image — 상대경로면 절대경로로 변환 (네이버는 절대 URL 요구)
        if (strpos($image, 'http') !== 0) {
            $image = $home_url . '/' . ltrim($image, '/');
        }

        $entry_item = array(
            '@type' => 'Organization',
            'name'  => (string) ($item['title'] ?? ''),
            'image' => $image,
            'url'   => $url,
        );

        // description — 업종·타입만 (카피 중립 원칙)
        $desc_parts = array_filter(array(
            isset($item['industry']) ? trim((string) $item['industry']) : '',
            isset($item['type']) ? trim((string) $item['type']) : '',
        ));
        if (!empty($desc_parts)) {
            $entry_item['description'] = implode(' · ', $desc_parts);
        }

        $list[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'item'     => $entry_item,
        );
    }

    // 6 미만이면 노출 거부 가능성 → 마크업 출력 안 함
    if (count($list) < 6) return;

    $schema = array(
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => '포트폴리오',
        'itemListElement' => $list,
    );

    echo "\n<script type=\"application/ld+json\">" . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
}

/**
 * Portfolio Case (single) — CreativeWork JSON-LD
 *
 * `/portfolio/{slug}/` 진입 시 출력. 큐레이션 데이터(`cm-portfolio-main.php`) 기반.
 */
add_action('wp_head', 'uw_portfolio_case_jsonld', 50);
function uw_portfolio_case_jsonld()
{
    $slug = get_query_var('cm_port_slug');
    if (!$slug) return;
    if (!function_exists('cm_get_portfolio_by_slug')) return;

    $row = cm_get_portfolio_by_slug($slug);
    if (!$row) return;

    $home_url = rtrim(home_url(), '/');
    $image    = trim((string) ($row['image'] ?? ''));
    if ($image !== '' && strpos($image, 'http') !== 0) {
        $image = $home_url . '/' . ltrim($image, '/');
    }

    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'CreativeWork',
        'name'     => (string) ($row['title'] ?? ''),
        'url'      => $home_url . '/portfolio/' . $row['slug'] . '/',
        'creator'  => array(
            '@type' => 'Organization',
            'name'  => get_bloginfo('name'),
            'url'   => home_url(),
        ),
    );
    if ($image)                            $schema['image']       = $image;
    if (!empty($row['industry']))          $schema['genre']       = $row['industry'];
    if (!empty($row['description']))       $schema['description'] = $row['description'];

    echo "\n<script type=\"application/ld+json\">" . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
}

/**
 * Security Headers
 */
add_action('send_headers', 'starter_security_headers');
function starter_security_headers()
{
    if (!is_admin()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}

/**
 * Cleanup WP Head
 */
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
add_filter('the_generator', '__return_empty_string');
add_filter('show_admin_bar', '__return_false');

/**
 * CPT Engines
 */
require_once get_template_directory() . '/inc/uw-board/class-uw-board-cpt.php';
require_once get_template_directory() . '/inc/uw-board/class-uw-board-admin.php';
require_once get_template_directory() . '/inc/uw-board/class-uw-board-engine.php';

require_once get_template_directory() . '/inc/uw-inquiry/class-uw-inquiry-cpt.php';
require_once get_template_directory() . '/inc/uw-inquiry/class-uw-inquiry-admin.php';
require_once get_template_directory() . '/inc/uw-inquiry/class-uw-inquiry-handler.php';

require_once get_template_directory() . '/inc/uw-auth/class-uw-auth-roles.php';
require_once get_template_directory() . '/inc/uw-auth/class-uw-auth-handler.php';
require_once get_template_directory() . '/inc/uw-auth/class-uw-auth-admin.php';

require_once get_template_directory() . '/inc/uw-quote/class-uw-quote-handler.php';

require_once get_template_directory() . '/inc/uw-maintenance/class-uw-maintenance-cpt.php';

// 2026-05-20 — '전문 칼럼' → 블로그(post) 통합 완료. column CPT 전체 모듈 비활성.
// 잔여 column 게시물(auto-draft 4건)은 DB에 남아있지만 post_type 미등록 상태로 어디서도 표시되지 않음.
// /column/* → 신규 post 슬러그 301 redirect는 functions.php 하단 template_redirect 핸들러가 처리.
// require_once get_template_directory() . '/inc/uw-column/class-uw-column-cpt.php';

/**
 * Maintenance Metrics (SSOT) — 배너·KPI·차트 공유
 */
require_once get_template_directory() . '/inc/cm-maintenance-metrics.php';

/**
 * Banner Settings
 */
require_once get_template_directory() . '/inc/cm-banner-settings.php';

/**
 * Dashboard Settings
 */
require_once get_template_directory() . '/inc/cm-dashboard-data.php';

/**
 * FAQ 데이터 모듈 (페이지별 Q&A 배치)
 */
require_once get_template_directory() . '/inc/cm-faq-data.php';

/**
 * 고정 페이지 자동 생성 (1회성 seed)
 */
require_once get_template_directory() . '/inc/cm-page-seed.php';

/**
 * 공지사항 자동 시드 (notice term + 샘플 공지)
 */
require_once get_template_directory() . '/inc/cm-notice-seed.php';

/**
 * 메인 페이지 문의 폼 자동 등록
 */
require_once get_template_directory() . '/inc/cm-inquiry-seed.php';

/**
 * 블로그 설정 (Customizer + WP admin 편의)
 */
require_once get_template_directory() . '/inc/cm-blog-settings.php';

/**
 * 블로그 글별 FAQ (글 하단 FAQ 섹션 + meta box)
 */
require_once get_template_directory() . '/inc/cm-blog-single-faq.php';

/**
 * Stats 헬퍼 — section-stats / section-contact 공용
 * front-page 외 페이지에서 section-contact 단독 호출 시 fatal 방지
 */
require_once get_template_directory() . '/inc/cm-stats-helpers.php';

/**
 * 보안 강화 — 헤더·xmlrpc·민감 경로 차단·honeypot
 * 2026-05-20 보안 검수 P0+P1 패치
 */
require_once get_template_directory() . '/inc/cm-security.php';

/**
 * 관리자 SMS 알림 (Solapi) — 문의·견적 폼 접수 시 운영자 휴대폰 문자 발송
 */
require_once get_template_directory() . '/inc/cm-sms.php';

/**
 * Google Tag Manager — head 스니펫 + body noscript (운영 환경만 출력)
 */
require_once get_template_directory() . '/inc/cm-gtm.php';

/**
 * Favicon — 루트 /favicon.ico → 테마 favicon.png 301 (안정 URL·크롤러 호환)
 */
require_once get_template_directory() . '/inc/cm-favicon.php';

/**
 * 네이버 검색광고 — 프리미엄 로그분석 + 전환추적 (운영 환경 + UW_NAVER_WA 설정 시만 출력)
 */
require_once get_template_directory() . '/inc/cm-naver-ads.php';

/**
 * Google Ads — 전역 사이트 태그(gtag.js) + 전환 헬퍼 (운영 환경만 출력). 전환: 문의 완료 페이지
 */
require_once get_template_directory() . '/inc/cm-gads.php';

/**
 * Service 하위 URL 통합 — /service-materials, /service-process → /service#앵커 301 redirect
 * 사이트맵 통합 이전 인덱싱된 URL 보존용
 */
add_action('template_redirect', function () {
    if (is_admin()) return;
    $uri = isset($_SERVER['REQUEST_URI'])
        ? parse_url(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])), PHP_URL_PATH)
        : '';
    $clean = trim((string) $uri, '/');
    $map = array(
        'service-materials' => '/service#materials',
        'service-process'   => '/service#process',
        'service-intro'     => '/service',
    );
    if (isset($map[$clean])) {
        wp_safe_redirect(home_url($map[$clean]), 301);
        exit;
    }

    // 구 전문 칼럼(column) URL → post 통합 후 신규 URL 301 (2026-05-20 통합)
    // /column/{slug}/  → /{slug}/   (slug는 동일 유지, post_type만 변경됨)
    // /column/         → /blog/
    if ($clean === 'column' || $clean === 'column/') {
        wp_safe_redirect(home_url('/blog/'), 301);
        exit;
    }
    if (strpos($clean, 'column/') === 0) {
        $slug = trim(substr($clean, strlen('column/')), '/');
        if ($slug !== '') {
            $post = get_page_by_path($slug, OBJECT, 'post');
            if ($post && $post->post_status === 'publish') {
                wp_safe_redirect(get_permalink($post), 301);
                exit;
            }
        }
    }
});

/**
 * /blog/ 페이지네이션 canonical redirect 우회
 *
 * /blog/는 Page 타입(slug=blog → page-blog.php)에서 커스텀 WP_Query로 글을 렌더링한다.
 * Static page에서 /page/N/ 진입 시 WP가 canonical redirect로 /blog/로 떨어뜨려
 * paginate_links가 무한히 1페이지로 회귀하는 알려진 이슈를 우회한다.
 */
add_filter('redirect_canonical', 'uw_blog_allow_page_pagination', 10, 2);
function uw_blog_allow_page_pagination($redirect_url, $requested_url)
{
    if (is_page('blog') && preg_match('#/page/\d+/?$#', $requested_url)) {
        return false;
    }
    return $redirect_url;
}

/**
 * SMTP Mail Configuration
 * wp-config.php에 SMTP 상수가 정의되어 있을 때만 동작
 */
add_action('phpmailer_init', function ($phpmailer) {
    if (!defined('SMTP_HOST') || !defined('SMTP_USERNAME')) {
        return;
    }
    $phpmailer->isSMTP();
    $phpmailer->Host       = SMTP_HOST;
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = SMTP_PORT;
    $phpmailer->SMTPSecure = SMTP_SECURE;
    $phpmailer->Username   = SMTP_USERNAME;
    $phpmailer->Password   = SMTP_PASSWORD;
    $phpmailer->From       = SMTP_USERNAME;
    $phpmailer->FromName   = get_bloginfo('name');
});
