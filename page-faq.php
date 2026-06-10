<?php
/**
 * Template Name: 자주 묻는 질문 (FAQ)
 *
 * - 카테고리 탭 필터: 전체 / 제작 / 계약 / 일반 / 유지관리
 * - 아코디언은 메인페이지 main-faq-* 동일 패턴 (CSS grid 0fr→1fr 트랜지션)
 * - FAQPage JSON-LD 자동 출력 (전체 항목)
 */

if (!defined('ABSPATH')) exit;

$faq_data = unwebs_full_faq_data();

// FAQPage 스키마 (전체 항목)
$schema_items = array();
foreach ($faq_data as $cat) {
    foreach ($cat['items'] as $item) {
        $answer_text = wp_strip_all_tags($item['a']);
        $schema_items[] = array(
            '@type'          => 'Question',
            'name'           => $item['q'],
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text'  => $answer_text,
            ),
        );
    }
}
$schema = array(
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => $schema_items,
);

get_header();
?>

<main class="cm-main" id="main-content" role="main">

  <h1 class="blind">자주 묻는 질문</h1>

  <section class="sub-faq-con sub-content-con">
    <div class="area">

      <div class="cm-tit-box" data-animate="fade-up">
        <span class="cm-tit-sub">FAQ</span>
        <h2 class="cm-tit">자주 묻는 질문</h2>
      </div>

      <div class="sub-faq-tabs" role="tablist" aria-label="FAQ 카테고리" data-animate="fade-up" data-delay="100">
        <button type="button" class="sub-faq-tab is-active" role="tab" aria-selected="true" data-target="all">전체</button>
        <?php foreach ($faq_data as $key => $cat) : ?>
        <button type="button" class="sub-faq-tab" role="tab" aria-selected="false" data-target="<?php echo esc_attr($key); ?>"><?php echo esc_html($cat['label']); ?></button>
        <?php endforeach; ?>
      </div>

      <div class="sub-faq-wrap" id="cmSubFaq" data-animate="fade-up" data-delay="200">
      <?php
      $panel_index = 0;
      foreach ($faq_data as $key => $cat) :
      ?>
        <div class="sub-faq-group" data-category="<?php echo esc_attr($key); ?>">
          <h3 class="sub-faq-cate"><?php echo esc_html($cat['label']); ?></h3>
          <ul class="sub-faq-list">
            <?php foreach ($cat['items'] as $item) :
              $panel_id = 'sub-faq-panel-' . $panel_index;
              $panel_index++;
            ?>
            <li class="sub-faq-item">
              <h4 class="sub-faq-q">
                <button type="button"
                        class="sub-faq-trigger"
                        aria-expanded="false"
                        aria-controls="<?php echo esc_attr($panel_id); ?>">
                  <span class="sub-faq-q-mark" aria-hidden="true">Q</span>
                  <span class="sub-faq-q-text"><?php echo esc_html($item['q']); ?></span>
                  <span class="sub-faq-arrow" aria-hidden="true">
                    <i class="xi-angle-down-min"></i>
                  </span>
                </button>
              </h4>
              <div class="sub-faq-panel-wrap"
                   id="<?php echo esc_attr($panel_id); ?>"
                   role="region"
                   aria-hidden="true">
                <div class="sub-faq-panel">
                  <div class="sub-faq-a">
                    <div class="sub-faq-a-text"><?php echo wp_kses_post($item['a']); ?></div>
                  </div>
                </div>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
      </div>

    </div>

    <script type="application/ld+json"><?php echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
  </section>

</main>

<?php get_footer(); ?>
