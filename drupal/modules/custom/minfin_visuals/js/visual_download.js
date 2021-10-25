(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.visualsPage = {
    attach: function (context, settings) {
      if (settings.minfin && settings.minfin.visual_download && settings.minfin.visual_download.api) {
        $('.page-actions').prepend('<a href="' + settings.minfin.visual_download.api + '" class="action icon-download" download title="' + Drupal.t('Download this data') + '"></a>')
      }
      if (settings.minfin && settings.minfin.visual_download && settings.minfin.visual_download.source) {
        $('.page-actions').prepend('<a href="' + settings.minfin.visual_download.source + '" class="action icon-download" download title="' + Drupal.t('Download source file') + '"></a>')
      }
    }
  };

}(jQuery, Drupal, drupalSettings));
