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
    requires_element.on('change keydown', { parent: requires_element, passed_function: requires_function, current: this }, this.set_requires_fields );
    requires_element.change();
  };

  $.fn.set_requires_fields = function( event ) {
    var passed_function = event.data.passed_function;
    var parent = event.data.parent;
    var current = event.data.current;
    if ( eval( 'parent.' + passed_function ) ) {
      current.removeClass( 'hidden' );
      //current.find('input').prop('disabled', false);
    } else {
      current.addClass( 'hidden' );
      //current.find('input').prop('disabled', true);
    }
  };

  // Execute the above check.
  // The syntax is to set a tr data-muut_requires attribute to the id of another element in the page.
  // It will run a check that is the function (in string form) stored in the same tr's data-muut_require_func attribute.
  // If true, it enables assigns that tr the class "disabled" and any inputs in that tr are disabled.
  var check_all_requires_fields = function() {
    $('body.toplevel_page_muut tr[data-muut_requires], ' +
      'body.toplevel_page_muut th[data-muut_requires], ' +
      'body.toplevel_page_muut .muut_requires_input_block').each(function(){
      $(this).check_requires_fields();
    });
  };
  check_all_requires_fields();

  $('#muut_enable_proxy_rewrites').on('change', function() {
    if (!$(this).is(':checked') && $('#muut_use_custom_s3_bucket').is(':checked')) {
      $('#muut_use_custom_s3_bucket').prop('checked', false);
      check_all_requires_fields();
    }
  });

  $('#muut_use_custom_s3_bucket').on('change', function() {
    if ($(this).is(':checked') && !$('#muut_enable_proxy_rewrites').is(':checked')) {
      $('#muut_enable_proxy_rewrites').prop('checked', true);
    }
  });

  // Functionality for the Advanced Options.
  $('#muut_forum_page_advanced_options_link').on('click', function() {
    $('#muut_forum_page_advanced_options').toggle();
  });

  // Functionality for outlining fields with errors on the settings page.
  if ( muut_error_fields instanceof Array ) {
    for(index=0; index<muut_error_fields.length; index++) {
      $('#' + muut_error_fields[index]).addClass('muut_error_field').on('focusin', function(e){
        $(this).on('keydown', function(e) {
          $(this).removeClass('muut_error_field');
        });
      });
    }
  }
});