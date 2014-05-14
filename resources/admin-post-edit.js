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
  var muut_toggle_commenting = function() {
    if ($('#comment_status').is(':checked')) {
      muut_enable_tab('commenting-tab');
      muut_disable_tab('channel-tab');
      muut_disable_tab('forum-tab');
    }
    if (!$('#comment_status').is(':checked')) {
      muut_disable_tab('commenting-tab');
      muut_enable_tab('channel-tab');
      muut_enable_tab('forum-tab');
    }
  };

  $('#comment_status').on('change', function() {
    muut_toggle_commenting();
  });

  $('a.enable_comments_link').on('click', function(e) {
    $('#comment_status').prop('checked', true).trigger('change');
    e.preventDefault();
  });

  $('a.disable_comments_link').on('click', function(e) {
    $('#comment_status').prop('checked', false).trigger('change');
    e.preventDefault();
  });

  var muut_disable_tab = function( tab_name ) {
    $('#muut_tab_content-' + tab_name + ', li[data-muut_tab=' + tab_name +']').addClass('disabled').removeClass('enabled');
  };

  var muut_enable_tab = function( tab_name ) {
    $('#muut_tab_content-' + tab_name + ', li[data-muut_tab=' + tab_name +']').addClass('enabled').removeClass('disabled');
  }

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