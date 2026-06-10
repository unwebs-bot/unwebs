/**
 * UW Inquiry Admin JavaScript
 * 
 * 입력폼 관리자 스크립트
 * 
 * @package starter-theme
 */

(function ($) {
  'use strict';

  $(document).ready(function () {
    initFieldBuilder();
    initFormActions();
    initCsvExport();
    initMiscActions();
  });

  // ==========================================================================
  // 필드 빌더
  // ==========================================================================
  function initFieldBuilder() {
    // SortableJS 초기화
    var fieldList = document.getElementById('uw-field-list');
    if (fieldList && typeof Sortable !== 'undefined') {
      new Sortable(fieldList, {
        handle: '.uw-field-drag',
        animation: 150,
        ghostClass: 'sortable-ghost',
        onEnd: function () {
          updateFieldOrder();
        }
      });
    }

    // 필드 수정 토글
    $(document).on('click', '.uw-field-edit', function (e) {
      e.stopPropagation();
      var $item = $(this).closest('.uw-field-item');
      var $body = $item.find('.uw-field-body');

      if ($body.is(':visible')) {
        $body.slideUp(200);
      } else {
        // 다른 열린 필드 닫기
        $('.uw-field-body:visible').slideUp(200);
        $body.slideDown(200);
      }
    });

    // 필드 삭제
    $(document).on('click', '.uw-field-delete', function (e) {
      e.stopPropagation();
      if (confirm('이 필드를 삭제하시겠습니까?')) {
        $(this).closest('.uw-field-item').fadeOut(200, function () {
          $(this).remove();
          updateFieldOrder();
        });
      }
    });

    // 라벨 변경 시 헤더 업데이트
    $(document).on('input', '.uw-field-body input[name*="[label]"]', function () {
      var $item = $(this).closest('.uw-field-item');
      $item.find('.uw-field-label').text($(this).val());
    });

    // 타입 변경 시 헤더 업데이트 + 옵션 필드 표시/숨김
    $(document).on('change', '.uw-field-body select[name*="[type]"]', function () {
      var $item = $(this).closest('.uw-field-item');
      var typeLabel = uwInquiryAdmin.fieldTypes[$(this).val()] || $(this).val();
      $item.find('.uw-field-type').text(typeLabel);

      // 옵션 필드 표시/숨김
      var type = $(this).val();
      var $optionsRow = $item.find('.uw-field-options-row');
      if (type === 'select' || type === 'checkbox' || type === 'radio') {
        $optionsRow.slideDown(200);
      } else {
        $optionsRow.slideUp(200);
      }
    });

    // 필드 추가
    $('#uw-add-field').on('click', function () {
      var fieldId = 'field_' + Date.now();
      var template = `
        <div class="uw-field-item" data-field-id="${fieldId}">
          <div class="uw-field-header">
            <span class="uw-field-drag">☰</span>
            <span class="uw-field-label">새 필드</span>
            <span class="uw-field-type">단답형 (Text)</span>
            <label class="uw-field-toggle">
              <input type="checkbox" name="fields[${fieldId}][enabled]" value="1" checked>
              <span class="slider"></span>
            </label>
            <button type="button" class="uw-field-edit">수정</button>
            <button type="button" class="uw-field-delete">삭제</button>
          </div>
          <div class="uw-field-body">
            <input type="hidden" name="fields[${fieldId}][id]" value="${fieldId}">
            <input type="hidden" name="fields[${fieldId}][order]" value="999" class="field-order">
            
            <div class="uw-field-row">
              <label>라벨</label>
              <input type="text" name="fields[${fieldId}][label]" value="새 필드" required>
            </div>
            <div class="uw-field-row">
              <label>필드 타입</label>
              <select name="fields[${fieldId}][type]">
                ${Object.entries(uwInquiryAdmin.fieldTypes).map(([key, val]) =>
        `<option value="${key}">${val}</option>`
      ).join('')}
              </select>
            </div>
            <div class="uw-field-row uw-field-options-row" style="display: none;">
              <label>옵션 (한 줄에 하나씩)</label>
              <textarea name="fields[${fieldId}][options]" rows="4" placeholder="옵션1&#10;옵션2&#10;옵션3"></textarea>
              <p class="description">드롭다운, 체크박스, 라디오 타입에서 선택할 수 있는 옵션들을 한 줄씩 입력하세요.</p>
            </div>
            <div class="uw-field-row">
              <label>플레이스홀더</label>
              <input type="text" name="fields[${fieldId}][placeholder]" value="">
            </div>
            <div class="uw-field-row">
              <label>도움말 텍스트</label>
              <input type="text" name="fields[${fieldId}][help_text]" value="">
            </div>
            <div class="uw-field-row">
              <label>
                <input type="checkbox" name="fields[${fieldId}][required]" value="1">
                필수 입력
              </label>
            </div>
          </div>
        </div>
      `;

      $('#uw-field-list').append(template);
      updateFieldOrder();

      // 새 필드 열기
      $('#uw-field-list .uw-field-item:last-child .uw-field-body').slideDown(200);
    });

    // 초기 로드 시 옵션 필드 표시/숨김 처리
    $('.uw-field-item').each(function () {
      var $item = $(this);
      var type = $item.find('select[name*="[type]"]').val();
      var $optionsRow = $item.find('.uw-field-options-row');
      if (type === 'select' || type === 'checkbox' || type === 'radio') {
        $optionsRow.show();
      }
    });
  }

  // 필드 순서 업데이트
  function updateFieldOrder() {
    $('#uw-field-list .uw-field-item').each(function (index) {
      $(this).find('.field-order').val(index + 1);
    });
  }

  // ==========================================================================
  // 폼 액션
  // ==========================================================================
  function initFormActions() {
    // 폼 생성/수정 제출
    $('#uw-inquiry-create-form, #uw-inquiry-edit-form').on('submit', function (e) {
      e.preventDefault();

      var $form = $(this);
      var $btn = $form.find('button[type="submit"]');
      var formId = $form.data('form-id') || 0;

      // 필드 데이터 수집
      var fields = [];
      $('#uw-field-list .uw-field-item').each(function () {
        var $item = $(this);
        var fieldId = $item.data('field-id');

        fields.push({
          id: fieldId,
          type: $item.find('select[name*="[type]"]').val() || 'text',
          label: $item.find('input[name*="[label]"]').val(),
          placeholder: $item.find('input[name*="[placeholder]"]').val() || '',
          help_text: $item.find('input[name*="[help_text]"]').val() || '',
          options: $item.find('textarea[name*="[options]"]').val() || '',
          required: $item.find('input[name*="[required]"]').is(':checked') ? '1' : '',
          enabled: $item.find('input[name*="[enabled]"]').is(':checked') ? '1' : '',
          order: parseInt($item.find('.field-order').val()) || 0
        });
      });

      $btn.prop('disabled', true).text('저장 중...');

      $.ajax({
        url: uwInquiryAdmin.ajaxUrl,
        type: 'POST',
        data: {
          action: 'uw_inquiry_save_form',
          nonce: uwInquiryAdmin.nonce,
          form_id: formId,
          form_title: $('#form_title').val(),
          fields: fields,
          // 추가 설정 - 명시적으로 '1' 또는 '' 전송
          privacy_text: $('#privacy_text').val() || '',
          privacy_required: $('#privacy_required').is(':checked') ? '1' : '',
          captcha_enabled: $('#captcha_enabled').is(':checked') ? '1' : '',
          notify_emails: $('#notify_emails').val() || '',
          mail_subject: $('#mail_subject').val() || '',
          success_type: $('#success_type').val() || 'popup',
          success_message: $('#success_message').val() || '',
          success_page_id: $('#success_page_id').val() || 0
        },
        success: function (response) {
          if (response.success) {
            alert(response.data.message);
            if (response.data.redirect) {
              location.href = response.data.redirect;
            }
          } else {
            alert(response.data);
          }
        },
        error: function () {
          alert('서버 오류가 발생했습니다.');
        },
        complete: function () {
          $btn.prop('disabled', false).text('설정 저장');
        }
      });
    });

    // 폼 삭제
    $(document).on('click', '.uw-delete-form', function () {
      var formId = $(this).data('id');

      if (!confirm('이 입력폼을 삭제하시겠습니까?\n관련된 모든 문의 내역도 함께 삭제됩니다.')) {
        return;
      }

      $.ajax({
        url: uwInquiryAdmin.ajaxUrl,
        type: 'POST',
        data: {
          action: 'uw_inquiry_delete_form',
          nonce: uwInquiryAdmin.nonce,
          form_id: formId
        },
        success: function (response) {
          if (response.success) {
            location.reload();
          } else {
            alert(response.data);
          }
        }
      });
    });

    // 문의 삭제
    $(document).on('click', '.uw-delete-entry', function () {
      var entryId = $(this).data('id');
      var redirect = $(this).data('redirect');

      if (!confirm('이 문의를 삭제하시겠습니까?')) {
        return;
      }

      $.ajax({
        url: uwInquiryAdmin.ajaxUrl,
        type: 'POST',
        data: {
          action: 'uw_inquiry_delete_entry',
          nonce: uwInquiryAdmin.nonce,
          entry_id: entryId
        },
        success: function (response) {
          if (response.success) {
            if (redirect) {
              location.href = redirect;
            } else {
              location.reload();
            }
          } else {
            alert(response.data);
          }
        }
      });
    });

    // 완료 타입 변경 시 필드 표시/숨김
    $('#success_type').on('change', function () {
      if ($(this).val() === 'popup') {
        $('.uw-success-popup').show();
        $('.uw-success-redirect').hide();
      } else {
        $('.uw-success-popup').hide();
        $('.uw-success-redirect').show();
      }
    });
  }

  // ==========================================================================
  // CSV 내보내기
  // ==========================================================================
  function initCsvExport() {
    $(document).on('click', '.uw-export-csv', function () {
      var $btn = $(this);
      var formId = $btn.data('form-id');

      $btn.prop('disabled', true).text('다운로드 중...');

      $.ajax({
        url: uwInquiryAdmin.ajaxUrl,
        type: 'POST',
        data: {
          action: 'uw_inquiry_export_csv',
          nonce: uwInquiryAdmin.nonce,
          form_id: formId
        },
        success: function (response) {
          if (response.success) {
            // CSV 생성
            var csvContent = "\uFEFF"; // BOM for UTF-8

            response.data.data.forEach(function (row) {
              var rowString = row.map(function (field) {
                field = String(field).replace(/"/g, '""');
                return '"' + field + '"';
              }).join(",");
              csvContent += rowString + "\n";
            });

            // 다운로드
            var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            var url = URL.createObjectURL(blob);
            var link = document.createElement("a");
            link.setAttribute("href", url);
            link.setAttribute("download", response.data.filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          } else {
            alert(response.data);
          }
        },
        error: function () {
          alert('서버 오류가 발생했습니다.');
        },
        complete: function () {
          $btn.prop('disabled', false).text('CSV 다운로드');
        }
      });
    });
  }

  // ==========================================================================
  // 기타 액션
  // ==========================================================================
  function initMiscActions() {
    // 숏코드 복사
    $(document).on('click', '.uw-copy-shortcode', function () {
      var shortcode = $(this).data('shortcode');

      if (navigator.clipboard) {
        navigator.clipboard.writeText(shortcode).then(function () {
          alert('숏코드가 복사되었습니다.');
        });
      } else {
        // Fallback
        var $temp = $('<input>');
        $('body').append($temp);
        $temp.val(shortcode).select();
        document.execCommand('copy');
        $temp.remove();
        alert('숏코드가 복사되었습니다.');
      }
    });

    // 목록 행 클릭 시 상세로 이동
    $(document).on('click', '.uw-entry-row', function (e) {
      if ($(e.target).closest('button, a, .uw-status-badge').length) return;

      var $link = $(this).find('a.button');
      if ($link.length) {
        location.href = $link.attr('href');
      }
    });

    // 실시간 신규 entry 폴링 + 안읽음 배지 토글
    initEntryPolling();
    initReadToggle();
  }

  /**
   * 30초 간격으로 새 entry 카운트 체크
   * 신규 발견 → 상단 배너 표시 + "지금 새로고침" 버튼
   */
  function initEntryPolling() {
    var $table = $('.uw-inquiry-table[data-form-id]');
    if (!$table.length) return;

    var formId = $table.data('form-id');
    var lastId = parseInt($table.data('latest-id'), 10) || 0;
    var $banner = null;

    function showBanner(count) {
      if ($banner) {
        $banner.find('.uw-newalert-count').text(count);
        return;
      }
      $banner = $(
        '<div class="uw-newalert" role="status">' +
        '  <span class="uw-newalert-dot"></span>' +
        '  새 문의 <strong class="uw-newalert-count">' + count + '</strong>건이 도착했습니다.' +
        '  <button type="button" class="button button-primary uw-newalert-reload">지금 새로고침</button>' +
        '</div>'
      );
      $('.uw-inquiry-admin h1').first().after($banner);
      $banner.find('.uw-newalert-reload').on('click', function () { location.reload(); });
    }

    setInterval(function () {
      $.post(uwInquiryAdmin.ajaxUrl, {
        action: 'uw_inquiry_check_new',
        nonce: uwInquiryAdmin.nonce,
        form_id: formId,
        last_id: lastId
      }, function (res) {
        if (res && res.success && res.data && res.data.count > 0) {
          showBanner(res.data.count);
        }
      });
    }, 30000);
  }

  /**
   * 좌측 admin 메뉴 "입력폼" 버블 숫자 갱신 (0이면 제거, 없으면 생성)
   */
  function updateMenuBubble(count) {
    count = parseInt(count, 10) || 0;
    var $menuLi  = $('#adminmenu li.toplevel_page_uw-inquiry');
    var $bubbles = $menuLi.find('.awaiting-mod');

    if (count > 0) {
      if ($bubbles.length) {
        $bubbles.find('.uw-unread-count').text(count);
      } else {
        var html = ' <span class="awaiting-mod"><span class="uw-unread-count">' + count + '</span></span>';
        $menuLi.find('> a .wp-menu-name').append(html);
        $menuLi.find('.wp-submenu a[href="admin.php?page=uw-inquiry"]').first().append(html);
      }
    } else {
      $bubbles.remove();
    }
  }

  /**
   * 상태 배지 클릭 시 읽음/안읽음 수동 토글
   */
  function initReadToggle() {
    $(document).on('click', '.uw-status-badge', function (e) {
      e.preventDefault();
      e.stopPropagation();
      var $badge = $(this);
      var $row = $badge.closest('.uw-entry-row');
      var entryId = $row.data('entry-id');
      var nextRead = $row.hasClass('is-unread') ? 1 : 0;

      $.post(uwInquiryAdmin.ajaxUrl, {
        action: 'uw_inquiry_toggle_read',
        nonce: uwInquiryAdmin.nonce,
        entry_id: entryId,
        read: nextRead
      }, function (res) {
        if (res && res.success) {
          if (nextRead) {
            $row.removeClass('is-unread');
            $badge.removeClass('uw-status-unread').addClass('uw-status-read').text('읽음');
          } else {
            $row.addClass('is-unread');
            $badge.removeClass('uw-status-read').addClass('uw-status-unread').text('● 안읽음');
          }
          if (res.data && typeof res.data.unread_count !== 'undefined') {
            updateMenuBubble(res.data.unread_count);
          }
        }
      });
    });
  }

})(jQuery);
