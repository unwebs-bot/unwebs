<?php
/**
 * 404 Not Found Template
 */
get_header();
?>

<main class="cm-main" id="main-content" role="main">
  <section class="cm-error-con">
    <div class="area">
      <div class="cm-error-wrap" data-animate="fade-up">
        <div class="cm-error-code">404</div>
        <h1 class="cm-error-tit">페이지를 찾을 수 없습니다</h1>
        <p class="cm-error-txt">
          요청하신 페이지가 삭제되었거나 주소가 변경되었을 수 있습니다.<br>
          아래 링크를 통해 다른 페이지를 확인해주세요.
        </p>
        <div class="cm-error-actions">
          <a href="<?php echo esc_url(home_url('/')); ?>" class="cm-btn cm-btn-primary">홈으로 이동</a>
          <a href="<?php echo esc_url(home_url('/contact')); ?>" class="cm-btn">문의하기</a>
        </div>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
