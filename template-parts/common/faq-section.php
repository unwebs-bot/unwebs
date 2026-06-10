<?php
/**
 * Template Part: FAQ Section
 *
 * 페이지별 FAQ 섹션 (아코디언 UI).
 * FAQPage JSON-LD 스키마는 Rank Math Schema Generator로 페이지 편집 시 입력.
 *
 * 사용법:
 * $faq_items = array(
 *     array('q' => '질문1', 'a' => '답변1'),
 *     array('q' => '질문2', 'a' => '답변2'),
 * );
 * set_query_var('faq_items', $faq_items);
 * set_query_var('faq_title', '자주 묻는 질문');
 * get_template_part('template-parts/common/faq-section');
 */

$faq_items = get_query_var('faq_items', array());
$faq_title = get_query_var('faq_title', '자주 묻는 질문');

if (empty($faq_items)) {
    return;
}
?>

<section class="cm-faq-con sub-content-con">
  <div class="area">
    <div class="cm-tit-box" data-animate="fade-up">
      <h3 class="cm-tit"><?php echo esc_html($faq_title); ?></h3>
    </div>

    <div class="cm-faq-list" data-animate="fade-up" data-delay="200">
      <?php foreach ($faq_items as $i => $item) : ?>
      <details class="cm-faq-item">
        <summary class="cm-faq-q">
          <span class="cm-faq-q-mark">Q</span>
          <span class="cm-faq-q-txt"><?php echo esc_html($item['q']); ?></span>
        </summary>
        <div class="cm-faq-a">
          <span class="cm-faq-a-mark">A</span>
          <div class="cm-faq-a-txt"><?php echo wp_kses_post($item['a']); ?></div>
        </div>
      </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
