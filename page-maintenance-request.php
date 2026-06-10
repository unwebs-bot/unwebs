<?php
/**
 * Template Name: 유지보수 신청 (Maintenance Request)
 *
 * 구성:
 *  1) Intro Hero — cm-tit-box
 *  2) 신청 3단계 프로세스 카드
 *  3) CTA — 폼 섹션으로 앵커
 *  4) 유지보수 항목 및 비용 안내(Notice)
 *  5) 비용 안내 테이블 (건별 / 부가 서비스 2열)
 *  6) 신청 폼 — uw_inquiry
 */
get_header();

// 건별 유지보수 / 부가 서비스 비용표 (좌·우 2열)
// 건별 유지보수 핵심 8개 — /maintenance-pricing 데이터와 가격/항목명 동기화. 세부설명(sub)은 신청 페이지 디자인상 생략
$mr_fees_left = array(
    array('tit' => '텍스트 수정',                 'sub' => '', 'cost' => '30,000원~'),
    array('tit' => '이미지 단순 교체',            'sub' => '', 'cost' => '30,000원~'),
    array('tit' => '서브 페이지 추가',            'sub' => '', 'cost' => '100,000원~'),
    array('tit' => '메인 페이지 섹션 추가/변경',  'sub' => '', 'cost' => '150,000원~'),
    array('tit' => '팝업 디자인 및 업로드',       'sub' => '', 'cost' => '100,000원~'),
    array('tit' => '배너 디자인 및 업로드',       'sub' => '', 'cost' => '80,000원~'),
    array('tit' => '게시판 추가 (일반·갤러리)',   'sub' => '', 'cost' => '100,000원~'),
    array('tit' => '폼 메일 제작/변경',           'sub' => '', 'cost' => '200,000원~'),
);
// 부가 서비스 핵심 8개 — /maintenance-pricing 항목명/가격 동기화
$mr_fees_right = array(
    array('tit' => '문자알림 셋팅',           'sub' => '', 'cost' => '50,000원~'),
    array('tit' => '메일 알림 셋팅',          'sub' => '', 'cost' => '50,000원~'),
    array('tit' => '도메인 이메일 셋팅',      'sub' => '', 'cost' => '150,000원~'),
    array('tit' => '네임서버 변경',           'sub' => '', 'cost' => '100,000원~'),
    array('tit' => '실시간 상담 채널 추가',   'sub' => '', 'cost' => '150,000원~'),
    array('tit' => '방문자 분석 도구 설치',   'sub' => '', 'cost' => '200,000원~'),
    array('tit' => '지도 서비스 변경 (네이버/카카오)', 'sub' => '', 'cost' => '100,000원~'),
    array('tit' => '서버 이전',               'sub' => '', 'cost' => '별도협의'),
);
$mr_fee_rows = max(count($mr_fees_left), count($mr_fees_right));
?>


