<?php
/**
 * Template Name: 유지보수 비용 안내 (Maintenance Pricing)
 *
 * figma 117:584. 5섹션:
 *  1) Hero
 *  2) 정기 유지보수 상품 3카드 (기본/고급(추천)/최고급)
 *  3) 도입 혜택 (3카드)
 *  4) 건별 유지보수 표 (PDF p.13 데이터)
 *  5) 부가서비스 표 (figma 데이터)
 */
get_header();

// 견적 모달 nonce + AJAX URL (이 페이지 한정)
$mp_quote_nonce  = wp_create_nonce(UW_Quote_Handler::NONCE_KEY);
$mp_ajax_url     = admin_url('admin-ajax.php');
$mp_privacy_link = home_url('/privacy-policy/');

// 정기 유지보수 3개 플랜 — 거인소프트 견적서 기준 (월 10/20/30만원, 연 환산)
$mp_plans = array(
    array(
        'key'         => 'basic',
        'label'       => '기본형',
        'desc'        => "개인 혹은 소규모 사업자를 위한\n기본 유지보수 플랜",
        'price'       => '100,000원',
        'period'      => '/ 월 (연 1,200,000원)',
        'highlight'   => false,
        'items' => array(
            '하자보수 A/S'          => '무료',
            '텍스트 수정'           => '1회',
            '이미지 (2000×2000)'    => '1회',
            '문의폼 수정'           => '1회',
            '메인 비주얼 이미지'    => '-',
            '게시판/갤러리'         => '-',
            '배너/팝업'             => '-',
            '서브 페이지 추가'      => '-',
        ),
    ),
    array(
        'key'         => 'premium',
        'label'       => '고급형',
        'desc'        => "회사, 기업, 브랜드 고객을 위한\n고급형 플랜입니다.",
        'price'       => '200,000원',
        'period'      => '/ 월 (연 2,400,000원)',
        'highlight'   => true,
        'badge'       => '전체 이용자 중 85%가 사용하고 있어요!',
        'items' => array(
            '하자보수 A/S'          => '무료',
            '텍스트 수정'           => '제한없음',
            '이미지 (2000×2000)'    => '2회',
            '문의폼 수정'           => '2회',
            '메인 비주얼 이미지'    => '1회',
            '게시판/갤러리'         => '1회',
            '배너/팝업'             => '2회',
            '서브 페이지 추가'      => '1회/2개월',
        ),
    ),
    array(
        'key'         => 'top',
        'label'       => '최고급형',
        'desc'        => "대형 브랜드 혹은 잦은 컨텐츠 업로드가\n필요한 대상의 플랜",
        'price'       => '300,000원',
        'period'      => '/ 월 (연 3,600,000원)',
        'highlight'   => false,
        'items' => array(
            '하자보수 A/S'          => '무료',
            '텍스트 수정'           => '제한없음',
            '이미지 (2000×2000)'    => '제한없음',
            '문의폼 수정'           => '5회',
            '메인 비주얼 이미지'    => '3회',
            '게시판/갤러리'         => '3회',
            '배너/팝업'             => '3회',
            '서브 페이지 추가'      => '1회',
        ),
    ),
);

// 도입 혜택 3카드 — 아이콘은 assets/images/content/maintenance-pricing/
$mp_benefits = array(
    array(
        'icon'      => 'benefit-01',
        'tit_pre'   => '우선 검토를 위한 ',
        'tit_high'  => '매니저 배정',
        'tit_post'  => '',
        'desc'      => "전담 매니저가 배정되어 요청 시,\n최대 24시간 내 빠른 검토를 우선적으로 진행합니다.",
    ),
    array(
        'icon'      => 'benefit-02',
        'tit_pre'   => "기간 내에 사용하지 못한 항목,\n",
        'tit_high'  => '다음 월로 이월',
        'tit_post'  => ' 가능',
        'desc'      => "해당 월에 사용하지 못한 작업 항목은\n최대 3개월까지 이월하여 사용할 수 있습니다.",
    ),
    array(
        'icon'      => 'benefit-03',
        'tit_pre'   => "유지보수 전체 비용의\n",
        'tit_high'  => '10% 추가 공제',
        'tit_post'  => '',
        'desc'      => "선결제 시 전체 금액의 10%를 추가 공제해드립니다.\n(6개월 이상 계약 시 적용)",
    ),
);

