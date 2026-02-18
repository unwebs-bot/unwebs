<?php
/**
 * UW Maintenance Admin
 *
 * 유지보수 현황 관리자 인터페이스
 *
 * @package starter-theme
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit;
}

class UW_Maintenance_Admin
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
  private function __construct()
  {
    add_action('admin_menu', array($this, 'add_admin_menu'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

    // AJAX handlers
    add_action('wp_ajax_uw_maintenance_save_entry', array($this, 'ajax_save_entry'));
    add_action('wp_ajax_uw_maintenance_delete_entry', array($this, 'ajax_delete_entry'));
    add_action('wp_ajax_uw_maintenance_save_kpi', array($this, 'ajax_save_kpi'));
  }

  /**
   * Add admin menu
   */
  public function add_admin_menu()
  {
    add_menu_page(
      '유지보수 현황',
      '유지보수 현황',
      'manage_options',
      'uw-maintenance',
      array($this, 'render_list_page'),
      'dashicons-admin-tools',
      28
    );

    add_submenu_page(
      'uw-maintenance',
      '유지보수 관리',
      '유지보수 관리',
      'manage_options',
      'uw-maintenance',
      array($this, 'render_list_page')
    );

    add_submenu_page(
      'uw-maintenance',
      '새 항목 추가',
      '새 항목 추가',
      'manage_options',
      'uw-maintenance-add',
      array($this, 'render_edit_page')
    );

    add_submenu_page(
      'uw-maintenance',
      'KPI 설정',
      'KPI 설정',
      'manage_options',
      'uw-maintenance-kpi',
      array($this, 'render_kpi_page')
    );
  }

  /**
   * Enqueue admin assets
   */
  public function enqueue_admin_assets($hook)
  {
    if (strpos($hook, 'uw-maintenance') === false) {
      return;
    }

    wp_enqueue_style(
      'uw-maintenance-admin',
      get_theme_file_uri('/assets/css/cpt/maintenance/admin.css'),
      array(),
      '1.0.0'
    );

    wp_enqueue_script(
      'uw-maintenance-admin',
      get_theme_file_uri('/assets/js/CPT/maintenance/uw-maintenance-admin.js'),
      array('jquery'),
      '1.0.0',
      true
    );

    wp_localize_script('uw-maintenance-admin', 'uwMaintenanceAdmin', array(
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('uw_maintenance_admin_nonce'),
      'types' => UW_Maintenance_CPT::get_types(),
      'statuses' => UW_Maintenance_CPT::get_statuses(),
    ));
  }

  /**
   * Render list page
   */
  public function render_list_page()
  {
    // Handle edit mode
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
      $this->render_edit_page();
      return;
    }

    $entries = $this->get_all_entries();
    $types = UW_Maintenance_CPT::get_types();
    $statuses = UW_Maintenance_CPT::get_statuses();
