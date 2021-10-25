var BEGROTINGSSTATEN_FILE = "/modules/custom/rijksfinancien_visuals/assets/rutteii_vs_rutte_iii/data/begrotingsstaten.csv";
var WIJZIGINGEN_FILE = "/modules/custom/rijksfinancien_visuals/assets/rutteii_vs_rutte_iii/data/wijzigingen.csv";
var COLORS_FILE = "/modules/custom/rijksfinancien_visuals/assets/rutteii_vs_rutte_iii/data/colors.csv";

var chapters; //chapter-year combinations
var articles; //article-year combinations
var chapterGroups; //chapters that are the same, through the years
var articleGroups; //articles that are the same, through the years
var articleConnections; //from old to new article
var chapterConnections; //from old to new chapter
var colors;

//reads old and new version of article list and chapter list
//and links each old article to a new article
function readData(yearOld, yearNew, callbackFunction) {

  readBegrotingsStaten(BEGROTINGSSTATEN_FILE, function(begrotingsStaten) {
    //from begrotingsStaten, get chapters and articles, format is:
    // jaar: +row.Begrotingsjaar,
    // begroting: row.Begrotingsstaat,
    // artikelNr: row.Onderdeel,
    // artikel: row.Omschrijving,
    // ontwerp: parseAmount(row.ontwerpbegroting),
    // vastgesteld: parseAmount(row["vastgestelde begroting"]),
    // eersteSup: parseAmount(row["1ste suppletoire begroting"]),
    // tweedeSup: parseAmount(row["2de suppletoire begroting"]),
    // realisatie: parseAmount(row.Realisatie)
    chapters = [];
    articles = [];

    begrotingsStaten.forEach(function(b) {
      if (b.jaar == yearOld || b.jaar == yearNew ) { //only entries from these two years
        //check if there is an entry for this chapter-year combination
        var foundChapters = chapters.filter(function(c) { return c.id == b.begroting && c.year == b.jaar });
        var chapter;
        if (foundChapters.length > 0) {
          chapter = foundChapters[0];
        } else {
          var label = b.begroting.substring(b.begroting.indexOf(",") + 1).trim();
          chapter = {
            id: b.begroting,
            year: b.jaar,
            label: label,
            articles: [],
            nameChanged: false,
            isDeleted: false,
            isNew: false
          };
          //find the group this chapter belongs to

          chapters.push(chapter);
        }

        //if article is 'TOTAAL' ignore it.
        if (b.artikel != 'TOTAAL') {
          //check if there is an entry for this article (chapter-year-article number is unique)
          var foundArticles = articles.filter(function(a) { return a.chapter == chapter && a.nr == b.artikelNr });
          var article;
          if (foundArticles.length > 0) {
            article = foundArticles[0];
          } else {
            article = {
              chapter: chapter,
              nr: b.artikelNr,
              name: b.artikel,
              nameChanged: false,
              isDeleted: false,
              isNew: false,
              budgetChanged: false
            };
            articles.push(article);
            chapter.articles.push(article);
          }
        }
      }
    }) // begrotingsstaten.forEach

    readWijzigingen(WIJZIGINGEN_FILE, function(connections) {
      // jaarOud: +row.was_jaar,
      // begrotingOud: row.was_begroting,
      // artikelNrOud: row.was_artikelnr,
      // jaarNieuw: +row.wordt_jaar,
      // begrotingNieuw: row.wordt_begroting,
      // artikelNrNieuw: row.wordt_artikelnr,
      // type: row.type_wijziging
      articleConnections = [];
      chapterConnections = [];

      connections.forEach(function(con) {
        //check if this is a change in article, or in chapter
        if (con.artikelNrOud == "*") {
          //this is a change in chapter. find con.begrotingOud in chapters and con.begrotingNieuw in chapters
          //and link them
          var fromChapter = chapters.filter(function(c) {
            return c.id == con.begrotingOud && c.year == con.jaarOud;
          })[0];
          var toChapter = chapters.filter(function(c) {
            return c.id == con.begrotingNieuw && c.year == con.jaarNieuw;
          })[0];
          chapterConnections.push({
            from: fromChapter,
            to: toChapter,
            type: con.type
          });
          switch (con.type) {
            case "name":
              fromChapter.nameChanged = true;
              toChapter.nameChanged = true;
              break;
          }

        } else {
          //change in article. link old article to new article
          var fromArticle = articles.filter(function(a) {
                              return a.chapter.id == con.begrotingOud &&
                                     a.chapter.year == con.jaarOud &&
                                     a.nr == con.artikelNrOud;
                            })[0];
          var toArticle = articles.filter(function(a) {
                              return a.chapter.id == con.begrotingNieuw &&
                                     a.chapter.year == con.jaarNieuw &&
                                     a.nr == con.artikelNrNieuw;
                            })[0];
          articleConnections.push({
            from: fromArticle,
            to: toArticle,
            type: con.type
          });
          switch (con.type) {
            case "name":
              fromArticle.nameChanged = true;
              toArticle.nameChanged = true;
              break;
            case "budget":
              fromArticle.budgetChanged = true;
              toArticle.budgetChanged = true;
              break;
          }
        }
      }) // connections.forEach

      readColors(COLORS_FILE, function(data) {
        colors = data;

        //all data is read ()
        sort();
        groupChapters();
        groupArticles();
        checkNewOrDeleted(); //add status to each article and chapter
        callbackFunction();
      }) // readColors
    }) // readWijzigingen
  }) // readBegrotingsStaten
}

