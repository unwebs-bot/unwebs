<?php
/**
 * UW Inquiry Handler
 * 
 * 프론트엔드 폼 처리 및 메일 발송
 * 
 * @package starter-theme
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class UW_Inquiry_Handler
{
    /**
     * Instance
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        // 숏코드 등록
        add_shortcode('uw_inquiry_form', array($this, 'render_form_shortcode'));

        // 프론트엔드 에셋
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));

        // AJAX 폼 제출 핸들러 (로그인/비로그인 모두)
        add_action('wp_ajax_uw_inquiry_submit', array($this, 'ajax_submit_form'));
        add_action('wp_ajax_nopriv_uw_inquiry_submit', array($this, 'ajax_submit_form'));
    }

    /**
     * 프론트엔드 에셋 등록
     */
    public function enqueue_frontend_assets()
    {
        // CSS는 style.css에서 @import로 로드됨 (cpt/inquiry/inquiry.css)
        wp_enqueue_script('uw-inquiry', get_theme_file_uri('/assets/js/CPT/inquiry/uw-inquiry.js'), array('jquery'), '1.0.1', true);

        wp_localize_script('uw-inquiry', 'uwInquiry', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('uw_inquiry_nonce'),
        ));
    }

    /**
     * 숏코드: 입력폼 렌더링
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render_form_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts, 'uw_inquiry_form');

        $form_id = absint($atts['id']);

        if (!$form_id) {
            return '<div class="uw-inquiry uw-inquiry--error"><p class="uw-inquiry-error-message">폼 ID가 지정되지 않았습니다.</p></div>';
        }

        $form = get_post($form_id);
        if (!$form || $form->post_type !== 'uw_inquiry_form') {
            return '<div class="uw-inquiry uw-inquiry--error"><p class="uw-inquiry-error-message">폼을 찾을 수 없습니다.</p></div>';
        }

        $fields = get_post_meta($form_id, '_uw_inquiry_fields', true);
        $fields = is_array($fields) && !empty($fields) ? $fields : UW_Inquiry_CPT::get_default_fields();

        $privacy_text = get_post_meta($form_id, '_uw_inquiry_privacy_text', true);
        $privacy_required = get_post_meta($form_id, '_uw_inquiry_privacy_required', true);
        $captcha_enabled = get_post_meta($form_id, '_uw_inquiry_captcha_enabled', true);
        $success_type = get_post_meta($form_id, '_uw_inquiry_success_type', true) ?: 'popup';
        $success_message = get_post_meta($form_id, '_uw_inquiry_success_message', true) ?: '정상적으로 접수되었습니다.';
        $success_page_id = get_post_meta($form_id, '_uw_inquiry_success_page_id', true);

        // 활성화된 필드만 필터링
        $active_fields = array_filter($fields, function($field) {
            return !empty($field['enabled']);
        });

        // 순서대로 정렬
        usort($active_fields, function($a, $b) {
            return ($a['order'] ?? 0) - ($b['order'] ?? 0);
        });

        ob_start();
        ?>
        <section class="uw-inquiry" id="uw-inquiry-<?php echo esc_attr($form_id); ?>" data-uw-inquiry>
            <form class="uw-inquiry-form uw-inquiry-form" 
                  data-form-id="<?php echo esc_attr($form_id); ?>"
                  data-success-type="<?php echo esc_attr($success_type); ?>"
                  data-success-message="<?php echo esc_attr($success_message); ?>"
                  data-success-url="<?php echo $success_page_id ? esc_url(get_permalink($success_page_id)) : ''; ?>"
                  enctype="multipart/form-data"
                  novalidate>
                
                <div class="uw-inquiry-fields">
                    <?php foreach ($active_fields as $field) : ?>
                        <?php $this->render_field($field, $form_id); ?>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($privacy_required) : ?>
                <div class="uw-inquiry-privacy<?php echo $privacy_required ? ' uw-inquiry-privacy-required' : ''; ?>">
                    <?php if (!empty($privacy_text)) : ?>
                    <div class="uw-inquiry-privacy-content">
                        <div class="uw-inquiry-privacy-text">
                            <?php echo wp_kses_post(wpautop($privacy_text)); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($privacy_required) : ?>
                    <div class="uw-inquiry-privacy-agree-wrap">
                        <label class="uw-inquiry-privacy-agree uw-privacy-agree">
                            <input type="checkbox" 
                                   class="uw-inquiry-privacy-checkbox" 
                                   name="privacy_agree" 
                                   value="1" 
                                   required>
                            <span class="uw-inquiry-privacy-label">
                                개인정보 처리방침에 동의합니다.
                                <abbr class="uw-inquiry-field-required" title="필수">*</abbr>
                            </span>
                        </label>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($captcha_enabled) : ?>
                <fieldset class="uw-inquiry-field uw-inquiry-field--captcha uw-inquiry-field--required uw-field-group" data-field-type="captcha">
                    <div class="uw-inquiry-field-header">
                        <label class="uw-inquiry-field-label" for="uw_captcha_<?php echo esc_attr($form_id); ?>">
                            자동등록방지
                            <abbr class="uw-inquiry-field-required" title="필수">*</abbr>
                        </label>
                    </div>

                    <div class="uw-inquiry-field-control">
                        <div class="uw-inquiry-captcha uw-captcha-wrapper">
                            <img src="<?php echo esc_url(get_theme_file_uri('inc/uw-inquiry/captcha.php')); ?>?t=<?php echo time(); ?>" 
                                 alt="자동등록방지" 
                                 id="uw_captcha_image_<?php echo esc_attr($form_id); ?>"
                                 class="uw-inquiry-captcha-image uw-captcha-image">
                            <button type="button" 
                                    class="uw-inquiry-captcha-refresh uw-captcha-refresh" 
                                    data-captcha-refresh="<?php echo esc_attr($form_id); ?>"
                                    aria-label="캡챠 새로고침">
                                새로고침
                            </button>
                        </div>
                        <input type="text" 
                               class="uw-inquiry-field-input uw-inquiry-captcha-input"
                               id="uw_captcha_<?php echo esc_attr($form_id); ?>" 
                               name="captcha_answer" 
                               placeholder="숫자 입력"
                               required
                               autocomplete="off"
                               maxlength="6"
                               aria-describedby="captcha-help-<?php echo esc_attr($form_id); ?>">
                    </div>
                    <span class="uw-inquiry-field-error" role="alert" aria-live="polite"></span>
                </fieldset>
                <?php endif; ?>
                
                <?php
                /* 보안 — honeypot 등 외부 hook (cm-security.php 등록) */
                do_action('uw_inquiry_form_before_submit', $form_id);
                ?>
                <div class="uw-inquiry-actions uw-inquiry-submit">
                    <button type="submit" class="uw-inquiry-submit uw-submit-btn">
                        <span class="uw-inquiry-submit-text btn-text">문의하기</span>
                        <span class="uw-inquiry-submit-loading btn-loading" aria-hidden="true">처리중...</span>
                    </button>
                </div>
                
                <div class="uw-inquiry-message uw-inquiry-message" role="status" aria-live="polite"></div>
            </form>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * 필드 렌더링 (BEM 구조)
     * 
     * @param array $field Field configuration
     * @param int $form_id Form ID for unique identifiers
     */
    private function render_field($field, $form_id = 0)
    {
        $field_id = 'uw_field_' . $field['id'];
        $field_name = $field['id'];
        $field_type = $field['type'];
        $is_required = !empty($field['required']);
        $help_id = $field_id . '_help';
        $error_id = $field_id . '_error';
        
        // BEM modifier classes
        $modifiers = array(
            'uw-inquiry-field--' . esc_attr($field_type),
            'uw-inquiry-field--' . esc_attr($field['id']), // ID-based modifier
        );
        if ($is_required) {
            $modifiers[] = 'uw-inquiry-field--required';
        }
        ?>
        <fieldset class="uw-inquiry-field <?php echo implode(' ', $modifiers); ?> uw-field-group uw-field-type-<?php echo esc_attr($field_type); ?>" 
                  data-field-type="<?php echo esc_attr($field_type); ?>" 
                  data-field-id="<?php echo esc_attr($field['id']); ?>"
                  data-label="<?php echo esc_attr($field['label']); ?>">
            
            <div class="uw-inquiry-field-header uw-inquiry-field-header--<?php echo esc_attr($field['id']); ?>">
                <label class="uw-inquiry-field-label uw-inquiry-field-label--<?php echo esc_attr($field['id']); ?>" for="<?php echo esc_attr($field_id); ?>">
                    <?php echo esc_html($field['label']); ?>
                    <?php if ($is_required) : ?>
                        <abbr class="uw-inquiry-field-required" title="필수">*</abbr>
                    <?php endif; ?>
                </label>
            </div>
            
            <?php if (!empty($field['help_text'])) : ?>
                <p class="uw-inquiry-field-help uw-field-help" id="<?php echo esc_attr($help_id); ?>">
                    <?php echo esc_html($field['help_text']); ?>
                </p>
            <?php endif; ?>
            
            <div class="uw-inquiry-field-control">
                <?php
                $aria_describedby = !empty($field['help_text']) ? $help_id : '';
                
                switch ($field_type) {
                    case 'textarea':
                        ?>
                        <textarea 
                            class="uw-inquiry-field-input uw-inquiry-field-textarea"
                            id="<?php echo esc_attr($field_id); ?>" 
                            name="<?php echo esc_attr($field_name); ?>" 
                            placeholder="<?php echo esc_attr($field['placeholder']); ?>"
                            rows="5"
                            <?php echo $aria_describedby ? 'aria-describedby="' . esc_attr($aria_describedby) . '"' : ''; ?>
                            <?php echo $is_required ? 'required' : ''; ?>
                        ></textarea>
                        <?php
                        break;

                    case 'select':
                        $options = !empty($field['options']) ? explode("\n", $field['options']) : array();
                        ?>
                        <select 
                            class="uw-inquiry-field-input uw-inquiry-field-select"
                            id="<?php echo esc_attr($field_id); ?>" 
                            name="<?php echo esc_attr($field_name); ?>"
                            <?php echo $aria_describedby ? 'aria-describedby="' . esc_attr($aria_describedby) . '"' : ''; ?>
                            <?php echo $is_required ? 'required' : ''; ?>
                        >
                            <option value=""><?php echo esc_html($field['placeholder'] ?: '선택하세요'); ?></option>
                            <?php foreach ($options as $option) : 
                                $option = trim($option);
                                if (empty($option)) continue;
                            ?>
                                <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php
                        break;

                    case 'checkbox':
                        $options = !empty($field['options']) ? explode("\n", $field['options']) : array();
                        ?>
                        <div class="uw-inquiry-field-options uw-inquiry-field-checkbox-group uw-checkbox-group">
                            <?php foreach ($options as $index => $option) : 
                                $option = trim($option);
                                if (empty($option)) continue;
                                $option_id = $field_id . '_' . $index;
                            ?>
                                <label class="uw-inquiry-field-option uw-checkbox-item" for="<?php echo esc_attr($option_id); ?>">
                                    <input 
                                        class="uw-inquiry-field-checkbox"
                                        type="checkbox" 
                                        id="<?php echo esc_attr($option_id); ?>"
                                        name="<?php echo esc_attr($field_name); ?>[]" 
                                        value="<?php echo esc_attr($option); ?>"
                                    >
                                    <span class="uw-inquiry-field-option-label"><?php echo esc_html($option); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <?php
                        break;

                    case 'radio':
                        $options = !empty($field['options']) ? explode("\n", $field['options']) : array();
                        ?>
                        <div class="uw-inquiry-field-options uw-inquiry-field-radio-group uw-radio-group">
                            <?php foreach ($options as $index => $option) : 
                                $option = trim($option);
                                if (empty($option)) continue;
                                $option_id = $field_id . '_' . $index;
                            ?>
                                <label class="uw-inquiry-field-option uw-radio-item" for="<?php echo esc_attr($option_id); ?>">
                                    <input 
                                        class="uw-inquiry-field-radio"
                                        type="radio" 
                                        id="<?php echo esc_attr($option_id); ?>"
                                        name="<?php echo esc_attr($field_name); ?>" 
                                        value="<?php echo esc_attr($option); ?>"
                                        <?php echo $index === 0 && $is_required ? 'required' : ''; ?>
                                    >
                                    <span class="uw-inquiry-field-option-label"><?php echo esc_html($option); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <?php
                        break;

                    case 'file':
                        $is_multi      = !empty($field['multiple']);
                        $max_files     = isset($field['max_files']) ? (int) $field['max_files'] : 1;
                        $max_filesize  = isset($field['max_filesize']) ? (int) $field['max_filesize'] : (10 * 1024 * 1024);
                        $max_filesize_mb = max(1, round($max_filesize / (1024 * 1024)));

                        if ($is_multi) :
                            $multi_input_name = $field_name . '[]';
                            ?>
                            <div class="uw-inquiry-field-multifile uw-multifile-wrapper"
                                 data-multifile
                                 data-max-files="<?php echo esc_attr($max_files); ?>"
                                 data-max-filesize="<?php echo esc_attr($max_filesize); ?>">
                                <div class="uw-multifile-box">
                                    <button type="button" class="uw-multifile-btn" aria-controls="<?php echo esc_attr($field_id); ?>">
                                        <i class="xi-plus" aria-hidden="true"></i>
                                        파일 추가하기
                                    </button>
                                    <p class="uw-multifile-help">최대 <?php echo (int) $max_files; ?>개, 파일당 <?php echo (int) $max_filesize_mb; ?>MB</p>
                                </div>
                                <input
                                    class="uw-inquiry-field-input uw-multifile-input"
                                    type="file"
                                    id="<?php echo esc_attr($field_id); ?>"
                                    name="<?php echo esc_attr($multi_input_name); ?>"
                                    multiple
                                    style="display: none;"
                                    <?php echo $aria_describedby ? 'aria-describedby="' . esc_attr($aria_describedby) . '"' : ''; ?>
                                    <?php echo $is_required ? 'required' : ''; ?>
                                >
                                <ul class="uw-multifile-list" hidden></ul>
                            </div>
                            <?php
                        else :
                            ?>
                            <div class="uw-inquiry-field-file uw-file-wrapper">
                                <div class="uw-inquiry-file-box">
                                    <button type="button" class="uw-inquiry-file-btn">파일 선택</button>
                                    <div class="uw-inquiry-file-status">
                                        <span class="uw-inquiry-file-placeholder"><?php echo esc_html($field['placeholder'] ?: '회사소개서, 서비스 소개서, 상품 브로셔 등 홈페이지 제작에 참고할 만한 자료가 있다면 첨부해 주세요. 최대 100MB까지 첨부할 수 있습니다.'); ?></span>
                                    </div>
                                    <div class="uw-inquiry-file-size">0 Byte / 100 MB</div>
                                </div>
                                <input
                                    class="uw-inquiry-field-input uw-inquiry-field-file-input"
                                    type="file"
                                    id="<?php echo esc_attr($field_id); ?>"
                                    name="<?php echo esc_attr($field_name); ?>"
                                    style="display: none;"
                                    <?php echo $aria_describedby ? 'aria-describedby="' . esc_attr($aria_describedby) . '"' : ''; ?>
                                    <?php echo $is_required ? 'required' : ''; ?>
                                >
                                <div class="uw-inquiry-field-file-preview uw-file-preview" style="display: none;">
                                    <span class="uw-inquiry-field-file-name uw-file-name"></span>
                                    <button type="button" class="uw-inquiry-field-file-remove uw-file-remove" title="삭제" aria-label="파일 삭제">&times;</button>
                                </div>
                            </div>
                            <?php
                        endif;
                        break;

                    case 'date':
                        ?>
                        <input 
                            class="uw-inquiry-field-input uw-inquiry-field-date"
                            type="date" 
                            id="<?php echo esc_attr($field_id); ?>" 
                            name="<?php echo esc_attr($field_name); ?>" 
                            <?php echo $aria_describedby ? 'aria-describedby="' . esc_attr($aria_describedby) . '"' : ''; ?>
                            <?php echo $is_required ? 'required' : ''; ?>
                        >
                        <?php
                        break;

                    case 'email':
                        ?>
                        <input 
                            class="uw-inquiry-field-input uw-inquiry-field-email"
                            type="email" 
                            id="<?php echo esc_attr($field_id); ?>" 
                            name="<?php echo esc_attr($field_name); ?>" 
                            placeholder="<?php echo esc_attr($field['placeholder'] ?: 'example@email.com'); ?>"
                            title="올바른 이메일 형식을 입력해주세요 (예: example@email.com)"
                            <?php echo $aria_describedby ? 'aria-describedby="' . esc_attr($aria_describedby) . '"' : ''; ?>
                            <?php echo $is_required ? 'required' : ''; ?>
                        >
                        <?php
                        break;

                    case 'tel':
                        ?>
                        <input 
                            class="uw-inquiry-field-input uw-inquiry-field-tel"
                            type="tel" 
                            id="<?php echo esc_attr($field_id); ?>" 
                            name="<?php echo esc_attr($field_name); ?>" 
                            placeholder="<?php echo esc_attr($field['placeholder'] ?: '010-0000-0000'); ?>"
                            <?php echo $aria_describedby ? 'aria-describedby="' . esc_attr($aria_describedby) . '"' : ''; ?>
                            <?php echo $is_required ? 'required' : ''; ?>
                        >
                        <?php
                        break;

                    default: // text
                        ?>
                        <input 
                            class="uw-inquiry-field-input uw-inquiry-field-text"
                            type="text" 
                            id="<?php echo esc_attr($field_id); ?>" 
                            name="<?php echo esc_attr($field_name); ?>" 
                            placeholder="<?php echo esc_attr($field['placeholder']); ?>"
                            <?php echo $aria_describedby ? 'aria-describedby="' . esc_attr($aria_describedby) . '"' : ''; ?>
                            <?php echo $is_required ? 'required' : ''; ?>
                        >
                        <?php
                        break;
                }
                ?>
            </div>
            
            <span class="uw-inquiry-field-error" id="<?php echo esc_attr($error_id); ?>" role="alert" aria-live="polite"></span>
        </fieldset>
        <?php
    }

    /**
     * AJAX: 폼 제출 처리
     */
    public function ajax_submit_form()
    {
        // nonce 검증
        if (!check_ajax_referer('uw_inquiry_nonce', 'nonce', false)) {
            wp_send_json_error('보안 검증에 실패했습니다.');
        }

        // Rate Limiting (IP 기반: 5분에 3회 제한)
        $client_ip = $this->get_client_ip();
        $rate_limit_key = 'uw_inquiry_rate_' . md5($client_ip);
        $rate_limit = get_transient($rate_limit_key);

        if ($rate_limit === false) {
            set_transient($rate_limit_key, 1, 5 * MINUTE_IN_SECONDS);
        } elseif ($rate_limit >= 3) {
            wp_send_json_error('너무 많은 요청입니다. 잠시 후 다시 시도해주세요.');
        } else {
            set_transient($rate_limit_key, $rate_limit + 1, 5 * MINUTE_IN_SECONDS);
        }

        $form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;

        if (!$form_id) {
            wp_send_json_error('폼 정보가 없습니다.');
        }

        $form = get_post($form_id);
        if (!$form || $form->post_type !== 'uw_inquiry_form') {
            wp_send_json_error('폼을 찾을 수 없습니다.');
        }

        // 이미지 캡챠 검증 (활성화된 경우만)
        $captcha_enabled = get_post_meta($form_id, '_uw_inquiry_captcha_enabled', true);
        if ($captcha_enabled) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $submitted_answer = isset($_POST['captcha_answer']) ? trim($_POST['captcha_answer']) : '';
            $correct_answer = isset($_SESSION['uw_captcha_code']) ? $_SESSION['uw_captcha_code'] : '';
            
            if (empty($correct_answer) || $submitted_answer !== $correct_answer) {
                wp_send_json_error('자동등록방지 코드가 올바르지 않습니다.');
            }
            // 사용 후 세션 삭제
            unset($_SESSION['uw_captcha_code']);
        }

        // 필드 데이터 수집
        $fields = get_post_meta($form_id, '_uw_inquiry_fields', true);
        $fields = is_array($fields) ? $fields : UW_Inquiry_CPT::get_default_fields();

        $entry_data = array();
        foreach ($fields as $field) {
            if (empty($field['enabled'])) continue;

            $field_id = $field['id'];
            
            // 파일 필드 처리
            if ($field['type'] === 'file') {
                $is_multi      = !empty($field['multiple']);
                $max_files     = isset($field['max_files']) ? (int) $field['max_files'] : 1;
                $max_filesize  = isset($field['max_filesize']) ? (int) $field['max_filesize'] : (10 * 1024 * 1024);

                // 확장자별 허용 MIME 매핑. 확장자 1차 게이트 + MIME 2차 검증.
                // 한글(hwp/hwpx)·압축(rar/7z)·디자인(ai/psd) 등은 환경에 따라 application/octet-stream으로
                // 인식되므로 octet-stream도 허용 목록에 포함.
                $allowed_ext_to_mimes = array(
                    // 이미지
                    'jpg'  => array('image/jpeg'),
                    'jpeg' => array('image/jpeg'),
                    'png'  => array('image/png'),
                    'gif'  => array('image/gif'),
                    'webp' => array('image/webp'),
                    'bmp'  => array('image/bmp', 'image/x-ms-bmp'),
                    'svg'  => array('image/svg+xml', 'text/xml', 'text/plain'),
                    'heic' => array('image/heic', 'image/heif', 'application/octet-stream'),
                    'heif' => array('image/heif', 'image/heic', 'application/octet-stream'),
                    'tiff' => array('image/tiff'),
                    'tif'  => array('image/tiff'),
                    // 문서
                    'pdf'  => array('application/pdf'),
                    'doc'  => array('application/msword', 'application/vnd.ms-office', 'application/octet-stream'),
                    'docx' => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'),
                    'xls'  => array('application/vnd.ms-excel', 'application/vnd.ms-office', 'application/octet-stream'),
                    'xlsx' => array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'),
                    'ppt'  => array('application/vnd.ms-powerpoint', 'application/vnd.ms-office', 'application/octet-stream'),
                    'pptx' => array('application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'),
                    // 한글 (한컴오피스)
                    'hwp'  => array('application/x-hwp', 'application/haansofthwp', 'application/vnd.hancom.hwp', 'application/octet-stream'),
                    'hwpx' => array('application/hwp+zip', 'application/vnd.hancom.hwpx', 'application/zip', 'application/octet-stream'),
                    // 텍스트
                    'txt'  => array('text/plain'),
                    'csv'  => array('text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'),
                    'rtf'  => array('application/rtf', 'text/rtf'),
                    // 압축
                    'zip'  => array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/octet-stream'),
                    'rar'  => array('application/vnd.rar', 'application/x-rar-compressed', 'application/x-rar', 'application/octet-stream'),
                    '7z'   => array('application/x-7z-compressed', 'application/octet-stream'),
                    // 디자인 원본
                    'ai'   => array('application/postscript', 'application/illustrator', 'application/pdf', 'application/octet-stream'),
                    'psd'  => array('image/vnd.adobe.photoshop', 'application/x-photoshop', 'application/octet-stream'),
                );
                $allowed_extensions = array_keys($allowed_ext_to_mimes);

                if (!function_exists('wp_handle_upload')) {
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                }
                $upload_overrides = array('test_form' => false);

                // 비표준 확장자(hwp/hwpx/pptx/7z/rar/ai/psd 등)를 wp_handle_upload가 거부하지 않도록
                // 폼 처리 중에만 upload_mimes 필터 확장.
                $extra_mimes_filter = function ($mimes) {
                    $extra = array(
                        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'ppt'  => 'application/vnd.ms-powerpoint',
                        'hwp'  => 'application/x-hwp',
                        'hwpx' => 'application/hwp+zip',
                        'rar'  => 'application/vnd.rar',
                        '7z'   => 'application/x-7z-compressed',
                        'ai'   => 'application/postscript',
                        'psd'  => 'image/vnd.adobe.photoshop',
                        'svg'  => 'image/svg+xml',
                        'heic' => 'image/heic',
                        'heif' => 'image/heif',
                        'tiff|tif' => 'image/tiff',
                        'bmp'  => 'image/bmp',
                        'rtf'  => 'application/rtf',
                        'csv'  => 'text/csv',
                    );
                    return array_merge((array) $mimes, $extra);
                };
                add_filter('upload_mimes', $extra_mimes_filter, 99);
                // wp_check_filetype_and_ext 가 MIME 불일치 시 false ext를 돌려주는 케이스도 우회.
                $skip_real_mime = function ($data, $file, $filename, $mimes, $real_mime) use ($allowed_extensions) {
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    if (in_array($ext, $allowed_extensions, true)) {
                        $data['ext']  = $ext;
                        $data['type'] = isset($mimes[$ext]) ? $mimes[$ext] : ($real_mime ?: 'application/octet-stream');
                    }
                    return $data;
                };
                add_filter('wp_check_filetype_and_ext', $skip_real_mime, 99, 5);

                // 다중 파일: $_FILES[field_id] 의 각 키가 배열
                $files_iter = array();
                if (!empty($_FILES[$field_id])) {
                    if ($is_multi && is_array($_FILES[$field_id]['name'])) {
                        $count = count($_FILES[$field_id]['name']);
                        if ($count > $max_files) {
                            wp_send_json_error('첨부파일은 최대 ' . $max_files . '개까지 가능합니다.');
                        }
                        for ($i = 0; $i < $count; $i++) {
                            if ($_FILES[$field_id]['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                            $files_iter[] = array(
                                'name'     => $_FILES[$field_id]['name'][$i],
                                'type'     => $_FILES[$field_id]['type'][$i],
                                'tmp_name' => $_FILES[$field_id]['tmp_name'][$i],
                                'error'    => $_FILES[$field_id]['error'][$i],
                                'size'     => $_FILES[$field_id]['size'][$i],
                            );
                        }
                    } elseif (!$is_multi && $_FILES[$field_id]['error'] !== UPLOAD_ERR_NO_FILE) {
                        $files_iter[] = $_FILES[$field_id];
                    }
                }

                if (empty($files_iter)) {
                    if (!empty($field['required'])) {
                        wp_send_json_error($field['label'] . ' 파일을 첨부해주세요.');
                    }
                    $entry_data[$field_id] = '';
                    continue;
                }

                // Phase 1: 모든 파일 사전 검증 (확장자 + MIME + 크기 + 업로드 에러)
                foreach ($files_iter as $f) {
                    if ($f['error'] !== UPLOAD_ERR_OK) {
                        remove_filter('upload_mimes', $extra_mimes_filter, 99);
                        remove_filter('wp_check_filetype_and_ext', $skip_real_mime, 99);
                        wp_send_json_error('파일 업로드 중 오류가 발생했습니다. (' . esc_html($f['name']) . ')');
                    }
                    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                    if (!isset($allowed_ext_to_mimes[$ext])) {
                        remove_filter('upload_mimes', $extra_mimes_filter, 99);
                        remove_filter('wp_check_filetype_and_ext', $skip_real_mime, 99);
                        wp_send_json_error('허용되지 않는 파일 형식입니다. (허용: 이미지·PDF·Word·Excel·PowerPoint·한글·텍스트·압축·디자인 원본) — ' . esc_html($f['name']));
                    }
                    $actual_type = wp_check_filetype($f['name'])['type'];
                    if (function_exists('finfo_open')) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $actual_type = finfo_file($finfo, $f['tmp_name']);
                        finfo_close($finfo);
                    }
                    if (!in_array($actual_type, $allowed_ext_to_mimes[$ext], true)) {
                        remove_filter('upload_mimes', $extra_mimes_filter, 99);
                        remove_filter('wp_check_filetype_and_ext', $skip_real_mime, 99);
                        wp_send_json_error('파일 내용이 확장자와 일치하지 않습니다. — ' . esc_html($f['name']));
                    }
                    if ($f['size'] > $max_filesize) {
                        remove_filter('upload_mimes', $extra_mimes_filter, 99);
                        remove_filter('wp_check_filetype_and_ext', $skip_real_mime, 99);
                        wp_send_json_error('파일 크기가 너무 큽니다. (최대 ' . round($max_filesize / 1024 / 1024) . 'MB) — ' . esc_html($f['name']));
                    }
                }

                // Phase 2: 일괄 업로드. 도중 실패 시 앞서 업로드된 파일 롤백
                $uploaded_list = array();
                foreach ($files_iter as $f) {
                    $uploaded_file = wp_handle_upload($f, $upload_overrides);
                    if (!$uploaded_file || isset($uploaded_file['error'])) {
                        foreach ($uploaded_list as $rollback) {
                            if (!empty($rollback['file']) && file_exists($rollback['file'])) {
                                @unlink($rollback['file']);
                            }
                        }
                        remove_filter('upload_mimes', $extra_mimes_filter, 99);
                        remove_filter('wp_check_filetype_and_ext', $skip_real_mime, 99);
                        wp_send_json_error('파일 업로드에 실패했습니다. (' . esc_html($f['name']) . ')');
                    }
                    $uploaded_list[] = array(
                        'url'  => $uploaded_file['url'],
                        'file' => $uploaded_file['file'],
                        'name' => sanitize_file_name($f['name']),
                        'type' => $uploaded_file['type'],
                    );
                }

                remove_filter('upload_mimes', $extra_mimes_filter, 99);
                remove_filter('wp_check_filetype_and_ext', $skip_real_mime, 99);

                $entry_data[$field_id] = $is_multi ? $uploaded_list : (isset($uploaded_list[0]) ? $uploaded_list[0] : '');
                continue;
            }
            
            $value = isset($_POST[$field_id]) ? $_POST[$field_id] : '';

            // 배열 값 처리 (checkbox)
            if (is_array($value)) {
                $value = implode(', ', array_map('sanitize_text_field', $value));
            } else {
                $value = $field['type'] === 'textarea' ? wp_kses_post($value) : sanitize_text_field($value);
            }

            // 필수 필드 검증
            if (!empty($field['required']) && empty($value)) {
                wp_send_json_error($field['label'] . ' 항목은 필수입니다.');
            }

            $entry_data[$field_id] = $value;
        }

        // 개인정보 동의 확인
        $privacy_required = get_post_meta($form_id, '_uw_inquiry_privacy_required', true);
        if ($privacy_required && empty($_POST['privacy_agree'])) {
            wp_send_json_error('개인정보 처리방침에 동의해주세요.');
        }

        // 문의 내역 저장
        $now_local = current_time('Y-m-d H:i');
        $entry_title = !empty($entry_data['field_name'])
            ? $entry_data['field_name'] . ' - ' . $now_local
            : '문의 - ' . $now_local;

        $entry_id = wp_insert_post(array(
            'post_title'  => $entry_title,
            'post_type'   => 'uw_inquiry_entry',
            'post_status' => 'private',
        ));

        if (!$entry_id || is_wp_error($entry_id)) {
            wp_send_json_error('저장에 실패했습니다. 다시 시도해주세요.');
        }

        // 메타 데이터 저장
        update_post_meta($entry_id, '_uw_inquiry_form_id', $form_id);
        update_post_meta($entry_id, '_uw_inquiry_data', $entry_data);
        update_post_meta($entry_id, '_uw_inquiry_ip', $this->get_client_ip());
        update_post_meta($entry_id, '_uw_inquiry_user_agent', isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '');

        // 어드민 알림 메일
        $this->send_notification_email($form_id, $entry_data, $entry_id);

        // 어드민 SMS 알림 (메일과 독립 — Solapi 상수 설정 시에만 발송)
        $this->send_admin_sms_notification($form_id, $entry_data, $entry_id);

        // 문의자 자동회신 메일
        $this->send_autoreply_email($form_id, $entry_data, $entry_id);

        // 성공 응답
        $success_type = get_post_meta($form_id, '_uw_inquiry_success_type', true) ?: 'popup';
        $success_message = get_post_meta($form_id, '_uw_inquiry_success_message', true) ?: '정상적으로 접수되었습니다.';
        $success_page_id = get_post_meta($form_id, '_uw_inquiry_success_page_id', true);

        wp_send_json_success(array(
            'message' => $success_message,
            'type' => $success_type,
            'redirect' => $success_page_id ? get_permalink($success_page_id) : '',
        ));
    }

    /**
     * 어드민 SMS 알림 (Solapi) — 메일과 별개로 발송. inc/cm-sms.php
     */
    private function send_admin_sms_notification($form_id, $entry_data, $entry_id)
    {
        if (!function_exists('uw_send_admin_sms')) return;

        $form  = get_post($form_id);
        $title = $form ? $form->post_title : '문의';

        $name  = isset($entry_data['field_name'])  ? trim((string) $entry_data['field_name'])  : '';
        $phone = isset($entry_data['field_phone']) ? trim((string) $entry_data['field_phone']) : '';

        $lines = array('[언웹스] 새 문의 접수', $title);
        $who = trim($name . ($phone !== '' ? ' / ' . $phone : ''));
        if ($who !== '') $lines[] = $who;
        $lines[] = '메일·관리자페이지에서 확인하세요.';

        uw_send_admin_sms(implode("\n", $lines));
    }

    /**
     * 알림 이메일 발송
     */
    private function send_notification_email($form_id, $entry_data, $entry_id)
    {
        $notify_emails = get_post_meta($form_id, '_uw_inquiry_notify_emails', true);
        
        // 디버그 로깅 (WP_DEBUG 활성화 시)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[UW Inquiry] send_notification_email called for entry_id: ' . $entry_id);
            error_log('[UW Inquiry] notify_emails setting: ' . $notify_emails);
        }
        
        if (empty($notify_emails)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[UW Inquiry] No notification emails configured - skipping email send');
            }
            return;
        }

        $emails = array_map('trim', explode(',', $notify_emails));
        $emails = array_filter($emails, 'is_email');

        if (empty($emails)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[UW Inquiry] No valid emails found after filtering');
            }
            return;
        }

        $form = get_post($form_id);
        $fields = get_post_meta($form_id, '_uw_inquiry_fields', true);
        $fields = is_array($fields) ? $fields : array();

        $mail_subject = get_post_meta($form_id, '_uw_inquiry_mail_subject', true);
        if (empty($mail_subject)) {
            $mail_subject = '[' . get_bloginfo('name') . '] 새로운 문의가 접수되었습니다';
        }

        // 이메일 본문 구성
        $message = "새로운 문의가 접수되었습니다.\n\n";
        $message .= "폼: " . $form->post_title . "\n";
        $message .= "접수일시: " . current_time('Y-m-d H:i:s') . "\n\n";
        $message .= "----------------------------------------\n\n";

        $attachments = array();
        $reply_to_email = '';

        foreach ($fields as $field) {
            if (!empty($field['enabled']) && isset($entry_data[$field['id']])) {
                $value = $entry_data[$field['id']];

                // 파일 필드 처리 (배열인 경우)
                if (is_array($value)) {
                    if (isset($value[0]) && is_array($value[0]) && isset($value[0]['file'])) {
                        $names = array();
                        foreach ($value as $item) {
                            if (isset($item['file']) && file_exists($item['file'])) {
                                $attachments[] = $item['file'];
                            }
                            if (isset($item['name'])) $names[] = $item['name'];
                        }
                        $value = !empty($names) ? implode(', ', $names) . ' (첨부파일 ' . count($names) . '개)' : '';
                    } elseif (isset($value['file']) && file_exists($value['file'])) {
                        $attachments[] = $value['file'];
                        $value = $value['name'] . ' (첨부파일)';
                    } elseif (isset($value['name'])) {
                        $value = $value['name'] . ' (첨부파일)';
                    } else {
                        $value = implode(', ', $value);
                    }
                }

                // 빈 값 본문 출력 생략 (라벨만 남는 빈 줄 방지)
                if ($value === '' || $value === null) continue;

                // Reply-To 자동 추출: type=email 첫 필드
                if ($field['type'] === 'email' && empty($reply_to_email) && is_email($value)) {
                    $reply_to_email = $value;
                }

                $message .= $field['label'] . ": " . $value . "\n\n";
            }
        }

        // 메일 첨부 용량 제한 (Gmail 등 25MB 한도 → base64 인코딩 여유로 18MB 안전선)
        $mail_attach_limit = apply_filters('uw_inquiry_mail_attach_limit', 18 * 1024 * 1024);
        $attach_total = 0;
        foreach ($attachments as $att_file) {
            if (is_string($att_file) && file_exists($att_file)) {
                $attach_total += filesize($att_file);
            }
        }
        $attachments_skipped = false;
        if ($attach_total > $mail_attach_limit) {
            $attachments = array();      // 메일 첨부 제외 (데이터·파일은 이미 저장되어 무손실)
            $attachments_skipped = true;
        }

        $message .= "----------------------------------------\n\n";
        if ($attachments_skipped) {
            $message .= "※ 첨부파일 용량이 커서 이 메일에는 포함하지 않았습니다.\n";
            $message .= "   아래 관리자 페이지에서 직접 다운로드해 주세요.\n\n";
        }
        $message .= "관리자 페이지에서 상세 내용을 확인하세요:\n";
        $message .= admin_url('admin.php?page=uw-inquiry&action=view_entry&entry_id=' . $entry_id . '&form_id=' . $form_id);

        // From: 사이트 도메인 기반 noreply (필터로 운영 환경에서 변경 가능)
        $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
        $from_email = apply_filters('uw_inquiry_from_email', 'contact@unwebs.co.kr');
        $from_name  = apply_filters('uw_inquiry_from_name', get_bloginfo('name'));

        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>',
        );
        if (!empty($reply_to_email)) {
            $headers[] = 'Reply-To: ' . $reply_to_email;
        }

        // 각 이메일 주소로 발송
        foreach ($emails as $email) {
            $result = wp_mail($email, $mail_subject, $message, $headers, $attachments);
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                if ($result) {
                    error_log('[UW Inquiry] Email sent successfully to: ' . $email);
                } else {
                    error_log('[UW Inquiry] Email FAILED to send to: ' . $email);
                    // WordPress 메일 오류 확인
                    global $phpmailer;
                    if (isset($phpmailer) && is_object($phpmailer)) {
                        error_log('[UW Inquiry] PHPMailer error: ' . $phpmailer->ErrorInfo);
                    }
                }
            }
        }
    }

    /**
     * 자동회신 메일 (문의자에게 HTML 메일)
     */
    private function send_autoreply_email($form_id, $entry_data, $entry_id)
    {
        // 활성/비활성 토글 (필터)
        if (!apply_filters('uw_inquiry_autoreply_enabled', true, $form_id)) return;

        $to_email = isset($entry_data['field_email']) ? $entry_data['field_email'] : '';
        if (!is_email($to_email)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[UW Inquiry] autoreply skipped — invalid email');
            }
            return;
        }

        $form          = get_post($form_id);
        $fields        = get_post_meta($form_id, '_uw_inquiry_fields', true);
        $fields        = is_array($fields) ? $fields : array();
        $contact_email = apply_filters('uw_inquiry_contact_email', 'contact@unwebs.co.kr');
        $kakao_url     = apply_filters('uw_inquiry_kakao_url', get_option('uw_inquiry_kakao_url', ''));
        $site_url      = home_url();
        $site_name     = get_bloginfo('name');

        // From / Reply-To
        $site_host  = wp_parse_url($site_url, PHP_URL_HOST);
        $from_email = apply_filters('uw_inquiry_from_email', 'contact@unwebs.co.kr');
        $from_name  = apply_filters('uw_inquiry_from_name', $site_name);

        // 제목
        $subject = apply_filters(
            'uw_inquiry_autoreply_subject',
            '[' . $site_name . '] 문의 접수가 완료되었습니다',
            $form_id, $entry_data
        );

        // HTML 본문
        ob_start();
        include __DIR__ . '/templates/autoreply.php';
        $html = ob_get_clean();

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Reply-To: ' . $contact_email,
        );

        $sent = wp_mail($to_email, $subject, $html, $headers);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[UW Inquiry] autoreply ' . ($sent ? 'sent' : 'FAILED') . ' to ' . $to_email);
        }
    }

    /**
     * 클라이언트 IP 가져오기
     */
    private function get_client_ip()
    {
        $ip_keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        );

        foreach ($ip_keys as $key) {
            if (isset($_SERVER[$key])) {
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
}

// Initialize
UW_Inquiry_Handler::get_instance();
