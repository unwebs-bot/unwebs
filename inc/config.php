<?php
/**
 * Site Configuration
 *
 * 프로젝트 시작 시 아래 값들을 실제 정보로 교체하세요.
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

/**
 * Get site config
 */
function starter_get_config()
{
    static $config = null;

    if ($config === null) {
        $config = array(
            // Site / SEO Info
            'site' => array(
                'title'       => '홈페이지제작전문 | 언웹스',
                'description' => '기업홈페이지제작 1000+ | LG, 문화체육관광부, 현대엔지니어링 프로젝트 진행',
                'keywords'    => '홈페이지제작, 웹사이트제작, 아임웹 제작, 워드프레스제작, 기업홈페이지, 언웹스',
                'og_image'    => '/assets/images/ogimage.png',     // 테마 상대경로
                'theme_color' => '#222222',
                'google_verification' => '',   // Google Search Console
                'naver_verification'  => '',   // Naver Search Advisor
            ),

            // Company Info
            'company' => array(
                'name'      => '언웹스',
                'name_en'   => 'Unwebs',
                'ceo'       => '이종진',
                'tel'       => '0507.1381.3731',
                'fax'       => '',
                'email'     => 'contact@unwebs.co.kr',
                'address'   => '서울특별시 성동구 뚝섬로13길 38 KT&G 상상플래닛, 6층',
                'biz_no'    => '164-62-00641',
                'corp_no'   => '',
                'hours'     => '평일 09:00 ~ 17:00 (주말/공휴일 제외)',
            ),

            // SNS Links
            'sns' => array(
                'facebook'  => '',
                'instagram' => '',
                'youtube'   => '',
                'blog'      => '',
            ),

            // Navigation Menu
            'nav' => array(
                'service' => array(
                    'label' => '홈페이지 제작',
                    'url'   => '#',
                    'items' => array(
                        array('slug' => '/service-info/', 'label' => '서비스 안내'),
                        array('slug' => '/resources/', 'label' => '준비자료'),
                        array('slug' => '/process/', 'label' => '제작절차'),
                    ),
                ),
                'maintenance' => array(
                    'label' => '유지보수',
                    'url'   => '#',
                    'items' => array(
                        array('slug' => '/maintenance-apply/', 'label' => '유지보수 신청'),
                        array('slug' => '/one-time-maintenance/', 'label' => '건별 유지보수'),
                        array('slug' => '/subscription-maintenance/', 'label' => '정기 유지보수'),
                    ),
                ),
                'portfolio' => array(
                    'label' => '포트폴리오',
                    'url'   => '/portfolio/',
                    'items' => array(),
                ),
                'support' => array(
                    'label' => '고객지원',
                    'url'   => '#',
                    'items' => array(
                        array('slug' => '/notice/', 'label' => '공지사항'),
                        array('slug' => '/faq/', 'label' => '자주묻는질문'),
                        array('slug' => 'https://guide.unwebs.co.kr', 'label' => '가이드 센터', 'target' => '_blank'),
                    ),
                ),
                'blog' => array(
                    'label' => '블로그',
                    'url'   => '/blog/',
                    'items' => array(),
                ),
            ),
        );
    }

    return $config;
}

/**
 * Get site/SEO info
 */
function starter_site($key = null)
{
    $config = starter_get_config();
    if ($key === null) {
        return $config['site'];
    }
    return isset($config['site'][$key]) ? $config['site'][$key] : '';
}

/**
 * Get head meta data (페이지별 동적 생성)
 */
function starter_head_meta()
{
    $site = starter_site();
    $site_url = home_url('/');

    // 페이지별 타이틀
    if (is_front_page()) {
        $page_title = $site['title'];
    } elseif (is_singular()) {
        $page_title = get_the_title() . ' | ' . $site['title'];
    } else {
        $page_title = wp_title('|', false, 'right') . $site['title'];
    }

    // 페이지별 설명
    if (is_singular() && has_excerpt()) {
        $page_desc = get_the_excerpt();
    } else {
        $page_desc = $site['description'];
    }

    // OG 이미지 (특성 이미지 → 기본 이미지)
    if (is_singular() && has_post_thumbnail()) {
        $og_image = get_the_post_thumbnail_url(null, 'large');
    } else {
        $og_image = get_theme_file_uri($site['og_image']);
    }

    // Canonical URL
    if (is_singular()) {
        $canonical = get_permalink();
    } else {
        $canonical = $site_url;
    }

    return array(
        'title'       => esc_attr($page_title),
        'description' => esc_attr($page_desc),
        'keywords'    => esc_attr($site['keywords']),
        'og_image'    => esc_url($og_image),
        'canonical'   => esc_url($canonical),
        'site_url'    => esc_url($site_url),
        'theme_color' => esc_attr($site['theme_color']),
        'google_verification' => esc_attr($site['google_verification']),
        'naver_verification'  => esc_attr($site['naver_verification']),
    );
}

