(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.search_sorting_filter = {
    attach: function (context) {
      $('form.minfin-search-sorting-filter-form select', context).each(function () {
        $(this).on('change', function () {
          $(this).closest('form').submit();
        });
      });
    }
  };

})(jQuery, Drupal);
