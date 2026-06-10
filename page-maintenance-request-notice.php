<?php
/**
 * Template Name: 유지보수 신청 - 유의사항 (Maintenance Request Notice)
 *
 * figma 117:398. 로그인 + maintenance_client 역할 필수 (enforce_access).
 *
 * 구성:
 *  1) Hero — 유지보수 신청 / 신청 시 유의사항
 *  2) 유의사항 박스 (반드시 지켜주시기 바랍니다)
 *  3) 자료 정리 방법 (STEP 1-3)
 *  4) 수정 표시 방법 (예시 + 4개 마커)
 *  5) 최하단 신청 폼 (uw_inquiry)
 */
get_header();

$current_user = wp_get_current_user();
$user_company = get_user_meta($current_user->ID, 'uw_company', true);
$user_phone   = get_user_meta($current_user->ID, 'uw_phone',   true);
$user_name    = get_user_meta($current_user->ID, 'uw_name',    true) ?: $current_user->display_name;
$user_email   = $current_user->user_email;

// 유지보수 신청 폼 (uw_inquiry_form CPT, slug=maintenance-request)
$form_post = get_page_by_path('maintenance-request', OBJECT, 'uw_inquiry_form');
$form_id   = $form_post ? $form_post->ID : 0;

// 수정요청서 다운로드 URL — pptx/docx/pdf 자동 인식
$download_url = '';
$download_filename = '';
foreach (array('pptx', 'docx', 'pdf') as $ext) {
    $p = get_theme_file_path('/assets/files/maintenance-request-form.' . $ext);
    if (file_exists($p)) {
        $download_url      = get_theme_file_uri('/assets/files/maintenance-request-form.' . $ext);
        $download_filename = '수정요청서.' . $ext;
        break;
    }
}
?>


