<?php
/**
 * Front Page Template
 */
get_header();
?>

<main class="cm-main" id="main-content" role="main">

  <h1 class="blind">홈페이지 제작·리뉴얼·유지보수</h1>

  <!-- 메인 비주얼 - 포트폴리오 쇼케이스 -->
  <?php get_template_part('template-parts/main/visual', 'portfolio'); ?>

  <!-- 실시간 프로젝트 현황 대시보드 -->
  <?php get_template_part('template-parts/main/section', 'dashboard'); ?>

  <!-- 실시간 프로젝트 (이미지 슬라이드 marquee) -->
  <?php get_template_part('template-parts/main/section', 'projects'); ?>

  <!-- 데이터 수치 카운터 -->
  <?php get_template_part('template-parts/main/section', 'stats'); ?>

  <!-- 포트폴리오 쇼케이스 -->
  <?php get_template_part('template-parts/main/section', 'portfolio'); ?>

  <!-- 유지보수 서비스 현황 -->
  <?php get_template_part('template-parts/main/section', 'maintenance'); ?>

  <!-- 고객 후기 -->
  <?php get_template_part('template-parts/main/section', 'reviews'); ?>

  <!-- 홈페이지 전문 칼럼 -->
  <?php get_template_part('template-parts/main/section', 'columns'); ?>

  <!-- 자주 묻는 질문 -->
  <?php get_template_part('template-parts/main/section', 'faq'); ?>

  <!-- 문의하기 -->
  <?php get_template_part('template-parts/main/section', 'contact'); ?>

</main>

<?php get_footer(); ?>
