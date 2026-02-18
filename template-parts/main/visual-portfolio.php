<?php
/**
 * Template Part: 메인 비주얼 - 포트폴리오 쇼케이스
 */

if (!defined('ABSPATH')) exit;

$slides = array(
  array(
    'bg'       => get_theme_file_uri('/assets/images/visual_bg01.png'),
    'logo'     => get_theme_file_uri('/assets/images/visual_logo01.png'),
    'title'    => '<strong>LG 디오스 팝업</strong><br>랜딩페이지 제작',
    'category' => '기업 / 비즈니스',
    'type'     => '반응형 홈페이지 (PC, MOB)',
    'period'   => '3-6개월',
    'logo_h'   => '30px',
    'pc_img'   => get_theme_file_uri('/assets/images/visual_pc01.png'),
    'mob_img'  => get_theme_file_uri('/assets/images/visual_mob01.png'),
  ),
  array(
    'bg'       => get_theme_file_uri('/assets/images/visual_bg02.png'),
    'logo'     => get_theme_file_uri('/assets/images/visual_logo02.png'),
    'title'    => '<strong>코딩 유튜브 국내 1위</strong><br>조코딩넷 제작',
    'category' => '기업 / 비즈니스',
    'type'     => '반응형 홈페이지 (PC, MOB)',
    'period'   => '1-3개월',
    'logo_h'   => '60px',
    'pc_img'   => get_theme_file_uri('/assets/images/visual_pc02.png'),
    'mob_img'  => get_theme_file_uri('/assets/images/visual_mob02.png'),
  ),
  array(
    'bg'       => get_theme_file_uri('/assets/images/visual_bg03.png'),
    'logo'     => get_theme_file_uri('/assets/images/visual_logo03.png'),
    'title'    => '<strong>LG전자 미디어아트</strong><br>에이프레임',
    'category' => '기업 / 비즈니스',
    'type'     => '반응형 홈페이지 (PC, MOB)',
    'period'   => '1-3개월',
    'logo_h'   => '30px',
    'pc_img'   => get_theme_file_uri('/assets/images/visual_pc03.png'),
    'mob_img'  => get_theme_file_uri('/assets/images/visual_mob03.png'),
  ),
  array(
    'bg'       => get_theme_file_uri('/assets/images/visual_bg04.png'),
    'logo'     => get_theme_file_uri('/assets/images/visual_logo04.png'),
    'title'    => '<strong>해외자원개발 심포지엄</strong><br>웹사이트 구축',
    'category' => '협회 / 기관',
    'type'     => '반응형 홈페이지 (PC, MOB)',
    'period'   => '1-3개월',
    'logo_h'   => '30px',
    'pc_img'   => get_theme_file_uri('/assets/images/visual_pc04.png'),
    'mob_img'  => get_theme_file_uri('/assets/images/visual_mob04.png'),
  ),
);
?>

<section class="uw-visual">

  <?php foreach ($slides as $i => $slide) : ?>
  <div class="uw-visual__slide<?php echo ($i === 0) ? ' is-active' : ''; ?>" data-index="<?php echo $i; ?>">

    <div class="uw-visual__bg-img" style="background-image: url('<?php echo esc_url($slide['bg']); ?>');"></div>
    <div class="uw-visual__bg-effect"></div>

    <div class="uw-visual__container">

      <!-- Left: Project Info -->
      <div class="uw-visual__info">

        <div class="uw-visual__header">
          <div class="uw-visual__logo">
            <img src="<?php echo esc_url($slide['logo']); ?>" alt="브랜드 로고" style="max-height: <?php echo esc_attr($slide['logo_h']); ?>">
          </div>
          <h2 class="uw-visual__title"><?php echo $slide['title']; ?></h2>
        </div>

        <dl class="uw-visual__meta">
          <div class="uw-visual__meta-item">
            <dt class="uw-visual__meta-label">Category</dt>
            <dd class="uw-visual__meta-value"><?php echo esc_html($slide['category']); ?></dd>
          </div>
          <div class="uw-visual__meta-item">
            <dt class="uw-visual__meta-label">Type</dt>
            <dd class="uw-visual__meta-value"><?php echo esc_html($slide['type']); ?></dd>
          </div>
          <div class="uw-visual__meta-item">
            <dt class="uw-visual__meta-label">Period</dt>
            <dd class="uw-visual__meta-value"><?php echo esc_html($slide['period']); ?></dd>
          </div>
        </dl>

        <div class="uw-visual__actions">
          <a href="#" class="uw-visual__btn"><i class="xi-library-books-o"></i> 제작사례</a>
          <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="uw-visual__btn uw-visual__btn--primary"><i class="xi-pen"></i> 견적문의</a>
        </div>

        <!-- 인디케이터 -->
        <div class="uw-visual__nav">
          <?php for ($j = 0; $j < count($slides); $j++) : ?>
          <div class="uw-visual__nav-item<?php echo ($j === 0) ? ' is-active' : ''; ?>" data-slide="<?php echo $j; ?>">
            <div class="uw-visual__nav-fill"></div>
          </div>
          <?php endfor; ?>
        </div>

      </div>

      <!-- Right: Device Mockups -->
      <div class="uw-visual__devices">
        <div class="uw-visual__tablet">
          <div class="uw-visual__screen">
            <img src="<?php echo esc_url($slide['pc_img']); ?>" alt="PC View" class="uw-visual__scroll-img">
          </div>
          <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/Tablet.png')); ?>" alt="" class="uw-visual__tablet-frame" aria-hidden="true">
        </div>

        <div class="uw-visual__phone">
          <img src="<?php echo esc_url($slide['mob_img']); ?>" alt="Mobile View">
        </div>
      </div>

    </div>

  </div>
  <?php endforeach; ?>

  <!-- Scroll Down -->
  <div class="uw-visual__scroll">
    <span class="uw-visual__scroll-text">Scroll Down</span>
    <div class="uw-visual__scroll-line"></div>
  </div>

</section>
