<?php
/**
 * Template Name: 홈페이지 제작 (Service) — 통합 페이지
 *
 * /service — 단일 페이지. Hero(반응형 체험) 통과 후 플로팅 탭이 등장하며
 * 5개 섹션(#info / #features / #process / #materials / #faq)으로 앵커 이동.
 *  - 반응형 체험은 탭에 포함되지 않는 Hero
 *  - 드롭다운(GNB)은 기존 3개 유지: #info / #materials / #process
 * FAQ 배치: Q1, Q3, Q4, Q9, Q10, Q18 (3개 섹션 통합)
 */
get_header();
?>


<main class="cm-main" id="main-content" role="main">

  <h1 class="blind">홈페이지 제작 서비스</h1>

  <!-- ========== Hero: 반응형 체험 (탭 X) ========== -->
  <?php get_template_part('template-parts/common/responsive-demo'); ?>

  <!-- ========== 플로팅 섹션 탭 (PC 전용, 반응형 체험 통과 후 등장) ========== -->
  <?php get_template_part('template-parts/common/service-tabs'); ?>

  <!-- ========== #info — 서비스 안내 ========== -->
  <div id="info" class="service-anchor">
    <?php get_template_part('template-parts/common/service-info'); ?>
  </div>

  <!-- ========== #features — 무료 제공항목 ========== -->
  <div id="features" class="service-anchor">
    <?php get_template_part('template-parts/common/service-features'); ?>
  </div>

  <!-- ========== #process — 제작절차 ========== -->
  <div id="process" class="service-anchor">
    <!-- PC 전용: 간트 일정표 (모바일에선 service-process로 대체) -->
    <div class="hidden-mobile">
      <?php get_template_part('template-parts/main/section', 'schedule'); ?>
    </div>
    <section class="service-process-con sub-content-con">
      <?php get_template_part('template-parts/common/service-process'); ?>
    </section>
  </div>

  <!-- ========== #materials — 홈페이지 준비자료 ========== -->
  <?php
  $materials_steps = array(
    array(
      'tit'    => '로고 원본 파일 (AI, PNG, 고해상도 파일)',
      'desc'   => '홈페이지에 삽입될 로고 파일을 원본으로 전달해 주세요.',
      'layout' => 'compare',
    ),
    array(
      'tit'    => '준비 가이드 1, 2번 양식 작성',
      'desc'   => '다운로드한 준비 가이드를 양식에 맞춰 작성해 주세요.<br>사이트 기본 구성 확인, 하단 푸터 정보 삽입을 위해 사용됩니다.',
      'cta'    => array(
        'label'    => '가이드 다운로드',
        'url'      => get_theme_file_uri('/assets/files/unwebs-project-request.docx'),
        'download' => '언웹스 제작의뢰서 양식.docx',
      ),
      'layout' => 'forms',
    ),
    array(
      'tit'    => '메뉴 구조도 작성',
      'desc'   => '대메뉴와 하위에 들어가는 소메뉴를 작성해주세요.<br>소메뉴는 대메뉴에 마우스를 올릴 시 보여지는 하위메뉴입니다.',
      'layout' => 'table',
    ),
    array(
      'tit'    => '메뉴별 자료',
      'desc'   => '각 메뉴에 들어갈 텍스트 및 이미지 등 자료를 폴더에 넣어주세요.',
      'layout' => 'folder-steps',
    ),
    array(
      'tit'    => '기타 자료 (선택사항)',
      'desc'   => '홈페이지에 직접 삽입되지는 않더라도 <strong>회사소개서, 서비스 소개서</strong> 등 귀사 관련 파일 전달은 비즈니스 이해와 사전 기획에 큰 도움이 됩니다.',
      'layout' => 'file-icons',
    ),
    array(
      'tit'    => '자료 준비 방법 (예시 가이드)',
      'desc'   => '해당 자료 전달 방법을 참고하여 준비해주시길 바랍니다.<br>혹은 준비해두신 자료가 있다면 자유로운 형태로 전달해주셔도 좋습니다.',
      'layout' => 'video',
    ),
  );

  // 미디어 자동 매핑 — assets/images/content/service-materials/{n}.{webp,jpg,mp4,webm}
  $materials_media_dir = get_theme_file_path('/assets/images/content/service-materials');
  $materials_media_uri = get_theme_file_uri('/assets/images/content/service-materials');
  ?>
  <div id="materials" class="service-anchor">
    <section class="service-materials-con">
      <div class="area">

        <div class="cm-tit-box" data-animate="fade-up">
          <span class="cm-tit-sub" data-no-split>홈페이지 제작을 하기 위해 필요한 사전 자료 및 준비 방법</span>
          <h2 class="cm-tit">홈페이지 준비자료</h2>
        </div>

        <div class="materials-card">
          <ol class="materials-steps">
            <?php foreach ($materials_steps as $i => $s):
              $n = $i + 1;
              $is_video   = ($s['layout'] === 'video');
              $has_webp   = file_exists("$materials_media_dir/$n.webp");
              $has_jpg    = file_exists("$materials_media_dir/$n.jpg");
              $has_mp4    = file_exists("$materials_media_dir/$n.mp4");
              $has_webm   = file_exists("$materials_media_dir/$n.webm");
              $has_poster = file_exists("$materials_media_dir/$n-poster.jpg");
              $has_image  = $has_webp || $has_jpg;
              $has_videof = $has_mp4 || $has_webm;
              $has_media  = $has_image || $has_videof;
            ?>
              <li class="materials-step" data-layout="<?php echo esc_attr($s['layout']); ?>" data-animate="fade-up">
                <span class="materials-step-num" aria-hidden="true"><?php echo $n; ?></span>
                <div class="materials-step-body">
                  <div class="materials-step-head">
                    <h3 class="materials-step-tit"><?php echo esc_html($s['tit']); ?></h3>
                    <?php if (!empty($s['cta'])): ?>
                      <a class="materials-step-cta" href="<?php echo esc_url($s['cta']['url']); ?>"<?php echo !empty($s['cta']['download']) ? ' download="' . esc_attr($s['cta']['download']) . '"' : ''; ?>>
                        <i class="xi-download" aria-hidden="true"></i>
                        <span><?php echo esc_html($s['cta']['label']); ?></span>
                      </a>
                    <?php endif; ?>
                  </div>
                  <p class="materials-step-desc"><?php echo wp_kses($s['desc'], array('br' => array(), 'strong' => array(), 'b' => array())); ?></p>
                  <?php if ($has_media): ?>
                    <div class="materials-step-media<?php echo $is_video && $has_videof ? ' has-video' : ($has_image ? ' has-image' : ''); ?>" data-layout="<?php echo esc_attr($s['layout']); ?>">
                      <?php if ($is_video && $has_videof): ?>
                        <video class="materials-step-video"
                               autoplay muted loop playsinline preload="metadata"
                               <?php echo $has_poster ? 'poster="' . esc_url("$materials_media_uri/$n-poster.jpg") . '"' : ''; ?>>
                          <?php if ($has_webm): ?>
                            <source src="<?php echo esc_url("$materials_media_uri/$n.webm"); ?>" type="video/webm">
                          <?php endif; ?>
                          <?php if ($has_mp4): ?>
                            <source src="<?php echo esc_url("$materials_media_uri/$n.mp4"); ?>" type="video/mp4">
                          <?php endif; ?>
                        </video>
                        <button type="button" class="materials-step-video-toggle" aria-label="정지" data-state="playing">
                          <svg class="materials-step-video-icon materials-step-video-icon-pause" viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="6" y="5" width="4" height="14" rx="1" fill="currentColor"/>
                            <rect x="14" y="5" width="4" height="14" rx="1" fill="currentColor"/>
                          </svg>
                          <svg class="materials-step-video-icon materials-step-video-icon-play" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M8 5.14v13.72a1 1 0 0 0 1.55.83l10.29-6.86a1 1 0 0 0 0-1.66L9.55 4.31A1 1 0 0 0 8 5.14z" fill="currentColor"/>
                          </svg>
                        </button>
                      <?php elseif ($has_image): ?>
                        <picture>
                          <?php if ($has_webp): ?>
                            <source srcset="<?php echo esc_url("$materials_media_uri/$n.webp"); ?>" type="image/webp">
                          <?php endif; ?>
                          <img src="<?php echo esc_url("$materials_media_uri/$n." . ($has_jpg ? 'jpg' : 'webp')); ?>"
                               alt="<?php echo esc_attr($s['tit']); ?>"
                               loading="lazy" decoding="async">
                        </picture>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ol>
        </div>

      </div>
    </section>
  </div>

  <!-- ========== #faq — 자주묻는질문 (메인페이지 섹션 재사용) ========== -->
  <div id="faq" class="service-anchor">
    <?php get_template_part('template-parts/main/section', 'faq'); ?>
  </div>

</main>

<?php get_footer(); ?>
