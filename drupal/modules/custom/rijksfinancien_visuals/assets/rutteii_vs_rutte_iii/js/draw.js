
var CHAPTER_SIZE = {
                      height: 20, //height of chapter bar when folded
                      width: 20, //width of chapter bar when folded
                      spacing: 10, //nr pixels between each chapter
                      labelWidth: 400, //max width of chapter label
                      labelMargin: 5, //space between label and rect
                      cornerRadius: 3, //'roundness' of corners
                      iconSize: 14
                   };
var ARTICLE_SIZE = {
                      height: 20, //space for article dot and label
                      width: 120,
                      spacing: 5, //nr pixels between each article
                      dotRadius: 2, //radius of dot
                      dotRadiusLarge: 5, //radius of large dot
                      starRadius: 8, //radius of star
                      crossSize: 4, //size of cross
                      labelMargin: 5, //space between dot and label
                      iconSize: 12
                   };

var svgWidth;

function draw() {
  svgWidth = parseInt(svg.attr("width"));

  //calculate vertical position of each chapter
  calculateVerticalPositions();
  updateFades();

  //draw chapters, only the selected ones
  //selection is updated in index.js, whenever user clicks a button
  //each chapter and article gets a value for 'show', indicating whether or
  //not the item is part of the current selection
  var chaptersToShow = chapters.filter(function(c) { return c.show });

  var newChapters = chapterLayer.selectAll(".chapter")
    .data(chaptersToShow, function(d) { return d.id + d.year })
    .enter()
    .append("g")
    .attr("class", "chapter")
    .attr("transform", function(d) {
      //initial position
      var x = d.year == YEAR_OLD ? CHAPTER_SIZE.labelWidth : svgWidth - CHAPTER_SIZE.labelWidth;
      //return "translate(" + x + "," + 0 + ")";
      return "translate(" + x + "," + d.chapterGroup.yPos + ")";
    });

  //append label to each chapter group
  newChapters.append("text")
    .attr("class", "chapterLabel")
    .style("text-anchor", function(d) {
      return d.year == YEAR_OLD ? "end" : "start";
    })
    .attr("x", function(d) {
      return d.year == YEAR_OLD ? -CHAPTER_SIZE.labelMargin : CHAPTER_SIZE.labelMargin
    })
    .attr("y", 16)
    .text(function(d) {
      var label = d.id;
      return label;
    })
    .on('click', function(d) {
      d3.event.stopPropagation(); //to prevent click event listener of svg to fire
      //toggle chapter selection
      selectedChapter = selectedChapter == d ? null : d;
      draw();
    })

  //append rectangles to each chapter group
  newChapters.append("rect")
    .attr("class", "chapterRect")
    .style("fill", function(d) { return getColor(d) })
    .attr("rx", CHAPTER_SIZE.cornerRadius)
    .attr("ry", CHAPTER_SIZE.cornerRadius)
    .on("click", function(d) {
      d3.event.stopPropagation(); //to prevent click event listener of svg to fire
      d.chapterGroup.folded = !d.chapterGroup.folded;
      draw();
    });

  //append symbol to articles (dot, cross, star). This symbol has its own group.
  newChapters
    .append("g")
    .attr("transform", function(d) {
      //translate such that group 0,0 is midpoint
      var x = d.year == YEAR_OLD ? CHAPTER_SIZE.width/2 : -CHAPTER_SIZE.width/2;
      var y = CHAPTER_SIZE.height/2;
      return "translate(" + x + "," + y + ")";
    })
    .attr("class", "symbol")
    .each(function(d) {
        addSymbol(d3.select(this), d, true); //true: isChapter
    });

  //remove chapters that are not selected
  chapterLayer.selectAll(".chapter")
    .data(chaptersToShow, function(d) { return d.id + d.year })
    .exit()
    .remove();

  //update opacity and position of chapters
  chapterLayer.selectAll(".chapter")
    .style("opacity", function(d) {
      return d.fade ? 0.2 : 1.0;
    })
    .transition()
    .attr("transform", function(d) {
      var x = d.year == YEAR_OLD ? CHAPTER_SIZE.labelWidth : svgWidth - CHAPTER_SIZE.labelWidth;
      return "translate(" + x + "," + d.chapterGroup.yPos + ")";
    });

  //update height and width of chapter rectangles (in case chapters are folded or unfolded)
  //update position of chapter rectangles (only effects chapters on right)
  chapterLayer.selectAll(".chapterRect")
    .transition()
    .attr("height", function(d) { return getChapterGroupHeight(d.chapterGroup) })
    .attr("width", CHAPTER_SIZE.width)
    .attr("x", function(d) {
      return d.year == YEAR_OLD ?  0 : -CHAPTER_SIZE.width;
    });

  //append article to each chapter
  var newArticles = chapterLayer.selectAll(".chapter").selectAll(".article")
    .data(function(d) {
       //get all selected articles for chapter d,
      return d.articles.filter(function(a) {
        return a.show;
      })
    }, function(d) { return d.chapter.id + "_" + d.chapter.year + "_" + d.nr; })
    .enter()
    .append("g") //g is a group within the chapter group
    .attr("class", "article");

  //append symbol to articles (dot, cross, star). This symbol has its own group.
  newArticles
    .append("g")
    .attr("transform", function(d) {
      //translate such that group 0,0 is midpoint
      var x = d.chapter.year == YEAR_OLD ? CHAPTER_SIZE.width/2 : -CHAPTER_SIZE.width/2;
      var y = ARTICLE_SIZE.height/2;
      return "translate(" + x + "," + y + ")";
    })
    .attr("class", "symbol")
    .each(function(d) {
        addSymbol(d3.select(this), d, false); //false: isChapter
    });


  //append label to articles
  newArticles
    .append("text")
    .attr("class", "articleLabel")
    .style("text-anchor", function(d) {
      return d.chapter.year == YEAR_OLD ? "end" : "start";
    })
    .attr("x", function(d) {
      return d.chapter.year == YEAR_OLD ? -CHAPTER_SIZE.labelMargin : CHAPTER_SIZE.labelMargin
    })
    .attr("y", ARTICLE_SIZE.height/2)
    .attr("dominant-baseline", "middle")
    .text(function(d) {
      var label = d.nr + " " + d.name;
      return label;
    })
    .on('click', function(d) {
      d3.event.stopPropagation(); //to prevent click event listener of svg to fire
      //toggle article selection
      selectedArticle = selectedArticle == d ? null : d;
      draw();
    })

  //remove articles should not be shown
  chapterLayer.selectAll(".article")
    .data(function() {
      //get all selected articles in the old year
      return articles.filter(function(a) {
        return a.show;
      })
    }, function(d) { return d.chapter.id + "_" + d.chapter.year + "_" + d.nr; })
    .exit()
    .remove();


  //update opacity and position of articles. Relative position may change because of filtering
  chapterLayer.selectAll(".article")
    .style("opacity", function(d) {
      return d.fade ? 0.2 : 1.0;
    })
    .attr("transform", function(d) {
      return "translate(0," + d.articleGroup.yPos + ")";
    });

  //update visibility of articles
  chapterLayer.selectAll(".article")
    .style("display", function(d) {
        return d.articleGroup.chapterGroup.folded ? "none" : "inline";
    });

  drawAndUpdateConnections();

  //update height of svg. Use vertical position and height of last chapter to
  //determine the space needed.
  var svgHeight = 0;
  if (chaptersToShow.length > 0) {
    var lastChapter = chaptersToShow[chaptersToShow.length-1];
    svgHeight = lastChapter.chapterGroup.yPos + getChapterGroupHeight(lastChapter.chapterGroup)

  }
  svg.attr("height", svgHeight);
}

