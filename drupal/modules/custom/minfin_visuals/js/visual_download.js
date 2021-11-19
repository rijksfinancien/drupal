(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.visualsPage = {
    attach: function (context, settings) {
      let url, title;
      const showDownloadSource = settings.minfin && settings.minfin.visual_download && settings.minfin.visual_download.source;
      const showDownloadApi = settings.minfin && settings.minfin.visual_download && settings.minfin.visual_download.api;

      if (showDownloadSource && showDownloadApi) {
        $('.page-actions').prepend('<div class="downloads action"><div class="toggle-downloads" title="' + Drupal.t('Download files') + '"><div class="icon-download not-closing" tabindex="0"></div></div><div class="downloads-menu not-closing"></div></div>');
        $('.page-actions .downloads-menu').hide();

        $(document).on('click keydown', '.toggle-downloads', function (e) {
          if (e.keyCode === 13 || e.type === 'click') {
            $('.page-actions .downloads-menu').toggle();
          }
        });

        $('html').click(function (e) {
          if (!$(e.target).hasClass('not-closing')) {
            $('.page-actions .downloads-menu').hide();
          }
        });

        if (showDownloadSource) {
          title = Drupal.t('Source file');
          url = settings.minfin.visual_download.source;
          let dataType = url.split(/[#?]/)[0].split('.').pop().trim();

          $('.downloads-menu').append('<a href="' + url + '" class="download download-source-file not-closing" download title="' + Drupal.t('Download source file') + '">' + title + ' (' + dataType + ')</a>');
        }

        if (showDownloadApi) {
          title = Drupal.t('API');
          url = settings.minfin.visual_download.api;

          $('.downloads-menu').append('<a href="' + url + '" class="download download-api not-closing" download title="' + Drupal.t('Download API data') + '">' + title + ' (csv)</a>');
        }
      } else {
        if (showDownloadApi) {
          title = Drupal.t('Download API data');
          url = settings.minfin.visual_download.api;

          $('.page-actions').prepend('<a href="' + url + '" class="action icon-download" download title="' + title + '"></a>')
        }

        if (showDownloadSource) {
          title = Drupal.t('Download source file');
          url = settings.minfin.visual_download.source;

          $('.page-actions').prepend('<a href="' + url + '" class="action icon-download" download title="' + title + '"></a>')
        }
      }
    }
  };
}(jQuery, Drupal, drupalSettings));