//sort chapters, sort articles within chapters
//this makes that the chapterGroups and articleGroups are in the right
//order when created later on
function sort() {
  //sort chapters, keep order of colors file
  chapters.sort(function(c1, c2) {
    //get index in colors of both chapters c1 and c2
    var index1 = findWithAttr(colors, 'chapter', c1.id);
    var index2 = findWithAttr(colors, 'chapter', c2.id);
    if (index1 < index2) return -1;
    if (index1 > index2) return 1;
    return 0;
  })
  //sort articles within each chapter
  chapters.forEach(function(c) {
    c.articles.sort(function(a1, a2) {
      if (a2.nr == "-" || parseInt(a1.nr) < parseInt(a2.nr)) return -1;
      if (a1.nr == "-" || parseInt(a1.nr) > parseInt(a2.nr)) return 1;
      else return 0;
    })
  })
}

//put each chapter in the correct group (of similar chapters, e.g. that have the same name,
//or that are connected via a name change)
function groupChapters() {
  chapterGroups = [];
  chapters.forEach(function(c) {
    let chapterGroup = null;
    //check if this chapter is connected to another chapter, with connection type == name.
    const foundConnections = chapterConnections.filter(function(conn) {
      return conn.type == 'name' && (conn.from == c || conn.to == c);
    });

    if (foundConnections.length > 0) {
      const conn = foundConnections[0];
      const otherChapter = conn.from == c ? conn.to : conn.from;
      //find group that contains other chapter
      const foundChapterGroups = chapterGroups.filter(function(g) {
        return g.chapters.indexOf(otherChapter) != -1;
      })
      if (foundChapterGroups.length > 0) { chapterGroup = foundChapterGroups[0]; }
    };

    if (chapterGroup == null) {
      // chapter is not in connection, or chapter is in connection but other chapter is not in a group yet
      // see if there is a match in name with another chapter
      const foundChapterGroups = chapterGroups.filter(function(g) {
        return g.chapters.filter(function(groupChapter) {
          return groupChapter.id == c.id; // year can be different
        }).length > 0;
      })
      if (foundChapterGroups.length > 0) { chapterGroup = foundChapterGroups[0]; }
    }
    if (chapterGroup == null) {
      //still no group found for this chapter: create a new one
      chapterGroup = {
        chapters: []
      };
      chapterGroups.push(chapterGroup);
    }
    chapterGroup.chapters.push(c);
    c.chapterGroup = chapterGroup;
  })

}

//put each article in the correct group (of similar articles, e.g. that have the same name,
//or that are connected via a name change)
function groupArticles() {
  //cycle through chapterGroups
  //within each group, collect articles and group them according to name connection or same number
  chapterGroups.forEach(function(chapterGroup) {

    let articlesInThisGroup = [];
    chapterGroup.chapters.forEach(function(c) {
      articlesInThisGroup = articlesInThisGroup.concat(c.articles);
    });
    articlesInThisGroup.sort(function(a1, a2) {
      if (a2.nr == "-" || parseInt(a1.nr) < parseInt(a2.nr)) return -1;
      if (a1.nr == "-" || parseInt(a1.nr) > parseInt(a2.nr)) return 1;
      else return 0;
    })

    //group this articles
    articleGroups = []; //array of articleGroup objects

    articlesInThisGroup.forEach(function(a) {
      let articleGroup = null; //group for current article
      //check if this article is connected to another article, with connection type == name.
      //this other article is assumed to be in the same chapter group!
      const foundConnections = articleConnections.filter(function(conn) {
        return conn.type == 'name' && (conn.from == a || conn.to == a);
      });

      if (foundConnections.length > 0) {
        const conn = foundConnections[0];
        const otherArticle = conn.from == a ? conn.to : conn.from;

        //find group that contains other article
        const foundArticleGroups = articleGroups.filter(function(g) {
          return g.articles.indexOf(otherArticle) != -1;
        })
        if (foundArticleGroups.length > 0) {
          articleGroup = foundArticleGroups[0];
        }
      };

      if (articleGroup == null) {
        // article is not in connection, or article is in connection but other article is not in a group yet
        // see if there is a match in name with another article
        const foundArticleGroups = articleGroups.filter(function(g) {
          return g.articles.filter(function(groupArticle) {
            return groupArticle.nr == a.nr; // year can be different
          }).length > 0;
        })
        if (foundArticleGroups.length > 0) {
          articleGroup = foundArticleGroups[0];
        }
      }
      if (articleGroup == null) {
        //still no group found for this chapter: create a new one
        articleGroup = {
                        articles: [],
                        chapterGroup: chapterGroup
                       };
        articleGroups.push(articleGroup);
      }
      articleGroup.articles.push(a);
      a.articleGroup = articleGroup;
    }) // articlesInThisGroup.forEach
    chapterGroup.articleGroups = articleGroups;
  }) // chapterGroups.forEach
}


