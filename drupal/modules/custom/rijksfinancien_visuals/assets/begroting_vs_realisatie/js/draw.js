var COLOR_MORE = "#01689b";
var COLOR_LESS = "#a90061";
var COLOR_MORE_LIGHT = "#cce0f1";
var COLOR_LESS_LIGHT = "#e5b2cf";
//domain for bar chart is fixed at -50 to 50%
var DOMAIN_PERC = [-50, 50]

var detailedXScaleLocal;

function draw() {

  //set title of the page
  d3.select("#beg-real_title")
    .text(function() {
      return labels["beg-real_title"][LANGUAGE] + ((selectedItem == null) ? "" : (" - " + selectedItem.label));
    })

  //show backButton only when individual artikelen is shown (and not the totals)
  d3.select("#beg-real_backButton")
    .style("display", function() {
      return selectedItem != null ? "inline-block" : "none";
    });

  d3.select("#beg-real_backButton span")
    .text(labels["beg-real_backButton"][LANGUAGE]);


  //make list of items to show.
  //if no item is selected, only show Totalen of each department.
  //if item is selected, show its artikelen.
  if (selectedItem == null) itemsToShow = data.filter(function(d) { return d.omschrijving == "TOTAAL" });
  else itemsToShow = data.filter(function(d) {
    return (d.hoofdstuk == selectedItem.hoofdstuk) && (d.omschrijving != "TOTAAL");
  });
  //remove items that have no maxValue or that have maxValue == 0 (which means that they have no data for the period that is displayed)
  itemsToShow = itemsToShow.filter(function(i) { return i.maxValue != null && i.maxValue > 0});

  //size of area for the bar chart, not including labels and axes
  var barAreaWidth  = BARCHART_SIZE.width - BARCHART_SIZE.margin.left - BARCHART_SIZE.margin.right;
  var barAreaHeight = BARCHART_SIZE.height - BARCHART_SIZE.margin.top - BARCHART_SIZE.margin.bottom;
  var barXScale = d3.scaleBand()
    .range([0,barAreaWidth])
    .paddingInner(BARCHART_SIZE.barPaddingInner) //relative space between bars
    .paddingOuter(BARCHART_SIZE.barPaddingOuter)
    .domain(years);
  var barYScale = d3.scaleLinear().range([barAreaHeight,0]).clamp(true);

  //size of area for the detailed bar chart, not including labels and axes
  var detailedAreaWidth  = DETAILED_GRAPH_SIZE.width - DETAILED_GRAPH_SIZE.margin.left - DETAILED_GRAPH_SIZE.margin.right;
  var detailedAreaHeight = DETAILED_GRAPH_SIZE.height - DETAILED_GRAPH_SIZE.margin.top - DETAILED_GRAPH_SIZE.margin.bottom;
  var detailedXScale = d3.scaleBand()
    .range([0,detailedAreaWidth])
    .paddingInner(DETAILED_GRAPH_SIZE.blockPaddingInner) //relative space between year blocks
    .paddingOuter(DETAILED_GRAPH_SIZE.blockPaddingOuter)
    .domain(years);

  //y scale for detailed chart. Its domain changes per chart
  var detailedYScale = d3.scaleLinear().range([detailedAreaHeight-1,0]); //show at least a thin line (so -1)

  //scale for barcharts per year
  detailedXScaleLocal = d3.scaleBand()
    .range([0,detailedXScale.bandwidth()])
    .paddingInner(DETAILED_GRAPH_SIZE.begrotingsBarPaddingInner) //relative space between begrotings bars
    .paddingOuter(DETAILED_GRAPH_SIZE.begrotingsBarPaddingOuter)
    .domain([0,1,2,3]); //indices 0-3, for: ontwerp, vastgesteld, 1e sup, 2e sup, not including index 4: realisatie

  var minSaldoRel = null;
  var maxSaldoRel = null;
  itemsToShow.forEach(function(i) {
    i.values.forEach(function(v) {
      if (minSaldoRel == null || v.saldoRel < minSaldoRel) minSaldoRel = v.saldoRel;
      if (maxSaldoRel == null || v.saldoRel > maxSaldoRel) maxSaldoRel = v.saldoRel;
    })
  })
  barYScale.domain(DOMAIN_PERC);


  //each 'artikel' gets a DIV element, containing two graphs: the barchart and a line chart

  var newCharts = d3.select("#beg-real_charts").selectAll(".chartBlock")
    .data(itemsToShow, function(d) { return d.id })
    .enter()
    .append("div")
    .attr("class","chartBlock");

  newCharts
    .append("div")
    .attr("class", "chartHeader");

  newCharts.select(".chartHeader")
    .append("span")
    .attr("class","chartTitle")
    .text(function(d) {
      var chapter = LANGUAGE == "en" ? d.hoofdstuk_en : d.label;
      var description = LANGUAGE == "en" ? d.omschrijving_en : d.omschrijving;
      return chapter + " - " + description;
    });

  newCharts.select(".chartHeader")
    .append("button")
    .attr("type", "button")
    .attr("class", "btn btn-link detailButton")
    .text(labels["articleButton"][LANGUAGE])
    .style("display", function(d) {
      //show button when it has children
      var nrChildren = data.filter(function(v) {
        //do not translate 'TOTAAL' here, this is part of the data
        return (d.omschrijving == "TOTAAL") && (v.hoofdstuk == d.hoofdstuk) && (v.omschrijving != "TOTAAL");
      }).length;
      return nrChildren > 0 ? "inline" : "none";
    })
    .on("click", function(d) {
      selectedItem = d;
      draw();
    });

  newCharts.select(".chartHeader")
    .append("button")
    .attr("type", "button")
    .attr("class", "btn btn-link detailButton")
    .text(labels["beg-real_backButton"][LANGUAGE])
    .style("display", function(d) {
      return d.omschrijving != "TOTAAL" ? "inline" : "none";
    })
    .on("click", function() {
      selectedItem = null;
      draw();
    });

  newCharts
    .append("div")
    .attr("class","barChart")
    .each(function(d) {
      drawBarChart(d3.select(this), d, barXScale, barYScale);
    });

  newCharts
    .append("div")
    .attr("class","detailedChart")
    .each(function(d) {
      drawDetailedGraphs(d3.select(this), d, detailedAreaHeight, detailedXScale, detailedYScale, detailedXScaleLocal);
    });

  d3.select("#beg-real_charts").selectAll(".chartBlock")
    .data(itemsToShow, function(d) { return d.id })
    .exit()
    .remove();

}

