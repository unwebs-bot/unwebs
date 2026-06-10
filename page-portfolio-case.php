<?php
/**
 * Portfolio Case Template (커스텀 라우트 `/portfolio/{slug}/`)
 *
 * 데이터 소스: `inc/cm-portfolio-main.php` 하드코딩 큐레이션.
 * 라우트 처리: functions.php의 rewrite rule + template_include.
 *
 * 콘텐츠 영역(추가 갤러리·프로세스 등)은 `hero_intro` 외에는 미니멀.
 * 향후 확장 시 cm_portfolio_dataset()에 필드 추가 후 이 템플릿에서 노출.
 */

if (!defined('ABSPATH')) exit;

$slug = get_query_var('cm_port_slug');
$row  = function_exists('cm_get_portfolio_by_slug') ? cm_get_portfolio_by_slug($slug) : null;

if (!$row) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part('404');
    return;
}

$title        = $row['title'] ?? '';
$industry     = $row['industry'] ?? '';
$service      = $row['type'] ?? '';
$image        = trim((string) ($row['image'] ?? ''));
$external_url = trim((string) ($row['external_url'] ?? ''));
$external_safe = $external_url !== '' ? esc_url_raw($external_url, array('http', 'https')) : '';
$description  = $row['description'] ?? '';
$hero_intro   = $row['hero_intro'] ?? '';

// 페이지 타이틀 — Rank Math가 처리할 수 있도록 add_filter
add_filter('pre_get_document_title', function () use ($title) {
    return esc_html($title) . ' | ' . get_bloginfo('name');
}, 99);

get_header();
?>

<main class="cm-main port-single-con" id="main-content" role="main">

  <!-- Hero -->
  <section class="port-single-hero">
    <div class="area">
      <div class="port-single-hero-meta">
        <?php if ($industry) : ?>
          <span class="port-single-hero-cat"><?php echo esc_html($industry); ?></span>
        <?php endif; ?>
        <?php if ($service) : ?>
          <span class="port-single-hero-service"><?php echo esc_html($service); ?></span>
        <?php endif; ?>
      </div>

      <h1 class="port-single-hero-tit"><?php echo esc_html($title); ?></h1>

      <?php if ($hero_intro) : ?>
        <p class="port-single-hero-intro"><?php echo esc_html($hero_intro); ?></p>
      <?php endif; ?>

      <?php if ($external_safe) : ?>
        <a href="<?php echo esc_url($external_safe); ?>"
           class="port-single-hero-cta cm-btn cm-btn-primary"
           target="_blank" rel="noopener noreferrer">
          사이트 방문 <i class="xi-external-link" aria-hidden="true"></i>
        </a>
      <?php endif; ?>
    </div>

    <?php if ($image) :
      $image_src = strpos($image, 'http') === 0 ? $image : home_url('/' . ltrim($image, '/'));
    ?>
      <div class="port-single-hero-img">
        <img src="<?php echo esc_url($image_src); ?>"
             alt="<?php echo esc_attr($title . ' 메인 이미지'); ?>">
      </div>
    <?php endif; ?>
  </section>

  <!-- Body — description 등 추가 콘텐츠 -->
  <?php if ($description) : ?>
  <section class="port-single-body">
    <div class="area">
      <div class="port-single-content">
        <p><?php echo esc_html($description); ?></p>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- 메인으로 돌아가기 -->
  <section class="port-single-more">
    <div class="area">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="cm-btn">
        <i class="xi-arrow-left" aria-hidden="true"></i> 메인으로
      </a>
    </div>
  </section>

</main>

<?php
get_footer();