//calculates y coordinate of each chapterGroup and article with show == true
function calculateVerticalPositions() {

  var chapterGroupsToShow = chapterGroups.filter(function(cGroup) { return cGroup.show });

  var y = 0; //position of next chapter
  chapterGroupsToShow.forEach(function(cGroup) {
    cGroup.yPos = y;
    y += getChapterGroupHeight(cGroup) + CHAPTER_SIZE.spacing; //increase y to position next chapterGroup

    //position articleGroups in chapterGroup, relative position wrt chapter
    var articleGroupsToShow = cGroup.articleGroups.filter(function(aGroup) { return aGroup.show });
    for (var i=0; i<articleGroupsToShow.length; i++) {
      var aGroup = articleGroupsToShow[i];
      if (cGroup.folded) aGroup.yPos = 0;
      else aGroup.yPos = CHAPTER_SIZE.height + ARTICLE_SIZE.spacing + i * (ARTICLE_SIZE.height + ARTICLE_SIZE.spacing);
    }
  })
}

function getChapterGroupHeight(cGroup) {
  if (cGroup.folded) return CHAPTER_SIZE.height;
  //get the articleGroups in this chapterGroup that are shown
  var articleGroupsToShow = cGroup.articleGroups.filter(function(g) { return g.show });
  var spaceForArticles = articleGroupsToShow.length * (ARTICLE_SIZE.height + ARTICLE_SIZE.spacing);
  return CHAPTER_SIZE.height + ARTICLE_SIZE.spacing + spaceForArticles;
}