//draws a barchart in 'element', which is a D3 element
function drawBarChart(d3Element, item, xScale, yScale) {
  //remove any chart that already is present
  d3Element.select("svg").remove();

  var svg = d3Element
    .append("svg")
    .attr("width", BARCHART_SIZE.width)
    .attr("height", BARCHART_SIZE.height);

  svg.append("text")
    .attr("class","chartDescription")
    .attr("x", 0)
    .attr("y", BARCHART_SIZE.margin.top - 20)
    .text(labels["chartDescription_perc"][LANGUAGE]);

  var graph = svg
    .append("g")
    .attr("id", "beg-real_barArea")
    .attr("transform", function() {
      return "translate(" + BARCHART_SIZE.margin.left + "," + BARCHART_SIZE.margin.top + ")";
    })

  graph.selectAll(".bar")
    .data(function() {
        //only show data that has a valid value for saldoRel, and that falls in the range of years
        return item.values.filter(function(v) {
          return (v.saldoRel != null && years.indexOf(v.jaar) != -1) })
      },
      function(d) { return d.jaar; }) // map bar on year
    .enter()
    .append("rect")
    .attr("class","bar")
    .attr("x", function(d) { return xScale(d.jaar); })
    .attr("y", function(d) {
      //if saldo smaller than zero, y is on axis
      return yScale(Math.max(0, d.saldoRel));
    })
    .attr("width", function(d) { return xScale.bandwidth(); })
    .attr("height", function(d) {
      return Math.abs(yScale(0) - yScale(d.saldoRel));
    })
    .style("fill", function(d) {
      return d.saldoAbs > 0 ? COLOR_LESS_LIGHT : COLOR_MORE_LIGHT;
    });

  graph.selectAll(".valueLabel")
    .data(function() {
        //only show data that has a valid value for saldoRel, and that falls in the range of years
        return item.values.filter(function(v) {
          return (v.saldoRel != null && years.indexOf(v.jaar) != -1) })
      },
      function(d) { return d.jaar; }) // map bar on year
    .enter()
    .append("text")
    .attr("class","valueLabel")
    .attr("x", function(d) { return xScale(d.jaar) + xScale.bandwidth()/2; })
    .attr("y", function(d) {
      var offset = d.saldoRel < 0 ? 12 : -2;
      return yScale(d.saldoRel) + offset;
    })
    .text(function(d) {
      return d3.format(".1f")(d.saldoRel).replace(".",",") + " %";
    })
    .style("fill", function(d) {
      return d.saldoRel < 0 ? COLOR_MORE : COLOR_LESS;
    })

  graph.selectAll(".yearLabel")
    .data(years, function(d) { return d })
    .enter()
    .append("text")
    .attr("class", "yearLabel")
    .attr("x", function(d) { return xScale.bandwidth()/2 + xScale(d)})
    .attr("y", BARCHART_SIZE.height - BARCHART_SIZE.margin.bottom)
    .text(function(d) { return d });

  graph.append("line")
    .attr("class", "zeroLine")
    .attr("x1", 0)
    .attr("y1", yScale(0))
    .attr("x2", BARCHART_SIZE.width - BARCHART_SIZE.margin.right)
    .attr("y2", yScale(0));

  var axis = graph.append("g")
    .attr("class","axis")
    .call(d3.axisLeft(yScale)
      .ticks(5)
      .tickFormat(function(d) { return d + "%" })
    )

  axis.selectAll("line").remove();
  axis.select(".domain").remove();
}

