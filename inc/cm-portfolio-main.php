<?php
/**
 * Main Portfolio — 메인 캐러셀 + 자체 single 페이지 + ItemList JSON-LD 단일 SSOT
 *
 * 정책 (2026-04-30 변경):
 *  - CPT 미사용. 100% 코드 큐레이션 (대기업·가치 입증된 브랜드만 6~8개).
 *  - 자체 single URL: `/portfolio/{slug}/` — functions.php의 rewrite rule이 처리.
 *  - 외부 클라이언트 사이트는 single 안에서 "사이트 방문" 버튼으로 별도 노출.
 *  - 두 사용처가 같은 함수 호출 → 메인 카드와 ItemList JSON-LD 자동 일치.
 *      · `template-parts/main/section-portfolio.php` (메인 섹션 카드)
 *      · `functions.php` → `uw_main_portfolio_itemlist()` (ItemList JSON-LD)
 *      · `page-portfolio-case.php` → 단일 항목 조회 (`cm_get_portfolio_by_slug()`)
 *
 * 항목 추가/수정:
 *  - 아래 배열을 직접 편집. 신규 항목 추가 후 WP 어드민 → 설정 → 고유주소 저장 1회 (rewrite flush).
 *  - slug         : URL 식별자 (영문 소문자 + 하이픈). 변경 시 SEO 영향 큼
 *  - title        : 회사·브랜드 명
 *  - industry     : 카드 첫 번째 뱃지 (업종)
 *  - type         : 카드 두 번째 뱃지 (서비스명)
 *  - image        : 카드 PC 썸네일(풀페이지 캡처, 호버 시 세로 스크롤). 절대 URL 또는 사이트 루트 기준 경로
 *  - image_mob    : 카드 우하단 모바일 목업 이미지(모바일 풀페이지 캡처). 비어두면 placeholder
 *  - popup_image  : 팝업 풀페이지 캡처. 비어두면 image(PC 썸네일)를 그대로 사용 — 보통 비워둠
 *  - web_type     : 팝업 TYPE 행 (예: 반응형)
 *  - option       : 팝업 OPTION 행 (예: 페이지추가, 기능개발추가)
 *  - price        : 팝업 PRICE 행 (예: 반응형 홈페이지제작 + 페이지추가 비용)
 *  - tags         : 팝업 태그 배열 (예: ['반응형','의료','연세대'] — # 없이 텍스트만)
 *  - external_url : 클라이언트 라이브 사이트 (팝업 "사이트 바로가기" + single "사이트 방문"). 비어두면 버튼 미노출
 *  - description  : single 페이지·ItemList description. 카피 중립 — 포지셔닝 노출 X
 *  - hero_intro   : single 페이지 hero 아래 1~2줄 인트로 (선택)
 */

if (!defined('ABSPATH')) exit;

