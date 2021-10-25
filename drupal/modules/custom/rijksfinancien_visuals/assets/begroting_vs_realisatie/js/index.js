var DATA_FILE = "/modules/custom/rijksfinancien_visuals/assets/begroting_vs_realisatie/data/begrotingsstaten_web.csv";
var TRANSLATION_FILE = "/modules/custom/rijksfinancien_visuals/assets/begroting_vs_realisatie/data/suppletoire_begroting.translations.csv";
var LANGUAGE = 'nl';

var BARCHART_SIZE = {
  margin: {top:30, right:0, bottom:30, left:70},
  height: 200,  //height of chart including title and axes
  width: 270, //width of chart including title and axes
  barPaddingInner: 0.1, //space between bars
  barPaddingOuter: 0.1 //space between bars and graph border
};

var DETAILED_GRAPH_SIZE = {
  margin: {top:30, right:0, bottom:30, left:80},
  height: 200,  //height of graph including title and axes
  width: 450, //width of graph including title and axes
  blockPaddingInner: 0.15, //space between year blocks
  blockPaddingOuter: 0.1, //space between year blocks and graph border
  begrotingsBarPaddingInner: 0.15, //space between begrotings bars, within detailed graph for one year
  begrotingsBarPaddingOuter: 0 //space between outer bars and graph border
};
var labels = {
  "beg-real_title": { nl: "Verschil begroting vs. realisatie", en: "Difference budget vs. actuals" },
  "beg-real_backButton": { nl: "Terug naar totalen", en: "Back to totals" },
  "articleButton": { nl: "Toon artikelen", en: "Show articles"},
  "chartTitle": { nl: "Totaal", en: "Total" },
  "chartDescription_perc": { nl: "Procentuele afwijking van ontwerpbegroting", en: "% deviation from draft budget" },
  "chartDescription_abs": { nl: "Begroting (balken) en realisatie (lijn) in miljoenen Euro's", en: "Levels of budgeted (bars) and actual (dotted line) spending" },
  "realisatieLabel": { nl: "Realisatie", en: "Actual spending" }
};
var data;
var years;

var selectedItem = null;
var itemsToShow = [];

readData(DATA_FILE,TRANSLATION_FILE, function() {
  draw();
});

function backButtonClicked() {
  selectedItem = null; //back to showing all totals
  draw();
}

function getParameterByName(name, url) {
  if (!url) url = window.location.href;
  name = name.replace(/[\[\]]/g, "\\$&");
  var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
    results = regex.exec(url);
  if (!results) return null;
  if (!results[2]) return '';
  return decodeURIComponent(results[2].replace(/\+/g, " "));
}
