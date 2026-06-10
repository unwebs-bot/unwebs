<?php
/**
 * Template Part: 홈페이지 제작 — 플로팅 섹션 탭
 *
 *  - 헤더 바로 아래 sticky. 반응형 체험 섹션 통과 후 등장 (JS Observer)
 *  - 5개 탭: #info / #features / #process / #materials / #faq
 *  - 모바일(768 이하)에서는 display: none — JS 사용 안 함
 *  - JS는 assets/js/service-tabs.js 에서 enqueue
 */
if (!defined('ABSPATH'))
  exit;

$tabs = array(
  array('id' => 'info',      'label' => '서비스 안내'),
  array('id' => 'features',  'label' => '무료 제공항목'),
  array('id' => 'process',   'label' => '제작절차'),
  array('id' => 'materials', 'label' => '홈페이지 준비자료'),
  array('id' => 'faq',       'label' => '자주묻는질문'),
);
?>

<div class="service-tabs-wrap" data-service-tabs aria-hidden="true">
  <nav class="service-tabs" aria-label="페이지 섹션 이동">
    <ul class="service-tabs-list">
      <?php foreach ($tabs as $i => $t): ?>
        <li class="service-tabs-item<?php echo $i === 0 ? ' is-active' : ''; ?>">
          <a href="#<?php echo esc_attr($t['id']); ?>" class="service-tabs-link" data-target="<?php echo esc_attr($t['id']); ?>">
            <?php echo esc_html($t['label']); ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>
</div>
