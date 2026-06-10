<?php
/**
 * Blog Settings — Customizer + WP admin 편의 + 운영 헬퍼
 *
 * - 외관 > 사용자 정의하기 > "블로그" 섹션
 *     - 인덱스 sub-텍스트
 *     - 인덱스 메인 타이틀
 *     - 페이지당 게시글 수
 * - 옵션 값을 home/archive/page-blog가 읽어 출력
 * - WP admin 글 목록에 썸네일 컬럼 추가
 * - 글 등록 시 대표 이미지·카테고리 누락 admin notice
 */

if (!defined('ABSPATH')) exit;

/* ==========================================================================
   WP admin '글' → '블로그' 라벨 변경 + 메뉴 아이콘
   ========================================================================== */

add_filter('post_type_labels_post', 'uw_blog_relabel_post_type');
function uw_blog_relabel_post_type($labels)
{
    $labels->name               = '블로그';
    $labels->singular_name      = '블로그 글';
    $labels->menu_name          = '블로그';
    $labels->name_admin_bar     = '블로그 글';
    $labels->add_new            = '새 글 추가';
    $labels->add_new_item       = '새 블로그 글 추가';
    $labels->edit_item          = '블로그 글 편집';
    $labels->new_item           = '새 블로그 글';
    $labels->view_item          = '블로그 글 보기';
    $labels->view_items         = '블로그 목록';
    $labels->search_items       = '블로그 검색';
    $labels->not_found          = '등록된 블로그 글이 없습니다.';
    $labels->not_found_in_trash = '휴지통에 블로그 글이 없습니다.';
    $labels->all_items          = '모든 글';
    $labels->archives           = '블로그 아카이브';
    return $labels;
}

add_action('admin_menu', 'uw_blog_admin_menu_icon', 999);
function uw_blog_admin_menu_icon()
{
    global $menu;
    if (!is_array($menu)) return;
    foreach ($menu as $i => $item) {
        if (isset($item[2]) && $item[2] === 'edit.php') {
            // 아이콘 + 위치 보정 (Post 기본 = edit-post, 블로그스럽게 'admin-post' 유지하면서 라벨만 강조)
            $menu[$i][6] = 'dashicons-admin-post';
        }
    }
}


/* ==========================================================================
   기본값 & 옵션 헬퍼
   ========================================================================== */

if (!function_exists('uw_blog_default_settings')) {
    function uw_blog_default_settings() {
        return array(
            'sub_text'       => '홈페이지 제작부터 SEO·AEO에 대한 인사이트를 확인해 보세요.',
            'main_title'     => '블로그',
            'posts_per_page' => 8,
        );
    }
}

/**
 * 단일 옵션 조회 (Customizer → DB → 기본값)
 *
 * @param string $key  sub_text | main_title | posts_per_page
 * @return mixed
 */
function uw_blog_get_setting($key)
{
    $defaults = uw_blog_default_settings();
    if (!array_key_exists($key, $defaults)) return null;

    $val = get_theme_mod('uw_blog_' . $key, $defaults[$key]);

    if ($key === 'posts_per_page') {
        $val = max(1, min(48, (int) $val));
    }
    return $val;
}


/* ==========================================================================
   Customizer 등록
   ========================================================================== */

