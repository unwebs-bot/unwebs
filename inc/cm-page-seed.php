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
    if (get_option('cm_page_seed_v4') === 'done') return;

    // slug => 제목
    $pages = array(
        'service'           => '홈페이지 제작',
        'service-materials' => '준비자료',
        'service-process'   => '제작절차',
        'blog'              => '블로그',
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

    update_option('cm_page_seed_v4', 'done');
}
