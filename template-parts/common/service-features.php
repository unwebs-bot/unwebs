<?php
/**
 * Template Part: 무료 제공 항목 (Service Features)
 *
 *  - 홈페이지 제작 시 무상 제공되는 10가지 서비스 그리드
 *  - 5열 × 2행 (1180 이하 3열 / 768 이하 2열 / 480 이하 2열 유지)
 *  - 아이콘 자동 매핑: assets/images/content/service-features/{slug}.webp(+.png)
 */
if (!defined('ABSPATH'))
  exit;

$features = array(
  array('slug' => 'warranty',  'tit' => '하자보수 평생 무료',   'txt' => '납품 후 결함 무상 수정'),
  array('slug' => 'maintain',  'tit' => '유지보수 옵션',         'txt' => '디자인모드 콘텐츠 관리'),
  array('slug' => 'admin',     'tit' => '관리자 통계·설정',      'txt' => '트래픽·문의 한눈 확인'),
  array('slug' => 'image',     'tit' => '정품 이미지 라이선스', 'txt' => '30만 컷 이상 상업용 풀'),
  array('slug' => 'analytics', 'tit' => '구글 애널리틱스',       'txt' => '방문자·전환 데이터 수집'),
  array('slug' => 'gsc',       'tit' => '구글 서치콘솔',         'txt' => '구글 검색 노출 등록'),
  array('slug' => 'naver',     'tit' => '네이버 서치어드바이저', 'txt' => '네이버 검색 노출 등록'),
  array('slug' => 'browser',   'tit' => '크로스브라우징',        'txt' => 'Chrome·Safari·Edge 호환'),
  array('slug' => 'standard',  'tit' => '웹표준 준수',           'txt' => 'W3C·시맨틱 마크업'),
  array('slug' => 'manual',    'tit' => '운영 매뉴얼 PDF',       'txt' => '관리방법 PDF 제공'),
);

$icon_dir = get_theme_file_path('/assets/images/content/service-features');
$icon_uri = get_theme_file_uri('/assets/images/content/service-features');
?>

<section class="service-features-con sub-content-con">
  <div class="area">

    <div class="cm-tit-box" data-animate="fade-up">
      <span class="cm-tit-sub">제작 및 사후 운영에 필요한 10가지 서비스를 무상으로 제공합니다</span>
      <h2 class="cm-tit">무료 제공 항목</h2>
    </div>

    <ul class="service-features-list" data-animate="fade-up" data-delay="100">
      <?php foreach ($features as $i => $f):
        // 인덱스(01~10) 기반 매핑 — 카드 순서대로 1:1
        $idx = sprintf('%02d', $i + 1);
        $has_webp = file_exists("$icon_dir/$idx.webp");
        $has_png  = file_exists("$icon_dir/$idx.png");
        $has_img  = $has_webp || $has_png;
      ?>
        <li class="service-features-item">
          <div class="service-features-icon" data-icon="<?php echo esc_attr($f['slug']); ?>">
            <?php if ($has_img): ?>
              <picture>
                <?php if ($has_webp): ?>
                  <source srcset="<?php echo esc_url("$icon_uri/$idx.webp"); ?>" type="image/webp">
                <?php endif; ?>
                <img src="<?php echo esc_url("$icon_uri/$idx." . ($has_png ? 'png' : 'webp')); ?>"
                     alt="<?php echo esc_attr($f['tit']); ?>"
                     loading="lazy" decoding="async">
              </picture>
            <?php endif; ?>
          </div>
          <h3 class="service-features-tit"><?php echo esc_html($f['tit']); ?></h3>
          <p class="service-features-txt"><?php echo esc_html($f['txt']); ?></p>
        </li>
      <?php endforeach; ?>
    </ul>

  </div>
</section>
