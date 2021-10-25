(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.indiciaMinfinBeleidsevaluatieVisual = {
    attach: function (context, settings) {
      var beleidscyclus_data = {
        agenda: [],
        bepaling: [],
        voorbereiding: [],
        uitvoering: [],
        terugkoppeling: []
      }

      // Preparing links for each cyclus.
      if (settings['beleids_evaluatie']) {
        var beleids_evaluatie_settings = settings['beleids_evaluatie'];

        if (beleids_evaluatie_settings['agenda_links']) {
          beleidscyclus_data['agenda'] = beleids_evaluatie_settings['agenda_links'];
        }

        if (beleids_evaluatie_settings['bepaling_links']) {
          beleidscyclus_data['bepaling'] = beleids_evaluatie_settings['bepaling_links'];
        }

        if (beleids_evaluatie_settings['voorbereiding_links']) {
          beleidscyclus_data['voorbereiding'] = beleids_evaluatie_settings['voorbereiding_links'];
        }

        if (beleids_evaluatie_settings['uitvoering_links']) {
          beleidscyclus_data['uitvoering'] = beleids_evaluatie_settings['uitvoering_links'];
        }

        if (beleids_evaluatie_settings['terugkoppeling_links']) {
          beleidscyclus_data['terugkoppeling'] = beleids_evaluatie_settings['terugkoppeling_links'];
        }
      }

      $(document).ready(function () {
        // add circle svg template to dom
        placeTemplate('agenda');
        placeTemplate('bepaling');
        placeTemplate('voorbereiding');
        placeTemplate('uitvoering');
        placeTemplate('terugkoppeling');
        // add events to circles
        $('div.circle').each(function () {
          var source = $(this).attr('source');
          $(this).bind('click', function () {
            if (holdPopup && source != curPopup) {
              show_be_popup(this, source, true);
            }
            holdPopup = true;
            $('#visual_be_popup .close_popup').css('display', 'block');
          });
          $(this).bind('mouseover', function () {
            show_be_popup(this, source);
          });
          $(this).bind('mouseout', function () {
            hide_be_popup();
          });
        });
        $(window).resize(function () {
          hide_be_popup();
          holdPopup = false;
        });
      })

      function placeTemplate(id, colour) {
        var temp = document.getElementById("visual_be_cricle").innerHTML;
        var target = document.getElementById(id + "_container");
        target.innerHTML = temp;
      }

      /*
      // Get and fill template
      */
      function mustache(temp, replace) {
        var matches = temp.match(/{+(.*?)}+/g);
        for (let m in matches) {
          var re = new RegExp(matches[m], "g");
          var key = matches[m].replace(/{{/, '').replace(/}}/, '');
          var str = typeof (replace[key]) != 'undefined' ? replace[key] : '';
          temp = temp.replace(re, str);
        }
        var listHtml = new DOMParser().parseFromString(temp, 'text/html');
        var node = listHtml.getElementsByTagName('body')[0].children[0];
        return node;
      }

      /*
      // Show popup on hover
      */
      var popupTimer = false;
      var curPopup = false;
      var holdPopup = false;
      var be_titles = ['Agendering'];

      function show_be_popup(obj, source, hold) {
        if (typeof (hold) == 'undefined') {
          hold = false;
        }
        // Get name from h4
        var name = $(obj).find('h4').html();
        // stop timer
        if (popupTimer) clearTimeout(popupTimer);
        // start new timer to open the popup
        popupTimer = setTimeout(function (obj, source, name, hold) {
          // now open the popup if not already open
          if ((hold || !holdPopup) && source != curPopup) {
            // remember current source
            curPopup = source;
            // remove popup from dom
            $('#visual_be_popup').remove();
            var temp = document.getElementById("visual_be_popup_template").innerHTML;
            var node = mustache(temp, {
              name: name
            });
            var target = document.getElementsByTagName("body")[0];
            target.appendChild(node);
            // add list to popup
            if (typeof (beleidscyclus_data[source]) != 'undefined' && beleidscyclus_data[source].length) {
              var hasLinks = false;
              for (var d in beleidscyclus_data[source]) {
                if (typeof (beleidscyclus_data[source][d]['name']) == 'string' && beleidscyclus_data[source][d]['name'] != '') {
                  if (typeof (beleidscyclus_data[source][d]['link']) == 'string' && beleidscyclus_data[source][d]['link'] != '') {
                    var row = $('<a class="minfin_link" href="' + beleidscyclus_data[source][d]['link'] + '">' + beleidscyclus_data[source][d]['name'] + '</a>');
                  }
                  else {
                    var row = $('<div class="minfin_nolink">' + beleidscyclus_data[source][d]['name'] + '</div>');
                  }
                  hasLinks = true;
                  $('#visual_be_popup_info').append(row);
                }
              }
              if (hasLinks) {
                $('#visual_be_popup_info').css('display', 'block');
              }
            }

            // add events
            $('#visual_be_popup').bind('mouseover', function () {
              // stop timer, prevent closing
              if (popupTimer) clearTimeout(popupTimer);
            });
            $('#visual_be_popup').bind('mouseout', function () {
              // stop timer, prevent closing
              if (popupTimer) clearTimeout(popupTimer);
              // hide popup
              hide_be_popup();
            });
            // position popup
            var y = $(obj).offset().top + $(obj).height() - 40;
            var w = $('#visual_be_popup').width() / 2;
            var x = $(obj).offset().left + ($(obj).width() / 2) - w;
            if (x < 10) x = 10;
            $('#visual_be_popup').css('left', x);
            $('#visual_be_popup').css('top', y);
            // hold?
            if (holdPopup) {
              $('#visual_be_popup .close_popup').css('display', 'block');
            }
            // bind event to clos button
            $('#visual_be_popup .close_popup').bind('click', function () {
              $('#visual_be_popup').remove();
              holdPopup = false;
              curPopup = false;
            })
          }
        }, hold ? 0 : 200, obj, source, name, hold);
      }

      /*
      // Hide popup
      */
      function hide_be_popup() {
        // stop timer
        if (popupTimer) clearTimeout(popupTimer);
        // start new timer to open the popup
        popupTimer = setTimeout(function () {
          if (!holdPopup) {
            // clear status
            curPopup = false;
            // remove popup from dom
            $('#visual_be_popup').remove();
          }
        }, 200);
      }
    }
  };

}(jQuery, Drupal));

