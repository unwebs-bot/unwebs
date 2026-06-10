<?php
/**
 * Template Part: 제작 절차 (Service Process)
 *
 * 사용처:
 *  - page-service-process.php (/service-process)
 *  - template-parts/main/section-schedule.php (모바일 전용 표시)
 *
 * 출력: <section> 내부 콘텐츠 (모바일 헤더 + sticky 사이드바 + 블록)
 *  - 호출측에서 <section> 래퍼 + id 지정
 */
if (!defined('ABSPATH')) exit;

$service_process_steps = array(
    array(
        'label'   => '상담 · 기획',
        'summary' => '목표와 구조를 먼저 정리합니다',
        'txt'     => '전문 컨설턴트와의 홈페이지 제작에 대한 1:1 맞춤 컨설팅을 진행합니다. <br>프로젝트 진행이 확정되면 원하는 컨셉과 방향, 콘텐츠 등의 기획을 정의합니다. ',
        'client'  => array(
            '제작 목적 · 타겟 · 사이트맵 정리',
            '참고 레퍼런스 사이트 2~3개',
            '희망 예산 · 오픈 일정 공유',
        ),
        'owner'   => array(
            '12시간 내 견적 회신',
            '사이트맵 · 페이지 구조 설계',
            '단계별 일정 및 체크리스트 제안',
        ),
    ),
    array(
        'label'   => '디자인 시안',
        'summary' => '브랜드 톤에 맞춘 시안을 제작합니다',
        'txt'     => '작성한 기획안을 토대로 UI/UX를 고려한 <b>메인 + 주요 서브 페이지</b> 디자인 시안을 작업합니다.<br>PC·모바일 뷰를 함께 설계하고, 2회 수정이 기본 포함됩니다.',
        'client'  => array(
            '로고 원본 · 브랜드 컬러 · 폰트 가이드',
            '페이지별 텍스트 원고 확정',
            '매장·제품·시설 사진 등 이미지 자료',
        ),
        'owner'   => array(
            '메인 · 서브 시안 제작',
            'PC · 모바일 반응형 뷰 동시 설계',
            '시안 뷰어 링크 공유 및 수정 반영',
        ),
    ),
    array(
        'label'   => '퍼블리싱 · 개발',
        'summary' => '반응형 코드와 기능을 구현합니다',
        'txt'     => '컨펌이 완료된 디자인 시안을 바탕으로 웹 퍼블리싱 및 기능 개발을 진행합니다.<br><b>PC · 태블릿 · 모바일</b> 등 반응형으로 구현하며, 작업 중에는 임시 URL이 발급되어 실시간으로 진행 상황을 확인할 수 있습니다.',
        'client'  => array(
            '디자인 최종 컨펌',
            '플러그인 · 커스텀 기능 요구 최종 확정',
            '진행 중 피드백 회신',
        ),
        'owner'   => array(
            '커스텀 테마 퍼블리싱',
            '커스텀 기능 · 게시판 · 폼 개발',
            'PC / 태블릿 / 모바일 3단 검수',
        ),
    ),
    array(
        'label'   => 'QA · 납품',
        'summary' => '운영 환경까지 세팅해 전달합니다',
        'txt'     => '내부 QA(브라우저 호환 · 속도 · SEO)를 거친 뒤 <b>호스팅 · 도메인 · SSL · 메일</b> 운영 환경까지 직접 세팅해 납품합니다.<br>관리자 매뉴얼을 함께 전달하며, 오픈 이후에도 월 단위 구독형 유지보수로 안정 운영을 지원합니다.',
        'client'  => array(
            '최종 검수 및 수정사항 확정',
            '도메인 · 호스팅 계정 정보 공유',
            '오픈 일정 확정',
        ),
        'owner'   => array(
            '크로스 브라우저 · 속도 · SEO QA',
            '호스팅 · 도메인 · SSL · SMTP 세팅',
            '관리자 사용 매뉴얼 전달',
            '정기 유지보수 연계 안내',
        ),
    ),
);
?>

<!-- 모바일 전용 상단 헤더 -->
<div class="service-process-mobile-header">
  <div class="cm-tit-box" data-animate="fade-up">
    <span class="cm-tit-sub">PROCESS</span>
    <h2 class="cm-tit">제작 절차</h2>
  </div>
</div>

<div class="area service-process-wrap">

  <!-- 왼쪽: sticky sidebar -->
  <aside class="service-process-side">
    <div class="service-process-sticky">
      <div class="cm-tit-box" data-animate="fade-up">
        <span class="cm-tit-sub">PROCESS</span>
        <h2 class="cm-tit">제작 절차<br>4단계로 완성합니다</h2>
      </div>
      <ol class="service-process-step-list">
        <?php foreach ($service_process_steps as $i => $step) : ?>
        <li class="service-process-step<?php echo $i === 0 ? ' is-active' : ''; ?>" data-step="<?php echo $i + 1; ?>">
          <span class="service-process-step-num"><?php echo sprintf('%02d', $i + 1); ?></span>
          <span class="service-process-step-label"><?php echo esc_html($step['label']); ?></span>
        </li>
        <?php endforeach; ?>
      </ol>
    </div>
  </aside>

  <!-- 오른쪽: scrollable content -->
  <?php
  $process_img_dir = get_theme_file_path('/assets/images/content/service-process');
  $process_img_uri = get_theme_file_uri('/assets/images/content/service-process');
  ?>
  <div class="service-process-blocks">
    <?php foreach ($service_process_steps as $i => $step) :
      $n = $i + 1;
      $has_webp = file_exists("$process_img_dir/process-$n.webp");
      $has_jpg  = file_exists("$process_img_dir/process-$n.jpg");
      $has_img  = $has_webp || $has_jpg;
    ?>
    <article class="service-process-block" data-index="<?php echo $i; ?>">
      <div class="service-process-img" aria-hidden="true">
        <?php if ($has_img): ?>
          <picture>
            <?php if ($has_webp): ?>
              <source srcset="<?php echo esc_url("$process_img_uri/process-$n.webp"); ?>" type="image/webp">
            <?php endif; ?>
            <img src="<?php echo esc_url("$process_img_uri/process-$n." . ($has_jpg ? 'jpg' : 'webp')); ?>"
                 alt="<?php echo esc_attr($step['label']); ?>"
                 loading="lazy" decoding="async">
          </picture>
        <?php else: ?>
          <span class="service-process-img-placeholder"><?php echo sprintf('%02d', $n); ?></span>
        <?php endif; ?>
      </div>
      <h3 class="service-process-block-label"><?php echo esc_html($step['label']); ?></h3>
      <p class="service-process-block-txt"><?php echo wp_kses($step['txt'], array('b' => array(), 'strong' => array(), 'br' => array())); ?></p>

      <div class="service-process-roles" aria-label="역할 분담">
        <div class="service-process-role service-process-role-client">
          <span class="service-process-role-tag">고객</span>
          <ul class="service-process-role-list">
            <?php foreach ($step['client'] as $item) : ?>
              <li><?php echo esc_html($item); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="service-process-role service-process-role-owner">
          <span class="service-process-role-tag">언웹스</span>
          <ul class="service-process-role-list">
            <?php foreach ($step['owner'] as $item) : ?>
              <li><?php echo esc_html($item); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</div>
