(function ($, Drupal) {
  'use strict';

  // Toggles the advanced search block.
  Drupal.behaviors.toggle_search = {
    attach: function (context, settings) {
      let active = false;
      const querystring = window.location.search;
      if (querystring && querystring.match("search=") && querystring.match("search=").length > 0) {
        active = true;
        $('#minfin-search .icon-search').toggleClass('active');
        $('#block-minfinadvancedsearchblock').toggleClass('active');
      }
      toggleFocusableFormElements(active);

      $(document).on('click keydown', '#mainMenu .searchToggle',function (e) {
        if (e.type === 'click' || e.type === 'keypress' && e.keyCode === 13) {
          $(this).toggleClass('active');
          $('#minfin-search .icon-search').trigger('click');
        }
      });

      $(document).on('click keydown', '#minfin-search .icon-search', function (e) {
        if (e.type === 'click' || e.type === 'keydown' && e.keyCode === 13) {
          active = !active;
          $(this).toggleClass('active');
          $('#block-minfinadvancedsearchblock').toggleClass('active');
          toggleFocusableFormElements(active);
        }
      });

      // Disable form elements when the advanced search is not active for accessibility.
      function toggleFocusableFormElements(active) {
        const $formElements = $('.minfin-advanced-search-form :input');
        if (active) {
          $formElements.removeAttr('disabled');
        }
        else {
          $formElements.prop('disabled', true);
        }
      }
    },
  };

  // Toggles the advanced search block.
  Drupal.behaviors.search_suggester = {
    attach: function (context, settings) {
      const resultContainer = $('#edit-search-suggestions');
      const doc = $(document);

      function processResult(data, searchRow = false, suggestionId = '') {
        if (searchRow) {
          var old = $('#' + suggestionId);
          $(data).insertAfter(old);
          old.remove();

          $('#' + suggestionId + ' select').each(function () {
            $(this).val($(this).data('default-value'));
          }).chosen({
            disable_search: true,
            inherit_select_classes: true
          });
        }
        else {
          resultContainer.empty().append(data).addClass('active');
          resultContainer.width($('.js-suggester').width() + 5);
        }
      }

      let req, term;
      let contentType = $('input[name="type"]').val();

      function getUrl(url, suggestionId = '', params = {}, searchRow = false) {
        // Abort a running ajax request.
        if (req) {
          req.abort();
        }
        if (!searchRow) {
          if (contentType) {
            url += encodeURI(contentType) + '/';
          }
          if (term) {
            url += encodeURI(term);
            params['suggestion'] = suggestionId;
            url += '?';
            url += $.param(params);
            req = $.get(url, function (data) {
              processResult(data);
            });
          }
        }
        else {
          url += '?';
          params['search_row'] = suggestionId.replace('search-result-', '');
          url += $.param(params);
          req = $.get(url, function (data) {
            processResult(data, searchRow, suggestionId);
          });
        }
      }

      doc.on('keyup focus', '.js-suggester', function () {
          term = $(this).val();
          if (term.length === 0) {
            resultContainer.empty();
            resultContainer.removeClass('active');
          }
          else if (term.length > 1) {
            getUrl('/ajax/suggest/');
          }
        },
      );

      doc.on('mouseup focus', 'input[name=\'type\']', function () {
        contentType = $(this).val();
        getUrl('/ajax/suggest/');
      });

      doc.mouseup(function (e) {
        if (!resultContainer.is(e.target) &&
          resultContainer.has(e.target).length === 0) {
          resultContainer.empty();
          resultContainer.removeClass('active');
        }
      });

      doc.once('badges').ajaxComplete(function () {
        $('.minfin-advanced-search-form .search-badge').each(function () {
          if ($(this).find('option').length <= 1) {
            $(this).attr('disabled', '');
          }
          if ($(this).data('defaultValue')) {
            $(this).val($(this).data('default-value'));
          }
          $(this).chosen({
            disable_search: true,
            inherit_select_classes: true
          });
          $(this).change(function () {
            //get the updated values from the badges, and reload the suggester
            $(this).closest('.suggestion').each(function () {
              let badges = {};
              $.each($(this).find('.search-badge'), function () {
                badges[$(this).data('key')] = $(this).val();
              });

              getUrl('/ajax/suggest/', $(this).attr('id'), badges);
            });
          });
        });
      });

      doc.on('change', '#search-container .search-badge', function () {
        let badges = {};
        $(this).parents('.search-badges').find('select.search-badge').each(function () {
          badges[$(this).data('key')] = $(this).val();
        });
        getUrl('/ajax/update_search_row/', $(this).parents('.search-result').attr('id'), badges, true);
      });

      $('select.search-badge').each((function () {
        if ($(this).find('option').length <= 1) {
          $(this).attr('disabled', '');
        }
        $(this).chosen({
          disable_search: true,
          inherit_select_classes: true
        });
      }));

      $(document).on('click', '.facet-heading', function () {
        $(this).parent().toggleClass('active');
      });

    },
  };
}(jQuery, Drupal));