// 아이콘 자동 인식 (png/gif/webp/svg)
$mp_benefit_icon = function($base) {
    $dir = '/assets/images/content/maintenance-pricing';
    foreach (array('gif', 'png', 'webp', 'svg') as $ext) {
        $p = get_theme_file_path($dir . '/' . $base . '.' . $ext);
        if (file_exists($p)) return get_theme_file_uri($dir . '/' . $base . '.' . $ext);
    }
    return '';
};

// 건별 유지보수 표 — 5 카테고리 × 25항목 (언웹스 정착 단가). 모든 가격은 `~` 시작가 표기.
$mp_onetime_groups = array(
    array(
        'label' => '페이지/메뉴 추가',
        'rows'  => array(
            array('item' => '서브 페이지 추가',          'sub' => '1페이지 분량 / PPT·PDF·Word A4 기준 / 자료 제공 시',      'price' => '100,000원~'),
            array('item' => '게시판 추가 (일반·갤러리)', 'sub' => '게시판 1종 신규 생성 + 권한 설정',                       'price' => '100,000원~'),
            array('item' => '인트로/스플래시 페이지',     'sub' => '접속 시 대문 페이지 / 1화면 100% 높이 1컷',              'price' => '150,000원~'),
            array('item' => '이벤트 페이지 디자인',       'sub' => '700×1,000 이내 / 이벤트 단독 페이지',                    'price' => '300,000원~'),
        ),
    ),
    array(
        'label' => '콘텐츠 추가/변경',
        'rows'  => array(
            array('item' => '메인 페이지 섹션 1파트 추가/변경', 'sub' => '메인 영역 1개 섹션 추가 또는 전체 변경 / 높이 1,000px 기준', 'price' => '150,000원~'),
            array('item' => '서브 페이지 섹션 1파트 추가/변경', 'sub' => '서브 페이지 내 섹션 추가 또는 전체 변경',                 'price' => '100,000원~'),
            array('item' => '페이지 내 요소 추가/변경',         'sub' => '버튼·기존 파트 분할·부분 디자인 교체 / 1회 작업 최소',    'price' => '50,000원~'),
            array('item' => '로고 변경 (일괄)',                 'sub' => '헤더·푸터·회사소개·공유 썸네일 일괄 교체 / 1회 작업 최소', 'price' => '50,000원~'),
            array('item' => '텍스트 수정',                      'sub' => 'A4용지 1/2분량 기준',                                    'price' => '30,000원~'),
            array('item' => '이미지 단순 교체',                 'sub' => '2,000×2,000 이내 / 건당',                                'price' => '30,000원~'),
        ),
    ),
    array(
        'label' => '비주얼·모션',
        'rows'  => array(
            array('item' => '메인 비주얼 효과',              'sub' => '간단한 모션 + 텍스트 효과',                    'price' => '150,000원~'),
            array('item' => '메인 비주얼 (고난도)',          'sub' => '복잡한 효과·인터랙션·시네마틱',                'price' => '별도협의'),
            array('item' => '서브 비주얼 효과',              'sub' => '간단한 모션 + 텍스트 효과',                    'price' => '100,000원~'),
            array('item' => '비주얼 동영상 전환',            'sub' => '메인/서브 이미지 → 영상 전환 / 10MB 이내',     'price' => '150,000원~'),
            array('item' => '메뉴/네비게이션 디자인 변경',   'sub' => '메인 + 서브 네비게이션 디자인 리뉴얼',         'price' => '150,000원~'),
            array('item' => '팝업 디자인 및 업로드',         'sub' => '380×450 이내',                                 'price' => '100,000원~'),
            array('item' => '배너 디자인 및 업로드',         'sub' => '200×50 이내',                                  'price' => '80,000원~'),
        ),
    ),
    array(
        'label' => '기능 수정·추가',
        'rows'  => array(
            array('item' => '게시판 항목 토글',              'sub' => '조회수·작성자·작성일 등 표시/숨김 변경',       'price' => '50,000원~'),
            array('item' => '게시판 스킨 변경 (개당)',       'sub' => '설정된 게시판 디자인 변경',                    'price' => '100,000원~'),
            array('item' => '메인 탭 게시판 추출',           'sub' => '메인에 탭 형식 게시판 노출',                   'price' => '100,000원~'),
            array('item' => '빠른 문의폼',                   'sub' => '메인 또는 플로팅(따라다니는) 문의 폼',         'price' => '200,000원~'),
            array('item' => '로그인/회원제 전환',            'sub' => '비회원제 → 회원제 시스템 도입',                'price' => '300,000원~'),
            array('item' => '폼 메일 제작/변경',             'sub' => '문의 폼 신규 또는 기존 변경 + 메일 발송 셋팅', 'price' => '200,000원~'),
            array('item' => '기타 게시판/프로그램 수정',     'sub' => '댓글·첨부·정렬 등 커스텀',                     'price' => '별도협의'),
        ),
    ),
    array(
        'label' => '인프라·기타',
        'rows'  => array(
            array('item' => '호스팅 서버 이전',              'sub' => '운영 중인 웹 사이트 서버 이전',                'price' => '100,000원~'),
            array('item' => '보안 인증서 (SSL) 설치',        'sub' => '주소창 자물쇠 표시 + HTTPS 보안 연결 적용',    'price' => '100,000원~'),
            array('item' => '도메인 등록·이전',              'sub' => '신규 등록 또는 외부 → 호스팅사 이전',          'price' => '50,000원~'),
        ),
    ),
);

