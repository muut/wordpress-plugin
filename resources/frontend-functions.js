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

  // Adds the comments navigation link to the forum navigation.
  var body = $('body');
  if ( body.hasClass('muut-forum-home') && !body.hasClass('muut-custom-nav') && typeof muut_show_comments_in_nav != 'undefined' && muut_show_comments_in_nav ) {
    muut().on( 'init', function() {
      $(".m-forums").append('<p><a href="#!/' + muut_comments_base_domain + '" title="Comments" data-channel="Comments"  class="unlisted">Comments</a></p>');
    });
  }
});