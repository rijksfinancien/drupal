//reads data, and creates list of years that are present in the data,
//and compiles the hoodstuk and omschrijving fields in the data
function readData(dataFile, translationsFile, callbackFunction) {
  d3.csv(dataFile,
    function(error, rows) {
      //this is data over multiple years, group data for the same artikel together in one serie
      data = []; //global variable, declared in index.html
      years = [];
      for (var i=0; i<rows.length; i++) {
        r = rows[i];
        //check if item is present (item is combination of hoofdstuk and artikel)
        var item;
        s = $.grep(data, function(o) { return o.hoofdstuk == r.hoofdstuk && o.artikel == r.artikel });
        if (s.length > 0) {
          //found
          item = s[0];
        } else {
          //not found, create new serie object
          var id = r.hoofdstuk.substring(0, r.hoofdstuk.indexOf(",")) + "_" + r.artikel;
          item = { 'id':id,
            'hoofdstuk':r.hoofdstuk,
            'label':r.label,
            'artikel':r.artikel,
            'omschrijving':r.omschrijving,
            'values':[],
            'maxValue':null //keep track of largest value, for choosing the right axis scale
          }
          data.push(item);
        }
        if (!isNaN(+r['realisatie'])) {
          var value = {'jaar':+r.jaar,
            'begrotingOntwerp':+r['begroting.ontwerp']*1000, //maal 1000 zodat getallen in euro's zijn
            'begrotingVastgesteld':+r['begroting.vastgesteld']*1000,
            'begrotingEersteSup':+r['begroting.suppl.1']*1000,
            'begrotingTweedeSup':+r['begroting.suppl.2']*1000,
            'realisatie':+r['realisatie']*1000};
          value.saldoAbs = (!isNaN(value.realisatie) && !isNaN(value.begrotingOntwerp))
            ? value.realisatie - value.begrotingOntwerp : null;
          value.saldoRel = value.saldoAbs != null && value.begrotingOntwerp != 0
            ? (value.saldoAbs / value.begrotingOntwerp) * 100 : null;

          item.values.push(value);
          //keep track of all years for which there is 'realisatie' data available
          if (years.indexOf(+r.jaar) == -1) years.push(+r.jaar);
        }
      }
      years.sort();
      //keep only the last three years
      years = years.slice(Math.max(years.length - 3, 1));

      //keep track of maximum value per item; only look at values within the last years
      data.forEach(function(item) {
        item.values.forEach(function(value) {
          if (years.indexOf(value.jaar) != -1) {
            var maxValue = Math.max(value.begrotingOntwerp,
              value.begrotingVastgesteld,
              value.begrotingEersteSup,
              value.begrotingTweedeSup,
              value.realisatie);
            if (item.maxValue == null || item.MaxValue < maxValue) item.maxValue = maxValue;
          }
        })
      })


      //sort data according to maxValue
      data.sort(function(d1, d2) {
        //some items have no values, set realisation to zero when sorting
        if (d1.maxValue < d2.maxValue) return 1;
        if (d1.maxValue > d2.maxValue) return -1;
        return 0;
      })
      translate(translationsFile, function() {
        callbackFunction();
      });
    });

}

//reads translations, and adds them to the data.

function translate(translationsFile, callbackFunction) {
  var ssv = d3.dsvFormat(";"); // ssv is "Semicolon Separated Values" parser
  d3.text(translationsFile, function(rawData) {

    var translations = ssv.parse(rawData); //parse raw data
    //cycle through all nodes
    data.forEach(function(d) {
      //take 'hoofdstuk' from data, without the part before the comma, e.g. 'XII'
      var commaIndex = d.hoofdstuk.indexOf(",");
      var chapter = commaIndex == -1 ? d.hoofdstuk.trim() : d.hoofdstuk.substring(commaIndex+1).trim();
      var matchingChapters = translations.filter(function(t) { return t.begrotingNL == chapter });
      d.hoofdstuk_en = matchingChapters.length > 0 ? matchingChapters[0].begrotingEN : "";
      if (d.artikel == '-') d.omschrijving_en = "TOTAL";
      else {
        //if artikel does not equal '-' (TOTAAL), find matching article within translations that match chapter
        //artikelnr cannot be used since it varies over the years, and does not match artikelnr in translation data
        var matchingArticles = matchingChapters.filter(function(c) { return c.artikelnr == d.artikel });
        d.omschrijving_en = matchingArticles.length > 0 ? matchingArticles[0].artikelEN : "";
      }
    });
    callbackFunction();
  });
}
