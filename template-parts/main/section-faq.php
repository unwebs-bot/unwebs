<?php
/**
 * Template Part: 메인 페이지 자주 묻는 질문 (FAQ)
 *
 * - CSS-only grid 트랜지션 (0fr → 1fr) 으로 부드러운 펼침
 * - JS는 단일 펼침 토글만 (main.js)
 * - FAQPage JSON-LD 자동 출력 (검색 리치 결과)
 */

if (!defined('ABSPATH')) exit;

$faq_items = starter_faq_main();
if (empty($faq_items)) return;

// FAQPage 스키마 (검색 리치 결과) — 'a'가 배열인 경우 합쳐서 한 문자열로
$schema = array(
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map(function ($item) {
        $answer_text = is_array($item['a']) ? implode(' ', $item['a']) : $item['a'];
        return array(
            '@type'          => 'Question',
            'name'           => $item['q'],
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text'  => $answer_text,
            ),
        );
    }, $faq_items),
);
?>

<section class="main-faq-con cm-section" aria-label="자주 묻는 질문">
  <div class="area">
    <div class="cm-tit-box" data-animate="fade-up">
      <span class="cm-tit-sub">FAQ</span>
      <h2 class="cm-tit">자주 묻는 질문</h2>
    </div>

    <ul class="main-faq-list" id="cmMainFaq" data-animate="fade-up" data-delay="200">
      <?php foreach ($faq_items as $i => $item) :
        $panel_id = 'main-faq-panel-' . $i;
      ?>
      <li class="main-faq-item">
        <h3 class="main-faq-q">
          <button type="button"
                  class="main-faq-trigger"
                  aria-expanded="false"
                  aria-controls="<?php echo esc_attr($panel_id); ?>">
            <span class="main-faq-q-mark" aria-hidden="true">Q</span>
            <span class="main-faq-q-text"><?php echo esc_html($item['q']); ?></span>
            <span class="main-faq-arrow" aria-hidden="true">
              <i class="xi-angle-down-min"></i>
            </span>
          </button>
        </h3>
        <div class="main-faq-panel-wrap"
             id="<?php echo esc_attr($panel_id); ?>"
             role="region"
             aria-hidden="true">
          <div class="main-faq-panel">
            <div class="main-faq-a">
              <?php
              $paragraphs = is_array($item['a']) ? $item['a'] : array($item['a']);
              foreach ($paragraphs as $p) :
              ?>
              <p class="main-faq-a-text"><?php echo esc_html($p); ?></p>
              <?php endforeach; ?>
              <?php if (!empty($item['cta_label']) && !empty($item['cta_url'])) : ?>
              <a class="main-faq-cta" href="<?php echo esc_url($item['cta_url']); ?>">
                <?php echo esc_html($item['cta_label']); ?>
                <i class="xi-angle-right-min" aria-hidden="true"></i>
              </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <script type="application/ld+json"><?php echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
</section>
