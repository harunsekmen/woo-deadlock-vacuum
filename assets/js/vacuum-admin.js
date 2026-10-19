/**
 * WooCommerce Deadlock-Free Database Vacuum Admin JS
 */

(function ($) {
  'use strict';

  if (typeof window.wdvData === 'undefined') {
    return;
  }

  var data = window.wdvData;

  $(document).ready(function () {
    $('#wdv-run-btn').on('click', function (e) {
      e.preventDefault();

      var $btn = $(this);
      var $feedback = $('.wdv-status-feedback');

      $btn.prop('disabled', true).addClass('updating-message');
      $feedback.css('color', '#2563eb').text(data.i18n.purging);

      $.ajax({
        url: data.ajaxUrl,
        type: 'POST',
        data: {
          action: 'wdv_trigger_vacuum',
          nonce: data.nonce
        },
        dataType: 'json',
        success: function (res) {
          $btn.prop('disabled', false).removeClass('updating-message');
          if (res.success) {
            $feedback.css('color', '#16a34a').text(res.data.message || data.i18n.success);
            if (res.data.total_all_time) {
              $('.wdv-total-purged').text(res.data.total_all_time.toLocaleString());
            }
          } else {
            $feedback.css('color', '#dc2626').text(res.data.message || 'Error occurred.');
          }
        },
        error: function () {
          $btn.prop('disabled', false).removeClass('updating-message');
          $feedback.css('color', '#dc2626').text('Server communication failure.');
        }
      });
    });
  });
})(jQuery);

