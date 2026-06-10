<?php
/**
 * 네이버 검색광고 — 프리미엄 로그분석 + 전환추적 (파워링크)
 *
 * 프리미엄 로그분석 공통 스크립트(wcslog.js) + 전환 측정 헬퍼.
 * 운영(production) 환경 + ID 설정 시에만 출력 → 로컬/스테이징 트래픽이 분석에 안 섞임.
 * cm-gtm.php 와 동일 패턴.
 *
 * ── 설정 (wp-config.php 권장) ───────────────────────────────
 *   define('UW_NAVER_WA', 's_xxxxxxxxxxxx');   // 네이버 검색광고 > 도구 > 프리미엄 로그분석/전환추적 의 "공통 스크립트" wa 값
 *   // 전환추적 ID가 로그분석과 다르면:
 *   // define('UW_NAVER_CNV_WA', 's_yyyyyyyy');
 *
 *   // 전환 유형 코드 — 네이버 전환추적에서 만든 유형과 반드시 일치시킬 것
 *   // (기본: 문의/전화 모두 "2" 신청·예약)
 *   // define('UW_NAVER_CNV_TYPE_INQUIRY', '2');
 *   // define('UW_NAVER_CNV_TYPE_TEL', '2');
 *
 *   // 로컬에서도 테스트: add_filter('uw_naver_enabled', '__return_true');
 *
 * ── 발화 지점 ──────────────────────────────────────────────
 *   - 공통 스크립트   : 모든 페이지 <head>  (wp_head)
 *   - 전환 헬퍼/전화  : 모든 페이지 푸터    (wp_footer) — a[href^="tel:"] 클릭 자동 감지
 *   - 문의 폼 완료    : assets/js/CPT/inquiry/uw-inquiry.js 성공 핸들러 → window.uwNaverCnvInquiry()
 */

if (!defined('ABSPATH')) exit;

/** 프리미엄 로그분석/전환 공통 tag ID */
function uw_naver_wa()
{
    $id = defined('UW_NAVER_WA') ? UW_NAVER_WA : '';
    return apply_filters('uw_naver_wa', $id);
}

/** 전환추적 tag ID (미설정 시 로그분석 ID 재사용) */
function uw_naver_cnv_wa()
{
    $id = defined('UW_NAVER_CNV_WA') ? UW_NAVER_CNV_WA : uw_naver_wa();
    return apply_filters('uw_naver_cnv_wa', $id);
}

/** 출력 여부 — 기본: 운영 환경 + ID 존재 */
function uw_naver_enabled()
{
    $enabled = (wp_get_environment_type() === 'production') && uw_naver_wa() !== '';
    return (bool) apply_filters('uw_naver_enabled', $enabled);
}

/** 전환 유형 코드 (네이버 전환추적 설정값과 일치시킬 것) */
function uw_naver_cnv_types()
{
    return apply_filters('uw_naver_cnv_types', array(
        'inquiry' => defined('UW_NAVER_CNV_TYPE_INQUIRY') ? UW_NAVER_CNV_TYPE_INQUIRY : '2',
        'tel'     => defined('UW_NAVER_CNV_TYPE_TEL')     ? UW_NAVER_CNV_TYPE_TEL     : '2',
    ));
}

/** <head>: 프리미엄 로그분석 공통 스크립트 */
function uw_naver_head()
{
    if (!uw_naver_enabled()) return;
    $wa = uw_naver_wa();
    ?>
  <!-- Naver Premium Log Analytics -->
  <script src="//wcs.naver.net/wcslog.js"></script>
  <script>
  if(!window.wcs_add) var wcs_add={};
  wcs_add["wa"]="<?php echo esc_js($wa); ?>";
  if(window.wcs){wcs_do();}
  </script>
  <!-- End Naver Premium Log Analytics -->
    <?php
}
add_action('wp_head', 'uw_naver_head', 20);

/** 푸터: 전환 헬퍼 + 전화 클릭 전환 (위임 리스너) */
function uw_naver_footer()
{
    if (!uw_naver_enabled()) return;
    $cnv_wa = uw_naver_cnv_wa();
    $types  = uw_naver_cnv_types();
    ?>
  <!-- Naver Conversion -->
  <script>
  (function(){
    function fire(type){
      try{
        if(!window.wcs || !wcs.cnv) return;
        if(!window.wcs_add) window.wcs_add={};
        wcs_add["wa"]="<?php echo esc_js($cnv_wa); ?>";
        var _nasa={};
        _nasa["cnv"]=wcs.cnv(type,"0");
        wcs_do(_nasa);
      }catch(e){}
    }
    window.uwNaverCnvInquiry=function(){ fire("<?php echo esc_js($types['inquiry']); ?>"); };
    window.uwNaverCnvTel=function(){ fire("<?php echo esc_js($types['tel']); ?>"); };
    document.addEventListener('click', function(e){
      var t = e.target && e.target.closest ? e.target.closest('a[href^="tel:"]') : null;
      if(t){ window.uwNaverCnvTel(); }
    }, true);
  })();
  </script>
  <!-- End Naver Conversion -->
    <?php
}
add_action('wp_footer', 'uw_naver_footer', 20);
