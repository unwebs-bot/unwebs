<?php
/**
 * Template Name: 회원가입 (Register)
 *
 * 가입 정보: 이메일, 비번, 회사명, 담당자명, 연락처 + 동의.
 * 가입 즉시 maintenance_pending 역할 → 관리자 승인 후 maintenance_client 전환.
 */
get_header();

$err_code = isset($_GET['uw_err']) ? sanitize_text_field(wp_unslash($_GET['uw_err'])) : '';
$ok_code  = isset($_GET['uw_ok'])  ? sanitize_text_field(wp_unslash($_GET['uw_ok']))  : '';

// 다중 에러 코드 (콤마 구분)
$err_codes = $err_code ? explode(',', $err_code) : array();

$prefill = array(
    'email'   => isset($_GET['uw_email'])   ? sanitize_email(wp_unslash($_GET['uw_email']))            : '',
    'company' => isset($_GET['uw_company']) ? sanitize_text_field(wp_unslash($_GET['uw_company']))     : '',
    'name'    => isset($_GET['uw_name'])    ? sanitize_text_field(wp_unslash($_GET['uw_name']))        : '',
    'phone'   => isset($_GET['uw_phone'])   ? sanitize_text_field(wp_unslash($_GET['uw_phone']))       : '',
);

$has_err = function($code) use ($err_codes) {
    return in_array($code, $err_codes, true);
};

// 가입 완료 상태
$is_complete = ($ok_code === 'registered');
?>


