<?php
/**
 * Search Results Template
 * 블로그 사이드바 + 카드 그리드 동일 적용 (검색어 표시 + 빈 결과 안내)
 */
get_header();

$search_query = trim(get_search_query());
$found_total  = (int) $GLOBALS['wp_query']->found_posts;
?>


<main class="cm-main" id="main-content" role="main">

  <h1 class="blind"><?php echo $search_query !== '' ? esc_html($search_query) . ' 검색 결과' : esc_html(uw_blog_get_setting('main_title')); ?></h1>

  <section class="blog-archive-con sub-content-con">
    <div class="area">

      <div class="cm-tit-box" data-animate="fade-up">
        <span class="cm-tit-sub"><?php echo esc_html(uw_blog_get_setting('sub_text')); ?></span>
        <h2 class="cm-tit">
          <?php if ($search_query !== '') : ?>
            "<?php echo esc_html($search_query); ?>" 검색 결과
          <?php else : ?>
            <?php echo esc_html(uw_blog_get_setting('main_title')); ?>
          <?php endif; ?>
        </h2>
      </div>

      <?php if ($found_total === 0 && $search_query !== '') : ?>

        <!-- 빈 검색 결과 -->
        <div class="blog-search-empty" data-animate="fade-up">
          <div class="blog-search-empty-icon" aria-hidden="true"><i class="xi-search"></i></div>
          <h3 class="blog-search-empty-tit">검색 결과가 없습니다</h3>
          <p class="blog-search-empty-txt">
            <strong>"<?php echo esc_html($search_query); ?>"</strong>와 일치하는 글을 찾지 못했습니다.<br>
            다른 단어로 다시 검색하시거나, 아래 카테고리에서 인사이트를 확인해 보세요.
          </p>

          <form role="search" method="get" class="blog-search blog-search-empty-form" action="<?php echo esc_url(home_url('/')); ?>">
            <label class="blind" for="blog-search-empty-input">다시 검색</label>
            <input id="blog-search-empty-input" type="search" name="s" class="blog-search-input" placeholder="다른 단어로 검색해 보세요" value="">
            <input type="hidden" name="post_type" value="post">
            <button type="submit" class="blog-search-btn" aria-label="검색"><i class="xi-search" aria-hidden="true"></i></button>
          </form>

          <?php
          $empty_cats = get_categories(array(
              'hide_empty' => true,
              'orderby'    => 'count',
              'order'      => 'DESC',
              'exclude'    => array(1),
              'number'     => 5,
          ));
          if (!empty($empty_cats)) :
          ?>
          <div class="blog-search-empty-cats">
            <span class="blog-search-empty-cats-label">추천 카테고리</span>
            <?php foreach ($empty_cats as $cat) : ?>
            <a class="blog-search-empty-cat" href="<?php echo esc_url(get_category_link($cat->term_id)); ?>">
              <?php echo esc_html($cat->name); ?> <span>(<?php echo (int) $cat->count; ?>)</span>
            </a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <a class="blog-search-empty-back" href="<?php echo esc_url(home_url('/blog/')); ?>">
            <i class="xi-angle-left-min" aria-hidden="true"></i> 블로그 전체로 돌아가기
          </a>
        </div>

      <?php else : ?>
        <?php
        set_query_var('blog_current_cat_slug', '');
        get_template_part('template-parts/common/blog-grid');
        ?>
      <?php endif; ?>

    </div>
  </section>

</main>

<?php get_footer(); ?>
