<?php
/**
 * UW Inquiry Admin Interface
 * 
 * 입력폼 관리자 메뉴 및 페이지 처리
 * 
 * @package starter-theme
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit;
}

class UW_Inquiry_Admin
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
    add_action('wp_ajax_uw_inquiry_save_form', array($this, 'ajax_save_form'));
    add_action('wp_ajax_uw_inquiry_delete_form', array($this, 'ajax_delete_form'));
    add_action('wp_ajax_uw_inquiry_save_fields', array($this, 'ajax_save_fields'));
    add_action('wp_ajax_uw_inquiry_delete_entry', array($this, 'ajax_delete_entry'));
    add_action('wp_ajax_uw_inquiry_export_csv', array($this, 'ajax_export_csv'));
  }

  /**
   * 관리자 메뉴 등록
   */
  public function add_admin_menu()
  {
    // 메인 메뉴: 입력폼
    add_menu_page(
      '입력폼',
      '입력폼',
      'manage_options',
      'uw-inquiry',
      array($this, 'render_forms_page'),
      'dashicons-feedback',
      27
    );

    // 서브메뉴: 입력폼 관리
    add_submenu_page(
      'uw-inquiry',
      '입력폼 관리',
      '입력폼 관리',
      'manage_options',
      'uw-inquiry',
      array($this, 'render_forms_page')
    );

    // 서브메뉴: 입력폼 생성
    add_submenu_page(
      'uw-inquiry',
      '입력폼 생성',
      '입력폼 생성',
      'manage_options',
      'uw-inquiry-create',
      array($this, 'render_create_page')
    );
  }

  /**
   * 관리자 에셋 로드
   */
  public function enqueue_admin_assets($hook)
  {
    if (strpos($hook, 'uw-inquiry') === false) {
      return;
    }

    // WordPress Media Uploader
    wp_enqueue_media();

    // Summernote for editor
    wp_enqueue_style('summernote', 'https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css');
    wp_enqueue_script('summernote', 'https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js', array('jquery'), '0.8.18', true);

    // SortableJS for drag and drop
    wp_enqueue_script('sortablejs', 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js', array(), '1.15.0', true);

    // Custom admin styles and scripts
    wp_enqueue_style('uw-inquiry-admin', get_theme_file_uri('/assets/css/cpt/inquiry/admin.css'), array(), '1.0.1');
    wp_enqueue_script('uw-inquiry-admin', get_theme_file_uri('/assets/js/CPT/inquiry/uw-inquiry-admin.js'), array('jquery', 'summernote', 'sortablejs'), '1.0.1', true);

    wp_localize_script('uw-inquiry-admin', 'uwInquiryAdmin', array(
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('uw_inquiry_admin_nonce'),
      'fieldTypes' => UW_Inquiry_CPT::get_field_types(),
    ));
  }

  /**
   * 모든 입력폼 가져오기
   */
  public function get_all_forms()
  {
    $forms = get_posts(array(
      'post_type' => 'uw_inquiry_form',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'orderby' => 'date',
      'order' => 'DESC',
    ));
    return $forms;
  }

  /**
   * 특정 폼의 문의 내역 수 가져오기
   */
  public function get_entry_count($form_id)
  {
    $entries = get_posts(array(
      'post_type' => 'uw_inquiry_entry',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'meta_query' => array(
        array(
          'key' => '_uw_inquiry_form_id',
          'value' => $form_id,
        ),
      ),
      'fields' => 'ids',
    ));
    return count($entries);
  }

  /**
   * 입력폼 목록 페이지 렌더링
   */
  public function render_forms_page()
  {
    $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : '';
    $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;

    // 특정 폼 관리 페이지
    if ($form_id && $action === 'manage') {
      $this->render_form_manage_page($form_id);
      return;
    }

    // 문의 상세 보기
    if ($action === 'view_entry' && isset($_GET['entry_id'])) {
      $this->render_entry_detail_page(absint($_GET['entry_id']));
      return;
    }

    // 폼 수정
    if ($form_id && $action === 'edit') {
      $this->render_edit_page($form_id);
      return;
    }

    // 기본: 폼 목록
    $forms = $this->get_all_forms();
    ?>
    <div class="wrap uw-inquiry-admin">
      <h1>입력폼 관리 <a href="<?php echo admin_url('admin.php?page=uw-inquiry-create'); ?>" class="page-title-action">새 입력폼
          추가</a></h1>

      <?php if (empty($forms)): ?>
        <div class="uw-inquiry-empty">
          <p>등록된 입력폼이 없습니다.</p>
          <a href="<?php echo admin_url('admin.php?page=uw-inquiry-create'); ?>" class="button button-primary">첫 입력폼 만들기</a>
        </div>
      <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
          <thead>
            <tr>
              <th style="width: 50px;">No</th>
              <th>폼 이름</th>
              <th style="width: 100px;">문의 건수</th>
              <th style="width: 150px;">숏코드</th>
              <th style="width: 150px;">생성일</th>
              <th style="width: 200px;">관리</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $count = count($forms);
            foreach ($forms as $index => $form):
              $entry_count = $this->get_entry_count($form->ID);
              $shortcode = '[uw_inquiry_form id="' . $form->ID . '"]';
              ?>
              <tr>
                <td>
                  <?php echo $count - $index; ?>
                </td>
                <td>
                  <strong>
                    <a href="<?php echo admin_url('admin.php?page=uw-inquiry&action=manage&form_id=' . $form->ID); ?>">
                      <?php echo esc_html($form->post_title); ?>
                    </a>
                  </strong>
                </td>
                <td><span class="uw-entry-count">
                    <?php echo $entry_count; ?>
                  </span></td>
                <td>
                  <code class="uw-shortcode"><?php echo esc_html($shortcode); ?></code>
                  <button type="button" class="button-link uw-copy-shortcode"
                    data-shortcode="<?php echo esc_attr($shortcode); ?>" title="복사">📋</button>
                </td>
                <td>
                  <?php echo get_the_date('Y-m-d H:i', $form->ID); ?>
                </td>
                <td>
                  <a href="<?php echo admin_url('admin.php?page=uw-inquiry&action=manage&form_id=' . $form->ID); ?>"
                    class="button button-small">문의관리</a>
                  <a href="<?php echo admin_url('admin.php?page=uw-inquiry&action=edit&form_id=' . $form->ID); ?>"
                    class="button button-small">설정</a>
                  <button type="button" class="button button-small button-link-delete uw-delete-form"
                    data-id="<?php echo $form->ID; ?>">삭제</button>
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
   * 입력폼 생성 페이지 렌더링
   */
  public function render_create_page()
  {
    ?>
    <div class="wrap uw-inquiry-admin">
      <h1>새 입력폼 생성</h1>

      <form id="uw-inquiry-create-form" class="uw-inquiry-form">
        <table class="form-table">
          <tr>
            <th><label for="form_title">폼 이름 <span class="required">*</span></label></th>
            <td>
              <input type="text" id="form_title" name="form_title" class="regular-text" required
                placeholder="예: 문의하기, 상담신청">
              <p class="description">관리자 구분용 이름입니다.</p>
            </td>
          </tr>
        </table>

        <h2>기본 필드 설정</h2>
        <p class="description">폼 생성 후 '입력폼 설정'에서 필드를 추가/수정할 수 있습니다.</p>

        <div id="uw-default-fields">
          <?php $this->render_field_list(UW_Inquiry_CPT::get_default_fields()); ?>
        </div>

        <p class="submit">
          <button type="submit" class="button button-primary button-large">입력폼 생성</button>
          <a href="<?php echo admin_url('admin.php?page=uw-inquiry'); ?>" class="button button-large">취소</a>
        </p>
      </form>
    </div>
    <?php
  }

  /**
   * 필드 목록 렌더링
   */
  private function render_field_list($fields)
  {
    $field_types = UW_Inquiry_CPT::get_field_types();
    ?>
    <div class="uw-field-list" id="uw-field-list">
      <?php foreach ($fields as $field): ?>
        <div class="uw-field-item" data-field-id="<?php echo esc_attr($field['id']); ?>">
          <div class="uw-field-header">
            <span class="uw-field-drag">☰</span>
            <span class="uw-field-label">
              <?php echo esc_html($field['label']); ?>
            </span>
            <span class="uw-field-type">
              <?php echo esc_html($field_types[$field['type']] ?? $field['type']); ?>
            </span>
            <label class="uw-field-toggle">
              <input type="checkbox" name="fields[<?php echo esc_attr($field['id']); ?>][enabled]" value="1" <?php checked($field['enabled']); ?>>
              <span class="slider"></span>
            </label>
            <button type="button" class="uw-field-edit">수정</button>
            <button type="button" class="uw-field-delete">삭제</button>
          </div>
          <div class="uw-field-body" style="display: none;">
            <input type="hidden" name="fields[<?php echo esc_attr($field['id']); ?>][id]"
              value="<?php echo esc_attr($field['id']); ?>">
            <input type="hidden" name="fields[<?php echo esc_attr($field['id']); ?>][order]"
              value="<?php echo esc_attr($field['order']); ?>" class="field-order">

            <div class="uw-field-row">
              <label>라벨</label>
              <input type="text" name="fields[<?php echo esc_attr($field['id']); ?>][label]"
                value="<?php echo esc_attr($field['label']); ?>" required>
            </div>
            <div class="uw-field-row">
              <label>필드 타입</label>
              <select name="fields[<?php echo esc_attr($field['id']); ?>][type]">
                <?php foreach ($field_types as $type => $label): ?>
                  <option value="<?php echo esc_attr($type); ?>" <?php selected($field['type'], $type); ?>>
                    <?php echo esc_html($label); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="uw-field-row">
              <label>플레이스홀더</label>
              <input type="text" name="fields[<?php echo esc_attr($field['id']); ?>][placeholder]"
                value="<?php echo esc_attr($field['placeholder']); ?>">
            </div>
            <div class="uw-field-row">
              <label>도움말 텍스트</label>
              <input type="text" name="fields[<?php echo esc_attr($field['id']); ?>][help_text]"
                value="<?php echo esc_attr($field['help_text'] ?? ''); ?>">
            </div>
            <?php
            $show_options = in_array($field['type'], array('select', 'checkbox', 'radio'));
            ?>
            <div class="uw-field-row uw-field-options-row" style="<?php echo $show_options ? '' : 'display: none;'; ?>">
              <label>옵션 (한 줄에 하나씩)</label>
              <textarea name="fields[<?php echo esc_attr($field['id']); ?>][options]" rows="4"
                placeholder="옵션1&#10;옵션2&#10;옵션3"><?php echo esc_textarea($field['options'] ?? ''); ?></textarea>
              <p class="description">드롭다운, 체크박스, 라디오 타입에서 선택할 수 있는 옵션들을 한 줄씩 입력하세요.</p>
            </div>
            <div class="uw-field-row">
              <label>
                <input type="checkbox" name="fields[<?php echo esc_attr($field['id']); ?>][required]" value="1" <?php checked($field['required']); ?>>
                필수 입력
              </label>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <button type="button" class="button uw-add-field" id="uw-add-field">+ 필드 추가</button>
    <?php
  }

  /**
   * 특정 폼 관리 페이지 (문의 목록)
   */
  public function render_form_manage_page($form_id)
  {
    $form = get_post($form_id);
    if (!$form || $form->post_type !== 'uw_inquiry_form') {
      echo '<div class="wrap"><p>폼을 찾을 수 없습니다.</p></div>';
      return;
    }

    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
    $per_page = 20;

    // 문의 내역 조회
    $args = array(
      'post_type' => 'uw_inquiry_entry',
      'post_status' => 'publish',
      'posts_per_page' => $per_page,
      'paged' => $paged,
      'orderby' => 'date',
      'order' => 'DESC',
      'meta_query' => array(
        array(
          'key' => '_uw_inquiry_form_id',
          'value' => $form_id,
        ),
      ),
    );

    // 검색
    if (!empty($search)) {
      $args['s'] = $search;
    }

    $query = new WP_Query($args);
    $total_entries = $this->get_entry_count($form_id);
    ?>
    <div class="wrap uw-inquiry-admin">
      <h1>
        <?php echo esc_html($form->post_title); ?>
        <span class="uw-total-count">(총
          <?php echo $total_entries; ?>건)
        </span>
      </h1>

      <div class="uw-inquiry-tabs">
        <a href="<?php echo admin_url('admin.php?page=uw-inquiry&action=manage&form_id=' . $form_id); ?>"
          class="uw-tab-link active">문의관리</a>
        <a href="<?php echo admin_url('admin.php?page=uw-inquiry&action=edit&form_id=' . $form_id); ?>"
          class="uw-tab-link">입력폼 설정</a>
      </div>

      <div class="uw-inquiry-header">
        <div class="uw-inquiry-actions">
          <button type="button" class="button uw-export-csv" data-form-id="<?php echo $form_id; ?>">CSV 다운로드</button>
        </div>
        <form class="uw-search-form" method="get">
          <input type="hidden" name="page" value="uw-inquiry">
          <input type="hidden" name="action" value="manage">
          <input type="hidden" name="form_id" value="<?php echo $form_id; ?>">
          <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="이름, 이메일, 연락처, 내용 검색...">
          <button type="submit" class="button">검색</button>
        </form>
      </div>

      <?php if (!$query->have_posts()): ?>
        <div class="uw-inquiry-empty">
          <p>접수된 문의가 없습니다.</p>
        </div>
      <?php else:
        // 폼 필드 가져오기 (동적 컬럼 생성용)
        $fields = get_post_meta($form_id, '_uw_inquiry_fields', true);
        $fields = is_array($fields) && !empty($fields) ? $fields : UW_Inquiry_CPT::get_default_fields();

        // 활성화된 필드만 필터링하고 순서대로 정렬
        $active_fields = array_filter($fields, function ($field) {
          return !empty($field['enabled']);
        });
        usort($active_fields, function ($a, $b) {
          return ($a['order'] ?? 0) - ($b['order'] ?? 0);
        });

        // 리스트에 표시할 필드 (최대 4개)
        $display_fields = array_slice($active_fields, 0, 4);
        ?>
        <table class="wp-list-table widefat fixed striped uw-inquiry-table">
          <thead>
            <tr>
              <th style="width: 50px;">No</th>
              <?php foreach ($display_fields as $field): ?>
                <th><?php echo esc_html($field['label']); ?></th>
              <?php endforeach; ?>
              <th style="width: 150px;">작성시각</th>
              <th style="width: 100px;">관리</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $start_num = ($paged - 1) * $per_page + 1;
            while ($query->have_posts()):
              $query->the_post();
              $entry_id = get_the_ID();
              $data = get_post_meta($entry_id, '_uw_inquiry_data', true);
              $data = is_array($data) ? $data : array();
              ?>
              <tr class="uw-entry-row" data-entry-id="<?php echo $entry_id; ?>">
                <td>
                  <?php echo $start_num++; ?>
                </td>
                <?php foreach ($display_fields as $field):
                  $field_value = isset($data[$field['id']]) ? $data[$field['id']] : '-';
                  // 긴 텍스트는 잘라서 표시
                  if (strlen($field_value) > 50) {
                    $field_value = mb_substr($field_value, 0, 50, 'UTF-8') . '...';
                  }
                  ?>
                  <td><?php echo esc_html($field_value); ?></td>
                <?php endforeach; ?>
                <td>
                  <?php echo get_the_date('Y-m-d H:i'); ?>
                </td>
                <td>
                  <a href="<?php echo admin_url('admin.php?page=uw-inquiry&action=view_entry&entry_id=' . $entry_id . '&form_id=' . $form_id); ?>"
                    class="button button-small">상세</a>
                  <button type="button" class="button button-small button-link-delete uw-delete-entry"
                    data-id="<?php echo $entry_id; ?>">삭제</button>
                </td>
              </tr>
            <?php endwhile;
            wp_reset_postdata(); ?>
          </tbody>
        </table>

        <?php
        // 페이지네이션
        $total_pages = $query->max_num_pages;
        if ($total_pages > 1):
          ?>
          <div class="uw-pagination">
            <?php
            echo paginate_links(array(
              'base' => add_query_arg('paged', '%#%'),
              'format' => '',
              'prev_text' => '&laquo;',
              'next_text' => '&raquo;',
              'total' => $total_pages,
              'current' => $paged,
            ));
            ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
    <?php
  }

  /**
   * 문의 상세 보기 페이지
   */
  public function render_entry_detail_page($entry_id)
  {
    $entry = get_post($entry_id);
    if (!$entry || $entry->post_type !== 'uw_inquiry_entry') {
      echo '<div class="wrap"><p>문의를 찾을 수 없습니다.</p></div>';
      return;
    }

    $form_id = get_post_meta($entry_id, '_uw_inquiry_form_id', true);
    $form = get_post($form_id);
    $data = get_post_meta($entry_id, '_uw_inquiry_data', true);
    $data = is_array($data) ? $data : array();
    $fields = get_post_meta($form_id, '_uw_inquiry_fields', true);
    $fields = is_array($fields) ? $fields : UW_Inquiry_CPT::get_default_fields();
    $ip = get_post_meta($entry_id, '_uw_inquiry_ip', true);
    ?>
    <div class="wrap uw-inquiry-admin">
      <h1>
        문의 상세
        <a href="<?php echo admin_url('admin.php?page=uw-inquiry&action=manage&form_id=' . $form_id); ?>"
          class="page-title-action">목록으로</a>
      </h1>

      <div class="uw-entry-detail">
        <table class="form-table">
          <tr>
            <th>접수 폼</th>
            <td><strong>
                <?php echo esc_html($form ? $form->post_title : '삭제된 폼'); ?>
              </strong></td>
          </tr>
          <tr>
            <th>접수 시각</th>
            <td>
              <?php echo get_the_date('Y-m-d H:i:s', $entry_id); ?>
            </td>
          </tr>
          <tr>
            <th>접수 IP</th>
            <td>
              <?php echo esc_html($ip ?: '-'); ?>
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <hr>
            </td>
          </tr>
          <?php foreach ($fields as $field): ?>
            <?php
            // enabled 필드만 표시하고, 데이터가 존재하면 출력
            if (empty($field['enabled']))
              continue;
            $field_value = isset($data[$field['id']]) ? $data[$field['id']] : '';
            if ($field_value === '' && !isset($data[$field['id']]))
              continue;
            ?>
            <tr>
              <th>
                <?php echo esc_html($field['label']); ?>
              </th>
              <td>
                <?php if ($field['type'] === 'textarea'): ?>
                  <div class="uw-entry-content">
                    <?php echo nl2br(esc_html($field_value)); ?>
                  </div>
                <?php elseif ($field['type'] === 'file' && is_array($field_value) && !empty($field_value['url'])): ?>
                  <a href="<?php echo esc_url($field_value['url']); ?>" target="_blank" class="button">
                    📎 <?php echo esc_html($field_value['name'] ?? '파일 다운로드'); ?>
                  </a>
                <?php elseif (is_array($field_value)): ?>
                  <?php echo esc_html(implode(', ', $field_value)); ?>
                <?php else: ?>
                  <?php echo esc_html($field_value ?: '-'); ?>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>

        <div class="uw-entry-actions">
          <a href="<?php echo admin_url('admin.php?page=uw-inquiry&action=manage&form_id=' . $form_id); ?>"
            class="button button-large">목록으로</a>
          <button type="button" class="button button-large button-link-delete uw-delete-entry"
            data-id="<?php echo $entry_id; ?>"
            data-redirect="<?php echo admin_url('admin.php?page=uw-inquiry&action=manage&form_id=' . $form_id); ?>">삭제</button>
        </div>
      </div>
    </div>
    <?php
  }

  /**
   * 폼 수정 페이지 렌더링
   */
  public function render_edit_page($form_id)
  {
    $form = get_post($form_id);
    if (!$form || $form->post_type !== 'uw_inquiry_form') {
      echo '<div class="wrap"><p>폼을 찾을 수 없습니다.</p></div>';
      return;
    }

    $fields = get_post_meta($form_id, '_uw_inquiry_fields', true);
    $fields = is_array($fields) && !empty($fields) ? $fields : UW_Inquiry_CPT::get_default_fields();

    // 설정 값 가져오기
    $default_privacy_text = <<<EOT
<[회사명]> (이하 '귀사'라 합니다)은(는) 「개인정보 보호법」 제30조에 따라 이용자의 개인정보를 보호하고 이와 관련한 고충을 신속하게 처리할 수 있도록 다음과 같은 처리방침을 수립·공개합니다. 본 방침에서 별도로 언급되지 않는 한, 이후 모든 조항에서 '귀사'는 위에서 명시한 <[회사명]>을 의미합니다.

○ 본 방침은 **[시행일자]**부터 시행됩니다.

1. 개인정보의 처리 목적 귀사는 다음의 목적을 위하여 개인정보를 처리합니다. 처리하고 있는 개인정보는 다음의 목적 이외의 용도로는 이용되지 않으며 이용 목적이 변경되는 경우에는 「개인정보 보호법」 제18조에 따라 별도의 동의를 받는 등 필요한 조치를 이행할 예정입니다.

민원사무 처리: 고객 문의 및 상담 요청에 대한 응대, 사실확인을 위한 연락·통지, 처리결과 통보 등을 목적으로 개인정보를 처리합니다.

2. 개인정보의 처리 및 보유 기간 ① 귀사는 법령에 따른 개인정보 보유·이용기간 또는 정보주체로부터 개인정보를 수집 시에 동의받은 개인정보 보유·이용기간 내에서 개인정보를 처리·보유합니다. ② 각각의 개인정보 처리 및 보유 기간은 다음과 같습니다.

보유 항목: 문의 접수 시 수집된 개인정보

보유 기간: [보유기간, 예: 3년]

보유 근거: 고객 문의 대응 및 이력 관리

관련 법령: 소비자의 불만 또는 분쟁처리에 관한 기록 (3년)

3. 처리하는 개인정보의 항목 귀사는 다음의 개인정보 항목을 처리하고 있습니다.

필수항목: 이름, 휴대전화번호, 이메일, 서비스 이용 기록, 접속 로그, 접속 IP 정보

선택항목: 문의 내용 (상세 내용 등)

4. 개인정보의 파기절차 및 파기방법 ① 귀사는 개인정보 보유기간의 경과, 처리목적 달성 등 개인정보가 불필요하게 되었을 때에는 지체 없이 해당 개인정보를 파기합니다. ② 전자적 파일 형태의 정보는 기록을 재생할 수 없는 기술적 방법을 사용하며, 종이 문서에 출력된 개인정보는 분쇄기로 분쇄하거나 소각을 통하여 파기합니다.

5. 정보주체와 법정대리인의 권리·의무 및 그 행사방법 이용자는 개인정보주체로서 언제든지 개인정보 열람·정정·삭제·처리정지 요구 등의 권리를 행사할 수 있습니다. 권리 행사는 서면, 전자우편 등을 통하여 하실 수 있으며 귀사는 이에 대해 지체 없이 조치하겠습니다.

6. 개인정보 보호책임자 ① 귀사는 개인정보 처리에 관한 업무를 총괄해서 책임지고, 개인정보 처리와 관련한 정보주체의 불만처리 및 피해구제 등을 위하여 아래와 같이 개인정보 보호책임자를 지정하고 있습니다.

▶ 개인정보 보호책임자

성명 : [성명]

직책 : [직책]

연락처 : [전화번호], [이메일]

▶ 개인정보 보호 담당부서

부서명 : [부서명]

담당자 : [담당자명]

연락처 : [전화번호], [이메일]

7. 개인정보의 안전성 확보 조치 귀사는 개인정보보호법 제29조에 따라 다음과 같이 안전성 확보에 필요한 기술적/관리적 및 물리적 조치를 하고 있습니다.

개인정보 취급 직원의 최소화 및 교육: 개인정보를 취급하는 직원을 지정하고 담당자에 한정시켜 최소화하여 관리하고 있습니다.

개인정보에 대한 접근 제한: 데이터베이스시스템에 대한 접근권한 부여, 변경, 말소를 통해 접근통제 조치를 취하고 있습니다.
EOT;

    $privacy_text = get_post_meta($form_id, '_uw_inquiry_privacy_text', true);
    if (empty($privacy_text)) {
      $privacy_text = $default_privacy_text;
    }
    $privacy_required = get_post_meta($form_id, '_uw_inquiry_privacy_required', true);
    $captcha_enabled = get_post_meta($form_id, '_uw_inquiry_captcha_enabled', true);
    $notify_emails = get_post_meta($form_id, '_uw_inquiry_notify_emails', true);
    $mail_subject = get_post_meta($form_id, '_uw_inquiry_mail_subject', true);
    $success_type = get_post_meta($form_id, '_uw_inquiry_success_type', true) ?: 'popup';
    $success_message = get_post_meta($form_id, '_uw_inquiry_success_message', true) ?: '정상적으로 접수되었습니다.';
    $success_page_id = get_post_meta($form_id, '_uw_inquiry_success_page_id', true);
    ?>
    <div class="wrap uw-inquiry-admin">
      <h1>
        <?php echo esc_html($form->post_title); ?> 설정
      </h1>

      <div class="uw-inquiry-tabs">
        <a href="<?php echo admin_url('admin.php?page=uw-inquiry&action=manage&form_id=' . $form_id); ?>"
          class="uw-tab-link">문의관리</a>
        <a href="<?php echo admin_url('admin.php?page=uw-inquiry&action=edit&form_id=' . $form_id); ?>"
          class="uw-tab-link active">입력폼 설정</a>
      </div>

      <form id="uw-inquiry-edit-form" class="uw-inquiry-form" data-form-id="<?php echo $form_id; ?>">
        <!-- 기본 설정 -->
        <div class="uw-settings-section">
          <h2>기본 설정</h2>
          <table class="form-table">
            <tr>
              <th><label for="form_title">폼 이름</label></th>
              <td>
                <input type="text" id="form_title" name="form_title" value="<?php echo esc_attr($form->post_title); ?>"
                  class="regular-text" required>
              </td>
            </tr>
            <tr>
              <th>숏코드</th>
              <td>
                <code>[uw_inquiry_form id="<?php echo $form_id; ?>"]</code>
              </td>
            </tr>
          </table>
        </div>

        <!-- 필드 설정 -->
        <div class="uw-settings-section">
          <h2>폼 필드 설정</h2>
          <p class="description">드래그하여 순서를 변경하고, 토글로 사용 여부를 설정할 수 있습니다.</p>
          <?php $this->render_field_list($fields); ?>
        </div>

        <!-- 개인정보 정책 -->
        <div class="uw-settings-section">
          <h2>개인정보 정책</h2>
          <table class="form-table">
            <tr>
              <th><label for="privacy_required">동의 필수 여부</label></th>
              <td>
                <label>
                  <input type="checkbox" id="privacy_required" name="privacy_required" value="1" <?php checked($privacy_required); ?>>
                  개인정보 동의를 필수로 체크해야 제출 가능
                </label>
              </td>
            </tr>
            <tr>
              <th><label for="privacy_text">개인정보 처리방침</label></th>
              <td>
                <textarea id="privacy_text" name="privacy_text" rows="10"
                  class="large-text"><?php echo esc_textarea($privacy_text); ?></textarea>
                <p class="description">폼 하단에 표시될 개인정보 처리방침 내용입니다.</p>
              </td>
            </tr>
          </table>
        </div>

        <!-- 스팸 방지 설정 -->
        <div class="uw-settings-section">
          <h2>스팸 방지</h2>
          <table class="form-table">
            <tr>
              <th><label for="captcha_enabled">자동등록방지 사용</label></th>
              <td>
                <label>
                  <input type="checkbox" id="captcha_enabled" name="captcha_enabled" value="1" <?php checked($captcha_enabled); ?>>
                  자동등록방지 이미지 캡챠 활성화
                </label>
                <p class="description">활성화하면 폼 제출 시 이미지에 표시된 숫자를 입력해야 합니다.</p>
              </td>
            </tr>
          </table>
        </div>

        <!-- 메일 설정 -->
        <div class="uw-settings-section">
          <h2>메일/알림 설정</h2>
          <table class="form-table">
            <tr>
              <th><label for="notify_emails">알림 수신 이메일</label></th>
              <td>
                <input type="text" id="notify_emails" name="notify_emails" value="<?php echo esc_attr($notify_emails); ?>"
                  class="large-text" placeholder="admin@example.com, sales@example.com">
                <p class="description">쉼표로 구분하여 여러 이메일을 입력할 수 있습니다.</p>
                <div class="notice notice-info inline" style="margin-top:10px; padding:10px 14px;">
                  <p style="margin:0 0 6px;"><strong>커스텀 도메인 이메일 사용 안내</strong> (예: contact@mydomain.co.kr)</p>
                  <p style="margin:0; font-size:12.5px; color:#555;">
                    자체 도메인 이메일로 수신하려면 해당 도메인의 <strong>DNS에 MX 레코드</strong>가 설정되어 있어야 합니다.<br>
                    MX 레코드가 없으면 메일이 발송되더라도 수신할 서버가 없어 메일이 도착하지 않습니다.<br><br>
                    <strong>설정 방법:</strong> 도메인 관리(카페24, 가비아 등) > DNS 관리 > MX 레코드 추가<br>
                    <code>호스트: @ | 타입: MX | 값: mail.mydomain.co.kr | 우선순위: 10</code><br><br>
                    또는 <strong>Google Workspace</strong>, <strong>네이버 웍스</strong> 등 외부 메일 서비스를 연결하면<br>
                    해당 서비스에서 MX 레코드 값을 안내받을 수 있습니다.<br><br>
                    <em>Gmail, Naver 등 일반 메일 주소는 별도 설정 없이 바로 수신 가능합니다.</em>
                  </p>
                </div>
              </td>
            </tr>
            <tr>
              <th><label for="mail_subject">메일 제목</label></th>
              <td>
                <input type="text" id="mail_subject" name="mail_subject" value="<?php echo esc_attr($mail_subject); ?>"
                  class="regular-text" placeholder="새로운 문의가 접수되었습니다">
              </td>
            </tr>
          </table>
        </div>

        <!-- 완료 설정 -->
        <div class="uw-settings-section">
          <h2>완료 액션 설정</h2>
          <table class="form-table">
            <tr>
              <th><label for="success_type">완료 시 동작</label></th>
              <td>
                <select id="success_type" name="success_type">
                  <option value="popup" <?php selected($success_type, 'popup'); ?>>팝업 메시지 표시</option>
                  <option value="redirect" <?php selected($success_type, 'redirect'); ?>>페이지 리다이렉트</option>
                </select>
              </td>
            </tr>
            <tr class="uw-success-popup" <?php echo $success_type !== 'popup' ? 'style="display:none;"' : ''; ?>>
              <th><label for="success_message">완료 메시지</label></th>
              <td>
                <input type="text" id="success_message" name="success_message"
                  value="<?php echo esc_attr($success_message); ?>" class="regular-text">
              </td>
            </tr>
            <tr class="uw-success-redirect" <?php echo $success_type !== 'redirect' ? 'style="display:none;"' : ''; ?>>
              <th><label for="success_page_id">이동할 페이지</label></th>
              <td>
                <?php
                wp_dropdown_pages(array(
                  'name' => 'success_page_id',
                  'id' => 'success_page_id',
                  'selected' => $success_page_id,
                  'show_option_none' => '-- 페이지 선택 --',
                  'option_none_value' => '',
                ));
                ?>
              </td>
            </tr>
          </table>
        </div>

        <p class="submit">
          <button type="submit" class="button button-primary button-large">설정 저장</button>
          <a href="<?php echo admin_url('admin.php?page=uw-inquiry'); ?>" class="button button-large">목록으로</a>
        </p>
      </form>
    </div>
    <?php
  }

  /**
   * AJAX: 폼 저장
   */
  public function ajax_save_form()
  {
    check_ajax_referer('uw_inquiry_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error('권한이 없습니다.');
    }

    $form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;
    $title = isset($_POST['form_title']) ? sanitize_text_field($_POST['form_title']) : '';
    $fields = isset($_POST['fields']) ? $_POST['fields'] : array();

    if (empty($title)) {
      wp_send_json_error('폼 이름을 입력해주세요.');
    }

    // 필드 데이터 정리
    $clean_fields = array();
    if (!empty($fields) && is_array($fields)) {
      foreach ($fields as $field) {
        if (empty($field['id']) || empty($field['label']))
          continue;

        $clean_fields[] = array(
          'id' => sanitize_key($field['id']),
          'type' => sanitize_key($field['type'] ?? 'text'),
          'label' => sanitize_text_field($field['label']),
          'required' => !empty($field['required']),
          'placeholder' => sanitize_text_field($field['placeholder'] ?? ''),
          'help_text' => sanitize_text_field($field['help_text'] ?? ''),
          'enabled' => !empty($field['enabled']),
          'order' => absint($field['order'] ?? 0),
          'options' => isset($field['options']) ? sanitize_textarea_field($field['options']) : '',
        );
      }
    }

    // 새 폼 생성 또는 업데이트
    $post_data = array(
      'post_title' => $title,
      'post_type' => 'uw_inquiry_form',
      'post_status' => 'publish',
    );

    if ($form_id) {
      $post_data['ID'] = $form_id;
      wp_update_post($post_data);
    } else {
      $form_id = wp_insert_post($post_data);
    }

    if (!$form_id || is_wp_error($form_id)) {
      wp_send_json_error('폼 저장에 실패했습니다.');
    }

    // 필드 저장
    if (!empty($clean_fields)) {
      update_post_meta($form_id, '_uw_inquiry_fields', $clean_fields);
    }

    // 추가 설정 저장
    if (isset($_POST['privacy_text'])) {
      update_post_meta($form_id, '_uw_inquiry_privacy_text', wp_kses_post($_POST['privacy_text']));
    }
    if (isset($_POST['privacy_required'])) {
      update_post_meta($form_id, '_uw_inquiry_privacy_required', !empty($_POST['privacy_required']));
    }
    // 자동등록방지 설정 저장
    update_post_meta($form_id, '_uw_inquiry_captcha_enabled', !empty($_POST['captcha_enabled']));
    if (isset($_POST['notify_emails'])) {
      update_post_meta($form_id, '_uw_inquiry_notify_emails', sanitize_text_field($_POST['notify_emails']));
    }
    if (isset($_POST['mail_subject'])) {
      update_post_meta($form_id, '_uw_inquiry_mail_subject', sanitize_text_field($_POST['mail_subject']));
    }
    if (isset($_POST['success_type'])) {
      update_post_meta($form_id, '_uw_inquiry_success_type', sanitize_key($_POST['success_type']));
    }
    if (isset($_POST['success_message'])) {
      update_post_meta($form_id, '_uw_inquiry_success_message', sanitize_text_field($_POST['success_message']));
    }
    if (isset($_POST['success_page_id'])) {
      update_post_meta($form_id, '_uw_inquiry_success_page_id', absint($_POST['success_page_id']));
    }

    wp_send_json_success(array(
      'message' => '저장되었습니다.',
      'form_id' => $form_id,
      'redirect' => admin_url('admin.php?page=uw-inquiry&action=edit&form_id=' . $form_id),
    ));
  }

  /**
   * AJAX: 폼 삭제
   */
  public function ajax_delete_form()
  {
    check_ajax_referer('uw_inquiry_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error('권한이 없습니다.');
    }

    $form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;

    if (!$form_id) {
      wp_send_json_error('폼 ID가 없습니다.');
    }

    // 관련 문의 내역도 삭제
    $entries = get_posts(array(
      'post_type' => 'uw_inquiry_entry',
      'post_status' => 'any',
      'posts_per_page' => -1,
      'meta_query' => array(
        array(
          'key' => '_uw_inquiry_form_id',
          'value' => $form_id,
        ),
      ),
      'fields' => 'ids',
    ));

    foreach ($entries as $entry_id) {
      wp_delete_post($entry_id, true);
    }

    // 폼 삭제
    wp_delete_post($form_id, true);

    wp_send_json_success(array(
      'message' => '삭제되었습니다.',
    ));
  }

  /**
   * AJAX: 필드 저장
   */
  public function ajax_save_fields()
  {
    check_ajax_referer('uw_inquiry_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error('권한이 없습니다.');
    }

    $form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;
    $fields = isset($_POST['fields']) ? $_POST['fields'] : array();

    if (!$form_id) {
      wp_send_json_error('폼 ID가 없습니다.');
    }

    $clean_fields = array();
    foreach ($fields as $field) {
      if (empty($field['id']))
        continue;

      $clean_fields[] = array(
        'id' => sanitize_key($field['id']),
        'type' => sanitize_key($field['type'] ?? 'text'),
        'label' => sanitize_text_field($field['label'] ?? ''),
        'required' => !empty($field['required']),
        'placeholder' => sanitize_text_field($field['placeholder'] ?? ''),
        'help_text' => sanitize_text_field($field['help_text'] ?? ''),
        'enabled' => !empty($field['enabled']),
        'order' => absint($field['order'] ?? 0),
        'options' => isset($field['options']) ? sanitize_textarea_field($field['options']) : '',
      );
    }

    update_post_meta($form_id, '_uw_inquiry_fields', $clean_fields);

    wp_send_json_success(array(
      'message' => '필드가 저장되었습니다.',
    ));
  }

  /**
   * AJAX: 문의 삭제
   */
  public function ajax_delete_entry()
  {
    check_ajax_referer('uw_inquiry_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error('권한이 없습니다.');
    }

    $entry_id = isset($_POST['entry_id']) ? absint($_POST['entry_id']) : 0;

    if (!$entry_id) {
      wp_send_json_error('문의 ID가 없습니다.');
    }

    wp_delete_post($entry_id, true);

    wp_send_json_success(array(
      'message' => '삭제되었습니다.',
    ));
  }

  /**
   * AJAX: CSV 내보내기
   */
  public function ajax_export_csv()
  {
    check_ajax_referer('uw_inquiry_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error('권한이 없습니다.');
    }

    $form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;

    if (!$form_id) {
      wp_send_json_error('폼 ID가 없습니다.');
    }

    $form = get_post($form_id);
    $fields = get_post_meta($form_id, '_uw_inquiry_fields', true);
    $fields = is_array($fields) ? $fields : UW_Inquiry_CPT::get_default_fields();

    // 문의 내역 조회
    $entries = get_posts(array(
      'post_type' => 'uw_inquiry_entry',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'orderby' => 'date',
      'order' => 'DESC',
      'meta_query' => array(
        array(
          'key' => '_uw_inquiry_form_id',
          'value' => $form_id,
        ),
      ),
    ));

    $csv_data = array();

    // 헤더 행
    $header = array('No', '접수일');
    foreach ($fields as $field) {
      if (!empty($field['enabled'])) {
        $header[] = $field['label'];
      }
    }
    $header[] = 'IP';
    $csv_data[] = $header;

    // 데이터 행
    $count = 1;
    foreach ($entries as $entry) {
      $data = get_post_meta($entry->ID, '_uw_inquiry_data', true);
      $data = is_array($data) ? $data : array();
      $ip = get_post_meta($entry->ID, '_uw_inquiry_ip', true);

      $row = array($count++, get_the_date('Y-m-d H:i:s', $entry->ID));
      foreach ($fields as $field) {
        if (!empty($field['enabled'])) {
          $value = $data[$field['id']] ?? '';
          // 파일 필드 처리 (배열인 경우)
          if (is_array($value)) {
            $value = $value['name'] ?? $value['url'] ?? '';
          }
          $row[] = $value;
        }
      }
      $row[] = $ip;
      $csv_data[] = $row;
    }

    wp_send_json_success(array(
      'filename' => sanitize_file_name($form->post_title) . '_' . date('Y-m-d') . '.csv',
      'data' => $csv_data,
    ));
  }
}

// Initialize
UW_Inquiry_Admin::get_instance();
