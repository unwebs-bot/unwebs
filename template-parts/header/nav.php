<?php
/**
 * Header Navigation
 *
 * @uses starter_nav() from inc/config.php
 */

$nav = starter_nav();
$current_section = starter_current_nav_section();
?>

<div class="cm-header-wrap">
  <div class="cm-header-inner">

    <!-- Logo (H1 아님 — 페이지 고유 H1은 각 페이지 본문이 소유) -->
    <div class="cm-header-logo">
      <a href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php bloginfo('name'); ?> 홈">
        <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/common/logo.png')); ?>" alt="<?php bloginfo('name'); ?>">
      </a>
    </div>

    <!-- GNB (PC Navigation) -->
    <nav class="cm-gnb" aria-label="메인 메뉴">
      <ul class="cm-gnb-list">
        <?php foreach ($nav as $key => $menu) :
          // 문의하기는 우측 CTA 버튼과 중복이라 GNB에서 숨김
          if ($key === 'contact') continue;
        ?>
        <li class="cm-gnb-item<?php echo ($key === $current_section) ? ' is-active' : ''; ?>">
          <a href="<?php echo esc_url(home_url($menu['url'])); ?>" class="cm-gnb-link">
            <?php echo esc_html($menu['label']); ?><?php if (!empty($menu['items'])) : ?><i class="xi-angle-down cm-gnb-arrow"></i><?php endif; ?>
          </a>
          <?php if (!empty($menu['items'])) : ?>
          <div class="cm-gnb-sub">
            <ul class="cm-gnb-sub-list">
              <?php foreach ($menu['items'] as $item) : ?>
              <li class="cm-gnb-sub-item">
                <?php
                  $sub_url = (!empty($item['target']) && $item['target'] === '_blank') ? $item['slug'] : home_url($item['slug']);
                  $sub_target = (!empty($item['target'])) ? ' target="' . esc_attr($item['target']) . '" rel="noopener noreferrer"' : '';
                ?>
                <a href="<?php echo esc_url($sub_url); ?>" class="cm-gnb-sub-link"<?php echo $sub_target; ?>>
                  <?php echo esc_html($item['label']); ?><?php if (!empty($item['target']) && $item['target'] === '_blank') : ?> <i class="xi-external-link cm-gnb-sub-external"></i><?php endif; ?>
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
    <div class="cm-header-cta">
      <?php /* 준비중 - 샘플 사이트 체험 버튼
      <a href="<?php echo esc_url(home_url('/sample/')); ?>" class="cm-header-btn cm-header-btn-outline" target="_blank" rel="noopener noreferrer"><i class="xi-external-link"></i> 샘플 사이트 체험</a>
      */ ?>
      <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="cm-header-btn cm-header-btn-primary">
        <i class="xi-border-color"></i> 프로젝트 문의
        <span class="cm-header-tooltip"><strong>3분 문의접수</strong>로 무료 상담을 받아보세요</span>
      </a>
    </div>

  </div>
</div>