// 표 하단 참고사항
$mp_onetime_notes = array(
    '프로그램 추가·변경, 고난이도 디자인 작업 시 비용이 추가될 수 있습니다.',
    '프로그램이 많거나 수익 기반의 사이트는 별도 협의에 따라 결정됩니다.',
    '모든 가격은 <strong>VAT 별도</strong>이며, 유지보수는 <strong>선납 원칙</strong>으로 입금 확인 후 작업에 들어갑니다.',
    '“자료 제공 시” 항목은 고객이 텍스트·이미지를 정리하여 제공한 경우 기준입니다. 자료 제작·기획이 필요한 경우 별도 견적입니다.',
    '“1회 작업 최소”는 전체 작업량을 산정하여 묶음 회수로 측정합니다.',
);

// 부가 서비스 표 — 4 카테고리 × 16항목
$mp_addon_groups = array(
    array(
        'label' => '알림·메시지',
        'rows'  => array(
            array('item' => '문자알림 셋팅',          'sub' => '문자 메시지 자동 발송 연동',                      'price' => '50,000원~'),
            array('item' => '메일 알림 셋팅',         'sub' => '폼 제출 시 자동 메일 발송',                       'price' => '50,000원~'),
            array('item' => '도메인 이메일 셋팅',     'sub' => '회사 도메인으로 이메일 발송/수신 환경 구성',      'price' => '150,000원~'),
            array('item' => '카카오 알림톡 셋팅',     'sub' => '카카오톡 자동 알림 발송 환경 구성',                'price' => '150,000원~'),
        ),
    ),
    array(
        'label' => '인프라·보안',
        'rows'  => array(
            array('item' => '네임서버 변경',          'sub' => '도메인 네임서버 전환 작업',                       'price' => '100,000원~'),
            array('item' => '도메인 등록·이전',       'sub' => '신규 등록 또는 외부 → 호스팅사 이전',             'price' => '50,000원~'),
            array('item' => '서버 이전',              'sub' => '운영 중인 웹 사이트 서버 옮기기',                 'price' => '별도협의'),
            array('item' => '사이트 백업 자동화',     'sub' => '외부 저장소에 매주 사이트 자동 백업',             'price' => '150,000원~'),
            array('item' => '보안 강화 셋팅',         'sub' => '해킹 차단 방화벽 + 악성코드 점검 + 관리자 보호',  'price' => '150,000원~'),
            array('item' => '사이트 속도 최적화 연동', 'sub' => '전 세계 분산 서버 캐싱으로 페이지 속도 향상',     'price' => '100,000원~'),
        ),
    ),
    array(
        'label' => '마케팅·분석',
        'rows'  => array(
            array('item' => '방문자 분석 도구 설치', 'sub' => '네이버·구글 통계 도구 계정 생성 및 설치',         'price' => '200,000원~'),
            array('item' => '검색엔진 등록',          'sub' => '네이버·구글에 사이트 등록 및 검색 노출 신청',     'price' => '100,000원~'),
            array('item' => '광고 추적 설치 (1종)',   'sub' => '메타·구글·카카오 광고 효과 측정 코드 삽입',         'price' => '80,000원~'),
        ),
    ),
    array(
        'label' => '연동·운영',
        'rows'  => array(
            array('item' => '지도 서비스 변경 (네이버/카카오)', 'sub' => '기존 지도 → 다른 지도로 전환',                'price' => '100,000원~'),
            array('item' => '실시간 상담 채널 추가',          'sub' => '카카오 채널·채널톡 등 위젯 연동',              'price' => '150,000원~'),
            array('item' => '이미지 일괄 최적화',             'sub' => '사이트 전체 이미지 압축으로 로딩 속도 향상',     'price' => '150,000원~'),
        ),
    ),
);
?>


