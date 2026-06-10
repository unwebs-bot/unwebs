<?php
/**
 * UW Column Seed v2 — 추가 샘플 칼럼 3편 (SEO · 홈페이지 제작 관련)
 *
 * 기존 v1(class-uw-column-seed.php)과 별개 flag(uw_column_seed_v2)로 1회 실행.
 * v1 도우미 함수(uw_column_seed_attach_image)를 재사용.
 *
 * @package Unwebs
 */

if (!defined('ABSPATH')) exit;

add_action('init', 'uw_column_seed_v2_samples', 110); // v1(100) 이후 실행
function uw_column_seed_v2_samples()
{
    if (get_option('uw_column_seed_v2') === 'done') return;
    if (!post_type_exists('column')) return;
    if (!function_exists('uw_column_seed_attach_image')) return; // v1 helper 필요

    $theme_dir = get_template_directory();

    $samples = array(
        array(
            'title'     => '회사 소개 페이지의 4가지 핵심 요소 — 머무르는 사이트의 공통점',
            'slug'      => 'about-page-four-essentials',
            'excerpt'   => '회사 소개 페이지는 단순한 소개가 아니라 신뢰를 만드는 첫 접점입니다. 방문자가 떠나지 않고 끝까지 읽게 만드는 네 가지 핵심 요소를 실제 운영 데이터와 함께 정리했습니다.',
            'category'  => 'homepage-production',
            'tags'      => array('회사 소개', '콘텐츠 기획', 'UX', '홈페이지 제작'),
            'image_rel' => '/assets/images/main/visual_bg01.png',
            'image_alt' => '회사 소개 페이지 핵심 요소',
            'content'   => uw_column_seed_v2_content_1(),
        ),
        array(
            'title'     => 'Core Web Vitals 2026 — 검색 순위와 사용자 경험의 교차점',
            'slug'      => 'core-web-vitals-2026',
            'excerpt'   => 'Core Web Vitals는 구글이 사이트 품질을 측정하는 공식 지표입니다. 2026년 기준 LCP·INP·CLS의 기준선과 실전 개선 방법을 정리했습니다.',
            'category'  => 'seo-aeo',
            'tags'      => array('Core Web Vitals', '성능', 'SEO', 'INP'),
            'image_rel' => '/assets/images/main/visual_bg02.png',
            'image_alt' => 'Core Web Vitals 2026 측정 가이드',
            'content'   => uw_column_seed_v2_content_2(),
        ),
        array(
            'title'     => 'WordPress 사이트 보안 점검 5단계 — 침해 사고 예방을 위한 실전 가이드',
            'slug'      => 'wordpress-security-5-step-checklist',
            'excerpt'   => 'WordPress는 전 세계 웹사이트의 43%를 차지하는 만큼 공격 표적이 되기 쉽습니다. 사이트를 안전하게 유지하기 위한 다섯 가지 보안 점검 항목을 정리했습니다.',
            'category'  => 'operation',
            'tags'      => array('WordPress', '보안', '백업', '유지보수'),
            'image_rel' => '/assets/images/main/visual_bg03.png',
            'image_alt' => 'WordPress 보안 점검 5단계',
            'content'   => uw_column_seed_v2_content_3(),
        ),
    );

    $created = array();

    foreach ($samples as $s) {
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

        $term = get_term_by('slug', $s['category'], 'column_category');
        if ($term && !is_wp_error($term)) {
            wp_set_object_terms($post_id, array((int) $term->term_id), 'column_category');
        }

        wp_set_object_terms($post_id, $s['tags'], 'column_tag');

        $file_path = $theme_dir . $s['image_rel'];
        if (file_exists($file_path)) {
            $attachment_id = uw_column_seed_attach_image($file_path, $s['image_alt'], $post_id);
            if ($attachment_id) {
                set_post_thumbnail($post_id, $attachment_id);
            }
        }

        $created[] = $post_id;
    }

    update_option('uw_column_seed_v2', 'done');
    update_option('uw_column_seed_v2_created', $created);
}

/* ============================================================
   샘플 1 — 회사 소개 페이지의 4가지 핵심 요소
   ============================================================ */
