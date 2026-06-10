/**
 * Main Visual Slider
 *
 * Features:
 * - Crossfade slide transitions
 * - Background zoom effect
 * - Text enter/exit animations
 * - Progress bar navigation
 * - Auto-play with manual override
 */

(function () {
  'use strict';

  // ==========================================================================
  // DOM Elements
  // ==========================================================================
  const visual = document.querySelector('.main-visual-con');
  if (!visual) return;

  const slides = visual.querySelectorAll('.main-visual-slide');
  const allNavItems = visual.querySelectorAll('.main-visual-nav-item');

  if (slides.length === 0) return;

  let currentSlide = 0;
  const slideInterval = 7000;
  let slideTimer = null;

  // ==========================================================================
  // Animation Reset
  // ==========================================================================

  /**
   * 슬라이드 내부 요소 애니메이션 초기화
   * @param {Element} slide - Slide element
   */
  function resetSlideAnimations(slide) {
    var animatedEls = slide.querySelectorAll(
      '.main-visual-header, .main-visual-meta, .main-visual-actions, .main-visual-nav, .main-visual-tablet, .main-visual-phone, .main-visual-scroll-img'
    );
    animatedEls.forEach(function (el) {
      el.style.animation = 'none';
      el.offsetHeight; // reflow
      el.style.animation = '';
    });
  }

  // ==========================================================================
  // Slide Control
  // ==========================================================================

  /**
   * Show specific slide
   * @param {number} index - Slide index
   */
  function showSlide(index) {
    // 이전 슬라이드에 퇴장 클래스 추가
    var exitingSlide = slides[currentSlide];
    if (exitingSlide && currentSlide !== index) {
      exitingSlide.classList.add('is-exiting');
      setTimeout(function () {
        exitingSlide.classList.remove('is-exiting');
      }, 600);
    }

    // 모든 슬라이드/네비 비활성화 + fill 초기화
    slides.forEach(function (slide) {
      slide.classList.remove('is-active');
    });
    allNavItems.forEach(function (nav) {
      nav.classList.remove('is-active');
      var f = nav.querySelector('.main-visual-nav-fill');
      if (f) {
        f.style.animation = 'none';
        f.style.width = '0';
      }
    });

    // 활성 슬라이드 애니메이션 초기화 후 활성화
    resetSlideAnimations(slides[index]);
    slides[index].classList.add('is-active');

    // 모든 슬라이드의 해당 인덱스 nav-item 활성화
    allNavItems.forEach(function (nav) {
      if (parseInt(nav.getAttribute('data-slide'), 10) === index) {
        nav.classList.add('is-active');
        var fill = nav.querySelector('.main-visual-nav-fill');
        if (fill) {
          fill.offsetHeight; // reflow
          fill.style.width = '';
          fill.style.animation = 'cmVisualProgress ' + (slideInterval / 1000) + 's ease-out forwards';
        }
      }
    });

    currentSlide = index;
  }

  /**
   * Go to next slide
   */
  function nextSlide() {
    var nextIndex = (currentSlide + 1) % slides.length;
    showSlide(nextIndex);
  }

  /**
   * Start auto-play
   */
  function startAutoSlide() {
    // Init first slide progress bar
    allNavItems.forEach(function (nav) {
      if (parseInt(nav.getAttribute('data-slide'), 10) === currentSlide) {
        var fill = nav.querySelector('.main-visual-nav-fill');
        if (fill) {
          fill.style.animation = 'cmVisualProgress ' + (slideInterval / 1000) + 's ease-out forwards';
        }
      }
    });

    slideTimer = setInterval(nextSlide, slideInterval);
  }

  /**
   * Stop auto-play
   */
  function stopAutoSlide() {
    clearInterval(slideTimer);
  }

  /**
   * Navigate to specific slide (manual)
   * @param {number} index - Slide index
   */
  function goToSlide(index) {
    stopAutoSlide();
    showSlide(index);
    slideTimer = setInterval(nextSlide, slideInterval);
  }

  // ==========================================================================
  // Event Listeners
  // ==========================================================================

  // Navigation click
  allNavItems.forEach(function (item) {
    item.addEventListener('click', function () {
      var index = parseInt(this.getAttribute('data-slide'), 10);
      goToSlide(index);
    });
  });

  // ==========================================================================
  // Initialize
  // ==========================================================================
  startAutoSlide();

  // Public API
  window.cmVisual = {
    goToSlide: goToSlide,
    startAutoSlide: startAutoSlide,
    stopAutoSlide: stopAutoSlide
  };

})();

