var YEAR_OLD = 2017;
var YEAR_NEW = 2018;

var svg,
    chapterLayer, //svg group for drawing the chapters and articles
    connectionLayer; //and for drawing the connections

var showAll = true; //show all articles and chapters, no filtering
var showNameChanges = false; //show articles and chapters that have changed name
var showNew = false; //show new articles and chapters
var showDeletions = false; //show deleted articles and chapters
var showBudgetChanges = false; //show articles and chapters with reallocated budget

var selectedChapter = null; //clicked chapter name, highlight connections
var selectedArticle = null; //clicked article name, highlight connections


//initialize svg

svg = d3.select("#r23_graph")
  .append("svg")
  .attr("width", jQuery("#r23_graph").innerWidth())
  .on("click", function() {
    selectedChapter = null;
    selectedArticle = null;
    draw();
  });
  //height is set in draw() after every user interaction.

connectionLayer = svg.append("g").attr("id","connections");
chapterLayer = svg.append("g").attr("id","chaptersAndArticles");

function onFilterChange(clickedFilter) {
  //all filters to false
  showAll = false;
  showNameChanges = false; //show articles and chapters that have changed name
  showNew = false; //show new articles and chapters
  showDeletions = false; //show deleted articles and chapters
  showBudgetChanges = false;

  switch(clickedFilter) {
    case 'all':
      showAll = true;
      foldAll();
      break;
    case 'name':
      showNameChanges = true;
      unfoldAll();
      break;
    case 'new':
      showNew = true;
      unfoldAll();
      break;
    case 'deleted':
      showDeletions = true;
      unfoldAll();
      break;
    case 'budget':
      showBudgetChanges = true;
      unfoldAll();
      break;
  }
  selectedChapter = null; //deselect anything
  updateFades();
  updateSelection();
  draw();
}

function foldAll() {
  chapterGroups.forEach(function(cGroup) { cGroup.folded = true; });
}

function unfoldAll() {
  chapterGroups.forEach(function(cGroup) { cGroup.folded = false; });
}

jQuery('#showArticlesButton').click(function() {
  unfoldAll();
  draw();
});

jQuery('#hideArticlesButton').click(function() {
  foldAll();
  draw();
});

//read articles and chapters, and changes from yearOld to yearNew (stored in connections).
readData(YEAR_OLD, YEAR_NEW, function() {
  //set all chapterGroups to folded = true (so articles are not visible initially)
  chapterGroups.forEach(function(c) { c.folded = true });
  updateSelection();
  draw();
});

//update 'show' attribute on chapters, articles, chapterGroups and articleGroups
//depending on filters (e.g. showChangesOnly)
//chapterGroup and articleGroup have 'status' attribute with
//value nameChanged, isNew, isDeleted, budgetChanged (when budget is transferred from or to the article)
function updateSelection() {
  articles.forEach(function(a) {
    a.show = false;
    if (showAll) a.show = true;
    else if (showNameChanges && a.nameChanged) a.show = true;
    else if (showNew && a.isNew) a.show = true;
    else if (showDeletions && a.isDeleted) a.show = true;
    else if (showBudgetChanges && a.budgetChanged) a.show = true;
  });
  chapters.forEach(function(c) {
    c.show = false;
    //if chapter has article that should be shown, show chapter
    if (c.articles.filter(function(a) { return a.show }).length > 0) {
      c.show = true;
    } else {
      //else chapter should be shown if its status reflects the selected filter
      if (showAll) c.show = true;
      else if (showNameChanges && c.nameChanged) c.show = true;
      else if (showNew && c.isNew) c.show = true;
      else if (showDeletions && c.isDeleted) c.show = true;
    }
  });

  //if a chapterGroup has a chapter that should be shown, show the chapterGroup
  chapterGroups.forEach(function(cGroup) {
    //show chapterGroup if one of its chapters should be shown
    cGroup.show = cGroup.chapters.filter(function(c) { return c.show }).length > 0;
    //if an articleGroup has an article that should be shown, show the articleGroup
    cGroup.articleGroups.forEach(function(aGroup) {
      aGroup.show = aGroup.articles.filter(function(a) { return a.show }).length > 0;
    });
  })
}
