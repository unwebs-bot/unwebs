/**
 * UW Maintenance Admin JavaScript
 *
 * @package starter-theme
 */

(function ($) {
  'use strict';

  $(document).ready(function () {
    initEntryForm();
    initKpiForm();
    initDeleteEntry();
  });

  /**
   * Entry form submission
   */
  function initEntryForm() {
    $('#uw-maintenance-form').on('submit', function (e) {
      e.preventDefault();

      var $form = $(this);
      var $btn = $form.find('button[type="submit"]');
      var entryId = $form.data('entry-id') || 0;

      $btn.prop('disabled', true).text('저장 중...');

      $.ajax({
        url: uwMaintenanceAdmin.ajaxUrl,
        type: 'POST',
        data: {
          action: 'uw_maintenance_save_entry',
          nonce: uwMaintenanceAdmin.nonce,
          entry_id: entryId,
          company: $('#company').val(),
          date: $('#date').val(),
          type: $('#type').val(),
          status: $('#status').val()
        },
        success: function (response) {
          if (response.success) {
            alert(response.data.message);
            if (response.data.redirect) {
              location.href = response.data.redirect;
            }
          } else {
            alert(response.data || '저장 중 오류가 발생했습니다.');
          }
        },
        error: function () {
          alert('서버 오류가 발생했습니다.');
        },
        complete: function () {
          $btn.prop('disabled', false).text(entryId > 0 ? '수정하기' : '추가하기');
        }
      });
    });
  }

  /**
   * KPI settings form submission
   */
  function initKpiForm() {
    $('#uw-maintenance-kpi-form').on('submit', function (e) {
      e.preventDefault();

      var $form = $(this);
      var $btn = $form.find('button[type="submit"]');

      $btn.prop('disabled', true).text('저장 중...');

      $.ajax({
        url: uwMaintenanceAdmin.ajaxUrl,
        type: 'POST',
        data: {
          action: 'uw_maintenance_save_kpi',
          nonce: uwMaintenanceAdmin.nonce,
          today_completed: $('#today_completed').val(),
          monthly_completed: $('#monthly_completed').val(),
          one_day_rate: $('#one_day_rate').val(),
          free_rate: $('#free_rate').val(),
          status_link: $('#status_link').val()
        },
        success: function (response) {
          if (response.success) {
            alert(response.data.message);
          } else {
            alert(response.data || '저장 중 오류가 발생했습니다.');
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
  }

  /**
   * Delete entry
   */
  function initDeleteEntry() {
    $(document).on('click', '.uw-maint-delete', function () {
      var $btn = $(this);
      var entryId = $btn.data('id');

      if (!confirm('이 항목을 삭제하시겠습니까?')) {
        return;
      }

      $btn.prop('disabled', true).text('삭제 중...');

      $.ajax({
        url: uwMaintenanceAdmin.ajaxUrl,
        type: 'POST',
        data: {
          action: 'uw_maintenance_delete_entry',
          nonce: uwMaintenanceAdmin.nonce,
          entry_id: entryId
        },
        success: function (response) {
          if (response.success) {
            $btn.closest('tr').fadeOut(300, function () {
              $(this).remove();
            });
          } else {
            alert(response.data || '삭제 중 오류가 발생했습니다.');
            $btn.prop('disabled', false).text('삭제');
          }
        },
        error: function () {
          alert('서버 오류가 발생했습니다.');
          $btn.prop('disabled', false).text('삭제');
        }
      });
    });
  }

})(jQuery);
