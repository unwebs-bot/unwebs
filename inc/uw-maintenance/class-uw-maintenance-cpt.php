<?php
/**
 * UW Maintenance CPT Registration
 *
 * 유지보수 현황 Custom Post Type
 *
 * @package starter-theme
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit;
}

class UW_Maintenance_CPT
{

  /**
   * Instance
   */
  private static $instance = null;

  /**
   * KPI Options key
   */
  const KPI_OPTION_KEY = 'uw_maintenance_kpi_options';

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
  private function __construct()
  {
    add_action('init', array($this, 'register_post_type'));
  }

  /**
   * Register CPT: uw_maintenance
   */
  public function register_post_type()
  {
    $labels = array(
      'name' => '유지보수 현황',
      'singular_name' => '유지보수',
      'menu_name' => '유지보수',
      'add_new' => '새 항목 추가',
      'add_new_item' => '새 유지보수 항목 추가',
      'edit_item' => '항목 수정',
      'new_item' => '새 항목',
      'view_item' => '항목 보기',
      'search_items' => '항목 검색',
      'not_found' => '항목이 없습니다',
      'not_found_in_trash' => '휴지통에 항목이 없습니다',
    );

    $args = array(
      'labels' => $labels,
      'public' => false,
      'publicly_queryable' => false,
      'show_ui' => false,
      'show_in_menu' => false,
      'query_var' => false,
      'rewrite' => false,
      'capability_type' => 'post',
      'has_archive' => false,
      'hierarchical' => false,
      'supports' => array('title'),
      'show_in_rest' => false,
    );

    register_post_type('uw_maintenance', $args);
  }

  /**
   * Get available maintenance types
   */
  public static function get_types()
  {
    return array(
      'design' => '디자인',
      'publishing' => '퍼블리싱',
      'development' => '개발',
      'etc' => '기타',
    );
  }

  /**
   * Get available statuses
   */
  public static function get_statuses()
  {
    return array(
      'receiving' => '접수중',
      'ongoing' => '진행중',
      'completed' => '수정완료',
    );
  }

  /**
   * Get status CSS class
   */
  public static function get_status_class($status)
  {
    switch ($status) {
      case 'receiving':
      case '접수중':
        return 'uw-maintenance__badge--receiving';
      case 'completed':
      case '수정완료':
        return 'uw-maintenance__badge--completed';
      default:
        return 'uw-maintenance__badge--ongoing';
    }
  }

  /**
   * Get status label
   */
  public static function get_status_label($status)
  {
    $statuses = self::get_statuses();
    return isset($statuses[$status]) ? $statuses[$status] : $status;
  }

  /**
   * Get type label
   */
  public static function get_type_label($type)
  {
    $types = self::get_types();
    return isset($types[$type]) ? $types[$type] : $type;
  }

  /**
   * Mask company name for privacy
   * "(주)측량지적설계" -> "(주) 측******"
   * "한국개발" -> "한******"
   */
  public static function mask_company_name($name)
  {
    if (empty($name)) {
      return '';
    }

    // Extract prefix like (주), (유), (사), (재)
    if (preg_match('/^(\([^)]+\))\s*(.+)$/', $name, $matches)) {
      $prefix = $matches[1];
      $rest = trim($matches[2]);
      $first_char = mb_substr($rest, 0, 1, 'UTF-8');
      return $prefix . ' ' . $first_char . str_repeat('*', 6);
    }

    // No prefix - just mask after first character
    $first_char = mb_substr($name, 0, 1, 'UTF-8');
    return $first_char . str_repeat('*', 6);
  }

  /**
   * Get KPI options with defaults
   */
  public static function get_kpi_options()
  {
    $defaults = array(
      'today_completed' => 0,
      'monthly_completed' => 0,
      'one_day_rate' => 0.0,
      'free_rate' => 0.0,
      'status_link' => '',
    );

    $options = get_option(self::KPI_OPTION_KEY, array());
    return wp_parse_args($options, $defaults);
  }

  /**
   * Get maintenance entries for ticker display
   *
   * @param int $limit Number of entries to retrieve
   * @return array Array of maintenance entry data
   */
  public static function get_ticker_entries($limit = 20)
  {
    $posts = get_posts(array(
      'post_type' => 'uw_maintenance',
      'posts_per_page' => $limit,
      'post_status' => 'publish',
      'meta_key' => '_uw_maintenance_date',
      'orderby' => 'meta_value',
      'order' => 'DESC',
    ));

    $entries = array();
    foreach ($posts as $post) {
      $entries[] = array(
        'id' => $post->ID,
        'company' => get_post_meta($post->ID, '_uw_maintenance_company', true),
        'date' => get_post_meta($post->ID, '_uw_maintenance_date', true),
        'type' => get_post_meta($post->ID, '_uw_maintenance_type', true),
        'status' => get_post_meta($post->ID, '_uw_maintenance_status', true),
      );
    }

    return $entries;
  }

  /**
   * Split entries into columns for ticker display
   *
   * @param array $entries Array of entries
   * @param int $columns Number of columns (default 2)
   * @return array Array of column arrays
   */
  public static function split_for_columns($entries, $columns = 2)
  {
    if (empty($entries)) {
      return array_fill(0, $columns, array());
    }

    $total = count($entries);
    $per_column = ceil($total / $columns);
    $result = array();

    for ($i = 0; $i < $columns; $i++) {
      $result[] = array_slice($entries, $i * $per_column, $per_column);
    }

    return $result;
  }

  /**
   * Format date for display
   * "2025-01-15" -> "2025.01.15"
   */
  public static function format_date($date)
  {
    if (empty($date)) {
      return '';
    }
    return str_replace('-', '.', $date);
  }
}

// Initialize
UW_Maintenance_CPT::get_instance();
