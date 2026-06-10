/**
 * Service Materials — Video Play/Pause Toggle
 *
 *  - .materials-step-video-toggle 클릭 시 직전 <video> 재생/정지
 *  - data-state 속성으로 CSS가 아이콘 표시 전환 (playing → pause SVG, paused → play SVG)
 */
(function () {
  'use strict';

  var toggles = document.querySelectorAll('.materials-step-video-toggle');
  if (!toggles.length) return;

  toggles.forEach(function (btn) {
    var media = btn.closest('.materials-step-media');
    if (!media) return;
    var video = media.querySelector('video');
    if (!video) return;

    function syncState() {
      if (video.paused) {
        btn.setAttribute('data-state', 'paused');
        btn.setAttribute('aria-label', '재생');
      } else {
        btn.setAttribute('data-state', 'playing');
        btn.setAttribute('aria-label', '정지');
      }
    }

    btn.addEventListener('click', function () {
      if (video.paused) {
        video.play();
      } else {
        video.pause();
      }
    });

    video.addEventListener('play',  syncState);
    video.addEventListener('pause', syncState);
    syncState();
  });
})();
