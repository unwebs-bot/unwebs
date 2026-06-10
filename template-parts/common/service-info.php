<?php
/**
 * Template Part: 서비스 안내 (Service Info)
 *
 *  - 2×2 카드. 이미지(상) + 제목 + 한 줄 설명 + 체크 특징 2~3개
 *  - 카드별 media 자동 매핑 (우선순위):
 *    1) {slug}.mp4 / {slug}.webm 존재 → <video autoplay>
 *    2) {slug}.webp / {slug}.jpg / {slug}.png 존재 → <picture> + <img>
 *    3) 둘 다 없음 → 빈 placeholder
 *  - 경로: assets/images/content/service/{slug}/
 */
if (!defined('ABSPATH'))
  exit;

$info_cards = array(
  array(
    'slug'     => 'search-register',
    'tit'      => '사이트 검색 등록',
    'desc'     => '구글·네이버 등 주요 검색엔진에 사이트를 등록하고 노출 환경을 구축합니다.',
    'features' => array(
      '구글 서치콘솔 등록',
      '네이버 서치어드바이저 등록',
      '사이트맵 자동 제출',
    ),
  ),
  array(
    'slug'     => 'admin',
    'tit'      => '관리자페이지',
    'desc'     => '코드 수정 없이 콘텐츠를 직접 등록·수정할 수 있는 관리자 모드를 제공합니다.',
    'features' => array(
      '게시판·공지·문의 일괄 관리',
      '디자인모드로 페이지 직접 수정',
      '모바일 환경에서도 관리 가능',
    ),
  ),
  array(
    'slug'     => 'seo',
    'tit'      => 'SEO 검색엔진 최적화',
    'desc'     => '검색결과 상단 노출을 위한 기술적 최적화를 기본으로 적용합니다.',
    'features' => array(
      '메타·OG·구조화데이터 자동 출력',
      '시맨틱 마크업·웹표준 준수',
      '사이트맵·robots.txt 자동 생성',
    ),
  ),
  array(
    'slug'     => 'analytics',
    'tit'      => '접속 통계 분석',
    'desc'     => '방문자 데이터를 수집하고 유입 경로·전환을 한눈에 확인합니다.',
    'features' => array(
      '구글 애널리틱스(GA4) 세팅',
      '실시간 방문자 현황 확인',
      '유입 채널·키워드 분석',
    ),
  ),
);
?>

<section class="service-info-con sub-content-con">
  <div class="area">

    <div class="cm-tit-box" data-animate="fade-up">
      <span class="cm-tit-sub">SERVICE</span>
      <h2 class="cm-tit">서비스 안내</h2>
    </div>

    <ul class="service-info-list">
      <?php
      $media_base_dir = get_theme_file_path('/assets/images/content/service');
      $media_base_uri = get_theme_file_uri('/assets/images/content/service');
      foreach ($info_cards as $i => $c):
        $slug    = $c['slug'];
        $mp4     = "$media_base_dir/$slug/$slug.mp4";
        $webm    = "$media_base_dir/$slug/$slug.webm";
        $poster  = "$media_base_dir/$slug/$slug-poster.jpg";
        $img_webp = "$media_base_dir/$slug/$slug.webp";
        $img_jpg  = "$media_base_dir/$slug/$slug.jpg";
        $img_png  = "$media_base_dir/$slug/$slug.png";
        $has_mp4    = file_exists($mp4);
        $has_webm   = file_exists($webm);
        $has_poster = file_exists($poster);
        $has_webp   = file_exists($img_webp);
        $has_jpg    = file_exists($img_jpg);
        $has_png    = file_exists($img_png);
        $has_video  = $has_mp4 || $has_webm;
        $has_image  = $has_webp || $has_jpg || $has_png;
        $img_fallback_ext = $has_jpg ? 'jpg' : ($has_png ? 'png' : ($has_webp ? 'webp' : ''));
      ?>
        <li class="service-info-item" data-animate="fade-up" data-delay="<?php echo 100 + ($i * 50); ?>">
          <div class="service-info-media<?php echo $has_video ? ' has-video' : ''; ?><?php echo (!$has_video && $has_image) ? ' has-image' : ''; ?>" data-slug="<?php echo esc_attr($slug); ?>">
            <?php if ($has_video): ?>
              <video class="service-info-video"
                     autoplay muted loop playsinline preload="metadata"
                     <?php echo $has_poster ? 'poster="' . esc_url("$media_base_uri/$slug/$slug-poster.jpg") . '"' : ''; ?>>
                <?php if ($has_webm): ?>
                  <source src="<?php echo esc_url("$media_base_uri/$slug/$slug.webm"); ?>" type="video/webm">
                <?php endif; ?>
                <?php if ($has_mp4): ?>
                  <source src="<?php echo esc_url("$media_base_uri/$slug/$slug.mp4"); ?>" type="video/mp4">
                <?php endif; ?>
              </video>
            <?php elseif ($has_image): ?>
              <picture>
                <?php if ($has_webp): ?>
                  <source srcset="<?php echo esc_url("$media_base_uri/$slug/$slug.webp"); ?>" type="image/webp">
                <?php endif; ?>
                <img class="service-info-image"
                     src="<?php echo esc_url("$media_base_uri/$slug/$slug.$img_fallback_ext"); ?>"
                     alt="<?php echo esc_attr($c['tit']); ?>"
                     loading="lazy" decoding="async">
              </picture>
            <?php endif; ?>
          </div>
          <div class="service-info-text">
            <h3 class="service-info-tit"><?php echo esc_html($c['tit']); ?></h3>
            <p class="service-info-desc"><?php echo esc_html($c['desc']); ?></p>
            <?php if (!empty($c['features'])): ?>
              <ul class="service-info-features">
                <?php foreach ($c['features'] as $feat): ?>
                  <li class="service-info-feature">
                    <span class="service-info-check" aria-hidden="true">
                      <i class="xi-check"></i>
                    </span>
                    <span class="service-info-feature-txt"><?php echo esc_html($feat); ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>

  </div>
</section>
