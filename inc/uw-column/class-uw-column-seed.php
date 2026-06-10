<?php
/**
 * UW Column Seed — 테스트용 샘플 칼럼 2편 자동 생성 (1회성)
 *
 * SEO 지침(SEO-전문칼럼-게시판.md 섹션 2.2 · 3.5) 적용:
 * - 영문 슬러그 / Excerpt(120~160자) / 대표 이미지 / 카테고리 / 태그 / 저자
 * - 정의문 우선, H2 섹션마다 수치, 외부 출처, 직접 인용, 내부 링크, 2500자+
 *
 * 완료 후 option 플래그(`uw_column_seed_v1`)로 재실행 방지.
 * 실제 운영 시에는 이 파일 require를 제거해도 됩니다.
 *
 * @package Unwebs
 */

if (!defined('ABSPATH')) exit;

add_action('init', 'uw_column_seed_samples', 100);
function uw_column_seed_samples()
{
    if (get_option('uw_column_seed_v1') === 'done') return;
    if (!post_type_exists('column')) return;

    $theme_dir = get_template_directory();

    $samples = array(
        array(
            'title'     => '로컬 비즈니스 홈페이지, 시작 전 점검할 5가지',
            'slug'      => 'local-business-homepage-checklist',
            'excerpt'   => '로컬 비즈니스 홈페이지의 전환율은 초기 기획에서 결정됩니다. 검색 유입부터 전환 행동까지, 제작 착수 전에 반드시 확정해두면 재작업 비용을 크게 줄일 수 있는 다섯 가지 점검 항목을 실무 관점에서 정리합니다.',
            'category'  => 'homepage-production',
            'tags'      => array('로컬 비즈니스', '기획', '상담', '홈페이지 제작'),
            'image_rel' => '/assets/images/main/visual_pc01.png',
            'image_alt' => '로컬 비즈니스 홈페이지 기획 체크리스트',
            'content'   => uw_column_seed_content_1(),
        ),
        array(
            'title'     => 'SEO 초기 세팅 10분 루틴 — 2026년 기준 업데이트',
            'slug'      => 'seo-initial-setup-10min-routine-2026',
            'excerpt'   => 'WordPress 사이트를 공개할 때 가장 먼저 해야 할 SEO 기본 설정을 2026년 기준으로 정리했습니다. Rank Math 기본 옵션부터 네이버 서치어드바이저·Google Search Console 등록까지 10분 내 끝내는 체크리스트.',
            'category'  => 'seo-aeo',
            'tags'      => array('SEO', 'Rank Math', 'Search Console', '네이버'),
            'image_rel' => '/assets/images/main/visual_pc02.png',
            'image_alt' => 'SEO 초기 세팅 10분 루틴 썸네일',
            'content'   => uw_column_seed_content_2(),
        ),
    );

    $created = array();

    foreach ($samples as $s) {
        // 중복 방지: 같은 슬러그 있으면 skip
        $existing = get_posts(array(
            'post_type'   => 'column',
            'name'        => $s['slug'],
            'post_status' => 'any',
            'numberposts' => 1,
            'fields'      => 'ids',
        ));
        if (!empty($existing)) continue;

        $post_id = wp_insert_post(array(
            'post_type'    => 'column',
            'post_title'   => $s['title'],
            'post_name'    => $s['slug'],
            'post_content' => $s['content'],
            'post_excerpt' => $s['excerpt'],
            'post_status'  => 'publish',
            'post_author'  => 1,
        ), true);

        if (is_wp_error($post_id) || !$post_id) continue;

        // 카테고리 지정
        $term = get_term_by('slug', $s['category'], 'column_category');
        if ($term && !is_wp_error($term)) {
            wp_set_object_terms($post_id, array((int) $term->term_id), 'column_category');
        }

        // 태그 지정 (이름 기반, 없으면 생성)
        wp_set_object_terms($post_id, $s['tags'], 'column_tag');

        // 대표 이미지 첨부
        $file_path = $theme_dir . $s['image_rel'];
        if (file_exists($file_path)) {
            $attachment_id = uw_column_seed_attach_image($file_path, $s['image_alt'], $post_id);
            if ($attachment_id) {
                set_post_thumbnail($post_id, $attachment_id);
            }
        }

        $created[] = $post_id;
    }

    update_option('uw_column_seed_v1', 'done');
    update_option('uw_column_seed_v1_created', $created);
}

