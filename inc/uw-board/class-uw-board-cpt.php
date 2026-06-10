<?php
/**
 * UW Board CPT & Taxonomy Registration
 * 
 * @package starter-theme
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit;
}

class UW_Board_CPT
{

  /**
   * Instance
   */
  private static $instance = null;

  /**
   * Get instance
   */
  public static function get_instance()
  {
    if (null === self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Constructor
   */
  /**
   * 공개 게시판 슬러그 — 프론트 URL/permalink 활성화 대상
   * faq는 page-faq.php(고정 페이지)로 대체되어 제외
   */
  const PUBLIC_SLUGS = array('notice');

  private function __construct()
  {
    add_action('init', array($this, 'register_post_type'));
    add_action('init', array($this, 'register_taxonomy'));
    add_action('init', array($this, 'register_rewrite'), 20);
    add_filter('post_type_link', array($this, 'filter_post_type_link'), 10, 2);
  }

  /**
   * /{slug}/ → uw_board_type archive
   * /{slug}/page/N/ → paged
   * /{slug}/{post_name}/ → single uw_board
   *
   * 공개 슬러그(notice, faq)에 대해서만 등록.
   */
  public function register_rewrite()
  {
    foreach (self::PUBLIC_SLUGS as $slug) {
      $s = preg_quote($slug, '/');
      add_rewrite_rule(
        '^' . $s . '/page/([0-9]+)/?$',
        'index.php?uw_board_type=' . $slug . '&paged=$matches[1]',
        'top'
      );
      add_rewrite_rule(
        '^' . $s . '/([^/]+)/?$',
        'index.php?post_type=uw_board&name=$matches[1]&uw_board_type=' . $slug,
        'top'
      );
      add_rewrite_rule(
        '^' . $s . '/?$',
        'index.php?uw_board_type=' . $slug,
        'top'
      );
    }
  }

  /**
   * uw_board permalink을 /{type}/{post_name}/ 으로 변환
   */
  public function filter_post_type_link($post_link, $post)
  {
    if (empty($post) || $post->post_type !== 'uw_board') {
      return $post_link;
    }

    $terms = get_the_terms($post->ID, 'uw_board_type');
    if (empty($terms) || is_wp_error($terms)) {
      return $post_link;
    }

    $type_slug = $terms[0]->slug;
    if (!in_array($type_slug, self::PUBLIC_SLUGS, true)) {
      return $post_link;
    }

    return home_url('/' . $type_slug . '/' . $post->post_name . '/');
  }

  /**
   * Register CPT: uw_board
   */
  public function register_post_type()
  {
    $labels = array(
      'name' => '게시판 글',
      'singular_name' => '게시판 글',
      'menu_name' => '게시판',
      'add_new' => '새 글 작성',
      'add_new_item' => '새 글 작성',
      'edit_item' => '글 수정',
      'new_item' => '새 글',
      'view_item' => '글 보기',
      'search_items' => '글 검색',
      'not_found' => '글이 없습니다',
      'not_found_in_trash' => '휴지통에 글이 없습니다',
    );

    $args = array(
      'labels' => $labels,
      'public' => true,
      'publicly_queryable' => true,
      'show_ui' => false,  // 커스텀 관리자 UI 사용
      'show_in_menu' => false,
      'query_var' => true,
      // 직접 rewrite rule로 /{type}/{slug}/ 라우팅 (UW_Board_CPT::register_rewrite)
      'rewrite' => false,
      'capability_type' => 'post',
      'has_archive' => false, // taxonomy archive(/notice/, /faq/)가 목록 역할
      'hierarchical' => false,
      'supports' => array('title', 'editor', 'author', 'thumbnail'),
      'show_in_rest' => false,
      'exclude_from_search' => false,
    );

    register_post_type('uw_board', $args);
  }

  /**
   * 게시글 작성자 표시명 가져오기 (공통 헬퍼)
   *
   * @param int|WP_Post $post Post ID or WP_Post object
   * @return string 작성자 표시명
   */
  public static function get_author_display_name($post)
  {
    if (is_numeric($post)) {
      $post_id = $post;
      $post_author = get_post_field('post_author', $post_id);
    } else {
      $post_id = $post->ID;
      $post_author = $post->post_author;
    }

    // 1. 비회원 작성자명 확인
    $guest_name = get_post_meta($post_id, '_uw_guest_name', true);
    if ($guest_name) {
      return esc_html($guest_name);
    }

    // 2. 관리자 확인
    if ($post_author && user_can($post_author, 'manage_options')) {
      return '관리자';
    }

    // 3. 일반 회원
    if ($post_author) {
      $display_name = get_the_author_meta('display_name', $post_author);
      if ($display_name) {
        return esc_html($display_name);
      }
    }

    // 4. 기본값 (작성자 정보 없음)
    return '관리자';
  }

  /**
   * Register Taxonomy: uw_board_type
   */
  public function register_taxonomy()
  {
    $labels = array(
      'name' => '게시판 유형',
      'singular_name' => '게시판 유형',
      'search_items' => '게시판 검색',
      'all_items' => '모든 게시판',
      'edit_item' => '게시판 수정',
      'update_item' => '게시판 업데이트',
      'add_new_item' => '새 게시판 추가',
      'new_item_name' => '새 게시판 이름',
      'menu_name' => '게시판 유형',
    );

    $args = array(
      'hierarchical' => false,
      'labels' => $labels,
      'public' => true,
      'publicly_queryable' => true,
      'show_ui' => false,
      'show_admin_column' => false,
      'query_var' => 'uw_board_type',
      // 직접 rewrite rule로 /{type}/ 라우팅 (UW_Board_CPT::register_rewrite)
      'rewrite' => false,
      'show_in_rest' => false,
    );

    register_taxonomy('uw_board_type', 'uw_board', $args);
  }
}

// Initialize
UW_Board_CPT::get_instance();