?>
    <div class="wrap uw-maint-wrap">
      <h1 class="wp-heading-inline">유지보수 현황 관리</h1>
      <a href="<?php echo admin_url('admin.php?page=uw-maintenance-add'); ?>" class="page-title-action">새 항목 추가</a>
      <hr class="wp-header-end">

      <?php if (empty($entries)) : ?>
        <div class="uw-maint-empty">
          <p>등록된 유지보수 항목이 없습니다.</p>
          <a href="<?php echo admin_url('admin.php?page=uw-maintenance-add'); ?>" class="button button-primary">첫 번째 항목 추가</a>
        </div>
      <?php else : ?>
        <table class="wp-list-table widefat fixed striped uw-maint-table">
          <thead>
            <tr>
              <th scope="col" class="column-company">회사명</th>
              <th scope="col" class="column-date">접수일</th>
              <th scope="col" class="column-type">유형</th>
              <th scope="col" class="column-status">상태</th>
              <th scope="col" class="column-actions">관리</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($entries as $entry) : ?>
              <tr data-id="<?php echo esc_attr($entry['id']); ?>">
                <td class="column-company">
                  <strong><?php echo esc_html($entry['company']); ?></strong>
                </td>
                <td class="column-date"><?php echo esc_html($entry['date']); ?></td>
                <td class="column-type">
                  <?php echo esc_html(isset($types[$entry['type']]) ? $types[$entry['type']] : $entry['type']); ?>
                </td>
                <td class="column-status">
                  <span class="uw-maint-status uw-maint-status--<?php echo esc_attr($entry['status']); ?>">
                    <?php echo esc_html(isset($statuses[$entry['status']]) ? $statuses[$entry['status']] : $entry['status']); ?>
                  </span>
                </td>
                <td class="column-actions">
                  <a href="<?php echo admin_url('admin.php?page=uw-maintenance&action=edit&id=' . $entry['id']); ?>" class="button button-small">수정</a>
                  <button type="button" class="button button-small button-link-delete uw-maint-delete" data-id="<?php echo esc_attr($entry['id']); ?>">삭제</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  <?php
  }

  /**
   * Render edit page
   */
  public function render_edit_page()
  {
    $entry_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
    $is_edit = $entry_id > 0;

    $entry = array(
      'company' => '',
      'date' => date('Y-m-d'),
      'type' => 'design',
      'status' => 'receiving',
    );

    if ($is_edit) {
      $post = get_post($entry_id);
      if ($post && $post->post_type === 'uw_maintenance') {
        $entry = array(
          'company' => get_post_meta($entry_id, '_uw_maintenance_company', true),
          'date' => get_post_meta($entry_id, '_uw_maintenance_date', true),
          'type' => get_post_meta($entry_id, '_uw_maintenance_type', true),
          'status' => get_post_meta($entry_id, '_uw_maintenance_status', true),
        );
      }
    }

    $types = UW_Maintenance_CPT::get_types();
    $statuses = UW_Maintenance_CPT::get_statuses();
  ?>
    <div class="wrap uw-maint-wrap">
      <h1><?php echo $is_edit ? '유지보수 항목 수정' : '새 유지보수 항목 추가'; ?></h1>

      <form id="uw-maintenance-form" class="uw-maint-form" data-entry-id="<?php echo esc_attr($entry_id); ?>">
        <table class="form-table">
          <tr>
            <th scope="row"><label for="company">회사명 <span class="required">*</span></label></th>
            <td>
              <input type="text" id="company" name="company" class="regular-text" value="<?php echo esc_attr($entry['company']); ?>" required>
              <p class="description">회사명을 입력하세요. 프론트엔드에서는 자동으로 마스킹됩니다.</p>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="date">접수일 <span class="required">*</span></label></th>
            <td>
              <input type="date" id="date" name="date" value="<?php echo esc_attr($entry['date']); ?>" required>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="type">유형</label></th>
            <td>
              <select id="type" name="type">
                <?php foreach ($types as $key => $label) : ?>
                  <option value="<?php echo esc_attr($key); ?>" <?php selected($entry['type'], $key); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
              </select>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="status">상태</label></th>
            <td>
              <select id="status" name="status">
                <?php foreach ($statuses as $key => $label) : ?>
                  <option value="<?php echo esc_attr($key); ?>" <?php selected($entry['status'], $key); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
              </select>
            </td>
          </tr>
        </table>

        <p class="submit">
          <button type="submit" class="button button-primary"><?php echo $is_edit ? '수정하기' : '추가하기'; ?></button>
          <a href="<?php echo admin_url('admin.php?page=uw-maintenance'); ?>" class="button">취소</a>
        </p>
      </form>
    </div>
  <?php
  }

  /**
   * Render KPI settings page
   */
  public function render_kpi_page()
  {
    $kpi = UW_Maintenance_CPT::get_kpi_options();
  ?>
    <div class="wrap uw-maint-wrap">
      <h1>KPI 설정</h1>
      <p class="description">유지보수 서비스 현황 페이지에 표시될 KPI 값을 설정합니다.</p>

      <form id="uw-maintenance-kpi-form" class="uw-maint-form">
        <div class="uw-maint-kpi-grid">
          <div class="uw-maint-kpi-card">
            <label for="today_completed">금일 유지보수 수정완료</label>
            <div class="uw-maint-kpi-input">
              <input type="number" id="today_completed" name="today_completed" value="<?php echo esc_attr($kpi['today_completed']); ?>" min="0">
              <span class="unit">건</span>
            </div>
          </div>

          <div class="uw-maint-kpi-card">
            <label for="monthly_completed">최근 30일 수정완료</label>
            <div class="uw-maint-kpi-input">
              <input type="number" id="monthly_completed" name="monthly_completed" value="<?php echo esc_attr($kpi['monthly_completed']); ?>" min="0">
              <span class="unit">건</span>
            </div>
          </div>

          <div class="uw-maint-kpi-card">
            <label for="one_day_rate">1일 이내 처리완료</label>
            <div class="uw-maint-kpi-input">
              <input type="number" id="one_day_rate" name="one_day_rate" value="<?php echo esc_attr($kpi['one_day_rate']); ?>" min="0" max="100" step="0.1">
              <span class="unit">%</span>
            </div>
          </div>

          <div class="uw-maint-kpi-card">
            <label for="free_rate">무상 유지보수 비율</label>
            <div class="uw-maint-kpi-input">
              <input type="number" id="free_rate" name="free_rate" value="<?php echo esc_attr($kpi['free_rate']); ?>" min="0" max="100" step="0.1">
              <span class="unit">%</span>
            </div>
          </div>
        </div>

        <table class="form-table" style="margin-top: 30px;">
          <tr>
            <th scope="row"><label for="status_link">실시간 현황보기 URL</label></th>
            <td>
              <input type="url" id="status_link" name="status_link" class="regular-text" value="<?php echo esc_url($kpi['status_link']); ?>" placeholder="https://">
              <p class="description">'실시간 현황보기' 버튼 클릭 시 이동할 URL을 입력하세요. (선택사항)</p>
            </td>
          </tr>
        </table>

        <p class="submit">
          <button type="submit" class="button button-primary">설정 저장</button>
        </p>
      </form>
    </div>
<?php
  }

  /**
   * Get all maintenance entries
   */
  private function get_all_entries()
  {
    $posts = get_posts(array(
      'post_type' => 'uw_maintenance',
      'posts_per_page' => -1,
      'post_status' => 'any',
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
   * AJAX: Save entry
   */
  public function ajax_save_entry()
  {
    check_ajax_referer('uw_maintenance_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error('권한이 없습니다.');
    }

    $entry_id = isset($_POST['entry_id']) ? absint($_POST['entry_id']) : 0;
    $company = isset($_POST['company']) ? sanitize_text_field($_POST['company']) : '';
    $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
    $type = isset($_POST['type']) ? sanitize_key($_POST['type']) : 'design';
    $status = isset($_POST['status']) ? sanitize_key($_POST['status']) : 'receiving';

    if (empty($company)) {
      wp_send_json_error('회사명을 입력해주세요.');
    }

    if (empty($date)) {
      wp_send_json_error('접수일을 입력해주세요.');
    }

    // Validate type and status
    $valid_types = array_keys(UW_Maintenance_CPT::get_types());
    $valid_statuses = array_keys(UW_Maintenance_CPT::get_statuses());

    if (!in_array($type, $valid_types)) {
      $type = 'design';
    }
    if (!in_array($status, $valid_statuses)) {
      $status = 'receiving';
    }

    $post_data = array(
      'post_type' => 'uw_maintenance',
      'post_status' => 'publish',
      'post_title' => $company . ' - ' . $date,
    );

    if ($entry_id > 0) {
      $post_data['ID'] = $entry_id;
      $result = wp_update_post($post_data);
    } else {
      $result = wp_insert_post($post_data);
    }

    if (is_wp_error($result)) {
      wp_send_json_error('저장 중 오류가 발생했습니다.');
    }

    $post_id = $entry_id > 0 ? $entry_id : $result;

    update_post_meta($post_id, '_uw_maintenance_company', $company);
    update_post_meta($post_id, '_uw_maintenance_date', $date);
    update_post_meta($post_id, '_uw_maintenance_type', $type);
    update_post_meta($post_id, '_uw_maintenance_status', $status);

    wp_send_json_success(array(
      'message' => $entry_id > 0 ? '수정되었습니다.' : '추가되었습니다.',
      'redirect' => admin_url('admin.php?page=uw-maintenance'),
    ));
  }

  /**
   * AJAX: Delete entry
   */
  public function ajax_delete_entry()
  {
    check_ajax_referer('uw_maintenance_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error('권한이 없습니다.');
    }

    $entry_id = isset($_POST['entry_id']) ? absint($_POST['entry_id']) : 0;

    if ($entry_id <= 0) {
      wp_send_json_error('잘못된 요청입니다.');
    }

    $post = get_post($entry_id);
    if (!$post || $post->post_type !== 'uw_maintenance') {
      wp_send_json_error('항목을 찾을 수 없습니다.');
    }

    $result = wp_delete_post($entry_id, true);

    if (!$result) {
      wp_send_json_error('삭제 중 오류가 발생했습니다.');
    }

    wp_send_json_success(array(
      'message' => '삭제되었습니다.',
    ));
  }

  /**
   * AJAX: Save KPI settings
   */
  public function ajax_save_kpi()
  {
    check_ajax_referer('uw_maintenance_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error('권한이 없습니다.');
    }

    $kpi = array(
      'today_completed' => isset($_POST['today_completed']) ? absint($_POST['today_completed']) : 0,
      'monthly_completed' => isset($_POST['monthly_completed']) ? absint($_POST['monthly_completed']) : 0,
      'one_day_rate' => isset($_POST['one_day_rate']) ? floatval($_POST['one_day_rate']) : 0.0,
      'free_rate' => isset($_POST['free_rate']) ? floatval($_POST['free_rate']) : 0.0,
      'status_link' => isset($_POST['status_link']) ? esc_url_raw($_POST['status_link']) : '',
    );

    // Clamp percentage values
    $kpi['one_day_rate'] = max(0, min(100, $kpi['one_day_rate']));
    $kpi['free_rate'] = max(0, min(100, $kpi['free_rate']));

    update_option(UW_Maintenance_CPT::KPI_OPTION_KEY, $kpi);

    wp_send_json_success(array(
      'message' => '설정이 저장되었습니다.',
    ));
  }
}

// Initialize
UW_Maintenance_Admin::get_instance();
