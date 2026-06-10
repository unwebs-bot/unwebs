<?php
/**
 * Template Name: 비밀번호 재설정 (Reset Password)
 *
 * URL: /resetpassword/?key=...&login=...
 * key/login 검증 후 새 비밀번호 입력 → handle_resetpassword 처리.
 */
get_header();

$err_code = isset($_GET['uw_err']) ? sanitize_text_field(wp_unslash($_GET['uw_err'])) : '';
$key      = isset($_GET['key'])    ? sanitize_text_field(wp_unslash($_GET['key']))    : '';
$login    = isset($_GET['login'])  ? sanitize_text_field(wp_unslash($_GET['login']))  : '';

$is_valid = false;
$user     = null;
if ($key && $login) {
    $user = check_password_reset_key($key, $login);
    $is_valid = !is_wp_error($user);
}

$error_msg = '';
if ($err_code) {
    $error_msg = UW_Auth_Handler::get_error_message($err_code);
}
?>


<main class="cm-main auth-main" id="main-content" role="main">

  <section class="auth-con sub-content-con">
    <div class="area">

      <div class="auth-card auth-card-resetpw" data-animate="fade-up">

        <div class="auth-head">
          <h1 class="auth-tit">비밀번호 재설정</h1>
          <p class="auth-txt">
            <?php if ($is_valid): ?>
              새 비밀번호를 입력해주세요.
            <?php else: ?>
              재설정 링크가 만료되었거나 잘못된 요청입니다.
            <?php endif; ?>
          </p>
        </div>

        <?php if (!$is_valid): ?>

          <div class="auth-alert auth-alert-error" role="alert">
            <i class="xi-error-o" aria-hidden="true"></i>
            <span>비밀번호 찾기 페이지에서 다시 요청해주세요.</span>
          </div>
          <div class="auth-form-foot">
            <a href="<?php echo esc_url(home_url('/lostpassword/')); ?>" class="cm-btn cm-btn-primary auth-submit">비밀번호 찾기로</a>
          </div>

        <?php else: ?>

          <?php if ($error_msg): ?>
            <div class="auth-alert auth-alert-error" role="alert">
              <i class="xi-error-o" aria-hidden="true"></i>
              <span><?php echo esc_html($error_msg); ?></span>
            </div>
          <?php endif; ?>

          <form class="auth-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" novalidate>
            <input type="hidden" name="action" value="uw_auth_resetpassword">
            <?php wp_nonce_field(UW_Auth_Handler::NONCE_RESETPW, 'uw_auth_resetpw_nonce'); ?>
            <input type="hidden" name="uw_key"   value="<?php echo esc_attr($key); ?>">
            <input type="hidden" name="uw_login" value="<?php echo esc_attr($login); ?>">

            <div class="auth-field">
              <label class="auth-label" for="uw-reset-pw1">새 비밀번호</label>
              <input id="uw-reset-pw1"
                     class="cm-input"
                     type="password"
                     name="uw_password"
                     placeholder="8자 이상"
                     autocomplete="new-password"
                     minlength="8"
                     required>
            </div>

            <div class="auth-field">
              <label class="auth-label" for="uw-reset-pw2">새 비밀번호 확인</label>
              <input id="uw-reset-pw2"
                     class="cm-input"
                     type="password"
                     name="uw_password2"
                     placeholder="동일한 비밀번호 입력"
                     autocomplete="new-password"
                     minlength="8"
                     required>
            </div>

            <button type="submit" class="cm-btn cm-btn-primary auth-submit">비밀번호 변경</button>
          </form>

        <?php endif; ?>
      </div>

    </div>
  </section>

</main>

<?php get_footer(); ?>
