<?php
/**
 * Template Part: 실시간 프로젝트 (낚시 섹션)
 *
 * 이미지 슬라이드 (marquee) · 타이틀 없음 · 양끝 흰색 페이드 · 호버 시 pause + 이미지 zoom
 *
 * ─────────────────────────────────────────────
 * 2주마다 수동 업데이트 포인트:
 *   1) 아래 $projects 배열의 name·img 값 교체
 *   2) 이미지는 /assets/images/main/projects/ 폴더에 업로드
 *      (파일 없으면 기존 /assets/images/main/visual_pc0*.png 을 fallback 경로로 사용)
 * ─────────────────────────────────────────────
 */

if (!defined('ABSPATH')) exit;

$projects = array(
    array('name' => '리버티웨어드론',     'img' => 'project01.png', 'category' => '기업/기관'),
    array('name' => 'SNS지니',   'img' => 'project02.png', 'category' => '스타트업'),
    array('name' => 'ONULHOME',   'img' => 'project03.png', 'category' => '브랜드'),
    array('name' => '어나드범어', 'img' => 'project04.png', 'category' => '브랜드'),
    array('name' => '한빛이엔씨',     'img' => 'project05.png', 'category' => '기업/기관'),
    array('name' => '아이키커스',     'img' => 'project06.png', 'category' => '병원/의료'),
    array('name' => '그린하이텍',   'img' => 'project07.png', 'category' => '기업/기관'),
);

// 월 단위 시드 (YYYYMM). 같은 한 달 동안 같은 날짜 세트, 다음 달 1일에 자동 갱신.
$salt = defined('AUTH_KEY') ? AUTH_KEY : 'unwebs';
$seed = crc32(date('Ym') . $salt);
mt_srand($seed);

// 프로젝트명 앞 2글자만 노출 + *** (이미지 예시 "언디***" 형식)
$items = array();
foreach ($projects as $p) {
    $days_ago = mt_rand(5, 30);
    $img_path = '/assets/images/main/projects/' . $p['img'];
    // projects/ 폴더에 파일 있으면 우선, 없으면 main/ 하위 파일로 fallback
    $abs = get_template_directory() . $img_path;
    if (!file_exists($abs)) {
        $img_path = '/assets/images/main/' . $p['img'];
    }

    $items[] = array(
        'name'     => mb_substr($p['name'], 0, 2) . '***',
        'img'      => get_theme_file_uri($img_path),
        'date'     => date('Y-m-d', strtotime('-' . $days_ago . ' days')),
        'category' => $p['category'],
    );
}
mt_srand();
?>

<section class="main-projects-con" aria-label="실시간 프로젝트">
  <div class="main-projects-mask">
    <ul class="main-projects-list">
      <?php for ($r = 0; $r < 2; $r++) : ?>
        <?php foreach ($items as $item) : ?>
        <li class="main-projects-item"<?php echo $r > 0 ? ' aria-hidden="true"' : ''; ?>>
          <div class="main-projects-card">
            <div class="main-projects-img">
              <img
                src="<?php echo esc_url($item['img']); ?>"
                alt="<?php echo esc_attr($item['name']); ?> 메인페이지"
                loading="lazy"
                width="300" height="200">
              <span class="main-projects-badge">제작중</span>
            </div>
            <div class="main-projects-info">
              <div class="main-projects-text">
                <h3 class="main-projects-tit"><?php echo esc_html($item['name']); ?></h3>
                <span class="main-projects-cat"><?php echo esc_html($item['category']); ?></span>
              </div>
              <time class="main-projects-date"><?php echo esc_html($item['date']); ?></time>
            </div>
          </div>
        </li>
        <?php endforeach; ?>
      <?php endfor; ?>
    </ul>
  </div>
</section>
