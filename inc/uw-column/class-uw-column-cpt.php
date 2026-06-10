<?php
/**
 * UW Column CPT — 전문 칼럼 게시판
 *
 * Gutenberg + Rank Math 호환을 위해 WP 기본 관리자 UI 사용.
 * rewrite slug / taxonomy 설계는 SEO-전문칼럼-게시판.md 기반.
 *
 * @package Unwebs
 */

if (!defined('ABSPATH')) exit;

class UW_Column_CPT
{
    const POST_TYPE        = 'column';
    const TAX_CATEGORY     = 'column_category';
    const TAX_TAG          = 'column_tag';
    const REWRITE_SLUG     = 'column'; // 한글 URL 호환 이슈로 영문 슬러그 채택
    const FLUSH_OPTION_KEY = 'uw_column_rewrite_flushed_v3_hidden_tax';

    private static $instance = null;

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('init', array($this, 'maybe_seed_categories'), 95);
        add_action('init', array($this, 'maybe_flush_rewrite'), 99);
    }

    /**
     * 읽는 시간 계산 (한국어 기준 — 분당 500자)
     *
     * @param int $post_id
     * @return int 분 (최소 1)
     */
    public static function reading_time($post_id)
    {
        $content = get_post_field('post_content', $post_id);
        $text    = wp_strip_all_tags($content);
        $count   = mb_strlen(preg_replace('/\s+/u', '', $text), 'UTF-8');
        return max(1, (int) ceil($count / 500));
    }

    /**
     * 초기 카테고리 4종 자동 생성 (1회성)
     */
    public function maybe_seed_categories()
    {
        if (get_option('uw_column_cat_seed_v1') === 'done') return;
        if (!taxonomy_exists(self::TAX_CATEGORY)) return;

        $defaults = array(
            'homepage-production' => '홈페이지 제작',
            'seo-aeo'             => 'SEO·AEO',
            'operation'           => '운영·관리',
            'design-planning'     => '디자인·기획',
        );

        foreach ($defaults as $slug => $name) {
            if (term_exists($name, self::TAX_CATEGORY) || term_exists($slug, self::TAX_CATEGORY)) continue;
            wp_insert_term($name, self::TAX_CATEGORY, array('slug' => $slug));
        }

        update_option('uw_column_cat_seed_v1', 'done');
    }

    /**
     * 전문 칼럼 CPT 등록
     */
    public function register_post_type()
    {
        $labels = array(
            'name'               => '전문 칼럼',
            'singular_name'      => '전문 칼럼',
            'menu_name'          => '전문 칼럼',
            'name_admin_bar'     => '전문 칼럼',
            'add_new'            => '새 칼럼 추가',
            'add_new_item'       => '새 칼럼 글 추가',
            'edit_item'          => '칼럼 편집',
            'new_item'           => '새 칼럼',
            'view_item'          => '칼럼 보기',
            'view_items'         => '칼럼 목록',
            'search_items'       => '칼럼 검색',
            'not_found'          => '등록된 칼럼이 없습니다.',
            'not_found_in_trash' => '휴지통에 칼럼이 없습니다.',
            'all_items'          => '모든 칼럼',
            'archives'           => '칼럼 아카이브',
            'attributes'         => '칼럼 속성',
            'insert_into_item'   => '칼럼에 삽입',
            'featured_image'     => '대표 이미지',
            'set_featured_image' => '대표 이미지 설정',
        );

        // 2026-05-20 — '전문 칼럼' → 블로그(post) 통합. CPT는 데이터 보존 + admin/프론트 모두 숨김.
        // 구 데이터 5건은 post로 이전됨. 신규 column 글 등록은 불가.
        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => false,
            'show_in_menu'        => false,
            'show_in_nav_menus'   => false,
            'show_in_admin_bar'   => false,
            'show_in_rest'        => false,
            'capability_type'     => 'post',
            'has_archive'         => false,
            'hierarchical'        => false,
            'supports'            => array('title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions', 'custom-fields'),
            'rewrite'             => false,
            'query_var'           => false,
        );

        register_post_type(self::POST_TYPE, $args);
    }

    /**
     * 칼럼 전용 taxonomy (카테고리 · 태그)
     */
    public function register_taxonomies()
    {
        // 2026-05-20 — column → post 통합 후 taxonomy 전면 비공개.
        // term relationship이 끊겨 thin content가 인덱싱되는 것 방지.
        $hidden = array(
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => false,
            'show_admin_column'  => false,
            'show_in_rest'       => false,
            'show_in_nav_menus'  => false,
            'rewrite'            => false,
            'query_var'          => false,
        );

        // 카테고리
        register_taxonomy(self::TAX_CATEGORY, self::POST_TYPE, array_merge(array(
            'labels'       => array('name' => '칼럼 카테고리'),
            'hierarchical' => true,
        ), $hidden));

        // 태그
        register_taxonomy(self::TAX_TAG, self::POST_TYPE, array_merge(array(
            'labels'       => array('name' => '칼럼 태그'),
            'hierarchical' => false,
        ), $hidden));
    }

    /**
     * CPT·taxonomy 등록 후 1회성 rewrite flush
     * register 훅 이후에 실행되어야 하므로 priority 99
     */
    public function maybe_flush_rewrite()
    {
        if (get_option(self::FLUSH_OPTION_KEY) === 'done') return;
        flush_rewrite_rules(false);
        update_option(self::FLUSH_OPTION_KEY, 'done');
    }
}

UW_Column_CPT::get_instance();
