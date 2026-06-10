<?php
/**
 * Template Part: 반응형 체험 섹션 (Responsive Demo)
 *
 *  - 디바이스 토글 (PC / 태블릿 / 모바일) 클릭 시 .service-responsive-device 의 width 변경
 *  - 내부 샘플 사이트는 CSS Container Query (container-type: inline-size) 로 자체 반응형
 *  - pointer-events: none 으로 샘플 사이트 클릭 차단
 *  - 다크 배경 / 카드형 토글
 */
if (!defined('ABSPATH'))
  exit;
?>

<section class="service-responsive-con sub-content-con">
  <div class="area">

    <div class="cm-tit-box" data-animate="fade-up">
      <span class="cm-tit-sub">브라우저 창 크기에 따라 유동적으로 반응하는 홈페이지</span>
      <h2 class="cm-tit">웹표준을 준수한 반응형으로 제작합니다</h2>
    </div>

    <!-- 디바이스 토글 (카드형) -->
    <ul class="service-responsive-toggle" data-animate="fade-up" data-delay="100">
      <li class="service-responsive-toggle-item is-active" data-device="pc">
        <i class="xi-desktop" aria-hidden="true"></i>
        <span>PC</span>
      </li>
      <li class="service-responsive-toggle-item" data-device="laptop">
        <i class="xi-laptop" aria-hidden="true"></i>
        <span>노트북</span>
      </li>
      <li class="service-responsive-toggle-item" data-device="tablet">
        <i class="xi-tablet" aria-hidden="true"></i>
        <span>태블릿</span>
      </li>
      <li class="service-responsive-toggle-item" data-device="mobile">
        <i class="xi-mobile" aria-hidden="true"></i>
        <span>모바일</span>
      </li>
    </ul>

    <!-- 프리뷰 프레임 -->
    <div class="service-responsive-frame" data-animate="fade-up" data-delay="200">
      <div class="service-responsive-device is-pc">

        <!-- 샘플 사이트 (Container Query로 자체 반응형) -->
        <div class="srd-site">

          <!-- Header -->
          <header class="srd-header">
            <div class="srd-logo">NOVUS</div>
            <nav class="srd-nav">
              <a>About</a>
              <a>Service</a>
              <a>Portfolio</a>
              <a>Contact</a>
            </nav>
            <button class="srd-burger" aria-hidden="true">
              <span></span><span></span><span></span>
            </button>
          </header>

          <!-- Section 1 — Main Visual (한글) -->
          <section class="srd-visual">
            <div class="srd-visual-bg" aria-hidden="true"></div>
            <div class="srd-visual-overlay" aria-hidden="true"></div>
            <div class="srd-visual-inner">
              <span class="srd-visual-sub">ABOUT US</span>
              <h3 class="srd-visual-tit">전문성과 신뢰를 바탕으로 최적의 솔루션을 제공합니다.</h3>
              <p class="srd-visual-txt">고객의 성공을 함께 만들어 갑니다</p>
              <span class="srd-visual-btn">회사소개 보기 <i class="xi-arrow-right"></i></span>
            </div>
          </section>

          <!-- Section 2 — Strengths (한글) -->
          <section class="srd-values">
            <div class="srd-values-head">
              <h3 class="srd-values-tit">서비스 소개</h3>
              <p class="srd-values-desc">오랜 경험과 노하우로 고객에게 최고의 가치를 전달합니다.</p>
            </div>
            <div class="srd-values-grid">
              <article class="srd-value-card">
                <div class="srd-value-icon"><i class="xi-check-circle-o"></i></div>
                <h4 class="srd-value-tit">전문 기술력</h4>
                <p class="srd-value-txt">검증된 노하우와 안정적인 기술로 신뢰할 수 있는 결과물을 제공합니다.</p>
              </article>
              <article class="srd-value-card">
                <div class="srd-value-icon"><i class="xi-puzzle"></i></div>
                <h4 class="srd-value-tit">맞춤형 솔루션</h4>
                <p class="srd-value-txt">고객의 요구사항을 정확히 분석해 최적의 솔루션을 제안합니다.</p>
              </article>
              <article class="srd-value-card">
                <div class="srd-value-icon"><i class="xi-flash"></i></div>
                <h4 class="srd-value-tit">신속한 대응</h4>
                <p class="srd-value-txt">체계적인 일정 관리로 약속한 기한 내에 정확하게 진행합니다.</p>
              </article>
              <article class="srd-value-card">
                <div class="srd-value-icon"><i class="xi-heart-o"></i></div>
                <h4 class="srd-value-tit">사후 관리 지원</h4>
                <p class="srd-value-txt">납품 이후에도 지속적인 관리와 기술 지원을 제공합니다.</p>
              </article>
            </div>
          </section>

          <!-- Section 3 — Notice / News (한글) -->
          <section class="srd-notice">
            <div class="srd-notice-head">
              <div class="srd-notice-headline">
                <span class="srd-notice-sub">NOTICE</span>
                <h3 class="srd-notice-tit">공지사항</h3>
              </div>
              <span class="srd-notice-more">전체 보기 <i class="xi-arrow-right"></i></span>
            </div>
            <ul class="srd-notice-list">
              <li class="srd-notice-item">
                <span class="srd-notice-badge is-new">NEW</span>
                <a class="srd-notice-link">2026년 상반기 신규 서비스 안내</a>
                <span class="srd-notice-date">2026.05.08</span>
              </li>
              <li class="srd-notice-item">
                <span class="srd-notice-badge">공지</span>
                <a class="srd-notice-link">홈페이지 리뉴얼 오픈 안내</a>
                <span class="srd-notice-date">2026.04.22</span>
              </li>
              <li class="srd-notice-item">
                <span class="srd-notice-badge">공지</span>
                <a class="srd-notice-link">고객센터 운영시간 변경 안내</a>
                <span class="srd-notice-date">2026.04.10</span>
              </li>
              <li class="srd-notice-item">
                <span class="srd-notice-badge">이벤트</span>
                <a class="srd-notice-link">상반기 고객 감사 이벤트 진행</a>
                <span class="srd-notice-date">2026.03.28</span>
              </li>
            </ul>
          </section>

        </div>
      </div>
    </div>

  </div>
</section>

<script>
  (function () {
    'use strict';
    var toggles = document.querySelectorAll('.service-responsive-toggle-item');
    var device = document.querySelector('.service-responsive-device');
    if (!toggles.length || !device) return;

    toggles.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var d = btn.getAttribute('data-device');
        toggles.forEach(function (b) { b.classList.remove('is-active'); });
        btn.classList.add('is-active');
        device.classList.remove('is-pc', 'is-laptop', 'is-tablet', 'is-mobile');
        device.classList.add('is-' + d);
      });
    });
  })();
</script>