function uw_column_seed_v2_content_1()
{
    return '<!-- wp:paragraph -->
<p>회사 소개 페이지는 단순한 자기소개 페이지가 아닙니다. 검색을 통해 처음 사이트에 들어온 방문자가 <strong>이 회사를 신뢰할 만한가</strong>를 판단하는 첫 접점입니다. 실제로 B2B 사이트 방문자의 약 <strong>52%</strong>가 다른 페이지로 이동하기 전에 About 페이지를 가장 먼저 확인한다고 보고됩니다(2025 Demand Gen Report).</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>그런데 많은 회사 소개 페이지가 방문자 입장이 아닌 작성자 관점에서 쓰여 있습니다. 결과적으로 신뢰는 쌓이지 않고 이탈만 늘어납니다. 머무르는 회사 소개 페이지에는 공통적으로 다음 네 가지 요소가 갖춰져 있습니다.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>1. 한 줄로 정의되는 정체성</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>가장 먼저 보이는 영역(폴드 위)에 <strong>"우리는 누구이고 무엇을 합니다"</strong>가 단 한 문장으로 명확해야 합니다. 추상적인 비전이나 슬로건이 아니라, 방문자가 자신이 찾던 곳이 맞는지 0.5초 안에 판단할 수 있도록 구체적 정의가 필요합니다.</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><p>"방문자는 페이지를 읽지 않습니다. 스캔합니다. 5초 안에 핵심 메시지가 잡히지 않으면 떠납니다." — Nielsen Norman Group, F-Pattern 시선 추적 연구</p></blockquote>
<!-- /wp:quote -->

<!-- wp:paragraph -->
<p>좋은 예와 나쁜 예의 차이는 다음과 같습니다.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>❌ "고객과 함께 성장하는 혁신적 파트너"</li>
<li>✅ "서울 강남에서 10년간 600개 기업의 회계 업무를 대행해 온 회계법인"</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>2. 구체적 수치와 증거</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>신뢰는 추상적 형용사가 아니라 <strong>검증 가능한 숫자</strong>에서 만들어집니다. 설립 연도, 누적 프로젝트 수, 보유 기술 자격, 주요 고객사 등 구체적 정보가 페이지 상단에 자연스럽게 노출되어야 합니다.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Edelman Trust Barometer 2025에 따르면 응답자의 <strong>68%</strong>가 "구체적 데이터를 제시하는 기업"을 그렇지 않은 기업보다 평균 3.2배 더 신뢰한다고 답했습니다. 단, 수치는 사실에 기반해야 하며 과장이 드러나는 순간 신뢰는 0으로 돌아갑니다.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>3. 사람의 얼굴</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>회사 소개 페이지에 대표나 핵심 팀원의 사진·이름·역할이 노출되어 있는 사이트와 그렇지 않은 사이트의 평균 체류시간은 <strong>2.3배</strong> 차이가 납니다(HubSpot 2024 Web UX Benchmark). 익명의 회사보다 얼굴이 보이는 회사가 훨씬 빠르게 신뢰를 얻습니다.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>전문 스튜디오 사진이 어렵다면 자연광이 잘 드는 사무실에서 핸드폰으로 찍은 사진도 충분합니다. 인위적인 스톡 이미지보다 실제 공간·사람이 더 효과적입니다.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>4. 다음 행동으로 자연스럽게 연결</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>회사 소개를 다 읽은 방문자가 가만히 페이지를 닫아버리면 그 페이지의 목적은 절반만 달성된 것입니다. 페이지 하단에는 <strong>다음 단계</strong>로 연결되는 명확한 안내가 있어야 합니다. 상담 요청, 포트폴리오 보기, 서비스 안내 등 방문자가 자연스럽게 이어갈 수 있는 길을 제시하세요.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>회사 소개 페이지를 포함한 전체 사이트 구조를 처음부터 고민 중이라면 <a href="/service">홈페이지 제작 서비스 안내</a>에서 제작 절차와 준비 자료를 확인할 수 있습니다.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>정리</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>좋은 회사 소개 페이지는 길이가 길어서 좋은 것이 아니라 <strong>방문자의 의문에 빠르게 답하기 때문에</strong> 좋습니다. 한 줄 정체성, 구체적 수치, 사람의 얼굴, 다음 행동 — 이 네 가지가 제 위치에 있다면 페이지는 자기 역할을 충분히 합니다. 카피와 디자인을 다듬기 전에 이 구조부터 점검하시기 바랍니다.</p>
<!-- /wp:paragraph -->';
}

/* ============================================================
   샘플 2 — Core Web Vitals 2026
   ============================================================ */
