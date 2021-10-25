(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.indiciaArchiveYearSelector = {
    attach: function (context, settings) {
      const $yearSelectorForm = $('.minfin-general-archive-selector-form');
      if ($yearSelectorForm.length) {
        const $from = $yearSelectorForm.find('.form-item-from input');
        const $till = $yearSelectorForm.find('.form-item-till input');

        if ($from.length && $till.length) {
          $from.change(function () {
            $till.val($from.val());
          });
        }
      }
    }
  };

}(jQuery, Drupal));