/**
 * Dashboard Vertical Ticker
 *
 * Features:
 * - Auto-scrolling card list
 * - Infinite loop (moves first item to end)
 * - Both lists scroll simultaneously
 */
(function () {
  'use strict';

  var GAP = 15;
  var SPEED = 3000;

  var projectList = document.getElementById('cmDashboardProject');
  var consultList = document.getElementById('cmDashboardConsult');

  if (!projectList && !consultList) return;

  function tickOnce(list) {
    if (!list || !list.firstElementChild) return;
    var firstItem = list.firstElementChild;
    var itemHeight = firstItem.offsetHeight;

    list.style.transition = 'transform 0.5s ease-in-out';
    list.style.transform = 'translateY(-' + (itemHeight + GAP) + 'px)';

    setTimeout(function () {
      list.style.transition = 'none';
      list.appendChild(firstItem);
      list.style.transform = 'translateY(0)';
    }, 500);
  }

  setInterval(function () {
    tickOnce(projectList);
    tickOnce(consultList);
  }, SPEED);

})();

/**
 * Maintenance 2-Column Ticker — CSS marquee로 통일됨
 * (이전 setInterval step 슬라이더는 CSS animation과 충돌해 제거)
 *
 * - PC vertical: .main-maintenance-track > .main-maintenance-list × 2 (CSS keyframe)
 * - Mobile horizontal: .main-maintenance-list 가로 marquee (CSS keyframe)
 * - JS 개입 없음
 */

/**
 * Schedule — Gantt 인터랙션
 *  - 뷰포트 진입 → .is-animated (막대 fill 애니메이션 트리거)
 *  - 그 외 hover 인터랙션 모두 제거
 */
(function () {
  'use strict';

  var schedule = document.getElementById('cmSchedule');
  if (!schedule) return;

  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        schedule.classList.add('is-animated');
        io.disconnect();
      }
    });
  }, { threshold: 0.2 });
  io.observe(schedule);
})();

/**
 * Service Process — sticky sidebar + scroll-driven 활성화
 *  - /service/#process 본 섹션
 *  - section-schedule.php 모바일 전용 영역 (동일 마크업 재사용)
 *  - 모바일(768 이하)에서는 fade·scale 애니메이션 비활성
 */
(function () {
  'use strict';

  // 모바일에서는 스크롤 반응 애니메이션 자체를 끔
  if (window.matchMedia('(max-width: 768px)').matches) return;

  var blocks = document.querySelectorAll('.service-process-block');
  var steps  = document.querySelectorAll('.service-process-step');
  if (!blocks.length || !steps.length) return;

  var ticking = false;

  function update() {
    var viewH = window.innerHeight;
    var activeIdx = 0;

    blocks.forEach(function (block, i) {
      var rect = block.getBoundingClientRect();
      var center = rect.top + rect.height / 2;
      var ratio = center / viewH;
      var fade;

      if (ratio < 0.3) {
        // 위쪽으로 빠져나가는 중 — 작아지고 연해짐
        fade = Math.max(0, 1 - (0.3 - ratio) / 0.3);
      } else if (ratio > 0.7) {
        // 아래쪽에서 진입 — 작고 연한 상태에서 점점 커지고 진해짐
        fade = Math.max(0, 1 - (ratio - 0.7) / 0.3);
      } else {
        // 활성 영역
        fade = 1;
      }

      block.style.opacity = fade;
      block.style.transform = 'scale(' + (0.92 + fade * 0.08) + ')';

      if (rect.top < viewH * 0.5 && rect.bottom > 0) {
        activeIdx = i;
      }
    });

    steps.forEach(function (step, i) {
      step.classList.toggle('is-active', i === activeIdx);
      step.classList.toggle('is-done', i < activeIdx);
    });

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

/**
 * Stats Rolling Number Counter
 *
 * - IntersectionObserver triggers digit rolling animation
 */
(function () {
  'use strict';

  var statsSection = document.getElementById('cmStats');
  if (!statsSection) return;

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        statsSection.classList.add('is-animated');
        observer.disconnect();
      }
    });
  }, { threshold: 0.3 });

  observer.observe(statsSection);
})();


