<?php
/**
 * UW Auth Admin — wp-admin 사용자 목록 승인 액션 + 메일 발송
 *
 *  - 사용자 목록의 행 액션에 "유지보수 승인" 추가
 *  - 승인 시 역할 maintenance_pending → maintenance_client
 *  - 가입/승인 시 메일 발송
 */

if (!defined('ABSPATH')) exit;

class UW_Auth_Admin {

    const APPROVE_QUERY = 'uw_auth_approve';
    const APPROVE_NONCE = 'uw_auth_approve_nonce';

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // 사용자 메뉴 — 승인 대기 인원 뱃지 (모든 메뉴 등록 후 실행되도록 늦은 priority)
        add_action('admin_menu', array($this, 'add_pending_badge'), 999);
        // 사용자 목록 행 액션
        add_filter('user_row_actions', array($this, 'add_approve_action'), 10, 2);
        // 승인 처리
        add_action('admin_init', array($this, 'process_approve'));
        // 승인 후 admin notice
        add_action('admin_notices', array($this, 'admin_notice'));
        // 사용자 목록 컬럼: 회사명 추가
        add_filter('manage_users_columns', array($this, 'add_company_column'));
        add_filter('manage_users_custom_column', array($this, 'render_company_column'), 10, 3);

        // 메일 발송 훅
        add_action('uw_auth_user_registered', array($this, 'mail_on_register'), 10, 2);

