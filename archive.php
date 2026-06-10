<?php
/**
 * Archive Template (카테고리·태그·날짜)
 * 블로그 기본 post만 이 파일 사용. CPT는 archive-{cpt}.php 사용.
 */
get_header();

$current_slug = '';
$cat_obj      = null;
if (is_category()) {
    $cat_obj = get_queried_object();
    if ($cat_obj && !empty($cat_obj->slug)) {
        $current_slug = $cat_obj->slug;
    }
}

$archive_title = is_category() && $cat_obj
    ? $cat_obj->name
    : (is_tag() ? '#' . single_tag_title('', false) : (is_archive() ? single_term_title('', false) : uw_blog_get_setting('main_title')));
$post_count    = is_category() && $cat_obj ? (int) $cat_obj->count : 0;
?>


<main class="cm-main" id="main-content" role="main">

  <h1 class="blind"><?php echo esc_html($archive_title); ?></h1>

  <section class="blog-archive-con sub-content-con">
    <div class="area">

      <div class="cm-tit-box blog-archive-head" data-animate="fade-up">
        <span class="cm-tit-sub"><?php echo esc_html(uw_blog_get_setting('sub_text')); ?></span>
        <h2 class="cm-tit"><?php echo esc_html($archive_title); ?></h2>
      </div>

      <?php
      set_query_var('blog_current_cat_slug', $current_slug);
      get_template_part('template-parts/common/blog-grid');
      ?>

    </div>
  </section>

</main>

<?php get_footer(); ?>