/* ==========================================================================
   [data-animate] — 뷰포트 진입 시 .is-visible 토글
   ========================================================================== */
(function () {
  'use strict';

  var targets = document.querySelectorAll('[data-animate]');
  if (targets.length === 0) return;

  if (!('IntersectionObserver' in window)) {
    targets.forEach(function (el) { el.classList.add('is-visible'); });
    return;
  }

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        var delay = entry.target.getAttribute('data-delay');
        if (delay) entry.target.style.animationDelay = (parseInt(delay, 10) / 1000) + 's';
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0, rootMargin: '0px 0px -10% 0px' });

  targets.forEach(function (el) { observer.observe(el); });
})();


/**
 * Main FAQ Accordion (단일 펼침)
 * - 클릭 시 다른 항목 자동 닫힘
 * - aria-expanded + aria-hidden 동기화
 */
(function () {
  'use strict';

  var faq = document.getElementById('cmMainFaq');
  if (!faq) return;

  var triggers = faq.querySelectorAll('.main-faq-trigger');

  var items = faq.querySelectorAll('.main-faq-item');

  triggers.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var isOpen = btn.getAttribute('aria-expanded') === 'true';
      var thisItem = btn.closest('.main-faq-item');

      // 모두 닫기 (단일 펼침 모드)
      items.forEach(function (it) {
        it.classList.remove('is-open');
        var b = it.querySelector('.main-faq-trigger');
        if (b) b.setAttribute('aria-expanded', 'false');
        var pid = b ? b.getAttribute('aria-controls') : null;
        var panel = pid ? document.getElementById(pid) : null;
        if (panel) panel.setAttribute('aria-hidden', 'true');
      });

      // 클릭한 항목이 닫혀있던 경우만 열기
      if (!isOpen) {
        if (thisItem) thisItem.classList.add('is-open');
        btn.setAttribute('aria-expanded', 'true');
        var panel = document.getElementById(btn.getAttribute('aria-controls'));
        if (panel) panel.setAttribute('aria-hidden', 'false');
      }
    });
  });
})();


/**
 * Main Contact — 통계 디지털 롤링(IntersectionObserver) + 약관 모달
 */
(function () {
  'use strict';

  // ── 통계 디지털 롤링 (.main-contact-con 에 .is-animated 토글) ──
  var contactSection = document.querySelector('.main-contact-con');
  if (contactSection) {
    var statsObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        contactSection.classList.add('is-animated');
        statsObserver.disconnect();
      });
    }, { threshold: 0.25 });
    statsObserver.observe(document.getElementById('cmContactStats') || contactSection);
  }

  // ── 약관 동의: 폼 제출 버튼 위로 이동 ──
  var agree = document.querySelector('.main-contact-form-area .main-contact-agree');
  var inquiryForm = document.querySelector('.main-contact-form-area .uw-inquiry-form');
  if (agree && inquiryForm) {
    var actions = inquiryForm.querySelector('.uw-inquiry-actions');
    if (actions) {
      inquiryForm.insertBefore(agree, actions);
    } else {
      inquiryForm.appendChild(agree);
    }
  }

  // ── 약관 모달 ──
  var modal = document.getElementById('cmContactModal');
  var openBtn = document.getElementById('cmContactAgreeBtn');
  if (!modal || !openBtn) return;

  function openModal() {
    var scrollbarW = window.innerWidth - document.documentElement.clientWidth;
    if (scrollbarW > 0) {
      document.body.style.paddingRight = scrollbarW + 'px';
    }
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
  }
  function closeModal() {
    modal.hidden = true;
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
  }

  openBtn.addEventListener('click', openModal);
  modal.querySelectorAll('[data-modal-close]').forEach(function (el) {
    el.addEventListener('click', closeModal);
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.hidden) closeModal();
  });
})();


