<?php
/**
 * Footer Main Template
 *
 * @uses starter_get_config() from inc/config.php
 */

$config  = starter_get_config();
$nav     = $config['nav'];
$company = $config['company'];
?>

<footer class="uw-footer" role="contentinfo">

  <!-- Upper Section: Navigation & CTA -->
  <div class="uw-footer__upper">
    <div class="uw-footer__inner">

      <!-- Left: Navigation Links -->
      <nav class="uw-footer__nav" aria-label="푸터 내비게이션">
        <?php foreach ($nav as $key => $section) : ?>
        <div class="uw-footer__nav-col">
          <h3 class="uw-footer__nav-title">
            <?php if (!empty($section['url']) && $section['url'] !== '#') : ?>
              <a href="<?php echo esc_url(home_url($section['url'])); ?>"><?php echo esc_html($section['label']); ?></a>
            <?php else : ?>
              <?php echo esc_html($section['label']); ?>
            <?php endif; ?>
          </h3>
          <?php if (!empty($section['items'])) : ?>
          <ul class="uw-footer__nav-list">
            <?php foreach ($section['items'] as $item) : ?>
            <li>
              <a href="<?php echo esc_url(
                strpos($item['slug'], 'http') === 0 ? $item['slug'] : home_url($item['slug'])
              ); ?>"<?php if (!empty($item['target'])) echo ' target="' . esc_attr($item['target']) . '" rel="noopener noreferrer"'; ?>>
                <?php echo esc_html($item['label']); ?>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </nav>

      <!-- Right: Logo, CTA & Contact -->
      <div class="uw-footer__cta">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="uw-footer__logo">
          <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/logo.png')); ?>" alt="<?php bloginfo('name'); ?>">
        </a>
        <div class="uw-footer__phone">
          <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $company['tel'])); ?>" class="uw-footer__phone-number">
            <?php echo esc_html($company['tel']); ?>
          </a>
          <?php if (!empty($company['hours'])) : ?>
          <p class="uw-footer__phone-hours"><?php echo esc_html($company['hours']); ?></p>
          <?php endif; ?>
        </div>
        <div class="uw-footer__cta-buttons">
          <a href="#" class="uw-footer__cta-btn">
            <span>회사소개서 다운로드</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
          </a>
          <a href="#" class="uw-footer__cta-btn uw-footer__cta-btn--primary">
            <span>제작의뢰서 다운로드</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
          </a>
        </div>
      </div>

    </div>
  </div>

  <!-- Lower Section: Business Info & Copyright -->
  <div class="uw-footer__lower">
    <div class="uw-footer__inner">

      <div class="uw-footer__info">
        <dl class="uw-footer__info-item">
          <dt>대표</dt>
          <dd><?php echo esc_html($company['ceo']); ?></dd>
        </dl>
        <?php if (!empty($company['email'])) : ?>
        <dl class="uw-footer__info-item">
          <dt>메일</dt>
          <dd><?php echo esc_html($company['email']); ?></dd>
        </dl>
        <?php endif; ?>
        <dl class="uw-footer__info-item">
          <dt>전화</dt>
          <dd><?php echo esc_html($company['tel']); ?></dd>
        </dl>
        <dl class="uw-footer__info-item">
          <dt>상호</dt>
          <dd><?php echo esc_html($company['name']); ?></dd>
        </dl>
        <?php if (!empty($company['biz_no'])) : ?>
        <dl class="uw-footer__info-item">
          <dt>사업자등록번호</dt>
          <dd><?php echo esc_html($company['biz_no']); ?></dd>
        </dl>
        <?php endif; ?>
        <?php if (!empty($company['address'])) : ?>
        <dl class="uw-footer__info-item">
          <dt>주소</dt>
          <dd><?php echo esc_html($company['address']); ?></dd>
        </dl>
        <?php endif; ?>
      </div>

      <div class="uw-footer__copyright">
        <p>&copy;<?php echo date('Y'); ?> UNWEBS. All rights Reserved</p>
      </div>

    </div>
  </div>

</footer>