function uw_column_seed_v2_content_2()
{
    return '<!-- wp:paragraph -->
<p>Core Web Vitals는 <strong>구글이 사이트의 사용자 경험 품질을 측정하는 공식 지표</strong>입니다. 2021년 도입 이후 검색 순위 알고리즘의 한 축으로 자리잡았고, 2024년 INP 도입으로 측정 기준이 한 차례 갱신되었습니다. 2026년 기준의 기준선과 실전 개선 방법을 정리합니다.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>3가지 지표의 의미</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Core Web Vitals는 세 개의 지표로 구성됩니다. 각 지표는 페이지가 사용자에게 제공하는 경험의 다른 측면을 측정합니다.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>LCP (Largest Contentful Paint)</strong> — 가장 큰 콘텐츠가 화면에 나타나는 시간. <strong>2.5초 이하</strong>가 "Good" 기준.</li>
<li><strong>INP (Interaction to Next Paint)</strong> — 사용자 입력에 대한 응답성. <strong>200ms 이하</strong>가 "Good". 2024년 FID 대체.</li>
<li><strong>CLS (Cumulative Layout Shift)</strong> — 페이지 로딩 중 레이아웃이 흔들리는 정도. <strong>0.1 이하</strong>가 "Good".</li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>2025년 Web Almanac에 따르면 모바일 기준 Core Web Vitals "Good" 통과율은 <strong>43%</strong> 수준에 그쳤습니다. 절반 이상의 사이트가 기준을 통과하지 못하고 있다는 의미입니다.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>측정 방법</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>측정 도구는 크게 두 가지로 나뉩니다. <strong>Field Data</strong>(실사용자 측정)와 <strong>Lab Data</strong>(시뮬레이션 측정). 검색 순위에 영향을 주는 것은 Field Data이므로, 다음 도구들이 우선순위가 높습니다.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>PageSpeed Insights</strong> — pagespeed.web.dev. Field + Lab 통합.</li>
<li><strong>Google Search Console</strong> → Core Web Vitals 보고서. 28일 이동 평균.</li>
<li><strong>Chrome User Experience Report (CrUX)</strong> — BigQuery로 원천 데이터 접근.</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>LCP 개선 — 가장 큰 그림을 빨리 보여주기</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>LCP는 일반적으로 페이지 상단의 메인 이미지나 큰 텍스트 블록이 대상입니다. 다음 세 가지가 가장 효과적인 개선 방법입니다.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"ordered":true} -->
<ol>
<li>이미지 최적화 — WebP/AVIF 사용, 적절한 해상도, lazy loading 제외(상단 이미지)</li>
<li>웹폰트 최적화 — font-display: swap, preload, 서브셋팅</li>
<li>서버 응답시간 단축 — 캐싱, CDN, 호스팅 등급 검토</li>
</ol>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>INP 개선 — 클릭에 빠르게 반응하기</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>INP는 사용자가 클릭·탭·키 입력 후 다음 화면이 그려질 때까지의 시간입니다. 무거운 JavaScript가 메인 스레드를 점유하고 있으면 INP가 늘어납니다. 주요 개선 방법은 다음과 같습니다.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>긴 작업(Long Task)을 작은 단위로 분할 (yield to main thread)</li>
<li>불필요한 third-party 스크립트 제거 또는 지연 로딩</li>
<li>이벤트 핸들러를 debounce·throttle 처리</li>
</ul>
<!-- /wp:list -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><p>"INP는 사용자가 사이트를 살아있다고 느끼게 만드는 가장 직접적인 지표입니다." — Chrome DevRel, 2024 INP 도입 공식 발표</p></blockquote>
<!-- /wp:quote -->

<!-- wp:heading -->
<h2>CLS 개선 — 흔들리지 않는 레이아웃</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>CLS는 페이지 로딩 도중 콘텐츠가 갑자기 밀려서 사용자가 잘못된 버튼을 누르게 만드는 현상을 측정합니다. 가장 흔한 원인은 다음과 같습니다.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>이미지·iframe에 width/height 속성 미지정</li>
<li>광고·임베드 영역의 공간 미확보</li>
<li>웹폰트 로드 후 텍스트가 다시 그려지면서 발생하는 시프트</li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>모든 미디어와 동적 영역에 <strong>고정 크기 또는 aspect-ratio</strong>를 지정하면 CLS는 대부분 0.05 이하로 떨어집니다.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>정리</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Core Web Vitals 개선은 한 번에 완벽해지지 않습니다. 측정 → 가장 큰 문제 1개 개선 → 재측정의 사이클을 4주 단위로 반복하는 것이 현실적입니다. 사이트 공개 시 기본 세팅을 점검하려면 <a href="/column/seo-initial-setup-10min-routine-2026">SEO 초기 세팅 10분 루틴</a>도 참고하세요.</p>
<!-- /wp:paragraph -->';
}

/* ============================================================
   샘플 3 — WordPress 보안 점검 5단계
   ============================================================ */
