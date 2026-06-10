<?php
/**
 * Single Template — 공지사항 상세 (uw_board)
 * FAQ는 archive에서 아코디언으로 처리하므로 이 템플릿은 주로 공지용.
 *
 * 구성: 카테고리 라벨(고객지원) + 제목 + 작성일 + 본문 + 첨부파일 + 이전/다음 + 목록
 */
get_header();

// 게시판 타입 판별 (현재는 notice만 single 사용)
$terms      = get_the_terms(get_the_ID(), 'uw_board_type');
$board_slug = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->slug : 'notice';
$list_url   = home_url('/' . $board_slug . '/');

// 카테고리 라벨
$category_sub = '고객지원';

// 이전/다음 글 (같은 board type 내에서)
function uw_notice_get_adjacent($current_id, $board_slug, $direction = 'prev')
{
    $current_post = get_post($current_id);
    if (!$current_post) return null;

    $compare = ($direction === 'prev') ? '<' : '>';
    $order   = ($direction === 'prev') ? 'DESC' : 'ASC';

    $q = new WP_Query(array(
        'post_type'      => 'uw_board',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => $order,
        'date_query'     => array(
            array(
                'column' => 'post_date',
                $compare === '<' ? 'before' : 'after' => $current_post->post_date,
            ),
        ),
        'tax_query'      => array(array(
            'taxonomy' => 'uw_board_type',
            'field'    => 'slug',
            'terms'    => $board_slug,
        )),
    ));

    return $q->have_posts() ? $q->posts[0] : null;
}
?>

<main class="cm-main" id="main-content" role="main">

  <?php while (have_posts()) : the_post();
    $post_id        = get_the_ID();
    $attachments    = get_post_meta($post_id, '_uw_attachments', true);
    $has_attachment = !empty($attachments);
    $prev_post      = uw_notice_get_adjacent($post_id, $board_slug, 'prev');
    $next_post      = uw_notice_get_adjacent($post_id, $board_slug, 'next');
  ?>

  <article class="notice-single-con sub-content-con">
    <div class="area">

      <div class="cm-tit-box" data-animate="fade-up">
        <span class="cm-tit-sub"><?php echo esc_html($category_sub); ?></span>
        <h2 class="cm-tit">공지사항</h2>
      </div>

      <header class="notice-single-header" data-animate="fade-up" data-delay="100">
        <h1 class="notice-single-tit"><?php the_title(); ?></h1>
        <time class="notice-single-date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
          <?php echo esc_html(get_the_date('Y-m-d')); ?>
        </time>
      </header>

      <div class="notice-single-body" data-animate="fade-up" data-delay="200">
        <?php the_content(); ?>
      </div>

      <?php if ($has_attachment) : ?>
      <div class="notice-single-files" data-animate="fade-up">
        <h3 class="notice-files-tit">첨부파일</h3>
        <ul class="notice-files-list">
          <?php
          $idx = 1;
          foreach ((array) $attachments as $att_id) :
            $file_url  = wp_get_attachment_url($att_id);
            $file_path = get_attached_file($att_id);
            if (!$file_url) continue;
            $file_name = basename($file_path);
          ?>
          <li class="notice-files-item">
            <a href="<?php echo esc_url($file_url); ?>" class="notice-files-link" download>
              <span class="notice-files-num">#<?php echo (int) $idx++; ?></span>
              <span class="notice-files-name"><?php echo esc_html($file_name); ?></span>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <nav class="notice-single-nav" aria-label="이전/다음 글">
        <div class="notice-nav-item is-prev">
          <span class="notice-nav-label">이전 글</span>
          <?php if ($prev_post) : ?>
            <a class="notice-nav-link" href="<?php echo esc_url(get_permalink($prev_post)); ?>">
              <?php echo esc_html(get_the_title($prev_post)); ?>
            </a>
          <?php else : ?>
            <span class="notice-nav-empty">이전 글이 없습니다.</span>
          <?php endif; ?>
        </div>
        <div class="notice-nav-item is-next">
          <span class="notice-nav-label">다음 글</span>
          <?php if ($next_post) : ?>
            <a class="notice-nav-link" href="<?php echo esc_url(get_permalink($next_post)); ?>">
              <?php echo esc_html(get_the_title($next_post)); ?>
            </a>
          <?php else : ?>
            <span class="notice-nav-empty">다음 글이 없습니다.</span>
          <?php endif; ?>
        </div>
      </nav>

      <div class="notice-single-actions" data-animate="fade-up">
        <a href="<?php echo esc_url($list_url); ?>" class="cm-btn">목록으로</a>
      </div>

    </div>
  </article>

  <?php endwhile; ?>

</main>

<?php get_footer(); ?>
