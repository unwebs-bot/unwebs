/**
 * Splitting.js 초기화 + IntersectionObserver 트리거
 *
 * 동작:
 *   1) cm-tit / cm-tit-sub / main-visual-tit 을 char 단위 span으로 자동 분할
 *   2) cm-tit-box / main-visual-slide 진입 또는 활성 시 .is-animated 클래스 토글
 *   3) CSS transition으로 char별 순차 슬라이드
 *
 * main-visual-slide는 슬라이드 fade 시스템이 별도라 .is-active 상태에서 char 슬라이드 동작
 * (CSS에서 .main-visual-slide.is-active 셀렉터로 처리 — JS 트리거 불필요)
 */
(function () {
  'use strict';

  if (typeof Splitting !== 'function') return;

  // 1. 분할 실행 — cm-tit / cm-tit-sub / cm-tit-txt / main-visual-tit
  //    [data-no-split] 속성이 붙은 요소는 제외 — 특정 섹션 splitting 비활성화 옵션
  Splitting({
    target: '.cm-tit:not([data-no-split]), .cm-tit-sub:not([data-no-split]), .cm-tit-txt:not([data-no-split]), .main-visual-tit:not([data-no-split])',
    by: 'chars',
  });

  // 2. 트리거 요소 수집 — cm-tit-box(메인+서브 본문)
  const triggers = document.querySelectorAll('.cm-tit-box');
  if (!triggers.length) return;

  // 3. IntersectionObserver — 30% 노출 시 발동, 한 번만
  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-animated');
          io.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.3 }
  );

  triggers.forEach((el) => io.observe(el));
})();
