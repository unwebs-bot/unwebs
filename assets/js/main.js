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
  const visual = document.querySelector('.uw-visual');
  if (!visual) return;

  const slides = visual.querySelectorAll('.uw-visual__slide');
  const allNavItems = visual.querySelectorAll('.uw-visual__nav-item');
  const scrollBtn = visual.querySelector('.uw-visual__scroll');

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
      '.uw-visual__header, .uw-visual__meta, .uw-visual__actions, .uw-visual__nav, .uw-visual__tablet, .uw-visual__phone, .uw-visual__scroll-img'
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
      var f = nav.querySelector('.uw-visual__nav-fill');
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
        var fill = nav.querySelector('.uw-visual__nav-fill');
        if (fill) {
          fill.offsetHeight; // reflow
          fill.style.width = '';
          fill.style.animation = 'uwVisualProgress ' + (slideInterval / 1000) + 's ease-out forwards';
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
        var fill = nav.querySelector('.uw-visual__nav-fill');
        if (fill) {
          fill.style.animation = 'uwVisualProgress ' + (slideInterval / 1000) + 's ease-out forwards';
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

  // Scroll down button
  if (scrollBtn) {
    scrollBtn.addEventListener('click', function () {
      var nextSection = visual.nextElementSibling;
      if (nextSection) {
        nextSection.scrollIntoView({ behavior: 'smooth' });
      }
    });
  }

  // ==========================================================================
  // Initialize
  // ==========================================================================
  startAutoSlide();

  // Public API
  window.uwVisual = {
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

  var projectList = document.getElementById('uwDashboardProject');
  var consultList = document.getElementById('uwDashboardConsult');

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
 * Maintenance 2-Column Ticker
 *
 * Features:
 * - Desktop: Vertical auto-scrolling ticker (2 columns)
 * - Mobile (<=768px): Horizontal infinite marquee (CSS animation)
 * - Pause on hover
 */
(function () {
  'use strict';

  var GAP = 15;
  var SPEED = 3000;
  var isPaused = false;
  var tickerInterval = null;

  var tickerContainer = document.getElementById('uwMaintenanceTicker');
  if (!tickerContainer) return;

  var columns = tickerContainer.querySelectorAll('.uw-maintenance__list');
  if (columns.length === 0) return;

  function isMobile() {
    return window.innerWidth <= 768;
  }

  // --- Mobile: Clone items for seamless CSS marquee loop ---
  function setupMobileMarquee() {
    columns.forEach(function (list) {
      if (list.dataset.marqueeCloned) return;

      var items = Array.from(list.children);
      items.forEach(function (item) {
        var clone = item.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        list.appendChild(clone);
      });

      list.dataset.marqueeCloned = 'true';
    });
  }

  // --- Desktop: Vertical ticker ---
  function tickColumn(list) {
    if (!list || !list.firstElementChild || isPaused) return;
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

  function startDesktopTicker() {
    if (tickerInterval) return;
    tickerInterval = setInterval(function () {
      if (isPaused) return;
      columns.forEach(function (list) {
        tickColumn(list);
      });
    }, SPEED);
  }

  function stopDesktopTicker() {
    if (tickerInterval) {
      clearInterval(tickerInterval);
      tickerInterval = null;
    }
  }

  // --- Pause on hover ---
  tickerContainer.addEventListener('mouseenter', function () {
    isPaused = true;
  });
  tickerContainer.addEventListener('mouseleave', function () {
    isPaused = false;
  });

  // --- Clear inline styles from desktop ticker ---
  function clearDesktopStyles() {
    columns.forEach(function (list) {
      list.style.transition = '';
      list.style.transform = '';
    });
  }

  // --- Initialize based on viewport ---
  function init() {
    if (isMobile()) {
      stopDesktopTicker();
      clearDesktopStyles();
      setupMobileMarquee();
    } else {
      startDesktopTicker();
    }
  }

  init();

  // Handle resize
  var resizeTimer;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(init, 250);
  });

})();

/**
 * Schedule Process Dashboard
 *
 * - IntersectionObserver triggers bar reveal animation
 * - Hover tooltip on process bars
 */
(function () {
  'use strict';

  var schedule = document.getElementById('uwSchedule');
  if (!schedule) return;

  // Animation trigger
  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        schedule.classList.add('is-animated');
        observer.disconnect();
      }
    });
  }, { threshold: 0.15 });

  observer.observe(schedule);

  // Tooltip
  var tooltip = document.getElementById('uwScheduleTooltip');
  var ttTitle = document.getElementById('uwScheduleTtTitle');
  var ttList = document.getElementById('uwScheduleTtList');
  if (!tooltip || !ttTitle || !ttList) return;

  var colorMap = {
    blue: '#2563eb',
    green: '#059669',
    amber: '#d97706',
    pink: '#db2777',
    indigo: '#4f46e5'
  };

  var bars = schedule.querySelectorAll('.uw-schedule__bar');
  bars.forEach(function (bar) {
    bar.addEventListener('mouseenter', function () {
      var title = bar.getAttribute('data-title');
      var color = bar.getAttribute('data-color');
      var items = bar.getAttribute('data-list').split(',');

      ttTitle.textContent = title;
      ttTitle.style.borderLeftColor = colorMap[color] || '#2563eb';
      ttList.innerHTML = '';
      items.forEach(function (item) {
        var li = document.createElement('li');
        li.textContent = item.trim();
        ttList.appendChild(li);
      });
    });

    bar.addEventListener('mousemove', function (e) {
      tooltip.style.opacity = '1';
      tooltip.style.transform = 'scale(1)';
      tooltip.style.left = (e.clientX + 20) + 'px';
      tooltip.style.top = (e.clientY + 20) + 'px';
    });

    bar.addEventListener('mouseleave', function () {
      tooltip.style.opacity = '0';
      tooltip.style.transform = 'scale(0.95)';
    });
  });
})();

/**
 * Stats Rolling Number Counter
 *
 * - IntersectionObserver triggers digit rolling animation
 */
(function () {
  'use strict';

  var statsSection = document.getElementById('uwStats');
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
