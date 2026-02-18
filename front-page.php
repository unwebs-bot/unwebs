<?php
/**
 * Front Page Template
 */
get_header();
?>

<main class="uw-main" id="main-content" role="main">

  <!-- 메인 비주얼 - 포트폴리오 쇼케이스 -->
  <?php get_template_part('template-parts/main/visual', 'portfolio'); ?>

  <!-- 실시간 프로젝트 현황 대시보드 -->
  <?php get_template_part('template-parts/main/section', 'dashboard'); ?>

  <!-- 포트폴리오 쇼케이스 -->
  <?php get_template_part('template-parts/main/section', 'portfolio'); ?>

  <!-- 유지보수 서비스 현황 -->
  <?php get_template_part('template-parts/main/section', 'maintenance'); ?>

  <!-- 일정관리 프로세스 -->
  <?php get_template_part('template-parts/main/section', 'schedule'); ?>

  <!-- 데이터 수치 카운터 -->
  <?php get_template_part('template-parts/main/section', 'stats'); ?>

</main>

<?php get_footer(); ?>
