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
  muutObj().on('load', function() {
    // Set the latest comments wrapper object.

    var widget_latest_comments_wrapper = $('#muut-widget-latest-comments-wrapper');

    var widget_latest_comments_current_list_elements = $(widget_latest_comments_wrapper).find('.muut_recentcomments');

    var widget_latest_comments_num_showing = widget_latest_comments_current_list_elements.length;

    // Init all of the facelink functionality (tooltips and such).
    widget_latest_comments_wrapper.facelinkinit();

    var muut_poll_wordpress_cache = function( timeout ) {
      setTimeout( function() {
        jQuery.ajax({
          url: muut_latest_comments_request_endpoint,
          async: false,
          cache: false,
          dataType: 'json',
          success: function(data) {
            var old_data = muut_latest_comments_json;
            muut_latest_comments_json = data;
            widget_latest_comments_wrapper.trigger('json_update', [ muut_latest_comments_json, old_data ] );
            if ( timeout >= 1000 ) {
              muut_poll_wordpress_cache( timeout );
            }
          }
        });
      }, timeout);
    };

    // When a reply event comes through the websocket.
    muutRpc.on('reply', function( path, user ) {
      // If the path shows that it is a comment on a post...
      if(path.search(muutObj().path + '/' + muut_latest_comments_path) != -1) {
        muut_poll_wordpress_cache(0);
      }
    });

    // The poll time must be greater than 1 second (1000 milliseconds).
    if ( muut_latest_comments_poll_time >= 1000 ) {
      // Poll the WP server to get the new JSON for the widget every <timeout> seconds.
      muut_poll_wordpress_cache( muut_latest_comments_poll_time );
    }

    // Listen for the json_update event so that we can compare data and act accordingly.
    widget_latest_comments_wrapper.on('json_update', function( event, new_obj, old_obj ) {
    widget_latest_comments_num_showing = widget_latest_comments_current_list_elements.length;
      muut_latest_comments_json = new_obj;
      new_obj = new_obj.latest_comments_posts;
      old_obj = old_obj.latest_comments_posts;
      var new_post_ids = [];
      for (i = 0; i < new_obj.length; i++) {
        new_post_ids.push(new_obj[i].post_id);
      }
      var num_new_items = false;
      var post_ids_to_delete = [];
      for(i = 0; i < widget_latest_comments_num_showing; i++) {
        var index_match = $.inArray(old_obj[i].post_id, new_post_ids);
        var old_string = JSON.stringify(old_obj[i]);
        var new_string = JSON.stringify(new_obj[index_match]);

        // If there is a match and it is identical to the old one, we know that the list has new items above it, but
        // it remains the top relating to the "old list".
        if(index_match >= 0 && old_string == new_string && num_new_items === false){
          num_new_items = index_match;
          // If there is a match, but it is different, then the element has had a new post. Continue to the next one to
        // see if it should be functioning as the new "top of previous list" item.
        } else if(index_match >= 0 && old_string != new_string) {
          post_ids_to_delete.push(old_obj[i].post_id);
        // If there is no post id match, that means ALL elements have been replaced by the new list.
        // Skip the rest of the loop.
        } else if (index_match == '-1' && num_new_items == 0){
          num_new_items = old_obj.length;
          break;
        }
      }
      
      // Delete the posts that are being replaced by updates to them (i.e. being moved to the top).
      for(i = 0; i < post_ids_to_delete.length; i++) {
        widget_latest_comments_wrapper.find('.muut_recentcomments[data-post-id="' + post_ids_to_delete[i] + '"]').hide(400, function(){ $(this).remove(); });
      }
      // Refresh the current list of items and get figure out how many to remove from the bottom.
      widget_latest_comments_num_showing = widget_latest_comments_num_showing - post_ids_to_delete.length;
      var difference_count_from_new_items = muut_latest_comments_num_posts - num_new_items;
      for(i = 0; i < widget_latest_comments_num_showing - difference_count_from_new_items; i++) {
        $(widget_latest_comments_current_list_elements.get(-1)).hide(400, function() { $(this).remove(); });
      }

      // Generate the HTML for the new elements and prepend it to the list.
      var new_item_html = '';
      for(i = 0; i < num_new_items; i++) {
        // Get the pretty timestamp.
        var timestamp = Date.now() - (new_obj[i].timestamp * 1000);
        var list_time = muut_time_format( timestamp );
        // Generate the HTML.
        new_item_html += muut_latest_comments_row_template.replace(/%USER_PATH%/g, new_obj[i].user.path)
          .replace(/%USER_DISPLAYNAME%/g, new_obj[i].user.displayname)
          .replace(/%USER_IMAGEURL%/g, new_obj[i].user.img)
          .replace(/%POSTID%/g, new_obj[i].post_id)
          .replace(/%TIMESTAMP%/g, timestamp)
          .replace(/%LISTTIME%/g, list_time)
          .replace(/%POST_PERMALINK%/g, new_obj[i].post_permalink)
          .replace(/%POST_TITLE%/g, new_obj[i].post_title);
      }
      // Append the HTML.
      $('#muut-recentcomments').prepend(new_item_html).facelinkinit();
    });
  });
});
