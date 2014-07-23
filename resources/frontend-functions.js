/**
 * Contains the functionality that will be used for Muut on the frontend
 * Version 1.0
 * Requires jQuery
 *
 * Copyright (c) 2014 Moot, Inc.
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */
var NEPER = 2.718;
jQuery(document).ready( function($) {
  var __muut_frontend_strings = muut_frontend_functions_localized;

  // Adds the comments navigation link to the forum navigation.
  var body = $('body');
  if ( body.hasClass('muut-forum-home') && !body.hasClass('muut-custom-nav') && typeof muut_show_comments_in_nav != 'undefined' && muut_show_comments_in_nav ) {
    // Make sure the title of the comments page is "Comments".
    muutObj().on( 'load', function(page) {
      var comments_link_class = "unlisted ";
      if (typeof( muut_comments_base_domain ) == 'string' && page.relativePath == '/' + muut_comments_base_domain) {
        page.title = "Comments";
        var comments_link_class = "m-selected";
      }
      if ($('#muut_site_comments_nav').length == 0) {
        $(".m-forums").append('<p><a id="muut_site_comments_nav" href="#!/' + muut_comments_base_domain + '" title="' + __muut_frontend_strings.comments + '" data-channel="' + __muut_frontend_strings.comments + '"  class="' + comments_link_class + '">' + __muut_frontend_strings.comments + '</a></p>');
      }
    });
  }

  $.fn.extend({
    // The function that is used to initialize all m-facelink anchors below the jQuery element collection calling the function.
    facelinkinit: function() {
      var online_usernames = Array();
      muutObj().online.forEach(function(user) {
        online_usernames.push(user.username);
      });
      if ($(this).hasClass('m-facelink')) {
        var facelinks = $(this);
      } else {
        var facelinks = $(this).find('.m-facelink');
      }
      $.each(facelinks, function() {
        // If the facelinks are not marked as already having been initialized...
        if ( !$(this).hasClass('m-facelink-inited') ) {
          // Add the username tooltip.
          $(this).tooltip2({prefix: 'm-', delayIn: 0, delayOut: 0}).appendTo($(this));
          if($(this).hasClass('m-is-admin')) {
            $(this).find(".m-tooltip").append("<em> (" + __muut_frontend_strings.admin + ")</em>");
          }
          // Load the user page if the portrait is clicked.
          $(this).on('click', function(e) {
            var el = $(this);
            var page = el.data('href').substr(2);
            muutObj().load(page);
          });
          var current_user_name = $(this).data('href').substr(4);
          // This class is required for tooltips to work--something on the Muut end.
          $(this).addClass('m-online')
          if($.inArray(current_user_name, online_usernames) >= 0) {
            $(this).addClass('m-user-online_' + current_user_name);
          } else {
            // This hides the "online" circle, which has been added by the required m-online.
            // Ugly, I know.
            $(this).addClass('m-wp-hideafter');
          }
          $(this).addClass('m-facelink-inited');
        }
      });
    }
  });
});

// Function that contains the template for avatars.
var get_user_avatar_html = function(user) {
  var is_admin_class = '';
  if(user.is_admin) {
    is_admin_class = 'm-is-admin ';
  }

  // Construct the actual username without the '@'.
  if(user.path.substr(0,1) == '@') {
    var username = user.path.substr(1);
  }

  var username_for_class = username.replace(' ', '_');
  var online_user_href_markup = '';
  if ( typeof muut_forum_page_permalink == 'string' ) {
    online_user_href_markup = 'href="' + muut_forum_page_permalink + '#!/' + user.path + '"';
  }
  // Return the HTML for the face.
  var html = '<a class="m-facelink ' + is_admin_class + 'm-online m-user-online_' + username_for_class +'" title="' + user.displayname + '" ' + online_user_href_markup + ' data-href="#!/' + user.path + '"><img class="m-face" src="' + user.img + '"></a>';
  return html;
};

// Function that tidies the usernames of specific bad (but important) characters.
var tidy_muut_username = function(username) {
  if (typeof(username) == 'string'){
    username = (username.replace(':', '\\:')).replace(' ', '_');
  }
  return username;
};

// Sets up a polling request to the WordPress server.
// Should only be used if webhooks are not being used to initiate a request.
var muut_poll_wordpress_cache = function(obj, endpoint_uri, timeout, event_obj) {
  setTimeout( function() {
    muut_request_json(obj, endpoint_uri, event_obj);
    muut_poll_wordpress_cache(obj, endpoint_uri, timeout, event_obj);
  }, timeout);
};

// Sends request to server to a specific JSON endpoint.
var muut_request_json = function(obj, endpoint_uri, event_obj) {
  jQuery.ajax({
    url: endpoint_uri,
    async: false,
    dataType: 'json',
    success: function(data) {
      var old_data = obj;
      obj = data;
      event_obj.trigger('json_update', [ obj, old_data ] );
    }
  });
};

// Generate Muut-style shorthand string for timestamp (1s, 4d, etc.)
var muut_time_format = function(timestamp) {
  var time_since = Math.round(timestamp / 1000);
  var list_time = '';
  if ( time_since < 60 ) {
    list_time = 'just now';
  } else if ( time_since < ( 60 * 60 ) ) {
    list_time = String(Math.floor( time_since / 60 )) + 'm';
  } else if ( time_since < ( 60 * 60 * 24 ) ) {
    list_time = String(Math.floor( time_since / ( 60 * 60 ) )) + 'h';
  } else if ( time_since < ( 60 * 60 * 24 * 7 ) ) {
    list_time = String(Math.floor( time_since / ( 60 * 60 * 24 ) )) + 'd';
  } else {
    list_time = String(Math.floor( time_since / ( 60 * 60 * 24 * 7 ) )) + 'w';
  }
  return list_time;
};