if (!function_exists('cm_portfolio_dataset')) {
    function cm_portfolio_dataset()
    {
        $base    = '/wp-content/themes/Unwebs/assets/images/main/projects/';
        $fs_base = get_template_directory() . '/assets/images/main/projects/';

        // [slug, title, industry, type, tags[], external_url, img_ext]
        //  img_ext 'jpg' = 기존 자동캡처(썸네일=팝업 동일), 'webp' = 사용자 원본 가공본(thumb/pc/mob 분리)
        $rows = array(
            array('christian-mate',   '크리스천메이트',       '기업/기관',   '리뉴얼',   array('기업/기관', '서비스', '리뉴얼'),   'https://c-mate.co.kr/',                'webp'),
            array('cheonsang-tax',    '세무회계 천상',         '기업/기관',   '신규제작', array('기업/기관', '금융', '신규제작'),   'http://www.cheonsangtax.co.kr/',       'webp'),
            array('st-works',         '에스티웍스',           '기업/기관',   '리뉴얼',   array('기업/기관', 'IT', '리뉴얼'),       'https://master11377.imweb.me/',        'webp'),
            array('h-plus',           '에이치플러스',         '건강/의학',   '신규제작', array('건강/의학', '병원', '신규제작'),   'https://hplusmedicalcenter.imweb.me/', 'webp'),
            array('a-frame',          '에이프레임',           '기업/기관',   '신규제작', array('기업/기관', '미디어', '신규제작'), 'https://a-frame.kr/',                  'webp'),
            array('poedit',           '포에디트',             '기업/기관',   '리뉴얼',   array('기업/기관', '디자인', '리뉴얼'),   'https://poedit.co.kr/',                'webp'),
            array('luwon-smile',      '루원스마일안과',       '건강/의학',   '리뉴얼',   array('건강/의학', '병원', '리뉴얼'),     'https://lu1smile.co.kr/',              'webp'),
            array('samsung-pyeonhan', '삼성편한내과',         '건강/의학',   '신규제작', array('건강/의학', '병원', '신규제작'),   'https://smclinic.imweb.me/',           'webp'),
            array('solm-academy',     '솔므 홈케어 아카데미', '교육',       '리뉴얼',   array('교육', '리뉴얼'),                 'https://solmm.co.kr/',                 'webp'),
            array('jocoding',         '조코딩넷',             '브랜드/개인', '리뉴얼',   array('브랜드/개인', '리뉴얼'),           'https://jocoding.net/',                'webp'),
            array('doori-network',    '두리네트워크',         '기업/기관',   '리뉴얼',   array('기업/기관', '리뉴얼'),             'https://doorinetwork.imweb.me/',       'webp'),
            array('cch-edu',          '축최합에듀',           '브랜드/개인', '리뉴얼',   array('브랜드/개인', '리뉴얼'),           'https://www.cchedu.com/',              'webp'),
            array('ibma',             '국제바디무브먼트협회', '협회',       '신규제작', array('협회', '신규제작'),               'https://ibma.imweb.me/',               'webp'),
            array('a-rev',            '에이레브',             '브랜드',     '신규제작', array('브랜드', '신규제작'),             'https://bill74962.imweb.me/main',      'webp'),
            array('brain-cc',         '브레인척척',           '건강/의학',   '신규제작', array('건강/의학', '신규제작'),           'https://brainccnr0760286.imweb.me/',   'webp'),
            array('deokso-top',       '덕소탑내과의원',       '건강/의학',   '신규제작', array('건강/의학', '신규제작'),           'https://deoksotop.imweb.me/',          'webp'),
            array('onulhome',         'ONULHOME',             '브랜드',     '신규제작', array('브랜드', '신규제작'),             'https://onulhome.imweb.me/',           'webp'),
            array('anad-beomeo',      '어나드범어',           '브랜드',     '신규제작', array('브랜드', '신규제작'),             'https://lkt485762560.imweb.me/',       'webp'),
            array('coa-ent',          '코아 이비인후과',      '건강/의학',   '신규제작', array('건강/의학', '신규제작'),           'https://coa-ent01.imweb.me/',          'webp'),
            array('easy-ent',         '편한이비인후과',       '건강/의학',   '신규제작', array('건강/의학', '병원', '신규제작'),   'https://easyent.imweb.me/',            'webp'),
            array('tasty-jokbal',     '더맛있는족발보쌈',     '프랜차이즈', '리뉴얼',   array('프랜차이즈', '리뉴얼'),            'https://xn--sh1b6nw5n8pcxvw8ib.kr/',   'webp'),
            array('never-designing',  '네버디자이닝',         '브랜드',     '리뉴얼',   array('브랜드', '디자인', '리뉴얼'),       'https://neverdesigning.com/',          'webp'),
            array('symposium',        '해외자원심포지엄',     '공공기관',   '리뉴얼',   array('공공기관', '협회', '리뉴얼'),       'https://symposium1.imweb.me/',         'webp'),
            array('unsense',          '언뜻마케팅',           '기업/기관',   '리뉴얼',   array('기업/기관', '서비스', '리뉴얼'),   'https://unsense.co.kr/',               'webp'),
            array('sns-genie',        'SNS지니',              '랜딩페이지', '신규제작', array('랜딩페이지', '서비스', '신규제작'), 'https://snsgini.imweb.me/',            'webp'),
            array('kings-realty',     '(주)킹스리얼티',       '기업/기관',   '신규제작', array('기업/기관', '신규제작'),           'https://kings-realty.imweb.me/',       'webp'),
            array('baekdudaegan',     '백두대간보전회',       '협회',       '리뉴얼',   array('협회', '리뉴얼'),                 'http://www.baekdudaegan.or.kr/index.php', 'webp'),
            array('mania-electronics','매니아전자',           '기업/기관',   '신규제작', array('기업/기관', 'IT', '신규제작'),     'https://maniaelectronics.com/',        'webp'),
            array('neulpureun',       '늘푸른내과',           '건강/의학',   '신규제작', array('건강/의학', '병원', '신규제작'),   'https://greenkedney.imweb.me/',        'webp'),
            array('danggeun-mkt',     '당근마케팅',           '기업/기관',   '신규제작', array('기업/기관', '서비스', '신규제작'), 'https://danggeunmkt.imweb.me/',        'webp'),
            array('ieozoom',          '이어줌디자인',         '브랜드',     '신규제작', array('브랜드', '신규제작'),             'https://ieozoom.com/',                 'webp'),
            array('lumi-obgy',        '루미산부인과',         '건강/의학',   '신규제작', array('건강/의학', '병원', '신규제작'),   'http://www.lumiobgy.com/',             'webp'),
        );

        $out = array();
        foreach ($rows as $r) {
            list($slug, $title, $industry, $type, $tags, $url) = $r;
            $ext = isset($r[6]) ? $r[6] : 'webp';
            // 네이버 캐러셀(ItemList)용 가로형 1200×630 이미지. 파일 있으면 사용, 없으면 빈값(=썸네일 fallback).
            $card = file_exists($fs_base . $slug . '-card.webp') ? ($base . $slug . '-card.webp') : '';
            if ($ext === 'jpg') {
                // 기존 자동캡처: 썸네일=팝업 동일(popup_image 비우면 image로 fallback)
                $image = $base . $slug . '-pc.jpg';
                $mob   = $base . $slug . '-mob.jpg';
                $popup = '';
            } elseif ($ext === 'mixed') {
                // PC만 구 자동캡처 jpg(고해상도), 모바일은 새 webp
                $image = $base . $slug . '-pc.jpg';
                $mob   = $base . $slug . '-mob.webp';
                $popup = '';
            } else {
                // 사용자 원본 가공: 카드 썸네일(적당히 크롭) / 팝업 풀 이미지 분리
                $image = $base . $slug . '-thumb.webp';
                $mob   = $base . $slug . '-mob.webp';
                $popup = $base . $slug . '-pc.webp';
            }
            // [테스트] 팝업 내 모바일 PiP — 우선 크리스천메이트만 (OK면 조건 제거해 전체 적용)
            $popup_mobile = ($slug === 'christian-mate') ? $mob : '';
            // 팝업 PC 이미지 갤러리(순서대로 노출). 기본 1장, 특정 항목만 여러 장.
            //  - tasty-jokbal: pc1(thumb) + pc2(pc) 두 장
            if ($slug === 'tasty-jokbal') {
                $popup_gallery = array($image, $popup); // pc1=thumb, pc2=pc
            } else {
                $base_popup    = ($popup !== '') ? $popup : $image;
                $popup_gallery = ($base_popup !== '') ? array($base_popup) : array();
            }
            $out[] = array(
                'slug'         => $slug,
                'title'        => $title,
                'industry'     => $industry,
                'type'         => $type,
                'image'        => $image,
                'card_image'   => $card,
                'image_mob'    => $mob,
                'popup_image'  => $popup,
                'popup_mobile' => $popup_mobile,
                'popup_gallery'=> $popup_gallery,
                'web_type'     => '반응형',
                'option'       => '',
                'price'        => '',
                'tags'         => $tags,
                'external_url' => $url,
                'description'  => '',
                'hero_intro'   => '',
            );
        }

        // 관리자 지정 순서 적용 (wp-admin '포트폴리오 순서'). slug 배열로 재정렬.
        // 옵션 없으면 코드 순서 그대로. order에 없는 신규 항목은 뒤에 붙임.
        $order = get_option('uw_portfolio_order', array());
        if (!empty($order) && is_array($order)) {
            $by_slug = array();
            foreach ($out as $it) {
                $by_slug[$it['slug']] = $it;
            }
            $ordered = array();
            foreach ($order as $slug) {
                if (isset($by_slug[$slug])) {
                    $ordered[] = $by_slug[$slug];
                    unset($by_slug[$slug]);
                }
            }
            foreach ($by_slug as $it) {
                $ordered[] = $it;
            }
            $out = $ordered;
        }

        return $out;
    }
}

