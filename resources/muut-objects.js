/**
 * Contains the objects that are used for Muut dynamic functionality.
 * The objects tie in with Muut socket events.
 * Version 1.0
 * Requires jQuery
 *
 * Copyright (c) 2014 Moot, Inc.
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */
var muut_object = {
  online_users: []
};
jQuery(document).ready(function($) {
  var muut_objects_strings = muut_objects_localized;
  // Embed the hidden Muut for widgets and pages where we need to load it in the background.
  if (typeof muut() == 'undefined' && typeof muut_force_load != 'undefined' && muut_force_load ) {
    if( typeof muut_conf == 'object') {
      $.extend(muut_conf, muut_widget_conf);
    } else {
      muut_conf = muut_widget_conf;
    }

    $('#muut_hidden_embed_div').muut(muut_conf);
  }



  /************
   * MY FEED WIDGET FUNCTIONALITY
   ************/
  muut().on('load', function() {
    var widget_my_feed_wrapper = $('#muut-widget-my-feed-wrapper');
    widget_my_feed_wrapper.find('.muut_login').on('click', function(e) {
      e.preventDefault();
      $('.m-login').click();
    });
    muut().user.on('login', function() {
      muut().load('feed');
    });
    muut().user.on('logout', function() {
      widget_my_feed_wrapper.find('.muut_login').show();
    });
    if (widget_my_feed_wrapper.find('.m-logged').length > 0 ) {
      widget_my_feed_wrapper.find('.muut_login').hide();
    }

    // When a user
    muut().user.on('logout', function(event) {
      $('.muut').muut();
    });
    muut().channel.on('login', function(event) {
      $('.muut').muut('feed');
    });
  });



  $.fn.extend({
    usertooltip: function() {
      $(this).tooltip2({prefix: 'm-', delayIn: 0, delayOut: 0});
      if($(this).hasClass('m-is-admin')) {
        $(this).find(".m-tooltip").append("<em> (" + muut_objects_strings.admin + ")</em>");
      }
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