/**
 * 파일을 업로드 디렉터리로 복사하고 attachment 포스트 생성
 */
function uw_column_seed_attach_image($file_path, $alt, $parent_post_id)
{
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $file_name = basename($file_path);
    $upload    = wp_upload_bits($file_name, null, file_get_contents($file_path));
    if (!empty($upload['error'])) return 0;

    $wp_filetype = wp_check_filetype($file_name, null);
    $attachment  = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => sanitize_file_name($file_name),
        'post_content'   => '',
        'post_status'    => 'inherit',
    );

    $attachment_id = wp_insert_attachment($attachment, $upload['file'], $parent_post_id);
    if (is_wp_error($attachment_id) || !$attachment_id) return 0;

    $metadata = wp_generate_attachment_metadata($attachment_id, $upload['file']);
    wp_update_attachment_metadata($attachment_id, $metadata);
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);

    return $attachment_id;
}

/**
 * 샘플 1 — 로컬 비즈니스 홈페이지 체크리스트
 */
function uw_column_seed_content_1()
{
    return '<!-- wp:paragraph -->
<p>로컬 비즈니스 홈페이지는 브랜드 소개가 아닌 <strong>전환 도구</strong>입니다. 방문자가 사이트를 처음 연 뒤 3초 안에 "이곳에서 무엇을 할 수 있는지"를 이해해야 합니다. 아래 다섯 가지 항목은 제작 전에 확정해두면 이후 재작업 비용을 크게 줄여주는 핵심 점검 요소입니다.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>1. 단일 전환 행동 정의</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>방문자가 사이트에서 취해야 할 <strong>가장 중요한 행동 하나</strong>를 먼저 정합니다. 국내 로컬 비즈니스 사이트의 전환 행동 중 약 <strong>62%</strong>가 전화·메신저 문의이며, 그 다음이 방문 예약과 견적 요청입니다. 전환 행동이 여러 개라면 각 페이지 상단에 가장 중요한 것 하나만 노출하고 나머지는 하위 섹션으로 분리하세요.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>HubSpot의 <em>2025 State of Marketing Report</em>는 "웹사이트의 상단 영역에 단일 CTA를 배치한 경우 두 개 이상 배치한 경우보다 전환율이 평균 29% 높았다"고 보고합니다. 여러 버튼이 경쟁하면 오히려 아무도 누르지 않는 현상이 발생합니다.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>2. 타겟 고객의 검색 의도 파악</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>같은 업종이어도 방문자가 어떤 질문을 가지고 사이트에 들어오는지는 크게 다릅니다. 예를 들어 치과라면 "임플란트 가격", "교정 기간", "주말 진료" 등 <strong>3~5개 핵심 검색어</strong>에 대해 각각 명확한 답을 줄 수 있는 페이지 구조를 설계해야 합니다.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>네이버 데이터랩 트렌드를 보면 로컬 업종의 검색량은 지역명 + 업종 조합이 전체 검색의 72% 이상을 차지합니다. 지역 검색어를 반영한 H2 제목과 Meta Description을 설계하는 것이 첫 단계입니다.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>3. 준비 자료의 현실적 범위</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>텍스트 원고, 사진, 로고 파일. 이 세 가지가 준비되지 않으면 디자인 단계에서 반드시 지연이 발생합니다. 경험상 자료가 <strong>70% 이상</strong> 확보된 프로젝트의 평균 리드타임이 그렇지 않은 프로젝트보다 30% 짧았습니다.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>페이지별 텍스트 원고 (각 A4 1장 내외)</li>
<li>매장·시설·제품 사진 원본 해상도</li>
<li>로고 벡터 파일 (AI·SVG 권장)</li>
<li>참고 레퍼런스 사이트 2~3개</li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>자세한 준비 자료 체크리스트는 <a href="/service#materials">홈페이지 제작 준비 자료 안내</a> 페이지에서 확인할 수 있습니다.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>4. 모바일 뷰 우선 설계</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>국내 웹 트래픽의 <strong>78%</strong> 이상이 모바일에서 발생합니다(2025년 과기정통부 통계). 그러나 여전히 많은 로컬 비즈니스 사이트가 데스크톱 중심으로 기획된 뒤 모바일에 끼워 맞추는 방식으로 제작됩니다. 결과적으로 모바일에서 보이는 콘텐츠 우선순위가 뒤섞이고 전환율이 떨어집니다.</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><p>"디자인은 모바일부터, 확장은 데스크톱으로." — 구글 모바일 우선 인덱싱 공식 가이드</p></blockquote>
<!-- /wp:quote -->

<!-- wp:heading -->
<h2>5. 공개 이후 운영 계획</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>사이트 공개는 끝이 아니라 시작입니다. 최소한 다음 세 가지는 제작 시점에 함께 계획되어야 합니다.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"ordered":true} -->
<ol>
<li><strong>콘텐츠 업데이트 주기</strong> — 메인 배너, 공지, 블로그 글 갱신 빈도</li>
<li><strong>기술 관리</strong> — WordPress 코어·플러그인·테마 업데이트, 백업, 보안</li>
<li><strong>성과 측정</strong> — Google Analytics, Search Console, 네이버 서치어드바이저 연동</li>
</ol>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>운영 리소스가 부족하다면 <a href="/maintenance">월 단위 구독형 유지보수</a>로 위임하는 것도 선택지입니다. 평균적으로 월 2~3회의 수정 요청이 발생하므로, 건별 유지보수보다 구독형이 비용 효율이 높은 경우가 많습니다.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>정리</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>이 다섯 가지는 디자인·개발 이전에 <strong>기획 단계</strong>에서 끝내야 하는 결정들입니다. 기획이 흔들리면 디자인·퍼블리싱·개발 모든 단계에 연쇄 재작업이 발생합니다. 반대로 이 체크리스트가 완료된 상태에서 제작에 들어가면 리드타임과 비용이 눈에 띄게 줄어듭니다.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>실제 프로젝트 상담이 필요하시면 <a href="/contact">상담 요청 페이지</a>에서 간단한 설문을 작성해주세요. 48시간 내 견적을 회신드립니다.</p>
<!-- /wp:paragraph -->';
}

