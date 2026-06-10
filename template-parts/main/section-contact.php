<?php
/**
 * Template Part: 메인 페이지 문의 섹션 (마지막 섹션)
 *
 * 3단 구조: 헤드라인+통계 / 좌측 정보 박스 / 우측 폼
 * 폼은 기존 uw_inquiry CPT 시스템 재사용 (자동 시드된 form_id 활용)
 */

if (!defined('ABSPATH')) exit;

$form_id      = (int) get_option('cm_main_inquiry_form_id');
$contact_email = 'contact@unwebs.co.kr';
$guide_url    = get_theme_file_uri('/assets/files/unwebs-project-request.docx');

$stats = array(
    array('label' => '누적 프로젝트',          'value' => '300',   'suffix' => '+'),
    array('label' => '누적 유지보수',          'value' => '2000',  'suffix' => '+',  'comma' => true),
    array('label' => '고객 만족도 및 재의뢰',   'value' => '95',    'suffix' => '%'),
    array('label' => '견적 안내까지',          'value' => '12',    'suffix' => '시간'),
);
?>

<section class="main-contact-con cm-section" aria-label="문의하기">
  <div class="area">

    <!-- ① 상단 헤드라인 + 통계 (cm-tit-box 표준) -->
    <div class="cm-tit-box" data-animate="fade-up">
      <span class="cm-tit-sub">웹사이트 제작, 복잡하게 고민하지 마세요</span>
      <h2 class="cm-tit">프로젝트 견적문의</h2>
    </div>

    <ul class="main-contact-stats" id="cmContactStats" data-animate="fade-up" data-delay="100">
      <?php foreach ($stats as $si => $s) :
        $value_str = $s['value'];
        if (!empty($s['comma']) && strlen($value_str) >= 4) {
            $value_str = number_format((int) $value_str);
        }
        $chars = preg_split('//u', $value_str, -1, PREG_SPLIT_NO_EMPTY);
      ?>
      <li class="main-contact-stats-item">
        <span class="main-contact-stats-label"><?php echo esc_html($s['label']); ?></span>
        <span class="main-contact-stats-value">
          <span class="main-contact-stats-num">
            <?php
            foreach ($chars as $di => $char) :
                if (!ctype_digit($char)) :
                    ?>
                    <span class="main-contact-stats-digit-static"><?php echo esc_html($char); ?></span>
                    <?php
                    continue;
                endif;
                $d    = intval($char);
                $dir  = ($di % 2 === 0) ? 'up' : 'down';
                $seed = ($si + 1) * 1000 + $di;
                $list = uw_stats_digit_list($d, $dir, $seed);
            ?>
            <span class="main-contact-stats-digit-wrap">
              <span class="main-contact-stats-digit-box <?php echo $dir; ?>">
                <?php foreach ($list as $item) : ?>
                <span class="main-contact-stats-digit-item"><?php echo $item; ?></span>
                <?php endforeach; ?>
              </span>
            </span>
            <?php endforeach; ?>
          </span><span class="main-contact-stats-suffix"><?php echo esc_html($s['suffix']); ?></span>
        </span>
      </li>
      <?php endforeach; ?>
    </ul>

    <!-- ② / ③ 2컬럼 -->
    <div class="main-contact-wrap" data-animate="fade-up" data-delay="200">

      <!-- 좌측 정보 박스 -->
      <aside class="main-contact-side">
        <div class="main-contact-side-block">
          <span class="main-contact-side-label">메일 상담</span>
          <a class="main-contact-side-value" href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a>
        </div>

        <div class="main-contact-side-divider" aria-hidden="true"></div>

        <div class="main-contact-side-block">
          <span class="main-contact-side-label">제작 문의는 이렇게 진행돼요</span>
          <ol class="main-contact-process">
            <li><span class="main-contact-process-num">1</span><span class="main-contact-process-text">문의를 남겨주시면, 담당자가 확인합니다.</span></li>
            <li><span class="main-contact-process-num">2</span><span class="main-contact-process-text">맞춤 제안·견적서를 1일 내에 송부합니다.</span></li>
            <li><span class="main-contact-process-num">3</span><span class="main-contact-process-text">프로젝트 진행 여부를 담당자에게 회신합니다.</span></li>
          </ol>
        </div>

        <div class="main-contact-side-divider" aria-hidden="true"></div>

        <div class="main-contact-side-block">
          <p class="main-contact-side-note">아직 홈페이지 자료 준비가 안되어있으시다면<br>아래 가이드를 다운로드 받아보세요.</p>
          <a class="main-contact-guide-btn" href="<?php echo esc_url($guide_url); ?>" download="언웹스 제작의뢰서 양식.docx">
            <i class="xi-download" aria-hidden="true"></i>
            홈페이지 자료 준비 가이드
          </a>
        </div>
      </aside>

      <!-- 우측 폼 -->
      <div class="main-contact-form-area">
        <?php
        if ($form_id) {
            set_query_var('form_id', $form_id);
            include get_template_directory() . '/inc/uw-inquiry/content-inquiry-form.php';
        } else {
            echo '<p class="main-contact-empty">문의 폼이 준비 중입니다.</p>';
        }
        ?>

        <!-- 약관 동의 (시각용 — 항상 체크 + 모달 토글) -->
        <div class="main-contact-agree">
          <span class="main-contact-agree-check" aria-hidden="true">
            <i class="xi-check"></i>
          </span>
          <button type="button" class="main-contact-agree-btn" id="cmContactAgreeBtn" aria-haspopup="dialog">
            <span class="main-contact-agree-required">[필수]</span>
            개인정보 수집/이용동의
            <i class="xi-angle-right-min" aria-hidden="true"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- 약관 모달 -->
  <div class="main-contact-modal" id="cmContactModal" role="dialog" aria-modal="true" aria-labelledby="cmContactModalTit" hidden>
    <div class="main-contact-modal-dim" data-modal-close></div>
    <div class="main-contact-modal-panel">
      <header class="main-contact-modal-head">
        <h3 class="main-contact-modal-tit" id="cmContactModalTit">개인정보 수집/이용동의</h3>
        <button type="button" class="main-contact-modal-close" data-modal-close aria-label="닫기">
          <i class="xi-close"></i>
        </button>
      </header>
      <div class="main-contact-modal-body">
        <p>언웹스는 기업/단체 및 개인의 정보 수집 및 이용 등 처리에 있어 아래의 사항을 관계법령에 따라 아래와 같이 고지하고 안내해 드립니다.</p>
        <dl class="main-contact-modal-list">
          <dt>1. 정보수집의 이용 목적</dt>
          <dd>홈페이지 제작 상담 및 제작의 진행</dd>
          <dt>2. 수집/이용 항목</dt>
          <dd>회사/단체명, 이름(담당자명), 연락처, 이메일, 상담내용</dd>
          <dt>3. 보유 및 이용기간</dt>
          <dd>홈페이지 제작 상담 종료 후 6개월, 제작 완료 후 웹호스팅 유지보수 계약기간 종료 후 1년, 정보제공자의 삭제 요청 시 즉시</dd>
          <dt>4. 개인정보처리담당</dt>
          <dd>이메일 <a href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a></dd>
        </dl>
      </div>
    </div>
  </div>
</section>
