/**
 * Contains the functionality to make sure Muut is loaded for widgets ONLY if it hasn't been loaded yet.
 * Version 1.0
 * Requires jQuery
 *
 * Copyright (c) 2014 Moot, Inc.
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */
jQuery(document).ready( function($) {

  // Embed the hidden Muut for widgets and pages where we need to load it in the background.
  if (typeof muutObj().path == 'undefined' && typeof muut_force_load != 'undefined' && muut_force_load ) {
    if ( typeof muut_must_fetch_signed !== 'undefined' && muut_must_fetch_signed && muut_fetch_signed_nonce ) {
      muut_fetch_signed_data(muut_fetch_signed_nonce, ajaxurl).done( function( data ) {
        $.extend(muut_conf, muut_widget_conf);
        $.extend(muut_conf, data);
        $('#muut_hidden_embed_div').muut(muut_conf);
        muut().on('load', function() {
          $('body').trigger('muut_loaded');
        });
      })
    } else {
      if( typeof muut_conf == 'object') {
        $.extend(muut_conf, muut_widget_conf);
      } else {
        muut_conf = muut_widget_conf;
      }
      $('#muut_hidden_embed_div').muut(muut_conf);
      muut().on('load', function() {
        $('body').trigger('muut_loaded');
      });
    }
  }

  if ( typeof muutObj().path != 'undefined' ) {
    muutObj().on('load', function() {
      $('body').trigger('muut_loaded');
    });
  }

});