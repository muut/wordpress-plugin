/**
 * Contains the functionality that will be used for Muut on the admin side.
 * Version 1.0
 * Requires jQuery
 *
 * Copyright (c) 2014 Moot, Inc.
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */
jQuery(document).ready( function($) {

  var muut_localized = muut_admin_functions_localized;

  /********************************************/
  /* CODE FOR FORUM PAGE EDITOR FUNCTIONALITY */
  /********************************************/
  // If this is a Muut forum, make sure to show the forum name field and disable the template selector.
  $('#muut_is_forum_true').click( function() {
    if ($('#muut_forum').val() == ''){
      $('#muut_forum').val($('#editable-post-name').text());
    }
    $('#muut_page_forum_settings').show();
    $('#page_template').prop('disabled', 'disabled');
    // Make sure the page template is set as default, even though we are disabling the dropdown.
    $('<input />').attr('type', 'hidden')
      .attr('name', "page_template")
      .attr('value', "default")
      .appendTo('#post');

    $('#page_template option').filter( function() {
        return $(this).val() == 'default';
    }).prop('selected', true);
  });
  if ($('#muut_is_forum_true').is(':checked')) {
    $('#page_template').prop('disabled', 'disabled');
  }

  // If we change it to not being a forum, make sure to hide the name field and re-enable the template selector.
  $( '#muut_is_forum_false').click( function() {
    $('#muut_page_forum_settings').hide();
    $('#page_template').prop('disabled', false);
  });

  // If a given setting is dependent upon another one's value, style/disable or enable it properly.
  // See explanation below these two function declarations.
  $.fn.check_requires_fields = function() {
    var requires_element = $( '#' + this.data('muut_requires') );
    var requires_function = $( this ).data('muut_require_func');
    requires_element.on('change', { parent: requires_element, passed_function: requires_function, current: this }, this.set_requires_fields );
    requires_element.change();
  }

  $.fn.set_requires_fields = function( event ) {
    var passed_function = event.data.passed_function;
    var parent = event.data.parent;
    var current = event.data.current;
    if ( eval( 'parent.' + passed_function ) ) {
      current.removeClass( 'disabled' );
      current.find('input').prop('disabled', false);
    } else {
      current.addClass( 'disabled' );
      current.find('input').prop('disabled', true);
    }
  }

  // Execute the above check.
  // The syntax is to set a tr data-muut_requires attribute to the id of another element in the page.
  // It will run a check that is the function (in string form) stored in the same tr's data-muut_require_func attribute.
  // If true, it enables assigns that tr the class "disabled" and any inputs in that tr are disabled.
  $('body.muut_settings tr[data-muut_requires]').check_requires_fields();

  /********************************************/
  /* CODE FOR CUSTOM NAVIGATION FUNCTIONALITY */
  /********************************************/

  var muut_inserted_header_block_index = 0;
  $('#muut_add_category_header').on('click', function(e) {
    if ( typeof categoryHeaderBlockTemplate === 'string' ) {
      e.stopPropagation();
      var insert_header_replacements = { '%ID%': 'new_' + muut_inserted_header_block_index };
      var insert_block = categoryHeaderBlockTemplate.replace(/%\w+%/g, function(all) {
        return insert_header_replacements[all] || all;
      });
      $('#muut_forum_nav_headers').prepend(insert_block).find('.muut-header-title.x-editable').first().editable('toggle');
      refresh_category_sortables();
      refresh_customized_navigation_array();
      muut_inserted_header_block_index++;
    }
  });

  var muut_inserted_forum_category_index = 0;
  $(document).on('click', '.new_category_for_header', function(e) {
    if ( typeof categoryBlockTemplate === 'string' ) {
      e.stopPropagation();
      var insert_category_replacements = { '%ID%': 'new_' + muut_inserted_forum_category_index };
      var insert_block = categoryBlockTemplate.replace(/%\w+%/g, function(all) {
        return insert_category_replacements[all] || all;
      });
      $(this).closest('.muut_forum_header_item').find('.muut_category_list').prepend(insert_block).find('.muut-category-title.x-editable').first().editable('toggle');
      refresh_category_sortables();
      refresh_customized_navigation_array();
      muut_inserted_forum_category_index++;
    }
  });

    // Hook up the sortable lists.
  function refresh_category_sortables() {
    $('#muut_forum_nav_headers').sortable({
      cursor: 'move',
      handle: '.muut-category-header-actions',
      update: refresh_customized_navigation_array
    });

    $('.muut_category_list').sortable({
      cursor: 'move',
      connectWith: '.muut_category_lists_connected',
      placeholder: 'muut_category_sortable_placeholder',
      update: refresh_customized_navigation_array
    });


    $('#muut_forum_nav_headers .x-editable').on( 'save', function() {
      refresh_customized_navigation_array();
    });


    $('#muut_forum_nav_headers .muut_show_in_allposts_check').on('click', function() {
      refresh_customized_navigation_array();
    });
  }

  function refresh_customized_navigation_array() {
    var headers_order_array =  $('#muut_forum_nav_headers').sortable('toArray');
    var headers_order_new = new Array();

    // For each header, make sure the data is set up.
    for (var index = 0; index < headers_order_array.length; index++) {
      var header_categories_array = $('#' + headers_order_array[index] + ' .muut_category_list').sortable('toArray');
      var header_categories_new = new Array();

      // Prepare the categories array under this header.
      for (var index_y = 0; index_y < header_categories_array.length; index_y++) {
        var show_in_allposts_value = $('#' + header_categories_array[index_y] + ' .muut_show_in_allposts_check').is(':checked');
        header_categories_new[index_y] = {
          id: $('#' + header_categories_array[index_y]).data('id'),
          name: $('#' + header_categories_array[index_y] + ' .muut-category-title.editable').editable('getValue', true),
          args: {
            show_in_allposts: show_in_allposts_value
          }
        };
      }

      // And prepare the main array with all headers and categories.
      headers_order_new[index] = {
        id: $('#' + headers_order_array[index]).data('id'),
        name: $('#'+ headers_order_array[index] + ' .muut-header-title.editable').editable('getValue', true),
        categories: header_categories_new
      };
    }

    $('#muut_customized_navigation_array_field').val(JSON.stringify(headers_order_new));
  }

  // Make sure editables are by default done inline.
  $.fn.editable.defaults.mode = 'inline';
  $.fn.editable.defaults.showbuttons = false;

  $('#muut_forum_nav_headers .x-editable').editable().on('click', function() {
    refresh_category_sortables();
  });

  refresh_category_sortables();
  refresh_customized_navigation_array();

});