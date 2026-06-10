/**
 * Service Page — Floating Section Tabs
 *
 *  - 반응형 체험 섹션(.service-responsive-con) 통과 후 탭 등장
 *  - 클릭 시 헤더+탭 높이만큼 offset 보정하며 smooth scroll
 *  - 섹션 상단이 헤더+탭 라인 바로 아래로 진입 시 해당 탭 활성화
 *  - 모바일(768 이하)에서는 동작 skip (CSS에서 display:none)
 */
(function () {
  'use strict';

  if (window.matchMedia('(max-width: 768px)').matches) return;

  var wrap = document.querySelector('[data-service-tabs]');
  if (!wrap) return;

  var links = Array.prototype.slice.call(wrap.querySelectorAll('.service-tabs-link'));
  if (!links.length) return;

  var sections = links
    .map(function (a) { return document.getElementById(a.getAttribute('data-target')); })
    .filter(Boolean);
  if (!sections.length) return;

  var heroEl = document.querySelector('.service-responsive-con');

  /* 헤더 + 탭 높이 합산 (offset 보정용) */
  function getHeaderOffset() {
    var header = document.querySelector('.cm-header') || document.querySelector('header.cm-header-wrap') || document.querySelector('.cm-header-wrap');
    var headerH = header ? header.getBoundingClientRect().height : 80;
    var tabsH = wrap.classList.contains('is-visible') ? wrap.getBoundingClientRect().height : 56;
    return headerH + tabsH;
  }

  /* 탭 활성화 */
  function setActive(id) {
    links.forEach(function (a) {
      var item = a.parentElement;
      if (a.getAttribute('data-target') === id) {
        item.classList.add('is-active');
      } else {
        item.classList.remove('is-active');
      }
    });
  }

  /* 클릭 — smooth scroll + offset 보정 */
  links.forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      var id = a.getAttribute('data-target');
      var target = document.getElementById(id);
      if (!target) return;
      var top = target.getBoundingClientRect().top + window.pageYOffset - getHeaderOffset() + 1;
      window.scrollTo({ top: top, behavior: 'smooth' });
    });
  });

  /* 탭 sticky 등장 — 반응형 체험 섹션 하단 통과 시 */
  function updateVisibility() {
    if (!heroEl) {
      wrap.classList.add('is-visible');
      wrap.setAttribute('aria-hidden', 'false');
      return;
    }
    var heroBottom = heroEl.getBoundingClientRect().bottom;
    var headerH = (document.querySelector('.cm-header-wrap') || {}).getBoundingClientRect
      ? document.querySelector('.cm-header-wrap').getBoundingClientRect().height
      : 80;
    if (heroBottom <= headerH + 4) {
      wrap.classList.add('is-visible');
      wrap.setAttribute('aria-hidden', 'false');
    } else {
      wrap.classList.remove('is-visible');
      wrap.setAttribute('aria-hidden', 'true');
    }
  }

  /* 활성 탭 결정 — 섹션 상단이 헤더+탭 라인 바로 아래에 진입했는가 */
  function updateActive() {
    var line = getHeaderOffset() + 8;
    var currentId = sections[0].id;
    for (var i = 0; i < sections.length; i++) {
      var rect = sections[i].getBoundingClientRect();
      if (rect.top - line <= 0) {
        currentId = sections[i].id;
      } else {
        break;
      }
    }
    setActive(currentId);
  }

  var ticking = false;
  function onScroll() {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(function () {
      updateVisibility();
      updateActive();
      ticking = false;
    });
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);
  updateVisibility();
  updateActive();
})();
