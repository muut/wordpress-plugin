/**
 * Contains the functionality that will be used for Muut on the frontend
 * Version 1.0
 * Requires jQuery
 *
 * Copyright (c) 2014 Moot, Inc.
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */
jQuery(document).ready( function($) {

  // Handles making generated permalinks to forum categories to function properly on the Forum main page.
  if ( typeof muut_current_page_permalink == 'string' && $('body').hasClass('muut-forum-home') ) {
    $('a[href^="' + muut_current_page_permalink + '#!"]').on('click', function(e) {
      var el = $(this);
      var page = el.attr('href').slice(muut_current_page_permalink.length + 2);
      muut().load(page);
    });
  }

  // Adds the comments navigation link to the forum navigation.
  if ( $('body').hasClass('muut-forum-home') && !$('body').hasClass('muut-custom-nav') && typeof muut_show_comments_in_nav != 'undefined' && muut_show_comments_in_nav ) {
    muut().on( 'init', function() {
      $(".m-forums").append('<p><a href="#!/' + muut_comments_base_domain + '" title="Comments">Comments</a></p>');
    });
  }
});