function getColor(chapter) {
  return colors.filter(function(c) { return c.chapter == chapter.id })[0].color;
}

//add symbol to article or chapter (dot, star, or cross)
//added, deleted
function addSymbol(g, d, isChapter) {
  var symbolColor = "#ffffff";
  //midpoint of symbol should be 0,0
  const iconSize = isChapter ? CHAPTER_SIZE.iconSize : ARTICLE_SIZE.iconSize;
  if (d.isNew) {
    g.append("svg:image")
      .attr('x', -iconSize/2)
      .attr('y', -iconSize/2)
      .attr('width', iconSize)
      .attr('height', iconSize)
      .attr("xlink:href", isChapter ? "/modules/custom/rijksfinancien_visuals/assets/rutteii_vs_rutte_iii/img/isNewChapter.svg" : "/modules/custom/rijksfinancien_visuals/assets/rutteii_vs_rutte_iii/img/isNewArticle.svg");
  } else if (d.isDeleted) {
    g.append("svg:image")
      .attr('x', -iconSize/2)
      .attr('y', -iconSize/2)
      .attr('width', iconSize)
      .attr('height', iconSize)
      .attr("xlink:href", isChapter ? "/modules/custom/rijksfinancien_visuals/assets/rutteii_vs_rutte_iii/img/isDeletedChapter.svg" : "/modules/custom/rijksfinancien_visuals/assets/rutteii_vs_rutte_iii/img/isDeletedArticle.svg");
  } else if (d.nameChanged) {
    g.append("svg:image")
      .attr('x', -iconSize/2)
      .attr('y', -iconSize/2)
      .attr('width', iconSize)
      .attr('height', iconSize)
      .attr("xlink:href", isChapter ? "/modules/custom/rijksfinancien_visuals/assets/rutteii_vs_rutte_iii/img/nameChangedChapter.svg" : "/modules/custom/rijksfinancien_visuals/assets/rutteii_vs_rutte_iii/img/nameChangedArticle.svg");
  } else if (!isChapter) { //add dot to articles when there is no change
    g.append("circle")
    /*
     .attr("r", function(d) {
       return d.budgetChanged ? ARTICLE_SIZE.dotRadiusLarge : ARTICLE_SIZE.dotRadius;
     })
     */
     .attr("r", ARTICLE_SIZE.dotRadius)
     .style("fill", symbolColor);
  }



}

