<?php
/**
 * UW Auth Handler — 프론트엔드 인증 처리
 *
 * 처리 액션:
 *  - uw_auth_login        : 로그인
 *  - uw_auth_register     : 회원가입
 *  - uw_auth_lostpassword : 비번 재설정 메일
 *  - uw_auth_resetpassword: 새 비번 설정
 *
 * 모두 admin-post.php (admin_post + admin_post_nopriv) 사용.
 * 페이지 단위 접근 제어 + redirect_to 처리는 template_redirect 훅.
 */

if (!defined('ABSPATH')) exit;

class UW_Auth_Handler {

    const NONCE_LOGIN     = 'uw_auth_login_nonce';
    const NONCE_REGISTER  = 'uw_auth_register_nonce';
    const NONCE_LOSTPW    = 'uw_auth_lostpw_nonce';
    const NONCE_RESETPW   = 'uw_auth_resetpw_nonce';

    /** 페이지 슬러그 (DB 페이지와 동기화) */
    const SLUG_LOGIN     = 'login';
    const SLUG_REGISTER  = 'register';
    const SLUG_LOSTPW    = 'lostpassword';
    const SLUG_RESETPW   = 'resetpassword';
    const SLUG_NOTICE    = 'maintenance-request-notice';

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // POST 처리 (admin-post.php 엔드포인트)
        add_action('admin_post_nopriv_uw_auth_login',         array($this, 'handle_login'));
        add_action('admin_post_uw_auth_login',                array($this, 'handle_login'));
        add_action('admin_post_nopriv_uw_auth_register',      array($this, 'handle_register'));
        add_action('admin_post_uw_auth_register',             array($this, 'handle_register'));
        add_action('admin_post_nopriv_uw_auth_lostpassword',  array($this, 'handle_lostpassword'));
        add_action('admin_post_uw_auth_lostpassword',         array($this, 'handle_lostpassword'));
        add_action('admin_post_nopriv_uw_auth_resetpassword', array($this, 'handle_resetpassword'));
        add_action('admin_post_uw_auth_resetpassword',        array($this, 'handle_resetpassword'));

        // 페이지 접근 제어
        add_action('template_redirect', array($this, 'enforce_access'));

