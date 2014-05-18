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
  var muut_frontend_strings = muut_frontend_functions_localized;

  // Adds the comments navigation link to the forum navigation.
  var body = $('body');
  if ( body.hasClass('muut-forum-home') && !body.hasClass('muut-custom-nav') && typeof muut_show_comments_in_nav != 'undefined' && muut_show_comments_in_nav ) {
    // Make sure the title of the comments page is "Comments".
    muut().on( 'load', function(page) {
      var comments_link_class = "unlisted ";
      if (typeof( muut_comments_base_domain ) == 'string' && page.relativePath == '/' + muut_comments_base_domain) {
        page.title = "Comments";
        var comments_link_class = "m-selected";
      }
      if ($('#muut_site_comments_nav').length == 0) {
        $(".m-forums").append('<p><a id="muut_site_comments_nav" href="#!/' + muut_comments_base_domain + '" title="' + muut_frontend_strings.comments + '" data-channel="' + muut_frontend_strings.comments + '"  class="' + comments_link_class + '">' + muut_frontend_strings.comments + '</a></p>');
      }
    });
  }
});