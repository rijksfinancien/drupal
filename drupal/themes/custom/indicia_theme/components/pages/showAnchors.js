(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.showAnchors = {
    attach: function (context, settings) {

      if (settings.indicia_theme && settings.indicia_theme.show_anchors) {

        const node = $('article.node');

        let anchors = '';
        node.find('div.field__item > div.row').each(function() {
          const id = $(this)[0].id;
          const title = $(this).find('div.container > h2').text();
          if (id && title) {
            anchors += '<li><a href="#' + id + '">' +  title + '</a></li>';
          }
        });

        if (anchors.length > 0) {
          node.prepend('<div class="anchors"><div class="container"><h2>Inhoudsopgave</h2><ul>' + anchors + '</ul></div></div>');
        }

      }

    }
  };
}(jQuery, Drupal));
