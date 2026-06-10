<?php
/**
 * Template Part: 포트폴리오 팝업 (공용)
 *
 * 메인 섹션·포트폴리오 목록 양쪽에서 1회 출력 후 카드(.js-port-card)들이 공유.
 * 내용은 클릭한 카드의 data-port-* 속성을 main.js가 읽어 채움.
 * 우측 사이드탭/VIEW SIZE/MORE VIEW 없는 단일 통합 레이아웃.
 */

if (!defined('ABSPATH')) exit;

$contact_url = home_url('/contact');
?>
<div class="port-modal" id="cmPortModal" role="dialog" aria-modal="true" aria-labelledby="cmPortModalTit" hidden>
  <div class="port-modal-dim" data-modal-close></div>
  <div class="port-modal-panel">
    <button type="button" class="port-modal-close" data-modal-close aria-label="닫기">
      <i class="xi-close" aria-hidden="true"></i>
    </button>

    <div class="port-modal-body">
      <header class="port-modal-head">
        <h3 class="port-modal-tit" id="cmPortModalTit"></h3>
        <p class="port-modal-url" hidden>
          <span class="port-modal-url-label">URL</span>
          <a class="port-modal-url-link" href="#" target="_blank" rel="noopener noreferrer"></a>
        </p>
      </header>

      <dl class="port-modal-meta">
        <div class="port-modal-meta-item port-modal-meta-industry" hidden>
          <dt>업종</dt><dd></dd>
        </div>
        <div class="port-modal-meta-item port-modal-meta-work" hidden>
          <dt>작업 유형</dt><dd></dd>
        </div>
        <div class="port-modal-meta-item port-modal-meta-type" hidden>
          <dt>화면</dt><dd></dd>
        </div>
      </dl>

      <div class="port-modal-actions">
        <a class="port-modal-visit cm-btn" href="#" target="_blank" rel="noopener noreferrer" hidden>
          사이트 바로가기 <i class="xi-external-link" aria-hidden="true"></i>
        </a>
        <a class="port-modal-inquiry cm-btn" href="<?php echo esc_url($contact_url); ?>">견적 문의하기</a>
      </div>

      <!-- PC 통이미지 + 모바일 통이미지(우측 겹침, 흰 기기 프레임). 함께 스크롤 -->
      <div class="port-modal-capture" hidden>
        <img class="port-modal-capture-img" src="" alt="" loading="lazy">
      </div>
    </div>
  </div>
</div>
