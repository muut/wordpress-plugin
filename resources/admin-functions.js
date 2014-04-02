/**
 * Contains the functionality that will be used for Muut on the admin side.
 * Version 1.0
 * Requires jQuery
 *
 * Copyright (c) 2014 Moot, Inc.
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */
jQuery(document).ready( function($) {

  // If this is a Muut forum, make sure to show the forum name field and disable the template selector.
  $('#muut_is_forum_true').click( function() {
    if ($('#muut_forum').val() == ''){
      $('#muut_forum').val($('#editable-post-name').text());
    }
    $('#muut_page_specify_forum').show();
    $('#page_template').prop('disabled', 'disabled');
    $('#page_template option').filter( function() {
        return $(this).val() == 'default';
    }).prop('selected', true);
  });
  if ($('#muut_is_forum_true').is(':checked')) {
    $('#page_template').prop('disabled', 'disabled');
  }

  // If we change it to not being a forum, make sure to hide the name field and re-enable the template selector.
  $( '#muut_is_forum_false').click( function() {
    $('#muut_page_specify_forum').hide();
    $('#page_template').prop('disabled', false);
  });
});