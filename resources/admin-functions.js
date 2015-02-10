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

  /********************************************/
  /* CODE FOR MUUT SETTINGS PAGE              */
  /********************************************/
  // If a given setting is dependent upon another one's value, hide or show it properly.
  // See explanation below these two function declarations.
  $.fn.check_requires_fields = function() {
    var requires_element = $( '#' + this.data('muut_requires') );
    var requires_function = $( this ).data('muut_require_func');
    var requires_true_cb = $(this).data('muut_require_true_cb');
    var requires_false_cb = $(this).data('muut_require_false_cb');
    requires_element.on('change keydown', { parent: requires_element, passed_function: requires_function, current: this, true_cb: requires_true_cb, false_cb: requires_false_cb }, this.set_requires_fields );
    requires_element.change();
  };

  // Should not be called directly, is used by the check_requires_fields function (above).
  $.fn.set_requires_fields = function( event ) {
    var passed_function = event.data.passed_function;
    var true_cb = event.data.true_cb;
    var false_cb = event.data.false_cb;
    var parent = event.data.parent;
    var current = event.data.current;
    if ( eval( 'parent.' + passed_function ) ) {
      eval( 'current.' + true_cb );
    } else {
      eval( 'current.' + false_cb );
    }
  };

  // Execute the above check.
  // The syntax is to set a tr data-muut_requires attribute to the id of another element in the page.
  // It will run a check that is the function (in string form) stored in the same tr's data-muut_require_func attribute.
  // If true, it enables assigns that tr the class "disabled" and any inputs in that tr are disabled.
  var check_all_requires_fields = function() {
    $('body.toplevel_page_muut tr[data-muut_requires], ' +
      'body.toplevel_page_muut th[data-muut_requires], ' +
      'body.toplevel_page_muut .muut_requires_input_block, ' +
      '.requires_signed_embed').each(function(){
      $(this).check_requires_fields();
    });
  };

  // Run the check-all-requires-fields function (defined above) once on page load.
  check_all_requires_fields();

  // If enable proxy rewrites becomes unchecked and the use custom s3 bucket is checked, uncheck it.
  // Then check requires fields.
  var muut_enable_proxy_rewrites_checkbox = $('#muut_enable_proxy_rewrites');
  var muut_use_custom_s3_bucket_checkbox = $('#muut_use_custom_s3_bucket');
  muut_enable_proxy_rewrites_checkbox.on('change', function() {
    if (!$(this).is(':checked') && muut_use_custom_s3_bucket_checkbox.is(':checked')) {
      muut_use_custom_s3_bucket_checkbox.prop('checked', false);
      muut_use_custom_s3_bucket_checkbox.check_requires_fields();
    }
  });

  // When The custom S3 bucket field has focus, display the descriptive text line explaining what it needs to be.
  var custom_s3_bucket_description_paragraph = $('#muut_s3_requirement_paragraph');
  $('#muut_custom_s3_bucket_name').on('focus', function() {
    custom_s3_bucket_description_paragraph.css('visibility', 'visible');
  }).on('focusout', function() {
    custom_s3_bucket_description_paragraph.css('visibility', 'hidden');
  });

  // Functionality for outlining fields with errors on the settings page.
  if ( typeof muut_error_fields !== 'undefined' && muut_error_fields instanceof Array ) {
    for(index=0; index<muut_error_fields.length; index++) {
      $('#' + muut_error_fields[index]).addClass('muut_error_field').on('focusin', function(e){
        $(this).on('keydown', function(e) {
          $(this).removeClass('muut_error_field');
        });
      });
    }
  }

  $('input.muut_autoselect').on('focus', function(e) {
    $(this).blur();
    $(this).select();
  })

  var muut_open_webhooks_setup_thickbox = function() {
    setTimeout(function() {
      $('#TB_window').css('width', '750px').css('margin-left', '-375px').css('height', 'auto');
      $('#TB_ajaxContent').css('width', 'auto');
    }, 1);
  };

  /** Function for dismissing a given admin notice **/
  var muut_dismiss_notice = function( notice_el, notice_name ) {
      var data = {
        action: 'dismiss_notice',
        security: $(notice_el).find('input[name="dismiss_nonce"]').val(),
        dismiss: true,
        notice_name: notice_name
      };

      $.post( ajaxurl, data, function(response) {
        $(notice_el).hide('slow');
      });
  };

  /** Dismiss the Review Request popup on link click to do so **/
  var review_request_notice = $('#muut_dismiss_review_request_notice');
  review_request_notice.on('click', '.dismiss_notice', function(e) {
    muut_dismiss_notice( review_request_notice, 'review_request');
  });

  /** Dismiss the upgrade notice **/
  var muut_update_notice = $('#muut_update_notice');
  muut_update_notice.on('click', '.dismiss_notice', function(e) {
    muut_dismiss_notice( muut_update_notice, 'update_notice');
  });

  /** Dismiss the file permissions notice **/
  var muut_uploads_dir_fail_notice = $('#muut_uploads_dir_fail_notice');
  muut_uploads_dir_fail_notice.on('click', '.dismiss_notice', function(e) {
    muut_dismiss_notice( muut_uploads_dir_fail_notice, 'uploads_dir_fail_notice');
  });

  // Resize thickbox for the settings webhooks integration box.
  $('.muut_settings_finish_webhook_setup').on('click', function() {
    muut_open_webhooks_setup_thickbox();
  });

  if ( typeof open_webhooks_setup_window != 'undefined' && open_webhooks_setup_window == true ) {
    $('.muut_settings_finish_webhook_setup').click();
    muut_open_webhooks_setup_thickbox();
  }

  // Borrowed and modified from realmacsoftware author Ben. http://realmacsoftware.com/blog/author:ben
  // For retina image support, just add the class "retinaise" to the img tag.
  function retinaise() {
    // Check if it's a retina device or not
    var retina = (window.devicePixelRatio > 1) ? true : false;

    // Loop through all the images you want to update
    $("img.retinaise").each(function(i,image) {
      var source = image.getAttribute('src');

      // Append "@2x" to the image src if it's a retina device
      // else remove the appended "@2x" if it's a non-retina device
      if (retina == true) {
        source = source.replace(/\.\w+$/, function(match) { return "@2x" + match; });
      } else {
        source = source.replace(/(@2x)/, '');
      }

      // Set the image src
      image.setAttribute('src', source);
    });

  }
  retinaise();
});