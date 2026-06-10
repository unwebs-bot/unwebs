<?php
/**
 * Default Page Template (서브페이지 기본 템플릿)
 */
get_header();
?>


<main class="cm-main" id="main-content" role="main">
  <section class="sub-content-con">
    <div class="area">
      <?php
      while (have_posts()) :
        the_post();
        the_content();
      endwhile;
      ?>
    </div>
  </section>
</main>

<?php get_footer(); ?>
