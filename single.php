<?php
/**
 * Single Post Template
 * 블로그 단일 글. BlogPosting JSON-LD는 Rank Math 자동.
 *
 * 기능:
 *  - 읽기 진행 바 (상단 얇은 primary)
 *  - 사이드 TOC (PC sticky), 모바일 인라인 박스
 *  - 본문 h2/h3 자동 id (uw_blog_single_content 필터)
 *  - 공유 (복사/페북/X)
 *  - 이전·다음 / 관련 글 / 하단 CTA
 */
get_header();
?>


<!-- 읽기 진행 바 -->
<div class="blog-reading-progress" aria-hidden="true">
  <span class="blog-reading-progress-bar"></span>
</div>

<main class="cm-main" id="main-content" role="main">

  <?php while (have_posts()) : the_post();
    $cats         = get_the_category();
    $first_cat    = (!empty($cats)) ? reset($cats) : null;
    $content_html = apply_filters('the_content', get_the_content());
    $content_text = wp_strip_all_tags($content_html);
    $word_count   = mb_strlen(preg_replace('/\s+/u', '', $content_text));
    $read_min     = max(1, (int) ceil($word_count / 500));

    // 본문 h2/h3 → id + TOC 항목 수집
    $toc = array();
    $content_with_ids = preg_replace_callback(
        '/<h([23])([^>]*)>(.*?)<\/h\1>/is',
        function ($m) use (&$toc) {
            $level = (int) $m[1];
            $attrs = $m[2];
            $inner = $m[3];
            $text  = trim(wp_strip_all_tags($inner));
            if ($text === '') return $m[0];
            $base  = sanitize_title($text);
            if ($base === '') $base = 'sec-' . (count($toc) + 1);
            $id    = $base;
            $i = 2;
            $existing = array_column($toc, 'id');
            while (in_array($id, $existing, true)) { $id = $base . '-' . $i++; }
            $toc[] = array('id' => $id, 'text' => $text, 'level' => $level);
            if (preg_match('/\sid=/', $attrs)) {
                return '<h' . $level . $attrs . '>' . $inner . '</h' . $level . '>';
            }
            return '<h' . $level . $attrs . ' id="' . esc_attr($id) . '">' . $inner . '</h' . $level . '>';
        },
        $content_html
    );
  ?>

  <article class="blog-single-con sub-content-con">
    <div class="area">

      <!-- 브레드크럼 -->
      <?php if (function_exists('rank_math_the_breadcrumbs')) : ?>
        <nav class="cm-breadcrumb blog-single-breadcrumb" aria-label="breadcrumb"><?php rank_math_the_breadcrumbs(); ?></nav>
      <?php else : ?>
        <nav class="cm-breadcrumb blog-single-breadcrumb" aria-label="breadcrumb">
          <a href="<?php echo esc_url(home_url('/')); ?>">홈</a>
          <span class="sep">/</span>
          <a href="<?php echo esc_url(home_url('/blog/')); ?>">블로그</a>
          <?php if ($first_cat) : ?>
            <span class="sep">/</span>
            <a href="<?php echo esc_url(get_category_link($first_cat->term_id)); ?>"><?php echo esc_html($first_cat->name); ?></a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>

      <!-- Hero -->
      <header class="blog-single-header" data-animate="fade-up">
        <?php if ($first_cat) : ?>
        <a class="blog-single-cat" href="<?php echo esc_url(get_category_link($first_cat->term_id)); ?>">
          <?php echo esc_html($first_cat->name); ?>
        </a>
        <?php endif; ?>

        <h1 class="blog-single-tit"><?php the_title(); ?></h1>

        <ul class="blog-single-meta">
          <li class="blog-single-meta-item">
            <i class="xi-calendar" aria-hidden="true"></i>
            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('Y. m. d.')); ?></time>
          </li>
          <?php
          // 수정일이 작성일보다 1일 이상 뒤일 때만 표시 (즉시 수정 정리분 노이즈 제거)
          $published_ts = (int) get_the_time('U');
          $modified_ts  = (int) get_the_modified_time('U');
          if ($modified_ts - $published_ts > DAY_IN_SECONDS) :
          ?>
          <li class="blog-single-meta-item">
            <i class="xi-renew" aria-hidden="true"></i>
            <span>업데이트 <time datetime="<?php echo esc_attr(get_the_modified_date('c')); ?>"><?php echo esc_html(get_the_modified_date('Y. m. d.')); ?></time></span>
          </li>
          <?php endif; ?>
          <li class="blog-single-meta-item">
            <i class="xi-time-o" aria-hidden="true"></i>
            <span><?php echo (int) $read_min; ?>분 분량</span>
          </li>
        </ul>
      </header>

      <!-- 썸네일 -->
      <?php if (has_post_thumbnail()) : ?>
      <div class="blog-single-thumb" data-animate="fade-up" data-delay="100">
        <?php the_post_thumbnail('large', array(
            'loading' => 'eager',
            'alt'     => esc_attr(get_the_title()),
        )); ?>
      </div>
      <?php endif; ?>

      <!-- 본문 레이아웃: 단일 컬럼 (max 780px, 가독성 우선) -->
      <div class="blog-single-layout">

        <?php if (!empty($toc)) : ?>
        <!-- 모바일 인라인 TOC (1280 미만) -->
        <details class="blog-single-toc-mobile">
          <summary>목차 <i class="xi-angle-down-min" aria-hidden="true"></i></summary>
          <ol>
            <?php foreach ($toc as $item) : ?>
            <li class="blog-toc-level-<?php echo (int) $item['level']; ?>">
              <a href="#<?php echo esc_attr($item['id']); ?>"><?php echo esc_html($item['text']); ?></a>
            </li>
            <?php endforeach; ?>
          </ol>
        </details>
        <?php endif; ?>

        <div class="blog-single-body" data-animate="fade-up" data-delay="150">
          <?php echo $content_with_ids; // 이미 the_content 필터 통과 ?>
        </div>

      </div>

      <?php if (!empty($toc)) : ?>
      <!--
        PC 우측 fixed 미니 TOC
        - <nav> + <ol> + aria-label: 검색엔진·스크린리더가 "목차"로 인식
        - data-toc-link: scrollspy
        - SEO/AEO: 본문 h2/h3 그대로 anchor 텍스트 → 봇이 섹션 hierarchy 파악
      -->
      <nav class="blog-single-toc" aria-label="이 글의 목차" data-anchor-toc>
        <h2 class="blog-single-toc-tit">목차</h2>
        <ol class="blog-single-toc-list">
          <?php foreach ($toc as $item) : ?>
          <li class="blog-toc-item blog-toc-level-<?php echo (int) $item['level']; ?>">
            <a href="#<?php echo esc_attr($item['id']); ?>" data-toc-link="<?php echo esc_attr($item['id']); ?>"><?php echo esc_html($item['text']); ?></a>
          </li>
          <?php endforeach; ?>
        </ol>
      </nav>

      <!--
        AEO 강화: 목차를 ItemList JSON-LD로 출력
        - AI/검색엔진이 각 섹션을 인용·딥링크할 수 있는 구조화 데이터
        - position 순서 + URL(anchor) 명시 → "이 글에서 X 섹션" 답변에 활용 가능
      -->
      <script type="application/ld+json">
      <?php
        $toc_items_ld = array();
        $perma = get_permalink();
        foreach ($toc as $i => $item) {
            $toc_items_ld[] = array(
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $item['text'],
                'url'      => $perma . '#' . $item['id'],
            );
        }
        echo wp_json_encode(array(
            '@context'         => 'https://schema.org',
            '@type'            => 'ItemList',
            'name'             => get_the_title() . ' 목차',
            'numberOfItems'    => count($toc_items_ld),
            'itemListOrder'    => 'https://schema.org/ItemListOrderAscending',
            'itemListElement'  => $toc_items_ld,
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      ?>
      </script>
      <?php endif; ?>

      <!-- 태그 — post_tag 운영 정책 확정 전 비활성. column_tag 데이터는 마이그레이션 대상 -->
      <?php /*
      if (has_tag()) :
        $tags = get_the_tags();
        echo '<div class="blog-single-tags"><span class="blog-single-tags-label">태그</span>';
        foreach ($tags as $tag) {
            printf(
                '<a class="blog-single-tag" href="%s">#%s</a>',
                esc_url(get_tag_link($tag->term_id)),
                esc_html($tag->name)
            );
        }
        echo '</div>';
      endif;
      */ ?>

      <!-- 글별 FAQ (post meta 입력 시에만) -->
      <?php
      $faq_items = function_exists('uw_blog_get_faq_items') ? uw_blog_get_faq_items(get_the_ID()) : array();
      if (!empty($faq_items)) {
          set_query_var('faq_items', $faq_items);
          set_query_var('faq_title', '자주 묻는 질문');
          get_template_part('template-parts/common/faq-section');
      }
      ?>

      <!-- 하단 CTA -->
      <div class="blog-single-cta" data-animate="fade-up">
        <div class="blog-single-cta-inner">
          <p class="blog-single-cta-sub">이 글이 도움이 되셨다면</p>
          <h3 class="blog-single-cta-tit">홈페이지 제작·SEO 상담을 받아보세요</h3>
          <p class="blog-single-cta-txt">3분 문의접수로 무료 상담을 받아보실 수 있습니다.</p>
          <div class="blog-single-cta-actions">
            <a class="blog-single-cta-btn blog-single-cta-btn-primary" href="<?php echo esc_url(home_url('/contact/')); ?>">
              <i class="xi-border-color" aria-hidden="true"></i> 프로젝트 문의
            </a>
            <a class="blog-single-cta-btn blog-single-cta-btn-ghost" href="<?php echo esc_url(home_url('/service/')); ?>">
              서비스 자세히 보기 <i class="xi-angle-right-min" aria-hidden="true"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- 이전·다음 -->
      <nav class="blog-single-nav" aria-label="이전·다음 글">
        <?php
        $prev = get_previous_post();
        $next = get_next_post();
        ?>
        <?php if ($prev) : ?>
        <a href="<?php echo esc_url(get_permalink($prev)); ?>" class="blog-single-nav-item blog-single-nav-prev">
          <span class="blog-single-nav-label"><i class="xi-angle-left-min" aria-hidden="true"></i> 이전 글</span>
          <span class="blog-single-nav-tit"><?php echo esc_html($prev->post_title); ?></span>
        </a>
        <?php else : ?>
        <span class="blog-single-nav-item is-empty"></span>
        <?php endif; ?>
        <?php if ($next) : ?>
        <a href="<?php echo esc_url(get_permalink($next)); ?>" class="blog-single-nav-item blog-single-nav-next">
          <span class="blog-single-nav-label">다음 글 <i class="xi-angle-right-min" aria-hidden="true"></i></span>
          <span class="blog-single-nav-tit"><?php echo esc_html($next->post_title); ?></span>
        </a>
        <?php else : ?>
        <span class="blog-single-nav-item is-empty"></span>
        <?php endif; ?>
      </nav>

    </div>
  </article>

  <?php endwhile; ?>

</main>

<?php get_footer(); ?>
