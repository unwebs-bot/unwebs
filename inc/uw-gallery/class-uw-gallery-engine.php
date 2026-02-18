<?php
/**
 * UW Gallery Frontend Engine
 * 
 * 프론트엔드 렌더링 및 숏코드 처리
 * 5종 레이아웃: grid, masonry, justified, thumbnail, slide
 * 
 * @package starter-theme
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
  exit;
}

// 비디오 Trait 로드
require_once get_theme_file_path('inc/uw-gallery/trait-uw-gallery-video.php');

class UW_Gallery_Engine
{
  use UW_Gallery_Video_Trait;

  private static $instance = null;
  private static $enqueued = false;

  public static function get_instance()
  {
    if (null === self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function __construct()
  {
    add_shortcode('uw_gallery', array($this, 'render_shortcode'));
  }

  /**
   * 프론트엔드 에셋 로드
   */
  private function enqueue_assets()
  {
    if (self::$enqueued) {
      return;
    }

    // CSS는 style.css에서 @import로 로드됨 (cpt/gallery/gallery.css)

    // Frontend JS (Vanilla)
    wp_enqueue_script(
      'uw-gallery',
      get_theme_file_uri('assets/js/CPT/gallery/uw-gallery.js'),
      array(),
      '2.0.1',
      true
    );

    self::$enqueued = true;
  }

  /**
   * 숏코드 렌더링
   * [uw_gallery id="123"]
   */
  public function render_shortcode($atts)
  {
    $atts = shortcode_atts(array(
      'id' => 0,
    ), $atts, 'uw_gallery');

    $gallery_id = absint($atts['id']);

    if (!$gallery_id) {
      return '<p class="uw-gallery-error">갤러리 ID를 지정해주세요.</p>';
    }

    $gallery = get_post($gallery_id);

    if (!$gallery || $gallery->post_type !== 'uw_gallery') {
      return '<p class="uw-gallery-error">갤러리를 찾을 수 없습니다.</p>';
    }

    // 비공개 갤러리 체크
    if ($gallery->post_status === 'private' && !current_user_can('read_private_posts')) {
      return '';
    }

    // 에셋 로드
    $this->enqueue_assets();

    // 설정 가져오기
    $settings = UW_Gallery_CPT::get_gallery_settings($gallery_id);
    $items = $settings['_uw_gallery_items'] ?: array();

    if (empty($items)) {
      return '<p class="uw-gallery-empty">갤러리에 이미지가 없습니다.</p>';
    }

    $layout = $settings['_uw_gallery_layout'] ?: 'grid';

    // 공통 옵션
    $gutter = $settings['_uw_gallery_gutter'] ?? 15;
    $border_width = $settings['_uw_gallery_border_width'] ?? 0;
    $border_radius = $settings['_uw_gallery_border_radius'] ?? 8;
    $hover_effect = $settings['_uw_gallery_hover_effect'] ?? 'zoom';
    $lightbox_theme = $settings['_uw_gallery_lightbox_theme'] ?? 'dark';
    $lazy_load = $settings['_uw_gallery_lazy_load'] ?? true;

    // 텍스트 옵션
    $text_position = $settings['_uw_gallery_text_position'] ?? 'bottom';
    $text_align = $settings['_uw_gallery_text_align'] ?? 'left';
    $overlay_opacity = $settings['_uw_gallery_overlay_opacity'] ?? 70;
    $show_title = $settings['_uw_gallery_show_title'] ?? true;
    $show_description = $settings['_uw_gallery_show_description'] ?? false;
    $show_filter = $settings['_uw_gallery_show_filter'] ?? false;
    $use_lightbox = $settings['_uw_gallery_lightbox'] ?? true;
    $custom_css = $settings['_uw_gallery_custom_css'] ?? '';

    // 레이아웃별 옵션
    $grid_columns_pc = $settings['_uw_gallery_grid_columns_pc'] ?? 4;
    $grid_columns_tablet = $settings['_uw_gallery_grid_columns_tablet'] ?? 3;
    $grid_columns_mobile = $settings['_uw_gallery_grid_columns_mobile'] ?? 2;
    $grid_ratio = $settings['_uw_gallery_grid_ratio'] ?? '1:1';

    $masonry_columns = $settings['_uw_gallery_masonry_columns'] ?? 4;

    $justified_row_height_min = $settings['_uw_gallery_justified_row_height_min'] ?? 200;
    $justified_row_height_max = $settings['_uw_gallery_justified_row_height_max'] ?? 300;
    $justified_last_row = $settings['_uw_gallery_justified_last_row'] ?? 'left';

    $thumbnail_main_ratio = $settings['_uw_gallery_thumbnail_main_ratio'] ?? '16:9';
    $thumbnail_position = $settings['_uw_gallery_thumbnail_position'] ?? 'bottom';
    $thumbnail_transition = $settings['_uw_gallery_thumbnail_transition'] ?? 'click';

    $slide_autoplay = $settings['_uw_gallery_slide_autoplay'] ?? false;
    $slide_speed = $settings['_uw_gallery_slide_speed'] ?? 5000;
    $slide_arrows = $settings['_uw_gallery_slide_arrows'] ?? true;
    $slide_dots = $settings['_uw_gallery_slide_dots'] ?? true;
    $slide_loop = $settings['_uw_gallery_slide_loop'] ?? true;

    // 하위 호환
    $columns = $grid_columns_pc;
    $mobile_cols = $grid_columns_mobile;

    // 카테고리 수집 (필터용)
    $all_categories = array();
    if ($show_filter) {
      foreach ($items as $item) {
        if (!empty($item['categories']) && is_array($item['categories'])) {
          foreach ($item['categories'] as $cat) {
            $cat = trim($cat);
            if ($cat && !in_array($cat, $all_categories)) {
              $all_categories[] = $cat;
            }
          }
        }
      }
    }

    // aspect-ratio 변환
    $ratio_map = array(
      '1:1' => '1 / 1',
      '4:3' => '4 / 3',
      '16:9' => '16 / 9',
      'original' => 'auto'
    );
    $css_ratio = isset($ratio_map[$grid_ratio]) ? $ratio_map[$grid_ratio] : 'auto';
    $main_ratio_val = isset($ratio_map[$thumbnail_main_ratio]) ? $ratio_map[$thumbnail_main_ratio] : '16 / 9';

    ob_start();

    // 커스텀 CSS
    if ($custom_css): ?>
      <style>
        .uw-gallery-<?php echo $gallery_id; ?> {
          <?php echo wp_strip_all_tags($custom_css); ?>
        }
      </style>
    <?php endif; ?>

    <div
      class="uw-gallery uw-gallery-<?php echo $gallery_id; ?> uw-gallery--<?php echo esc_attr($layout); ?> uw-gallery--text-<?php echo esc_attr($text_position); ?> uw-gallery--hover-<?php echo esc_attr($hover_effect); ?> uw-gallery--lightbox-<?php echo esc_attr($lightbox_theme); ?>"
      data-layout="<?php echo esc_attr($layout); ?>" data-columns="<?php echo $columns; ?>"
      data-mobile-columns="<?php echo $mobile_cols; ?>" data-lightbox="<?php echo $use_lightbox ? 'true' : 'false'; ?>"
      data-hover="<?php echo esc_attr($hover_effect); ?>" data-lightbox-theme="<?php echo esc_attr($lightbox_theme); ?>"
      data-lazy="<?php echo $lazy_load ? 'true' : 'false'; ?>"
      data-thumb-position="<?php echo esc_attr($thumbnail_position); ?>"
      data-thumb-transition="<?php echo esc_attr($thumbnail_transition); ?>"
      data-slide-autoplay="<?php echo $slide_autoplay ? 'true' : 'false'; ?>" data-slide-speed="<?php echo $slide_speed; ?>"
      data-slide-arrows="<?php echo $slide_arrows ? 'true' : 'false'; ?>"
      data-slide-dots="<?php echo $slide_dots ? 'true' : 'false'; ?>"
      data-slide-loop="<?php echo $slide_loop ? 'true' : 'false'; ?>" style="
        --uw-gallery-columns: <?php echo $columns; ?>;
        --uw-gallery-columns-tablet: <?php echo $grid_columns_tablet; ?>;
        --uw-gallery-mobile-columns: <?php echo $mobile_cols; ?>;
        --uw-gallery-gap: <?php echo $gutter; ?>px;
        --uw-gallery-border-width: <?php echo $border_width; ?>px;
        --uw-gallery-border-radius: <?php echo $border_radius; ?>px;
        --uw-gallery-overlay-opacity: <?php echo $overlay_opacity / 100; ?>;
        --uw-gallery-text-align: <?php echo $text_align; ?>;
        --uw-gallery-ratio: <?php echo $css_ratio; ?>;
        --uw-gallery-main-ratio: <?php echo $main_ratio_val; ?>;
        --uw-gallery-row-height-min: <?php echo $justified_row_height_min; ?>px;
        --uw-gallery-row-height-max: <?php echo $justified_row_height_max; ?>px;
        --uw-masonry-columns: <?php echo $masonry_columns; ?>;
      ">>

      <?php
      // 카테고리 필터 바 (탭 + 드롭다운 병행)
      if ($show_filter && !empty($all_categories)): ?>
        <div class="uw-gallery-filter">
          <!-- 데스크톱: 탭 버튼 -->
          <div class="uw-gallery-filter-tabs">
            <button type="button" class="uw-filter-btn is-active" data-filter="*">전체</button>
            <?php foreach ($all_categories as $cat): ?>
              <button type="button" class="uw-filter-btn" data-filter="<?php echo esc_attr($cat); ?>">
                <?php echo esc_html($cat); ?>
              </button>
            <?php endforeach; ?>
          </div>

          <!-- 모바일: 드롭다운 -->
          <select class="uw-gallery-filter-select">
            <option value="*">전체</option>
            <?php foreach ($all_categories as $cat): ?>
              <option value="<?php echo esc_attr($cat); ?>"><?php echo esc_html($cat); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>

      <?php
      // 레이아웃별 렌더링
      switch ($layout):
        case 'thumbnail':
          $this->render_thumbnail_layout($items, $gallery_id, $show_title, $show_description, $use_lightbox);
          break;
        case 'slide':
          $this->render_slide_layout($items, $gallery_id, $show_title, $show_description, $use_lightbox);
          break;
        default:
          $this->render_grid_layout($items, $gallery_id, $show_title, $show_description, $use_lightbox, $text_position);
          break;
      endswitch;
      ?>

    </div>

    <?php
    return ob_get_clean();
  }

  /**
   * 그리드/Masonry/Justified 레이아웃 (공통)
   */
  private function render_grid_layout($items, $gallery_id, $show_title, $show_description, $use_lightbox, $text_position)
  {
    ?>
    <div class="uw-gallery-grid">
      <?php foreach ($items as $item):
        $is_video = isset($item['type']) && $item['type'] === 'video';
        $thumb_id = isset($item['thumb_id']) && $item['thumb_id'] ? $item['thumb_id'] : $item['id'];
        $custom_thumb_id = isset($item['custom_thumb_id']) ? $item['custom_thumb_id'] : 0;

        // 썸네일 URL: 비디오의 경우 커스텀 썸네일 우선, 없으면 자동 썸네일
        if ($is_video) {
          if ($custom_thumb_id) {
            $thumb_url = wp_get_attachment_image_url($custom_thumb_id, 'gallery-thumb');
          } else {
            $thumb_url = $this->get_video_thumbnail($item['video_url']);
          }
        } else {
          $thumb_url = wp_get_attachment_image_url($thumb_id, 'gallery-thumb');
        }

        $full_url = $is_video
          ? $this->get_video_embed_url($item['video_url'])
          : wp_get_attachment_image_url($item['id'], 'gallery-full');

        $title = isset($item['title']) ? $item['title'] : '';
        $description = isset($item['description']) ? $item['description'] : '';
        $categories = isset($item['categories']) && is_array($item['categories'])
          ? implode(' ', array_map('sanitize_title', $item['categories']))
          : '';
        $cat_data = isset($item['categories']) && is_array($item['categories'])
          ? implode(',', $item['categories'])
          : '';
        ?>
        <div class="uw-gallery-item <?php echo $categories; ?>" data-type="<?php echo $is_video ? 'video' : 'image'; ?>"
          data-categories="<?php echo esc_attr($cat_data); ?>">

          <?php if ($use_lightbox): ?>
            <a href="<?php echo esc_url($full_url); ?>" class="uw-gallery-link"
              data-lightbox="gallery-<?php echo $gallery_id; ?>" data-type="<?php echo $is_video ? 'video' : 'image'; ?>"
              data-title="<?php echo esc_attr($title); ?>">
            <?php endif; ?>

            <div class="uw-gallery-thumb">
              <?php if ($thumb_url): ?>
                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
              <?php endif; ?>

              <?php if ($is_video): ?>
                <span class="uw-gallery-play-icon">▶</span>
              <?php endif; ?>

              <?php if ($text_position === 'overlay' && ($show_title || $show_description)): ?>
                <div class="uw-gallery-overlay">
                  <?php if ($show_title && $title): ?>
                    <h4 class="uw-gallery-item-title"><?php echo esc_html($title); ?></h4>
                  <?php endif; ?>
                  <?php if ($show_description && $description): ?>
                    <p class="uw-gallery-item-desc"><?php echo esc_html($description); ?></p>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>

            <?php if ($use_lightbox): ?>
            </a>
          <?php endif; ?>

          <?php if ($text_position === 'bottom' && ($show_title || $show_description)): ?>
            <div class="uw-gallery-caption">
              <?php if ($show_title && $title): ?>
                <h4 class="uw-gallery-item-title"><?php echo esc_html($title); ?></h4>
              <?php endif; ?>
              <?php if ($show_description && $description): ?>
                <p class="uw-gallery-item-desc"><?php echo esc_html($description); ?></p>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <?php
  }

  /**
   * 썸네일형 레이아웃 (상단 큰 이미지 + 하단 썸네일)
   */
  private function render_thumbnail_layout($items, $gallery_id, $show_title, $show_description, $use_lightbox)
  {
    if (empty($items))
      return;

    $first_item = $items[0];
    $is_video = isset($first_item['type']) && $first_item['type'] === 'video';
    $custom_thumb_id = isset($first_item['custom_thumb_id']) ? $first_item['custom_thumb_id'] : 0;

    if ($is_video) {
      if ($custom_thumb_id) {
        $main_url = wp_get_attachment_image_url($custom_thumb_id, 'gallery-full');
      } else {
        $main_url = $this->get_video_thumbnail($first_item['video_url']);
      }
    } else {
      $main_url = wp_get_attachment_image_url($first_item['id'], 'gallery-full');
    }
    $main_title = isset($first_item['title']) ? $first_item['title'] : '';
    $main_desc = isset($first_item['description']) ? $first_item['description'] : '';
    ?>
    <div class="uw-gallery-thumbnail-layout">
      <!-- 메인 이미지 -->
      <div class="uw-gallery-main-image" data-index="0">
        <img src="<?php echo esc_url($main_url); ?>" alt="<?php echo esc_attr($main_title); ?>"
          id="uw-main-image-<?php echo $gallery_id; ?>">
        <?php if ($is_video): ?>
          <span class="uw-gallery-play-icon">▶</span>
        <?php endif; ?>

        <?php if ($show_title || $show_description): ?>
          <div class="uw-gallery-main-caption" id="uw-main-caption-<?php echo $gallery_id; ?>">
            <?php if ($show_title && $main_title): ?>
              <h3 class="uw-gallery-main-title"><?php echo esc_html($main_title); ?></h3>
            <?php endif; ?>
            <?php if ($show_description && $main_desc): ?>
              <p class="uw-gallery-main-desc"><?php echo esc_html($main_desc); ?></p>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- 썸네일 리스트 -->
      <div class="uw-gallery-thumbnails">
        <?php foreach ($items as $index => $item):
          $is_video = isset($item['type']) && $item['type'] === 'video';
          $custom_thumb_id = isset($item['custom_thumb_id']) ? $item['custom_thumb_id'] : 0;

          if ($is_video) {
            if ($custom_thumb_id) {
              $thumb_url = wp_get_attachment_image_url($custom_thumb_id, 'gallery-thumb');
              $full_url = wp_get_attachment_image_url($custom_thumb_id, 'gallery-full');
            } else {
              $thumb_url = $this->get_video_thumbnail($item['video_url']);
              $full_url = $this->get_video_thumbnail($item['video_url']);
            }
          } else {
            $thumb_url = wp_get_attachment_image_url(isset($item['thumb_id']) ? $item['thumb_id'] : $item['id'], 'gallery-thumb');
            $full_url = wp_get_attachment_image_url($item['id'], 'gallery-full');
          }
          $title = isset($item['title']) ? $item['title'] : '';
          $desc = isset($item['description']) ? $item['description'] : '';
          ?>
          <button type="button" class="uw-gallery-thumb-btn <?php echo $index === 0 ? 'is-active' : ''; ?>"
            data-index="<?php echo $index; ?>" data-full="<?php echo esc_url($full_url); ?>"
            data-title="<?php echo esc_attr($title); ?>" data-desc="<?php echo esc_attr($desc); ?>"
            data-type="<?php echo $is_video ? 'video' : 'image'; ?>">
            <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($title); ?>">
            <?php if ($is_video): ?>
              <span class="uw-thumb-video-icon">▶</span>
            <?php endif; ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>
    <?php
  }

  /**
   * 슬라이드형 레이아웃 (캐러셀)
   */
  private function render_slide_layout($items, $gallery_id, $show_title, $show_description, $use_lightbox)
  {
    ?>
    <div class="uw-gallery-slide-layout" data-gallery-id="<?php echo $gallery_id; ?>">
      <div class="uw-gallery-slides">
        <?php foreach ($items as $index => $item):
          $is_video = isset($item['type']) && $item['type'] === 'video';
          $custom_thumb_id = isset($item['custom_thumb_id']) ? $item['custom_thumb_id'] : 0;

          if ($is_video) {
            if ($custom_thumb_id) {
              $img_url = wp_get_attachment_image_url($custom_thumb_id, 'gallery-full');
            } else {
              $img_url = $this->get_video_thumbnail($item['video_url']);
            }
          } else {
            $img_url = wp_get_attachment_image_url($item['id'], 'gallery-full');
          }
          $title = isset($item['title']) ? $item['title'] : '';
          $description = isset($item['description']) ? $item['description'] : '';
          ?>
          <div class="uw-gallery-slide <?php echo $index === 0 ? 'is-active' : ''; ?>" data-index="<?php echo $index; ?>">
            <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($title); ?>">
            <?php if ($is_video): ?>
              <span class="uw-gallery-play-icon">▶</span>
            <?php endif; ?>

            <?php if ($show_title || $show_description): ?>
              <div class="uw-gallery-slide-caption">
                <?php if ($show_title && $title): ?>
                  <h4 class="uw-gallery-slide-title"><?php echo esc_html($title); ?></h4>
                <?php endif; ?>
                <?php if ($show_description && $description): ?>
                  <p class="uw-gallery-slide-desc"><?php echo esc_html($description); ?></p>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- 네비게이션 -->
      <button type="button" class="uw-slide-nav uw-slide-prev" aria-label="이전">
        <span>‹</span>
      </button>
      <button type="button" class="uw-slide-nav uw-slide-next" aria-label="다음">
        <span>›</span>
      </button>

      <!-- 인디케이터 -->
      <div class="uw-slide-dots">
        <?php foreach ($items as $index => $item): ?>
          <button type="button" class="uw-slide-dot <?php echo $index === 0 ? 'is-active' : ''; ?>"
            data-index="<?php echo $index; ?>" aria-label="슬라이드 <?php echo $index + 1; ?>"></button>
        <?php endforeach; ?>
      </div>
    </div>
    <?php
  }

}

// Initialize
UW_Gallery_Engine::get_instance();
