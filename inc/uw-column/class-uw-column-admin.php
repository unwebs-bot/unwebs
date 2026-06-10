<?php
/**
 * UW Column Admin — 전문 칼럼 관리자 UX 보조
 *
 * Gutenberg + Rank Math 흐름을 유지하면서 최소한의 관리자 편의만 얹는다.
 * - 글 목록에 썸네일·읽는 시간 컬럼 추가
 * - 대표 이미지 metabox 하단에 권장 사이즈 안내
 * - 발행 화면 상단에 슬러그 영문 작성 가이드
 *
 * @package Unwebs
 */

if (!defined('ABSPATH')) exit;

class UW_Column_Admin
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
        $pt = UW_Column_CPT::POST_TYPE;

        // 글 목록 컬럼
        add_filter("manage_{$pt}_posts_columns", array($this, 'add_list_columns'));
        add_action("manage_{$pt}_posts_custom_column", array($this, 'render_list_column'), 10, 2);

        // 대표 이미지 metabox 안내
        add_filter("admin_post_thumbnail_html", array($this, 'thumbnail_hint'), 10, 3);

        // 발행 화면 상단 안내
        add_action('edit_form_top', array($this, 'editor_notice'));

        // 관리자 목록 CSS (썸네일 컬럼 폭)
        add_action('admin_head', array($this, 'list_css'));
    }

    /**
     * 글 목록에 컬럼 추가
     * 기본(체크박스·제목·작성자·카테고리·태그·댓글·날짜) 유지 + 앞에 썸네일, 뒤에 읽는 시간
     */
    public function add_list_columns($columns)
    {
        $new = array();
        foreach ($columns as $key => $label) {
            if ($key === 'title') {
                $new['uw_thumb'] = '대표';
            }
            $new[$key] = $label;
            if ($key === 'title') {
                $new['uw_reading_time'] = '읽는 시간';
            }
        }
        return $new;
    }

    public function render_list_column($column, $post_id)
    {
        if ($column === 'uw_thumb') {
            if (has_post_thumbnail($post_id)) {
                echo get_the_post_thumbnail($post_id, array(60, 40), array(
                    'style' => 'width:60px;height:40px;object-fit:cover;border-radius:4px;display:block;',
                ));
            } else {
                echo '<span style="display:inline-block;width:60px;height:40px;background:#f0f0f0;border-radius:4px;text-align:center;line-height:40px;color:#aaa;font-size:11px;">없음</span>';
            }
        } elseif ($column === 'uw_reading_time') {
            echo esc_html(UW_Column_CPT::reading_time($post_id)) . '분';
        }
    }

    /**
     * 대표 이미지 metabox 하단 안내
     */
    public function thumbnail_hint($content, $post_id, $thumbnail_id)
    {
        if (get_post_type($post_id) !== UW_Column_CPT::POST_TYPE) return $content;

        $hint = '<p style="margin:10px 0 0;font-size:11px;color:#666;line-height:1.5;">권장: <b>1200×630 이상</b> JPG/PNG.<br>이 이미지가 OG·Article·리스트 썸네일로 사용됩니다.</p>';
        return $content . $hint;
    }

    /**
     * 발행 화면 상단 안내 (Gutenberg 이전 classic 에디터에서 edit_form_top)
     */
    public function editor_notice($post)
    {
        if (get_post_type($post) !== UW_Column_CPT::POST_TYPE) return;

        echo '<div class="notice notice-info inline" style="margin:10px 0;"><p>';
        echo '<b>발행 전 체크:</b> ';
        echo '영문 슬러그(URL) · 대표 이미지(1200×630) · 요약(Excerpt, 120~160자) · 카테고리 1개 이상';
        echo ' / SEO는 <b>Rank Math</b> 사이드바에서 Focus Keyword·Description 입력';
        echo '</p></div>';
    }

    /**
     * 목록 CSS — 썸네일 컬럼 폭 축소
     */
    public function list_css()
    {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== UW_Column_CPT::POST_TYPE) return;
        ?>
        <style>
            .column-uw_thumb { width: 70px; }
            .column-uw_reading_time { width: 80px; }
        </style>
        <?php
    }
}

UW_Column_Admin::get_instance();
