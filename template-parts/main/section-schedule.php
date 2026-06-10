<?php
/**
 * Template Part: 일정관리 프로세스 (Gantt)
 *
 * 구성:
 *  - Gantt 차트: 1W~8W 시간축 + 5행 (프로젝트관리 / 기획 / 디자인 / 퍼블리싱 / 운영·유지보수)
 *  - 막대 3종: 메인(진한 primary) / 서브(옅은 보더 버튼) / 회색(외주·운영급)
 *
 * 데이터: start/end는 0~100% (1W=0%, 8W=100% 기준, 1주 ≒ 14.28%)
 */

if (!defined('ABSPATH')) exit;

// 시간축 라벨 — 1W~8W 모두 + 킥오프 (절대 % 좌표)
$timeline = array(
    array('label' => '1W',     'pos' => 0,     'kickoff' => false),
    array('label' => '킥오프',  'pos' => 8,     'kickoff' => true),
    array('label' => '2W',     'pos' => 14.28, 'kickoff' => false),
    array('label' => '3W',     'pos' => 28.57, 'kickoff' => false),
    array('label' => '4W',     'pos' => 42.86, 'kickoff' => false),
    array('label' => '5W',     'pos' => 57.14, 'kickoff' => false),
    array('label' => '6W',     'pos' => 71.43, 'kickoff' => false),
    array('label' => '7W',     'pos' => 85.71, 'kickoff' => false),
    array('label' => '8W',     'pos' => 100,   'kickoff' => false),
);

// 단계 구간 (실제 비율 — 계획 30 / 제작 50 / 운영 20)
$stages = array(
    array('label' => '계획·전략', 'note' => '',                       'start' => 0,  'end' => 30),
    array('label' => '제작·준비', 'note' => '',                       'start' => 30, 'end' => 80),
    array('label' => '운영',     'note' => '유지보수 계약(선택)',       'start' => 80, 'end' => 100),
);

$rows = array(
    array(
        'key'   => 'project',
        'label' => '프로젝트 관리',
        'main'  => null,
        'subs'  => array(
            array('name' => '일정 수립',      'start' => 0, 'end' => 7,   'type' => 'gray'),
            array('name' => '프로젝트 매니징', 'start' => 7, 'end' => 100, 'type' => 'gray'),
        ),
    ),
    array(
        'key'   => 'plan',
        'label' => '기획',
        'main'  => array('name' => '기획', 'start' => 0, 'end' => 30),
        'subs'  => array(
            array('name' => '프로젝트 정의·요구사항 수집', 'start' => 0,  'end' => 15),
            array('name' => '사이트맵·와이어프레임 기획',  'start' => 15, 'end' => 30),
        ),
    ),
    array(
        'key'   => 'design',
        'label' => '디자인',
        'main'  => array('name' => '디자인', 'start' => 25, 'end' => 60),
        'subs'  => array(
            array('name' => '디자인시스템·콘텐츠 구성', 'start' => 25, 'end' => 37),
            array('name' => '메인/서브 시안 작업',  'start' => 37, 'end' => 48),
            array('name' => '반응형 최적화 디자인',  'start' => 48, 'end' => 60),
        ),
    ),
    array(
        'key'   => 'publish',
        'label' => '퍼블리싱',
        'main'  => array('name' => '퍼블리싱', 'start' => 50, 'end' => 85),
        'subs'  => array(
            array('name' => '웹표준 코딩',    'start' => 50, 'end' => 62),
            array('name' => '반응형 구축',       'start' => 62, 'end' => 73),
            array('name' => '관리자 모듈/커스텀 개발',  'start' => 73, 'end' => 85),
        ),
    ),
    array(
        'key'   => 'operation',
        'label' => '오픈·운영',
        'main'  => array('name' => '오픈·운영', 'start' => 80, 'end' => 100),
        'subs'  => array(
            array('name' => 'QA 검수 및 SEO', 'start' => 80, 'end' => 90),
            array('name' => '호스팅·도메인·SSL',     'start' => 90, 'end' => 100),
        ),
    ),
);
?>

