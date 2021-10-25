(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.checkOverflow = function () {
    if (this.offsetWidth < this.scrollWidth) {
      $(this).addClass('overflow');
    }
    else {
      $(this).removeClass('overflow');
    }
  };

  Drupal.behaviors.addOverflowScroll = function (elements) {
    elements.on('scroll', function () {
      if ($(this).scrollLeft() > 0) {
        $(this).removeClass('overflow');
      }
      else {
        $(this).addClass('overflow');
      }
    });
  };

  Drupal.behaviors.indicia = {
    attach: function (context, settings) {
      // Add chosen to all selects with chosen class.
      const defaultOptions = {
        'disable_search_threshold': 0,
        'default_no_result_text': Drupal.t('No results'),
        'inherit_select_classes': true,
        'disable_search': false,
      };
      const $chosen = $('.chosen');
      if ($chosen.length) {
        $chosen.once('indiciaChosen').each(function (index, chosen) {
          const $currentChosen = $(chosen);
          let options = defaultOptions;

          if ($currentChosen.attr('placeholder')) {
            options['placeholder_text_single'] = Drupal.t($currentChosen.attr('placeholder'));
            options['placeholder_text_multiple'] = Drupal.t($currentChosen.attr('placeholder'));
          }

          if ($currentChosen.attr('data-disable-search')) {
            options['disable_search'] = true;
          }

          $currentChosen.chosen(options);
        });
      }

      // Labels the chosen search input for accessibility.
      $('.chosen-search-input').attr('aria-label', Drupal.t('Search'));

      // Check overflow on load.
      const resp = $('.responsive-wrapper').each(Drupal.behaviors.checkOverflow);
      resp.each(Drupal.behaviors.checkOverflow);
      Drupal.behaviors.addOverflowScroll(resp);

      const $externalLinks = $('a[href^="http"]');
      if ($externalLinks.length) {
        $externalLinks.once('indicia').each(function (index, externalLink) {
          const $externalLink = $(externalLink);
          $externalLink.attr('target', '_blank');

          if ($externalLink.find('img')) {
            $externalLink.addClass('contains-img');
          }
        });
      }
    }
  };

  Drupal.behaviors.indiciaSelect2 = {
    attach: function (context, settings) {
      const $select2 = $('.select2');
      if ($select2.length) {
        $select2.once('indiciaSelect2').each(function (i, select) {
          const $select = $(select);
          const options = [];

          if ($select.hasClass('no-search')) {
            options['minimumResultsForSearch'] = '-1';
          }

          $select.select2(options);
        });
      }
    }
  };

  Drupal.behaviors.chapterSelect = {
    attach: function (context, settings) {
      $(document).on('change', '.chapter-select', function () {
        window.location = this.value;
      });

      $(document).on('click', '.begrotings-fase', function () {
        var it = $(this);
        var text = it.children('.text');
        var ico = it.children('.toggle-icon');
        var active = it.hasClass('active');
        if (active) {
          it.removeClass('active');
          text.html(Drupal.t('Show budget fases'));
          ico.html('+');
        }
        else {
          it.addClass('active');
          text.html(Drupal.t('Hide budget fases'));
          ico.html('-');
        }
      });
    }
  };

  Drupal.behaviors.related = {
    attach: function (context, settings) {
      var active = true;

      $(window).on('load resize', function () {
        var $icon = $('.minfin-related .toggle-icon')
        active = $('.minfin-related .slider').is(':visible');

        if (active) {
          $icon.html('-');
        }
        else {
          $icon.html('+');
        }
      });

      $(document).on('click', '.minfin-related .header', function () {
        var it = $(this);
        var ico = it.children('.toggle-icon');
        if (active) {
          ico.html('+');
        }
        else {
          ico.html('-');
        }

        active = !active;
        it.siblings('.slider').slideToggle();
      });
    }
  };

  // Triggers window.print() when pressing the enter key while focused on a print action icon for accessibility.
  Drupal.behaviors.indiciaIconPrint = {
    attach: function (context, settings) {
      $(document).on('keydown', '.action.icon-print', function (e) {
        if (e.keyCode === 13) {
          window.print();
        }
      });
    }
  };

  Drupal.behaviors.indiciaBeleidsOverview = {
    attach: function (context, settings) {
      const $form = $('.minfin-beleidsevaluaties-overview-filter-form');
      const $inputs = $('.minfin-beleidsevaluaties-overview-filter-form :input');
      if ($form.length && $inputs.length) {
        $inputs.change(function () {
          $form.find('.form-submit').first().trigger('click');
        });
      }
    }
  }

  Drupal.behaviors.indiciaParagraphVisualTeasers = {
    attach: function (context, settings) {
      const $columns = $('.field--name-column');
      if ($columns.length) {
        $columns.each(function (i, column) {
          const $column = $(column);
          if ($column.find('.paragraph--type--coronavisual-teaser').length) {
            $column.addClass('full-height');
          }
        });
      }
    }
  }

  Drupal.behaviors.indiciaToggleSeperatedList = {
    attach: function (context, settings) {
      const $seperatedLists = $('.toggle-seperated-list');
      if ($seperatedLists.length) {
        $seperatedLists.once('indiciaToggleSeperatedList').each(function (i, list) {
          const $list = $(list);
          const text = $list.html();
          const limit = $list.data('seperated-list-limit') || 3;

          if (text.length) {
            const seperatedList = text.split('</a>, ');

            if (seperatedList.length > limit) {
              const limitedList = seperatedList.slice(0, limit);
              const hiddenList = seperatedList.slice(limit, seperatedList.length);
              const $toggle = $('<span>').toggleClass('toggle-hidden-list').html('</a>, (' + hiddenList.length + ' ' + Drupal.t('meer') + '...)');

              $list.empty();
              $list.append($('<span>').addClass('limited-list').html(limitedList.join('</a>, ')));
              $list.append($('<span>').addClass('hidden-list').html(', ' + hiddenList.join('</a>, ')));
              $list.append($toggle);

              $toggle.on('click', function () {
                $list.toggleClass('active');

                if ($list.hasClass('active')) {
                  $toggle.text('(' + hiddenList.length + ' ' + Drupal.t('minder') + '...)');
                }
                else {
                  $toggle.text('(' + hiddenList.length + ' ' + Drupal.t('meer') + '...)');
                }
              });
            }
          }
        });
      }
    }
  }

}(jQuery, Drupal));
