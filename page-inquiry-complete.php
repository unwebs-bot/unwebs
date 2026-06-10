<?php
/**
 * Template Name: 문의 접수 완료
 *
 * 슬러그 `inquiry-complete` 페이지에 자동 적용(page-{slug}.php).
 * 메인페이지 하단·/contact 의 "제작 문의" 폼(main-contact) 접수 후 redirect 도착지.
 *
 * ★ 이 페이지 도달 = 전환(conversion) 발화 지점:
 *   - 네이버 검색광고 전환     : window.uwNaverCnvInquiry()              (inc/cm-naver-ads.php)
 *   - 구글(GTM/GA4·Google Ads): dataLayer.push({event:'inquiry_complete'}) (inc/cm-gtm.php)
 *   폼 제출 시 남긴 sessionStorage 플래그가 있을 때만 발화 → 새로고침·직접접근 중복 집계 방지.
 */

if (!defined('ABSPATH')) exit;

// thank-you 페이지: 검색 색인 제외
add_filter('wp_robots', 'wp_robots_no_robots');
add_filter('rank_math/frontend/robots', function ($robots) {
    $robots['index']  = 'noindex';
    $robots['follow'] = 'follow';
    return $robots;
});

get_header();

$kakao_url = function_exists('uw_kakao_consult_url') ? uw_kakao_consult_url() : '';
?>

<main class="cm-main inquiry-complete" id="main-content" role="main">
  <section class="inquiry-complete-sec">
    <div class="inquiry-complete-card">

      <span class="inquiry-complete-icon" aria-hidden="true">
        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="24" cy="24" r="21" stroke="currentColor" stroke-width="3.5"/>
          <path d="M15 24.5L21.5 31L34 18" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </span>

      <h1 class="inquiry-complete-tit">문의 접수가 완료되었습니다</h1>
      <p class="inquiry-complete-sub">영업일 <strong>1일 내</strong>로 담당자가 연락드리겠습니다.</p>

      <hr class="inquiry-complete-divider">

      <p class="inquiry-complete-desc">
        보다 자세한 상담을 위해 아래 카카오톡으로<br>
        <strong>‘이름, 회사명, 연락처’</strong>를 전달해주세요.
      </p>

      <a class="inquiry-complete-kakao" href="<?php echo esc_url($kakao_url ?: '#'); ?>"<?php echo $kakao_url ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M12 3C6.48 3 2 6.59 2 11.02c0 2.86 1.9 5.37 4.76 6.78-.21.77-.76 2.79-.87 3.22-.14.55.2.54.42.39.17-.11 2.71-1.84 3.81-2.59.6.09 1.23.13 1.88.13 5.52 0 10-3.59 10-8.02C24 6.59 19.52 3 12 3z"/>
        </svg>
        <span>카카오톡 상담하기</span>
      </a>

      <a class="inquiry-complete-home" href="<?php echo esc_url(home_url('/')); ?>">홈으로 가기</a>

    </div>
  </section>
</main>

<script>
(function () {
  // 폼 제출을 거쳐 도착한 경우에만 전환 발화 (새로고침·직접접근·북마크 중복 집계 방지)
  try {
    if (sessionStorage.getItem('uw_inquiry_done') !== '1') return;
    sessionStorage.removeItem('uw_inquiry_done');
  } catch (e) { return; }

  // 네이버 검색광고 전환
  if (typeof window.uwNaverCnvInquiry === 'function') { window.uwNaverCnvInquiry(); }

  // 구글 애즈 전환 (gtag.js 직접 — inc/cm-gads.php)
  if (typeof window.uwGadsConversion === 'function') { window.uwGadsConversion(); }

  // GTM dataLayer 이벤트 (GA4·기타 GTM 태그용. ※ Google Ads 전환은 위 gtag가 처리 — GTM에 같은 전환 중복 연결 금지)
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push({ event: 'inquiry_complete' });
})();
</script>

<?php get_footer(); ?>
