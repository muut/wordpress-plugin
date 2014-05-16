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

  var muut_post_edit_localized = muut_admin_post_edit_localized;

  var muut_toggle_commenting = function() {
    if ($('#comment_status').is(':checked')) {
      muut_enable_commenting();
      $('.muut_tab_last_active[name=muut_tab_last_active_commenting-tab]').val(1);
      muut_set_current_tab( 'commenting-tab' );
    }
    if (!$('#comment_status').is(':checked')) {
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
  };

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
    //muut_tab_enable_dialog(muut_enable_commenting);
    e.preventDefault();
  });

  $('input.muut_enable_channel-tab').on('change', function(e) {
    if ( $(this).is(':checked') ) {
      muut_enable_channel();
    } else {
      muut_disable_tab('channel-tab');
    }
    //muut_tab_enable_dialog(muut_enable_channel);
    e.preventDefault();
  });

  $('input.muut_enable_forum-tab').on('change', function(e) {
    if ( $(this).is(':checked') ) {
      muut_enable_forum();
    } else {
      muut_disable_tab('forum-tab');
    }    //muut_tab_enable_dialog(muut_enable_forum);
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
  };

  var muut_enable_tab = function( tab_name ) {
    $('#muut_tab_content-' + tab_name + ', li[data-muut_tab=' + tab_name +']').addClass('enabled').removeClass('disabled');
    $('#muut_enable_tab-' + tab_name).prop('checked', true);
    $('.muut_tab_last_active[name=muut_tab_last_active_' + tab_name +']').val(1);
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

  if ($.fn.dialog) {
    var cancel_string = muut_post_edit_localized.cancel;
    var continue_string = muut_post_edit_localized.continue;
    var muut_tab_enable_dialog = function(callback) {
      if ( $('#muut_metabox_tabs > .wp-tab-bar > ul > li').size() <= 1 ) {
        callback();
      } else {
        $('#muut_tabs_disable_dialog').dialog({
          resizable: false,
          height: 140,
          modal: true,
          buttons: [ {
            text: muut_post_edit_localized.cancel,
            click: function() {
              $(this).dialog("close");
            }
          },
          {
            text: muut_post_edit_localized.continue,
            click: function() {
              $(this).dialog("close");
              callback();
            }
          }],
          dialogClass: 'no_title_modal'
        });
      }
    };
  }
});