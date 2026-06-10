<?php
/**
 * UW Maintenance Utilities
 *
 * 메인페이지 유지보수 섹션에서 사용하는 정적 유틸 모음
 * (상태 라벨/클래스, 회사명 마스킹, 날짜 포맷, 컬럼 분할)
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
        return 'main-maintenance-badge-receiving';
      case 'completed':
      case '수정완료':
        return 'main-maintenance-badge-completed';
      default:
        return 'main-maintenance-badge-ongoing';
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
