<?php
/**
 * Footer Modal Template
 *
 * Legal content modal for Privacy Policy and Terms of Service
 *
 * @uses starter_privacy_policy() from inc/config.php
 * @uses starter_terms_of_service() from inc/config.php
 */
?>

<!-- Legal Content Modal -->
<div class="cm-modal-overlay" aria-hidden="true">
  <div class="cm-modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="cm-modal-header">
      <h2 class="cm-modal-tit" id="modalTitle"></h2>
      <button type="button" class="cm-modal-close" aria-label="닫기">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
    <div class="cm-modal-body">
      <div class="cm-modal-content"></div>
    </div>
  </div>
</div>

<!-- Hidden Legal Content Templates -->
<template id="privacyContent">
  <?php echo starter_privacy_policy(); ?>
</template>

<template id="termsContent">
  <?php echo starter_terms_of_service(); ?>
</template>
