<?php
/**
 * UW Column Checklist — 발행 전 SEO 루브릭 실시간 체크
 *
 * 전문 칼럼 편집 화면(Gutenberg) 사이드바에 체크리스트 metabox.
 * JS가 Gutenberg 상태를 구독해 실시간으로 통과 여부 표시.
 *
 * @package Unwebs
 */

if (!defined('ABSPATH')) exit;

class UW_Column_Checklist
{
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
        add_action('add_meta_boxes', array($this, 'add_checklist_metabox'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_assets'));
    }

    public function add_checklist_metabox()
    {
        add_meta_box(
            'uw_column_checklist',
            '발행 전 SEO 체크리스트',
            array($this, 'render_metabox'),
            UW_Column_CPT::POST_TYPE,
            'side',
            'high'
        );
    }

    public function render_metabox($post)
    {
        ?>
        <div id="uw-column-checklist" class="uw-ccl">
          <div class="uw-ccl-summary">
            <span class="uw-ccl-count"><span class="uw-ccl-pass">0</span> / <span class="uw-ccl-total">0</span></span>
            <span class="uw-ccl-label">통과</span>
          </div>
          <div class="uw-ccl-progress"><div class="uw-ccl-progress-bar" style="width:0%"></div></div>
          <ul class="uw-ccl-list">
            <li data-key="title"><span class="uw-ccl-mark"></span>제목 60자 이내 + Focus Keyword 포함</li>
            <li data-key="slug"><span class="uw-ccl-mark"></span>영문 슬러그 (5~7 단어, 하이픈)</li>
            <li data-key="excerpt"><span class="uw-ccl-mark"></span>요약 120~160자</li>
            <li data-key="thumbnail"><span class="uw-ccl-mark"></span>대표 이미지 설정</li>
            <li data-key="category"><span class="uw-ccl-mark"></span>카테고리 1개+</li>
            <li data-key="tags"><span class="uw-ccl-mark"></span>태그 3~5개</li>
            <li data-key="wordcount"><span class="uw-ccl-mark"></span>본문 2,500자+</li>
            <li data-key="h2"><span class="uw-ccl-mark"></span>H2 3개+</li>
            <li data-key="numbers"><span class="uw-ccl-mark"></span>H2 섹션마다 수치 1개+</li>
            <li data-key="external_links"><span class="uw-ccl-mark"></span>외부 출처 링크 3개+</li>
            <li data-key="internal_links"><span class="uw-ccl-mark"></span>내부 링크 3개+</li>
            <li data-key="quote"><span class="uw-ccl-mark"></span>직접 인용문 1개+</li>
            <li data-key="images_alt"><span class="uw-ccl-mark"></span>본문 이미지 모두 alt 있음</li>
          </ul>
          <p class="uw-ccl-hint">Rank Math Focus Keyword·SEO Title·Description은 우측 Rank Math 사이드바에서 별도 입력하세요.</p>
        </div>
        <?php
    }

    public function enqueue_editor_assets()
    {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== UW_Column_CPT::POST_TYPE) return;

        $js_rel  = '/assets/js/admin/column-checklist.js';
        $css_rel = '/assets/css/admin/column-checklist.css';

        wp_enqueue_script(
            'uw-column-checklist',
            get_theme_file_uri($js_rel),
            array('wp-data', 'wp-edit-post', 'wp-dom-ready'),
            starter_get_version($js_rel),
            true
        );

        wp_enqueue_style(
            'uw-column-checklist',
            get_theme_file_uri($css_rel),
            array(),
            starter_get_version($css_rel)
        );
    }
}

UW_Column_Checklist::get_instance();
