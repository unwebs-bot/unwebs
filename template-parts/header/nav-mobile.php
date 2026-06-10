<?php
/**
 * Mobile Navigation - Slide-in Accordion Menu
 *
 * @uses starter_nav() from inc/config.php
 */

$nav = starter_nav();
$current_section = starter_current_nav_section();
?>

<!-- Mobile Menu Button (Dot Grid - same as PC) -->
<button class="cm-mobile-btn" type="button" aria-label="메뉴 열기" aria-expanded="false" aria-controls="cmMobileMenu">
  <span class="cm-mobile-btn-dotted">
    <span class="cm-mobile-btn-dot"></span>
    <span class="cm-mobile-btn-dot"></span>
    <span class="cm-mobile-btn-dot"></span>
    <span class="cm-mobile-btn-dot"></span>
  </span>
</button>

<!-- Mobile Menu Overlay -->
<div class="cm-mobile-overlay" aria-hidden="true"></div>

<!-- Mobile Navigation Panel -->
<nav class="cm-mobile-nav" id="cmMobileMenu" aria-label="모바일 메뉴" aria-hidden="true">

  <!-- Mobile Nav Header -->
  <div class="cm-mobile-nav-header">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="cm-mobile-nav-logo">
      <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/common/logo.png')); ?>" alt="<?php bloginfo('name'); ?>">
    </a>
  </div>

  <!-- Mobile Nav Content -->
  <div class="cm-mobile-nav-content">
    <ul class="cm-mobile-nav-list">
      <?php foreach ($nav as $key => $menu) : ?>
      <li class="cm-mobile-nav-item<?php echo ($key === $current_section) ? ' is-active' : ''; ?>" data-menu="<?php echo esc_attr($key); ?>">

        <?php if (!empty($menu['items'])) : ?>
        <!-- Accordion Header (with submenu) -->
        <button class="cm-mobile-nav-trigger" type="button" aria-expanded="false">
          <span class="cm-mobile-nav-tit"><?php echo esc_html($menu['label']); ?></span>
          <!-- SVG Toggle Icon -->
          <span class="cm-mobile-nav-icon">
            <svg class="cm-icon cm-icon-plus" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <svg class="cm-icon cm-icon-minus" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
          </span>
        </button>

        <!-- Accordion Content -->
        <div class="cm-mobile-nav-panel" aria-hidden="true">
          <ul class="cm-mobile-nav-sub-list">
            <?php foreach ($menu['items'] as $item) : ?>
            <li>
              <a href="<?php echo esc_url(home_url($item['slug'])); ?>" class="cm-mobile-nav-sub-link">
                <?php echo esc_html($item['label']); ?>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <?php else : ?>
        <!-- Direct Link (no submenu) -->
        <a href="<?php echo esc_url(home_url($menu['url'])); ?>" class="cm-mobile-nav-link">
          <span class="cm-mobile-nav-tit"><?php echo esc_html($menu['label']); ?></span>
        </a>
        <?php endif; ?>

      </li>
      <?php endforeach; ?>
    </ul>
  </div>

</nav>
