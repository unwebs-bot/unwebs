<?php
/**
 * 대시보드 샘플 데이터 시드 (일회성 실행)
 * 관리자 페이지 접속 시 데이터가 비어있으면 자동 삽입
 */

if (!defined('ABSPATH')) exit;

add_action('admin_init', 'uw_dashboard_seed_data');
function uw_dashboard_seed_data()
{
    // 이미 데이터가 있으면 스킵
    $projects = get_option('uw_dashboard_projects');
    $consults = get_option('uw_dashboard_consults');

    if (!empty($projects) || !empty($consults)) return;

    $project_seed = array(
        array('type' => '리뉴얼', 'title' => '제***움 반응형 홈페이지 제작',       'date' => '2026-02-04', 'status' => '진행중'),
        array('type' => '신규',   'title' => '아***텍 기업 홍보 사이트',             'date' => '2026-02-03', 'status' => '진행중'),
        array('type' => '신규',   'title' => '법무법인 ** 파트너스 웹 구축',         'date' => '2026-02-02', 'status' => '진행중'),
        array('type' => '리뉴얼', 'title' => '더***스 쇼핑몰 리뉴얼',               'date' => '2026-02-01', 'status' => '완료'),
        array('type' => '유지보수','title' => '한***업 연간 유지보수',                'date' => '2026-01-30', 'status' => '진행중'),
        array('type' => '신규',   'title' => '주***회 협회 홈페이지 구축',           'date' => '2026-01-28', 'status' => '완료'),
        array('type' => '리뉴얼', 'title' => '에***지 브랜드 사이트 리뉴얼',         'date' => '2026-01-25', 'status' => '진행중'),
        array('type' => '신규',   'title' => '글***벌 무역회사 웹사이트',             'date' => '2026-01-22', 'status' => '완료'),
    );

    $consult_seed = array(
        array('type' => '견적의뢰', 'title' => '[최**]님이 제작 상담을 문의했습니다.',   'date' => '2026-02-04', 'status' => '접수중'),
        array('type' => '견적의뢰', 'title' => '[김**]님이 견적서를 요청했습니다.',       'date' => '2026-02-04', 'status' => '상담완료'),
        array('type' => '견적의뢰', 'title' => '[이**]님이 포트폴리오를 요청했습니다.',   'date' => '2026-02-03', 'status' => '상담완료'),
        array('type' => '문의',     'title' => '[박**]님이 유지보수 문의를 남겼습니다.',   'date' => '2026-02-03', 'status' => '접수중'),
        array('type' => '견적의뢰', 'title' => '[정**]님이 제작 상담을 문의했습니다.',     'date' => '2026-02-02', 'status' => '상담완료'),
        array('type' => '문의',     'title' => '[송**]님이 홈페이지 제작을 문의했습니다.', 'date' => '2026-02-01', 'status' => '접수중'),
        array('type' => '견적의뢰', 'title' => '[한**]님이 쇼핑몰 견적을 요청했습니다.',   'date' => '2026-01-30', 'status' => '상담완료'),
        array('type' => '견적의뢰', 'title' => '[윤**]님이 리뉴얼 상담을 문의했습니다.',   'date' => '2026-01-28', 'status' => '상담완료'),
    );

    update_option('uw_dashboard_projects', $project_seed);
    update_option('uw_dashboard_consults', $consult_seed);
}
