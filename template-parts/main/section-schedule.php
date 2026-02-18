<?php
/**
 * Template Part: 일정관리 - 프로세스 대시보드
 *
 * 라이트 테마 간트 차트 (6주 / 5단계)
 * 호버 툴팁 + 바 리빌 애니메이션
 */

if (!defined('ABSPATH')) exit;

$weeks = 6;
$processes = array(
  array(
    'label'   => '계약 및 자료수취',
    'bar'     => '계약 프로세스',
    'col_s'   => 2, 'col_e' => 3,
    'color'   => 'blue',
    'tt'      => '계약 및 자료수취',
    'items'   => '문의 및 자료 전달,견적서 최종 확정 및 계약,사업자등록증 확인 및 계산서 발행,추가 요청 자료 전달',
  ),
  array(
    'label'   => '기획',
    'bar'     => '기획 및 기반 구축',
    'col_s'   => 3, 'col_e' => 4,
    'color'   => 'green',
    'tt'      => '홈페이지 기획',
    'items'   => '사전기획서 전달 및 피드백,와이어프레임 전달 및 피드백,디자인 컨셉 기획 및 구축',
  ),
  array(
    'label'   => '디자인 시안',
    'bar'     => '시안 작업 및 최적화',
    'col_s'   => 4, 'col_e' => 6,
    'color'   => 'amber',
    'tt'      => '디자인 시안 제작',
    'items'   => '메인 및 서브페이지 시안 송부,피드백 수취 및 수정 반영,서브시안 완료 및 모바일 최적화',
  ),
  array(
    'label'   => '개발 및 검수',
    'bar'     => '시스템 구현 및 연동',
    'col_s'   => 6, 'col_e' => 8,
    'color'   => 'pink',
    'tt'      => '개발 및 검수',
    'items'   => '퍼블리싱 및 기능 개발,오류 검수 및 사이트 테스트,도메인/SSL 연결 및 사이트 등록',
  ),
  array(
    'label'   => '최종 완료',
    'bar'     => '프로젝트 런칭',
    'col_s'   => 7, 'col_e' => 8,
    'color'   => 'indigo',
    'tt'      => '최종 완료',
    'items'   => '제작 완료 최종 승인,운영 가이드 전달 및 교육,유지보수 정책 안내',
  ),
);
?>

<section class="uw-schedule uw-section" id="uwSchedule">
  <div class="uw-schedule__header">
    <span class="uw-schedule__subtitle uw-section-subtitle">제작 프로세스</span>
    <h2 class="uw-schedule__title uw-section-title">홈페이지 제작, 이렇게 진행됩니다.</h2>
  </div>

  <div class="uw-schedule__container">
    <div class="uw-schedule__chart">

      <?php $maxDist = count($processes) + $weeks; // 5 + 6 = 11 ?>

      <!-- Header Row -->
      <div class="uw-schedule__cell uw-schedule__cell--corner" style="grid-row:1; grid-column:1; --cell-delay:0s"></div>
      <?php for ($w = 1; $w <= $weeks; $w++) : ?>
        <div class="uw-schedule__cell uw-schedule__cell--header" style="grid-row:1; grid-column:<?php echo $w + 1; ?>; --cell-delay:<?php echo number_format($w / $maxDist, 3); ?>s"><?php echo $w; ?>주차</div>
      <?php endfor; ?>

      <!-- Data Rows -->
      <?php foreach ($processes as $ri => $proc) :
        $row = $ri + 1;
        $gridRow = $ri + 2;
      ?>
        <div class="uw-schedule__cell uw-schedule__cell--label" style="grid-row:<?php echo $gridRow; ?>; grid-column:1; --cell-delay:<?php echo number_format($row / $maxDist, 3); ?>s"><?php echo esc_html($proc['label']); ?></div>
        <?php for ($w = 0; $w < $weeks; $w++) :
          $col = $w + 1;
        ?>
          <div class="uw-schedule__cell" style="grid-row:<?php echo $gridRow; ?>; grid-column:<?php echo $w + 2; ?>; --cell-delay:<?php echo number_format(($row + $col) / $maxDist, 3); ?>s"></div>
        <?php endfor; ?>
      <?php endforeach; ?>

      <!-- Process Bars -->
      <?php foreach ($processes as $i => $proc) :
        $row = $i + 2;
        $delay = 1.2 + $i * 0.15;
      ?>
        <div class="uw-schedule__bar uw-schedule__bar--<?php echo esc_attr($proc['color']); ?>"
             style="grid-row: <?php echo $row; ?>; grid-column: <?php echo $proc['col_s']; ?> / <?php echo $proc['col_e']; ?>; --bar-delay: <?php echo number_format($delay, 1); ?>s;"
             data-title="<?php echo esc_attr($proc['tt']); ?>"
             data-color="<?php echo esc_attr($proc['color']); ?>"
             data-list="<?php echo esc_attr($proc['items']); ?>">
          <?php if (!empty($proc['bar'])) echo esc_html($proc['bar']); ?>
        </div>
      <?php endforeach; ?>

    </div>
  </div>

  <!-- Mobile Vertical Timeline -->
  <div class="uw-schedule__timeline">
    <?php foreach ($processes as $i => $proc) :
      $week_start = $proc['col_s'] - 1;
      $week_end   = $proc['col_e'] - 2;
      $week_text  = ($week_start === $week_end)
        ? $week_start . '주차'
        : $week_start . '~' . $week_end . '주차';
      $items = explode(',', $proc['items']);
    ?>
      <div class="uw-schedule__step uw-schedule__step--<?php echo esc_attr($proc['color']); ?>" style="--step-delay: <?php echo number_format(0.1 + $i * 0.15, 2); ?>s;">
        <div class="uw-schedule__step-dot"></div>
        <div class="uw-schedule__step-content">
          <span class="uw-schedule__step-week"><?php echo $week_text; ?></span>
          <h3 class="uw-schedule__step-title"><?php echo esc_html($proc['label']); ?></h3>
          <ul class="uw-schedule__step-list">
            <?php foreach ($items as $item) : ?>
              <li><?php echo esc_html(trim($item)); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Tooltip -->
  <div class="uw-schedule__tooltip" id="uwScheduleTooltip">
    <h4 class="uw-schedule__tooltip-title" id="uwScheduleTtTitle"></h4>
    <ul class="uw-schedule__tooltip-list" id="uwScheduleTtList"></ul>
  </div>
</section>
