/**
 * Contains the objects that are used for Muut Popular Posts widget.
 * Version 1.0
 * Requires jQuery
 *
 * Copyright (c) 2014 Moot, Inc.
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */

jQuery(document).ready(function($) {

  // Once Muut is loaded...
  muutObj().on('load', function() {
    var body = $('body');

    if(body.hasClass('muut-forum-home') && typeof muut_stored_channel_list != 'undefined' && typeof muut_stored_channels_nonce == 'string' ) {
      var category_object = {};
      // Assign the actual displayed categories to an object to match the one currently stored.
      var live_channels = muutObj().categories;
      for (i = 0; i < live_channels.length; i++) {
        category_object[live_channels[i].path] = live_channels[i].title;
      }

      // If the current categories and the ones in storage do NOT match, then send the current ones to be stored.
      if (JSON.stringify(category_object) != JSON.stringify(muut_stored_channel_list)) {
        $.post(ajaxurl, {
            action: 'muut_store_current_channels',
            channel_list: category_object,
            security: muut_stored_channels_nonce
          }
        );
      }
    }

    var popular_posts_widgets = $('div.muut_widget_popular_posts_wrapper');
    if ( popular_posts_widgets.length > 0 ) {

      // Show the typing circle next to a thread if it is being typed in.
      muutRpc.event('type', function(path, user) {
        var selected_elements = popular_posts_widgets.find('.muut_popular_post_item[data-muut-post-path="' + path + '"]');
        selected_elements.each( function() {
          var icon = $("<em/>").ac("typing").appendTo($(this).find('.popular-posts-post-meta'));
          setTimeout(function() { icon.remove() }, NEPER * 1000);
        });
      });
    }
  });
});
