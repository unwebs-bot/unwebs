<?php
/**
 * UW Banner Settings - 메인페이지 상단 배너 관리자 설정
 */

if (!defined('ABSPATH')) exit;

/**
 * 관리자 메뉴 등록
 */
add_action('admin_menu', 'uw_banner_add_menu');
function uw_banner_add_menu()
{
    add_menu_page(
        '배너 설정',
        '배너 설정',
        'manage_options',
        'uw-banner-settings',
        'uw_banner_settings_page',
        'dashicons-megaphone',
        30
    );
}

/**
 * 설정 필드 등록
 */
add_action('admin_init', 'uw_banner_register_settings');
function uw_banner_register_settings()
{
    register_setting('uw_banner_options', 'uw_banner_options', 'uw_banner_sanitize');

    add_settings_section(
        'uw_banner_section',
        '진행현황 배너 설정',
        function () {
            echo '<p>메인페이지 상단에 표시되는 진행현황 배너의 수치를 설정합니다.</p>';
        },
        'uw-banner-settings'
    );

    $fields = array(
        'production_count'    => array('label' => '제작진행 건수', 'type' => 'number'),
        'revision_count'      => array('label' => '수정진행 건수', 'type' => 'number'),
        'maintenance_count'   => array('label' => '정기 유지보수 건수', 'type' => 'number'),
        'quarter_text'        => array('label' => '현재 쿼터 정보', 'type' => 'text', 'placeholder' => '2026년 2월 쿼터'),
        'available_count'     => array('label' => '진행가능 건수', 'type' => 'number'),
        'available_total'     => array('label' => '진행가능 전체 티오', 'type' => 'number'),
        'banner_visible'      => array('label' => '배너 표시', 'type' => 'checkbox'),
    );

    foreach ($fields as $id => $field) {
        add_settings_field(
            'uw_banner_' . $id,
            $field['label'],
            'uw_banner_render_field',
            'uw-banner-settings',
            'uw_banner_section',
            array_merge($field, array('id' => $id))
        );
    }
}

/**
 * 입력 필드 렌더링
 */
function uw_banner_render_field($args)
{
    $options = get_option('uw_banner_options', array());
    $id      = $args['id'];
    $type    = $args['type'];
    $value   = isset($options[$id]) ? $options[$id] : '';
    $name    = 'uw_banner_options[' . esc_attr($id) . ']';

    if ($type === 'checkbox') {
        $checked = !empty($value) ? 'checked' : '';
        echo '<label><input type="checkbox" name="' . $name . '" value="1" ' . $checked . '> 메인페이지에 배너를 표시합니다.</label>';
    } elseif ($type === 'number') {
        echo '<input type="number" name="' . $name . '" value="' . esc_attr($value) . '" class="small-text" min="0">';
    } else {
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
        echo '<input type="text" name="' . $name . '" value="' . esc_attr($value) . '" class="regular-text" placeholder="' . esc_attr($placeholder) . '">';
        if ($id === 'quarter_text') {
            echo '<p class="description">비워두면 현재 연/월 기준으로 자동 생성됩니다. (예: ' . esc_html(date_i18n('Y')) . '년 ' . esc_html(date_i18n('n')) . '월 쿼터)</p>';
        }
    }
}

/**
 * 입력값 살균
 */
function uw_banner_sanitize($input)
{
    $sanitized = array();

    $number_fields = array('production_count', 'revision_count', 'maintenance_count', 'available_count', 'available_total');
    foreach ($number_fields as $field) {
        $sanitized[$field] = isset($input[$field]) ? absint($input[$field]) : 0;
    }

    $sanitized['quarter_text']   = isset($input['quarter_text']) ? sanitize_text_field($input['quarter_text']) : '';
    $sanitized['banner_visible'] = !empty($input['banner_visible']) ? 1 : 0;

    add_settings_error('uw_banner_options', 'uw_banner_updated', '배너 설정이 저장되었습니다.', 'updated');

    return $sanitized;
}

/**
 * 설정 페이지 렌더링
 */
function uw_banner_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>배너 설정</h1>
        <?php settings_errors('uw_banner_options'); ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('uw_banner_options');
            do_settings_sections('uw-banner-settings');
            submit_button('저장');
            ?>
        </form>
    </div>
    <?php
}

/**
 * 배너 활성 시 body 클래스 추가
 */
add_filter('body_class', 'uw_banner_body_class');
function uw_banner_body_class($classes)
{
    if (is_front_page()) {
        $banner = get_option('uw_banner_options', array());
        if (!empty($banner['banner_visible'])) {
            $classes[] = 'has-uw-banner';
        }
    }
    return $classes;
}

/**
 * 배너 데이터 가져오기
 */
function uw_banner_get_data()
{
    $defaults = array(
        'production_count'  => 0,
        'revision_count'    => 0,
        'maintenance_count' => 0,
        'quarter_text'      => '',
        'available_count'   => 0,
        'available_total'   => 0,
        'banner_visible'    => 0,
    );

    $options = get_option('uw_banner_options', array());
    return wp_parse_args($options, $defaults);
}
