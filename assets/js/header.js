/**
 * Header System
 *
 * Features:
 * - Scroll detection (header background change)
 * - GNB dropdown (PC - each menu style)
 * - Sitemap overlay (PC fullscreen menu)
 * - Mobile navigation (slide-in accordion menu)
 */

(function () {
  'use strict';

  // ==========================================================================
  // DOM Elements
  // ==========================================================================
  const header = document.querySelector('.cm-header');
  const gnbItems = document.querySelectorAll('.cm-gnb-item');
  const sitemapBtn = document.querySelector('.cm-header-sitemap-btn');
  const sitemap = document.querySelector('.cm-sitemap');
  const sitemapCols = document.querySelectorAll('.cm-sitemap-col');
  const sitemapLinks = document.querySelectorAll('.cm-sitemap-link');

  // Mobile Navigation Elements
  const mobileBtn = document.querySelector('.cm-mobile-btn');
  const mobileNav = document.querySelector('.cm-mobile-nav');
  const mobileOverlay = document.querySelector('.cm-mobile-overlay');
  const mobileAccordionTriggers = document.querySelectorAll('.cm-mobile-nav-trigger');

  // Scroll position for scroll lock
  let scrollPosition = 0;

  // ==========================================================================
  // 1. Scroll Detection
  // ==========================================================================
  function handleScroll() {
    if (!header) return;

    const scrollY = window.scrollY || window.pageYOffset;
    const isSitemapOpen = document.body.classList.contains('sitemap-open');
    const isMobileOpen = document.body.classList.contains('mobile-menu-open');

    if (scrollY > 50) {
      header.classList.add('is-scrolled');
    } else {
      // Don't remove scrolled state if overlay menus are open
      if (!isSitemapOpen && !isMobileOpen) {
        header.classList.remove('is-scrolled');
      }
    }
  }

  // ==========================================================================
  // 2. GNB Dropdown (PC - Each Menu Style)
  // ==========================================================================
  function initGnbDropdown() {
    gnbItems.forEach((item) => {
      const subMenu = item.querySelector('.cm-gnb-sub');

      if (subMenu) {
        item.addEventListener('mouseenter', () => {
          subMenu.classList.add('is-open');
        });

        item.addEventListener('mouseleave', () => {
          subMenu.classList.remove('is-open');
        });

        // Keyboard accessibility
        const link = item.querySelector('.cm-gnb-link');
        if (link) {
          link.addEventListener('focus', () => {
            subMenu.classList.add('is-open');
          });
        }

        // Close on blur from last submenu item
        const lastSubLink = subMenu.querySelector('.cm-gnb-sub-item:last-child .cm-gnb-sub-link');
        if (lastSubLink) {
          lastSubLink.addEventListener('blur', () => {
            subMenu.classList.remove('is-open');
          });
        }
      }
    });
  }

  // ==========================================================================
  // 3. Sitemap Overlay (PC Fullscreen Menu)
  // ==========================================================================
  function toggleSitemap() {
    document.body.classList.toggle('sitemap-open');

    if (document.body.classList.contains('sitemap-open')) {
      header.classList.add('is-scrolled');
      // Trap focus in sitemap
      if (sitemap) {
        const firstLink = sitemap.querySelector('.cm-sitemap-link');
        if (firstLink) {
          setTimeout(() => firstLink.focus(), 600);
        }
      }
    } else {
      if (window.scrollY <= 50) {
        header.classList.remove('is-scrolled');
      }
      // Return focus to sitemap button
      if (sitemapBtn) {
        sitemapBtn.focus();
      }
    }
  }

  function closeSitemap() {
    document.body.classList.remove('sitemap-open');
    if (window.scrollY <= 50) {
      header.classList.remove('is-scrolled');
    }
  }

  function initSitemap() {
    // Toggle button
    if (sitemapBtn) {
      sitemapBtn.addEventListener('click', toggleSitemap);
    }

    // Hover effect on sitemap links
    sitemapLinks.forEach((link) => {
      link.addEventListener('mouseenter', () => {
        const parentCol = link.closest('.cm-sitemap-col');
        if (parentCol) {
          parentCol.classList.add('is-active');
        }
      });

      link.addEventListener('mouseleave', () => {
        sitemapCols.forEach((col) => col.classList.remove('is-active'));
      });
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && document.body.classList.contains('sitemap-open')) {
        closeSitemap();
      }
    });
  }

  // ==========================================================================
  // 4. Mobile Navigation (Slide-in Accordion)
  // ==========================================================================

  /**
   * Open mobile navigation
   */
  function openMobileNav() {
    // 이미 열린 상태에서 재호출 시 scrollPosition을 0(fixed top)으로 덮어써
    // close 시 맨 위로 스크롤되는 버그 방지
    if (document.body.classList.contains('mobile-menu-open')) return;

    // Save scroll position before locking
    scrollPosition = window.pageYOffset;

    document.body.classList.add('mobile-menu-open');
    document.body.style.top = `-${scrollPosition}px`;

    if (mobileNav) {
      mobileNav.setAttribute('aria-hidden', 'false');
    }
    if (mobileOverlay) {
      mobileOverlay.setAttribute('aria-hidden', 'false');
    }
    if (mobileBtn) {
      mobileBtn.setAttribute('aria-expanded', 'true');
    }

    // Focus first menu item for accessibility
    if (mobileNav) {
      const firstFocusable = mobileNav.querySelector('a, button');
      if (firstFocusable) {
        setTimeout(() => firstFocusable.focus(), 400);
      }
    }
  }

  /**
   * Close mobile navigation
   */
  function closeMobileNav() {
    document.body.classList.remove('mobile-menu-open');
    document.body.style.top = '';

    // Restore scroll position
    // html { scroll-behavior: smooth } cascade로 scrollTo가 부드럽게 동작 →
    // 닫을 때 scrollY 0에서 원래 위치까지 애니메이션되어 "맨 위로 갔다 돌아온다"는 인상.
    // 임시로 'auto'로 박아서 즉시 복원.
    const html = document.documentElement;
    const prevBehavior = html.style.scrollBehavior;
    html.style.scrollBehavior = 'auto';
    window.scrollTo(0, scrollPosition);
    html.style.scrollBehavior = prevBehavior;

    if (mobileNav) {
      mobileNav.setAttribute('aria-hidden', 'true');
    }
    if (mobileOverlay) {
      mobileOverlay.setAttribute('aria-hidden', 'true');
    }
    if (mobileBtn) {
      mobileBtn.setAttribute('aria-expanded', 'false');
      mobileBtn.focus();
    }

    // Close all accordion items
    closeAllAccordions();
  }

  /**
   * Toggle mobile navigation
   */
  function toggleMobileNav() {
    if (document.body.classList.contains('mobile-menu-open')) {
      closeMobileNav();
    } else {
      openMobileNav();
    }
  }

  /**
   * Close all accordion panels
   */
  function closeAllAccordions() {
    const items = document.querySelectorAll('.cm-mobile-nav-item');
    items.forEach((item) => {
      item.classList.remove('is-open');
      const trigger = item.querySelector('.cm-mobile-nav-trigger');
      const panel = item.querySelector('.cm-mobile-nav-panel');
      if (trigger) {
        trigger.setAttribute('aria-expanded', 'false');
      }
      if (panel) {
        panel.setAttribute('aria-hidden', 'true');
      }
    });
  }

  /**
   * Toggle accordion panel (single expand mode)
   * @param {HTMLElement} trigger - The clicked trigger button
   */
  function toggleAccordion(trigger) {
    const item = trigger.closest('.cm-mobile-nav-item');
    const panel = item.querySelector('.cm-mobile-nav-panel');
    const isOpen = item.classList.contains('is-open');

    // Close all other accordions first (single expand mode)
    closeAllAccordions();

    // Toggle current accordion
    if (!isOpen) {
      item.classList.add('is-open');
      trigger.setAttribute('aria-expanded', 'true');
      if (panel) {
        panel.setAttribute('aria-hidden', 'false');
      }
    }
  }

  /**
   * Initialize mobile navigation
   */
  function initMobileNav() {
    // Toggle button (hamburger)
    if (mobileBtn) {
      mobileBtn.addEventListener('click', toggleMobileNav);
    }

    // Overlay click to close
    if (mobileOverlay) {
      mobileOverlay.addEventListener('click', closeMobileNav);
    }

    // Accordion triggers
    mobileAccordionTriggers.forEach((trigger) => {
      trigger.addEventListener('click', () => {
        toggleAccordion(trigger);
      });
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && document.body.classList.contains('mobile-menu-open')) {
        closeMobileNav();
      }
    });

    // Handle resize - close mobile nav when switching to desktop
    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        if (window.innerWidth > 1024 && document.body.classList.contains('mobile-menu-open')) {
          closeMobileNav();
        }
      }, 250);
    });
  }

  // ==========================================================================
  // 5. Initialize
  // ==========================================================================
  function init() {
    // Scroll handler
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll(); // Initial check

    // GNB Dropdown
    initGnbDropdown();

    // Sitemap
    initSitemap();

    // Mobile Navigation
    initMobileNav();
  }

  // Run on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // ==========================================================================
  // 6. Public API (for external access if needed)
  // ==========================================================================
  window.cmHeader = {
    toggleSitemap: toggleSitemap,
    closeSitemap: closeSitemap,
    openMobileNav: openMobileNav,
    closeMobileNav: closeMobileNav,
    toggleMobileNav: toggleMobileNav,
    toggleAccordion: toggleAccordion,
    closeAllAccordions: closeAllAccordions
  };

})();