<main class="cm-main" id="main-content" role="main">

  <h1 class="blind">유지보수 신청 유의사항</h1>

  <!-- ========== Hero ========== -->
  <section class="mrn-intro-con sub-content-con cm-sub-hero">
    <div class="area">
      <div class="cm-tit-box" data-animate="fade-up">
        <span class="cm-tit-sub">유지보수 신청</span>
        <h2 class="cm-tit">신청 시 유의사항</h2>
      </div>
    </div>
  </section>

  <!-- ========== 반드시 지켜주시기 바랍니다 ========== -->
  <section class="mrn-notice-con">
    <div class="area">
      <div class="mrn-notice-box" data-animate="fade-up">
        <span class="mrn-notice-mark" aria-hidden="true">!</span>
        <div class="mrn-notice-body">
          <h3 class="mrn-notice-tit">반드시 지켜주시기 바랍니다.</h3>
          <p class="mrn-notice-txt">
            요구사항이 명확하지 않거나 자료 전달이 부정확할 경우, 작업이 후순위로 밀리거나 접수 자체가 반려될 수 있으며,<br>
            잘못된 정보로 인한 수정 작업은 중복 비용이 발생할 수 있습니다.
          </p>
          <p class="mrn-notice-txt">정확한 작업을 위해, 아래 2가지 사항을 반드시 숙지 후 신청해주시기 바랍니다.</p>
          <ul class="mrn-notice-list">
            <li><strong>1. 자료 정리 방법</strong></li>
            <li><strong>2. 수정 표시 방법</strong></li>
          </ul>
          <p class="mrn-notice-txt">
            위 2가지 사항을 꼭 확인하고, 지정된 방식에 맞춰 신청해주시기 바랍니다.<br>
            작업의 정확도와 효율을 높이기 위해 꼭 협조 부탁드립니다.
          </p>
          <?php if ($download_url): ?>
            <a href="<?php echo esc_url($download_url); ?>" class="mrn-download-btn" download="<?php echo esc_attr($download_filename); ?>">
              <i class="xi-download" aria-hidden="true"></i>
              <span>수정요청서 양식 다운로드</span>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== 1. 자료 정리 방법 ========== -->
  <section class="mrn-section-con">
    <div class="area">
      <div class="mrn-section-head" data-animate="fade-up">
        <span class="mrn-section-num" aria-hidden="true">1</span>
        <div class="mrn-section-head-txt">
          <h3 class="mrn-section-tit">자료 정리 방법</h3>
          <p class="mrn-section-sub">직접 관리를 하시는 분들을 위한 항목별 회당 업데이트 및 수정 서비스</p>
        </div>
      </div>

      <div class="mrn-steps" data-stagger>
        <div class="mrn-step" data-animate="fade-up">
          <span class="mrn-step-label">STEP 1.</span>
          <div class="mrn-step-illust" aria-hidden="true">
            <div class="mrn-folder-row">
              <div class="mrn-folder is-dim"><i class="xi-folder"></i><span>1. 메인페이지</span></div>
              <div class="mrn-folder"><i class="xi-folder"></i><span>2. 회사소개</span></div>
              <div class="mrn-folder is-dim"><i class="xi-folder"></i><span>3. 서비스소개</span></div>
            </div>
            <div class="mrn-tag-row">
              <span class="mrn-tag">2-1. 인사말</span>
              <span class="mrn-tag">2-2. 연혁</span>
              <span class="mrn-tag">2-3. 조직도</span>
            </div>
          </div>
          <div class="mrn-step-body">
            <h4 class="mrn-step-tit">수정이 필요한 페이지에 해당하는<br>폴더를 만들어 줍니다.</h4>
            <p class="mrn-step-txt">하위메뉴가 있다면 대메뉴 폴더 아래 추가로 생성해주세요</p>
          </div>
        </div>

        <div class="mrn-step" data-animate="fade-up">
          <span class="mrn-step-label">STEP 2.</span>
          <div class="mrn-step-illust" aria-hidden="true">
            <div class="mrn-folder mrn-folder-lg"><i class="xi-folder-open-o"></i><span>2-1. 인사말</span></div>
          </div>
          <div class="mrn-step-body">
            <h4 class="mrn-step-tit">수정할 영역에 해당하는<br>페이지 폴더를 열어주세요</h4>
            <p class="mrn-step-txt">하위메뉴가 있다면 대메뉴 폴더 아래 추가로 생성해주세요</p>
          </div>
        </div>

        <div class="mrn-step" data-animate="fade-up">
          <span class="mrn-step-label">STEP 3.</span>
          <div class="mrn-step-illust" aria-hidden="true">
            <div class="mrn-file-card">
              <div class="mrn-file-card-head">
                <span>대표님 프로필</span>
                <span class="mrn-file-card-sep"></span>
                <span>인사말 텍스트</span>
              </div>
              <div class="mrn-file-card-body">
                <i class="xi-image-o"></i>
                <i class="xi-document"></i>
              </div>
            </div>
            <i class="xi-long-arrow-down mrn-arrow-down" aria-hidden="true"></i>
            <div class="mrn-folder"><i class="xi-folder"></i><span>2-1. 인사말</span></div>
          </div>
          <div class="mrn-step-body">
            <h4 class="mrn-step-tit">수정할 자료와 설명을 수정할 페이지<br>폴더에 해당 페이지에 넣어주세요.</h4>
            <p class="mrn-step-txt">해당 페이지 자료를 정리해서 전달해주세요.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== 2. 수정 표시 방법 ========== -->
  <section class="mrn-section-con">
    <div class="area">
      <div class="mrn-section-head" data-animate="fade-up">
        <span class="mrn-section-num" aria-hidden="true">2</span>
        <div class="mrn-section-head-txt">
          <h3 class="mrn-section-tit">수정 표시 방법</h3>
          <p class="mrn-section-sub">
            수정사항을 텍스트 형태로만 설명하기 어려운 경우, <strong>해당 페이지를 캡쳐 하신 후 그 위에 수정할 내용을 작성</strong>해 주세요.<br>
            수정 내용을 정리하기 어려우시다면, <a href="<?php echo esc_url($download_url); ?>" class="mrn-inline-link">아래 요청서 양식을 다운</a>받아 편하게 작성해 주세요.
          </p>
        </div>
      </div>

      <div class="mrn-edit-download" data-animate="fade-up">
        <a href="<?php echo esc_url($download_url); ?>" class="mrn-download-btn" download>
          <i class="xi-download" aria-hidden="true"></i>
          <span>수정요청서 양식 다운로드</span>
        </a>
      </div>

      <?php
      // example.jpg / example.png 둘 다 인식
      $example_dir  = '/assets/images/content/maintenance-request-notice';
      $example_path = '';
      $example_url  = '';
      foreach (array('jpg', 'png', 'webp') as $ext) {
          $p = get_theme_file_path($example_dir . '/example.' . $ext);
          if (file_exists($p)) {
              $example_path = $p;
              $example_url  = get_theme_file_uri($example_dir . '/example.' . $ext);
              break;
          }
      }
      ?>
      <div class="mrn-example<?php echo $example_url ? ' has-image' : ''; ?>" data-animate="fade-up">
        <div class="mrn-example-preview">
          <?php if ($example_url): ?>
            <img class="mrn-example-img" src="<?php echo esc_url($example_url); ?>" alt="수정 표시 방법 예시 — 캡쳐 위에 빨간 번호로 수정 영역 표시" loading="lazy">
          <?php else: ?>
            <div class="mrn-example-img-placeholder">
              <i class="xi-image-o" aria-hidden="true"></i>
              <p>웹페이지 캡쳐 예시</p>
              <small>assets/images/content/maintenance-request-notice/example.jpg 업로드 시 자동 표시</small>
            </div>
            <!-- 이미지 없을 때만 CSS 마커 오버레이 (이미지 자체에 마커가 그려진 경우엔 중복 방지) -->
            <span class="mrn-marker mrn-marker-1">1</span>
            <span class="mrn-marker mrn-marker-2">2</span>
            <span class="mrn-marker mrn-marker-3">3</span>
            <span class="mrn-marker mrn-marker-4">4</span>
          <?php endif; ?>
        </div>

        <ol class="mrn-example-notes">
          <li class="mrn-example-note">
            <span class="mrn-example-num">1</span>
            <div>
              <strong>배경 이미지 변경</strong>
              <p>첨부 파일 중 OOO.jpg 이미지로 변경</p>
            </div>
          </li>
          <li class="mrn-example-note">
            <span class="mrn-example-num">2</span>
            <div>
              <strong>텍스트 변경</strong>
              <p><em>기존</em> 에너지 절감과 새로운 공간을 창조하는 스마트 틴팅 기술의 글로벌 기업이 되겠습니다.</p>
              <p><em>변경</em> 에너지 효율 혁신과 미래 공간을 재정의하는 스마트 틴팅 솔루션의 글로벌 리더로 성장하겠습니다.</p>
            </div>
          </li>
          <li class="mrn-example-note">
            <span class="mrn-example-num">3</span>
            <div>
              <strong>버튼 디자인 변경</strong>
              <p>기존 디자인의 둥글고 귀여운 느낌보다는 각지고 모던하게 바꾸고 싶습니다.<br>첨부한 이미지처럼 네모난 형식으로 수정해주세요. [첨부이미지: 버튼 레퍼런스.png]</p>
              <p class="mrn-example-bad"><span class="mrn-example-bad-tag">잘못된 예시</span> 더 깔끔하고 세련되게 수정해주세요 (X)</p>
            </div>
          </li>
          <li class="mrn-example-note">
            <span class="mrn-example-num">4</span>
            <div>
              <strong>섹션 삭제</strong>
              <p>특허 관련된 섹션은 전체 삭제해 주세요.</p>
            </div>
          </li>
        </ol>
      </div>
    </div>
  </section>

  <!-- ========== 신청 폼 ========== -->
  <section id="request-form" class="mrn-form-con sub-content-con">
    <div class="area">
      <div class="cm-tit-box" data-animate="fade-up">
        <span class="cm-tit-sub">REQUEST FORM</span>
        <h2 class="cm-tit">유지보수 신청</h2>
        <p class="cm-tit-txt">위 안내를 확인하셨다면 아래 폼을 작성해주세요.</p>
      </div>

      <div class="mrn-form-wrap" data-animate="fade-up" data-delay="200">
        <?php if ($form_id): ?>
          <?php echo do_shortcode('[uw_inquiry_form id="' . esc_attr($form_id) . '"]'); ?>

          <!-- 개인정보 처리방침 모달 -->
          <div class="mrn-privacy-modal" id="mrnPrivacyModal" role="dialog" aria-modal="true" aria-labelledby="mrnPrivacyTitle" aria-hidden="true">
            <div class="mrn-privacy-modal-overlay" data-mrn-privacy-close></div>
            <div class="mrn-privacy-modal-box" role="document">
              <div class="mrn-privacy-modal-head">
                <h3 class="mrn-privacy-modal-tit" id="mrnPrivacyTitle">개인정보 처리방침</h3>
                <button type="button" class="mrn-privacy-modal-close" aria-label="닫기" data-mrn-privacy-close>
                  <i class="xi-close" aria-hidden="true"></i>
                </button>
              </div>
              <div class="mrn-privacy-modal-body" id="mrnPrivacyBody">
                <!-- 본문은 JS가 uw-inquiry-privacy-text에서 이동/복사 -->
              </div>
              <div class="mrn-privacy-modal-foot">
                <button type="button" class="cm-btn cm-btn-primary" data-mrn-privacy-close>확인</button>
              </div>
            </div>
          </div>

          <!-- 로그인 사용자 정보 자동 채움 + 처리방침 모달 토글 -->
          <script>
          (function(){
            'use strict';
            var prefill = {
              field_company: <?php echo wp_json_encode($user_company); ?>,
              field_name:    <?php echo wp_json_encode($user_name); ?>,
              field_email:   <?php echo wp_json_encode($user_email); ?>,
              field_phone:   <?php echo wp_json_encode($user_phone); ?>
            };

            function ready(fn){
              if (document.readyState !== 'loading') fn();
              else document.addEventListener('DOMContentLoaded', fn);
            }

            ready(function(){
              // 1) 입력 prefill
              Object.keys(prefill).forEach(function(name){
                if (!prefill[name]) return;
                var el = document.querySelector('.mrn-form-wrap [name="' + name + '"]');
                if (el && !el.value) el.value = prefill[name];
              });

              // 2) 모달 처리
              var modal       = document.getElementById('mrnPrivacyModal');
              var modalBody   = document.getElementById('mrnPrivacyBody');
              var privacyText = document.querySelector('.mrn-form-wrap .uw-inquiry-privacy-text');
              var labelEl     = document.querySelector('.mrn-form-wrap .uw-inquiry-privacy-label');

              if (privacyText && modalBody) {
                modalBody.innerHTML = privacyText.innerHTML;
              }

              // 모달을 body 직속으로 이동 — transform/animation 부모 escape (position:fixed 안정)
              if (modal && modal.parentNode !== document.body) {
                document.body.appendChild(modal);
              }

              // "자세히 보기" 링크 inject
              if (labelEl) {
                var link = document.createElement('a');
                link.href = '#';
                link.className = 'mrn-privacy-more';
                link.textContent = '자세히 보기';
                link.setAttribute('role', 'button');
                link.addEventListener('click', function(e){
                  e.preventDefault();
                  openModal();
                });
                labelEl.appendChild(document.createTextNode(' '));
                labelEl.appendChild(link);
              }

              var savedScrollY = 0;

              function openModal(){
                if (!modal) return;
                savedScrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
                // 스크롤바 폭 보전 (jump 방지)
                var sbw = window.innerWidth - document.documentElement.clientWidth;
                if (sbw > 0) document.body.style.paddingRight = sbw + 'px';
                document.documentElement.classList.add('mrn-modal-lock');
                document.body.classList.add('mrn-modal-lock');
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                // 닫기 버튼에 포커스
                var closeBtn = modal.querySelector('.mrn-privacy-modal-close');
                if (closeBtn) setTimeout(function(){ closeBtn.focus(); }, 50);
              }
              function closeModal(){
                if (!modal) return;
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.documentElement.classList.remove('mrn-modal-lock');
                document.body.classList.remove('mrn-modal-lock');
                document.body.style.paddingRight = '';
                // 스크롤 위치 복원 (브라우저가 강제로 0으로 보낸 경우 대비)
                window.scrollTo(0, savedScrollY);
              }

              if (modal) {
                modal.querySelectorAll('[data-mrn-privacy-close]').forEach(function(el){
                  el.addEventListener('click', closeModal);
                });
                document.addEventListener('keydown', function(e){
                  if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
                });
              }
            });
          })();
          </script>
        <?php else: ?>
          <div class="mrn-form-fallback">
            <p><strong>유지보수 신청 폼이 아직 등록되지 않았습니다.</strong></p>
            <p>관리자 페이지에서 <code>uw_inquiry_form</code> CPT로 <code>maintenance-request</code> 슬러그 폼을 생성해주세요.</p>
            <p>또는 임시로 <a href="mailto:contact@unwebs.co.kr">contact@unwebs.co.kr</a>로 신청 내용을 보내주세요.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

</main>

<?php get_footer(); ?>
