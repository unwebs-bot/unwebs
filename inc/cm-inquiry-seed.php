<?php
/**
 * 메인 페이지 문의 폼 자동 시드
 *
 * 기존 uw_inquiry CPT 위에 메인 전용 폼 1개를 자동 등록.
 * 등록 후 옵션 'cm_main_inquiry_form_id'에 form ID 저장 → section-contact.php에서 참조.
 * 관리자 GUI(전문 폼 관리)에서 라벨·필수·이메일 수신처 모두 편집 가능.
 *
 * v3: field_attachment 다중 첨부(multiple, max_files=3, max_filesize=10MB) 지원
 */

if (!defined('ABSPATH')) exit;

function cm_main_inquiry_fields()
{
    return array(
        array(
            'id' => 'field_project_type',
            'type' => 'radio',
            'label' => '프로젝트 유형',
            'required' => true,
            'placeholder' => '',
            'options' => "홈페이지 제작\n유지보수\n쇼핑몰\n시스템 개발",
            'help_text' => '',
            'enabled' => true,
            'order' => 1,
        ),
        array(
            'id' => 'field_company',
            'type' => 'text',
            'label' => '회사명',
            'required' => true,
            'placeholder' => '회사 또는 단체명을 입력해주세요',
            'help_text' => '',
            'enabled' => true,
            'order' => 2,
        ),
        array(
            'id' => 'field_name',
            'type' => 'text',
            'label' => '담당자 성함 / 직책',
            'required' => true,
            'placeholder' => '예: 홍길동 / 마케팅팀장',
            'help_text' => '',
            'enabled' => true,
            'order' => 3,
        ),
        array(
            'id' => 'field_phone',
            'type' => 'tel',
            'label' => '연락처',
            'required' => true,
            'placeholder' => '010-0000-0000',
            'help_text' => '',
            'enabled' => true,
            'order' => 4,
        ),
        array(
            'id' => 'field_email',
            'type' => 'email',
            'label' => '이메일',
            'required' => true,
            'placeholder' => 'example@company.com',
            'help_text' => '',
            'enabled' => true,
            'order' => 5,
        ),
        array(
            'id' => 'field_message',
            'type' => 'textarea',
            'label' => '문의 내용',
            'required' => true,
            'placeholder' => '원하시는 사이트 스타일·기능·참고 사이트 등을 자유롭게 적어주세요. 비워두셔도 전문 담당자가 상담을 통해 함께 정리해드립니다.',
            'help_text' => '',
            'enabled' => true,
            'order' => 6,
        ),
        array(
            'id' => 'field_grant',
            'type' => 'radio',
            'label' => '지원사업 여부',
            'required' => false,
            'placeholder' => '',
            'options' => "해당없음\n수출바우처\n혁신바우처\n기타",
            'help_text' => '',
            'enabled' => true,
            'order' => 7,
        ),
        array(
            'id' => 'field_budget',
            'type' => 'text',
            'label' => '예산',
            'required' => false,
            'placeholder' => '예: 1,000만원 / 미정',
            'help_text' => '',
            'enabled' => true,
            'order' => 8,
        ),
        array(
            'id' => 'field_attachment',
            'type' => 'file',
            'label' => '첨부파일',
            'required' => false,
            'placeholder' => '관련 자료가 있다면 첨부해주세요 (최대 3개, 파일당 10MB)',
            'help_text' => '',
            'enabled' => true,
            'order' => 9,
            'multiple' => true,
            'max_files' => 3,
            'max_filesize' => 10485760,
        ),
    );
}

add_action('init', 'cm_seed_main_inquiry_form', 30);
function cm_seed_main_inquiry_form()
{
    if (!post_type_exists('uw_inquiry_form')) return;

    $existing_id = (int) get_option('cm_main_inquiry_form_id');

    // 기존 폼이 있으면 v3로 필드 정의 갱신만
    if ($existing_id && get_post($existing_id)) {
        if (get_option('cm_inquiry_seed_main_v3') !== 'done') {
            update_post_meta($existing_id, '_uw_inquiry_fields', cm_main_inquiry_fields());
            update_option('cm_inquiry_seed_main_v3', 'done');
        }
        return;
    }

    // 신규 생성
    $form_id = wp_insert_post(array(
        'post_type'    => 'uw_inquiry_form',
        'post_title'   => '메인 페이지 문의',
        'post_name'    => 'main-contact',
        'post_status'  => 'publish',
        'post_author'  => 1,
    ), true);

    if (is_wp_error($form_id) || !$form_id) return;

    update_post_meta($form_id, '_uw_inquiry_fields', cm_main_inquiry_fields());
    update_post_meta($form_id, '_uw_inquiry_notify_emails', 'contact@unwebs.co.kr');

    update_option('cm_main_inquiry_form_id', $form_id);
    update_option('cm_inquiry_seed_main_v2', 'done');
    update_option('cm_inquiry_seed_main_v3', 'done');
}