<main class="cm-main" id="main-content" role="main">

  <h1 class="blind">유지보수 비용 안내</h1>

  <!-- ========== Hero ========== -->
  <section class="mp-intro-con sub-content-con cm-sub-hero">
    <div class="area">
      <div class="cm-tit-box" data-animate="fade-up">
        <span class="cm-tit-sub">유지보수</span>
        <h2 class="cm-tit">비용 안내</h2>
      </div>
    </div>
  </section>

  <!-- ========== 정기 유지보수 3카드 ========== -->
  <section class="mp-plans-con">
    <div class="area">
      <div class="mp-plans-grid" data-stagger>
        <?php foreach ($mp_plans as $plan): ?>
          <article class="mp-plan<?php echo !empty($plan['highlight']) ? ' is-highlight' : ''; ?>" data-animate="fade-up">
            <?php if (!empty($plan['badge'])): ?>
              <div class="mp-plan-badge">
                <span><?php echo esc_html($plan['badge']); ?></span>
              </div>
            <?php endif; ?>
            <div class="mp-plan-head">
              <h3 class="mp-plan-tit"><?php echo esc_html($plan['label']); ?></h3>
              <p class="mp-plan-desc"><?php echo nl2br(esc_html($plan['desc'])); ?></p>
            </div>
            <div class="mp-plan-price">
              <strong><?php echo esc_html($plan['price']); ?></strong>
              <span class="mp-plan-period"><?php echo esc_html($plan['period']); ?></span>
            </div>
            <ul class="mp-plan-items">
              <?php foreach ($plan['items'] as $k => $v): ?>
                <li>
                  <span class="mp-plan-item-key"><?php echo esc_html($k); ?></span>
                  <span class="mp-plan-item-val"><?php echo esc_html($v); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <button type="button"
                    class="cm-btn cm-btn-primary mp-plan-cta"
                    data-mq-open
                    data-plan="<?php echo esc_attr($plan['key']); ?>"
                    data-plan-label="<?php echo esc_attr($plan['label']); ?>">
              <i class="xi-pen-o" aria-hidden="true"></i>
              <span>견적서 신청</span>
            </button>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ========== 도입 혜택 ========== -->
  <section class="mp-benefits-con">
    <div class="area">
      <div class="cm-tit-box" data-animate="fade-up">
        <h2 class="cm-tit">도입 혜택</h2>
        <p class="cm-tit-txt">정기 유지보수 서비스 도입 시 받을 수 있는 다양한 혜택입니다.</p>
      </div>

      <div class="mp-benefits-grid" data-stagger>
        <?php foreach ($mp_benefits as $b):
          $icon_url = $mp_benefit_icon($b['icon']);
        ?>
          <article class="mp-benefit" data-animate="fade-up">
            <h3 class="mp-benefit-tit">
              <?php
              echo nl2br(esc_html($b['tit_pre']));
              echo '<span class="mp-benefit-tit-high">' . esc_html($b['tit_high']) . '</span>';
              if (!empty($b['tit_post'])) echo esc_html($b['tit_post']);
              ?>
            </h3>
            <p class="mp-benefit-desc"><?php echo nl2br(esc_html($b['desc'])); ?></p>
            <?php if ($icon_url): ?>
              <img class="mp-benefit-icon" src="<?php echo esc_url($icon_url); ?>" alt="" loading="lazy" width="80" height="80">
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ========== 건별 유지보수 표 ========== -->
  <section class="mp-onetime-con">
    <div class="area">
      <div class="mp-section-head" data-animate="fade-up">
        <h3 class="mp-section-tit">건별 유지보수</h3>
        <p class="mp-section-sub">필요할 때마다 건당 견적. 정기 계약 없이 항목별 요청 가능.</p>
      </div>

      <div class="mp-table-wrap" data-animate="fade-up">
        <?php foreach ($mp_onetime_groups as $g): ?>
          <div class="mp-cat">
            <h4 class="mp-cat-tit"><?php echo esc_html($g['label']); ?></h4>
            <table class="mp-table mp-table-onetime">
              <caption class="blind"><?php echo esc_html($g['label']); ?> 비용표</caption>
              <colgroup>
                <col style="width:50%"><col style="width:50%">
              </colgroup>
              <thead>
                <tr>
                  <th scope="col">구분</th>
                  <th scope="col">비용</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($g['rows'] as $r): ?>
                  <tr>
                    <th scope="row" class="mp-table-item">
                      <strong><?php echo esc_html($r['item']); ?></strong>
                      <?php if (!empty($r['sub'])): ?><span><?php echo esc_html($r['sub']); ?></span><?php endif; ?>
                    </th>
                    <td class="mp-table-price"><?php echo esc_html($r['price']); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endforeach; ?>

        <?php if (!empty($mp_onetime_notes)): ?>
          <ul class="mp-table-notes">
            <?php foreach ($mp_onetime_notes as $note): ?>
              <li><?php echo wp_kses($note, array('strong' => array(), 'b' => array(), 'em' => array())); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ========== 부가서비스 표 ========== -->
  <section class="mp-addon-con">
    <div class="area">
      <div class="mp-section-head" data-animate="fade-up">
        <h3 class="mp-section-tit">부가서비스 (옵션)</h3>
        <p class="mp-section-sub">
          실제 운영하면서 필요한 각종 설정 변경이나 외부 서비스 연동 작업.<br>
          직접 처리하기 어려운 기능들을 전문가가 대신 설정해드리는 선택형 관리 서비스.
        </p>
      </div>

      <div class="mp-table-wrap" data-animate="fade-up">
        <?php foreach ($mp_addon_groups as $g): ?>
          <div class="mp-cat">
            <h4 class="mp-cat-tit"><?php echo esc_html($g['label']); ?></h4>
            <table class="mp-table mp-table-addon">
              <caption class="blind"><?php echo esc_html($g['label']); ?> 비용표</caption>
              <colgroup>
                <col style="width:50%"><col style="width:50%">
              </colgroup>
              <thead>
                <tr>
                  <th scope="col">구분</th>
                  <th scope="col">비용</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($g['rows'] as $r): ?>
                  <tr>
                    <th scope="row" class="mp-table-item">
                      <strong><?php echo esc_html($r['item']); ?></strong>
                      <?php if (!empty($r['sub'])): ?><span><?php echo esc_html($r['sub']); ?></span><?php endif; ?>
                    </th>
                    <td class="mp-table-price"><?php echo esc_html($r['price']); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

