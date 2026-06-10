<?php
/**
 * Template Part: 데이터 수치 섹션
 *
 * 본문폭 내 박스 형태. 박스 내부: cm-tit-box + 수치 3개 + 브랜드 로고 marquee(2줄).
 * 타이틀은 공통 cm-tit-box/cm-tit/cm-tit-sub 재사용. 색상만 섹션에서 override.
 */

if (!defined('ABSPATH')) exit;

$stats = array(
    array('value' => '249',  'unit' => '+', 'label' => '누적 프로젝트'),
    array('value' => '96',   'unit' => '%', 'label' => '고객 만족도'),
    array('value' => '2317', 'unit' => '+', 'label' => '누적 유지보수'),
);

// uw_stats_digit_list() — inc/cm-stats-helpers.php 로 이전 (단일 소스)

// 브랜드 로고 — 2줄 마퀴 (반대 방향)
$brand_logos_ltr = array(
    array('file' => 'LG-signature.svg',     'alt' => 'LG'),
    array('file' => '한국가스공사.svg',     'alt' => '한국가스공사'),
    array('file' => '문화체육관광부.svg',   'alt' => '문화체육관광부'),
    array('file' => '현대건설.svg',         'alt' => '현대건설'),
    array('file' => '과학의전당.svg',       'alt' => '예술의전당'),
    array('file' => '스포츠안전재단.svg',   'alt' => '스포츠안전재단'),
    array('file' => '삼성편한내과.svg',     'alt' => '삼성편한내과'),
    array('file' => '한국골든에이지포럼.svg', 'alt' => '한국골든에이지포럼'),
    array('file' => 'ST-Works.svg',         'alt' => 'ST-Works'),
);

$brand_logos_rtl = array(
    array('file' => '현대엔지니어링.svg',  'alt' => '현대엔지니어링'),
    array('file' => '힐스테이트.svg',      'alt' => '힐스테이트'),
    array('file' => '루원스마일안과.svg',  'alt' => '루원스마일안과'),
    array('file' => 'theSMC.svg',          'alt' => 'theSMC'),
    array('file' => '세무회계천상.svg',    'alt' => '세무회계천상'),
    array('file' => '리베라웨어.svg',      'alt' => '리베라웨어'),
    array('file' => '에이레브.svg',        'alt' => '에이레브'),
    array('file' => '크리스천메이트.svg',  'alt' => '크리스천메이트'),
);

$brand_logo_dir = '/assets/images/main/brands/';
?>

<section class="main-stats-con cm-section" id="cmStats">
  <div class="area">

    <div class="main-stats-box">

      <!-- 헤더 (공통 cm-tit-box 재사용) -->
      <?php
      // 언웹스 시작일(2023-01-13) 기준 누적 일수 — 매일 자동 갱신
      $uw_start = new DateTimeImmutable('2023-01-13', wp_timezone());
      $uw_today = new DateTimeImmutable('today', wp_timezone());
      $uw_days  = $uw_start->diff($uw_today)->days;
      ?>
      <div class="cm-tit-box">
        <span class="cm-tit-sub">공공기관부터 중견·대기업까지 지난 <?php echo number_format($uw_days); ?>일 동안<br class="cm-br-mobile"> 언웹스와 함께한 고객들의 기록입니다.</span>
        <h2 class="cm-tit">홈페이지 제작, 왜 언웹스일까요?</h2>
      </div>

      <!-- 수치 3개 -->
      <div class="main-stats-wrap">
        <?php foreach ($stats as $si => $stat) : ?>
        <div class="main-stats-item">
          <p class="main-stats-label"><?php echo esc_html($stat['label']); ?></p>
          <div class="main-stats-number">
            <?php
            $digits = str_split($stat['value']);
            foreach ($digits as $di => $digit) :
                $d    = intval($digit);
                $dir  = ($di % 2 === 0) ? 'up' : 'down';
                $seed = ($si + 1) * 100 + $di;
                $list = uw_stats_digit_list($d, $dir, $seed);
            ?>
            <div class="main-stats-digit-wrap">
              <ul class="main-stats-digit-box <?php echo $dir; ?>">
                <?php foreach ($list as $item) : ?>
                <li class="main-stats-digit-item"><?php echo $item; ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endforeach; ?>
            <span class="main-stats-unit"><?php echo esc_html($stat['unit']); ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- 브랜드 로고 marquee (2줄, 반대 방향) -->
      <div class="main-stats-brands">
        <div class="main-stats-brands-track main-stats-brands-track-ltr">
          <?php for ($r = 0; $r < 2; $r++) : ?>
            <?php foreach ($brand_logos_ltr as $logo) : ?>
            <div class="main-stats-brand-item"<?php echo $r > 0 ? ' aria-hidden="true"' : ''; ?>>
              <img src="<?php echo esc_url(get_theme_file_uri($brand_logo_dir . $logo['file'])); ?>" alt="<?php echo esc_attr($logo['alt']); ?>" loading="lazy" width="160" height="48">
            </div>
            <?php endforeach; ?>
          <?php endfor; ?>
        </div>
        <div class="main-stats-brands-track main-stats-brands-track-rtl">
          <?php for ($r = 0; $r < 2; $r++) : ?>
            <?php foreach ($brand_logos_rtl as $logo) : ?>
            <div class="main-stats-brand-item"<?php echo $r > 0 ? ' aria-hidden="true"' : ''; ?>>
              <img src="<?php echo esc_url(get_theme_file_uri($brand_logo_dir . $logo['file'])); ?>" alt="<?php echo esc_attr($logo['alt']); ?>" loading="lazy" width="160" height="48">
            </div>
            <?php endforeach; ?>
          <?php endfor; ?>
        </div>
      </div>

    </div>

  </div>
</section>
