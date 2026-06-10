<?php
/**
 * Template Name: 고객지원 (Support)
 *
 * 허브 페이지. 공지/FAQ/가이드센터로 연결.
 */
get_header();
?>


<main class="cm-main" id="main-content" role="main">

  <h1 class="blind">고객지원</h1>

  <section class="support-hub-con sub-content-con">
    <div class="area">
      <div class="cm-tit-box" data-animate="fade-up">
        <span class="cm-tit-sub">SUPPORT</span>
        <h2 class="cm-tit">고객지원 센터</h2>
        <p class="cm-tit-txt">공지사항, 자주 묻는 질문, 가이드 문서를 한곳에서 확인하세요.</p>
      </div>

      <div class="support-hub-list" data-stagger>
        <a href="<?php echo esc_url(home_url('/notice/')); ?>" class="support-hub-item" data-animate="fade-up">
          <h3 class="support-hub-tit">공지사항</h3>
          <p class="support-hub-txt">서비스 업데이트, 운영 안내, 중요 공지를 확인하세요.</p>
          <span class="support-hub-link">공지 목록 →</span>
        </a>
        <a href="<?php echo esc_url(home_url('/faq/')); ?>" class="support-hub-item" data-animate="fade-up">
          <h3 class="support-hub-tit">자주 묻는 질문</h3>
          <p class="support-hub-txt">제작·가격·유지보수 관련 FAQ 모음.</p>
          <span class="support-hub-link">FAQ 보기 →</span>
        </a>
        <a href="https://guide.unwebs.co.kr" class="support-hub-item" target="_blank" rel="noopener noreferrer" data-animate="fade-up">
          <h3 class="support-hub-tit">가이드 센터</h3>
          <p class="support-hub-txt">워드프레스 관리자 사용법, 튜토리얼 아카이브 (새창).</p>
          <span class="support-hub-link">가이드 이동 ↗</span>
        </a>
      </div>
    </div>
  </section>

</main>

<?php get_footer(); ?>
