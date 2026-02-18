<?php
/**
 * Template Part: 포트폴리오 쇼케이스
 *
 * PC: 4열 × 3줄 (12개)
 * Mobile: 1열 × 5줄 (나머지 숨김)
 * 호버: PC 이미지 스크롤 + 모바일 디바이스 팝업
 */

if (!defined('ABSPATH')) exit;

// 포트폴리오 데이터 (추후 CPT 연동 시 교체)
$portfolio_items = array(
  array('title' => '포트폴리오 01', 'pc_img' => 'https://cdn.imweb.me/upload/47fc8e829221a.png', 'mob_img' => 'https://cdn.imweb.me/upload/7b2baad6c6f68.png', 'link' => '#'),
  array('title' => '포트폴리오 02', 'pc_img' => 'https://cdn.imweb.me/upload/47fc8e829221a.png', 'mob_img' => 'https://cdn.imweb.me/upload/7b2baad6c6f68.png', 'link' => '#'),
  array('title' => '포트폴리오 03', 'pc_img' => 'https://cdn.imweb.me/upload/47fc8e829221a.png', 'mob_img' => 'https://cdn.imweb.me/upload/7b2baad6c6f68.png', 'link' => '#'),
  array('title' => '포트폴리오 04', 'pc_img' => 'https://cdn.imweb.me/upload/47fc8e829221a.png', 'mob_img' => 'https://cdn.imweb.me/upload/7b2baad6c6f68.png', 'link' => '#'),
  array('title' => '포트폴리오 05', 'pc_img' => 'https://cdn.imweb.me/upload/47fc8e829221a.png', 'mob_img' => 'https://cdn.imweb.me/upload/7b2baad6c6f68.png', 'link' => '#'),
  array('title' => '포트폴리오 06', 'pc_img' => 'https://cdn.imweb.me/upload/47fc8e829221a.png', 'mob_img' => 'https://cdn.imweb.me/upload/7b2baad6c6f68.png', 'link' => '#'),
  array('title' => '포트폴리오 07', 'pc_img' => 'https://cdn.imweb.me/upload/47fc8e829221a.png', 'mob_img' => 'https://cdn.imweb.me/upload/7b2baad6c6f68.png', 'link' => '#'),
  array('title' => '포트폴리오 08', 'pc_img' => 'https://cdn.imweb.me/upload/47fc8e829221a.png', 'mob_img' => 'https://cdn.imweb.me/upload/7b2baad6c6f68.png', 'link' => '#'),
  array('title' => '포트폴리오 09', 'pc_img' => 'https://cdn.imweb.me/upload/47fc8e829221a.png', 'mob_img' => 'https://cdn.imweb.me/upload/7b2baad6c6f68.png', 'link' => '#'),
  array('title' => '포트폴리오 10', 'pc_img' => 'https://cdn.imweb.me/upload/47fc8e829221a.png', 'mob_img' => 'https://cdn.imweb.me/upload/7b2baad6c6f68.png', 'link' => '#'),
  array('title' => '포트폴리오 11', 'pc_img' => 'https://cdn.imweb.me/upload/47fc8e829221a.png', 'mob_img' => 'https://cdn.imweb.me/upload/7b2baad6c6f68.png', 'link' => '#'),
  array('title' => '포트폴리오 12', 'pc_img' => 'https://cdn.imweb.me/upload/47fc8e829221a.png', 'mob_img' => 'https://cdn.imweb.me/upload/7b2baad6c6f68.png', 'link' => '#'),
);
?>

<section class="uw-portfolio uw-section">
  <div class="uw-portfolio__header">
    <span class="uw-portfolio__subtitle uw-section-subtitle">Portfolio</span>
    <h2 class="uw-portfolio__title uw-section-title">포트폴리오</h2>
  </div>

  <div class="uw-portfolio__container">
    <ul class="uw-portfolio__grid">
      <?php foreach ($portfolio_items as $i => $item) : ?>
        <li class="uw-portfolio__card">
          <a href="<?php echo esc_url($item['link']); ?>" class="uw-portfolio__link">
            <div class="uw-portfolio__pc">
              <img src="<?php echo esc_url($item['pc_img']); ?>" class="uw-portfolio__pc-img" alt="<?php echo esc_attr($item['title']); ?>" loading="lazy">
            </div>
            <div class="uw-portfolio__mob">
              <img src="<?php echo esc_url($item['mob_img']); ?>" class="uw-portfolio__mob-img" alt="<?php echo esc_attr($item['title']); ?> Mobile" loading="lazy">
            </div>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
