<?php
/**
 * Template Part: 고객 후기 (marquee 슬라이드)
 *
 * 카드 구조: 헤드라인(요약) + 상세 본문 + 하단 로고·회사명.
 * 실제 로고·문구는 추후 교체 (현재는 디자인용 더미).
 */

if (!defined('ABSPATH')) exit;

$reviews = array(
    array(
        'headline' => '개인 업무 때문에 답변, 자료를 매번 늦게 드렸는데 친절히 응대해주시고 일정도 다시 짜주셨습니다',
        'body'     => '계속 관리해야하는 저희 사이트 특성상, 관리자 모드도 중요함이 적었는데 친절히 직접 알려주시고 가이드북도 주셔서 참고하며 진행했습니다.',
        'company'  => 'NEVER DESIGNING',
        'logo'     => 'review-logo01.svg',
    ),
    array(
        'headline' => '짧은 일정이었음에도 요청사항을 빠르게 반영해주시고 결과물 퀄리티도 만족스러웠습니다',
        'body'     => '수정 요청이 많았는데도 매번 빠르게 반영해주시고, 작업 과정도 투명하게 공유해주셔서 안심하고 맡길 수 있었습니다.',
        'company'  => '사미헌 한정식',
        'logo'     => 'review-logo02.svg',
    ),
    array(
        'headline' => '처음 홈페이지를 만드는데도 꼼꼼하게 안내해주셔서 어렵지 않게 진행할 수 있었어요',
        'body'     => '무엇을 준비해야 하는지부터 운영 방법까지 차근차근 설명해주시고, 납품 이후에도 질문에 친절히 답변해주셨습니다.',
        'company'  => '미래인재 경찰학원',
        'logo'     => 'review-logo03.svg',
    ),
    array(
        'headline' => '브랜드 컨셉에 맞는 디자인을 제안해주시고 반응형까지 완벽하게 구현해주셨습니다',
        'body'     => '레퍼런스만 전달드렸는데도 저희 브랜드 톤에 맞게 잘 풀어주셔서 만족스러웠고, 모바일 화면도 깔끔하게 마무리되었습니다.',
        'company'  => '프라임클리닉',
        'logo'     => 'review-logo04.svg',
    ),
    array(
        'headline' => '예산에 맞춰 합리적으로 진행해주시고, 유지보수까지 꾸준히 챙겨주셔서 든든합니다',
        'body'     => '작은 규모의 수정도 빠르게 대응해주시고, 필요한 기능을 먼저 제안해주시는 부분이 특히 좋았습니다.',
        'company'  => '한빛이엔씨',
        'logo'     => 'review-logo05.svg',
    ),
    array(
        'headline' => '기존 사이트를 리뉴얼하면서 데이터 이관도 매끄럽게 처리해주셨습니다',
        'body'     => '걱정했던 기존 자료 이전 작업이 생각보다 수월했고, 새로운 구조로도 빠르게 적응할 수 있도록 가이드를 제공해주셨습니다.',
        'company'  => '네이처브릿지',
        'logo'     => 'review-logo06.svg',
    ),
);

$reviews_logo_dir = '/assets/images/main/reviews/';
?>

<section class="main-reviews-con cm-section" aria-label="고객 후기">
  <div class="area">
    <div class="cm-tit-box" data-animate="fade-up">
      <span class="cm-tit-sub">100% 실제 고객 후기</span>
      <h2 class="cm-tit">고객 인터뷰</h2>
    </div>
  </div>

  <div class="main-reviews-mask" data-animate="fade-up" data-delay="200">
    <ul class="main-reviews-list">
      <?php for ($r = 0; $r < 2; $r++) : ?>
        <?php foreach ($reviews as $review) : ?>
          <li class="main-reviews-item"<?php echo $r > 0 ? ' aria-hidden="true"' : ''; ?>>
            <div class="main-reviews-card">
              <p class="main-reviews-headline"><?php echo esc_html($review['headline']); ?></p>
              <p class="main-reviews-body"><?php echo esc_html($review['body']); ?></p>
              <div class="main-reviews-brand">
                <img class="main-reviews-logo"
                     src="<?php echo esc_url(get_theme_file_uri($reviews_logo_dir . $review['logo'])); ?>"
                     alt="<?php echo esc_attr($review['company'] . ' 로고'); ?>"
                     width="163" height="53" loading="lazy">
              </div>
            </div>
          </li>
        <?php endforeach; ?>
      <?php endfor; ?>
    </ul>
  </div>
</section>
