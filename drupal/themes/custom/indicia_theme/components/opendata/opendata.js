(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.yearMenu = {
    attach: function (context, settings) {
      var yearTables = $('.year-table');
      var yearSelects = $('#year a')
      $(document).on('click', '#year .year a', function () {
        yearTables.hide();
        yearSelects.removeClass('is-active')
        $('#table-' + $(this).addClass('is-active').data('year')).show();
      });
    }
  };
}(jQuery, Drupal));