        // 인증 페이지 noindex (WP 코어 + Rank Math 양쪽)
        add_filter('wp_robots', array($this, 'add_noindex_to_auth_pages'));
        add_filter('rank_math/frontend/robots', array($this, 'rank_math_noindex'));
    }

    /**
     * Rank Math가 출력하는 robots 메타에도 noindex 강제.
     */
    public function rank_math_noindex($robots) {
        if (!is_page()) return $robots;
        global $post;
        if (!$post) return $robots;

        $noindex_slugs = array(
            self::SLUG_LOGIN, self::SLUG_REGISTER, self::SLUG_LOSTPW,
            self::SLUG_RESETPW, self::SLUG_NOTICE,
        );
        if (in_array($post->post_name, $noindex_slugs, true)) {
            $robots['index']  = 'noindex';
            $robots['follow'] = 'nofollow';
        }
        return $robots;
    }

    /* =========================================================
     * Helpers
     * ========================================================= */

    private function get_client_ip() {
        $keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    /**
     * Rate Limit (IP+action 기준). 초과 시 false 반환.
     */
    private function check_rate_limit($action, $max = 5, $window = 300) {
        $key   = 'uw_auth_rl_' . md5($action . '|' . $this->get_client_ip());
        $count = (int) get_transient($key);
        if ($count >= $max) return false;
        set_transient($key, $count + 1, $window);
        return true;
    }

    /**
     * 에러/메시지를 쿼리로 실어 다시 페이지로 보낸다.
     */
    private function redirect_with($url, $args) {
        $url = add_query_arg($args, $url);
        wp_safe_redirect($url);
        exit;
    }

    private function page_url($slug) {
        return home_url('/' . $slug . '/');
    }

    /**
     * 안전한 redirect_to 정규화 (오픈 리다이렉트 방지).
     */
    private function safe_redirect_to($maybe_url, $fallback) {
        if (empty($maybe_url)) return $fallback;
        $url   = wp_unslash($maybe_url);
        $host  = wp_parse_url($url, PHP_URL_HOST);
        $home  = wp_parse_url(home_url(), PHP_URL_HOST);
        if ($host && $host !== $home) return $fallback;
        return $url;
    }

    /* =========================================================
     * 로그인
     * ========================================================= */

    public function handle_login() {
        $login_url = $this->page_url(self::SLUG_LOGIN);

        if (!isset($_POST['uw_auth_login_nonce']) ||
            !wp_verify_nonce($_POST['uw_auth_login_nonce'], self::NONCE_LOGIN)) {
            $this->redirect_with($login_url, array('uw_err' => 'nonce'));
        }

        // honeypot
        if (!empty($_POST['uw_h'])) {
            $this->redirect_with($login_url, array('uw_err' => 'bot'));
        }

        if (!$this->check_rate_limit('login')) {
            $this->redirect_with($login_url, array('uw_err' => 'rate'));
        }

        $email    = isset($_POST['uw_email']) ? sanitize_email(wp_unslash($_POST['uw_email'])) : '';
        $password = isset($_POST['uw_password']) ? (string) wp_unslash($_POST['uw_password']) : '';
        $remember = !empty($_POST['uw_remember']);
        $redirect_to = $this->safe_redirect_to(
            isset($_POST['redirect_to']) ? wp_unslash($_POST['redirect_to']) : '',
            $this->page_url(self::SLUG_NOTICE)
        );

        if (!$email || !$password) {
            $this->redirect_with($login_url, array('uw_err' => 'empty'));
        }

        $user = wp_authenticate($email, $password);
        if (is_wp_error($user)) {
            $this->redirect_with($login_url, array('uw_err' => 'invalid', 'uw_email' => rawurlencode($email)));
        }

        // 승인 대기 상태 차단
        if (UW_Auth_Roles::is_pending($user)) {
            $this->redirect_with($login_url, array('uw_err' => 'pending', 'uw_email' => rawurlencode($email)));
        }

        wp_set_auth_cookie($user->ID, $remember, is_ssl());
        wp_set_current_user($user->ID);

        wp_safe_redirect($redirect_to);
        exit;
    }

    /* =========================================================
     * 회원가입
     * ========================================================= */

    public function handle_register() {
        $reg_url = $this->page_url(self::SLUG_REGISTER);

        if (!isset($_POST['uw_auth_register_nonce']) ||
            !wp_verify_nonce($_POST['uw_auth_register_nonce'], self::NONCE_REGISTER)) {
            $this->redirect_with($reg_url, array('uw_err' => 'nonce'));
        }
        if (!empty($_POST['uw_h'])) {
            $this->redirect_with($reg_url, array('uw_err' => 'bot'));
        }
        if (!$this->check_rate_limit('register', 3, 600)) {
            $this->redirect_with($reg_url, array('uw_err' => 'rate'));
        }

        $email    = isset($_POST['uw_email'])    ? sanitize_email(wp_unslash($_POST['uw_email'])) : '';
        $password = isset($_POST['uw_password']) ? (string) wp_unslash($_POST['uw_password']) : '';
        $company  = isset($_POST['uw_company'])  ? sanitize_text_field(wp_unslash($_POST['uw_company'])) : '';
        $name     = isset($_POST['uw_name'])     ? sanitize_text_field(wp_unslash($_POST['uw_name']))    : '';
        $phone    = isset($_POST['uw_phone'])    ? sanitize_text_field(wp_unslash($_POST['uw_phone']))   : '';
        $agree    = !empty($_POST['uw_agree']);

        $errs = array();
        if (!is_email($email))                                   $errs[] = 'email';
        // 비밀번호 강도: 8자 이상 + 영문 + 숫자 혼합
        if (strlen($password) < 8
            || !preg_match('/[A-Za-z]/', $password)
            || !preg_match('/\d/', $password))                   $errs[] = 'password';
        if (!$company)                                           $errs[] = 'company';
        if (!$name)                                              $errs[] = 'name';
        if (!$phone)                                             $errs[] = 'phone';
        if (!$agree)                                             $errs[] = 'agree';

        if (!empty($errs)) {
            $this->redirect_with($reg_url, array(
                'uw_err'     => implode(',', $errs),
                'uw_email'   => rawurlencode($email),
                'uw_company' => rawurlencode($company),
                'uw_name'    => rawurlencode($name),
                'uw_phone'   => rawurlencode($phone),
            ));
        }

        // 사용자 열거 방지: 이메일 중복이면 응답은 success와 동일하게,
        // 본인 계정인 경우 안내 메일 발송 (OWASP 권장)
        if (email_exists($email)) {
            $blog = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
            $from = apply_filters('uw_auth_from_email', 'contact@unwebs.co.kr');
            wp_mail(
                $email,
                '[' . $blog . '] 이미 가입된 이메일로 신규 가입이 시도되었습니다',
                "안녕하세요.\n\n방금 본 이메일로 유지보수 회원 신규 가입이 시도되었습니다.\n"
                . "이미 가입된 계정이며, 본인이 시도한 경우 비밀번호 찾기를 이용해주세요.\n\n"
                . "비밀번호 찾기: " . home_url('/lostpassword/') . "\n\n"
                . "본인 시도가 아니라면 이 메일을 무시하셔도 됩니다.\n\n" . $blog,
                array(
                    'Content-Type: text/plain; charset=UTF-8',
                    'From: ' . $blog . ' <' . $from . '>',
                )
            );
            // 신규 가입과 동일한 응답
            $this->redirect_with($reg_url, array('uw_ok' => 'registered'));
        }

        $user_id = wp_create_user($email, $password, $email);
        if (is_wp_error($user_id)) {
            $this->redirect_with($reg_url, array('uw_err' => 'create'));
        }

        $user = new WP_User($user_id);
        $user->set_role(UW_Auth_Roles::ROLE_PENDING);

        // 프로필 + display_name
        wp_update_user(array(
            'ID'           => $user_id,
            'first_name'   => $name,
            'display_name' => $name,
        ));

        update_user_meta($user_id, 'uw_company', $company);
        update_user_meta($user_id, 'uw_name',    $name);
        update_user_meta($user_id, 'uw_phone',   $phone);
        update_user_meta($user_id, 'uw_status',  'pending');
        update_user_meta($user_id, 'uw_register_ip', $this->get_client_ip());

        // 메일 발송 — 관리자/고객 양쪽
        do_action('uw_auth_user_registered', $user_id, array(
            'email'   => $email,
            'name'    => $name,
            'company' => $company,
            'phone'   => $phone,
        ));

        $this->redirect_with($reg_url, array('uw_ok' => 'registered'));
    }

    /* =========================================================
     * 비밀번호 찾기
     * ========================================================= */

    public function handle_lostpassword() {
        $url = $this->page_url(self::SLUG_LOSTPW);

        if (!isset($_POST['uw_auth_lostpw_nonce']) ||
            !wp_verify_nonce($_POST['uw_auth_lostpw_nonce'], self::NONCE_LOSTPW)) {
            $this->redirect_with($url, array('uw_err' => 'nonce'));
        }
        if (!empty($_POST['uw_h'])) {
            $this->redirect_with($url, array('uw_err' => 'bot'));
        }
        if (!$this->check_rate_limit('lostpw', 3, 600)) {
            $this->redirect_with($url, array('uw_err' => 'rate'));
        }

        $email = isset($_POST['uw_email']) ? sanitize_email(wp_unslash($_POST['uw_email'])) : '';

        // 사용자 존재 여부 노출 방지 — 결과는 항상 동일.
        if ($email && email_exists($email)) {
            $_POST['user_login'] = $email;
            // WP 코어 retrieve_password() 활용 (메일 발송 포함)
            retrieve_password();
        }

        $this->redirect_with($url, array('uw_ok' => 'sent'));
    }

    /* =========================================================
     * 비밀번호 재설정 (key/login)
     * ========================================================= */

    public function handle_resetpassword() {
        $url = $this->page_url(self::SLUG_RESETPW);

        if (!isset($_POST['uw_auth_resetpw_nonce']) ||
            !wp_verify_nonce($_POST['uw_auth_resetpw_nonce'], self::NONCE_RESETPW)) {
            $this->redirect_with($url, array('uw_err' => 'nonce'));
        }
        if (!$this->check_rate_limit('resetpw', 5, 600)) {
            $this->redirect_with($url, array('uw_err' => 'rate'));
        }

        $key      = isset($_POST['uw_key'])   ? sanitize_text_field(wp_unslash($_POST['uw_key']))   : '';
        $login    = isset($_POST['uw_login']) ? sanitize_text_field(wp_unslash($_POST['uw_login'])) : '';
        $password = isset($_POST['uw_password']) ? (string) wp_unslash($_POST['uw_password']) : '';
        $password2= isset($_POST['uw_password2']) ? (string) wp_unslash($_POST['uw_password2']) : '';

        $user = check_password_reset_key($key, $login);
        if (is_wp_error($user)) {
            $this->redirect_with($this->page_url(self::SLUG_LOSTPW), array('uw_err' => 'expired'));
        }
        if (strlen($password) < 8
            || !preg_match('/[A-Za-z]/', $password)
            || !preg_match('/\d/', $password)) {
            $this->redirect_with($url, array('uw_err' => 'password', 'key' => $key, 'login' => $login));
        }
        if ($password !== $password2) {
            $this->redirect_with($url, array('uw_err' => 'mismatch', 'key' => $key, 'login' => $login));
        }

        reset_password($user, $password);

        $this->redirect_with($this->page_url(self::SLUG_LOGIN), array('uw_ok' => 'reset'));
    }

    /* =========================================================
     * 접근 제어 (template_redirect)
     * ========================================================= */

    public function enforce_access() {
        if (!is_page()) return;

        global $post;
        if (!$post) return;
        $slug = $post->post_name;

        // 관리자/편집자는 모든 접근 통과 (디자인 검수/콘텐츠 관리용)
        if (current_user_can('manage_options') || current_user_can('edit_pages')) {
            return;
        }

        // 인증 페이지 (login/register/lostpw): 이미 로그인한 유지보수 고객만 홈으로
        $auth_slugs = array(self::SLUG_LOGIN, self::SLUG_REGISTER, self::SLUG_LOSTPW);
        if (in_array($slug, $auth_slugs, true)
            && is_user_logged_in()
            && in_array(UW_Auth_Roles::ROLE_CLIENT, (array) wp_get_current_user()->roles, true)) {
            wp_safe_redirect(home_url('/'));
            exit;
        }

        // 유의사항+신청 페이지: 미로그인/대기 차단
        if ($slug === self::SLUG_NOTICE) {
            if (!is_user_logged_in()) {
                $login = add_query_arg(
                    array('redirect_to' => rawurlencode($_SERVER['REQUEST_URI'])),
                    $this->page_url(self::SLUG_LOGIN)
                );
                wp_safe_redirect($login);
                exit;
            }
            if (!UW_Auth_Roles::is_approved_client()) {
                $login = add_query_arg(array('uw_err' => 'pending'), $this->page_url(self::SLUG_LOGIN));
                wp_safe_redirect($login);
                exit;
            }
        }
    }

    /* =========================================================
     * 인증 페이지 noindex
     * ========================================================= */

    public function add_noindex_to_auth_pages($robots) {
        if (!is_page()) return $robots;
        global $post;
        if (!$post) return $robots;

        $noindex = array(
            self::SLUG_LOGIN,
            self::SLUG_REGISTER,
            self::SLUG_LOSTPW,
            self::SLUG_RESETPW,
            self::SLUG_NOTICE,
        );
        if (in_array($post->post_name, $noindex, true)) {
            $robots['noindex']  = true;
            $robots['nofollow'] = true;
        }
        return $robots;
    }

    /* =========================================================
     * 에러 메시지 매핑 (페이지 템플릿에서 사용)
     * ========================================================= */

    public static function get_error_message($code) {
        $map = array(
            'nonce'     => '잘못된 요청입니다. 다시 시도해주세요.',
            'bot'       => '봇으로 감지되었습니다.',
            'rate'      => '요청이 너무 잦습니다. 잠시 후 다시 시도해주세요.',
            'empty'     => '아이디(이메일)와 비밀번호를 모두 입력해주세요.',
            'invalid'   => '아이디 또는 비밀번호가 일치하지 않습니다.',
            'pending'   => '관리자 승인 대기 중입니다. 승인 완료 후 이용 가능합니다.',
            'email'     => '올바른 이메일을 입력해주세요.',
            'password'  => '비밀번호는 영문과 숫자를 포함한 8자 이상이어야 합니다.',
            'company'   => '회사명을 입력해주세요.',
            'name'      => '담당자명을 입력해주세요.',
            'phone'     => '연락처를 입력해주세요.',
            'agree'     => '안내사항에 동의해주세요.',
            'duplicate' => '이미 가입된 이메일입니다.',
            'create'    => '가입 처리 중 오류가 발생했습니다.',
            'expired'   => '재설정 링크가 만료되었습니다. 다시 요청해주세요.',
            'mismatch'  => '두 비밀번호가 일치하지 않습니다.',
        );
        return isset($map[$code]) ? $map[$code] : '오류가 발생했습니다.';
    }
}

UW_Auth_Handler::get_instance();