//draws lines between old and new articles
function drawAndUpdateConnections() {
  //only show articleConnection if both articles are shown
  articleConnectionsToShow = articleConnections.filter(function(aCon) {
    return aCon.from.show && aCon.to.show && aCon.type == "budget";
  });
  //add lines from articles that have changed to the new corresponding article
  connectionLayer.selectAll(".articleConnection")
    .data(articleConnectionsToShow, function(d) {
      var uniqueID = d.from.chapter.id + "_" + d.from.chapter.year + "_" + d.from.nr + "_"
                   + d.to.chapter.id + "_" + d.to.chapter.year + "_" + d.to.nr;
      return uniqueID;
    })
    .enter()
    .append("path")
    .attr("class", "articleConnection")
    .style("fill", "none")
    .style("stroke-linecap", "round");

  //update position and color of lines
  connectionLayer.selectAll(".articleConnection")
    .style("stroke", function(d) {
      return d.fade ? "#aaaaaa" : getColor(d.from.chapter);
    })
    .style("stroke-width", function(d) {
      return 2;
    })
    .style("opacity", function(d) {
      return d.fade ? 0 : 0.6
    })
    .transition()
    .attr("d", function(d) {
      //start point
      var x1 = CHAPTER_SIZE.labelWidth + CHAPTER_SIZE.width;
      var y1 = d.from.chapter.chapterGroup.yPos + d.from.articleGroup.yPos + ARTICLE_SIZE.height/2;
      //end point
      var x2 = svgWidth - CHAPTER_SIZE.labelWidth - CHAPTER_SIZE.width;
      var y2 = d.to.chapter.chapterGroup.yPos + d.to.articleGroup.yPos + ARTICLE_SIZE.height/2;
      //first control point
      var cx1 = (x1 + x2)/2;
      var cy1 = y1;
      //second control point
      var cx2 = (x1 + x2)/2;
      var cy2 = y2
      return "M" + x1 + "," + y1 + " C" + cx1 + "," + cy1 + " " + cx2 + "," + cy2 + " " + x2 + "," + y2;
    });


  connectionLayer.selectAll(".articleConnection")
    .data(articleConnectionsToShow, function(d) {
      var uniqueID = d.from.chapter.id + "_" + d.from.chapter.year + "_" + d.from.nr + "_"
                   + d.to.chapter.id + "_" + d.to.chapter.year + "_" + d.to.nr;
      return uniqueID;
    })
    .exit()
    .remove();
}

//highlight chapter that is clicked on (to highlight connections)
//and visuals that are connected to the clicked visual
function updateFades() {
  if (selectedChapter != null) {
    //fade everything
    chapters.forEach(function(c) {
      c.fade = true;
    })
    articleConnections.forEach(function(aCon) {
      aCon.fade = true;
    })
    articles.forEach(function(a) {
      a.fade = true;
    })


    selectedChapter.fade = false;
    //highlight connections that connect one of the selected chapter's articles.
    var aConsToBeHighlighted = articleConnections.filter(function(aCon) {
      return aCon.type == "budget" &&
            (selectedChapter.articles.indexOf(aCon.from) != -1 ||
             selectedChapter.articles.indexOf(aCon.to) != -1);
    })
    aConsToBeHighlighted.forEach(function(aCon) {
      aCon.fade = false;
      //highlight chapters at the ends of the connection (includes selected chapter)
      aCon.from.chapter.fade = false;
      aCon.to.chapter.fade = false;
      //highlight articles at the ends of the connection (including articles in selected chapter)
      aCon.from.fade = false;
      aCon.to.fade = false;
    })
  } else {
    //unfade everything
    chapters.forEach(function(c) {
      c.fade = false;
    })
    articleConnections.forEach(function(aCon) {
      aCon.fade = false;
    })
    articles.forEach(function(a) {
      a.fade = false;
    })
  }
}
