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

  var muut_tab_events_object = {};

  var muut_post_edit_localized = muut_admin_post_edit_localized;

  var muut_toggle_commenting = function() {
    var comment_status_checkbox = $('#comment_status');
    if (comment_status_checkbox.is(':checked')) {
      muut_enable_commenting();
      $('.muut_tab_last_active[name=muut_tab_last_active_commenting-tab]').val(1);
      muut_set_current_tab( 'commenting-tab' );
    }
    if (!comment_status_checkbox.is(':checked')) {
      muut_disable_tab('commenting-tab');
    }
  };

  var muut_set_current_tab = function( tab_name ) {
    $('#muut_tab-' + tab_name + ' a.muut_metabox_tab').click();
  };

  var muut_enable_commenting = function() {
    $('#comment_status').prop('checked', true);
    muut_enable_tab('commenting-tab');
    muut_disable_tab('channel-tab');
    muut_disable_tab('forum-tab');
    // Hide the comments metabox if Muut commenting is being used.
    $('#commentsdiv-hide').parent('label').hide();
    $('#commentsdiv').hide();
  };

  // On page load, if muut commenting is enabled, make sure to hide the comments meta box.
  if ( $('#muut_enable_tab-commenting-tab').is(':checked')) {
    $('#commentsdiv-hide').parent('label').hide();
    $('#commentsdiv').hide();
  }

  // When commenting is disabled, make sure to show the Commenting metabox/check box (if checked).
  $(muut_tab_events_object).on('tab_disable', function ( event, tab_name ) {
    if ( tab_name == 'commenting-tab' ) {
      $('#commentsdiv-hide').parent('label').show();
      if ($('#commentsdiv-hide').is(':checked')) {
        $('#commentsdiv').show();
      }
    }
  });

  var muut_enable_channel = function() {
    $('#comment_status').prop('checked', false);
    muut_disable_tab('commenting-tab');
    muut_enable_tab('channel-tab');
    muut_disable_tab('forum-tab');
  };

  var muut_enable_forum = function() {
    $('#comment_status').prop('checked', false);
    muut_disable_tab('commenting-tab');
    muut_disable_tab('channel-tab');
    muut_enable_tab('forum-tab');
  };

  $('#comment_status').on('change', function() {
    muut_toggle_commenting();
  });

  $('input.muut_enable_commenting-tab').on('change', function(e) {
    if ( $(this).is(':checked') ) {
      muut_enable_commenting();
    } else {
      muut_disable_tab('commenting-tab');
    }
    e.preventDefault();
  });

  $('input.muut_enable_channel-tab').on('change', function(e) {
    if ( $(this).is(':checked') ) {
      muut_enable_channel();
    } else {
      muut_disable_tab('channel-tab');
    }
    e.preventDefault();
  });

  $('input.muut_enable_forum-tab').on('change', function(e) {
    if ( $(this).is(':checked') ) {
      muut_enable_forum();
    } else {
      muut_disable_tab('forum-tab');
    }
    e.preventDefault();
  });

  $('a.disable_muut_commenting_link').on('click', function(e) {
    muut_disable_tab('commenting-tab');
    e.preventDefault();
  });

  var muut_disable_tab = function( tab_name ) {
    $('#muut_tab_content-' + tab_name + ', li[data-muut_tab=' + tab_name +']').addClass('disabled').removeClass('enabled');
    $('#muut_enable_tab-' + tab_name).prop('checked', false);
    $('.muut_tab_last_active[name=muut_tab_last_active_' + tab_name +']').val(0);
    $(muut_tab_events_object).trigger('tab_disable', [ tab_name ]);
  };

  var muut_enable_tab = function( tab_name ) {
    $('#muut_tab_content-' + tab_name + ', li[data-muut_tab=' + tab_name +']').addClass('enabled').removeClass('disabled');
    $('#muut_enable_tab-' + tab_name).prop('checked', true);
    $('.muut_tab_last_active[name=muut_tab_last_active_' + tab_name +']').val(1);
    $(muut_tab_events_object).trigger('tab_enable', [ tab_name ]);
  };

  if ($.fn.tabs) {
    $('#muut_metabox_tabs .muut-tab-panel.hidden').removeClass('hidden');
    $('#muut_metabox_tabs').tabs({
      activate: function(event, ui){
        ui.newTab.addClass('tabs');
        ui.newTab.children('input.muut_tab_last_open').val('1');
        ui.oldTab.removeClass('tabs');
        ui.oldTab.children('input.muut_tab_last_open').val('0');
      },
      active: $('#muut_metabox_tabs_list li.tabs').index()
    });
  }
});