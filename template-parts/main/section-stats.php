<?php
/**
 * Template Part: 데이터 수치 섹션
 *
 * 롤링 넘버 카운터 (스크롤 트리거)
 * 4개 KPI - 다크 테마
 */

if (!defined('ABSPATH')) exit;

$stats = array(
  array('value' => '750', 'unit' => 'K',  'label' => '누적 사이트 이용자 수'),
  array('value' => '125', 'unit' => '건', 'label' => '누적 프로젝트'),
  array('value' => '2023', 'unit' => '년', 'label' => '사업년도'),
  array('value' => '96',  'unit' => '%',  'label' => '고객 만족도'),
);

/**
 * 숫자 롤링용 digit 리스트 생성
 * @param int    $target    목표 숫자 (0-9)
 * @param string $direction 'up' | 'down'
 * @param int    $seed      셔플 시드
 * @return array
 */
function uw_stats_digit_list($target, $direction, $seed) {
  mt_srand($seed);
  $remaining = array();
  for ($d = 0; $d <= 9; $d++) {
    if ($d !== $target) $remaining[] = $d;
  }
  for ($i = count($remaining) - 1; $i > 0; $i--) {
    $j = mt_rand(0, $i);
    $tmp = $remaining[$i];
    $remaining[$i] = $remaining[$j];
    $remaining[$j] = $tmp;
  }
  mt_srand();

  if ($direction === 'up') {
    $remaining[] = $target;
  } else {
    array_unshift($remaining, $target);
  }
  return $remaining;
}
?>

<section class="uw-stats uw-section" id="uwStats">
  <div class="uw-stats__container">

    <?php foreach ($stats as $si => $stat) : ?>
      <div class="uw-stats__item">
        <div class="uw-stats__number">
          <?php
          $digits = str_split($stat['value']);
          foreach ($digits as $di => $digit) :
            $d   = intval($digit);
            $dir  = ($di % 2 === 0) ? 'up' : 'down';
            $seed = ($si + 1) * 100 + $di;
            $list = uw_stats_digit_list($d, $dir, $seed);
          ?>
            <div class="uw-stats__digit-wrap">
              <ul class="uw-stats__digit-box <?php echo $dir; ?>">
                <?php foreach ($list as $item) : ?>
                  <li class="uw-stats__digit-item"><?php echo $item; ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endforeach; ?>
          <span class="uw-stats__unit"><?php echo esc_html($stat['unit']); ?></span>
        </div>
        <p class="uw-stats__label"><?php echo esc_html($stat['label']); ?></p>
      </div>
    <?php endforeach; ?>

  </div>
</section>