/**
 * Get company info
 */
function starter_company($key = null)
{
    $config = starter_get_config();
    if ($key === null) {
        return $config['company'];
    }
    return isset($config['company'][$key]) ? $config['company'][$key] : '';
}

/**
 * Get navigation menu
 */
function starter_nav($section = null)
{
    $config = starter_get_config();
    if ($section === null) {
        return $config['nav'];
    }
    return isset($config['nav'][$section]) ? $config['nav'][$section] : array();
}

/**
 * Get current nav section from URL
 */
function starter_current_nav_section()
{
    // 보안: REQUEST_URI 살균 (XSS 방지)
    $uri = isset($_SERVER['REQUEST_URI'])
        ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI']))
        : '';
    $nav = starter_nav();

    foreach ($nav as $key => $data) {
        // $key는 내부 정의값이므로 안전
        if (strpos($uri, '/' . $key . '/') !== false) {
            return $key;
        }
    }

    return 'about'; // default
}

/**
 * Get SNS links
 */
function starter_sns($key = null)
{
    $config = starter_get_config();
    if ($key === null) {
        return array_filter($config['sns']); // 빈 값 제거
    }
    return isset($config['sns'][$key]) ? $config['sns'][$key] : '';
}

/**
 * Get social icon SVG
 */
function starter_get_social_icon($name)
{
    $icons = array(
        'facebook' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
        'instagram' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
        'youtube' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
        'blog' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.64.135-.954l11.566-4.458c.538-.196 1.006.128.828.94z"/></svg>',
    );

    return isset($icons[$name]) ? $icons[$name] : '';
}

/**
 * Get footer data
 */
function starter_footer_data()
{
    $company = starter_company();

    return array(
        'logo'        => get_theme_file_uri('/assets/images/logo.png'),
        'socialLinks' => array(
            array(
                'name' => 'facebook',
                'url'  => starter_sns('facebook'),
                'icon' => 'facebook',
            ),
            array(
                'name' => 'instagram',
                'url'  => starter_sns('instagram'),
                'icon' => 'instagram',
            ),
            array(
                'name' => 'youtube',
                'url'  => starter_sns('youtube'),
                'icon' => 'youtube',
            ),
            array(
                'name' => 'blog',
                'url'  => starter_sns('blog'),
                'icon' => 'blog',
            ),
        ),
        'companyInfo' => array(
            'address'   => $company['address'],
            'tel'       => $company['tel'],
            'fax'       => $company['fax'],
            'email'     => $company['email'],
            'copyright' => '© ' . date('Y') . ' ' . $company['name'] . '. All Rights Reserved.',
        ),
    );
}

/**
 * Get privacy policy content
 */
function starter_privacy_policy()
{
    return <<<'POLICY'
<h3>개인정보처리방침</h3>

<p>본 개인정보처리방침은 회사(이하 "회사")가 제공하는 서비스 이용과 관련하여 이용자의 개인정보 보호에 관한 사항을 규정합니다.</p>

<h4>제1조 (개인정보의 수집 및 이용 목적)</h4>
<p>회사는 다음의 목적을 위하여 개인정보를 처리합니다. 처리하고 있는 개인정보는 다음의 목적 이외의 용도로는 이용되지 않으며, 이용 목적이 변경되는 경우에는 별도의 동의를 받는 등 필요한 조치를 이행할 예정입니다.</p>
<ul>
<li>서비스 제공 및 계약의 이행</li>
<li>회원 관리 및 본인 확인</li>
<li>마케팅 및 광고에의 활용</li>
<li>서비스 개선 및 신규 서비스 개발</li>
</ul>

<h4>제2조 (수집하는 개인정보의 항목)</h4>
<p>회사는 서비스 제공을 위해 다음과 같은 개인정보를 수집합니다.</p>
<ul>
<li>필수항목: 이름, 연락처, 이메일 주소</li>
<li>선택항목: 회사명, 직위, 문의내용</li>
</ul>

<h4>제3조 (개인정보의 보유 및 이용기간)</h4>
<p>회사는 법령에 따른 개인정보 보유·이용기간 또는 정보주체로부터 개인정보를 수집 시에 동의 받은 개인정보 보유·이용기간 내에서 개인정보를 처리·보유합니다.</p>
<ul>
<li>계약 또는 청약철회 등에 관한 기록: 5년</li>
<li>대금결제 및 재화 등의 공급에 관한 기록: 5년</li>
<li>소비자의 불만 또는 분쟁처리에 관한 기록: 3년</li>
</ul>

<h4>제4조 (개인정보의 파기)</h4>
<p>회사는 개인정보 보유기간의 경과, 처리목적 달성 등 개인정보가 불필요하게 되었을 때에는 지체없이 해당 개인정보를 파기합니다.</p>

<h4>제5조 (정보주체의 권리·의무 및 행사방법)</h4>
<p>정보주체는 회사에 대해 언제든지 다음 각 호의 개인정보 보호 관련 권리를 행사할 수 있습니다.</p>
<ul>
<li>개인정보 열람 요구</li>
<li>오류 등이 있을 경우 정정 요구</li>
<li>삭제 요구</li>
<li>처리정지 요구</li>
</ul>

<h4>제6조 (개인정보 보호책임자)</h4>
<p>회사는 개인정보 처리에 관한 업무를 총괄해서 책임지고, 개인정보 처리와 관련한 정보주체의 불만처리 및 피해구제 등을 위하여 아래와 같이 개인정보 보호책임자를 지정하고 있습니다.</p>

<h4>제7조 (개인정보처리방침의 변경)</h4>
<p>이 개인정보처리방침은 시행일로부터 적용되며, 법령 및 방침에 따른 변경내용의 추가, 삭제 및 정정이 있는 경우에는 변경사항의 시행 7일 전부터 공지사항을 통하여 고지할 것입니다.</p>

<p><strong>시행일자: 2024년 1월 1일</strong></p>
POLICY;
}

