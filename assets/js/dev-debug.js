(function () {
  'use strict';

  document.body.classList.add('uw-debug');

  function update() {
    var vw = window.innerWidth;
    document.body.setAttribute('data-vw', vw);

    var overflow = document.documentElement.scrollWidth > vw;
    document.body.classList.toggle('uw-overflow', overflow);

    if (overflow && !window.__uwOverflowReported) {
      window.__uwOverflowReported = true;
      console.warn('[uw-debug] 가로 스크롤 감지 — scrollWidth:', document.documentElement.scrollWidth, 'vw:', vw);
    } else if (!overflow) {
      window.__uwOverflowReported = false;
    }
  }

  update();
  window.addEventListener('resize', update, { passive: true });
  window.addEventListener('load', update);
})();
