<?php
/**
 * 공지사항 시드 — notice term + board admin 설정 + 샘플 공지 1개 (1회성)
 *
 * - uw_board_type taxonomy에 'notice' term 등록
 * - uw_board_settings 옵션에 notice 보드 등록 → wp-admin 메뉴 자동 노출
 * - 샘플 공지글 1개 자동 삽입 (공지 고정 ON)
 * - rewrite rule flush (/notice/ URL 즉시 작동)
 * - 옵션 플래그로 재실행 방지
 */

if (!defined('ABSPATH')) exit;

add_action('init', 'cm_seed_notice', 30);
function cm_seed_notice()
{
    if (get_option('cm_notice_seed_v2') === 'done') return;

    // 1) notice term 보장
    if (!term_exists('notice', 'uw_board_type')) {
        wp_insert_term('공지사항', 'uw_board_type', array('slug' => 'notice'));
    }

    // 2) board admin 설정에 notice 보드 등록 → wp-admin 메뉴 자동 노출
    $boards = get_option('uw_board_settings', array());
    if (!isset($boards['notice'])) {
        $boards['notice'] = array(
            'name'             => '공지사항',
            'skin'             => 'style01',
            'per_page'         => 10,
            'read_permission'  => 'all',
            'write_permission' => 'logged_in', // 비회원 글쓰기 차단(공지는 관리자만)
        );
        update_option('uw_board_settings', $boards);
    }

    // 3) 샘플 공지 — 기존 공지글이 하나도 없을 때만 삽입
    $existing = get_posts(array(
        'post_type'      => 'uw_board',
        'posts_per_page' => 1,
        'post_status'    => 'any',
        'tax_query'      => array(
            array(
                'taxonomy' => 'uw_board_type',
                'field'    => 'slug',
                'terms'    => 'notice',
            ),
        ),
        'fields'         => 'ids',
    ));

    if (empty($existing)) {
        $post_id = wp_insert_post(array(
            'post_type'    => 'uw_board',
            'post_status'  => 'publish',
            'post_title'   => '언웹스 공지사항 페이지가 오픈되었습니다.',
            'post_name'    => 'unwebs-notice-open',
            'post_content' => "<p>안녕하세요, 언웹스입니다.</p>\n<p>공지사항 페이지를 새롭게 오픈했습니다. 앞으로 서비스 업데이트·점검 안내·이벤트 등 중요한 내용을 이 곳에서 공유드릴 예정입니다.</p>\n<p>문의 사항은 고객지원 채널로 연락 주시기 바랍니다.</p>",
            'post_author'  => 1,
        ));

        if ($post_id && !is_wp_error($post_id)) {
            wp_set_object_terms($post_id, 'notice', 'uw_board_type');
            update_post_meta($post_id, '_uw_is_pinned', '1');
        }
    }

    // 4) rewrite rule flush — /notice/ URL 즉시 작동
    flush_rewrite_rules(false);

    update_option('cm_notice_seed_v2', 'done');
}
