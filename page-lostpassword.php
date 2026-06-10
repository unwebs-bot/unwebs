<?php
/**
 * Template Name: 비밀번호 찾기 (Lost Password)
 *
 * 이메일 = 아이디이므로 아이디 찾기/비번 찾기를 한 페이지로 통합.
 * 사용자 존재 여부는 노출하지 않음 (메시지 항상 동일).
 */
get_header();

$err_code = isset($_GET['uw_err']) ? sanitize_text_field(wp_unslash($_GET['uw_err'])) : '';
$ok_code  = isset($_GET['uw_ok'])  ? sanitize_text_field(wp_unslash($_GET['uw_ok']))  : '';

$is_sent = ($ok_code === 'sent');

$error_msg = '';
if ($err_code) {
    $error_msg = UW_Auth_Handler::get_error_message($err_code);
}
?>


<main class="cm-main auth-main" id="main-content" role="main">

  <section class="auth-con sub-content-con">
    <div class="area">

      <div class="auth-card auth-card-lostpw" data-animate="fade-up">

        <div class="auth-head">
          <h1 class="auth-tit"><?php echo $is_sent ? '메일이 발송되었습니다' : '아이디 / 비밀번호 찾기'; ?></h1>
          <p class="auth-txt">
            <?php if ($is_sent): ?>
              입력하신 이메일로 비밀번호 재설정 안내를 보내드렸습니다.<br>메일함을 확인해주세요.
            <?php else: ?>
              가입하신 <strong>이메일이 곧 아이디</strong>입니다.<br>이메일을 입력하시면 비밀번호 재설정 링크를 보내드립니다.
            <?php endif; ?>
          </p>
        </div>

        <?php if ($is_sent): ?>

          <div class="auth-alert auth-alert-success" role="status">
            <i class="xi-check-circle" aria-hidden="true"></i>
            <span>메일이 도착하지 않으면 스팸함을 확인하거나 잠시 후 다시 시도해주세요.</span>
          </div>
          <div class="auth-form-foot">
            <a href="<?php echo esc_url(home_url('/login/')); ?>" class="cm-btn cm-btn-primary auth-submit">로그인으로</a>
          </div>

        <?php else: ?>

          <?php if ($error_msg): ?>
            <div class="auth-alert auth-alert-error" role="alert">
              <i class="xi-error-o" aria-hidden="true"></i>
              <span><?php echo esc_html($error_msg); ?></span>
            </div>
          <?php endif; ?>

          <form class="auth-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" novalidate>
            <input type="hidden" name="action" value="uw_auth_lostpassword">
            <?php wp_nonce_field(UW_Auth_Handler::NONCE_LOSTPW, 'uw_auth_lostpw_nonce'); ?>

            <input type="text" name="uw_h" value="" tabindex="-1" autocomplete="off" class="auth-honeypot" aria-hidden="true">

            <div class="auth-field">
              <label class="blind" for="uw-lostpw-email">이메일</label>
              <input id="uw-lostpw-email"
                     class="cm-input"
                     type="email"
                     name="uw_email"
                     placeholder="가입하신 이메일 (아이디)"
                     autocomplete="username"
                     required>
            </div>

            <button type="submit" class="cm-btn cm-btn-primary auth-submit">재설정 메일 받기</button>
          </form>

          <nav class="auth-links" aria-label="계정 부가 링크">
            <a href="<?php echo esc_url(home_url('/login/')); ?>">로그인</a>
            <span class="auth-links-sep" aria-hidden="true"></span>
            <a href="<?php echo esc_url(home_url('/register/')); ?>">회원가입</a>
          </nav>

        <?php endif; ?>
      </div>

    </div>
  </section>

</main>

<?php get_footer(); ?>
