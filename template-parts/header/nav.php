<?php
/**
 * Header Navigation
 *
 * @uses starter_nav() from inc/config.php
 */

$nav = starter_nav();
$current_section = starter_current_nav_section();
?>

<div class="uw-header__wrap">
  <div class="uw-header__inner">

    <!-- Logo -->
    <h1 class="uw-header__logo">
      <a href="<?php echo esc_url(home_url('/')); ?>">
        <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/logo.png')); ?>" alt="<?php bloginfo('name'); ?>">
      </a>
    </h1>

    <!-- GNB (PC Navigation) -->
    <nav class="uw-gnb" aria-label="메인 메뉴">
      <ul class="uw-gnb__list">
        <?php foreach ($nav as $key => $menu) : ?>
        <li class="uw-gnb__item<?php echo ($key === $current_section) ? ' is-active' : ''; ?>">
          <a href="<?php echo esc_url(home_url($menu['url'])); ?>" class="uw-gnb__link">
            <?php echo esc_html($menu['label']); ?><?php if (!empty($menu['items'])) : ?><i class="xi-angle-down uw-gnb__arrow"></i><?php endif; ?>
          </a>
          <?php if (!empty($menu['items'])) : ?>
          <div class="uw-gnb__sub">
            <ul class="uw-gnb__sub-list">
              <?php foreach ($menu['items'] as $item) : ?>
              <li class="uw-gnb__sub-item">
                <?php
                  $sub_url = (!empty($item['target']) && $item['target'] === '_blank') ? $item['slug'] : home_url($item['slug']);
                  $sub_target = (!empty($item['target'])) ? ' target="' . esc_attr($item['target']) . '" rel="noopener noreferrer"' : '';
                ?>
                <a href="<?php echo esc_url($sub_url); ?>" class="uw-gnb__sub-link"<?php echo $sub_target; ?>>
                  <?php echo esc_html($item['label']); ?><?php if (!empty($item['target']) && $item['target'] === '_blank') : ?> <i class="xi-external-link uw-gnb__sub-external"></i><?php endif; ?>
                </a>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <!-- CTA Buttons -->
    <div class="uw-header__cta">
      <?php /* 준비중 - 샘플 사이트 체험 버튼
      <a href="<?php echo esc_url(home_url('/sample/')); ?>" class="uw-header__btn uw-header__btn--outline" target="_blank" rel="noopener noreferrer"><i class="xi-external-link"></i> 샘플 사이트 체험</a>
      */ ?>
      <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="uw-header__btn uw-header__btn--primary">
        <i class="xi-border-color"></i> 프로젝트 문의
        <span class="uw-header__tooltip"><strong>3분 문의접수</strong>로 무료 상담을 받아보세요</span>
      </a>
    </div>

  </div>
</div>

<!-- Sitemap Overlay (PC Fullscreen Menu) -->
<div class="uw-sitemap" aria-hidden="true">
  <div class="uw-sitemap__bg"></div>
  <ul class="uw-sitemap__container">
    <?php foreach ($nav as $key => $menu) : ?>
    <li class="uw-sitemap__col" data-menu="<?php echo esc_attr($key); ?>">
      <h4 class="uw-sitemap__title"><?php echo esc_html($menu['label']); ?></h4>
      <?php if (!empty($menu['items'])) : ?>
      <ul class="uw-sitemap__list">
        <?php foreach ($menu['items'] as $item) : ?>
        <li>
          <a href="<?php echo esc_url(home_url($item['slug'])); ?>" class="uw-sitemap__link">
            <?php echo esc_html($item['label']); ?>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </li>
    <?php endforeach; ?>
  </ul>
</div>
