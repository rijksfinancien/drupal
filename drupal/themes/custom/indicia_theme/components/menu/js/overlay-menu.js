(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.overlayMmenu = {
    attach: function (context, settings) {
      const mainMenu = $('#mainMenu');
      // Mobile nav toggle.
      $(document).on('click', '.navbarToggler', function () {
        mainMenu.toggleClass('open');
        $('body').toggleClass('navbar-open');

        const $text = $(this).find('.text');
        if (mainMenu.hasClass('open')) {
          $text.text(Drupal.t('Close'));
        }
        else {
          $text.text(Drupal.t('Menu'));
        }
      });

      //hiden when scrolling down, show when scrolling up
      let lastScrollTop = 0;
      const menuBar = $('.menu-bar');
      const menuBarHeight = menuBar.height();
      let menuBarBot = 0;
      if (menuBar.position()) {
        menuBarBot = menuBar.position().top + menuBarHeight;
      }

      menuBar.css({'top': -menuBarHeight});
      $(window).scroll(function (event) {
        let st = $(this).scrollTop();
        if (st > lastScrollTop || st < menuBarBot) {
          menuBar.removeClass('fixed');
          $('body').removeClass('navbar-fixed');
          $('.menu-bar-wrapper').css({'height': 'auto'});
        }
        else {
          $('.menu-bar-wrapper').css({'height': menuBarHeight});
          mainMenu.removeClass('open');
          menuBar.addClass('fixed');
          $('body').addClass('navbar-fixed');
        }
        lastScrollTop = st;
      });

      // Desktop submenu opener.
      var subMenu = $('#submenu .container');
      $(document).on('click', '.icon-arrow-down', function () {
        subMenu.empty();
        const it = $(this);
        const wasOpen = it.hasClass('open');
        $('.icon-arrow-down').removeClass('open');

        if (!wasOpen) {
          it.addClass('open').siblings('ul').clone().appendTo(subMenu);
        }
      });
    }
  };
}(jQuery, Drupal));