<section class="main-schedule-con cm-section" id="cmSchedule">
  <div class="area">

    <div class="cm-tit-box" data-animate="fade-up">
      <span class="cm-tit-sub">체계적인 일정관리로 안정적인 결과물을 만들어 냅니다</span>
      <h2 class="cm-tit">홈페이지 제작과정</h2>
    </div>

    <!-- 모바일 전용: /service-process 와 동일한 마크업 재사용 -->
    <div class="main-schedule-process hidden-desktop" data-animate="fade-up" data-delay="200">
      <?php get_template_part('template-parts/common/service-process'); ?>
    </div>

    <div class="main-schedule-gantt hidden-mobile" data-animate="fade-up" data-delay="200">
      <div class="main-schedule-gantt-inner">

        <ul class="main-schedule-timeline" aria-hidden="true">
          <?php foreach ($timeline as $t) : ?>
          <li class="main-schedule-time<?php echo $t['kickoff'] ? ' is-kickoff' : ''; ?>"
              style="--pos: <?php echo (float) $t['pos']; ?>;">
            <?php echo esc_html($t['label']); ?>
          </li>
          <?php endforeach; ?>
        </ul>

        <div class="main-schedule-rows">
          <?php
          // % → W(주) 변환: 1W=0%, 8W=100% (7주 구간, 1주 ≒ 14.28%)
          $pct_to_week_label = function ($start, $end) {
              $w_start = (int) round(($start / 100) * 7 + 1);
              $w_end   = (int) round(($end   / 100) * 7 + 1);
              if ($w_start === $w_end) return $w_start . 'W';
              return $w_start . 'W ~ ' . $w_end . 'W';
          };

          foreach ($rows as $row_idx => $row) :
              $is_compact = empty($row['main']);
          ?>
          <div class="main-schedule-row<?php echo $is_compact ? ' is-compact' : ''; ?>"
               data-cat="<?php echo esc_attr($row['key']); ?>"
               style="--row-idx: <?php echo (int) $row_idx; ?>;">
            <div class="main-schedule-row-label" aria-hidden="true"><?php echo esc_html($row['label']); ?></div>
            <div class="main-schedule-track">

              <?php if (!empty($row['main'])) :
                  $range = $pct_to_week_label($row['main']['start'], $row['main']['end']);
                  $aria  = $row['main']['name'] . ', ' . $range;
              ?>
              <span class="main-schedule-bar is-main"
                    style="--start: <?php echo (float) $row['main']['start']; ?>; --end: <?php echo (float) $row['main']['end']; ?>;"
                    role="button"
                    tabindex="0"
                    aria-label="<?php echo esc_attr($aria); ?>"
                    data-range="<?php echo esc_attr($range); ?>"
                    data-start="<?php echo (float) $row['main']['start']; ?>"
                    data-end="<?php echo (float) $row['main']['end']; ?>">
                <span class="main-schedule-bar-text"><?php echo esc_html($row['main']['name']); ?></span>
              </span>
              <?php endif; ?>

              <?php foreach ($row['subs'] as $sub) :
                  $type  = isset($sub['type']) ? $sub['type'] : 'sub';
                  $range = $pct_to_week_label($sub['start'], $sub['end']);
                  $aria  = $sub['name'] . ', ' . $range;
              ?>
              <span class="main-schedule-bar is-<?php echo esc_attr($type); ?>"
                    style="--start: <?php echo (float) $sub['start']; ?>; --end: <?php echo (float) $sub['end']; ?>;"
                    role="button"
                    tabindex="0"
                    aria-label="<?php echo esc_attr($aria); ?>"
                    data-range="<?php echo esc_attr($range); ?>"
                    data-start="<?php echo (float) $sub['start']; ?>"
                    data-end="<?php echo (float) $sub['end']; ?>">
                <span class="main-schedule-bar-text"><?php echo esc_html($sub['name']); ?></span>
              </span>
              <?php endforeach; ?>

            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <ul class="main-schedule-stages">
          <?php foreach ($stages as $s) : ?>
          <li class="main-schedule-stage"
              style="--start: <?php echo (float) $s['start']; ?>; --end: <?php echo (float) $s['end']; ?>;">
            <span class="main-schedule-stage-label"><?php echo esc_html($s['label']); ?></span>
            <?php if (!empty($s['note'])) : ?>
            <span class="main-schedule-stage-note"><?php echo esc_html($s['note']); ?></span>
            <?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>

      </div>
    </div>

  </div>
</section>