</main>

<!-- ========== 견적서 신청 모달 ========== -->
<div class="mq-modal" id="mqModal" role="dialog" aria-modal="true" aria-labelledby="mqModalTitle" aria-hidden="true">
  <div class="mq-modal-overlay" data-mq-close></div>
  <div class="mq-modal-box" role="document">
    <div class="mq-modal-head">
      <h3 class="mq-modal-tit" id="mqModalTitle">
        <span class="mq-modal-plan-tag" id="mqPlanTag">고급형</span>
        견적서 신청
      </h3>
      <button type="button" class="mq-modal-close" aria-label="닫기" data-mq-close>
        <i class="xi-close" aria-hidden="true"></i>
      </button>
    </div>

    <form class="mq-form" id="mqForm" novalidate>
      <input type="hidden" name="action" value="<?php echo esc_attr(UW_Quote_Handler::ACTION); ?>">
      <input type="hidden" name="nonce"  value="<?php echo esc_attr($mp_quote_nonce); ?>">
      <input type="hidden" name="plan"   id="mqPlanInput" value="">
      <input type="text"   name="uw_h"   value="" tabindex="-1" autocomplete="off" class="mq-honeypot" aria-hidden="true">

      <div class="mq-modal-body">
        <div class="mq-field-row">
          <div class="mq-field">
            <label for="mqCompany">회사/기관명 <em>*</em></label>
            <input id="mqCompany" class="cm-input" type="text" name="company" autocomplete="organization" required>
          </div>
          <div class="mq-field">
            <label for="mqName">담당자명 <em>*</em></label>
            <input id="mqName" class="cm-input" type="text" name="name" autocomplete="name" required>
          </div>
        </div>

        <div class="mq-field-row">
          <div class="mq-field">
            <label for="mqPhone">연락처 <em>*</em></label>
            <input id="mqPhone" class="cm-input" type="tel" name="phone" placeholder="010-0000-0000" autocomplete="tel" required>
          </div>
          <div class="mq-field">
            <label for="mqEmail">이메일 <em>*</em></label>
            <input id="mqEmail" class="cm-input" type="email" name="email" placeholder="example@company.com" autocomplete="email" required>
          </div>
        </div>

        <div class="mq-field">
          <label for="mqSiteUrl">현재 운영 사이트 URL <span class="mq-optional">(선택)</span></label>
          <input id="mqSiteUrl" class="cm-input" type="url" name="site_url" placeholder="https://example.com" autocomplete="url">
        </div>

        <div class="mq-field">
          <label for="mqMessage">추가 요청사항 <span class="mq-optional">(선택)</span></label>
          <textarea id="mqMessage" class="cm-input mq-textarea" name="message" rows="4"
                    placeholder="필요한 추가 작업, 일정, 기타 요청사항을 자유롭게 작성해주세요."></textarea>
        </div>

        <label class="mq-agree">
          <input type="checkbox" name="agree" value="1" required>
          <span>개인정보 처리방침에 동의합니다. <em>*</em></span>
        </label>

        <div class="mq-alert" id="mqAlert" role="alert" hidden></div>
      </div>

      <div class="mq-modal-foot">
        <button type="button" class="cm-btn mq-btn-cancel" data-mq-close>취소</button>
        <button type="submit" class="cm-btn cm-btn-primary mq-btn-submit">
          <span class="mq-btn-label">견적 요청 보내기</span>
          <span class="mq-btn-spinner" hidden><i class="xi-spinner-2 xi-spin"></i></span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- 토스트 -->
