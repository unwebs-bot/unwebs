<?php
/**
 * Single Column Template — /column/{slug}/
 * 개별 칼럼 글. BlogPosting·Breadcrumb 스키마는 Rank Math 자동.
 */
get_header();
?>


<main class="cm-main" id="main-content" role="main">

  <?php while (have_posts()) : the_post();
    $ts       = get_the_time('U');
    $weekdays = array('일', '월', '화', '수', '목', '금', '토');
    $date     = date('Y.m.d', $ts) . ' (' . $weekdays[(int) date('w', $ts)] . ')';
  ?>

  <article class="column-single-con sub-content-con">
    <div class="area">

      <?php if (function_exists('rank_math_the_breadcrumbs')) : ?>
        <nav class="cm-breadcrumb" aria-label="breadcrumb"><?php rank_math_the_breadcrumbs(); ?></nav>
      <?php endif; ?>

      <header class="column-single-header" data-animate="fade-up">
        <?php
        $terms = get_the_terms(get_the_ID(), 'column_category');
        if (!is_wp_error($terms) && !empty($terms)) :
            $term = reset($terms);
        ?>
        <a href="<?php echo esc_url(get_term_link($term)); ?>" class="column-single-cat"><?php echo esc_html($term->name); ?></a>
        <?php endif; ?>

        <h1 class="column-single-tit"><?php the_title(); ?></h1>

        <div class="column-single-meta">
          <span class="column-single-author">by <?php echo esc_html(get_the_author()); ?></span>
          <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html($date); ?></time>
          <span class="column-single-reading"><?php echo esc_html(UW_Column_CPT::reading_time(get_the_ID())); ?>분 읽음</span>
        </div>
      </header>

      <?php if (has_post_thumbnail()) : ?>
      <figure class="column-single-thumb" data-animate="fade-up" data-delay="100">
        <?php the_post_thumbnail('large', array(
            'loading'       => 'eager',
            'fetchpriority' => 'high',
            'alt'           => esc_attr(get_the_title()),
        )); ?>
      </figure>
      <?php endif; ?>

      <div class="column-single-body" data-animate="fade-up" data-delay="200">
        <?php the_content(); ?>
      </div>

      <?php
      $tags = get_the_terms(get_the_ID(), 'column_tag');
      if (!is_wp_error($tags) && !empty($tags)) :
      ?>
      <div class="column-single-tags">
        <span class="column-single-tags-label">태그</span>
        <?php foreach ($tags as $tag) : ?>
          <a href="<?php echo esc_url(get_term_link($tag)); ?>" class="column-single-tag">#<?php echo esc_html($tag->name); ?></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php
      // 관련 칼럼: 같은 카테고리 최근 3개
      if (!empty($terms)) :
          $related = new WP_Query(array(
              'post_type'      => 'column',
              'posts_per_page' => 3,
              'post__not_in'   => array(get_the_ID()),
              'tax_query'      => array(array(
                  'taxonomy' => 'column_category',
                  'field'    => 'term_id',
                  'terms'    => $term->term_id,
              )),
          ));
          if ($related->have_posts()) :
      ?>
      <aside class="column-related">
        <h2 class="column-related-tit">관련 칼럼</h2>
        <ul class="column-related-list">
          <?php while ($related->have_posts()) : $related->the_post(); ?>
          <li>
            <a href="<?php the_permalink(); ?>" class="column-related-link">
              <?php if (has_post_thumbnail()) : ?>
              <span class="column-related-img">
                <?php the_post_thumbnail('thumbnail', array('loading' => 'lazy', 'alt' => esc_attr(get_the_title()))); ?>
              </span>
              <?php endif; ?>
              <span class="column-related-tit-txt"><?php the_title(); ?></span>
            </a>
          </li>
          <?php endwhile; ?>
        </ul>
      </aside>
      <?php
          endif;
          wp_reset_postdata();
      endif;
      ?>

      <nav class="column-single-nav" aria-label="이전·다음 글">
        <?php
        $prev = get_previous_post(true, '', 'column_category');
        $next = get_next_post(true, '', 'column_category');
        ?>
        <?php if ($prev) : ?>
        <a href="<?php echo esc_url(get_permalink($prev)); ?>" class="column-single-nav-prev">
          <span class="column-single-nav-label">이전 글</span>
          <span class="column-single-nav-tit"><?php echo esc_html($prev->post_title); ?></span>
        </a>
        <?php endif; ?>
        <?php if ($next) : ?>
        <a href="<?php echo esc_url(get_permalink($next)); ?>" class="column-single-nav-next">
          <span class="column-single-nav-label">다음 글</span>
          <span class="column-single-nav-tit"><?php echo esc_html($next->post_title); ?></span>
        </a>
        <?php endif; ?>
      </nav>

    </div>
  </article>

  <?php endwhile; ?>

</main>

<?php get_footer(); ?>