        // uw_inquiry — 유지보수 신청 폼(slug=maintenance-request) 커스터마이즈
        add_filter('uw_inquiry_autoreply_subject', array($this, 'maintenance_autoreply_subject'), 10, 3);
        // added_post_meta: handler가 _uw_inquiry_form_id 먼저, _uw_inquiry_data를 그 다음 저장 → data 저장 시점에 form_id 조회 가능
        add_action('added_post_meta',              array($this, 'maintenance_entry_title'),       20, 4);
    }

    /* =========================================================
     * 유지보수 신청 폼 — 자동회신 제목 컨텍스트 맞춤
     * ========================================================= */

    public function maintenance_autoreply_subject($subject, $form_id, $entry_data) {
        $form = get_post($form_id);
        if ($form && $form->post_name === UW_Auth_Handler::SLUG_REGISTER) return $subject; // 안전장치
        if ($form && $form->post_name === 'maintenance-request') {
            $blog = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
            return '[' . $blog . '] 유지보수 신청이 접수되었습니다';
        }
        return $subject;
    }

    /**
     * uw_inquiry_entry의 _uw_inquiry_data 메타 저장 시 — form_id가 maintenance-request면 회사명 prepend.
     * 예: "[회사명] 담당자 - 2026-05-15 13:31"
     *
     * added_post_meta 시그니처: ($meta_id, $object_id, $meta_key, $meta_value)
     */
    public function maintenance_entry_title($meta_id, $object_id, $meta_key, $meta_value) {
        if ($meta_key !== '_uw_inquiry_data') return;

        $post = get_post($object_id);
        if (!$post || $post->post_type !== 'uw_inquiry_entry') return;
        // 이미 prepend된 entry 재진입 방지
        if (strpos((string) $post->post_title, '[') === 0) return;

        $form_id = get_post_meta($object_id, '_uw_inquiry_form_id', true);
        if (!$form_id) return;
        $form = get_post($form_id);
        if (!$form || $form->post_name !== 'maintenance-request') return;

        $data = is_array($meta_value) ? $meta_value : get_post_meta($object_id, '_uw_inquiry_data', true);
        if (!is_array($data)) return;

        $company = !empty($data['field_company']) ? $data['field_company'] : '';
        if (!$company) return;

        wp_update_post(array('ID' => $object_id, 'post_title' => '[' . $company . '] ' . $post->post_title));
    }

    /* =========================================================
     * 사용자 메뉴 — 승인 대기 뱃지
     * ========================================================= */

    /**
     * maintenance_pending 사용자가 있으면 "사용자" 메뉴에 댓글 대기와 동일한 뱃지 표시.
     * 승인되면 role이 maintenance_client로 바뀌므로 카운트가 0이 되어 뱃지 자동 제거.
     */
    public function add_pending_badge() {
        if (!current_user_can('promote_users')) return;

        $counts  = count_users();
        $pending = isset($counts['avail_roles'][UW_Auth_Roles::ROLE_PENDING])
            ? (int) $counts['avail_roles'][UW_Auth_Roles::ROLE_PENDING]
            : 0;
        if (!$pending) return;

        $bubble = ' <span class="awaiting-mod count-' . $pending . '"><span class="pending-count">'
                . number_format_i18n($pending) . '</span></span>';

        global $menu, $submenu;

        // 메인 메뉴 "사용자"
        foreach ((array) $menu as $i => $item) {
            if (isset($item[2]) && $item[2] === 'users.php') {
                $menu[$i][0] .= $bubble;
                break;
            }
        }

        // 서브메뉴 "모든 사용자" — 클릭 시 대기 사용자만 필터된 목록으로 이동하도록 별도 항목 추가
        if (isset($submenu['users.php'])) {
            foreach ($submenu['users.php'] as $i => $item) {
                if (isset($item[2]) && $item[2] === 'users.php') {
                    $submenu['users.php'][$i][0] .= $bubble;
                    break;
                }
            }
        }
        add_submenu_page(
            'users.php',
            '유지보수 승인 대기',
            '승인 대기' . $bubble,
            'promote_users',
            'users.php?role=' . UW_Auth_Roles::ROLE_PENDING
        );
    }

    /* =========================================================
     * 사용자 목록 — 승인 액션
     * ========================================================= */

    public function add_approve_action($actions, $user) {
        if (!current_user_can('promote_users')) return $actions;
        if (!UW_Auth_Roles::is_pending($user))  return $actions;

        $url = wp_nonce_url(
            add_query_arg(
                array(self::APPROVE_QUERY => $user->ID),
                admin_url('users.php')
            ),
            self::APPROVE_NONCE
        );
        $actions['uw_auth_approve'] = '<a href="' . esc_url($url) . '" style="color:#3182f6;font-weight:600">유지보수 승인</a>';
        return $actions;
    }

    public function process_approve() {
        if (empty($_GET[self::APPROVE_QUERY])) return;
        if (!current_user_can('promote_users')) wp_die('권한이 없습니다.');
        check_admin_referer(self::APPROVE_NONCE);

        $user_id = (int) $_GET[self::APPROVE_QUERY];
        $user    = get_user_by('id', $user_id);
        if (!$user) wp_die('사용자를 찾을 수 없습니다.');

        if (!UW_Auth_Roles::is_pending($user)) {
            wp_safe_redirect(add_query_arg('uw_auth_msg', 'not-pending', admin_url('users.php')));
            exit;
        }

        $user->set_role(UW_Auth_Roles::ROLE_CLIENT);
        update_user_meta($user_id, 'uw_status', 'approved');
        update_user_meta($user_id, 'uw_approved_at', current_time('mysql'));

        $this->mail_on_approve($user);

        wp_safe_redirect(add_query_arg('uw_auth_msg', 'approved', admin_url('users.php')));
        exit;
    }

    public function admin_notice() {
        if (empty($_GET['uw_auth_msg'])) return;
        $msg = sanitize_text_field($_GET['uw_auth_msg']);
        $text = '';
        $cls  = 'updated';
        switch ($msg) {
            case 'approved':    $text = '유지보수 고객으로 승인했습니다. 안내 메일이 발송되었습니다.'; break;
            case 'not-pending': $text = '이미 승인된 사용자입니다.'; $cls = 'notice-warning'; break;
            default: return;
        }
        echo '<div class="notice ' . esc_attr($cls) . ' is-dismissible"><p>' . esc_html($text) . '</p></div>';
    }

    /* =========================================================
     * 사용자 목록 — 회사명 컬럼
     * ========================================================= */

    public function add_company_column($columns) {
        $new = array();
        foreach ($columns as $k => $v) {
            $new[$k] = $v;
            if ($k === 'email') {
                $new['uw_company'] = '회사명';
                $new['uw_status']  = '유지보수 상태';
            }
        }
        return $new;
    }

    public function render_company_column($value, $column_name, $user_id) {
        if ($column_name === 'uw_company') {
            return esc_html(get_user_meta($user_id, 'uw_company', true));
        }
        if ($column_name === 'uw_status') {
            $user = get_user_by('id', $user_id);
            if (UW_Auth_Roles::is_pending($user))         return '<span style="color:#f63131">대기</span>';
            if (UW_Auth_Roles::is_approved_client($user)) return '<span style="color:#3182f6">승인</span>';
            return '—';
        }
        return $value;
    }

    /* =========================================================
     * 메일 발송
     * ========================================================= */

    public function mail_on_register($user_id, $data) {
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $admin    = get_option('admin_email');
        $approve_url = admin_url('users.php?role=' . UW_Auth_Roles::ROLE_PENDING);

        // 관리자 알림
        $subject = '[' . $blogname . '] 신규 유지보수 가입 신청';
        $body = "신규 유지보수 회원 가입이 접수되었습니다.\n\n"
              . "이름: {$data['name']}\n"
              . "회사명: {$data['company']}\n"
              . "이메일: {$data['email']}\n"
              . "연락처: {$data['phone']}\n\n"
              . "승인 페이지: {$approve_url}\n";
        wp_mail($admin, $subject, $body, $this->mail_headers());

        // 고객 안내
        $cust_subject = '[' . $blogname . '] 유지보수 회원 가입 신청이 접수되었습니다';
        $cust_body = "{$data['name']}님 안녕하세요.\n\n"
                   . "유지보수 회원 가입 신청이 정상 접수되었습니다.\n"
                   . "관리자 승인 후 로그인이 가능하며, 승인 완료 시 별도로 안내 드리겠습니다.\n\n"
                   . "감사합니다.\n" . $blogname;
        wp_mail($data['email'], $cust_subject, $cust_body, $this->mail_headers());
    }

    private function mail_on_approve($user) {
        $blogname  = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $name      = get_user_meta($user->ID, 'uw_name', true) ?: $user->display_name;
        $login_url = home_url('/login/');

        $subject = '[' . $blogname . '] 유지보수 회원 가입이 승인되었습니다';
        $body = "{$name}님 안녕하세요.\n\n"
              . "유지보수 회원 가입이 승인되었습니다.\n"
              . "아래 링크로 로그인하여 유지보수 신청을 진행하실 수 있습니다.\n\n"
              . "로그인: {$login_url}\n\n"
              . "감사합니다.\n" . $blogname;
        wp_mail($user->user_email, $subject, $body, $this->mail_headers());
    }

    private function mail_headers() {
        $from_email = apply_filters('uw_auth_from_email', 'contact@unwebs.co.kr');
        $from_name  = apply_filters('uw_auth_from_name',  get_bloginfo('name'));
        return array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>',
        );
    }
}

UW_Auth_Admin::get_instance();
