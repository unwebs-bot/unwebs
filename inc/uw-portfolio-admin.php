<?php
/**
 * 포트폴리오 순서관리 — wp-admin 드래그&드롭 정렬
 *
 *  - 메뉴: 관리자 좌측 '포트폴리오 순서'
 *  - 드래그로 순서 변경 → '순서 저장' → option(uw_portfolio_order, slug 배열) 저장
 *  - cm_portfolio_dataset()가 이 옵션으로 재정렬 → 메인(상위8)·포폴페이지·네이버 캐러셀 반영
 *  - 콘텐츠(추가/편집)는 코드+이미지 파이프라인 유지. 이 페이지는 "순서"만 관리.
 */

if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'uw_portfolio_admin_menu');
function uw_portfolio_admin_menu()
{
    add_menu_page(
        '포트폴리오 순서관리',
        '포트폴리오 순서',
        'manage_options',
        'uw-portfolio-order',
        'uw_portfolio_admin_render',
        'dashicons-portfolio',
        26
    );
}

add_action('admin_enqueue_scripts', 'uw_portfolio_admin_assets');
function uw_portfolio_admin_assets($hook)
{
    // 이 페이지에서만 (hook suffix가 환경따라 달라질 수 있어 느슨하게 매칭)
    if (strpos((string) $hook, 'uw-portfolio-order') === false) return;

    wp_enqueue_script('jquery-ui-sortable'); // jquery 의존 자동 포함
    wp_localize_script('jquery-ui-sortable', 'UWPort', array(
        'ajax'  => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('uw_portfolio_order'),
        'main'  => 8,
    ));
    // sortable 로드 직후 실행 보장 (인라인 <script> 타이밍 문제 회피)
    wp_add_inline_script('jquery-ui-sortable', uw_portfolio_admin_js());
}

function uw_portfolio_admin_js()
{
    return <<<'JS'
jQuery(function ($) {
  var $list = $('#uw-port-sortable');
  if (!$list.length) return;
  var $msg = $('#uw-port-msg');
  var MAIN = (window.UWPort && UWPort.main) ? UWPort.main : 8;

  function restripe() {
    $list.children('.uw-port-item').each(function (i) {
      $(this).find('.uw-port-num').text(i + 1);
      var $b = $(this).find('.uw-port-badge');
      if (i < MAIN) { $b.text('메인 노출').css({background:'#e7f0ff',color:'#1b64da'}); $(this).css('border-color','#bcd4ff'); }
      else { $b.text('').css('background','transparent'); $(this).css('border-color','#dcdcde'); }
    });
  }
  restripe();

  if ($.fn.sortable) {
    $list.sortable({ axis: 'y', cursor: 'grabbing', opacity: 0.85, placeholder: 'uw-port-ph', tolerance: 'pointer', update: restripe });
    $list.disableSelection();
  } else {
    $msg.text('정렬 스크립트 로드 실패 — 새로고침 해주세요.').css('color', '#d63638');
  }

  function order() {
    return $list.children('.uw-port-item').map(function () { return $(this).data('slug'); }).get();
  }

  function save(slugs, label) {
    $msg.text('저장 중…').css('color', '#888');
    var fd = new FormData();
    fd.append('action', 'uw_save_portfolio_order');
    fd.append('nonce', UWPort.nonce);
    $.each(slugs, function (i, s) { fd.append('order[]', s); });
    fetch(UWPort.ajax, { method: 'POST', credentials: 'same-origin', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (j && j.success) { $msg.text((label || '저장됨') + ' ✓ (' + j.data.count + '개)').css('color', '#1a7f37'); }
        else { $msg.text('저장 실패: ' + ((j && j.data) || '')).css('color', '#d63638'); }
      })
      .catch(function (e) { $msg.text('오류: ' + e).css('color', '#d63638'); });
  }

  $('#uw-port-save').on('click', function () { save(order(), '저장됨'); });
  $('#uw-port-reset').on('click', function () {
    if (!confirm('코드 기본 순서로 되돌립니다. 계속할까요?')) return;
    save([], '초기화됨');
    setTimeout(function () { location.reload(); }, 700);
  });
});
JS;
}

function uw_portfolio_admin_render()
{
    if (!current_user_can('manage_options')) return;
    $items = function_exists('cm_get_main_portfolio_items') ? cm_get_main_portfolio_items(500) : array();
    ?>
    <div class="wrap">
      <h1>포트폴리오 순서관리</h1>
      <p style="max-width:760px;color:#555;">
        카드를 <b>드래그</b>해서 순서를 바꾼 뒤 <b>순서 저장</b>을 누르세요.
        위에서부터 <b>상위 8개</b>가 <b>메인 페이지 + 네이버 검색 캐러셀</b>에 노출되고, 전체는 포트폴리오 페이지에 그대로 나옵니다.
      </p>
      <p>
        <button class="button button-primary button-large" id="uw-port-save">순서 저장</button>
        <button class="button" id="uw-port-reset" title="코드 기본 순서로 되돌림">기본 순서로 초기화</button>
        <span id="uw-port-msg" style="margin-left:10px;font-weight:600;"></span>
      </p>

      <ul id="uw-port-sortable" style="margin:16px 0;padding:0;list-style:none;max-width:760px;">
        <?php foreach ($items as $it): ?>
        <li class="uw-port-item" data-slug="<?php echo esc_attr($it['slug']); ?>"
            style="display:flex;align-items:center;gap:14px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:10px 14px;margin-bottom:8px;cursor:grab;user-select:none;">
          <span class="uw-port-num" style="flex:0 0 30px;text-align:center;font-weight:700;color:#888;"></span>
          <span class="dashicons dashicons-menu" style="color:#bbb;"></span>
          <img src="<?php echo esc_url($it['image']); ?>" alt="" draggable="false"
               style="flex:0 0 54px;width:54px;height:54px;object-fit:cover;object-position:top;border:1px solid #eee;border-radius:6px;background:#f3f3f3;pointer-events:none;">
          <span style="flex:1;min-width:0;">
            <strong style="display:block;font-size:14px;"><?php echo esc_html($it['title']); ?></strong>
            <span style="color:#888;font-size:12px;"><?php echo esc_html(trim($it['industry'] . ' · ' . $it['type'], ' ·')); ?></span>
          </span>
          <span class="uw-port-badge" style="flex:0 0 auto;font-size:11px;font-weight:700;padding:3px 8px;border-radius:999px;"></span>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <style>.uw-port-ph{height:74px;border:2px dashed #bcd4ff;border-radius:8px;margin-bottom:8px;background:#f5f9ff;list-style:none;}</style>
    <?php
}

add_action('wp_ajax_uw_save_portfolio_order', 'uw_save_portfolio_order_cb');
function uw_save_portfolio_order_cb()
{
    if (!current_user_can('manage_options')) wp_send_json_error('권한 없음', 403);
    check_ajax_referer('uw_portfolio_order', 'nonce');

    $order = isset($_POST['order']) ? (array) wp_unslash($_POST['order']) : array();
    $order = array_values(array_filter(array_map('sanitize_title', $order)));

    if (empty($order)) {
        delete_option('uw_portfolio_order');
        wp_send_json_success(array('count' => 0));
    }
    update_option('uw_portfolio_order', $order);
    wp_send_json_success(array('count' => count($order)));
}
