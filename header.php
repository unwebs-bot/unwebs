<?php
/**
 * Header Template
 *
 * Rank Math SEO 플러그인 활성화 시 메타/OG/canonical/robots는 Rank Math가 출력.
 * 미활성화 fallback으로 커스텀 meta를 starter_head_meta()로 출력.
 */
$meta = starter_head_meta();
$has_rank_math = class_exists('RankMath');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">

  <?php if (function_exists('uw_gtm_head')) uw_gtm_head(); ?>

  <?php if (!$has_rank_math) : ?>
  <!-- Fallback SEO Meta (Rank Math 미활성화 시) -->
  <title><?php echo $meta['title']; ?></title>
  <meta name="description" content="<?php echo $meta['description']; ?>">
  <meta name="keywords" content="<?php echo $meta['keywords']; ?>">
  <link rel="canonical" href="<?php echo $meta['canonical']; ?>">
  <meta name="robots" content="index, follow">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?php echo $meta['title']; ?>">
  <meta property="og:description" content="<?php echo $meta['description']; ?>">
  <meta property="og:image" content="<?php echo $meta['og_image']; ?>">
  <meta property="og:url" content="<?php echo $meta['canonical']; ?>">
  <meta property="og:site_name" content="<?php echo $meta['title']; ?>">
  <meta property="og:locale" content="ko_KR">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo $meta['title']; ?>">
  <meta name="twitter:description" content="<?php echo $meta['description']; ?>">
  <meta name="twitter:image" content="<?php echo $meta['og_image']; ?>">
  <?php endif; ?>

  <!-- Search Console Verification (Rank Math 유무 관계없이 유지) -->
  <?php if (!empty($meta['google_verification'])) : ?>
  <meta name="google-site-verification" content="<?php echo $meta['google_verification']; ?>">
  <?php endif; ?>
  <?php if (!empty($meta['naver_verification'])) : ?>
  <meta name="naver-site-verification" content="<?php echo $meta['naver_verification']; ?>">
  <?php endif; ?>

  <!-- Favicon -->
  <meta name="theme-color" content="<?php echo $meta['theme_color']; ?>">
  <link rel="icon" type="image/png" href="<?php echo esc_url(get_theme_file_uri('/assets/images/common/favicon.png')); ?>">
  <link rel="apple-touch-icon" href="<?php echo esc_url(get_theme_file_uri('/assets/images/common/favicon.png')); ?>">

  <!-- 외부 CDN preconnect (Pretendard·XEIcon=jsdelivr / Splitting=unpkg) — 연결 핸드셰이크 선행으로 LCP/FCP 개선 -->
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link rel="preconnect" href="https://unpkg.com" crossorigin>

  <!-- Pretendard -->
  <link rel="stylesheet" as="style" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard-dynamic-subset.css">

  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if (function_exists('uw_gtm_noscript')) uw_gtm_noscript(); ?>

<!-- Skip Navigation (접근성) -->
<a class="cm-skip-nav" href="#main-content">본문 바로가기</a>

<?php if (is_front_page()) : get_template_part('template-parts/common/banner', 'status'); endif; ?>

<!-- Header -->
<header class="cm-header" id="cmHeader" role="banner">
  <?php get_template_part('template-parts/header/nav'); ?>
</header>

<!-- Mobile Navigation -->
<?php get_template_part('template-parts/header/nav-mobile'); ?>
