<?php
/**
 * Template Part: 블로그 사이드바 + 카드 그리드
 *
 * 사용:
 *   set_query_var('blog_current_cat_slug', '<slug or empty>');
 *   set_query_var('blog_query', $wp_query_obj); // 외부 WP_Query 전달, 없으면 메인 루프 사용
 *   get_template_part('template-parts/common/blog-grid');
 */

if (!defined('ABSPATH')) exit;

$current_cat_slug = get_query_var('blog_current_cat_slug', '');
$blog_q           = get_query_var('blog_query', null);
$use_main_loop    = ($blog_q === null);

// 카테고리 사이드바 데이터
$blog_cats = get_categories(array(
    'hide_empty' => false,
    'orderby'    => 'term_id',
    'order'      => 'ASC',
    'exclude'    => array(1), // Uncategorized 제외
));
$total_count = (int) wp_count_posts('post')->publish;

// 페이지네이션 base — 외부 쿼리일 경우 명시
$paged    = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
$max_page = $use_main_loop ? $GLOBALS['wp_query']->max_num_pages : $blog_q->max_num_pages;
?>

<div class="blog-layout" data-animate="fade-up">

  <!-- 사이드바: 검색 + 카테고리 -->
  <aside class="blog-sidebar">

    <form role="search" method="get" class="blog-search" action="<?php echo esc_url(home_url('/')); ?>">
      <label class="blind" for="blog-search-input">블로그 검색</label>
      <input id="blog-search-input"
             type="search"
             name="s"
             class="blog-search-input"
             placeholder="검색어를 입력해 주세요"
             value="<?php echo esc_attr(get_search_query()); ?>"
             autocomplete="off">
      <input type="hidden" name="post_type" value="post">
      <button type="submit" class="blog-search-btn" aria-label="검색">
        <i class="xi-search" aria-hidden="true"></i>
      </button>
    </form>

    <h3 class="blog-sidebar-tit">카테고리</h3>
    <ul class="blog-cat-list">
      <li class="blog-cat-item<?php echo empty($current_cat_slug) ? ' is-active' : ''; ?>">
        <a href="<?php echo esc_url(home_url('/blog/')); ?>" class="blog-cat-link">
          <span class="blog-cat-name">전체</span>
          <span class="blog-cat-count">(<?php echo (int) $total_count; ?>)</span>
        </a>
      </li>
      <?php foreach ($blog_cats as $cat) : ?>
      <li class="blog-cat-item<?php echo ($current_cat_slug === $cat->slug) ? ' is-active' : ''; ?>">
        <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>" class="blog-cat-link">
          <span class="blog-cat-name"><?php echo esc_html($cat->name); ?></span>
          <span class="blog-cat-count">(<?php echo (int) $cat->count; ?>)</span>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </aside>

  <!-- 본문: 카드 그리드 -->
  <div class="blog-main">
    <?php
    $has_posts = $use_main_loop ? have_posts() : $blog_q->have_posts();
    if ($has_posts) :
    ?>
    <div class="blog-list" data-stagger>
      <?php
      while ($use_main_loop ? have_posts() : $blog_q->have_posts()) :
        $use_main_loop ? the_post() : $blog_q->the_post();
        $cats = get_the_category();
        $first_cat = (!empty($cats)) ? reset($cats) : null;
      ?>
      <article class="blog-item" data-animate="fade-up">
        <a href="<?php the_permalink(); ?>" class="blog-item-link">
          <?php if (has_post_thumbnail()) : ?>
          <div class="blog-item-img">
            <?php the_post_thumbnail('medium_large', array(
                'loading' => 'lazy',
                'alt'     => esc_attr(get_the_title()),
            )); ?>
          </div>
          <?php else : ?>
          <div class="blog-item-img blog-item-img-placeholder">
            <span aria-hidden="true">UNWEBS</span>
          </div>
          <?php endif; ?>
          <div class="blog-item-info">
            <h3 class="blog-item-tit"><?php the_title(); ?></h3>
            <div class="blog-item-meta">
              <time class="blog-item-date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                <?php echo esc_html(get_the_date('Y.m.d')); ?>
              </time>
              <?php
              $card_text  = wp_strip_all_tags(get_the_content());
              $card_chars = mb_strlen(preg_replace('/\s+/u', '', $card_text), 'UTF-8');
              $card_min   = max(1, (int) ceil($card_chars / 500));
              ?>
              <span class="blog-item-readtime"><?php echo (int) $card_min; ?>분 분량</span>
            </div>
          </div>
        </a>
      </article>
      <?php endwhile; ?>
    </div>

    <nav class="cm-pagination" aria-label="페이지네이션">
      <?php
      $big = 999999999;
      echo paginate_links(array(
          'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
          'format'    => '?paged=%#%',
          'current'   => $paged,
          'total'     => $max_page,
          'prev_text' => '이전',
          'next_text' => '다음',
      ));
      ?>
    </nav>
    <?php if (!$use_main_loop) wp_reset_postdata(); ?>

    <?php else : ?>
    <p class="blog-empty">아직 등록된 게시글이 없습니다.</p>
    <?php endif; ?>
  </div>

</div>
