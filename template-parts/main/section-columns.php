<?php
/**
 * Template Part: 홈페이지 블로그 인사이트 (구 전문 칼럼)
 *
 * 2026-05-20 — column CPT 통합. WP 기본 post(블로그) 최신 6개 노출.
 * 글이 없으면 더미 카드 6개 표시.
 */

if (!defined('ABSPATH')) exit;

$column_query = new WP_Query(array(
    'post_type'           => 'post',
    'posts_per_page'      => 6,
    'ignore_sticky_posts' => true,
    'post_status'         => 'publish',
));

$weekdays = array('일', '월', '화', '수', '목', '금', '토');
?>

<section class="main-columns-con cm-section" aria-label="홈페이지 전문 칼럼">
  <div class="area">
    <div class="cm-tit-box" data-animate="fade-up">
      <span class="cm-tit-sub">홈페이지 제작에 도움이 될만한 인사이트를 확인해 보세요</span>
      <h2 class="cm-tit">홈페이지 전문 칼럼</h2>
    </div>

    <div class="main-columns-grid" data-animate="fade-up" data-delay="200">
      <?php if ($column_query->have_posts()) : ?>
        <?php
        while ($column_query->have_posts()) :
            $column_query->the_post();
            $ts   = get_the_time('U');
            $date = date('Y.m.d', $ts) . ' (' . $weekdays[(int) date('w', $ts)] . ')';
        ?>
          <a href="<?php the_permalink(); ?>" class="main-columns-item">
            <div class="main-columns-img">
              <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('medium_large', array(
                    'class'   => 'main-columns-thumb',
                    'loading' => 'lazy',
                    'alt'     => esc_attr(get_the_title()),
                )); ?>
              <?php else : ?>
                <span class="main-columns-placeholder" aria-hidden="true">COLUMN</span>
              <?php endif; ?>
            </div>
            <h3 class="main-columns-tit"><?php the_title(); ?></h3>
            <time class="main-columns-date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html($date); ?></time>
          </a>
        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>
      <?php else : ?>
        <?php for ($i = 0; $i < 6; $i++) : ?>
          <div class="main-columns-item is-dummy">
            <div class="main-columns-img">
              <span class="main-columns-placeholder" aria-hidden="true">COLUMN</span>
            </div>
            <h3 class="main-columns-tit">준비 중인 칼럼입니다</h3>
            <time class="main-columns-date">—</time>
          </div>
        <?php endfor; ?>
      <?php endif; ?>
    </div>
  </div>
</section>
