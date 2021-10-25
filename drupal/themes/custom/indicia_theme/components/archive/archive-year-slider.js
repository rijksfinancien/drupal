(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.indiciaArchiveYearSlider = {
    attach: function (context, settings) {
      const $yearSelectors = $('.year-selector');
      if ($yearSelectors.length) {
        $yearSelectors.each(function (index, slider) {
          const $slider = $(slider);
          const $items = $slider.find('li');
          const itemCount = $items.length;

          $slider.slick({
            mobileFirst: true,
            rows: 2,
            slidesPerRow: 5,
            accessibility: true,
            arrows: false, // We create custom arrows.
            infinite: false,
            responsive: [
              {
                breakpoint: 768,
                settings: {
                  rows: 1,
                  slidesToShow: 10,
                  slidesPerRow: 1,
                  slidesToScroll: 10,
                },
              },
            ],
          });

          // Initialise slider on load and slick breakpoint.
          initSlider();
          $slider.on('breakpoint', () => initSlider());

          function initSlider() {
            slideToActiveItem();
            addSliderActions();
          }

          // Slides to the decade containing the active item.
          function slideToActiveItem() {
            const $activeItem = $slider.find('a.active');

            let activeItemIndex = 0;
            if ($activeItem.length) {
              activeItemIndex = $activeItem.parents('.slick-slide').data('slick-index');
            }

            $slider.slick('slickGoTo', activeItemIndex, true);
          }

          // Adds the slider actions to slide through the decades.
          function addSliderActions() {
            const $arrowLeft = $('<span>').addClass('icon-arrow-left');
            const $arrowRight = $('<span>').addClass('icon-arrow-right');

            const $firstSlide = $('<button>').attr('type', 'button').attr('title', Drupal.t('First')).addClass('first-slide slide-action').append([$arrowLeft.clone(), $arrowLeft.clone()]);
            const $prevSlide = $('<button>').attr('type', 'button').attr('title', Drupal.t('Previous')).addClass('prev-slide slide-action').append($arrowLeft.clone());
            const $prefixSlideActions = $('<div>').addClass('prefix-slide-actions').append([$firstSlide, $prevSlide]);

            const $nextSlide = $('<button>').attr('type', 'button').attr('title', Drupal.t('Next')).addClass('next-slide slide-action').append($arrowRight.clone());
            const $lastSlide = $('<button>').attr('type', 'button').attr('title', Drupal.t('Last')).addClass('last-slide slide-action').append([$arrowRight.clone(), $arrowRight.clone()]);
            const $suffixSlideActions = $('<div>').addClass('suffix-slide-actions').append([$nextSlide, $lastSlide]);

            $slider.prepend($prefixSlideActions);
            $slider.append($suffixSlideActions);

            $slider.prepend($prefixSlideActions);
            $slider.append($suffixSlideActions);

            $firstSlide.click(function () {
              $slider.slick('slickGoTo', 0);
            });

            $prevSlide.click(function () {
              $slider.slick('slickPrev');
            });

            $nextSlide.click(function () {
              $slider.slick('slickNext');
            });

            $lastSlide.click(function () {
              $slider.slick('slickGoTo', itemCount - 1);
            });
          }
        });
      }
    }
  };

}(jQuery, Drupal));
