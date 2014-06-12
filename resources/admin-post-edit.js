/**
 * Contains the functionality that will be used for the post editor for Muut functionality.
 * Version 1.0
 * Requires jQuery
 *
 * Copyright (c) 2014 Moot, Inc.
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */
jQuery(document).ready( function($) {

  // Set the localization variable containing the localized strings.
  var muut_post_edit_localized = muut_admin_post_edit_localized;

  // Set up the tab-events-object which will some of the event listening to our custom triggers.
  var muut_tab_events_object = {};

  // Define the function to toggle Muut commenting.
  // Decides what to do based on the main WordPress discussion meta box "allow commenting" option.
  var muut_toggle_commenting = function() {
    var comment_status_checkbox = $('#comment_status');
    if (comment_status_checkbox.is(':checked')) {
      muut_enable_commenting();
      $('.muut_tab_last_active[name=muut_tab_last_active_commenting-tab]').val(1);
      muut_set_current_tab( 'commenting-tab' );
    } else {
      muut_disable_tab('commenting-tab');
    }
  };

  // Generalized function to set which tab is currently showing in the Muut meta box.
  var muut_set_current_tab = function( tab_name ) {
    $('#muut_tab-' + tab_name + ' a.muut_metabox_tab').click();
  };

  // On page load, if muut commenting is enabled, make sure to hide the comments meta box.
  if ( $('#muut_enable_tab-commenting-tab').is(':checked')) {
    $('#commentsdiv-hide').parent('label').hide();
    $('#commentsdiv').hide();
  }

  // When Muut commenting is disabled, make sure to show the Commenting metabox/check box (if checked).
  $(muut_tab_events_object).on('tab_disable', function ( event, tab_name ) {
    if ( tab_name == 'commenting-tab' ) {
      var default_wp_commenting_metabox_display_element = $('#commentsdiv-hide');
      default_wp_commenting_metabox_display_element.parent('label').show();
      if (default_wp_commenting_metabox_display_element.is(':checked')) {
        $('#commentsdiv').show();
      }
    }
  });

  // Declare function to enable Muut commenting.
  var muut_enable_commenting = function() {
    $('#comment_status').prop('checked', true);
    muut_enable_tab('commenting-tab');
    muut_disable_tab('channel-tab');
    muut_disable_tab('forum-tab');
    // Hide the comments metabox if Muut commenting is being used.
    $('#commentsdiv-hide').parent('label').hide();
    $('#commentsdiv').hide();
  };

  // Declare function to enable Muut channel embed.
  var muut_enable_channel = function() {
    $('#comment_status').prop('checked', false);
    muut_disable_tab('commenting-tab');
    muut_enable_tab('channel-tab');
    muut_disable_tab('forum-tab');
  };

  // Declare function to enable Muut forum page embed.
  var muut_enable_forum = function() {
    $('#comment_status').prop('checked', false);
    muut_disable_tab('commenting-tab');
    muut_disable_tab('channel-tab');
    muut_enable_tab('forum-tab');
  };

  // When the WP "Allow Comments" checkbox state changes, toggle the Muut commenting enabled status.
  $('#comment_status').on('change', function() {
    muut_toggle_commenting();
  });

  // Make sure proper functionality is used when Muut commenting activation checkbox changes.
  $('input.muut_enable_commenting-tab').on('change', function(e) {
    if ( $(this).is(':checked') ) {
      muut_enable_commenting();
    } else {
      muut_disable_tab('commenting-tab');
    }
    e.preventDefault();
  });

  // Make sure proper functionality is used when Muut channel embed activation checkbox changes.
  $('input.muut_enable_channel-tab').on('change', function(e) {
    if ( $(this).is(':checked') ) {
      muut_enable_channel();
    } else {
      muut_disable_tab('channel-tab');
    }
    e.preventDefault();
  });

  // Make sure proper functionality is used when Muut forum page embed activation checkbox changes.
  $('input.muut_enable_forum-tab').on('change', function(e) {
    if ( $(this).is(':checked') ) {
      muut_enable_forum();
    } else {
      muut_disable_tab('forum-tab');
    }
    e.preventDefault();
  });

  // Generalized function to disable a specific tab.
  var muut_disable_tab = function( tab_name ) {
    $('#muut_tab_content-' + tab_name + ', li[data-muut_tab=' + tab_name +']').addClass('disabled').removeClass('enabled');
    $('#muut_enable_tab-' + tab_name).prop('checked', false);
    $('.muut_tab_last_active[name=muut_tab_last_active_' + tab_name +']').val(0);
    $(muut_tab_events_object).trigger('tab_disable', [ tab_name ]);
    muut_toggle_page_template();
  };

  // Generalized function to enable a specific tab.
  var muut_enable_tab = function( tab_name ) {
    $('#muut_tab_content-' + tab_name + ', li[data-muut_tab=' + tab_name +']').addClass('enabled').removeClass('disabled');
    $('#muut_enable_tab-' + tab_name).prop('checked', true);
    $('.muut_tab_last_active[name=muut_tab_last_active_' + tab_name +']').val(1);
    $(muut_tab_events_object).trigger('tab_enable', [ tab_name ]);
    muut_toggle_page_template();
  };

  // Disable or enable the page template field
  var muut_toggle_page_template = function() {
    if ( $('#muut_enable_tab-channel-tab').is(':checked') || $('#muut_enable_tab-forum-tab').is(':checked')) {
      $('#page_template').prop('disabled', 'disabled');
      // Make sure the page template is set as default, even though we are disabling the dropdown.
      $('<input />').attr('type', 'hidden')
        .attr('name', "page_template")
        .attr('value', "default")
        .appendTo('#post');

      $('#page_template option').filter( function() {
        return $(this).val() == 'default';
      }).prop('selected', true);
    } else {
      $('#post').find('input[name="page_template"][type="hidden"]').remove();
      $('#page_template').prop('disabled', false);
    }
  };

  // Execute on load.
  muut_toggle_page_template();

  // Check if jQuery Tabs UI API is enabled before executing related functionality.
  if ($.fn.tabs) {
    var muut_metabox_tabs_element = $('#muut_metabox_tabs');
    muut_metabox_tabs_element.find('.muut-tab-panel.hidden').removeClass('hidden');
    // Execute the Tabs functionality for the Muut metabox tabs.
    muut_metabox_tabs_element.tabs({
      activate: function(event, ui){
        ui.newTab.addClass('tabs');
        ui.newTab.children('input.muut_tab_last_open').val('1');
        ui.oldTab.removeClass('tabs');
        ui.oldTab.children('input.muut_tab_last_open').val('0');
      },
      active: $('#muut_metabox_tabs_list').find('li.tabs').index()
    });
  }
});