//draw detailed barchart
function drawDetailedGraphs(d3Element, item, areaHeight, xScale, yScale, xScaleLocal) {
  var bars = []; //data for each individual bar

  item.values.forEach(function(v) {
    if (years.indexOf(v.jaar) != -1) {
      if (!isNaN(v.begrotingOntwerp))
        bars.push({
          year: v.jaar,
          value: v.begrotingOntwerp,
          index: 0
        });
      if (!isNaN(v.begrotingVastgesteld))
        bars.push({
          year: v.jaar,
          value: v.begrotingVastgesteld,
          index: 1
        });
      if (!isNaN(v.begrotingEersteSup))
        bars.push({
          year: v.jaar,
          value: v.begrotingEersteSup,
          index: 2
        });
      if (!isNaN(v.begrotingTweedeSup))
        bars.push({
          year: v.jaar,
          value: v.begrotingTweedeSup,
          index: 3
        });
      if (!isNaN(v.realisatie))
        bars.push({
          year: v.jaar,
          value: v.realisatie,
          index: 4
        });
    }
  })

  //each chart has its own domain for the y-axis, from lowest to highest value
  yScale.domain(
    d3.extent(bars.map(function(d) { return d.value; }))
  );

  var svg = d3Element
    .append("svg")
    .attr("width", DETAILED_GRAPH_SIZE.width)
    .attr("height", DETAILED_GRAPH_SIZE.height);


  svg.append("text")
    .attr("class","chartDescription")
    .attr("x", DETAILED_GRAPH_SIZE.margin.left)
    .attr("y", DETAILED_GRAPH_SIZE.margin.top - 20)
    .text(labels["chartDescription_abs"][LANGUAGE]);


  var graph = svg
    .append("g")
    .attr("id", "beg-real_lineArea")
    .attr("transform", function() {
      return "translate(" + DETAILED_GRAPH_SIZE.margin.left + "," + DETAILED_GRAPH_SIZE.margin.top + ")";
    })

  //add a group element for _each year_. Each group will contain one barchart
  //with 5 bars: ontwerpbegroting, vastgestelde begroting, 1e supp, 2e supp
  //and a bar representing 'realisatie'

  var yearCharts = graph.selectAll(".yearChart")
    .data(years, function(d) { return d })
    .enter()
    .append("g")
    .attr("class", "yearChart")
    .attr("transform", function(d) {
      return "translate(" + xScale(d) + ", 0)";
    });

  //add year label
  yearCharts
    .append("text")
    .attr("class","yearLabel")
    .attr("x", function(d) { return xScale.bandwidth()/2; })
    .attr("y", areaHeight + 30)
    .text(function(d) { return d });


  //add bars
  yearCharts.selectAll(".begrotingBar")
    .data(function(d) {
        //get all points for this year
        return bars.filter(function(b) { return b.year == d && b.index != 4; }); //do not include realisatie
      },
      function(d) { return d.index })
    .enter()
    .append("rect")
    .attr("class", "begrotingBar")
    .attr("x", function(d) {
      return xScaleLocal(d.index);
    })
    .attr("y", function(d) {
      //if there is a value, show at least a bar of size 2
      return yScale(d.value);
    })
    .attr("width", xScaleLocal.bandwidth())
    .attr("height", function(d) {
      return areaHeight - yScale(d.value);
    });

  //add letters under each bar ('o','v','1','2','R')
  var positionLabels = ['o','v','1','2','R']; //positionLabels do not get translated
  yearCharts.selectAll(".barLabel")
    .data([0,1,2,3]) //indices of bars
    .enter()
    .append("text")
    .attr("class", "barLabel")
    .attr("x", function(d) {
      return xScaleLocal(d) + xScaleLocal.bandwidth()/2; //center on bar
    })
    .attr("y", areaHeight + 10)
    .text(function(d,i) { return positionLabels[i] });

  // add line for realisation
  yearCharts.selectAll(".realisatieLine")
    .data(function(d) {
      //find realisation bar for this year, if any
      var rBars = bars.filter(function(b) { return b.year == d && b.index == 4 });
      return rBars.length > 0 ? rBars.splice(0,1) : [];
    })
    .enter()
    .append("line")
    .attr("class", "realisatieLine")
    .attr("x1", 0)
    .attr("y1", function(d) {
      //if there is a value, show at least a bar of size 2
      return yScale(d.value);
    })
    .attr("x2", xScale.bandwidth())
    .attr("y2", function(d) {
      var yValue;
      //if there is a value, show at least a bar of size 2
      return yScale(d.value);
    });


  // add 'R' symbol for realisation line
  yearCharts.selectAll(".realisatieLabel")
    .data(function(d) {
      //find realisation bar for this year, if any. Only for first year
      var rBars = bars.filter(function(b) { return b.year == d && b.index == 4 && b.year == years[0]});
      return rBars.length > 0 ? rBars.splice(0,1) : [];
    })
    .enter()
    .append("text")
    .attr("class", "realisatieLabel")
    .attr("x", 0)
    .attr("y", function(d) {
      //if there is a value, show at least a bar of size 2
      return yScale(d.value) - 4;
    })
    .text(labels["realisatieLabel"][LANGUAGE]);


  var axis = graph.append("g")
    .attr("class","axis")
    .call(d3.axisLeft(yScale)
      .ticks(5)
      .tickFormat(function(d) {
        //TODO use d3 formatting here, with localization
        var m = d / 1000000; //in milions
        var mString;
        if (Math.round(m) >= 1000) {
          m = Math.round(m);
          mString = String(m);
          if (mString.length > 3) {
            //add point before last three chars
            mString = mString.substring(0, mString.length-3) + "." + mString.substring(mString.length-3);
          }
        } else {
          //keep one decimal behind comma
          m = Math.round(10 * m) / 10;
          //replace point with comma
          mString = String(m);
          mString = mString.replace(".",",");
          if (mString.indexOf(",") == -1) mString += ",0";
        }
        mString += " Mln";
        return mString;
      })
    );


  axis.selectAll("line").remove();
  axis.select(".domain").remove();


}
