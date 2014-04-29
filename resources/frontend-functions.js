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
  if ( typeof muut_current_page_permalink == 'string' && $('body').hasClass('muut-forum-home') ) {
    $('a[href^="' + muut_current_page_permalink + '#!"]').on('click', function(e) {
      var el = $(this);
      var page = el.attr('href').slice(muut_current_page_permalink.length + 2);
      muut().load(page);
    });
  }
});