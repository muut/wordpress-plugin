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
  // Embed the hidden Muut for widgets and pages where we need to load it in the background.
  if (typeof muut() == 'undefined' && typeof muut_load_empty != 'undefined' && muut_load_empty ) {
    $('#muut_hidden_embed_div').muut(muut_conf);
  }

  // Once Muut is loaded...
  muut().on('load', function() {
    // Functionality for the online users widget.
    var widget_online_users_wrapper = $('#muut-widget-online-users-wrapper');
    if (widget_online_users_wrapper.length > 0) {
      // The custom trigger listeners.
      $(muut_object).on('add_online_user', function(e, user) {
        online_user_html = get_user_avatar_html(user);
        widget_online_users_wrapper.find('.m-logged-users').append(online_user_html);
      });
      $(muut_object).on('remove_online_user', function(e, user) {
        widget_online_users_wrapper.find('.m-user-online_' + user.username).remove();
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
      var load_online_users_initial_html = '';
      $.each(muut().online, function(index, user) {
        load_online_users_initial_html += get_user_avatar_html(user);
      });
      widget_online_users_wrapper.find('.m-logged-users').append(load_online_users_initial_html);
    }
  });
});

// Function that contains the template for avatars.
var get_user_avatar_html = function(user) {
  var html = '<a class="m-facelink m-is-admin m-user-online_' + user.username +'" href="#!/' + user.path + '" data-href="#!/' + user.path + '"><img class="m-face" src="' + user.img + '"></a><span class="m-tooltip" style="top: -18px; left: -25px;">' + user.displayname + '</span>';
  return html;
};