add_action('customize_register', 'uw_blog_customize_register');
function uw_blog_customize_register($wp_customize)
{
    $defaults = uw_blog_default_settings();

    $wp_customize->add_section('uw_blog_section', array(
        'title'       => __('블로그', 'unwebs'),
        'description' => __('블로그 인덱스(/blog/) 상단 카피와 페이지당 게시글 수를 설정합니다.', 'unwebs'),
        'priority'    => 120,
    ));

    // sub-텍스트 (파란 작은 글씨)
    $wp_customize->add_setting('uw_blog_sub_text', array(
        'default'           => $defaults['sub_text'],
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control('uw_blog_sub_text', array(
        'label'       => '서브 텍스트 (sub-tit · 파란색)',
        'section'     => 'uw_blog_section',
        'type'        => 'text',
        'description' => '인덱스 상단 한 줄 카피.',
    ));

    // 메인 타이틀
    $wp_customize->add_setting('uw_blog_main_title', array(
        'default'           => $defaults['main_title'],
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control('uw_blog_main_title', array(
        'label'   => '메인 타이틀',
        'section' => 'uw_blog_section',
        'type'    => 'text',
    ));

    // 페이지당 게시글 수
    $wp_customize->add_setting('uw_blog_posts_per_page', array(
        'default'           => $defaults['posts_per_page'],
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control('uw_blog_posts_per_page', array(
        'label'       => '페이지당 게시글 수',
        'section'     => 'uw_blog_section',
        'type'        => 'number',
        'input_attrs' => array('min' => 1, 'max' => 48, 'step' => 1),
        'description' => '1 ~ 48 사이.',
    ));
}


/* ==========================================================================
   메인 쿼리 — posts_per_page 옵션 반영 (블로그 인덱스 / 카테고리 / 검색)
   ========================================================================== */

add_action('pre_get_posts', 'uw_blog_set_posts_per_page');
function uw_blog_set_posts_per_page($query)
{
    if (is_admin() || !$query->is_main_query()) return;

    if ($query->is_home() || $query->is_category() || $query->is_search() || $query->is_tag()) {
        $query->set('posts_per_page', uw_blog_get_setting('posts_per_page'));
    }
}


/* ==========================================================================
   WP admin 글 목록 — 썸네일 컬럼
   ========================================================================== */

add_filter('manage_posts_columns', 'uw_blog_admin_thumb_column');
function uw_blog_admin_thumb_column($columns)
{
    $new = array();
    foreach ($columns as $key => $label) {
        if ($key === 'title') {
            $new['uw_thumb'] = '대표 이미지';
        }
        $new[$key] = $label;
    }
    return $new;
}

add_action('manage_posts_custom_column', 'uw_blog_admin_thumb_column_value', 10, 2);
function uw_blog_admin_thumb_column_value($column, $post_id)
{
    if ($column !== 'uw_thumb') return;

    if (has_post_thumbnail($post_id)) {
        echo get_the_post_thumbnail($post_id, array(80, 50), array(
            'style' => 'width:80px;height:50px;object-fit:cover;border-radius:4px;display:block;',
            'alt'   => '',
        ));
    } else {
        echo '<span style="display:inline-block;width:80px;height:50px;background:#f3f4f6;border-radius:4px;color:#9aa1ab;font-size:11px;line-height:50px;text-align:center;">없음</span>';
    }
}


/* ==========================================================================
   글 저장 시 누락 항목 admin notice (대표 이미지 / 카테고리)
   ========================================================================== */

add_action('admin_notices', 'uw_blog_missing_fields_notice');
function uw_blog_missing_fields_notice()
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->base !== 'post' || $screen->post_type !== 'post') return;

    global $post;
    if (!$post || $post->post_status === 'auto-draft') return;

    $issues = array();
    if (!has_post_thumbnail($post->ID)) {
        $issues[] = '대표 이미지가 없습니다 (블로그 카드 썸네일에 사용됩니다)';
    }
    $cats = wp_get_post_categories($post->ID);
    if (empty($cats) || (count($cats) === 1 && (int) $cats[0] === 1 /* Uncategorized */)) {
        $issues[] = '카테고리가 지정되지 않았습니다 (또는 Uncategorized)';
    }

    if (empty($issues)) return;
    echo '<div class="notice notice-warning"><p><strong>블로그 카드 표시 누락:</strong></p><ul style="list-style:disc;margin-left:20px;">';
    foreach ($issues as $msg) {
        echo '<li>' . esc_html($msg) . '</li>';
    }
    echo '</ul></div>';
}


/* ==========================================================================
   Uncategorized 카테고리 숨김 옵션 (사이드바에서 자동 제외 중)
   admin에서도 기본 카테고리 변경하지 않으면 신규 글이 Uncategorized에 들어감
   → 기본 카테고리를 "홈페이지 제작"으로 자동 변경 (term 존재 시 1회만)
   ========================================================================== */

add_action('init', 'uw_blog_set_default_category');
function uw_blog_set_default_category()
{
    if (get_option('uw_blog_default_cat_set')) return;

    $term = get_term_by('slug', 'production', 'category');
    if ($term && !is_wp_error($term)) {
        update_option('default_category', (int) $term->term_id);
        update_option('uw_blog_default_cat_set', 1);
    }
}


/* ==========================================================================
   검색결과 noindex 강제 (WP wp_robots + Rank Math 양방향)
   Rank Math UI 분담 원칙이지만, 설정 누락 시 안전망. 검색결과 thin content 차단.
   ========================================================================== */

add_filter('wp_robots', 'uw_blog_force_search_noindex');
function uw_blog_force_search_noindex($robots)
{
    if (is_search()) {
        $robots['noindex']  = true;
        $robots['follow']   = true;
        unset($robots['index']);
    }
    return $robots;
}

add_filter('rank_math/frontend/robots', 'uw_blog_rank_math_search_noindex');
function uw_blog_rank_math_search_noindex($robots)
{
    if (is_search()) {
        $robots['index']  = 'noindex';
        $robots['follow'] = 'follow';
    }
    return $robots;
}


/* ==========================================================================
   OG 이미지 fallback — 글 대표 이미지 없을 때 사이트 공용 OG 이미지
   Rank Math가 og:image를 출력하지 않거나 빈 값일 때 보강.
   ========================================================================== */

add_filter('rank_math/opengraph/facebook/image', 'uw_blog_og_image_fallback');
add_filter('rank_math/opengraph/twitter/image', 'uw_blog_og_image_fallback');
function uw_blog_og_image_fallback($image)
{
    if (!empty($image)) return $image;
    return get_theme_file_uri('/assets/images/common/ogimage.png');
}
