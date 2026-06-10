<?php
/**
 * CM Dashboard Data — 메인페이지 실시간 현황 대시보드 데이터 생성
 *
 * 프로젝트 현황 / 상담·견적 안내 현황 더미 데이터를 주차 시드로 생성.
 * 같은 주 동안 동일 결과, 월요일마다 새 세트.
 *
 * @package starter-theme
 */

if (!defined('ABSPATH')) exit;

/**
 * 상태에 따른 CSS modifier 클래스 반환
 */
function uw_dashboard_status_class($status)
{
    switch ($status) {
        case '접수중':   return 'main-dashboard-badge-receiving';
        case '상담완료':
        case '완료':     return 'main-dashboard-badge-completed';
        default:         return 'main-dashboard-badge-ongoing';
    }
}

/* ==========================================================================
   자동 생성 로직 (7일 단위 시드 — 매주 월요일 UTC 새 세트)
   규칙: 사용자 확정 (2026-04-21)
   - 왼쪽(프로젝트): tag 3종 ↔ 사이트 카테고리 1:1 매핑
       리뉴얼   ↔ 웹사이트 리뉴얼
       신규     ↔ 웹사이트 신규제작
       유지보수 ↔ 웹사이트 유지보수
     제목: {마스킹 기업명} - {사이트 카테고리}
     배지: 무조건 '진행중'
     날짜: 최근 20일 내 무작위
   - 오른쪽(상담): tag '상담문의' 고정
     제목: [{성}**]님이 {홈페이지 제작 | 유지보수} 문의가 접수되었습니다.
     배지: '접수중' / '상담완료' 중 랜덤
     날짜: 최근 20일 내 무작위
   ========================================================================== */

/**
 * 기업명 마스킹 (첫·마지막 글자만 노출, 나머지는 *)
 * 예: 주식회사언디자이닝 → 주*******닝
 */
function cm_dashboard_mask_name($name)
{
    $len = mb_strlen($name);
    if ($len <= 2) return $name;
    return mb_substr($name, 0, 1) . str_repeat('*', $len - 2) . mb_substr($name, -1);
}

/**
 * 한글 받침 유무 판별 — 주격 조사 '이/가' 자동 선택용
 */
function cm_dashboard_has_jongseong($char)
{
    if (!function_exists('mb_ord')) return false;
    $code = mb_ord($char, 'UTF-8');
    if ($code === false || $code < 0xAC00 || $code > 0xD7A3) return false;
    return (($code - 0xAC00) % 28) !== 0;
}

/**
 * 7일 단위 시드 (ISO 연도+주차 기반). 같은 주 동안 동일 결과, 월요일마다 새 세트.
 */
function cm_dashboard_week_seed()
{
    $salt = function_exists('wp_salt') ? wp_salt('auth') : (defined('AUTH_KEY') ? AUTH_KEY : uniqid('', true));
    return crc32('dashboard-' . date('oW') . $salt);
}

/**
 * 기업명 풀 — 허구 기업명. 필요 시 확장.
 */
function cm_dashboard_company_pool()
{
    return array(
        '주식회사언디자이닝', '네이처브릿지', '프라임클리닉', '블루웨일스튜디오',
        '한빛이엔씨', '온유컴퍼니', '로컬베이커리', '오아시스컨설팅',
        '미래건축사사무소', '그린하우스', '닥터스미스치과', '코어뷰티',
        '블리스케어', '피에스타디자인', '솔라리스에너지', '바르다푸드',
        '퀀텀리프', '데일리모먼트', '오로라랩', '리브리즈',
        '초록식탁', '청명한의원', '하루공방', '한결건설',
        '스카이블루코스메틱', '웰메이드스튜디오', '라온디앤씨', '온더테이블',
    );
}

/**
 * 성씨 풀 (한국 흔한 성씨).
 */
function cm_dashboard_surname_pool()
{
    return array('김','이','박','최','정','강','조','윤','장','임','한','오','서','신','권','황','안','송','전','홍');
}

/**
 * 프로젝트 현황 자동 생성 (왼쪽)
 */
function cm_dashboard_generate_projects($count = 10)
{
    $companies = cm_dashboard_company_pool();
    $type_map = array(
        '리뉴얼'   => '웹사이트 리뉴얼',
        '신규'     => '웹사이트 신규제작',
        '유지보수' => '웹사이트 유지보수',
    );
    $type_keys = array_keys($type_map);

    mt_srand(cm_dashboard_week_seed());

    // 기업명 중복 없이 셔플 후 상위 N개
    $shuffled = $companies;
    for ($i = count($shuffled) - 1; $i > 0; $i--) {
        $j = mt_rand(0, $i);
        $tmp = $shuffled[$i]; $shuffled[$i] = $shuffled[$j]; $shuffled[$j] = $tmp;
    }

    $items = array();
    for ($i = 0; $i < $count; $i++) {
        $company = $shuffled[$i % count($shuffled)];
        $type = $type_keys[mt_rand(0, count($type_keys) - 1)];
        $days_ago = mt_rand(0, 19);

        $site_cat = $type_map[$type];
        $particle = cm_dashboard_has_jongseong(mb_substr($site_cat, -1)) ? '이' : '가';

        $items[] = array(
            'type'   => $type,
            'title'  => '[' . cm_dashboard_mask_name($company) . ']의 ' . $site_cat . $particle . ' 진행중입니다.',
            'date'   => date('Y-m-d', strtotime('-' . $days_ago . ' days')),
            'status' => '진행중',
        );
    }

    mt_srand();
    return $items;
}

/**
 * 상담 및 견적 안내 현황 자동 생성 (오른쪽)
 */
function cm_dashboard_generate_consults($count = 10)
{
    $surnames = cm_dashboard_surname_pool();
    $messages = array(
        '홈페이지 제작 문의가 접수되었습니다.',
        '유지보수 문의가 접수되었습니다.',
    );

    // 프로젝트와 시드 다르게 (동일 패턴 방지)
    mt_srand(cm_dashboard_week_seed() ^ 0x5A5A5A5A);

    $items = array();
    for ($i = 0; $i < $count; $i++) {
        $surname = $surnames[mt_rand(0, count($surnames) - 1)];
        $message = $messages[mt_rand(0, count($messages) - 1)];

        // 날짜 분포: 약 30%는 최근(0~1일), 70%는 이전(2~19일)
        // → 상태는 날짜로 결정: 당일·어제 = 접수중, 그 외 = 상담완료
        if (mt_rand(0, 99) < 30) {
            $days_ago = mt_rand(0, 1);
        } else {
            $days_ago = mt_rand(2, 19);
        }
        $status = ($days_ago <= 1) ? '접수중' : '상담완료';

        $items[] = array(
            'type'   => '상담문의',
            'title'  => '[' . $surname . '**]님이 ' . $message,
            'date'   => date('Y-m-d', strtotime('-' . $days_ago . ' days')),
            'status' => $status,
        );
    }

    mt_srand();
    return $items;
}

/**
 * 메인페이지 대시보드 데이터 (자동 생성)
 */
function uw_dashboard_get_projects()
{
    return cm_dashboard_generate_projects();
}

function uw_dashboard_get_consults()
{
    return cm_dashboard_generate_consults();
}
