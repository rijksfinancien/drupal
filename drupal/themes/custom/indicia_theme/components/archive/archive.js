(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.indiciaArchiveGrid = {
    attach: function (context, settings) {
     $(window).on('load resize', function () {
       const $grid = $('.archive-grid', context);
       if ($grid.length) {
         // Reset vertical positions.
         const $columns = $grid.find('.archive-block')
         $columns.css('padding-top', 0);

         if (window.matchMedia('(min-width: 1024px)').matches) {
           // Retrieve highest count of rows of all categories.
           let totalRows = 0;
           $grid.find('.archive-blocks').each(function(i, columns) {
             const $columns = $(columns);
             const count = $columns.find('.archive-block').length
             if (count > totalRows) {
               totalRows = count;
             }
           });

           // Equalize vertical position of each subcategory.
           for (let rowCount = 2; rowCount <= totalRows; rowCount++) {
             const $columns = $grid.find(`.archive-blocks .archive-block:nth-child(${rowCount})`);

             // Retrieve max offset.
             let maxOffset = 0;
             $columns.each(function (i, column) {
               const offsetTop = $(column).offset().top;
               if (offsetTop > maxOffset) {
                 maxOffset = offsetTop;
               }
             });

             // Set vertical position.
             $columns.each(function (i, column) {
               const $column = $(column);
               const offsetTop = $column.offset().top;
               if (offsetTop < maxOffset) {
                 $column.css('padding-top', `${maxOffset - offsetTop}px`);
               }
             });
           }
         }
       }
     });
    }
  };

}(jQuery, Drupal));