if (!function_exists('cm_get_main_portfolio_items')) {
    /**
     * 메인 노출용 포트폴리오 항목 (슬러그 → 자체 single URL 자동 생성).
     *
     * @param int $limit 최대 항목 수 (기본 8)
     * @return array — section-portfolio.php·ItemList가 사용하는 정형화된 형태
     */
    function cm_get_main_portfolio_items($limit = 8)
    {
        $dataset = cm_portfolio_dataset();
        $items   = array();
        foreach ($dataset as $row) {
            if (empty($row['slug'])) continue;
            $items[] = array(
                'slug'        => $row['slug'],
                'title'       => $row['title'] ?? '',
                'industry'    => $row['industry'] ?? '',
                'type'        => $row['type'] ?? '',
                'image'       => $row['image'] ?? '',
                'card_image'  => $row['card_image'] ?? '',
                'image_mob'   => $row['image_mob'] ?? '',
                'popup_image' => $row['popup_image'] ?? '',
                'popup_mobile'=> $row['popup_mobile'] ?? '',
                'popup_gallery'=> (isset($row['popup_gallery']) && is_array($row['popup_gallery'])) ? $row['popup_gallery'] : array(),
                'web_type'    => $row['web_type'] ?? '',
                'option'      => $row['option'] ?? '',
                'price'       => $row['price'] ?? '',
                'tags'        => isset($row['tags']) && is_array($row['tags']) ? $row['tags'] : array(),
                'external_url'=> $row['external_url'] ?? '',
                'url'         => home_url('/portfolio/' . $row['slug'] . '/'), // 자체 single (SEO/직접접근 유지)
                'description' => $row['description'] ?? '',
            );
        }
        return array_slice($items, 0, max(1, (int) $limit));
    }
}

if (!function_exists('cm_get_portfolio_by_slug')) {
    /**
     * 단일 항목 조회 (single 페이지에서 사용).
     */
    function cm_get_portfolio_by_slug($slug)
    {
        $slug = (string) $slug;
        foreach (cm_portfolio_dataset() as $row) {
            if (!empty($row['slug']) && $row['slug'] === $slug) {
                return $row;
            }
        }
        return null;
    }
}
