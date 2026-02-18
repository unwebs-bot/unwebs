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
    wp_enqueue_style('xeicon', 'https://cdn.jsdelivr.net/gh/xpressengine/XEIcon@2.3.3/xeicon.min.css', array(), null);

    // Theme CSS (자동 버전 관리)
    wp_enqueue_style('theme-style', get_stylesheet_uri(), array(), starter_get_version('/style.css'));
    wp_enqueue_style('main-style', get_theme_file_uri('/assets/css/style.css'), array('theme-style'), starter_get_version('/assets/css/style.css'));

    // JS (자동 버전 관리)
    wp_enqueue_script('header', get_theme_file_uri('/assets/js/header.js'), array(), starter_get_version('/assets/js/header.js'), true);
    wp_enqueue_script('footer', get_theme_file_uri('/assets/js/footer.js'), array(), starter_get_version('/assets/js/footer.js'), true);
    wp_enqueue_script('main', get_theme_file_uri('/assets/js/main.js'), array('header', 'footer'), starter_get_version('/assets/js/main.js'), true);
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

require_once get_template_directory() . '/inc/uw-gallery/class-uw-gallery-cpt.php';
require_once get_template_directory() . '/inc/uw-gallery/class-uw-gallery-admin.php';
require_once get_template_directory() . '/inc/uw-gallery/class-uw-gallery-engine.php';

require_once get_template_directory() . '/inc/uw-maintenance/class-uw-maintenance-cpt.php';
require_once get_template_directory() . '/inc/uw-maintenance/class-uw-maintenance-admin.php';

/**
 * Banner Settings
 */
require_once get_template_directory() . '/inc/uw-banner-settings.php';

/**
 * Dashboard Settings
 */
require_once get_template_directory() . '/inc/uw-dashboard-settings.php';
require_once get_template_directory() . '/inc/uw-dashboard-seed.php';

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
