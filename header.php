<?php
/**
 * Header Template
 */
$meta = starter_head_meta();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <!-- Essential Meta -->
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">

  <!-- SEO Meta -->
  <title><?php echo $meta['title']; ?></title>
  <meta name="description" content="<?php echo $meta['description']; ?>">
  <meta name="keywords" content="<?php echo $meta['keywords']; ?>">
  <link rel="canonical" href="<?php echo $meta['canonical']; ?>">

  <!-- Crawler Control -->
  <meta name="robots" content="index, follow">

  <!-- Search Console Verification -->
  <?php if (!empty($meta['google_verification'])) : ?>
  <meta name="google-site-verification" content="<?php echo $meta['google_verification']; ?>">
  <?php endif; ?>
  <?php if (!empty($meta['naver_verification'])) : ?>
  <meta name="naver-site-verification" content="<?php echo $meta['naver_verification']; ?>">
  <?php endif; ?>

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?php echo $meta['title']; ?>">
  <meta property="og:description" content="<?php echo $meta['description']; ?>">
  <meta property="og:image" content="<?php echo $meta['og_image']; ?>">
  <meta property="og:url" content="<?php echo $meta['canonical']; ?>">
  <meta property="og:site_name" content="<?php echo $meta['title']; ?>">
  <meta property="og:locale" content="ko_KR">

  <!-- Favicon & App Icons -->
  <meta name="theme-color" content="<?php echo $meta['theme_color']; ?>">
  <link rel="icon" type="image/png" href="<?php echo esc_url(get_theme_file_uri('/assets/images/favicon.png')); ?>">
  <link rel="apple-touch-icon" href="<?php echo esc_url(get_theme_file_uri('/assets/images/favicon.png')); ?>">

  <!-- Pretendard -->
  <link rel="stylesheet" as="style" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard-dynamic-subset.css" />

  <!-- WordPress Head -->
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php if (is_front_page()) : get_template_part('template-parts/common/banner', 'status'); endif; ?>

<!-- Header -->
<header class="uw-header" id="uwHeader" role="banner">
  <?php get_template_part('template-parts/header/nav'); ?>
</header>

<!-- Mobile Navigation (Slide-in Accordion) -->
<?php get_template_part('template-parts/header/nav-mobile'); ?>
