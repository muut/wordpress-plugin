/**
 * Contains the objects that are used for Muut Online Users widget.
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
    // Functionality for the online users widget.
    var widget_online_users_wrapper = $('#muut-widget-online-users-wrapper');

    // IF THE ONLINE USERS WIDGET IS CURRENTLY ACTIVE AND BEING DISPLAYED.
    if (widget_online_users_wrapper.length > 0) {

      // Assign the localized translations.
      var __muut_widget_online_users_strings = muut_widget_online_users_localized;

      // Assign the proper/theoretical wrappers to the proper jQuery objects.
      var anon_count_wrapper = widget_online_users_wrapper.find('.m-anon-count');
      var num_logged_in_span = $('.widget_muut_online_users_widget').find('.num-logged-in');

      // Declare empty variables for later in the script.
      var load_online_users_initial_html = '';

      // Boolean settings variables.
      var show_anon_count = false;
      var show_num_logged_in = false;

      // Declare the function for updating the anonymous user count (called when users enter/leave.
      function update_anon_count() {
        // Obviously, only do this if we are supposed to show the anonymous user count.
        if ( show_anon_count ) {
          // If there are no anonymous users, hide the block entirely.
          if (muutObj().anon_count == 0 && !anon_count_wrapper.hasClass('hidden')) {
            anon_count_wrapper.addClass('hidden');
            // If we have added an anonymous user, make sure to RE-display the block.
          } else if (muutObj().anon_count > 0 && anon_count_wrapper.hasClass('hidden')) {
            anon_count_wrapper.removeClass('hidden');
          }

          // Replace the text (or rather, the count) to the updated number.
          anon_count_wrapper.find('em').text(muutObj().anon_count);
        }
      }

      // Declare the function for updating the logged-in user count.
      function update_num_logged_in() {
        // Obviously, only update if we are displaying the number to begin with.
        if ( show_num_logged_in ) {
          num_logged_in_span.text(muutObj().online.length);
        }
      }

      if ( anon_count_wrapper.length > 0 ) {
        show_anon_count = true;
        update_anon_count();
      }
      if ( num_logged_in_span.length > 0 ) {
        show_num_logged_in = true;
        update_num_logged_in();
      }

      // Function for adding an online user to the widget (when someone logs in, for example).
      function muut_add_online_user(user) {
        online_user_html = get_user_avatar_html(user);
        var user_faces = widget_online_users_wrapper.find('.m-logged-users').append(online_user_html).find('.m-facelink');
        var new_user_face = user_faces[user_faces.length - 1];
        $(new_user_face).mootboost(500);
        $(new_user_face).facelinkinit();
        muut_update_online_users_widget();
      }

      // Function for removing an online user to the widget (when someone logs out, for example).
      function muut_remove_online_user(user) {
        if(user.path.substr(0,1) == '@') {
          var username = user.path.substr(1);
        }
        var username_for_selector = tidy_muut_username(username);
        widget_online_users_wrapper.find('.m-user-online_' + username_for_selector).fadeOut(500, function() { $(this).remove() });
        muut_update_online_users_widget();
      }

      // Function that can be called to update/refresh the various widget stuff (e.g. how many users online, etc.).
      function muut_update_online_users_widget() {
        if(show_anon_count) {
          update_anon_count();
        }
        if(show_num_logged_in) {
          update_num_logged_in();
        }
      }

      // Execute the adding/removing based on Muut websocket events.
      muutRpc.event('enter', function(user) {
        muut_add_online_user(user);
      });
      muutRpc.event('leave', function(user) {
        muut_remove_online_user(user)
      });
      muutRpc.event('type', function(path, user) {
        var user_facelink = widget_online_users_wrapper.find('.m-facelink[data-href="#!/' + user.path + '"]');
        var selected_element = false;
        if ( user_facelink.length > 0 ) {
          selected_element = user_facelink;
        } else if ( show_anon_count ) {
          selected_element = anon_count_wrapper;
        }

        if ( selected_element ) {
          var icon = $("<em/>").ac("typing").appendTo(selected_element);
          setTimeout(function() { icon.remove() }, NEPER * 1000);
        }
      });

      // For each online user, attach the proper markup to the initially loaded HTML block.
      $.each(muutObj().online, function(index, user) {
        load_online_users_initial_html += get_user_avatar_html(user);
      });

      // Add the initial logged in users markup/faces to the widget.
      widget_online_users_wrapper.find('.m-logged-users').html(load_online_users_initial_html);

      // TODO: Replace this whole bit with a global check that accomplishes the facelink functionality assignment.
      widget_online_users_wrapper.facelinkinit();

      // If we are supposed to display the anonymous user count, add that HTML to the widget.
      if ( show_anon_count ) {
        update_anon_count();
      }
    }
  });
});
