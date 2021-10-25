(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.verzelfstandigingen = {
    attach: function (context) {
      rijksTreemap.create().init('#treemap-element', '/modules/custom/rijksfinancien_visuals/assets/verzelfstandigingen/data/treemap.json', {'parseUrl': true});
    }
  };

})(jQuery, Drupal);
