/**
 * Contains the objects that are used for Muut Latest Comments widget.
 * Version 1.0
 * Requires jQuery
 *
 * Copyright (c) 2014 Moot, Inc.
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */

jQuery(document).ready(function($) {

  // Once Muut is loaded...
  muut().on('load', function() {
    // Set the latest comments wrapper object.
    var widget_latest_comments_wrapper = $('#muut-widget-latest-comments-wrapper');

    // Init all of the facelink functionality (tooltips and such).
    widget_latest_comments_wrapper.facelinkinit();
  });
});
