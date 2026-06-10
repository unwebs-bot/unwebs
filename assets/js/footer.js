/**
 * Footer System
 *
 * Features:
 * - Legal modal (Privacy Policy, Terms of Service)
 * - Modal open/close with animation
 * - Keyboard accessibility (Escape to close)
 * - Focus trap within modal
 */

(function () {
  'use strict';

  // ==========================================================================
  // DOM Elements
  // ==========================================================================
  const modalOverlay = document.querySelector('.cm-modal-overlay');
  const modal = document.querySelector('.cm-modal');
  const modalTitle = document.querySelector('.cm-modal-tit');
  const modalContent = document.querySelector('.cm-modal-content');
  const modalCloseBtn = document.querySelector('.cm-modal-close');
  const privacyBtn = document.querySelector('[data-modal="privacy"]');
  const termsBtn = document.querySelector('[data-modal="terms"]');

  // Store the element that opened the modal for focus return
  let lastFocusedElement = null;

  // Calculate scrollbar width
  const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;

  // ==========================================================================
  // Modal Functions
  // ==========================================================================

  /**
   * Open modal with content
   * @param {string} type - 'privacy' or 'terms'
   */
  function openModal(type) {
    if (!modalOverlay || !modal) return;

    // Store the element that triggered the modal
    lastFocusedElement = document.activeElement;

    // Set content based on type
    const content = document.querySelector(`#${type}Content`);
    if (content && modalContent) {
      modalContent.innerHTML = content.innerHTML;
    }

    // Set title
    if (modalTitle) {
      modalTitle.textContent = type === 'privacy' ? '개인정보처리방침' : '이용약관';
    }

    // Show modal (prevent layout shift)
    document.body.style.paddingRight = scrollbarWidth + 'px';
    document.body.classList.add('modal-open');
    modalOverlay.classList.add('is-open');

    // Focus the close button for accessibility
    if (modalCloseBtn) {
      setTimeout(() => modalCloseBtn.focus(), 100);
    }
  }

  /**
   * Close modal
   */
  function closeModal() {
    if (!modalOverlay) return;

    document.body.style.paddingRight = '';
    document.body.classList.remove('modal-open');
    modalOverlay.classList.remove('is-open');

    // Return focus to the element that opened the modal
    if (lastFocusedElement) {
      lastFocusedElement.focus();
    }
  }

  /**
   * Handle click outside modal to close
   * @param {Event} e
   */
  function handleOverlayClick(e) {
    if (e.target === modalOverlay) {
      closeModal();
    }
  }

  /**
   * Handle keyboard events
   * @param {KeyboardEvent} e
   */
  function handleKeydown(e) {
    if (!modalOverlay || !modalOverlay.classList.contains('is-open')) return;

    // Close on Escape
    if (e.key === 'Escape') {
      closeModal();
      return;
    }

    // Trap focus within modal
    if (e.key === 'Tab') {
      trapFocus(e);
    }
  }

  /**
   * Trap focus within modal
   * @param {KeyboardEvent} e
   */
  function trapFocus(e) {
    if (!modal) return;

    const focusableElements = modal.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );

    const firstFocusable = focusableElements[0];
    const lastFocusable = focusableElements[focusableElements.length - 1];

    if (e.shiftKey) {
      // Shift + Tab
      if (document.activeElement === firstFocusable) {
        lastFocusable.focus();
        e.preventDefault();
      }
    } else {
      // Tab
      if (document.activeElement === lastFocusable) {
        firstFocusable.focus();
        e.preventDefault();
      }
    }
  }

  // ==========================================================================
  // Initialize
  // ==========================================================================
  function init() {
    // Privacy button
    if (privacyBtn) {
      privacyBtn.addEventListener('click', () => openModal('privacy'));
    }

    // Terms button
    if (termsBtn) {
      termsBtn.addEventListener('click', () => openModal('terms'));
    }

    // Close button
    if (modalCloseBtn) {
      modalCloseBtn.addEventListener('click', closeModal);
    }

    // Overlay click to close
    if (modalOverlay) {
      modalOverlay.addEventListener('click', handleOverlayClick);
    }

    // Keyboard events
    document.addEventListener('keydown', handleKeydown);
  }

  // Run on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // ==========================================================================
  // Public API
  // ==========================================================================
  window.cmFooter = {
    openModal: openModal,
    closeModal: closeModal
  };

})();
