<?php
/**
 * Column Archive Template — /column/
 * 전문 칼럼 인덱스 + 카테고리·태그 아카이브
 * SEO: 메타·canonical·스키마는 Rank Math 자동 처리. 테마는 구조·breadcrumb만 담당.
 */
get_header();

$is_tax          = is_tax();
$archive_title   = $is_tax ? single_term_title('', false) . ' 전문 칼럼' : '전문 칼럼';
$archive_desc    = $is_tax ? term_description() : '';

?>


<main class="cm-main" id="main-content" role="main">

  <section class="column-archive-con sub-content-con">
    <div class="area">

      <?php if (function_exists('rank_math_the_breadcrumbs')) : ?>
        <nav class="cm-breadcrumb" aria-label="breadcrumb"><?php rank_math_the_breadcrumbs(); ?></nav>
      <?php endif; ?>

      <?php if (!$is_tax) : ?>
      <div class="cm-tit-box" data-animate="fade-up">
        <span class="cm-tit-sub">COLUMN</span>
        <h2 class="cm-tit">전문 칼럼</h2>
        <p class="cm-tit-txt">홈페이지 제작·검색 최적화·운영 실무 인사이트</p>
      </div>
      <?php endif; ?>

      <?php if ($archive_desc) : ?>
      <div class="column-archive-desc" data-animate="fade-up"><?php echo wp_kses_post($archive_desc); ?></div>
      <?php endif; ?>

      <?php
      // 카테고리 필터
      $column_cats = get_terms(array(
          'taxonomy'   => 'column_category',
          'hide_empty' => false,
      ));
      $current_term = $is_tax ? get_queried_object() : null;
      if (!empty($column_cats) && !is_wp_error($column_cats)) :
      ?>
      <nav class="column-filter" aria-label="카테고리 필터" data-animate="fade-up">
        <a href="<?php echo esc_url(get_post_type_archive_link('column')); ?>"
           class="column-filter-item<?php echo !$is_tax ? ' is-active' : ''; ?>">전체</a>
        <?php foreach ($column_cats as $cat) :
          $is_active = $current_term && $current_term->term_id === $cat->term_id;
        ?>
          <a href="<?php echo esc_url(get_term_link($cat)); ?>"
             class="column-filter-item<?php echo $is_active ? ' is-active' : ''; ?>"><?php echo esc_html($cat->name); ?></a>
        <?php endforeach; ?>
      </nav>
      <?php endif; ?>

      <?php if (have_posts()) :
          $weekdays = array('일', '월', '화', '수', '목', '금', '토');
      ?>
      <div class="column-list" data-stagger>
        <?php while (have_posts()) : the_post();
          $ts   = get_the_time('U');
          $date = date('Y.m.d', $ts) . ' (' . $weekdays[(int) date('w', $ts)] . ')';
        ?>
        <article class="column-item" data-animate="fade-up">
          <a href="<?php the_permalink(); ?>" class="column-item-link">
            <div class="column-item-img">
              <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('medium_large', array(
                    'class'   => 'column-item-thumb',
                    'loading' => 'lazy',
                    'alt'     => esc_attr(get_the_title()),
                )); ?>
              <?php else : ?>
                <span class="column-item-placeholder" aria-hidden="true">COLUMN</span>
              <?php endif; ?>
            </div>
            <div class="column-item-info">
              <?php
              $terms = get_the_terms(get_the_ID(), 'column_category');
              if (!is_wp_error($terms) && !empty($terms)) :
                  $term = reset($terms);
              ?>
              <span class="column-item-cat"><?php echo esc_html($term->name); ?></span>
              <?php endif; ?>
              <h3 class="column-item-tit"><?php the_title(); ?></h3>
              <?php if (get_the_excerpt()) : ?>
              <p class="column-item-txt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 30)); ?></p>
              <?php endif; ?>
              <div class="column-item-meta">
                <time class="column-item-date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html($date); ?></time>
                <span class="column-item-reading"><?php echo esc_html(UW_Column_CPT::reading_time(get_the_ID())); ?>분</span>
              </div>
            </div>
          </a>
        </article>
        <?php endwhile; ?>
      </div>

      <nav class="cm-pagination" aria-label="페이지네이션">
        <?php echo paginate_links(array('prev_text' => '이전', 'next_text' => '다음')); ?>
      </nav>

      <?php else : ?>
      <p class="column-empty">아직 등록된 칼럼이 없습니다.</p>
      <?php endif; ?>

    </div>
  </section>

</main>

<?php get_footer(); ?>