/**
 * Main Contact — 첫 프로젝트 유형 라디오 기본 체크
 */
(function () {
  'use strict';
  var formArea = document.querySelector('.main-contact-form-area');
  if (!formArea) return;
  var firstProjectRadio = formArea.querySelector('input[name="field_project_type"]');
  if (firstProjectRadio) firstProjectRadio.checked = true;
})();


/**
 * Multi-file 첨부 (uw_inquiry 다중 파일 옵션)
 *
 * - 박스 클릭 시 파일 선택 → 기존 목록에 누적
 * - max-files 초과 / max-filesize 초과 시 alert
 * - 각 항목 X 버튼으로 개별 삭제
 * - DataTransfer 로 input.files 동기화 → 폼 submit 시 그대로 전송
 */
(function () {
  'use strict';

  var wrappers = document.querySelectorAll('[data-multifile]');
  if (!wrappers.length) return;

  // 서버측 허용 MIME과 정합 — 확장자 기반 사전 거부 (UX 개선)
  var ALLOWED_EXT = [
    // 이미지
    'jpg','jpeg','png','gif','webp','bmp','svg','heic','heif','tiff','tif',
    // 문서
    'pdf','doc','docx','xls','xlsx','ppt','pptx','hwp','hwpx','txt','csv','rtf',
    // 압축
    'zip','rar','7z',
    // 디자인 원본
    'ai','psd'
  ];

  function getExt(name) {
    var idx = name.lastIndexOf('.');
    return idx >= 0 ? name.slice(idx + 1).toLowerCase() : '';
  }

  function formatBytes(bytes) {
    if (!bytes) return '0 B';
    var k = 1024;
    var sizes = ['B', 'KB', 'MB', 'GB'];
    var i = Math.min(Math.floor(Math.log(bytes) / Math.log(k)), sizes.length - 1);
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
  }

  wrappers.forEach(function (wrap) {
    var input    = wrap.querySelector('.uw-multifile-input');
    var btn      = wrap.querySelector('.uw-multifile-btn');
    var list     = wrap.querySelector('.uw-multifile-list');
    var maxFiles = parseInt(wrap.getAttribute('data-max-files'), 10) || 1;
    var maxSize  = parseInt(wrap.getAttribute('data-max-filesize'), 10) || (10 * 1024 * 1024);
    if (!input || !btn || !list) return;

    var pending = []; // 누적된 File

    function syncInput() {
      try {
        var dt = new DataTransfer();
        pending.forEach(function (f) { dt.items.add(f); });
        input.files = dt.files;
      } catch (e) {
        // DataTransfer 미지원 환경 (구형 IE 등) — 무시
      }
    }

    function render() {
      list.innerHTML = '';
      if (pending.length === 0) {
        list.hidden = true;
        btn.disabled = false;
        return;
      }
      list.hidden = false;
      pending.forEach(function (f, idx) {
        var li = document.createElement('li');
        li.className = 'uw-multifile-item';
        li.innerHTML =
          '<span class="uw-multifile-item-name"></span>' +
          '<span class="uw-multifile-item-size"></span>' +
          '<button type="button" class="uw-multifile-item-remove" aria-label="파일 삭제">&times;</button>';
        li.querySelector('.uw-multifile-item-name').textContent = f.name;
        li.querySelector('.uw-multifile-item-size').textContent = formatBytes(f.size);
        li.querySelector('.uw-multifile-item-remove').addEventListener('click', function (e) {
          e.preventDefault();
          pending.splice(idx, 1);
          syncInput();
          render();
        });
        list.appendChild(li);
      });
      btn.disabled = pending.length >= maxFiles;
    }

    btn.addEventListener('click', function (e) {
      e.preventDefault();
      if (pending.length >= maxFiles) {
        alert('첨부파일은 최대 ' + maxFiles + '개까지 가능합니다.');
        return;
      }
      input.click();
    });

    input.addEventListener('change', function () {
      var picked = Array.from(input.files || []);
      var slotsLeft = maxFiles - pending.length;
      if (slotsLeft <= 0) {
        alert('첨부파일은 최대 ' + maxFiles + '개까지 가능합니다.');
        return;
      }

      var rejectedSize = [];
      var rejectedExt  = [];
      var rejectedDup  = 0;
      var added = 0;

      for (var i = 0; i < picked.length; i++) {
        if (added >= slotsLeft) break;
        var f = picked[i];
        var ext = getExt(f.name);
        if (ALLOWED_EXT.indexOf(ext) === -1) { rejectedExt.push(f.name); continue; }
        if (f.size > maxSize) { rejectedSize.push(f.name); continue; }
        var dup = pending.some(function (p) { return p.name === f.name && p.size === f.size; });
        if (dup) { rejectedDup++; continue; }
        pending.push(f);
        added++;
      }

      if (rejectedExt.length) {
        alert('지원하지 않는 파일 형식입니다.\n허용: 이미지(jpg/png/gif/webp/bmp/svg/heic/tiff), PDF, Word(doc/docx), Excel(xls/xlsx), PowerPoint(ppt/pptx), 한글(hwp/hwpx), 텍스트(txt/csv/rtf), 압축(zip/rar/7z), 디자인 원본(ai/psd)\n\n거부: ' + rejectedExt.join(', '));
      }
      if (rejectedSize.length) {
        alert('파일당 최대 ' + Math.round(maxSize / 1024 / 1024) + 'MB까지 업로드 가능합니다.\n\n거부: ' + rejectedSize.join(', '));
      }
      if (picked.length > slotsLeft && added > 0) {
        alert('첨부파일은 최대 ' + maxFiles + '개까지 가능합니다.');
      }

      syncInput();
      render();
    });

    // 폼 reset 시 누적 초기화
    var form = wrap.closest('form');
    if (form) {
      form.addEventListener('reset', function () {
        pending = [];
        syncInput();
        render();
      });
    }

    render();
  });
})();


