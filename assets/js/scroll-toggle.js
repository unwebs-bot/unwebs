/**
 * Scroll-direction toggle
 *
 * 아래로 스크롤 → body에 .is-scrolled-down 추가 (헤더·배너 숨김)
 * 위로 스크롤 → .is-scrolled-down 제거 (헤더·배너 재등장)
 * 페이지 최상단(THRESHOLD 이하)에선 항상 표시
 *
 * 모든 페이지에서 실행 (header 공통 동작).
 */

(function () {
  'use strict';

  var body = document.body;
  var lastScroll = window.scrollY || 0;
  var ticking = false;

  var TOP_THRESHOLD = 80; // 이 값 이하에선 항상 표시
  var DELTA = 5;          // 방향 감지 민감도(px)

  function update() {
    var current = window.scrollY || window.pageYOffset || 0;

    if (current < TOP_THRESHOLD) {
      body.classList.remove('is-scrolled-down');
    } else if (current - lastScroll > DELTA) {
      // 아래로 이동
      body.classList.add('is-scrolled-down');
    } else if (lastScroll - current > DELTA) {
      // 위로 이동
      body.classList.remove('is-scrolled-down');
    }

    lastScroll = current;
    ticking = false;
  }

  window.addEventListener('scroll', function () {
    if (!ticking) {
      window.requestAnimationFrame(update);
      ticking = true;
    }
  }, { passive: true });

  update();
})();
