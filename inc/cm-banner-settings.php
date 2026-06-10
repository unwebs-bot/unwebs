<?php
/**
 * CM Banner — 메인페이지 상단 진행현황 배너
 *
 * 자동 생성 모드 (관리자 UI 없음)
 * - 주차 단위 시드(ISO 연+주차) 기반, 매주 월요일(UTC) 새 세트
 * - 제작진행: 10~20 랜덤
 * - 수정진행: 100~150 랜덤
 * - 정기 유지보수: 68 고정
 * - 진행가능 건수: 3/10 고정
 * - 쿼터 텍스트: YYYY년 M월 쿼터 자동
 * - 배너: 메인페이지(front-page)에서 항상 노출
 */

if (!defined('ABSPATH')) exit;

/**
 * 주차 단위 시드 (대시보드와 동일 패턴)
 */
function cm_banner_week_seed()
{
    $salt = function_exists('wp_salt') ? wp_salt('auth') : (defined('AUTH_KEY') ? AUTH_KEY : uniqid('', true));
    return crc32('banner-' . date('oW') . $salt);
}

/**
 * 배너 데이터 자동 생성
 *
 * - production_count: 메인 작업 진행 중 프로젝트 (배너 자체 시드)
 * - revision_count, maintenance_count: 유지보수 metrics SSOT 사용 → KPI·차트와 일치
 */
function uw_banner_get_data()
{
    mt_srand(cm_banner_week_seed());
    $production = mt_rand(10, 20);
    mt_srand();

    $m = function_exists('uw_maintenance_metrics') ? uw_maintenance_metrics() : array(
        'in_progress_revisions' => 0,
        'subscriptions'         => 0,
    );

    return array(
        'production_count'  => $production,
        'revision_count'    => (int) $m['in_progress_revisions'],
        'maintenance_count' => (int) $m['subscriptions'],
        'available_count'   => 3,
        'available_total'   => 10,
        'quarter_text'      => date_i18n('Y') . '년 ' . date_i18n('n') . '월 쿼터',
    );
}

/**
 * 메인페이지에서 body에 'has-status-banner' 클래스 추가
 * (CSS에서 헤더를 배너 높이만큼 아래로 밀어냄)
 */
add_filter('body_class', 'uw_banner_body_class');
function uw_banner_body_class($classes)
{
    if (is_front_page()) {
        $classes[] = 'has-status-banner';
    }
    return $classes;
}
