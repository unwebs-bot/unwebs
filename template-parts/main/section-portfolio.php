<?php
/**
 * Template Part: 포트폴리오 쇼케이스 (메인 섹션)
 *
 * 데이터: `inc/cm-portfolio-main.php` 하드코딩 배열 (CPT와 분리됨)
 * 4 × 2 = 8개 고정. 이미지 + 제목 + 뱃지 2개(업종/유형)
 * (※ 언웹스는 쇼핑몰/이커머스 제작 안 함 — 업종 카테고리 추가 시 주의)
 */

if (!defined('ABSPATH')) exit;

// 카드 디자인용 placeholder (이미지 미입력 시 fallback)
$img_pc_placeholder  = 'https://cdn.imweb.me/upload/47fc8e829221a.png';
$img_mob_placeholder = 'https://cdn.imweb.me/upload/7b2baad6c6f68.png';

$portfolio_items = function_exists('cm_get_main_portfolio_items')
    ? cm_get_main_portfolio_items()
    : array();
?>

<section class="main-portfolio-con cm-section">
  <div class="area">
    <div class="cm-tit-box" data-animate="fade-up">
      <span class="cm-tit-sub">최근 제작사례를 확인해보세요</span>
      <h2 class="cm-tit">포트폴리오</h2>
    </div>

    <ul class="main-portfolio-grid" data-animate="fade-up" data-delay="200">
      <?php foreach ($portfolio_items as $item) :
        $card_url = !empty($item['url']) ? $item['url'] : '';
        $img_pc   = !empty($item['image']) ? $item['image'] : $img_pc_placeholder;
        $img_mob  = !empty($item['image_mob']) ? $item['image_mob'] : $img_mob_placeholder;
        $is_external = $card_url !== '' && strpos($card_url, home_url()) !== 0;
        $tags_attr = !empty($item['tags']) ? implode(',', array_map('trim', (array) $item['tags'])) : '';
        $popup_img = !empty($item['popup_image']) ? $item['popup_image'] : (!empty($item['image']) ? $item['image'] : '');
        $popup_gallery = (!empty($item['popup_gallery']) && is_array($item['popup_gallery']))
            ? $item['popup_gallery']
            : ($popup_img !== '' ? array($popup_img) : array());
        $popup_imgs_attr = implode(',', array_map('esc_url', $popup_gallery));
      ?>
      <li class="main-portfolio-card">
        <?php if ($card_url !== '') : ?>
        <a href="<?php echo esc_url($card_url); ?>" class="main-portfolio-link js-port-card"<?php if ($is_external) : ?> target="_blank" rel="noopener noreferrer"<?php endif; ?>
           data-port-title="<?php echo esc_attr($item['title']); ?>"
           data-port-url="<?php echo esc_url($item['external_url']); ?>"
           data-port-industry="<?php echo esc_attr($item['industry']); ?>"
           data-port-work="<?php echo esc_attr($item['type']); ?>"
           data-port-type="<?php echo esc_attr($item['web_type']); ?>"
           data-port-option="<?php echo esc_attr($item['option']); ?>"
           data-port-price="<?php echo esc_attr($item['price']); ?>"
           data-port-tags="<?php echo esc_attr($tags_attr); ?>"
           data-port-image="<?php echo esc_attr($popup_imgs_attr); ?>"
           data-port-mobile="<?php echo esc_url($item['popup_mobile'] ?? ''); ?>">
        <?php else : ?>
        <div class="main-portfolio-link is-disabled">
        <?php endif; ?>

          <div class="main-portfolio-img">
            <div class="main-portfolio-pc">
              <img src="<?php echo esc_url($img_pc); ?>" class="main-portfolio-pc-img" alt="<?php echo esc_attr($item['title']); ?> PC" loading="lazy">
            </div>
            <div class="main-portfolio-mob">
              <img data-src="<?php echo esc_url($img_mob); ?>" class="main-portfolio-mob-img" alt="<?php echo esc_attr($item['title']); ?> Mobile" loading="lazy">
            </div>
          </div>

          <div class="main-portfolio-info">
            <h3 class="main-portfolio-name"><?php echo esc_html($item['title']); ?></h3>
            <div class="main-portfolio-tags">
              <?php if (!empty($item['industry'])) : ?>
                <span class="cm-tag"><?php echo esc_html($item['industry']); ?></span>
              <?php endif; ?>
              <?php if (!empty($item['type'])) : ?>
                <span class="cm-tag"><?php echo esc_html($item['type']); ?></span>
              <?php endif; ?>
            </div>
          </div>

        <?php if ($card_url !== '') : ?>
        </a>
        <?php else : ?>
        </div>
        <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php get_template_part('template-parts/common/portfolio-modal'); ?>
