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
  });

  /************
   * ONLINE USERS WIDGET FUNCTIONALITY
   ************/  // Once Muut is loaded...
  muut().on('load', function() {
    // Functionality for the online users widget.
    var widget_online_users_wrapper = $('#muut-widget-online-users-wrapper');
    var anon_count_wrapper = widget_online_users_wrapper.find('.m-anon-count');
    var num_logged_in_span = $('.widget_muut_online_users_widget').find('.num-logged-in');
    var show_anon_count = false;
    var show_num_logged_in = false;
    if ( anon_count_wrapper.length > 0 ) {
      show_anon_count = true;
    }
    if ( num_logged_in_span.length > 0 ) {
      show_num_logged_in = true;
    }
    if (widget_online_users_wrapper.length > 0) {
      // The custom trigger listeners.
      $(muut_object).on('add_online_user', function(e, user) {

        online_user_html = get_user_avatar_html(user);
        var user_faces = widget_online_users_wrapper.find('.m-logged-users').append(online_user_html).find('.m-facelink');
        var new_user_face = user_faces[user_faces.length - 1];
        $(new_user_face).mootboost(500);
        $(new_user_face).usertooltip();
        update_anon_count();
        update_num_logged_in();
      });
      $(muut_object).on('remove_online_user', function(e, user) {
        if(user.path.substr(0,1) == '@') {
          var username = user.path.substr(1);
        }
        var username_for_selector = (username.replace(':', '\\:')).replace(' ', '_');
        widget_online_users_wrapper.find('.m-user-online_' + username_for_selector).fadeOut(500, function() { $(this).remove() });
        update_anon_count();
        update_num_logged_in();
      });
      // For the websockets that Muut is using.
      muut().channel.on('enter', function(user) {
        $(muut_object).trigger('add_online_user', [user]);
      });
      muut().channel.on('leave', function(user) {
        $(muut_object).trigger('remove_online_user', [user]);
      });
      // Do Initial rendering of online users.
      muut_object.online_users = muut().online;
      muut_object.anon_count = muut().anon_count;
      var load_online_users_initial_html = '';
      $.each(muut().online, function(index, user) {
        load_online_users_initial_html += get_user_avatar_html(user);
      });
      if ( show_anon_count ) {
        var anon_count_class = '';
        if ( !muut_object.anon_count ) {
          anon_count_wrapper.addClass('hidden');
        }
        var anon_users_html = '+<em>' + muut_object.anon_count + '</em> ' + muut_objects_strings.anonymous_users;
        anon_count_wrapper.append(anon_users_html);
      }
      if ( show_num_logged_in ) {
        num_logged_in_span.text(muut().online.length);
      }
      widget_online_users_wrapper.find('.m-logged-users').html(load_online_users_initial_html);
    }

    muut().user.on('logout', function(event) {
      $('.muut').muut();
    });
    muut().channel.on('login', function(event) {
      $('.muut').muut('feed');
    });

    var update_anon_count = function() {
      if ( show_anon_count ) {
        if (muut().anon_count == 0 && !anon_count_wrapper.hasClass('hidden')) {
          anon_count_wrapper.addClass('hidden');
        } else if (muut().anon_count > 0 && anon_count_wrapper.hasClass('hidden')) {
          anon_count_wrapper.removeClass('hidden');
        }

        anon_count_wrapper.find('em').text(muut().anon_count);
      }
    };

    var update_num_logged_in = function() {
      if ( show_num_logged_in ) {
        num_logged_in_span.text(muut().online.length);
      }
    };
    $('.m-facelink').on('click', function(e) {
        var el = $(this);
        var page = el.data('href').substr(2);
        muut().load(page);
    }).usertooltip();
  });

  $.fn.extend({
    usertooltip: function() {
      this.tooltip2({prefix: 'm-', delayIn: 0, delayOut: 0});
      this.each(function() {
        if($(this).hasClass('m-is-admin')) {
          $(this).find(".m-tooltip").append("<em> (" + muut_objects_strings.admin + ")</em>");
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