<div class="mq-toast" id="mqToast" role="status" aria-live="polite" hidden>
  <i class="xi-check-circle" aria-hidden="true"></i>
  <span class="mq-toast-msg"></span>
</div>

<script>
(function(){
  'use strict';
  var ajaxUrl = <?php echo wp_json_encode($mp_ajax_url); ?>;

  function ready(fn){
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  ready(function(){
    var modal     = document.getElementById('mqModal');
    var form      = document.getElementById('mqForm');
    var planInput = document.getElementById('mqPlanInput');
    var planTag   = document.getElementById('mqPlanTag');
    var alertBox  = document.getElementById('mqAlert');
    var toast     = document.getElementById('mqToast');
    var btn       = form.querySelector('.mq-btn-submit');
    var btnLabel  = btn.querySelector('.mq-btn-label');
    var btnSpin   = btn.querySelector('.mq-btn-spinner');
    var savedY    = 0;

    // body 직속 이동 (transform 부모 escape)
    if (modal.parentNode !== document.body) document.body.appendChild(modal);
    if (toast.parentNode !== document.body) document.body.appendChild(toast);

    function openModal(plan, planLabel){
      planInput.value = plan;
      planTag.textContent = planLabel;
      alertBox.hidden = true; alertBox.textContent = '';

      savedY = window.pageYOffset || document.documentElement.scrollTop || 0;
      var sbw = window.innerWidth - document.documentElement.clientWidth;
      if (sbw > 0) document.body.style.paddingRight = sbw + 'px';
      document.documentElement.classList.add('mq-modal-lock');
      document.body.classList.add('mq-modal-lock');

      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
      setTimeout(function(){ form.querySelector('[name="company"]').focus(); }, 60);
    }
    function closeModal(){
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
      document.documentElement.classList.remove('mq-modal-lock');
      document.body.classList.remove('mq-modal-lock');
      document.body.style.paddingRight = '';
      window.scrollTo(0, savedY);
    }
    function showToast(msg){
      var msgEl = toast.querySelector('.mq-toast-msg');
      msgEl.textContent = msg;
      toast.hidden = false;
      // double rAF 후 visible 클래스 (transition 발동)
      requestAnimationFrame(function(){
        requestAnimationFrame(function(){ toast.classList.add('is-visible'); });
      });
      setTimeout(function(){
        toast.classList.remove('is-visible');
        setTimeout(function(){ toast.hidden = true; }, 320);
      }, 4500);
    }

    // 견적서 신청 버튼 트리거
    document.querySelectorAll('[data-mq-open]').forEach(function(b){
      b.addEventListener('click', function(){
        openModal(b.dataset.plan || 'premium', b.dataset.planLabel || '고급형');
      });
    });
    // 닫기
    modal.querySelectorAll('[data-mq-close]').forEach(function(el){
      el.addEventListener('click', closeModal);
    });
    document.addEventListener('keydown', function(e){
      if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });

    // 제출
    form.addEventListener('submit', function(e){
      e.preventDefault();
      alertBox.hidden = true;
      btn.disabled = true;
      btnLabel.hidden = true;
      btnSpin.hidden = false;

      var fd = new FormData(form);
      fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(json){
          if (json && json.success) {
            closeModal();
            form.reset();
            showToast(json.data && json.data.message ? json.data.message : '견적 요청이 접수되었습니다.');
          } else {
            alertBox.hidden = false;
            alertBox.textContent = (json && json.data) ? json.data : '오류가 발생했습니다.';
          }
        })
        .catch(function(){
          alertBox.hidden = false;
          alertBox.textContent = '네트워크 오류가 발생했습니다. 잠시 후 다시 시도해주세요.';
        })
        .finally(function(){
          btn.disabled = false;
          btnLabel.hidden = false;
          btnSpin.hidden = true;
        });
    });
  });
})();
</script>

<?php get_footer(); ?>
