<?php
/**
 * UW Gallery Admin Interface
 * 
 * 관리자 메뉴 및 페이지 처리
 * 
 * @package starter-theme
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit;
}

// 비디오 Trait 로드
require_once get_theme_file_path('inc/uw-gallery/trait-uw-gallery-video.php');

class UW_Gallery_Admin
{
  use UW_Gallery_Video_Trait;

  private static $instance = null;

  public static function get_instance()
  {
    if (null === self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function __construct()
  {
    add_action('admin_menu', array($this, 'add_admin_menu'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

    // AJAX handlers
    add_action('wp_ajax_uw_gallery_save', array($this, 'ajax_save_gallery'));
    add_action('wp_ajax_uw_gallery_delete', array($this, 'ajax_delete_gallery'));
    add_action('wp_ajax_uw_gallery_bulk_action', array($this, 'ajax_bulk_action'));
    add_action('wp_ajax_uw_gallery_trash', array($this, 'ajax_trash_gallery'));
    add_action('wp_ajax_uw_gallery_restore', array($this, 'ajax_restore_gallery'));
    add_action('wp_ajax_uw_gallery_change_status', array($this, 'ajax_change_status'));
  }

  /**
   * 관리자 메뉴 등록
   */
  public function add_admin_menu()
  {
    // 메인 메뉴: 갤러리
    add_menu_page(
      '갤러리',
      '갤러리',
      'manage_options',
      'uw-gallery',
      array($this, 'render_dashboard_page'),
      'dashicons-format-gallery',
      26
    );

    // 서브메뉴: 모든 갤러리
    add_submenu_page(
      'uw-gallery',
      '갤러리 관리',
      '갤러리 관리',
      'manage_options',
      'uw-gallery',
      array($this, 'render_dashboard_page')
    );

    // 서브메뉴: 새 갤러리 추가
    add_submenu_page(
      'uw-gallery',
      '새 갤러리 추가',
      '새 갤러리 추가',
      'manage_options',
      'uw-gallery-new',
      array($this, 'render_editor_page')
    );
  }

  /**
   * 관리자 에셋 로드
   */
  public function enqueue_admin_assets($hook)
  {
    if (strpos($hook, 'uw-gallery') === false) {
      return;
    }

    // WordPress Media Uploader
    wp_enqueue_media();

    // Admin CSS
    wp_enqueue_style(
      'uw-gallery-admin',
      get_theme_file_uri('assets/css/cpt/gallery/admin.css'),
      array(),
      '2.0.1'
    );

    // Admin JS
    wp_enqueue_script(
      'uw-gallery-admin',
      get_theme_file_uri('assets/js/CPT/gallery/uw-gallery-admin.js'),
      array(),
      '2.0.1',
      true
    );

    wp_localize_script('uw-gallery-admin', 'uwGalleryAdmin', array(
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('uw_gallery_admin'),
      'i18n' => array(
        'selectImages' => '이미지 선택',
        'addImages' => '갤러리에 추가',
        'confirmDelete' => '정말 삭제하시겠습니까?',
        'confirmBulkDelete' => '선택한 갤러리를 삭제하시겠습니까?',
        'confirmTrash' => '선택한 갤러리를 휴지통으로 이동하시겠습니까?',
        'confirmRestore' => '선택한 갤러리를 복구하시겠습니까?',
        'confirmPermanentDelete' => '선택한 갤러리를 영구 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.',
        'confirmStatusChange' => '상태를 변경하시겠습니까?',
      ),
    ));
  }

  /**
   * 갤러리 상태별 개수 조회
   */
  private function get_gallery_count($status)
  {
    $args = array(
      'post_type' => 'uw_gallery',
      'posts_per_page' => -1,
      'post_status' => $status,
      'fields' => 'ids',
    );
    $query = new WP_Query($args);
    return $query->found_posts;
  }

  /**
   * 대시보드 페이지 렌더링 (WP 표준 리스트 UI)
   */
  public function render_dashboard_page()
  {
    // 현재 필터 상태 확인
    $current_status = isset($_GET['post_status']) ? sanitize_key($_GET['post_status']) : 'all';

    // 상태별 개수 조회
    $counts = array(
      'all' => $this->get_gallery_count(array('publish', 'private')),
      'publish' => $this->get_gallery_count('publish'),
      'private' => $this->get_gallery_count('private'),
      'trash' => $this->get_gallery_count('trash'),
    );

    // 쿼리 상태 결정
    $query_status = $current_status === 'all'
      ? array('publish', 'private')
      : $current_status;

    // 갤러리 목록 조회
    $galleries = get_posts(array(
      'post_type' => 'uw_gallery',
      'posts_per_page' => -1,
      'orderby' => 'date',
      'order' => 'DESC',
      'post_status' => $query_status,
    ));

    // 5종 레이아웃 라벨
    $layout_labels = array(
      'grid' => '반응형 그리드',
      'masonry' => 'Masonry',
      'justified' => 'Justified',
      'thumbnail' => '썸네일형',
      'slide' => '슬라이드형',
    );
    ?>
    <div class="wrap uw-gallery-wrap">
      <h1 class="wp-heading-inline">갤러리 관리</h1>
      <a href="<?php echo admin_url('admin.php?page=uw-gallery-new'); ?>" class="page-title-action">새 갤러리 추가</a>
      <hr class="wp-header-end">

      <!-- 상태 필터 탭 -->
      <ul class="subsubsub">
        <li class="all">
          <a href="<?php echo admin_url('admin.php?page=uw-gallery'); ?>"
            class="<?php echo $current_status === 'all' ? 'current' : ''; ?>">
            전체 <span class="count">(<?php echo $counts['all']; ?>)</span>
          </a> |
        </li>
        <li class="publish">
          <a href="<?php echo admin_url('admin.php?page=uw-gallery&post_status=publish'); ?>"
            class="<?php echo $current_status === 'publish' ? 'current' : ''; ?>">
            공개됨 <span class="count">(<?php echo $counts['publish']; ?>)</span>
          </a> |
        </li>
        <li class="private">
          <a href="<?php echo admin_url('admin.php?page=uw-gallery&post_status=private'); ?>"
            class="<?php echo $current_status === 'private' ? 'current' : ''; ?>">
            비공개 <span class="count">(<?php echo $counts['private']; ?>)</span>
          </a> |
        </li>
        <li class="trash">
          <a href="<?php echo admin_url('admin.php?page=uw-gallery&post_status=trash'); ?>"
            class="<?php echo $current_status === 'trash' ? 'current' : ''; ?>">
            휴지통 <span class="count">(<?php echo $counts['trash']; ?>)</span>
          </a>
        </li>
      </ul>

      <div class="uw-gallery-dashboard">
        <?php if (empty($galleries)): ?>
          <div class="uw-gallery-empty <?php echo $current_status === 'trash' ? 'is-trash-empty' : ''; ?>">
            <?php if ($current_status === 'trash'): ?>
              <p>휴지통이 비어 있습니다.</p>
            <?php elseif ($current_status === 'private'): ?>
              <p>비공개 갤러리가 없습니다.</p>
            <?php elseif ($current_status === 'publish'): ?>
              <p>공개된 갤러리가 없습니다.</p>
            <?php else: ?>
              <p>등록된 갤러리가 없습니다.</p>
              <a href="<?php echo admin_url('admin.php?page=uw-gallery-new'); ?>" class="button button-primary">첫 번째 갤러리 만들기</a>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <!-- 일괄 작업 -->
          <div class="tablenav top">
            <div class="alignleft actions bulkactions">
              <select name="bulk_action" id="uw-bulk-action">
                <option value="">일괄 작업</option>
                <?php if ($current_status !== 'trash'): ?>
                  <option value="trash">휴지통으로 이동</option>
                  <option value="private">비공개로 전환</option>
                  <option value="publish">공개로 전환</option>
                <?php else: ?>
                  <option value="restore">복구</option>
                  <option value="delete">영구 삭제</option>
                <?php endif; ?>
              </select>
              <button type="button" id="uw-bulk-apply" class="button">적용</button>
            </div>
            <span class="displaying-num"><?php echo count($galleries); ?>개 항목</span>
          </div>

          <table class="wp-list-table widefat fixed striped uw-gallery-list-table">
            <thead>
              <tr>
                <td class="manage-column column-cb check-column">
                  <input type="checkbox" id="cb-select-all">
                </td>
                <th class="column-thumb">썸네일</th>
                <th class="column-title">제목</th>
                <th class="column-layout">레이아웃</th>
                <th class="column-count">이미지 수</th>
                <th class="column-date">생성일</th>
                <th class="column-publish-date">발행일자</th>
                <th class="column-shortcode">숏코드</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($galleries as $gallery):
                $items = get_post_meta($gallery->ID, '_uw_gallery_items', true) ?: array();
                $layout = get_post_meta($gallery->ID, '_uw_gallery_layout', true) ?: 'grid';
                $shortcode = '[uw_gallery id="' . $gallery->ID . '"]';
                $is_trash = $gallery->post_status === 'trash';

                // 첫 번째 이미지 썸네일
                $thumb_url = '';
                if (!empty($items) && isset($items[0])) {
                  $first_item = $items[0];
                  if ($first_item['type'] === 'video') {
                    $thumb_url = $this->get_video_thumbnail($first_item['video_url']);
                  } else {
                    $thumb_url = wp_get_attachment_image_url($first_item['thumb_id'] ?: $first_item['id'], 'thumbnail');
                  }
                }
                ?>
                <tr data-id="<?php echo $gallery->ID; ?>" class="<?php echo $is_trash ? 'is-trash' : ''; ?>">
                  <th scope="row" class="check-column">
                    <input type="checkbox" class="uw-gallery-checkbox" value="<?php echo $gallery->ID; ?>">
                  </th>
                  <td class="column-thumb">
                    <?php if ($thumb_url): ?>
                      <img src="<?php echo esc_url($thumb_url); ?>" alt="" class="uw-list-thumb">
                    <?php else: ?>
                      <span class="uw-no-thumb dashicons dashicons-format-gallery"></span>
                    <?php endif; ?>
                  </td>
                  <td class="column-title">
                    <strong>
                      <?php if (!$is_trash): ?>
                        <a href="<?php echo admin_url('admin.php?page=uw-gallery-new&id=' . $gallery->ID); ?>">
                          <?php echo esc_html($gallery->post_title ?: '(제목 없음)'); ?>
                        </a>
                      <?php else: ?>
                        <?php echo esc_html($gallery->post_title ?: '(제목 없음)'); ?>
                      <?php endif; ?>
                    </strong>
                    <?php if ($gallery->post_status === 'private'): ?>
                      <span class="post-state">— 비공개</span>
                    <?php elseif ($gallery->post_status === 'draft'): ?>
                      <span class="post-state">— 임시글</span>
                    <?php endif; ?>
                    <div class="row-actions">
                      <?php if (!$is_trash): ?>
                        <!-- 공개/비공개 상태 -->
                        <span class="edit">
                          <a href="<?php echo admin_url('admin.php?page=uw-gallery-new&id=' . $gallery->ID); ?>">편집</a> |
                        </span>
                        <?php if ($gallery->post_status === 'publish'): ?>
                          <span class="private">
                            <a href="#" class="uw-change-status" data-id="<?php echo $gallery->ID; ?>" data-status="private">비공개로 전환</a> |
                          </span>
                        <?php else: ?>
                          <span class="publish">
                            <a href="#" class="uw-change-status" data-id="<?php echo $gallery->ID; ?>" data-status="publish">공개로 전환</a> |
                          </span>
                        <?php endif; ?>
                        <span class="trash">
                          <a href="#" class="uw-gallery-trash" data-id="<?php echo $gallery->ID; ?>">휴지통</a>
                        </span>
                      <?php else: ?>
                        <!-- 휴지통 상태 -->
                        <span class="restore">
                          <a href="#" class="uw-gallery-restore" data-id="<?php echo $gallery->ID; ?>">복구</a> |
                        </span>
                        <span class="delete">
                          <a href="#" class="uw-gallery-delete-permanent" data-id="<?php echo $gallery->ID; ?>">영구 삭제</a>
                        </span>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td class="column-layout">
                    <?php echo isset($layout_labels[$layout]) ? $layout_labels[$layout] : $layout; ?>
                  </td>
                  <td class="column-count">
                    <?php echo count($items); ?>개
                  </td>
                  <td class="column-date">
                    <?php
                    $created_at = get_post_meta($gallery->ID, '_uw_gallery_created_at', true);
                    if ($created_at) {
                      echo date('Y.m.d', strtotime($created_at));
                    } else {
                      // 기존 데이터는 post_date 사용 (하위 호환)
                      echo get_the_date('Y.m.d', $gallery);
                    }
                    ?>
                  </td>
                  <td class="column-publish-date">
                    <?php echo get_the_date('Y.m.d H:i', $gallery); ?>
                  </td>
                  <td class="column-shortcode">
                    <code class="uw-shortcode-code"><?php echo esc_html($shortcode); ?></code>
                    <button type="button" class="button button-small uw-copy-shortcode"
                      data-shortcode="<?php echo esc_attr($shortcode); ?>">
                      복사
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
    <?php
  }

  /**
   * 갤러리 에디터 페이지 렌더링
   */
  public function render_editor_page()
  {
    $gallery_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
    $gallery = $gallery_id ? get_post($gallery_id) : null;
    $is_edit = (bool) $gallery;

    // 설정 가져오기 (기본값 포함)
    $settings = $is_edit
      ? UW_Gallery_CPT::get_gallery_settings($gallery_id)
      : UW_Gallery_CPT::get_default_meta();

    $title = $is_edit ? $gallery->post_title : '';
    $items = $settings['_uw_gallery_items'] ?: array();
    $post_status = $is_edit ? $gallery->post_status : 'publish';
    $visibility = $settings['_uw_gallery_visibility'] ?? 'public';

    // 5종 레이아웃 정의
    $layouts = array(
      'grid' => array('icon' => 'dashicons-grid-view', 'label' => '반응형 그리드'),
      'masonry' => array('icon' => 'dashicons-layout', 'label' => 'Masonry'),
      'justified' => array('icon' => 'dashicons-align-left', 'label' => 'Justified'),
      'thumbnail' => array('icon' => 'dashicons-format-image', 'label' => '썸네일형'),
      'slide' => array('icon' => 'dashicons-slides', 'label' => '슬라이드형'),
    );
    ?>
    <div class="wrap uw-gallery-wrap">
      <h1><?php echo $is_edit ? '갤러리 편집' : '새 갤러리 추가'; ?></h1>

      <form id="uw-gallery-form" class="uw-gallery-editor uw-gallery-editor-fullwidth">
        <input type="hidden" name="gallery_id" value="<?php echo $gallery_id; ?>">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('uw_gallery_admin'); ?>">

        <!-- 상단: 제목 -->
        <div class="uw-editor-header">
          <div class="uw-editor-title-wrap">
            <input type="text" id="gallery_title" name="title" value="<?php echo esc_attr($title); ?>"
              placeholder="갤러리 제목을 입력하세요" required>
          </div>
        </div>

        <!-- 레이아웃 + 공개 상태 (한 줄) -->
        <div class="uw-editor-meta-row">
          <!-- 레이아웃 선택 (아이콘 카드) -->
          <div class="uw-layout-section">
            <h3>레이아웃</h3>
            <div class="uw-layout-cards">
              <?php foreach ($layouts as $key => $layout): ?>
                <label class="uw-layout-card <?php echo $settings['_uw_gallery_layout'] === $key ? 'is-selected' : ''; ?>">
                  <input type="radio" name="layout" value="<?php echo $key; ?>" <?php checked($settings['_uw_gallery_layout'], $key); ?>>
                  <span class="dashicons <?php echo $layout['icon']; ?>"></span>
                  <span class="uw-layout-label"><?php echo $layout['label']; ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- 공개 상태 박스 -->
          <div class="uw-publish-box">
            <h3>공개</h3>
            <div class="uw-publish-row">
              <span class="dashicons dashicons-post-status"></span>
              <span>상태:
                <strong><?php echo $post_status === 'publish' ? '발행됨' : ($post_status === 'private' ? '비공개' : '초안'); ?></strong></span>
            </div>
            <div class="uw-publish-row">
              <span class="dashicons dashicons-calendar-alt"></span>
              <label>발행일:
                <input type="date" name="publish_date"
                  value="<?php echo $is_edit ? get_the_date('Y-m-d', $gallery) : date('Y-m-d'); ?>" class="uw-date-input">
              </label>
            </div>

            <input type="hidden" name="post_status" value="<?php echo esc_attr($post_status); ?>">
            <input type="hidden" name="visibility" value="<?php echo esc_attr($visibility); ?>">

            <div class="uw-publish-actions">
              <?php if ($is_edit): ?>
                <a href="#" class="uw-btn-link uw-gallery-delete-single" data-id="<?php echo $gallery_id; ?>">휴지통</a>
              <?php else: ?>
                <span></span>
              <?php endif; ?>
              <button type="submit" class="button button-primary">
                <?php echo $is_edit ? '업데이트' : '발행'; ?>
              </button>
            </div>
          </div>
        </div>

        <!-- 이미지 관리 영역 -->
        <div class="uw-editor-section">
          <h2>갤러리 이미지</h2>
          <div class="uw-gallery-images-wrap">
            <ul id="uw-gallery-items" class="uw-gallery-items">
              <?php foreach ($items as $index => $item):
                $thumb_url = '';
                $custom_thumb_id = isset($item['custom_thumb_id']) ? $item['custom_thumb_id'] : 0;

                if ($item['type'] === 'video') {
                  // 커스텀 썸네일이 있으면 우선 사용
                  if ($custom_thumb_id) {
                    $thumb_url = wp_get_attachment_image_url($custom_thumb_id, 'thumbnail');
                  } else {
                    $thumb_url = $this->get_video_thumbnail($item['video_url']);
                  }
                } else {
                  $thumb_id = isset($item['thumb_id']) ? $item['thumb_id'] : $item['id'];
                  $thumb_url = wp_get_attachment_image_url($thumb_id, 'thumbnail');
                }
                $item_title = isset($item['title']) ? $item['title'] : '';
                $item_desc = isset($item['description']) ? $item['description'] : '';
                $item_cats = isset($item['categories']) ? $item['categories'] : array();
                ?>
                <li class="uw-gallery-item" data-index="<?php echo $index; ?>">
                  <input type="hidden" name="items[<?php echo $index; ?>][id]" value="<?php echo $item['id']; ?>">
                  <input type="hidden" name="items[<?php echo $index; ?>][thumb_id]"
                    value="<?php echo isset($item['thumb_id']) ? $item['thumb_id'] : $item['id']; ?>">
                  <input type="hidden" name="items[<?php echo $index; ?>][custom_thumb_id]"
                    value="<?php echo $custom_thumb_id; ?>" class="uw-item-custom-thumb-input">
                  <input type="hidden" name="items[<?php echo $index; ?>][type]" value="<?php echo $item['type']; ?>">
                  <input type="hidden" name="items[<?php echo $index; ?>][video_url]"
                    value="<?php echo esc_attr($item['video_url'] ?? ''); ?>">
                  <input type="hidden" name="items[<?php echo $index; ?>][title]" value="<?php echo esc_attr($item_title); ?>"
                    class="uw-item-title-input">
                  <input type="hidden" name="items[<?php echo $index; ?>][description]"
                    value="<?php echo esc_attr($item_desc); ?>" class="uw-item-desc-input">
                  <input type="hidden" name="items[<?php echo $index; ?>][categories]"
                    value="<?php echo esc_attr(implode(',', $item_cats)); ?>" class="uw-item-cats-input">

                  <div class="uw-item-thumb">
                    <?php if ($thumb_url): ?>
                      <img src="<?php echo esc_url($thumb_url); ?>" alt="">
                    <?php endif; ?>
                    <?php if ($item['type'] === 'video'): ?>
                      <span class="uw-video-badge">▶</span>
                    <?php endif; ?>
                  </div>
                  <div class="uw-item-actions">
                    <button type="button" class="uw-item-edit" title="편집">✎</button>
                    <button type="button" class="uw-item-remove" title="삭제">×</button>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>

            <div class="uw-gallery-add-buttons">
              <button type="button" id="uw-add-images" class="button button-primary button-large" aria-label="미디어 라이브러리에서 이미지 추가">
                <span class="dashicons dashicons-images-alt2" aria-hidden="true"></span> 미디어 라이브러리에서 추가
              </button>
              <button type="button" id="uw-add-video" class="button button-large" aria-label="YouTube 또는 Vimeo 동영상 추가">
                <span class="dashicons dashicons-video-alt3" aria-hidden="true"></span> 동영상 추가
              </button>
            </div>
          </div>
        </div>

        <!-- 공통 필수 옵션 -->
        <div class="uw-editor-section">
          <h2>공통 옵션</h2>
          <div class="uw-settings-grid">
            <div class="uw-setting-item">
              <label>이미지 간격</label>
              <div class="uw-range-wrap">
                <input type="range" name="gutter" min="0" max="50"
                  value="<?php echo $settings['_uw_gallery_gutter'] ?? 15; ?>">
                <span class="uw-range-value"><?php echo $settings['_uw_gallery_gutter'] ?? 15; ?>px</span>
              </div>
            </div>
            <div class="uw-setting-item">
              <label>테두리 두께</label>
              <div class="uw-range-wrap">
                <input type="range" name="border_width" min="0" max="5"
                  value="<?php echo $settings['_uw_gallery_border_width'] ?? 0; ?>">
                <span class="uw-range-value"><?php echo $settings['_uw_gallery_border_width'] ?? 0; ?>px</span>
              </div>
            </div>
            <div class="uw-setting-item">
              <label>모서리 곡률</label>
              <div class="uw-range-wrap">
                <input type="range" name="border_radius" min="0" max="30"
                  value="<?php echo $settings['_uw_gallery_border_radius'] ?? 8; ?>">
                <span class="uw-range-value"><?php echo $settings['_uw_gallery_border_radius'] ?? 8; ?>px</span>
              </div>
            </div>
            <div class="uw-setting-item">
              <label>호버 효과</label>
              <select name="hover_effect">
                <option value="none" <?php selected($settings['_uw_gallery_hover_effect'] ?? 'zoom', 'none'); ?>>없음</option>
                <option value="zoom" <?php selected($settings['_uw_gallery_hover_effect'] ?? 'zoom', 'zoom'); ?>>줌 인
                </option>
                <option value="fade" <?php selected($settings['_uw_gallery_hover_effect'] ?? 'zoom', 'fade'); ?>>페이드 아웃
                </option>
                <option value="overlay" <?php selected($settings['_uw_gallery_hover_effect'] ?? 'zoom', 'overlay'); ?>>색상
                  오버레이</option>
              </select>
            </div>
            <div class="uw-setting-item">
              <label>라이트박스 테마</label>
              <select name="lightbox_theme">
                <option value="dark" <?php selected($settings['_uw_gallery_lightbox_theme'] ?? 'dark', 'dark'); ?>>다크
                </option>
                <option value="light" <?php selected($settings['_uw_gallery_lightbox_theme'] ?? 'dark', 'light'); ?>>라이트
                </option>
              </select>
            </div>
            <div class="uw-setting-item">
              <label>
                <input type="checkbox" name="lazy_load" value="1" <?php checked($settings['_uw_gallery_lazy_load'] ?? true, true); ?>>
                레이지 로딩
              </label>
            </div>
            <div class="uw-setting-item">
              <label>
                <input type="checkbox" name="lightbox" value="1" <?php checked($settings['_uw_gallery_lightbox'] ?? true, true); ?>>
                라이트박스 사용
              </label>
            </div>
            <div class="uw-setting-item">
              <label>
                <input type="checkbox" name="show_filter" value="1" <?php checked($settings['_uw_gallery_show_filter'] ?? false, true); ?>>
                카테고리 필터 표시
              </label>
            </div>
          </div>
        </div>

        <!-- 텍스트 노출 설정 -->
        <div class="uw-editor-section">
          <h2>텍스트 노출 설정</h2>
          <div class="uw-settings-grid">
            <div class="uw-setting-item">
              <label>텍스트 위치</label>
              <select name="text_position">
                <option value="bottom" <?php selected($settings['_uw_gallery_text_position'] ?? 'bottom', 'bottom'); ?>>이미지
                  하단</option>
                <option value="overlay" <?php selected($settings['_uw_gallery_text_position'] ?? 'bottom', 'overlay'); ?>>
                  이미지 오버레이</option>
              </select>
            </div>
            <div class="uw-setting-item">
              <label>텍스트 정렬</label>
              <select name="text_align">
                <option value="left" <?php selected($settings['_uw_gallery_text_align'] ?? 'left', 'left'); ?>>왼쪽</option>
                <option value="center" <?php selected($settings['_uw_gallery_text_align'] ?? 'left', 'center'); ?>>중앙
                </option>
                <option value="right" <?php selected($settings['_uw_gallery_text_align'] ?? 'left', 'right'); ?>>오른쪽
                </option>
              </select>
            </div>
            <div class="uw-setting-item">
              <label>오버레이 투명도</label>
              <div class="uw-range-wrap">
                <input type="range" name="overlay_opacity" min="0" max="100"
                  value="<?php echo $settings['_uw_gallery_overlay_opacity'] ?? 70; ?>">
                <span class="uw-range-value"><?php echo $settings['_uw_gallery_overlay_opacity'] ?? 70; ?>%</span>
              </div>
            </div>
            <div class="uw-setting-item">
              <label>
                <input type="checkbox" name="show_title" value="1" <?php checked($settings['_uw_gallery_show_title'] ?? true, true); ?>>
                제목 표시
              </label>
            </div>
            <div class="uw-setting-item">
              <label>
                <input type="checkbox" name="show_description" value="1" <?php checked($settings['_uw_gallery_show_description'] ?? false, true); ?>>
                설명 표시
              </label>
            </div>
          </div>
        </div>

        <!-- 레이아웃별 옵션 패널 (동적 표시) -->
        <div class="uw-editor-section uw-layout-options-section">
          <h2>레이아웃 옵션</h2>

          <!-- Grid 옵션 -->
          <div class="uw-layout-options" data-layout="grid"
            style="<?php echo ($settings['_uw_gallery_layout'] === 'grid') ? '' : 'display:none;'; ?>">
            <div class="uw-settings-grid">
              <div class="uw-setting-item">
                <label>PC 열 개수</label>
                <select name="grid_columns_pc">
                  <?php for ($i = 1; $i <= 6; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php selected($settings['_uw_gallery_grid_columns_pc'] ?? 4, $i); ?>>
                      <?php echo $i; ?>열
                    </option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="uw-setting-item">
                <label>태블릿 열 개수</label>
                <select name="grid_columns_tablet">
                  <?php for ($i = 1; $i <= 4; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php selected($settings['_uw_gallery_grid_columns_tablet'] ?? 3, $i); ?>><?php echo $i; ?>열</option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="uw-setting-item">
                <label>모바일 열 개수</label>
                <select name="grid_columns_mobile">
                  <?php for ($i = 1; $i <= 3; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php selected($settings['_uw_gallery_grid_columns_mobile'] ?? 2, $i); ?>><?php echo $i; ?>열</option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="uw-setting-item">
                <label>이미지 비율</label>
                <select name="grid_ratio">
                  <option value="original" <?php selected($settings['_uw_gallery_grid_ratio'] ?? '1:1', 'original'); ?>>원본
                    비율</option>
                  <option value="1:1" <?php selected($settings['_uw_gallery_grid_ratio'] ?? '1:1', '1:1'); ?>>1:1 (정사각형)
                  </option>
                  <option value="4:3" <?php selected($settings['_uw_gallery_grid_ratio'] ?? '1:1', '4:3'); ?>>4:3</option>
                  <option value="16:9" <?php selected($settings['_uw_gallery_grid_ratio'] ?? '1:1', '16:9'); ?>>16:9 (와이드)
                  </option>
                </select>
              </div>
            </div>
          </div>

          <!-- Masonry 옵션 -->
          <div class="uw-layout-options" data-layout="masonry"
            style="<?php echo ($settings['_uw_gallery_layout'] === 'masonry') ? '' : 'display:none;'; ?>">
            <div class="uw-settings-grid">
              <div class="uw-setting-item">
                <label>열 모드</label>
                <select name="masonry_column_mode">
                  <option value="fixed" <?php selected($settings['_uw_gallery_masonry_column_mode'] ?? 'fixed', 'fixed'); ?>>고정 열</option>
                  <option value="auto" <?php selected($settings['_uw_gallery_masonry_column_mode'] ?? 'fixed', 'auto'); ?>>
                    자동 채우기</option>
                </select>
              </div>
              <div class="uw-setting-item">
                <label>열 개수</label>
                <select name="masonry_columns">
                  <?php for ($i = 1; $i <= 6; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php selected($settings['_uw_gallery_masonry_columns'] ?? 4, $i); ?>>
                      <?php echo $i; ?>열
                    </option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="uw-setting-item">
                <label>정렬 기준</label>
                <select name="masonry_sort">
                  <option value="order" <?php selected($settings['_uw_gallery_masonry_sort'] ?? 'order', 'order'); ?>>등록순
                  </option>
                  <option value="height" <?php selected($settings['_uw_gallery_masonry_sort'] ?? 'order', 'height'); ?>>높이순
                  </option>
                </select>
              </div>
            </div>
          </div>

          <!-- Justified 옵션 -->
          <div class="uw-layout-options" data-layout="justified"
            style="<?php echo ($settings['_uw_gallery_layout'] === 'justified') ? '' : 'display:none;'; ?>">
            <div class="uw-settings-grid">
              <div class="uw-setting-item">
                <label>최소 행 높이</label>
                <div class="uw-range-wrap">
                  <input type="range" name="justified_row_height_min" min="100" max="400"
                    value="<?php echo $settings['_uw_gallery_justified_row_height_min'] ?? 200; ?>">
                  <span
                    class="uw-range-value"><?php echo $settings['_uw_gallery_justified_row_height_min'] ?? 200; ?>px</span>
                </div>
              </div>
              <div class="uw-setting-item">
                <label>최대 행 높이</label>
                <div class="uw-range-wrap">
                  <input type="range" name="justified_row_height_max" min="200" max="500"
                    value="<?php echo $settings['_uw_gallery_justified_row_height_max'] ?? 300; ?>">
                  <span
                    class="uw-range-value"><?php echo $settings['_uw_gallery_justified_row_height_max'] ?? 300; ?>px</span>
                </div>
              </div>
              <div class="uw-setting-item">
                <label>마지막 행 처리</label>
                <select name="justified_last_row">
                  <option value="left" <?php selected($settings['_uw_gallery_justified_last_row'] ?? 'left', 'left'); ?>>왼쪽
                    정렬</option>
                  <option value="justify" <?php selected($settings['_uw_gallery_justified_last_row'] ?? 'left', 'justify'); ?>>꽉 채우기</option>
                  <option value="hide" <?php selected($settings['_uw_gallery_justified_last_row'] ?? 'left', 'hide'); ?>>숨기기
                  </option>
                </select>
              </div>
            </div>
          </div>

          <!-- Thumbnail 옵션 -->
          <div class="uw-layout-options" data-layout="thumbnail"
            style="<?php echo ($settings['_uw_gallery_layout'] === 'thumbnail') ? '' : 'display:none;'; ?>">
            <div class="uw-settings-grid">
              <div class="uw-setting-item">
                <label>메인 이미지 비율</label>
                <select name="thumbnail_main_ratio">
                  <option value="16:9" <?php selected($settings['_uw_gallery_thumbnail_main_ratio'] ?? '16:9', '16:9'); ?>>
                    16:9 (와이드)</option>
                  <option value="4:3" <?php selected($settings['_uw_gallery_thumbnail_main_ratio'] ?? '16:9', '4:3'); ?>>4:3
                  </option>
                  <option value="1:1" <?php selected($settings['_uw_gallery_thumbnail_main_ratio'] ?? '16:9', '1:1'); ?>>1:1
                    (정사각형)</option>
                </select>
              </div>
              <div class="uw-setting-item">
                <label>썸네일 위치</label>
                <select name="thumbnail_position">
                  <option value="bottom" <?php selected($settings['_uw_gallery_thumbnail_position'] ?? 'bottom', 'bottom'); ?>>하단</option>
                  <option value="right" <?php selected($settings['_uw_gallery_thumbnail_position'] ?? 'bottom', 'right'); ?>>우측</option>
                </select>
              </div>
              <div class="uw-setting-item">
                <label>전환 방식</label>
                <select name="thumbnail_transition">
                  <option value="click" <?php selected($settings['_uw_gallery_thumbnail_transition'] ?? 'click', 'click'); ?>>클릭</option>
                  <option value="hover" <?php selected($settings['_uw_gallery_thumbnail_transition'] ?? 'click', 'hover'); ?>>호버</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Slide 옵션 -->
          <div class="uw-layout-options" data-layout="slide"
            style="<?php echo ($settings['_uw_gallery_layout'] === 'slide') ? '' : 'display:none;'; ?>">
            <div class="uw-settings-grid">
              <div class="uw-setting-item">
                <label>
                  <input type="checkbox" name="slide_autoplay" value="1" <?php checked($settings['_uw_gallery_slide_autoplay'] ?? false, true); ?>>
                  자동 재생
                </label>
              </div>
              <div class="uw-setting-item">
                <label>재생 속도</label>
                <div class="uw-range-wrap">
                  <input type="range" name="slide_speed" min="1000" max="10000" step="500"
                    value="<?php echo $settings['_uw_gallery_slide_speed'] ?? 5000; ?>">
                  <span class="uw-range-value"><?php echo ($settings['_uw_gallery_slide_speed'] ?? 5000) / 1000; ?>초</span>
                </div>
              </div>
              <div class="uw-setting-item">
                <label>
                  <input type="checkbox" name="slide_arrows" value="1" <?php checked($settings['_uw_gallery_slide_arrows'] ?? true, true); ?>>
                  화살표 표시
                </label>
              </div>
              <div class="uw-setting-item">
                <label>
                  <input type="checkbox" name="slide_dots" value="1" <?php checked($settings['_uw_gallery_slide_dots'] ?? true, true); ?>>
                  점 표시
                </label>
              </div>
              <div class="uw-setting-item">
                <label>
                  <input type="checkbox" name="slide_loop" value="1" <?php checked($settings['_uw_gallery_slide_loop'] ?? true, true); ?>>
                  무한 루프
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- 커스텀 CSS -->
        <div class="uw-editor-section">
          <h2>커스텀 CSS</h2>
          <textarea name="custom_css" rows="8"
            placeholder="이 갤러리에만 적용될 CSS를 입력하세요"><?php echo esc_textarea($settings['_uw_gallery_custom_css']); ?></textarea>
        </div>

        <?php if ($is_edit): ?>
          <!-- 숏코드 -->
          <div class="uw-editor-section uw-shortcode-section">
            <h2>숏코드</h2>
            <div class="uw-shortcode-display-large">
              <code>[uw_gallery id="<?php echo $gallery_id; ?>"]</code>
              <button type="button" class="button uw-copy-shortcode"
                data-shortcode='[uw_gallery id="<?php echo $gallery_id; ?>"]'>
                복사
              </button>
            </div>
          </div>
        <?php endif; ?>

      </form>
    </div>

    <!-- 비디오 추가 모달 -->
    <div id="uw-video-modal" class="uw-modal" style="display:none;">
      <div class="uw-modal-content">
        <h3>동영상 추가</h3>
        <div class="uw-form-field">
          <label>YouTube 또는 Vimeo URL</label>
          <input type="url" id="uw-video-url" placeholder="https://www.youtube.com/watch?v=...">
        </div>
        <div class="uw-modal-actions">
          <button type="button" id="uw-video-confirm" class="button button-primary">추가</button>
          <button type="button" id="uw-video-cancel" class="button">취소</button>
        </div>
      </div>
    </div>

    <!-- 이미지 편집 모달 -->
    <div id="uw-item-edit-modal" class="uw-modal" style="display:none;">
      <div class="uw-modal-content uw-modal-wide">
        <h3>이미지 정보 편집</h3>
        <input type="hidden" id="uw-edit-item-index">
        <input type="hidden" id="uw-edit-item-type">
        <div class="uw-form-field">
          <label>이미지 제목</label>
          <input type="text" id="uw-edit-item-title" placeholder="이미지 제목을 입력하세요">
        </div>
        <div class="uw-form-field">
          <label>상세 설명</label>
          <textarea id="uw-edit-item-description" rows="3" placeholder="이미지 설명을 입력하세요"></textarea>
        </div>
        <div class="uw-form-field">
          <label>카테고리 (쉼표로 구분)</label>
          <input type="text" id="uw-edit-item-categories" placeholder="예: 풍경, 자연, 여행">
          <p class="description">여러 카테고리를 쉼표(,)로 구분하여 입력하세요.</p>
        </div>
        <!-- 비디오 전용: 커스텀 썸네일 -->
        <div class="uw-form-field uw-video-only" style="display:none;">
          <label>커스텀 썸네일</label>
          <input type="hidden" id="uw-edit-item-custom-thumb-id" value="0">
          <div class="uw-custom-thumb-preview">
            <img id="uw-custom-thumb-preview-img" src="" alt="" style="display:none; max-width: 200px; height: auto; border-radius: 4px;">
            <p id="uw-custom-thumb-placeholder" class="description">자동 썸네일 사용 중</p>
          </div>
          <div style="margin-top: 10px;">
            <button type="button" id="uw-upload-custom-thumb" class="button">커스텀 썸네일 업로드</button>
            <button type="button" id="uw-remove-custom-thumb" class="button" style="display:none;">썸네일 제거</button>
          </div>
          <p class="description">비디오의 자동 썸네일 대신 사용할 이미지를 업로드할 수 있습니다.</p>
        </div>
        <div class="uw-modal-actions">
          <button type="button" id="uw-edit-item-save" class="button button-primary">저장</button>
          <button type="button" id="uw-edit-item-cancel" class="button">취소</button>
        </div>
      </div>
    </div>
    <?php
  }

  /**
   * AJAX: 갤러리 저장
   */
  public function ajax_save_gallery()
  {
    check_ajax_referer('uw_gallery_admin', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error(array('message' => '권한이 없습니다.'));
    }

    $gallery_id = isset($_POST['gallery_id']) ? absint($_POST['gallery_id']) : 0;
    $title = sanitize_text_field($_POST['title'] ?? '');

    if (empty($title)) {
      wp_send_json_error(array('message' => '제목을 입력해주세요.'));
    }

    // 발행일 처리
    $publish_date = isset($_POST['publish_date']) ? sanitize_text_field($_POST['publish_date']) : '';

    // 갤러리 생성/업데이트
    $post_data = array(
      'post_title' => $title,
      'post_type' => 'uw_gallery',
      'post_status' => sanitize_key($_POST['post_status'] ?? 'publish'),
    );

    // 발행일이 유효하면 추가
    if (!empty($publish_date) && strtotime($publish_date)) {
      $post_data['post_date'] = $publish_date . ' 00:00:00';
      $post_data['post_date_gmt'] = get_gmt_from_date($publish_date . ' 00:00:00');
    }

    if ($gallery_id) {
      $post_data['ID'] = $gallery_id;
      wp_update_post($post_data);
    } else {
      $gallery_id = wp_insert_post($post_data);
      // 최초 생성일 저장 (한 번만)
      if ($gallery_id && !is_wp_error($gallery_id)) {
        update_post_meta($gallery_id, '_uw_gallery_created_at', current_time('mysql'));
      }
    }

    if (is_wp_error($gallery_id)) {
      wp_send_json_error(array('message' => '저장에 실패했습니다.'));
    }

    // 아이템 저장 (새 구조)
    $items = array();
    if (!empty($_POST['items']) && is_array($_POST['items'])) {
      foreach ($_POST['items'] as $index => $item) {
        $categories = array();
        if (!empty($item['categories'])) {
          $categories = array_map('trim', explode(',', sanitize_text_field($item['categories'])));
          $categories = array_filter($categories);
        }

        $items[] = array(
          'id' => absint($item['id'] ?? 0),
          'thumb_id' => absint($item['thumb_id'] ?? 0),
          'custom_thumb_id' => absint($item['custom_thumb_id'] ?? 0),
          'type' => sanitize_key($item['type'] ?? 'image'),
          'video_url' => esc_url_raw($item['video_url'] ?? ''),
          'title' => sanitize_text_field($item['title'] ?? ''),
          'description' => sanitize_textarea_field($item['description'] ?? ''),
          'categories' => $categories,
          'order' => $index,
        );
      }
    }

    update_post_meta($gallery_id, '_uw_gallery_items', $items);
    update_post_meta($gallery_id, '_uw_gallery_layout', sanitize_key($_POST['layout'] ?? 'grid'));

    // 공통 옵션
    update_post_meta($gallery_id, '_uw_gallery_gutter', absint($_POST['gutter'] ?? 15));
    update_post_meta($gallery_id, '_uw_gallery_border_width', absint($_POST['border_width'] ?? 0));
    update_post_meta($gallery_id, '_uw_gallery_border_radius', absint($_POST['border_radius'] ?? 8));
    update_post_meta($gallery_id, '_uw_gallery_hover_effect', sanitize_key($_POST['hover_effect'] ?? 'zoom'));
    update_post_meta($gallery_id, '_uw_gallery_lightbox_theme', sanitize_key($_POST['lightbox_theme'] ?? 'dark'));
    update_post_meta($gallery_id, '_uw_gallery_lazy_load', isset($_POST['lazy_load']));
    update_post_meta($gallery_id, '_uw_gallery_lightbox', isset($_POST['lightbox']));
    update_post_meta($gallery_id, '_uw_gallery_show_filter', isset($_POST['show_filter']));

    // 텍스트 옵션
    update_post_meta($gallery_id, '_uw_gallery_text_position', sanitize_key($_POST['text_position'] ?? 'bottom'));
    update_post_meta($gallery_id, '_uw_gallery_text_align', sanitize_key($_POST['text_align'] ?? 'left'));
    update_post_meta($gallery_id, '_uw_gallery_overlay_opacity', absint($_POST['overlay_opacity'] ?? 70));
    update_post_meta($gallery_id, '_uw_gallery_show_title', isset($_POST['show_title']));
    update_post_meta($gallery_id, '_uw_gallery_show_description', isset($_POST['show_description']));

    // Grid 옵션
    update_post_meta($gallery_id, '_uw_gallery_grid_columns_pc', absint($_POST['grid_columns_pc'] ?? 4));
    update_post_meta($gallery_id, '_uw_gallery_grid_columns_tablet', absint($_POST['grid_columns_tablet'] ?? 3));
    update_post_meta($gallery_id, '_uw_gallery_grid_columns_mobile', absint($_POST['grid_columns_mobile'] ?? 2));
    update_post_meta($gallery_id, '_uw_gallery_grid_ratio', sanitize_text_field($_POST['grid_ratio'] ?? '1:1'));

    // Masonry 옵션
    update_post_meta($gallery_id, '_uw_gallery_masonry_column_mode', sanitize_key($_POST['masonry_column_mode'] ?? 'fixed'));
    update_post_meta($gallery_id, '_uw_gallery_masonry_columns', absint($_POST['masonry_columns'] ?? 4));
    update_post_meta($gallery_id, '_uw_gallery_masonry_sort', sanitize_key($_POST['masonry_sort'] ?? 'order'));

    // Justified 옵션
    update_post_meta($gallery_id, '_uw_gallery_justified_row_height_min', absint($_POST['justified_row_height_min'] ?? 200));
    update_post_meta($gallery_id, '_uw_gallery_justified_row_height_max', absint($_POST['justified_row_height_max'] ?? 300));
    update_post_meta($gallery_id, '_uw_gallery_justified_last_row', sanitize_key($_POST['justified_last_row'] ?? 'left'));

    // Thumbnail 옵션
    update_post_meta($gallery_id, '_uw_gallery_thumbnail_main_ratio', sanitize_text_field($_POST['thumbnail_main_ratio'] ?? '16:9'));
    update_post_meta($gallery_id, '_uw_gallery_thumbnail_position', sanitize_key($_POST['thumbnail_position'] ?? 'bottom'));
    update_post_meta($gallery_id, '_uw_gallery_thumbnail_transition', sanitize_key($_POST['thumbnail_transition'] ?? 'click'));

    // Slide 옵션
    update_post_meta($gallery_id, '_uw_gallery_slide_autoplay', isset($_POST['slide_autoplay']));
    update_post_meta($gallery_id, '_uw_gallery_slide_speed', absint($_POST['slide_speed'] ?? 5000));
    update_post_meta($gallery_id, '_uw_gallery_slide_arrows', isset($_POST['slide_arrows']));
    update_post_meta($gallery_id, '_uw_gallery_slide_dots', isset($_POST['slide_dots']));
    update_post_meta($gallery_id, '_uw_gallery_slide_loop', isset($_POST['slide_loop']));

    // 기타
    update_post_meta($gallery_id, '_uw_gallery_custom_css', wp_strip_all_tags($_POST['custom_css'] ?? ''));
    update_post_meta($gallery_id, '_uw_gallery_visibility', sanitize_key($_POST['visibility'] ?? 'public'));

    // 하위 호환
    update_post_meta($gallery_id, '_uw_gallery_columns', absint($_POST['grid_columns_pc'] ?? 4));
    update_post_meta($gallery_id, '_uw_gallery_mobile_cols', absint($_POST['grid_columns_mobile'] ?? 2));

    wp_send_json_success(array(
      'message' => '갤러리가 저장되었습니다.',
      'gallery_id' => $gallery_id,
      'redirect' => admin_url('admin.php?page=uw-gallery-new&id=' . $gallery_id),
    ));
  }

  /**
   * AJAX: 갤러리 삭제
   */
  public function ajax_delete_gallery()
  {
    check_ajax_referer('uw_gallery_admin', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error(array('message' => '권한이 없습니다.'));
    }

    $gallery_id = absint($_POST['id'] ?? 0);

    if (!$gallery_id) {
      wp_send_json_error(array('message' => '잘못된 요청입니다.'));
    }

    $result = wp_delete_post($gallery_id, true);

    if ($result) {
      wp_send_json_success(array('message' => '갤러리가 삭제되었습니다.'));
    } else {
      wp_send_json_error(array('message' => '삭제에 실패했습니다.'));
    }
  }

  /**
   * AJAX: 일괄 작업
   */
  public function ajax_bulk_action()
  {
    check_ajax_referer('uw_gallery_admin', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error(array('message' => '권한이 없습니다.'));
    }

    $action = sanitize_key($_POST['bulk_action'] ?? '');
    $ids = isset($_POST['ids']) ? array_map('absint', $_POST['ids']) : array();

    if (empty($action) || empty($ids)) {
      wp_send_json_error(array('message' => '잘못된 요청입니다.'));
    }

    $success_count = 0;

    foreach ($ids as $gallery_id) {
      switch ($action) {
        case 'trash':
          // 휴지통으로 이동 (복구 가능)
          if (wp_trash_post($gallery_id)) {
            $success_count++;
          }
          break;

        case 'restore':
          // 휴지통에서 복구
          if (wp_untrash_post($gallery_id)) {
            $success_count++;
          }
          break;

        case 'delete':
          // 영구 삭제
          if (wp_delete_post($gallery_id, true)) {
            $success_count++;
          }
          break;

        case 'private':
          // 비공개로 전환
          $result = wp_update_post(array(
            'ID' => $gallery_id,
            'post_status' => 'private',
          ));
          if ($result && !is_wp_error($result)) {
            $success_count++;
          }
          break;

        case 'publish':
          // 공개로 전환
          $result = wp_update_post(array(
            'ID' => $gallery_id,
            'post_status' => 'publish',
          ));
          if ($result && !is_wp_error($result)) {
            $success_count++;
          }
          break;
      }
    }

    wp_send_json_success(array(
      'message' => $success_count . '개 갤러리가 처리되었습니다.',
      'count' => $success_count,
    ));
  }

  /**
   * AJAX: 갤러리 휴지통 이동 (복구 가능)
   */
  public function ajax_trash_gallery()
  {
    check_ajax_referer('uw_gallery_admin', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error(array('message' => '권한이 없습니다.'));
    }

    $gallery_id = absint($_POST['id'] ?? 0);

    if (!$gallery_id) {
      wp_send_json_error(array('message' => '잘못된 요청입니다.'));
    }

    $result = wp_trash_post($gallery_id);

    if ($result) {
      wp_send_json_success(array('message' => '갤러리가 휴지통으로 이동되었습니다.'));
    } else {
      wp_send_json_error(array('message' => '휴지통 이동에 실패했습니다.'));
    }
  }

  /**
   * AJAX: 갤러리 휴지통에서 복구
   */
  public function ajax_restore_gallery()
  {
    check_ajax_referer('uw_gallery_admin', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error(array('message' => '권한이 없습니다.'));
    }

    $gallery_id = absint($_POST['id'] ?? 0);

    if (!$gallery_id) {
      wp_send_json_error(array('message' => '잘못된 요청입니다.'));
    }

    $result = wp_untrash_post($gallery_id);

    if ($result) {
      wp_send_json_success(array('message' => '갤러리가 복구되었습니다.'));
    } else {
      wp_send_json_error(array('message' => '복구에 실패했습니다.'));
    }
  }

  /**
   * AJAX: 갤러리 상태 변경 (publish <-> private)
   */
  public function ajax_change_status()
  {
    check_ajax_referer('uw_gallery_admin', 'nonce');

    if (!current_user_can('manage_options')) {
      wp_send_json_error(array('message' => '권한이 없습니다.'));
    }

    $gallery_id = absint($_POST['id'] ?? 0);
    $new_status = sanitize_key($_POST['status'] ?? '');

    if (!$gallery_id || !in_array($new_status, array('publish', 'private'))) {
      wp_send_json_error(array('message' => '잘못된 요청입니다.'));
    }

    $result = wp_update_post(array(
      'ID' => $gallery_id,
      'post_status' => $new_status,
    ));

    if ($result && !is_wp_error($result)) {
      $status_label = $new_status === 'publish' ? '공개' : '비공개';
      wp_send_json_success(array('message' => "갤러리가 {$status_label} 상태로 변경되었습니다."));
    } else {
      wp_send_json_error(array('message' => '상태 변경에 실패했습니다.'));
    }
  }
}

// Initialize
UW_Gallery_Admin::get_instance();
