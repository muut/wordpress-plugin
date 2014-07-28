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

    // If the widget exists...
    if ( widget_latest_comments_wrapper.length > 0 ) {

      var widget_latest_comments_current_list_elements = $(widget_latest_comments_wrapper).find('.muut_recentcomments');

      var widget_latest_comments_num_showing = widget_latest_comments_current_list_elements.length;

      var muut_comments_root_path = muutObj().path + '/' + muut_latest_comments_path;

      // Init all of the facelink functionality (tooltips and such).
      widget_latest_comments_wrapper.facelinkinit();

      // Get new results from the server.
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

      // Check to update the timestamps every minute.
      var update_time_displays = function() {
        setTimeout( function() {
          if ( Date.now() - last_update < 60000 ) {
            widget_latest_comments_current_list_elements = $(widget_latest_comments_current_list_elements.selector);
          }
          widget_latest_comments_current_list_elements.each(function(index, element) {
            var difference_timestamp = Date.now() - ( muut_latest_comments_json.latest_comments_posts[index].timestamp * 1000 );
            var new_display_time = muut_time_format( difference_timestamp );
            $(this).find('.muut-post-time-since').text(new_display_time);
          });
          update_time_displays();
        }, 60000);
      };
      var last_update = Date.now();
      update_time_displays();

      // Find out if a thread path is a WP Post commenting path.
      // Return the WP post id on success, or false on failure.
      var muut_is_wp_commenting_thread = function(path) {
        // The commenting base path.
        var path_post_id_re = new RegExp( muut_comments_root_path + '/([0-9]+)');
        // If the string lines up with the commenting base path.
        if(path.search(muut_comments_root_path) != -1) {
          // Do the regular expression comparison to get the WP Post id the path references.
          matches = path_post_id_re.exec(path);
          if ( matches && typeof matches[1] != 'undefined' ) {
            return parseInt(matches[1]);;
          }
        }
        return false;
      };

      // Get the array of currently locally cached WP *post ids* (in same order as they are stored).
      var muut_get_cached_post_ids = function() {
        var current_post_ids = [];
        for (i = 0; i < muut_latest_comments_json.latest_comments_posts.length; i++) {
          current_post_ids.push(muut_latest_comments_json.latest_comments_posts[i].post_id);
        }
        return current_post_ids;
      };

      // Check if a given post id is already stored locally, and if so what the index is in the locally cached array.
      // Return the index OR -1 if there is no match.
      var muut_post_id_is_cached_locally = function(post_id){
        var current_post_ids = muut_get_cached_post_ids();
        return $.inArray(post_id, current_post_ids);;
      };

      // If we are going to poll the server for new posts...
      // The poll time must be greater than 1 second (1000 milliseconds).
      if ( muut_latest_comments_poll_time >= 1000 ) {
        // Poll the WP server to get the new JSON for the widget every <timeout> seconds.
        muut_poll_wordpress_cache( muut_latest_comments_poll_time );
      } else {
        // When a reply event comes through the websocket.
        muutRpc.on('reply', function( path, reply_object ) {
          var post_id = muut_is_wp_commenting_thread(path);
          if (post_id != 'false') {
            var new_data = $.extend(true,{},muut_latest_comments_json);
            var index_match = muut_post_id_is_cached_locally(post_id);
            if ( index_match >= 0 ) {
              var post_data = new_data.latest_comments_posts[index_match];
              new_object = {
                post_id: post_id,
                post_permalink: post_data.post_permalink,
                post_title: post_data.post_title,
                timestamp: Math.floor(Date.now() / 1000).toString(),
                user: {
                  displayname: reply_object.user.displayname,
                  img: reply_object.user.img,
                  path: reply_object.user.path
                }
              };
              new_data.latest_comments_posts.splice( index_match, 1 );
              new_data.latest_comments_posts.unshift(new_object);
              widget_latest_comments_wrapper.trigger('json_update', [ new_data, muut_latest_comments_json ] );
            } else {
              setTimeout( function() {
                  muut_poll_wordpress_cache(0);
                }, 4000
              );
            }
          }
        });

        muutRpc.on('post', function( location, post_object ) {
          // If the path shows that it is a comment on a post...
          var post_id = muut_is_wp_commenting_thread(location.path);
          if (post_id != 'false') {
            var new_data = $.extend(true,{},muut_latest_comments_json);
            var index_match = muut_post_id_is_cached_locally(post_id);
            if ( index_match >= 0 ) {
              var post_data = new_data.latest_comments_posts[index_match];
              new_object = {
                post_id: post_id,
                post_permalink: post_data.post_permalink,
                post_title: post_data.post_title,
                timestamp: Math.floor(Date.now() / 1000).toString(),
                user: {
                  displayname: post_object.user.displayname,
                  img: post_object.user.img,
                  path: post_object.user.path
                }
              };
              new_data.latest_comments_posts.splice( index_match, 1 );
              new_data.latest_comments_posts.unshift(new_object);
              widget_latest_comments_wrapper.trigger('json_update', [ new_data, muut_latest_comments_json ] );
            } else {
              setTimeout( function() {
                  muut_poll_wordpress_cache(0);
                }, 4000
              );
            }
          }
        });

        muutRpc.on('send', function(event, object) {
          if ( ( event == 'reply' || event == 'createMoot' ) && typeof object[0] != 'undefined' && typeof muut_wp_post_id != 'undefined' ) {
            if(muut_is_wp_commenting_thread(object[0].path)) {
              var new_data = $.extend(true,{},muut_latest_comments_json);
              var current_post_ids = [];
              for (i = 0; i < muut_latest_comments_json.latest_comments_posts.length; i++) {
                current_post_ids.push(muut_latest_comments_json.latest_comments_posts[i].post_id);
              }
              var index_match = muut_post_id_is_cached_locally(muut_wp_post_id);
              if ( index_match >= 0 ) {
                new_data.latest_comments_posts.splice( index_match, 1 );
              } else if (new_data.latest_comments_posts.length == 10 )  {
                new_data.latest_comments_posts.splice( 9, 1 );
              }
              new_object = {
                post_id: muut_wp_post_id,
                post_permalink: muut_wp_post_permalink,
                post_title: muut_wp_post_title,
                timestamp: Math.floor(Date.now() / 1000).toString(),
                user: {
                  displayname: muutObj().user.displayname,
                  img: muutObj().user.img,
                  path: muutObj().user.path
                }
              };
              new_data.latest_comments_posts.unshift(new_object);
              widget_latest_comments_wrapper.trigger('json_update', [ new_data, muut_latest_comments_json ] );
            }
          }
        });
      }

      // Listen for the json_update event so that we can compare data and act accordingly.
      widget_latest_comments_wrapper.on('json_update', function( event, new_obj, old_obj ) {
        last_update = Date.now();
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
          widget_latest_comments_wrapper.find('.muut_recentcomments[data-post-id="' + post_ids_to_delete[i] + '"]').remove();
        }
        // Refresh the current list of items and get figure out how many to remove from the bottom.
        widget_latest_comments_num_showing = widget_latest_comments_num_showing - post_ids_to_delete.length;
        var difference_count_from_new_items = muut_latest_comments_num_posts - num_new_items;
        for(i = 0; i < widget_latest_comments_num_showing - difference_count_from_new_items; i++) {
          $(widget_latest_comments_current_list_elements.get(-1)).remove();
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
    }
  });
});
