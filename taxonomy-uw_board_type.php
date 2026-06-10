<?php
/**
 * Taxonomy Archive — 공지사항 / FAQ (uw_board_type)
 * URL: /notice/ or /faq/  (CPT post type archive가 아니라 taxonomy archive)
 *
 * 템플릿 hierarchy: taxonomy-uw_board_type.php → archive.php → index.php
 * FAQ 타입일 경우 FAQPage JSON-LD 스키마 자동 출력 대상 (Rank Math Schema Generator로 설정).
 */
get_header();

// 현재 taxonomy 상태로 타입 판단
$board_type = '';
if (is_tax('uw_board_type')) {
    $term = get_queried_object();
    $board_type = $term->slug;
} else {
    // URL 기반 fallback
    $uri = isset($_SERVER['REQUEST_URI'])
        ? trim(parse_url(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])), PHP_URL_PATH), '/')
        : '';
    if (strpos($uri, 'faq') === 0) {
        $board_type = 'faq';
    } elseif (strpos($uri, 'notice') === 0) {
        $board_type = 'notice';
    }
}

$is_faq        = ($board_type === 'faq');
$archive_title = $is_faq ? '자주 묻는 질문' : '공지사항';
$category_sub  = '고객지원';
?>

<main class="cm-main" id="main-content" role="main">

  <h1 class="blind"><?php echo esc_html($archive_title); ?></h1>

  <section class="board-archive-con sub-content-con">
    <div class="area">

      <div class="cm-tit-box" data-animate="fade-up">
        <span class="cm-tit-sub"><?php echo esc_html($category_sub); ?></span>
        <h2 class="cm-tit"><?php echo esc_html($archive_title); ?></h2>
      </div>

      <?php if ($is_faq) : ?>
      <!-- FAQ: 아코디언 형태 -->
      <?php if (have_posts()) : ?>
      <div class="board-faq-list" data-animate="fade-up">
        <?php while (have_posts()) : the_post(); ?>
        <details class="cm-faq-item">
          <summary class="cm-faq-q">
            <span class="cm-faq-q-mark">Q</span>
            <span class="cm-faq-q-txt"><?php the_title(); ?></span>
          </summary>
          <div class="cm-faq-a">
            <span class="cm-faq-a-mark">A</span>
            <div class="cm-faq-a-txt"><?php the_content(); ?></div>
          </div>
        </details>
        <?php endwhile; ?>
      </div>
      <?php else : ?>
      <p class="board-empty">등록된 항목이 없습니다.</p>
      <?php endif; ?>

      <?php else :
        // Notice: 공지 고정 우선 → 일반글, 테이블 3컬럼

        $paged    = max(1, (int) get_query_var('paged'));
        $per_page = 10;

        // 1페이지에서만 고정글 노출
        $pinned_query = null;
        if ($paged === 1) {
          $pinned_query = new WP_Query(array(
            'post_type'      => 'uw_board',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_key'       => '_uw_is_pinned',
            'meta_value'     => '1',
            'tax_query'      => array(array(
              'taxonomy' => 'uw_board_type',
              'field'    => 'slug',
              'terms'    => 'notice',
            )),
            'orderby'        => 'date',
            'order'          => 'DESC',
          ));
        }

        // 일반글 — 고정글 제외
        $list_query = new WP_Query(array(
          'post_type'      => 'uw_board',
          'post_status'    => 'publish',
          'posts_per_page' => $per_page,
          'paged'          => $paged,
          'orderby'        => 'date',
          'order'          => 'DESC',
          'tax_query'      => array(array(
            'taxonomy' => 'uw_board_type',
            'field'    => 'slug',
            'terms'    => 'notice',
          )),
          'meta_query'     => array(
            'relation' => 'OR',
            array('key' => '_uw_is_pinned', 'compare' => 'NOT EXISTS'),
            array('key' => '_uw_is_pinned', 'value' => '1', 'compare' => '!='),
          ),
        ));

        $has_pinned = $pinned_query && $pinned_query->have_posts();
        $has_list   = $list_query->have_posts();
      ?>

      <?php if ($has_pinned || $has_list) : ?>
      <div class="notice-list-wrap" data-animate="fade-up" data-delay="100">
        <table class="notice-list">
          <colgroup>
            <col class="notice-col-num">
            <col class="notice-col-tit">
            <col class="notice-col-date">
          </colgroup>
          <thead>
            <tr>
              <th scope="col" class="notice-th-num">NO</th>
              <th scope="col" class="notice-th-tit">제목</th>
              <th scope="col" class="notice-th-date">작성시간</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // 고정글
            if ($has_pinned) :
              while ($pinned_query->have_posts()) : $pinned_query->the_post();
                $attachments    = get_post_meta(get_the_ID(), '_uw_attachments', true);
                $has_attachment = !empty($attachments);
            ?>
            <tr class="notice-row is-pinned">
              <td class="notice-cell-num"><span class="notice-badge-pin">공지</span></td>
              <td class="notice-cell-tit">
                <a href="<?php the_permalink(); ?>" class="notice-link">
                  <span class="notice-tit-txt"><?php the_title(); ?></span>
                  <?php if ($has_attachment) : ?>
                    <span class="notice-icon-file" aria-label="첨부파일 있음"></span>
                  <?php endif; ?>
                </a>
              </td>
              <td class="notice-cell-date">
                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                  <?php echo esc_html(get_the_date('Y-m-d')); ?>
                </time>
              </td>
            </tr>
            <?php
              endwhile;
              wp_reset_postdata();
            endif;

            // 일반글
            $total = $list_query->found_posts;
            $num   = $total - (($paged - 1) * $per_page);

            if ($has_list) :
              while ($list_query->have_posts()) : $list_query->the_post();
                $attachments    = get_post_meta(get_the_ID(), '_uw_attachments', true);
                $has_attachment = !empty($attachments);
            ?>
            <tr class="notice-row">
              <td class="notice-cell-num"><?php echo (int) $num--; ?></td>
              <td class="notice-cell-tit">
                <a href="<?php the_permalink(); ?>" class="notice-link">
                  <span class="notice-tit-txt"><?php the_title(); ?></span>
                  <?php if ($has_attachment) : ?>
                    <span class="notice-icon-file" aria-label="첨부파일 있음"></span>
                  <?php endif; ?>
                </a>
              </td>
              <td class="notice-cell-date">
                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                  <?php echo esc_html(get_the_date('Y-m-d')); ?>
                </time>
              </td>
            </tr>
            <?php
              endwhile;
              wp_reset_postdata();
            endif;
            ?>
          </tbody>
        </table>

        <?php
        $total_pages = $list_query->max_num_pages;
        if ($total_pages > 1) :
        ?>
        <nav class="cm-pagination" aria-label="페이지네이션">
          <?php
          echo paginate_links(array(
            'base'      => trailingslashit(home_url('/notice')) . '%_%',
            'format'    => 'page/%#%/',
            'current'   => $paged,
            'total'     => $total_pages,
            'prev_text' => '이전',
            'next_text' => '다음',
            'mid_size'  => 2,
            'end_size'  => 1,
          ));
          ?>
        </nav>
        <?php endif; ?>

      </div>
      <?php else : ?>
      <p class="board-empty">등록된 공지사항이 없습니다.</p>
      <?php endif; ?>

      <?php endif; ?>

    </div>
  </section>

</main>

<?php get_footer(); ?>