<main class="cm-main" id="main-content" role="main">

  <h1 class="blind">유지보수 신청</h1>

  <!-- ========== Hero ========== -->
  <section class="maintenance-request-intro-con sub-content-con cm-sub-hero">
    <div class="area">
      <div class="cm-tit-box" data-animate="fade-up">
        <span class="cm-tit-sub">
          수정사항을 접수해 주세요.<br>
          접수 후 72시간 내에 완료 후 회신드립니다.</span>
        <h2 class="cm-tit">유지보수 신청</h2>
      </div>
    </div>
  </section>

  <!-- ========== 신청 절차 3단계 ========== -->
  <section class="maintenance-request-process-con">
    <div class="area">
      <div class="maintenance-request-process-card" data-animate="fade-up">
        <ol class="maintenance-request-process-list" data-stagger>
          <li class="maintenance-request-process-item" data-animate="fade-up">
            <span class="maintenance-request-process-step">STEP.01</span>
            <span class="maintenance-request-process-icon" aria-hidden="true">
              <i class="xi-log-in"></i>
            </span>
            <h3 class="maintenance-request-process-tit">언웹스 유지보수 로그인</h3>
            <p class="maintenance-request-process-txt">
              유지보수 신청은 언웹스 고객 전용 서비스로<br>
              먼저 로그인을 해주세요.
            </p>
          </li>
          <li class="maintenance-request-process-arrow" aria-hidden="true">
            <i class="xi-angle-right"></i>
          </li>
          <li class="maintenance-request-process-item" data-animate="fade-up">
            <span class="maintenance-request-process-step">STEP.02</span>
            <span class="maintenance-request-process-icon" aria-hidden="true">
              <i class="xi-upload"></i>
            </span>
            <h3 class="maintenance-request-process-tit">유지보수 접수</h3>
            <p class="maintenance-request-process-txt">
              신청 전 유의사항을 숙지한 후 접수합니다.<br>
              불명확한 요청은 지연될 수 있습니다.
            </p>
          </li>
          <li class="maintenance-request-process-arrow" aria-hidden="true">
            <i class="xi-angle-right"></i>
          </li>
          <li class="maintenance-request-process-item" data-animate="fade-up">
            <span class="maintenance-request-process-step">STEP.03</span>
            <span class="maintenance-request-process-icon" aria-hidden="true">
              <i class="xi-check-square-o"></i>
            </span>
            <h3 class="maintenance-request-process-tit">접수 확인 및 진행 안내</h3>
            <p class="maintenance-request-process-txt">
              요청 내용을 바탕으로 작업 범위와 비용을 안내드리며,<br>
              결제 완료 후 작업이 시작됩니다.
            </p>
          </li>
        </ol>
      </div>

      <?php
      $mr_notice_url = home_url('/maintenance-request-notice/');
      $mr_cta_url    = is_user_logged_in()
          ? $mr_notice_url
          : add_query_arg('redirect_to', rawurlencode('/maintenance-request-notice/'), home_url('/login/'));
      ?>
      <div class="maintenance-request-cta-wrap" data-animate="fade-up">
        <a href="<?php echo esc_url($mr_cta_url); ?>" class="cm-btn cm-btn-primary maintenance-request-cta">유지보수 신청하기</a>
      </div>
    </div>
  </section>

  <!-- ========== 유지보수 항목 및 비용 안내 ========== -->
  <section class="maintenance-request-notice-con">
    <div class="area">
      <div class="maintenance-request-notice-box" data-animate="fade-up">
        <span class="maintenance-request-notice-mark" aria-hidden="true">!</span>
        <div class="maintenance-request-notice-body">
          <h3 class="maintenance-request-notice-tit">유지보수 항목 및 비용 안내</h3>
          <p class="maintenance-request-notice-txt">
            유지보수 작업은 요청하신 내용의 작업 난이도 및 소요 시간(공수)에 따라 추가 비용이 발생할 수 있습니다.<br>
            비용 발생 시, 상세 내역을 메일로 안내드리며, 고객님의 결제 동의 후에만 작업이 진행됩니다.
          </p>
          <p class="maintenance-request-notice-txt">
            <strong>정기 유지보수 계약 고객의 경우,</strong><br>
            기본 제공 범위를 초과한 작업(예: 신규 기능 추가, 디자인 개편 등)에 한해서만 별도 비용이 발생하며,<br>
            이 또한 사전 안내 및 동의 절차를 거쳐 처리됩니다.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== 비용 안내 테이블 ========== -->
  <section class="maintenance-request-fee-con">
    <div class="area">
      <div class="maintenance-request-fee-wrap" data-animate="fade-up">
        <table class="maintenance-request-fee-table">
          <colgroup>
            <col style="width:25%"><col style="width:25%"><col style="width:25%"><col style="width:25%">
          </colgroup>
          <thead>
            <tr class="maintenance-request-fee-grouphead">
              <th colspan="2" scope="colgroup">건별 유지보수</th>
              <th colspan="2" scope="colgroup">부가 서비스</th>
            </tr>
            <tr class="maintenance-request-fee-subhead">
              <th scope="col">구분</th>
              <th scope="col">비용</th>
              <th scope="col">구분</th>
              <th scope="col">비용</th>
            </tr>
          </thead>
          <tbody>
            <?php for ($i = 0; $i < $mr_fee_rows; $i++):
              $l = isset($mr_fees_left[$i])  ? $mr_fees_left[$i]  : null;
              $r = isset($mr_fees_right[$i]) ? $mr_fees_right[$i] : null;
            ?>
              <tr>
                <th scope="row" class="maintenance-request-fee-cat">
                  <?php if ($l): ?>
                    <strong><?php echo esc_html($l['tit']); ?></strong>
                    <?php if (!empty($l['sub'])): ?><span><?php echo esc_html($l['sub']); ?></span><?php endif; ?>
                  <?php endif; ?>
                </th>
                <td class="maintenance-request-fee-cost"><?php echo $l ? esc_html($l['cost']) : ''; ?></td>
                <th scope="row" class="maintenance-request-fee-cat">
                  <?php if ($r): ?>
                    <strong><?php echo esc_html($r['tit']); ?></strong>
                    <?php if (!empty($r['sub'])): ?><span><?php echo esc_html($r['sub']); ?></span><?php endif; ?>
                  <?php endif; ?>
                </th>
                <td class="maintenance-request-fee-cost"><?php echo $r ? esc_html($r['cost']) : ''; ?></td>
              </tr>
            <?php endfor; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>


</main>

<?php get_footer(); ?>