function checkNewOrDeleted() {
  //checks if article and chapter is new or deleted and adapt status accordingly
  //other statuses have been added when processing connections
  //the following should be adapter in case of more than 2 years
  chapterGroups.forEach(function(cGroup) {
    cGroup.chapters.forEach(function(c) {
      c.isDeleted = (c.year == YEAR_OLD && cGroup.chapters.length == 1);
      c.isNew = (c.year == YEAR_NEW && cGroup.chapters.length == 1);
    })
    cGroup.articleGroups.forEach(function(aGroup) {
      aGroup.articles.forEach(function(a) {
        a.isDeleted = (a.chapter.year == YEAR_OLD && aGroup.articles.length == 1);
        a.isNew = (a.chapter.year == YEAR_NEW && aGroup.articles.length == 1);
      })
    })
  })
}


//from https://stackoverflow.com/questions/7176908/how-to-get-index-of-object-by-its-property-in-javascript
function findWithAttr(array, attr, value) {
    for(var i = 0; i < array.length; i += 1) {
        if(array[i][attr] === value) {
            return i;
        }
    }
    return -1;
}

//Begrotingsjaar;
//eenheid;
//Uitgaven (U) Verplichtingen (V) Ontvangsten (O);
//Begrotingsstaat;
//Onderdeel;
//Omschrijving;
//ontwerpbegroting;
//vastgestelde begroting;
//1ste suppletoire begroting;
//2de suppletoire begroting;
//Realisatie;
//Url

function readBegrotingsStaten(fileName, callbackFunction) {
  var ssv = d3.dsvFormat(";"); // ssv is "Semicolon Separated Values" parser
  d3.text(fileName, function(rawData) {
      var data = ssv.parse(rawData); //parse raw data
      var begrotingsStaten = [];
      data.forEach(function(row) {
        //only keep 'uitgaven'
        //filter out 'Rijk'
        if (row["Uitgaven (U) Verplichtingen (V) Ontvangsten (O)"] == "U" &&
            row.Begrotingsstaat != "Rijk") {

          begrotingsStaten.push({
            jaar: +row.Begrotingsjaar,
            begroting: row.Begrotingsstaat,
            artikelNr: row.Onderdeel,
            artikel: row.Omschrijving,
            ontwerp: parseAmount(row.ontwerpbegroting),
            vastgesteld: parseAmount(row["vastgestelde begroting"]),
            eersteSup: parseAmount(row["1ste suppletoire begroting"]),
            tweedeSup: parseAmount(row["2de suppletoire begroting"]),
            realisatie: parseAmount(row.Realisatie)
          });

        }
      });
      callbackFunction(begrotingsStaten);
  });
}


function readWijzigingen(fileName, callbackFunction) {
  var ssv = d3.dsvFormat(";"); // ssv is "Semicolon Separated Values" parser
  d3.text(fileName, function(rawData) {
      var data = ssv.parse(rawData); //parse raw data
      var wijzigingen = [];
      data.forEach(function(row) {
        wijzigingen.push({
          jaarOud: +row.was_jaar,
          begrotingOud: row.was_begroting,
          artikelNrOud: row.was_artikelnr,
          jaarNieuw: +row.wordt_jaar,
          begrotingNieuw: row.wordt_begroting,
          artikelNrNieuw: row.wordt_artikelnr,
          type: row.type_wijziging
        });

      });
      callbackFunction(wijzigingen);
  });
}

function readColors(fileName, callbackFunction) {
  var ssv = d3.dsvFormat(";"); // ssv is "Semicolon Separated Values" parser
  d3.text(fileName, function(rawData) {
      var data = ssv.parse(rawData); //parse raw data
      callbackFunction(data);
  })
}


//remove dots from amount string, and convert to Numeric
//amounts can be floating point
function parseAmount(amount) {
  return parseFloat(amount.replace(/\./g, "").trim());
}
