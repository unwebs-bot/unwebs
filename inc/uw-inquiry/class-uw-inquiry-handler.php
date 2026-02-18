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
                if (!empty($_FILES[$field_id]) && $_FILES[$field_id]['error'] === UPLOAD_ERR_OK) {
                    // 허용된 MIME 타입 검증
                    $allowed_types = array(
                        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/zip', 'application/x-zip-compressed'
                    );
                    
                    $file_type = wp_check_filetype($_FILES[$field_id]['name']);
                    $actual_type = $file_type['type'];

                    // PHP fileinfo 확장으로 실제 MIME 타입 검증
                    if (function_exists('finfo_open')) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $actual_type = finfo_file($finfo, $_FILES[$field_id]['tmp_name']);
                        finfo_close($finfo);
                    }

                    if (!in_array($actual_type, $allowed_types)) {
                        wp_send_json_error('허용되지 않는 파일 형식입니다. (허용: 이미지, PDF, 문서, 엑셀, ZIP)');
                    }

                    // 파일 크기 제한 (10MB)
                    $max_file_size = 10 * 1024 * 1024; // 10MB
                    if ($_FILES[$field_id]['size'] > $max_file_size) {
                        wp_send_json_error('파일 크기가 너무 큽니다. (최대 10MB)');
                    }

                    // 워드프레스 파일 업로드 함수 로드
                    if (!function_exists('wp_handle_upload')) {
                        require_once(ABSPATH . 'wp-admin/includes/file.php');
                    }
                    
                    $upload_overrides = array('test_form' => false);
                    $uploaded_file = wp_handle_upload($_FILES[$field_id], $upload_overrides);
                    
                    if ($uploaded_file && !isset($uploaded_file['error'])) {
                        // 파일 URL과 이름 저장
                        $entry_data[$field_id] = array(
                            'url' => $uploaded_file['url'],
                            'file' => $uploaded_file['file'],
                            'name' => sanitize_file_name($_FILES[$field_id]['name']),
                            'type' => $uploaded_file['type']
                        );
                    } else {
                        // 파일 업로드 실패
                        if (!empty($field['required'])) {
                            wp_send_json_error('파일 업로드에 실패했습니다.');
                        }
                        $entry_data[$field_id] = '';
                    }
                } else {
                    // 파일 없음
                    if (!empty($field['required']) && (empty($_FILES[$field_id]) || $_FILES[$field_id]['error'] === UPLOAD_ERR_NO_FILE)) {
                        wp_send_json_error($field['label'] . ' 파일을 첨부해주세요.');
                    }
                    $entry_data[$field_id] = '';
                }
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
        $entry_title = !empty($entry_data['field_name']) 
            ? $entry_data['field_name'] . ' - ' . date('Y-m-d H:i')
            : '문의 - ' . date('Y-m-d H:i');

        $entry_id = wp_insert_post(array(
            'post_title' => $entry_title,
            'post_type' => 'uw_inquiry_entry',
            'post_status' => 'publish',
        ));

        if (!$entry_id || is_wp_error($entry_id)) {
            wp_send_json_error('저장에 실패했습니다. 다시 시도해주세요.');
        }

        // 메타 데이터 저장
        update_post_meta($entry_id, '_uw_inquiry_form_id', $form_id);
        update_post_meta($entry_id, '_uw_inquiry_data', $entry_data);
        update_post_meta($entry_id, '_uw_inquiry_ip', $this->get_client_ip());
        update_post_meta($entry_id, '_uw_inquiry_user_agent', isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '');

        // 이메일 발송
        $this->send_notification_email($form_id, $entry_data, $entry_id);

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
        $message .= "접수일시: " . date('Y-m-d H:i:s') . "\n\n";
        $message .= "----------------------------------------\n\n";

        $attachments = array();

        foreach ($fields as $field) {
            if (!empty($field['enabled']) && isset($entry_data[$field['id']])) {
                $value = $entry_data[$field['id']];

                // 파일 필드 처리 (배열인 경우)
                if (is_array($value)) {
                    if (isset($value['file']) && file_exists($value['file'])) {
                        $attachments[] = $value['file'];
                        $value = $value['name'] . ' (첨부파일)';
                    } elseif (isset($value['name'])) {
                        $value = $value['name'] . ' (첨부파일)';
                    } else {
                        $value = implode(', ', $value);
                    }
                }

                $message .= $field['label'] . ": " . $value . "\n\n";
            }
        }

        $message .= "----------------------------------------\n\n";
        $message .= "관리자 페이지에서 상세 내용을 확인하세요:\n";
        $message .= admin_url('admin.php?page=uw-inquiry&action=view_entry&entry_id=' . $entry_id . '&form_id=' . $form_id);

        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );

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
