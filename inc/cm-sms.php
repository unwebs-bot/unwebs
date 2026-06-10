<?php
/**
 * 관리자 SMS 알림 (Solapi)
 *
 * 문의·견적 폼 접수 시 운영자 휴대폰으로 문자 발송.
 * 메일 발송과 독립적으로 동작하며, 인증정보는 wp-config.php 상수로 관리한다.
 *
 *   define('UW_SOLAPI_API_KEY',     'NCSxxxxxxxxxxxx');   // Solapi API Key
 *   define('UW_SOLAPI_API_SECRET',  'xxxxxxxxxxxxxxxx');  // Solapi API Secret
 *   define('UW_SOLAPI_SENDER',      '01021303731');       // 사전등록한 발신번호
 *   define('UW_SOLAPI_ADMIN_PHONE', '01021303731');       // 알림 받을 수신번호
 *
 * 상수가 하나라도 없으면 조용히 skip → 로컬/스테이징 환경 안전.
 *
 * @link https://developers.solapi.dev/references/authentication/api-key
 */

if (!defined('ABSPATH')) exit;

/**
 * 휴대폰 번호 정규화: 하이픈·공백·국가코드 제거 → 01012345678
 */
function uw_sms_normalize_phone($raw)
{
    $digits = preg_replace('/[^0-9]/', '', (string) $raw);
    // +82 / 82 국가코드를 0 으로 치환 (예: 821021303731 → 01021303731)
    if (strpos($digits, '82') === 0 && strlen($digits) >= 11) {
        $digits = '0' . substr($digits, 2);
    }
    return $digits;
}

/**
 * 관리자에게 SMS 발송 (Solapi messages/v4/send 단건)
 *
 * @param string $text 메시지 본문. 90byte(한글 45자) 초과 시 Solapi가 LMS로 자동 전환.
 * @param array  $args ['to' => 수신번호 override]
 * @return bool 발송 요청 성공 여부
 */
function uw_send_admin_sms($text, $args = array())
{
    // 인증정보 상수 확인 — 미설정 시 skip
    if (!defined('UW_SOLAPI_API_KEY') || !defined('UW_SOLAPI_API_SECRET')
        || !defined('UW_SOLAPI_SENDER') || !defined('UW_SOLAPI_ADMIN_PHONE')) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[UW SMS] Solapi 상수 미설정 — SMS 발송 skip');
        }
        return false;
    }

    $api_key    = UW_SOLAPI_API_KEY;
    $api_secret = UW_SOLAPI_API_SECRET;
    $from       = uw_sms_normalize_phone(UW_SOLAPI_SENDER);
    $to         = uw_sms_normalize_phone(!empty($args['to']) ? $args['to'] : UW_SOLAPI_ADMIN_PHONE);

    $text = trim(wp_strip_all_tags((string) $text));
    if ($text === '' || empty($to) || empty($from)) {
        return false;
    }

    // HMAC-SHA256 인증 헤더 생성
    // signature = HMAC-SHA256(date + salt, apiSecret), hex
    $date      = gmdate('Y-m-d\TH:i:s\Z');          // ISO 8601 (UTC)
    $salt      = bin2hex(random_bytes(16));
    $signature = hash_hmac('sha256', $date . $salt, $api_secret);
    $auth      = sprintf(
        'HMAC-SHA256 apiKey=%s, date=%s, salt=%s, signature=%s',
        $api_key, $date, $salt, $signature
    );

    $payload = array(
        'message' => array(
            'to'   => $to,
            'from' => $from,
            'text' => $text,
        ),
    );

    $response = wp_remote_post('https://api.solapi.com/messages/v4/send', array(
        'timeout' => 10,
        'headers' => array(
            'Authorization' => $auth,
            'Content-Type'  => 'application/json',
        ),
        'body' => wp_json_encode($payload),
    ));

    if (is_wp_error($response)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[UW SMS] 발송 실패(WP_Error): ' . $response->get_error_message());
        }
        return false;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $resp = wp_remote_retrieve_body($response);
    $json = json_decode($resp, true);

    // Solapi 성공 판정: HTTP 2xx + statusCode "2000"(있을 때만 검사)
    $status = is_array($json) && isset($json['statusCode']) ? $json['statusCode'] : '';
    $ok = ($code >= 200 && $code < 300) && ($status === '' || $status === '2000');

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[UW SMS] ' . ($ok ? 'OK' : 'FAIL') . ' (HTTP ' . $code . ') ' . $resp);
    }

    return $ok;
}
