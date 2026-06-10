<?php
/**
 * Blog Single FAQ — 글별 FAQPage 리치결과용 Q/A 입력
 *
 * - 글 편집 화면에 meta box (Q/A 5쌍 고정)
 * - 데이터: post meta `_uw_blog_faq` (배열)
 * - 렌더: single.php 본문 아래에서 `uw_blog_get_faq_items($post_id)` → faq-section.php
 * - 스키마: uw_blog_faq_jsonld()가 wp_head에서 FAQPage JSON-LD 자동 출력 (화면 데이터와 동일 소스 → 수동 입력 불필요)
 */

if (!defined('ABSPATH')) exit;

const UW_BLOG_FAQ_META   = '_uw_blog_faq';
const UW_BLOG_FAQ_PAIRS  = 5;

/**
 * post meta → 빈 항목 필터된 Q/A 배열
 *
 * @param int $post_id
 * @return array<int, array{q:string,a:string}>
 */
function uw_blog_get_faq_items($post_id)
{
    $raw = get_post_meta((int) $post_id, UW_BLOG_FAQ_META, true);
    if (!is_array($raw)) return array();

    $out = array();
    foreach ($raw as $row) {
        $q = isset($row['q']) ? trim((string) $row['q']) : '';
        $a = isset($row['a']) ? trim((string) $row['a']) : '';
        if ($q === '' || $a === '') continue;
        $out[] = array('q' => $q, 'a' => $a);
    }
    return $out;
}

/**
 * 블로그 글 FAQPage JSON-LD 자동 출력
 * 화면 출력(faq-section.php)과 동일한 uw_blog_get_faq_items() 사용 → 마크업↔콘텐츠 일치 보장.
 */
add_action('wp_head', 'uw_blog_faq_jsonld', 55);
function uw_blog_faq_jsonld()
{
    if (!is_singular('post')) return;

    $items = uw_blog_get_faq_items(get_the_ID());
    if (empty($items)) return;

    $main = array();
    foreach ($items as $it) {
        $main[] = array(
            '@type'          => 'Question',
            'name'           => $it['q'],
            'acceptedAnswer' => array('@type' => 'Answer', 'text' => wp_strip_all_tags($it['a'])),
        );
    }

    $schema = array(
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $main,
    );

    echo "\n<script type=\"application/ld+json\">" . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
}

/* ==========================================================================
   Meta box
   ========================================================================== */

add_action('add_meta_boxes_post', 'uw_blog_faq_add_meta_box');
function uw_blog_faq_add_meta_box()
{
    add_meta_box(
        'uw_blog_faq',
        '글 하단 FAQ (선택 · 최대 ' . UW_BLOG_FAQ_PAIRS . '쌍)',
        'uw_blog_faq_render_meta_box',
        'post',
        'normal',
        'low'
    );
}

function uw_blog_faq_render_meta_box($post)
{
    wp_nonce_field('uw_blog_faq_save', 'uw_blog_faq_nonce');
    $items = get_post_meta($post->ID, UW_BLOG_FAQ_META, true);
    if (!is_array($items)) $items = array();
    ?>
    <p style="margin:0 0 12px;color:#6b7280;font-size:12px;">
      입력한 쌍만 글 하단에 노출됩니다. FAQPage 리치결과를 원하면 Rank Math Schema Generator에서 같은 내용을 한 번 더 입력하세요.
    </p>
    <table class="form-table" style="margin-top:0;">
      <tbody>
        <?php for ($i = 0; $i < UW_BLOG_FAQ_PAIRS; $i++) :
            $q = isset($items[$i]['q']) ? (string) $items[$i]['q'] : '';
            $a = isset($items[$i]['a']) ? (string) $items[$i]['a'] : '';
        ?>
        <tr>
          <th scope="row" style="width:50px;vertical-align:top;padding-top:18px;">#<?php echo $i + 1; ?></th>
          <td>
            <label style="display:block;margin-bottom:6px;">
              <span style="display:inline-block;width:30px;color:#2563eb;font-weight:600;">Q.</span>
              <input type="text"
                     name="uw_blog_faq[<?php echo $i; ?>][q]"
                     value="<?php echo esc_attr($q); ?>"
                     placeholder="질문"
                     style="width:calc(100% - 40px);">
            </label>
            <label style="display:block;">
              <span style="display:inline-block;width:30px;color:#16a34a;font-weight:600;vertical-align:top;padding-top:6px;">A.</span>
              <textarea name="uw_blog_faq[<?php echo $i; ?>][a]"
                        rows="3"
                        placeholder="답변 (HTML 허용 — &lt;a&gt;, &lt;strong&gt;, &lt;br&gt; 등)"
                        style="width:calc(100% - 40px);"><?php echo esc_textarea($a); ?></textarea>
            </label>
          </td>
        </tr>
        <?php endfor; ?>
      </tbody>
    </table>
    <?php
}

/* ==========================================================================
   Save
   ========================================================================== */

add_action('save_post_post', 'uw_blog_faq_save', 10, 2);
function uw_blog_faq_save($post_id, $post)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    if (!isset($_POST['uw_blog_faq_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['uw_blog_faq_nonce'])), 'uw_blog_faq_save')) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $raw = isset($_POST['uw_blog_faq']) && is_array($_POST['uw_blog_faq']) ? wp_unslash($_POST['uw_blog_faq']) : array();

    $clean = array();
    foreach ($raw as $row) {
        $q = isset($row['q']) ? sanitize_text_field((string) $row['q']) : '';
        $a = isset($row['a']) ? wp_kses_post((string) $row['a']) : '';
        $clean[] = array('q' => $q, 'a' => $a);
    }

    // 모두 비어있으면 메타 자체 삭제 (DB 정리)
    $has_content = false;
    foreach ($clean as $row) {
        if ($row['q'] !== '' && $row['a'] !== '') { $has_content = true; break; }
    }

    if ($has_content) {
        update_post_meta($post_id, UW_BLOG_FAQ_META, $clean);
    } else {
        delete_post_meta($post_id, UW_BLOG_FAQ_META);
    }
}
