(function ($, Drupal) {
  Drupal.behaviors.premies_fiscaal = {
    attach: function (context, settings) {

      /**
       * Settings
       */
      var margin = {top: 20, right: 20, bottom: 30, left: 100},
        width,
        height,
        root,
        visual,
        column,
        hoofdstuk,
        label,
        chartWrapper,
        rootData,
        article;

      var x = d3.scaleBand();
      var y = d3.scaleLinear();

      /**
       * Set locale to nl-NL
       */
      d3.formatDefaultLocale(
        {
          "decimal": ",",
          "thousands": ".",
          "grouping": [3],
          "currency": ["€ ", ""]
        }
      );
      /**
       * Prepend values with currency symbol and use thousands separator
       */
      var format = d3.format("$,d");
      /**
       * Load the data
       */

      article = false;

      load();

      function load() {
        d3.json('/json/fiscale_regelingen/2020', function (error, data) {
          if (error) {
            throw error;
          }
          root = d3.hierarchy(data).sort(function (a, b) {
            return (Number(a.data.begroting) - Number(b.data.begroting));
          });
          rootData = root.data;
          rootData.sort(function(a,b){
            var aParts=0;
            var bParts=0;
            if(typeof(a.parts['fiscaal'])!='undefined'&&Number(a.parts['fiscaal'])>0){
              aParts++;
            }
            if(typeof(a.parts['begroting'])!='undefined'&&Number(a.parts['begroting'])>0){
              aParts++;
            }
            if(typeof(a.parts['premie'])!='undefined'&&Number(a.parts['premie'])>0){
              aParts++;
            }
            if(typeof(b.parts['fiscaal'])!='undefined'&&Number(b.parts['fiscaal'])>0){
              bParts++;
            }
            if(typeof(b.parts['begroting'])!='undefined'&&Number(b.parts['begroting'])>0){
              bParts++;
            }
            if(typeof(b.parts['premie'])!='undefined'&&Number(b.parts['premie'])>0){
              bParts++;
            }
            return bParts-aParts;
          });
          render(root.data);
        });
      }

      function render(root) {
        var target;
        $('.hoofdstuk-wrapper').remove();
        $('.back-link').remove();
        $('body').scrollTop($('.block-begroting-premie-fiscaal').offset().top - 200);
        visual = d3.select('#visual-wrapper')
          .selectAll('div')
          .data(root)
          .enter();
        column = visual.append('div')
          .classed('col-md-6', true)
          .classed('hoofdstuk-wrapper', true)
          .classed('expanded',true);

        hoofdstuk = column.append('div')
          .classed('hoofdstuk', true);
        label = hoofdstuk.append('div')
          .classed('label', true)
          .html(function (d) {
            return d.label;
          });
        // Add wrapper for chart
        chartWrapper = hoofdstuk.append('div')
          .classed('chart-wrapper', true)
          .each(function (d, i, group) {
            // Transform the data
            var partsArray = [];
            partsArray.push({'class':'premie','label': 'premie', 'value': (typeof(d.parts['premie']) != 'undefined'?Number(d.parts['premie']):0)});
            partsArray.push({'class':'begroting','label': 'begroting', 'value': (typeof(d.parts['begroting']) != 'undefined'?Number(d.parts['begroting']):0)});
            partsArray.push({'class':'fiscaal','label': 'fiscaal', 'value': (typeof(d.parts['fiscaal']) != 'undefined'?Number(d.parts['fiscaal']):0)});
            width = $(this).width() - margin.left - margin.right - 20;
            height = 200;
            x.range([0, width])
              .padding(0.5);
            y.range([height, 0]);
            var svg = d3.select(this).insert("svg")
              .attr("width", width + margin.left + margin.right)
              .attr("height", height + margin.top + margin.bottom)
              .append("g")
              .attr("transform",
                "translate(" + margin.left + "," + margin.top + ")");
            // Scale the range of the data in the domains
            x.domain(partsArray.map(function (d) {
              return d['label'];
            }));
            y.domain([0, d3.max(partsArray, function (d) {
              if (d['value'] > 0) {
                return d['value'];
              } else {
                return 0;
              }
            })]).nice();

            // Add the bars
            svg.selectAll(".bar")
              .data(partsArray)
              .enter().append("rect")
              .attr("class", function (d) {
                return "bar " + d['class']
              })
              .attr("x", function (d) {
                return x(d['label']);
              })
              .attr("width", x.bandwidth())
              .attr("y", function (d) {
                return y(d['value']);
              })
              .attr("height", function (d) {
                return height - y(d['value']);
              });
            // Add the axes
            svg.append("g")
              .attr("transform", "translate(0," + height + ")").call(d3.axisBottom(x));
            svg.append("g").call(d3.axisLeft(y));
          });

        // Add link to drill down to articles
        if (!article) {
          var articleLink = hoofdstuk.append('a')
            .classed('article-link', true)
            .html(function (d) {
              if (typeof(d.children) != 'undefined') {
                return 'Toon ' + d.children.length + ' begrotingsartikelen';
              } else {
                return '';
              }
            }).on('click', function (d, i) {
              d3.event.stopImmediatePropagation();
              $('.block-begroting-premie-fiscaal h2').html('Hoofdstuk ' + d.key + ": '" + d.label + "' onderverdeling per begrotingsartikel");
              article = true;
              render(d.children);
              $('.block-begroting-premie-fiscaal h2').after('<a class="back-link">Terug naar begrotingshoofdstukken<i class="icon-pijl-links"></i></i></a>');
              $('.block-begroting-premie-fiscaal .back-link').on('click', function () {
                article = false;
                render(rootData);
                $('.block-begroting-premie-fiscaal h2').html('<h2>Onderverdeling per begrotingshoofdstuk</h2>');
              });
            });
          articleLink.insert('i').classed('icon-pijl-rechts', true);
        }

        // Make same height
        var maxHeight = 0;
        $('.hoofdstuk-wrapper').each(function () {
          if ($(this).height() > maxHeight) {
            maxHeight = $(this).height();
          }
        });
        $('.hoofdstuk-wrapper').each(function () {
          $(this).height(maxHeight);
        });
      }
    }
  };
})(jQuery, Drupal);
