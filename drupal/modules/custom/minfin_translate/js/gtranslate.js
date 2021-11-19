/**
 * Function called by the initialization of Google Translate.
 */
function googleTranslateElementInit() {
  new google.translate.TranslateElement({
    pageLanguage: 'nl',
    autoDisplay: false
  }, 'google_translate_element');
}

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.minfin_translate_gtranslate = {
    attach: function (context, settings) {
      $('#ui-datepicker-div').addClass('notranslate');

      var container = $('.gtranslate-container', context);

      $('.gtranslate', context).click(function (e) {
        e.preventDefault();
        if (container.hasClass('active')) {
          $('.gtranslate', context).attr('aria-pressed', 'false');
          container.removeClass('active');
        }
        else {
          $('.gtranslate', context).attr('aria-pressed', 'true');
          container.addClass('active');
        }
      });

      $('.gtranslate-close', context).click(function (e) {
        $('.gtranslate', context).attr('aria-pressed', 'false');
        container.removeClass('active');
        // If event is not triggered by a mouse click.
        if (!e.originalEvent.detail) {
          $('.gtranslate', context).focus();
        }
      });

      $(document, context).mouseup(function (e) {
        if (!container.is(e.target) && container.has(e.target).length === 0 && !e.target.classList.contains('gtranslate')) {
          $('.gtranslate', context).attr('aria-pressed', 'false');
          container.removeClass('active');
        }
      });

      $(document, context).keyup(function(e) {
        if (e.key === 'Escape') {
          $('.gtranslate', context).attr('aria-pressed', 'false');
          container.removeClass('active');
        }
      });
    }
  };

}(jQuery, Drupal));
