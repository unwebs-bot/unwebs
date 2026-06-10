<?php
/**
 * 고정 페이지 자동 생성 (1회성 seed)
 *
 * 사이트맵 기반 핵심 페이지를 DB에 생성. 이미 존재하면 skip.
 * 완료 후 option 플래그로 재실행 방지. 페이지 추가가 필요하면 버전 키를 올리거나 플래그 삭제.
 */

if (!defined('ABSPATH')) exit;

add_action('init', 'cm_seed_core_pages', 20);
function cm_seed_core_pages()
{
    if (get_option('cm_page_seed_v5') === 'done') return;

    // slug => 제목
    $pages = array(
        'service'           => '홈페이지 제작',
        'service-materials' => '준비자료',
        'service-process'   => '제작절차',
        'blog'              => '블로그',
        'inquiry-complete'  => '문의 접수 완료',   // 제작문의 접수 후 redirect 도착지 (page-inquiry-complete.php)
    );

    foreach ($pages as $slug => $title) {
        $existing = get_page_by_path($slug, OBJECT, 'page');

        if (!$existing) {
            $post_id = wp_insert_post(array(
                'post_type'    => 'page',
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_content' => '',
                'post_author'  => 1,
            ));
        } else {
            $post_id = $existing->ID;
        }

    }

    update_option('cm_page_seed_v5', 'done');
}

/**
 * 제작문의 폼(main-contact) → 완료 페이지 redirect 연결.
 *
 * 페이지 시드(priority 20)와 폼 시드(cm-inquiry-seed.php, priority 30) 이후(priority 40)에 실행.
 * 둘 다 준비됐을 때 한 번만 폼 메타를 redirect 모드로 설정한다.
 * (완료 페이지 도달 시 네이버·구글 전환 발화 — page-inquiry-complete.php)
 */
add_action('init', 'cm_wire_inquiry_complete_redirect', 40);
function cm_wire_inquiry_complete_redirect()
{
    if (get_option('cm_inquiry_complete_wired_v1') === 'done') return;

    $page    = get_page_by_path('inquiry-complete', OBJECT, 'page');
    $form_id = (int) get_option('cm_main_inquiry_form_id');

    // 아직 페이지나 폼이 준비되지 않았으면 다음 로드에 재시도
    if (!$page || !$form_id || !get_post($form_id)) return;

    update_post_meta($form_id, '_uw_inquiry_success_type', 'redirect');
    update_post_meta($form_id, '_uw_inquiry_success_page_id', $page->ID);

    update_option('cm_inquiry_complete_wired_v1', 'done');
}