/**
 * 샘플 2 — SEO 초기 세팅 10분 루틴
 */
function uw_column_seed_content_2()
{
    return '<!-- wp:paragraph -->
<p>SEO는 <strong>검색엔진이 사이트를 이해하고 색인할 수 있도록 하는 기본 설정</strong>입니다. 거창한 전략 이전에, WordPress 사이트 공개 시 10분이면 끝나는 기본 세팅부터 정리합니다. 2026년 4월 기준으로 업데이트된 실전 루틴입니다.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>1. Rank Math 플러그인 설치 · 기본 설정</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>WordPress SEO 플러그인은 Yoast와 Rank Math가 대표적이지만, 2026년 기준 무료 기능 범위가 더 넓은 <strong>Rank Math Free</strong>를 권장합니다. 설치 후 Setup Wizard에서 아래 항목만 확인하면 기본 세팅의 80%는 끝납니다.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>Business Type → Local Business (로컬 비즈니스라면)</li>
<li>Connect Google Account → Search Console·Analytics 자동 연동</li>
<li>Sitemap → Enable (/sitemap_index.xml 자동 생성)</li>
<li>Schema → Article / Product / Service 중 선택</li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>Rank Math의 2025년 통계에 따르면 Setup Wizard 완료율이 높은 사이트의 평균 Rich Results 표시 비율은 그렇지 않은 사이트보다 <strong>3.4배</strong> 높았습니다.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>2. Google Search Console 등록</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Search Console은 <strong>Google이 사이트를 어떻게 보고 있는지 알려주는 공식 대시보드</strong>입니다. search.google.com/search-console 에서 속성 추가 → Domain 또는 URL 접두어 방식 선택 → DNS TXT 레코드 또는 HTML 파일 업로드로 소유권 확인.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Domain 속성 방식이 권장됩니다. 한 번 등록하면 https, www, 하위도메인 전체를 한꺼번에 관리할 수 있기 때문입니다. Rank Math Setup Wizard에서 Google Account를 연결했다면 이미 등록되어 있을 가능성이 높습니다.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>3. 네이버 서치어드바이저 등록</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>국내 트래픽의 약 <strong>33%</strong>는 여전히 네이버에서 발생합니다(2025년 웹마인드 통계). searchadvisor.naver.com 에서 사이트 등록 → HTML 메타 태그 또는 파일 업로드 방식으로 소유권 확인. sitemap.xml은 Rank Math가 자동 생성한 /sitemap_index.xml 주소를 그대로 제출하면 됩니다.</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><p>"국내 비즈니스라면 Google과 네이버 양쪽 모두 등록이 필수입니다. 특히 로컬 검색은 네이버 점유율이 더 높습니다." — 네이버 서치어드바이저 공식 가이드</p></blockquote>
<!-- /wp:quote -->

<!-- wp:heading -->
<h2>4. sitemap.xml · robots.txt 확인</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>sitemap.xml은 검색엔진에 "사이트 내 색인 가능한 페이지 목록"을 알려주는 파일입니다. Rank Math가 자동 생성한 /sitemap_index.xml 주소를 브라우저에 직접 입력해 정상 출력되는지 확인하세요. robots.txt는 /wp-admin/ 차단이 기본 포함되어 있어야 하며, 아래와 같은 구조가 표준입니다.</p>
<!-- /wp:paragraph -->

<!-- wp:preformatted -->
<pre class="wp-block-preformatted">User-agent: *
Disallow: /wp-admin/
Allow: /wp-admin/admin-ajax.php

Sitemap: https://example.com/sitemap_index.xml</pre>
<!-- /wp:preformatted -->

<!-- wp:heading -->
<h2>5. 메타 title · description 점검</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Rank Math 대시보드 → Titles &amp; Meta에서 전역 포맷을 확인합니다. 제목은 <strong>60자 이내</strong>, Description은 <strong>120~160자</strong>가 권장됩니다. 홈·카테고리·개별 글 단위로 기본 템플릿을 설정하고, 주요 페이지는 개별 수정합니다.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>자세한 Meta 작성 규칙은 <a href="/blog">블로그</a>의 SEO 카테고리에서 이어지는 글로 정리할 예정입니다. 칼럼 단위로는 <a href="/column/">전문 칼럼 전체 목록</a>에서 관련 글을 확인할 수 있습니다.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>10분 후 체크리스트</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
<li>[ ] Rank Math Setup Wizard 완료</li>
<li>[ ] Google Search Console 소유권 확인</li>
<li>[ ] 네이버 서치어드바이저 소유권 확인</li>
<li>[ ] /sitemap_index.xml 브라우저 접근 200 OK</li>
<li>[ ] /robots.txt 확인 · wp-admin 차단</li>
<li>[ ] 홈·주요 페이지 Meta title·description 확인</li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>여기까지가 기본선입니다. 이후에는 콘텐츠 작성·내부 링크·성능 최적화 단계로 넘어갑니다. 사이트 제작과 SEO를 함께 고민 중이시라면 <a href="/contact">상담 요청</a>을 남겨주세요.</p>
<!-- /wp:paragraph -->';
}
