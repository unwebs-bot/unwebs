<?php
/**
 * Template Part: 메인 비주얼 - 포트폴리오 쇼케이스
 */

if (!defined('ABSPATH')) exit;

$slides = array(
  array(
    'bg'       => get_theme_file_uri('/assets/images/main/visual_bg01.png'),
    'logo'     => get_theme_file_uri('/assets/images/main/visual_logo01.png'),
    'title'    => '<strong>LG 디오스 팝업</strong><br>랜딩페이지 제작',
    'category' => '기업 / 비즈니스',
    'type'     => '반응형 홈페이지 (PC, MOB)',
    'period'   => '3-6개월',
    'logo_h'   => '30px',
    'pc_img'   => get_theme_file_uri('/assets/images/main/visual_pc01.png'),
    'mob_img'  => get_theme_file_uri('/assets/images/main/visual_mob01.png'),
  ),
  array(
    'bg'       => get_theme_file_uri('/assets/images/main/visual_bg02.png'),
    'logo'     => get_theme_file_uri('/assets/images/main/visual_logo02.png'),
    'title'    => '<strong>코딩 유튜브 국내 1위</strong><br>조코딩넷 제작',
    'category' => '기업 / 비즈니스',
    'type'     => '반응형 홈페이지 (PC, MOB)',
    'period'   => '1-3개월',
    'logo_h'   => '60px',
    'pc_img'   => get_theme_file_uri('/assets/images/main/visual_pc02.png'),
    'mob_img'  => get_theme_file_uri('/assets/images/main/visual_mob02.png'),
  ),
  array(
    'bg'       => get_theme_file_uri('/assets/images/main/visual_bg03.png'),
    'logo'     => get_theme_file_uri('/assets/images/main/visual_logo03.png'),
    'title'    => '<strong>LG전자 미디어아트</strong><br>에이프레임',
    'category' => '기업 / 비즈니스',
    'type'     => '반응형 홈페이지 (PC, MOB)',
    'period'   => '1-3개월',
    'logo_h'   => '30px',
    'pc_img'   => get_theme_file_uri('/assets/images/main/visual_pc03.png'),
    'mob_img'  => get_theme_file_uri('/assets/images/main/visual_mob03.png'),
  ),
  array(
    'bg'       => get_theme_file_uri('/assets/images/main/visual_bg04.png'),
    'logo'     => get_theme_file_uri('/assets/images/main/visual_logo04.png'),
    'title'    => '<strong>해외자원개발 심포지엄</strong><br>웹사이트 구축',
    'category' => '협회 / 기관',
    'type'     => '반응형 홈페이지 (PC, MOB)',
    'period'   => '1-3개월',
    'logo_h'   => '30px',
    'pc_img'   => get_theme_file_uri('/assets/images/main/visual_pc04.png'),
    'mob_img'  => get_theme_file_uri('/assets/images/main/visual_mob04.png'),
  ),
);
?>

<section class="main-visual-con">

  <?php foreach ($slides as $i => $slide) : ?>
  <div class="main-visual-slide<?php echo ($i === 0) ? ' is-active' : ''; ?>" data-index="<?php echo $i; ?>">

    <div class="main-visual-bg-img" style="background-image: url('<?php echo esc_url($slide['bg']); ?>');"></div>
    <div class="main-visual-bg-effect"></div>

    <div class="area">
      <div class="main-visual-wrap">

      <!-- Left: Project Info -->
      <div class="main-visual-info">

        <div class="main-visual-header">
          <div class="main-visual-logo">
            <img src="<?php echo esc_url($slide['logo']); ?>" alt="<?php echo esc_attr(wp_strip_all_tags(str_replace('<br>', ' ', $slide['title'])) . ' 로고'); ?>" style="max-height: <?php echo esc_attr($slide['logo_h']); ?>">
          </div>
          <h2 class="main-visual-tit"><?php echo $slide['title']; ?></h2>
        </div>

        <dl class="main-visual-meta">
          <div class="main-visual-meta-item">
            <dt class="main-visual-meta-label">Category</dt>
            <dd class="main-visual-meta-value"><?php echo esc_html($slide['category']); ?></dd>
          </div>
          <div class="main-visual-meta-item">
            <dt class="main-visual-meta-label">Type</dt>
            <dd class="main-visual-meta-value"><?php echo esc_html($slide['type']); ?></dd>
          </div>
          <div class="main-visual-meta-item">
            <dt class="main-visual-meta-label">Period</dt>
            <dd class="main-visual-meta-value"><?php echo esc_html($slide['period']); ?></dd>
          </div>
        </dl>

        <div class="main-visual-actions">
          <a href="#" class="main-visual-btn"><i class="xi-library-books-o"></i> 제작사례</a>
          <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="main-visual-btn main-visual-btn-primary"><i class="xi-pen"></i> 견적문의</a>
        </div>

        <!-- 인디케이터 -->
        <div class="main-visual-nav">
          <?php for ($j = 0; $j < count($slides); $j++) : ?>
          <div class="main-visual-nav-item<?php echo ($j === 0) ? ' is-active' : ''; ?>" data-slide="<?php echo $j; ?>">
            <div class="main-visual-nav-fill"></div>
          </div>
          <?php endfor; ?>
        </div>

      </div>

      <!-- Right: Device Mockups -->
      <div class="main-visual-devices">
        <div class="main-visual-tablet">
          <div class="main-visual-screen">
            <img src="<?php echo esc_url($slide['pc_img']); ?>" alt="PC View" class="main-visual-scroll-img">
          </div>
          <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/main/Tablet.png')); ?>" alt="" class="main-visual-tablet-frame" aria-hidden="true">
        </div>

        <div class="main-visual-phone">
          <img src="<?php echo esc_url($slide['mob_img']); ?>" alt="Mobile View">
        </div>
      </div>

      </div>
    </div>

  </div>
  <?php endforeach; ?>

</section>