/**
 * Get terms of service content
 */
function starter_terms_of_service()
{
    return <<<'TERMS'
<h3>이용약관</h3>

<h4>제1조 (목적)</h4>
<p>이 약관은 회사(이하 "회사")가 제공하는 서비스의 이용조건 및 절차, 회사와 이용자의 권리, 의무, 책임사항과 기타 필요한 사항을 규정함을 목적으로 합니다.</p>

<h4>제2조 (정의)</h4>
<ul>
<li>"서비스"란 회사가 제공하는 모든 서비스를 의미합니다.</li>
<li>"이용자"란 이 약관에 따라 회사가 제공하는 서비스를 이용하는 자를 말합니다.</li>
<li>"회원"이란 회사에 개인정보를 제공하여 회원등록을 한 자로서, 회사의 정보를 지속적으로 제공받으며 서비스를 계속적으로 이용할 수 있는 자를 말합니다.</li>
</ul>

<h4>제3조 (약관의 효력 및 변경)</h4>
<p>이 약관은 서비스를 이용하고자 하는 모든 이용자에게 그 효력이 발생합니다. 회사는 필요한 경우 약관을 변경할 수 있으며, 변경된 약관은 공지사항에 공지함으로써 효력이 발생합니다.</p>

<h4>제4조 (서비스의 제공)</h4>
<p>회사는 다음과 같은 서비스를 제공합니다.</p>
<ul>
<li>기업 정보 및 제품 정보 제공</li>
<li>온라인 상담 및 문의 서비스</li>
<li>기타 회사가 정하는 서비스</li>
</ul>

<h4>제5조 (서비스의 중단)</h4>
<p>회사는 컴퓨터 등 정보통신설비의 보수점검, 교체 및 고장, 통신의 두절 등의 사유가 발생한 경우에는 서비스의 제공을 일시적으로 중단할 수 있습니다.</p>

<h4>제6조 (이용자의 의무)</h4>
<p>이용자는 다음 행위를 하여서는 안 됩니다.</p>
<ul>
<li>신청 또는 변경 시 허위내용의 등록</li>
<li>타인의 정보 도용</li>
<li>회사에 게시된 정보의 무단 변경</li>
<li>회사가 정한 정보 이외의 정보 등의 송신 또는 게시</li>
<li>회사 및 기타 제3자의 저작권 등 지적재산권에 대한 침해</li>
<li>회사 및 기타 제3자의 명예를 손상시키거나 업무를 방해하는 행위</li>
</ul>

<h4>제7조 (저작권의 귀속)</h4>
<p>회사가 작성한 저작물에 대한 저작권 기타 지적재산권은 회사에 귀속합니다. 이용자는 서비스를 이용함으로써 얻은 정보를 회사의 사전 승낙 없이 복제, 송신, 출판, 배포, 방송 기타 방법에 의하여 영리목적으로 이용하거나 제3자에게 이용하게 하여서는 안 됩니다.</p>

<h4>제8조 (면책조항)</h4>
<p>회사는 천재지변 또는 이에 준하는 불가항력으로 인하여 서비스를 제공할 수 없는 경우에는 서비스 제공에 관한 책임이 면제됩니다. 회사는 이용자의 귀책사유로 인한 서비스 이용의 장애에 대하여 책임을 지지 않습니다.</p>

<h4>제9조 (분쟁해결)</h4>
<p>회사와 이용자 간에 발생한 분쟁에 관한 소송은 회사의 본사 소재지를 관할하는 법원을 전속 관할법원으로 합니다.</p>

<h4>부칙</h4>
<p>이 약관은 2024년 1월 1일부터 시행합니다.</p>
TERMS;
}