<main class="cm-main auth-main" id="main-content" role="main">

  <section class="auth-con sub-content-con">
    <div class="area">

      <div class="auth-card auth-card-register" data-animate="fade-up">

        <div class="auth-head">
          <h1 class="auth-tit"><?php echo $is_complete ? '가입 신청 완료' : '회원가입'; ?></h1>
          <p class="auth-txt">
            <?php if ($is_complete): ?>
              가입 신청이 접수되었습니다.<br>관리자 승인 후 이메일로 안내드리겠습니다.
            <?php else: ?>
              유지보수 회원 가입 후 관리자 승인 시 신청이 가능합니다.
            <?php endif; ?>
          </p>
        </div>

        <?php if ($is_complete): ?>
          <div class="auth-alert auth-alert-success" role="status">
            <i class="xi-check-circle" aria-hidden="true"></i>
            <span>가입 신청이 정상적으로 접수되었습니다. 승인 완료 시 메일로 알려드립니다.</span>
          </div>
          <div class="auth-form-foot">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="cm-btn cm-btn-primary auth-submit">메인으로</a>
          </div>
        <?php else: ?>

          <?php if (!empty($err_codes) && !$has_err('duplicate') && !$has_err('email') && !$has_err('password') && !$has_err('company') && !$has_err('name') && !$has_err('phone') && !$has_err('agree')): ?>
            <div class="auth-alert auth-alert-error" role="alert">
              <i class="xi-error-o" aria-hidden="true"></i>
              <span><?php echo esc_html(UW_Auth_Handler::get_error_message($err_codes[0])); ?></span>
            </div>
          <?php elseif ($has_err('duplicate')): ?>
            <div class="auth-alert auth-alert-error" role="alert">
              <i class="xi-error-o" aria-hidden="true"></i>
              <span><?php echo esc_html(UW_Auth_Handler::get_error_message('duplicate')); ?></span>
            </div>
          <?php endif; ?>

          <form class="auth-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" novalidate>
            <input type="hidden" name="action" value="uw_auth_register">
            <?php wp_nonce_field(UW_Auth_Handler::NONCE_REGISTER, 'uw_auth_register_nonce'); ?>

            <input type="text" name="uw_h" value="" tabindex="-1" autocomplete="off" class="auth-honeypot" aria-hidden="true">

            <div class="auth-field">
              <label class="auth-label" for="uw-reg-email">이메일 (아이디)</label>
              <input id="uw-reg-email"
                     class="cm-input <?php echo $has_err('email') || $has_err('duplicate') ? 'has-error' : ''; ?>"
                     type="email"
                     name="uw_email"
                     value="<?php echo esc_attr($prefill['email']); ?>"
                     placeholder="example@email.com"
                     autocomplete="username"
                     required>
              <?php if ($has_err('email')): ?>
                <p class="auth-field-error"><?php echo esc_html(UW_Auth_Handler::get_error_message('email')); ?></p>
              <?php endif; ?>
            </div>

            <div class="auth-field">
              <label class="auth-label" for="uw-reg-password">비밀번호</label>
              <input id="uw-reg-password"
                     class="cm-input <?php echo $has_err('password') ? 'has-error' : ''; ?>"
                     type="password"
                     name="uw_password"
                     placeholder="8자 이상"
                     autocomplete="new-password"
                     minlength="8"
                     required>
              <?php if ($has_err('password')): ?>
                <p class="auth-field-error"><?php echo esc_html(UW_Auth_Handler::get_error_message('password')); ?></p>
              <?php endif; ?>
            </div>

            <div class="auth-field">
              <label class="auth-label" for="uw-reg-company">회사명</label>
              <input id="uw-reg-company"
                     class="cm-input <?php echo $has_err('company') ? 'has-error' : ''; ?>"
                     type="text"
                     name="uw_company"
                     value="<?php echo esc_attr($prefill['company']); ?>"
                     placeholder="회사/기관명"
                     autocomplete="organization"
                     required>
              <?php if ($has_err('company')): ?>
                <p class="auth-field-error"><?php echo esc_html(UW_Auth_Handler::get_error_message('company')); ?></p>
              <?php endif; ?>
            </div>

            <div class="auth-field">
              <label class="auth-label" for="uw-reg-name">담당자명</label>
              <input id="uw-reg-name"
                     class="cm-input <?php echo $has_err('name') ? 'has-error' : ''; ?>"
                     type="text"
                     name="uw_name"
                     value="<?php echo esc_attr($prefill['name']); ?>"
                     placeholder="홍길동"
                     autocomplete="name"
                     required>
              <?php if ($has_err('name')): ?>
                <p class="auth-field-error"><?php echo esc_html(UW_Auth_Handler::get_error_message('name')); ?></p>
              <?php endif; ?>
            </div>

            <div class="auth-field">
              <label class="auth-label" for="uw-reg-phone">연락처</label>
              <input id="uw-reg-phone"
                     class="cm-input <?php echo $has_err('phone') ? 'has-error' : ''; ?>"
                     type="tel"
                     name="uw_phone"
                     value="<?php echo esc_attr($prefill['phone']); ?>"
                     placeholder="010-0000-0000"
                     autocomplete="tel"
                     required>
              <?php if ($has_err('phone')): ?>
                <p class="auth-field-error"><?php echo esc_html(UW_Auth_Handler::get_error_message('phone')); ?></p>
              <?php endif; ?>
            </div>

            <div class="auth-field auth-agree">
              <label class="auth-check">
                <input type="checkbox" name="uw_agree" value="1" required>
                <span>유지보수 회원 가입 시 관리자 승인 절차가 진행되며, 입력 정보는 본인 확인 및 안내 목적으로 보관됩니다.</span>
              </label>
              <?php if ($has_err('agree')): ?>
                <p class="auth-field-error"><?php echo esc_html(UW_Auth_Handler::get_error_message('agree')); ?></p>
              <?php endif; ?>
            </div>

            <button type="submit" class="cm-btn cm-btn-primary auth-submit">가입 신청</button>
          </form>

          <nav class="auth-links" aria-label="계정 부가 링크">
            <span class="auth-links-txt">이미 회원이신가요?</span>
            <a href="<?php echo esc_url(home_url('/login/')); ?>">로그인</a>
          </nav>

        <?php endif; ?>
      </div>

    </div>
  </section>

</main>

<?php get_footer(); ?>
