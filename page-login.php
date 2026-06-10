<?php
/**
 * Template Name: 로그인 (Login)
 *
 * figma 117:540. 단일 카드 폼 (이메일/비번/링크 3종).
 *  - admin-post.php?action=uw_auth_login 으로 전송
 *  - redirect_to 쿼리 지원
 *  - 에러는 ?uw_err= 로 표시
 */
get_header();

$err_code = isset($_GET['uw_err']) ? sanitize_text_field(wp_unslash($_GET['uw_err'])) : '';
$ok_code  = isset($_GET['uw_ok'])  ? sanitize_text_field(wp_unslash($_GET['uw_ok']))  : '';
$prefill_email = isset($_GET['uw_email']) ? sanitize_email(wp_unslash($_GET['uw_email'])) : '';

$redirect_to = '';
if (!empty($_GET['redirect_to'])) {
    $redirect_to = esc_url_raw(wp_unslash($_GET['redirect_to']));
}

$error_msg = '';
if ($err_code) {
    $error_msg = UW_Auth_Handler::get_error_message($err_code);
}

$success_msg = '';
if ($ok_code === 'reset') {
    $success_msg = '비밀번호가 변경되었습니다. 새 비밀번호로 로그인해주세요.';
}
?>


<main class="cm-main auth-main" id="main-content" role="main">

  <section class="auth-con sub-content-con">
    <div class="area">

      <div class="auth-card auth-card-login" data-animate="fade-up">

        <div class="auth-head">
          <h1 class="auth-tit">로그인</h1>
          <p class="auth-txt">가입하신 계정으로 로그인하여 서비스를 이용해보세요.</p>
        </div>

        <?php if ($error_msg): ?>
          <div class="auth-alert auth-alert-error" role="alert">
            <i class="xi-error-o" aria-hidden="true"></i>
            <span><?php echo esc_html($error_msg); ?></span>
          </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
          <div class="auth-alert auth-alert-success" role="status">
            <i class="xi-check-circle" aria-hidden="true"></i>
            <span><?php echo esc_html($success_msg); ?></span>
          </div>
        <?php endif; ?>

        <form class="auth-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" novalidate>
          <input type="hidden" name="action" value="uw_auth_login">
          <?php wp_nonce_field(UW_Auth_Handler::NONCE_LOGIN, 'uw_auth_login_nonce'); ?>
          <?php if ($redirect_to): ?>
            <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>">
          <?php endif; ?>

          <!-- honeypot -->
          <input type="text" name="uw_h" value="" tabindex="-1" autocomplete="off" class="auth-honeypot" aria-hidden="true">

          <div class="auth-field">
            <label class="blind" for="uw-login-email">이메일</label>
            <input id="uw-login-email"
                   class="cm-input"
                   type="email"
                   name="uw_email"
                   value="<?php echo esc_attr($prefill_email); ?>"
                   placeholder="아이디 (이메일)"
                   autocomplete="username"
                   required>
          </div>

          <div class="auth-field">
            <label class="blind" for="uw-login-password">비밀번호</label>
            <input id="uw-login-password"
                   class="cm-input"
                   type="password"
                   name="uw_password"
                   placeholder="비밀번호"
                   autocomplete="current-password"
                   required>
          </div>

          <div class="auth-field auth-remember">
            <label class="auth-check">
              <input type="checkbox" name="uw_remember" value="1">
              <span>로그인 상태 유지</span>
            </label>
          </div>

          <button type="submit" class="cm-btn cm-btn-primary auth-submit">로그인</button>
        </form>

        <nav class="auth-links" aria-label="계정 부가 링크">
          <a href="<?php echo esc_url(home_url('/lostpassword/')); ?>">아이디 찾기</a>
          <span class="auth-links-sep" aria-hidden="true"></span>
          <a href="<?php echo esc_url(home_url('/lostpassword/')); ?>">비밀번호 찾기</a>
          <span class="auth-links-sep" aria-hidden="true"></span>
          <a href="<?php echo esc_url(home_url('/register/')); ?>">회원가입</a>
        </nav>

      </div>

    </div>
  </section>

</main>

<?php get_footer(); ?>
