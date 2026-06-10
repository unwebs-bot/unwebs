<?php
/**
 * UW Quote Handler — 정기 유지보수 견적 신청 (모달 폼, 비로그인 OK)
 *
 *  - AJAX 엔드포인트: wp_ajax(_nopriv)_uw_quote_submit
 *  - 입력 검증 + nonce + rate limit + honeypot
 *  - 저장: uw_inquiry_entry CPT 활용 (post_title `[회사명] 담당자 - 플랜 견적`)
 *  - 메일: 관리자 알림 + 신청자 자동회신
 */

if (!defined('ABSPATH')) exit;

class UW_Quote_Handler {

    const NONCE_KEY  = 'uw_quote_nonce';
    const ACTION     = 'uw_quote_submit';

    /** 허용 플랜 화이트리스트 — 페이지의 $mp_plans key와 매칭 */
    public static $plan_labels = array(
        'basic'   => '기본형',
        'premium' => '고급형',
        'top'     => '최고급형',
    );

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_ajax_'        . self::ACTION, array($this, 'handle_submit'));
        add_action('wp_ajax_nopriv_' . self::ACTION, array($this, 'handle_submit'));
    }

    /* =========================================================
     * AJAX 진입점
     * ========================================================= */

    public function handle_submit() {
        // 1) nonce
        if (!check_ajax_referer(self::NONCE_KEY, 'nonce', false)) {
            wp_send_json_error('보안 검증에 실패했습니다. 페이지를 새로고침 후 다시 시도해주세요.');
        }

        // 2) honeypot
        if (!empty($_POST['uw_h'])) {
            wp_send_json_error('잘못된 요청입니다.');
        }

        // 3) rate limit (IP 기준 — 10분 5회)
        $ip = $this->get_client_ip();
        $key = 'uw_quote_rl_' . md5($ip);
        $count = (int) get_transient($key);
        if ($count >= 5) {
            wp_send_json_error('요청이 너무 잦습니다. 잠시 후 다시 시도해주세요.');
        }
        set_transient($key, $count + 1, 10 * MINUTE_IN_SECONDS);

        // 4) 입력 sanitize
        $plan    = isset($_POST['plan']) ? sanitize_key(wp_unslash($_POST['plan'])) : '';
        $company = isset($_POST['company']) ? sanitize_text_field(wp_unslash($_POST['company'])) : '';
        $name    = isset($_POST['name'])    ? sanitize_text_field(wp_unslash($_POST['name']))    : '';
        $phone   = isset($_POST['phone'])   ? sanitize_text_field(wp_unslash($_POST['phone']))   : '';
        $email   = isset($_POST['email'])   ? sanitize_email(wp_unslash($_POST['email']))       : '';
        $site_url= isset($_POST['site_url'])? esc_url_raw(wp_unslash($_POST['site_url']))       : '';
        $message = isset($_POST['message']) ? wp_kses_post(wp_unslash($_POST['message']))       : '';
        $agree   = !empty($_POST['agree']);

        // 5) 검증
        if (!array_key_exists($plan, self::$plan_labels)) wp_send_json_error('플랜이 올바르지 않습니다.');
        if (!$company)         wp_send_json_error('회사/기관명을 입력해주세요.');
        if (!$name)            wp_send_json_error('담당자명을 입력해주세요.');
        if (!$phone)           wp_send_json_error('연락처를 입력해주세요.');
        if (!is_email($email)) wp_send_json_error('올바른 이메일을 입력해주세요.');
        if (!$agree)           wp_send_json_error('개인정보 처리방침에 동의해주세요.');

        $plan_label = self::$plan_labels[$plan];
        $now_local  = current_time('Y-m-d H:i');

        // 6) entry 저장 (uw_inquiry_entry CPT 활용)
        $entry_id = wp_insert_post(array(
            'post_type'   => 'uw_inquiry_entry',
            'post_status' => 'private',
            'post_title'  => '[' . $company . '] ' . $name . ' — ' . $plan_label . ' 견적 (' . $now_local . ')',
            'post_author' => is_user_logged_in() ? get_current_user_id() : 0,
        ));
        if (!$entry_id || is_wp_error($entry_id)) {
            wp_send_json_error('저장에 실패했습니다. 잠시 후 다시 시도해주세요.');
        }

        $entry_data = array(
            'field_inquiry_type' => '정기 유지보수 견적 — ' . $plan_label,
            'field_company'      => $company,
            'field_name'         => $name,
            'field_phone'        => $phone,
            'field_email'        => $email,
            'field_subject'      => '[' . $plan_label . '] 정기 유지보수 견적 요청',
            'field_message'      => ($site_url ? "운영 사이트: {$site_url}\n\n" : '') . $message,
            'uw_plan'            => $plan,
            'uw_plan_label'      => $plan_label,
            'uw_site_url'        => $site_url,
        );

        update_post_meta($entry_id, '_uw_inquiry_data',       $entry_data);
        update_post_meta($entry_id, '_uw_inquiry_form_id',    0);              // 폼 CPT 없이 직접 처리
        update_post_meta($entry_id, '_uw_inquiry_source',     'maintenance-quote');
        update_post_meta($entry_id, '_uw_inquiry_plan',       $plan);
        update_post_meta($entry_id, '_uw_inquiry_ip',         $ip);
        update_post_meta($entry_id, '_uw_inquiry_user_agent', isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '');

        // 7) 메일 발송
        $this->mail_admin($entry_id, $entry_data);
        $this->mail_customer($entry_data);

        // 어드민 SMS 알림 (Solapi 상수 설정 시에만 발송)
        $this->sms_admin($entry_data);

        wp_send_json_success(array(
            'message' => "{$plan_label} 견적 요청이 접수되었습니다.\n영업일 기준 24시간 내 견적서를 메일로 발송드립니다.",
        ));
    }

    /* =========================================================
     * Helpers
     * ========================================================= */

    private function get_client_ip() {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR') as $k) {
            if (!empty($_SERVER[$k])) {
                $ip = $_SERVER[$k];
                if (strpos($ip, ',') !== false) $ip = explode(',', $ip)[0];
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }

    private function mail_headers() {
        $blog = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $from = apply_filters('uw_quote_from_email', 'contact@unwebs.co.kr');
        return array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $blog . ' <' . $from . '>',
        );
    }

    private function mail_admin($entry_id, $data) {
        $blog       = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $to         = apply_filters('uw_quote_notify_email', get_option('admin_email'));
        $subject    = '[' . $blog . '] 정기 유지보수 견적 요청 — ' . $data['uw_plan_label'];
        $admin_url  = admin_url('post.php?post=' . $entry_id . '&action=edit');

        $body  = "신규 정기 유지보수 견적 요청이 접수되었습니다.\n\n";
        $body .= "선택 플랜:  {$data['uw_plan_label']}\n";
        $body .= "회사/기관:  {$data['field_company']}\n";
        $body .= "담당자명:   {$data['field_name']}\n";
        $body .= "연락처:     {$data['field_phone']}\n";
        $body .= "이메일:     {$data['field_email']}\n";
        if (!empty($data['uw_site_url'])) {
            $body .= "운영 사이트: {$data['uw_site_url']}\n";
        }
        if (!empty($data['field_message'])) {
            $body .= "\n[요청 사항]\n" . preg_replace('/^운영 사이트:.*\n\n/u', '', $data['field_message']) . "\n";
        }
        $body .= "\n----\n";
        $body .= "관리자 페이지: " . $admin_url . "\n";

        $headers   = $this->mail_headers();
        $headers[] = 'Reply-To: ' . $data['field_email'];
        wp_mail($to, $subject, $body, $headers);
    }

    private function mail_customer($data) {
        $blog    = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $subject = '[' . $blog . '] 정기 유지보수 견적 요청이 접수되었습니다';

        $body  = "{$data['field_name']}님 안녕하세요.\n\n";
        $body .= "정기 유지보수 견적 요청({$data['uw_plan_label']})이 정상 접수되었습니다.\n";
        $body .= "영업일 기준 24시간 내 상세 견적서를 본 메일 주소로 발송드리겠습니다.\n\n";
        $body .= "[접수 내용]\n";
        $body .= "선택 플랜: {$data['uw_plan_label']}\n";
        $body .= "회사/기관: {$data['field_company']}\n";
        $body .= "담당자명:  {$data['field_name']}\n";
        $body .= "연락처:    {$data['field_phone']}\n";
        if (!empty($data['uw_site_url'])) {
            $body .= "운영 사이트: {$data['uw_site_url']}\n";
        }
        $body .= "\n문의: contact@unwebs.co.kr / 010-2130-3731\n\n감사합니다.\n{$blog}";

        wp_mail($data['field_email'], $subject, $body, $this->mail_headers());
    }

    /**
     * 어드민 SMS 알림 (Solapi) — 견적 요청 접수 시. inc/cm-sms.php
     */
    private function sms_admin($data)
    {
        if (!function_exists('uw_send_admin_sms')) return;

        $plan  = isset($data['uw_plan_label']) ? trim((string) $data['uw_plan_label']) : '';
        $comp  = isset($data['field_company']) ? trim((string) $data['field_company']) : '';
        $name  = isset($data['field_name'])    ? trim((string) $data['field_name'])    : '';
        $phone = isset($data['field_phone'])   ? trim((string) $data['field_phone'])   : '';

        $lines = array('[언웹스] 유지보수 견적 요청');
        if ($plan !== '') $lines[] = $plan;
        $who = trim($comp . ' ' . $name);
        if ($phone !== '') $who = trim($who . ' / ' . $phone);
        if ($who !== '') $lines[] = $who;

        uw_send_admin_sms(implode("\n", $lines));
    }
}

UW_Quote_Handler::get_instance();