function uw_column_seed_v2_content_3()
{
    return '<!-- wp:paragraph -->
<p>WordPress는 2026년 기준 전 세계 웹사이트의 <strong>43%</strong>를 차지하는 가장 큰 CMS입니다. 시장 점유율이 큰 만큼 자동화된 공격의 표적이 되기도 쉽습니다. Sucuri의 2025 Website Threat Report에 따르면 침해 사고의 <strong>96%</strong>가 WordPress 사이트에서 발생했습니다.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>다행히 침해의 대부분은 기본 보안 점검을 지키지 않은 사이트에서 발생합니다. 다음 다섯 가지 항목만 유지해도 자동화된 공격의 90% 이상을 차단할 수 있습니다.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>1. 코어·플러그인·테마 최신 유지</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>2025년 침해 사고의 <strong>52%</strong>는 알려진 취약점이 있는 구버전 플러그인 때문에 발생했습니다. 가장 단순하면서 가장 효과적인 방어는 업데이트 누락을 막는 것입니다.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>코어 마이너 업데이트는 자동 적용 (기본 설정)</li>
<li>주요 플러그인은 주 1회 점검, 보안 패치는 즉시 적용</li>
<li>사용하지 않는 플러그인·테마는 비활성화가 아닌 <strong>완전 삭제</strong></li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>2. 관리자 계정 강화</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>"admin"이라는 사용자명은 무차별 대입 공격(brute force)의 1순위 타겟입니다. 사용자명을 변경하고, 강력한 비밀번호 + 2단계 인증(2FA)을 적용하세요.</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><p>"2FA를 활성화한 계정은 비활성 계정 대비 침해 가능성이 99.9% 낮았습니다." — Microsoft Digital Defense Report 2024</p></blockquote>
<!-- /wp:quote -->

<!-- wp:list -->
<ul>
<li>관리자 사용자명에 "admin", "root", 도메인명 사용 금지</li>
<li>비밀번호 16자 이상, 영문 + 숫자 + 특수문자 조합</li>
<li>Wordfence·iThemes Security·Two Factor 등 2FA 플러그인 적용</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>3. 정기 백업</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>완벽한 예방은 불가능합니다. 침해가 발생했을 때 복구 가능성은 백업 상태에 달려 있습니다. 다음 세 가지 원칙을 권장합니다.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"ordered":true} -->
<ol>
<li><strong>3-2-1 규칙</strong> — 백업본 3개, 미디어 2종, 오프사이트 1개</li>
<li><strong>주기</strong> — 콘텐츠 갱신이 잦으면 일 단위, 그렇지 않으면 주 단위</li>
<li><strong>복원 테스트</strong> — 3개월에 한 번은 실제 복원이 되는지 검증</li>
</ol>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>UpdraftPlus, BackupBuddy, Jetpack VaultPress 등이 대표적인 백업 솔루션입니다. 호스팅사가 제공하는 백업은 보조 수단으로만 활용하고, 별도 오프사이트 백업을 반드시 함께 두세요.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>4. SSL 인증서 · HTTPS 강제</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>SSL은 단순히 "자물쇠 아이콘"이 아니라 통신 중간자 공격을 차단하는 기본 방어선입니다. 2026년 기준 Chrome·Safari·Firefox 모두 비-HTTPS 사이트에 "안전하지 않음" 경고를 표시합니다.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>Let\'s Encrypt 무료 인증서 활용 (대부분 호스팅사가 자동 발급)</li>
<li>wp-config.php에 FORCE_SSL_ADMIN 설정으로 관리자 페이지 HTTPS 강제</li>
<li>http → https 영구 리다이렉트(.htaccess)</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>5. 보안 플러그인 + 모니터링</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>마지막은 능동적 방어 + 모니터링입니다. Wordfence Free, Sucuri Free 같은 보안 플러그인은 무료 버전만으로도 자동화된 공격의 상당수를 차단합니다.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>방화벽(WAF) 활성화</li>
<li>로그인 시도 횟수 제한 (5회 이상 시 IP 차단)</li>
<li>파일 변경 알림 — 핵심 파일이 외부에서 변경되면 즉시 이메일 알림</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>정리</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>보안은 한 번 세팅하고 끝나는 작업이 아니라 <strong>주기적인 점검 루틴</strong>입니다. 위 다섯 항목을 분기에 한 번씩만 검토해도 일반적인 침해 위험은 크게 줄어듭니다. 운영 리소스가 부족하다면 <a href="/maintenance">월 단위 유지보수 서비스</a>에서 보안 점검도 함께 진행할 수 있습니다.</p>
<!-- /wp:paragraph -->';
}
