<?php
/**
 * Blog Page Template — /blog/ (슬러그 페이지 매칭)
 * 카테고리 사이드바 + 카드 그리드 (메인 루프 외부 WP_Query)
 */
get_header();
?>


<main class="cm-main" id="main-content" role="main">

  <h1 class="blind">블로그</h1>

  <section class="blog-list-con sub-content-con">
    <div class="area">

      <?php if (function_exists('rank_math_the_breadcrumbs')) : ?>
        <nav class="cm-breadcrumb" aria-label="breadcrumb"><?php rank_math_the_breadcrumbs(); ?></nav>
      <?php endif; ?>

      <div class="cm-tit-box" data-animate="fade-up">
        <span class="cm-tit-sub"><?php echo esc_html(uw_blog_get_setting('sub_text')); ?></span>
        <h2 class="cm-tit"><?php echo esc_html(uw_blog_get_setting('main_title')); ?></h2>
      </div>

      <?php
      $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
      $blog_q = new WP_Query(array(
          'post_type'      => 'post',
          'posts_per_page' => uw_blog_get_setting('posts_per_page'),
          'paged'          => $paged,
          'post_status'    => 'publish',
      ));

      set_query_var('blog_current_cat_slug', '');
      set_query_var('blog_query', $blog_q);
      get_template_part('template-parts/common/blog-grid');
      ?>

    </div>
  </section>

</main>

<?php get_footer(); ?>
