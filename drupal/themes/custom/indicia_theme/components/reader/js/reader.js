(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.reader = {
    attach: function (context, settings) {
      // Modal.
      const modal = $('#modal'), body = $('body, html'), doc = $(document);
      let scrollTop;

      const openModal = function () {
        scrollTop = $(window).scrollTop();
        modal.addClass('active');
        body.addClass('modal-active');
        const resp = modal.find('.responsive-wrapper');
        resp.each(Drupal.behaviors.checkOverflow);
        Drupal.behaviors.addOverflowScroll(resp);
      };

      const closeModal = function () {
        window.location.href = '#'
        modal.removeClass('active');
        body.removeClass('modal-active');
        $(window).scrollTop(scrollTop - 30);
      };

      doc.on('click', '#modal .close', function () {
        closeModal();
      });

      doc.on('click', '#modal', function (e) {
        if (e.target !== this) {
          return;
        }
        closeModal();
      });

      // Esc listener.
      document.body.addEventListener('keyup', function (e) {
        if (e.key === "Escape") {
          if (modal.isVisible()) {
            closeModal();
          }
          else {
            closeReader();
          }
        }
      });

      // Table popout.
      doc.on('click', '.action-popup', function () {
        const table = $(this).parents('.table-container').children('.responsive-wrapper').clone();
        $('#modal-content').empty().append(table);
        openModal();
      });

      var openTable = function () {
        window.location.hash = 'showTable'
        let id = $('.action-open-table').data('table-id');
        let popup = $('#reader-page .action-popup');
        if (popup[id]) {
          popup[id].click();
        }
        else if (popup[0]) {
          popup[0].click();
        }
      }

      doc.on('click', '.action-open-table', openTable);
      if(window.location.hash === '#showTable') {
        openTable()
      }

      // Table widening.
      doc.on('click', '.action-widen', function () {
        $(this).parents('.table-container').toggleClass('widen').find('.responsive-wrapper').each(Drupal.behaviors.checkOverflow);
      });

      // Toggle,save and reinstate reader mode.
      const openReader = function () {
        body.addClass('reader-mode');
        sessionStorage.setItem('reader-mode', 'true');
      };
      const closeReader = function () {
        body.removeClass('reader-mode');
        sessionStorage.setItem('reader-mode', 'false');
        changeFontSize(1);
      };
      if (sessionStorage.getItem('reader-mode') === 'true' && jQuery('.action-reader-mode').length) {
        openReader();
      }
      doc.on('click', '.action-reader-mode', openReader);
      doc.on('click', '.action-close-reader', closeReader);

      // Toggle,save and reinstate reader dark mode.
      const normalButton = $('.action-page-color-normal');
      const sepiaButton = $('.action-page-color-sepia');
      const darkButton = $('.action-page-color-dark');

      const toggleReaderModeDark = function () {
        normalButton.removeClass('active');
        sepiaButton.removeClass('active');
        darkButton.addClass('active');
        body.removeClass('reader-mode-sepia');
        body.toggleClass('reader-mode-dark');
        sessionStorage.setItem('reader-mode-dark', 'true');
        sessionStorage.removeItem('reader-mode-sepia');
      };
      if (sessionStorage.getItem('reader-mode-dark') === 'true') {
        toggleReaderModeDark();
      }
      doc.on('click', '.action-page-color-dark', toggleReaderModeDark);

      // Toggle,save and reinstate reader dark mode.
      const toggleReaderModeSepia = function () {
        normalButton.removeClass('active');
        sepiaButton.addClass('active');
        darkButton.removeClass('active');
        body.removeClass('reader-mode-dark');
        body.toggleClass('reader-mode-sepia');
        sessionStorage.setItem('reader-mode-sepia', 'true');
        sessionStorage.removeItem('reader-mode-dark');
      };
      if (sessionStorage.getItem('reader-mode-sepia') === 'true') {
        toggleReaderModeSepia();
      }
      doc.on('click', '.action-page-color-sepia', toggleReaderModeSepia);

      // Reset reader colors.
      doc.on('click', '.action-page-color-normal', function () {
        normalButton.addClass('active');
        sepiaButton.removeClass('active');
        darkButton.removeClass('active');
        body.removeClass('reader-mode-dark');
        body.removeClass('reader-mode-sepia');
        sessionStorage.removeItem('reader-mode-sepia');
        sessionStorage.removeItem('reader-mode-dark');
      });

      // Font scaling.
      let current = 1;
      const readerPage = $('#reader-page');

      function changeFontSize(amount) {
        readerPage.find('p, .table-title').css({
          'font-size': amount + 'em',
          'line-height': 1.7 + 'em'
        });
        readerPage.find('h4').css({
          'font-size': amount * 1.5 + 'em',
          'line-height': 1.7 + 'em'
        });
      }

      doc.on('click', '.action-font-bigger', function () {
        current += .1;
        changeFontSize(current);
      });
      doc.on('click', '.action-font-smaller', function () {
        current -= .1;
        changeFontSize(current);
      });
    }
  };
}(jQuery, Drupal));