/* ==========================================================================
   메인 contact 폼 — '문의 내용' textarea 양식 템플릿 강제 주입
   어드민 placeholder는 무시. hardcoded 양식 텍스트가 항상 우선.
   placeholder는 첫 글자 입력 시 사라지므로 양식 가이드 역할 못함 →
   실제 value로 박아 사용자가 항목별로 채워넣을 수 있게.
   ========================================================================== */
(function () {
  'use strict';

  var INQUIRY_TEMPLATE = '기존 사이트 URL(보유시) : \n참고 사이트 URL : \n문의 내용 : \n';

  function init() {
    var textareas = document.querySelectorAll('.main-contact-form-area .uw-inquiry-field-textarea');
    textareas.forEach(function (ta) {
      if (ta.value && ta.value.trim() !== '') return; // 이미 사용자가 값 입력했으면 건드리지 X
      ta.value = INQUIRY_TEMPLATE;
      ta.removeAttribute('placeholder');
    });

    // 라벨 텍스트로 필드 식별 → placeholder 강제 (어드민 의존 X)
    var fields = document.querySelectorAll('.main-contact-form-area .uw-inquiry-field');
    fields.forEach(function (f) {
      var label = f.querySelector('.uw-inquiry-field-label');
      if (!label) return;
      var labelText = label.textContent || '';
      var input = f.querySelector('.uw-inquiry-field-input:not(.uw-inquiry-field-textarea):not(.uw-inquiry-field-select)');
      if (!input) return;
      if (/예산/.test(labelText)) {
        input.setAttribute('placeholder', '예: 500만원/미정');
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();


/* ==========================================================================
   sub-faq — 카테고리 탭 필터 + 아코디언 (page-faq.php)
   아코디언: 메인 main-faq 와 동일 패턴 (단일 펼침 + grid 0fr→1fr)
   ========================================================================== */
(function () {
  'use strict';

  function closeAll(items) {
    items.forEach(function (it) {
      it.classList.remove('is-open');
      var b = it.querySelector('.sub-faq-trigger');
      if (b) {
        b.setAttribute('aria-expanded', 'false');
        var pid = b.getAttribute('aria-controls');
        var panel = pid ? document.getElementById(pid) : null;
        if (panel) panel.setAttribute('aria-hidden', 'true');
      }
    });
  }

  function init() {
    var con = document.querySelector('.sub-faq-con');
    if (!con) return;

    // 탭 필터
    var tabs = con.querySelectorAll('.sub-faq-tab');
    var items = con.querySelectorAll('.sub-faq-item');

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var target = tab.getAttribute('data-target') || 'all';

        tabs.forEach(function (t) {
          var active = t === tab;
          t.classList.toggle('is-active', active);
          t.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        if (target === 'all') {
          con.removeAttribute('data-filter');
        } else {
          con.setAttribute('data-filter', target);
        }

        // 필터 전환 시 열려있던 아코디언 닫기 (잔상 방지)
        closeAll(items);
      });
    });

    // 아코디언 토글 (단일 펼침)
    var triggers = con.querySelectorAll('.sub-faq-trigger');
    triggers.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var isOpen = btn.getAttribute('aria-expanded') === 'true';
        var thisItem = btn.closest('.sub-faq-item');

        closeAll(items);

        if (!isOpen && thisItem) {
          thisItem.classList.add('is-open');
          btn.setAttribute('aria-expanded', 'true');
          var panel = document.getElementById(btn.getAttribute('aria-controls'));
          if (panel) panel.setAttribute('aria-hidden', 'false');
        }
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();




/* ==========================================================================
   blog single — 읽기 진행 바 + TOC 활성 항목
   ========================================================================== */
(function () {
  'use strict';

  function init() {
    var body = document.querySelector('.blog-single-body');
    var bar  = document.querySelector('.blog-reading-progress-bar');
    var tocLinks = document.querySelectorAll('.blog-single-toc-list a[data-toc-link]');
    if (!body) return;

    // 진행 바
    function updateProgress() {
      if (!bar) return;
      var rect = body.getBoundingClientRect();
      var viewH = window.innerHeight;
      var startY = rect.top + window.scrollY - viewH * 0.2;
      var endY   = rect.top + window.scrollY + rect.height - viewH * 0.5;
      var current = window.scrollY;
      var ratio = 0;
      if (endY > startY) {
        ratio = (current - startY) / (endY - startY);
      }
      ratio = Math.max(0, Math.min(1, ratio));
      bar.style.width = (ratio * 100) + '%';
    }

    // TOC scrollspy
    var headings = [];
    if (tocLinks.length) {
      tocLinks.forEach(function (l) {
        var id = l.getAttribute('data-toc-link');
        var el = id ? document.getElementById(id) : null;
        if (el) headings.push({ link: l, el: el });
      });
    }

    function updateTocActive() {
      if (!headings.length) return;
      var offset = 120;
      var activeIdx = 0;
      for (var i = 0; i < headings.length; i++) {
        var top = headings[i].el.getBoundingClientRect().top;
        if (top - offset <= 0) {
          activeIdx = i;
        } else {
          break;
        }
      }
      headings.forEach(function (h, i) {
        h.link.classList.toggle('is-active', i === activeIdx);
      });
    }

    var ticking = false;
    function onScroll() {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(function () {
        updateProgress();
        updateTocActive();
        ticking = false;
      });
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    updateProgress();
    updateTocActive();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();


/**
 * Portfolio Popup — 카드(.js-port-card) 클릭 시 통합 팝업 오픈
 * 데이터는 카드의 data-port-* 속성에서 읽어 채운다.
 * 데이터가 없으면 가로채지 않고 single 페이지(/portfolio/{slug}/)로 폴백 — SEO/직접접근 유지.
 */
(function () {
  'use strict';

  var modal = document.getElementById('cmPortModal');
  if (!modal) return;

  var cards = document.querySelectorAll('.js-port-card');
  if (!cards.length) return;

  var elTit     = modal.querySelector('.port-modal-tit');
  var elUrlWrap = modal.querySelector('.port-modal-url');
  var elUrlLink = modal.querySelector('.port-modal-url-link');
  var elIndustry = modal.querySelector('.port-modal-meta-industry');
  var elWork     = modal.querySelector('.port-modal-meta-work');
  var elType     = modal.querySelector('.port-modal-meta-type');
  var elVisit   = modal.querySelector('.port-modal-visit');
  var elCapWrap = modal.querySelector('.port-modal-capture');
  var elCapImg  = modal.querySelector('.port-modal-capture-img');
  var elBody    = modal.querySelector('.port-modal-body');

  function setMeta(wrap, value) {
    if (!wrap) return;
    var dd = wrap.querySelector('dd');
    if (value) {
      if (dd) dd.textContent = value;
      wrap.hidden = false;
    } else {
      wrap.hidden = true;
    }
  }

  function fill(card) {
    var d = card.dataset;
    elTit.textContent = d.portTitle || '';

    if (d.portUrl) {
      elUrlLink.textContent = d.portUrl;
      elUrlLink.href = d.portUrl;
      elUrlWrap.hidden = false;
    } else {
      elUrlWrap.hidden = true;
    }

    setMeta(elIndustry, d.portIndustry);
    setMeta(elWork, d.portWork);
    setMeta(elType, d.portType);

    if (d.portUrl) {
      elVisit.href = d.portUrl;
      elVisit.hidden = false;
    } else {
      elVisit.hidden = true;
    }

    // data-port-image 는 콤마 구분 다중 URL 지원 (순서대로 세로 노출)
    var imgs = (d.portImage || '').split(',').map(function (u) { return u.trim(); }).filter(Boolean);
    elCapWrap.innerHTML = '';
    if (imgs.length) {
      imgs.forEach(function (src, i) {
        var img = document.createElement('img');
        img.className = 'port-modal-capture-img';
        img.src = src;
        img.alt = (d.portTitle || '') + ' 상세 화면' + (imgs.length > 1 ? ' ' + (i + 1) : '');
        img.loading = 'lazy';
        elCapWrap.appendChild(img);
      });
      elCapWrap.hidden = false;
    } else {
      elCapWrap.hidden = true;
    }
  }

  function hasContent(card) {
    var d = card.dataset;
    return !!(d.portImage || d.portUrl || d.portType || d.portOption || d.portPrice || d.portTags);
  }

  function openModal(card) {
    fill(card);
    var scrollbarW = window.innerWidth - document.documentElement.clientWidth;
    if (scrollbarW > 0) document.body.style.paddingRight = scrollbarW + 'px';
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    if (elBody) elBody.scrollTop = 0;
  }

  function closeModal() {
    modal.hidden = true;
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
  }

  cards.forEach(function (card) {
    card.addEventListener('click', function (e) {
      if (!hasContent(card)) return;
      e.preventDefault();
      openModal(card);
    });

    // 모바일 목업은 호버 시에만 로드 (초기 로드에서 제외 → 요청수·용량 절감)
    var mob = card.querySelector('.main-portfolio-mob-img[data-src]');
    if (mob) {
      var loadMob = function () {
        mob.src = mob.getAttribute('data-src');
        mob.removeAttribute('data-src');
        card.removeEventListener('mouseenter', loadMob);
      };
      card.addEventListener('mouseenter', loadMob);
    }
  });

  modal.querySelectorAll('[data-modal-close]').forEach(function (el) {
    el.addEventListener('click', closeModal);
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.hidden) closeModal();
  });
})();
