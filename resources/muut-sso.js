/**
 * Contains the functionality that is used for Muut SSO.
 * Version 1.0
 * Requires jQuery
 *
 * Copyright (c) 2014 Moot, Inc.
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */
jQuery( function($) {
  var body = $('body');
  if ( typeof muut_conf != 'undefined' && ( typeof muutObj() == 'undefined' || typeof muut() == 'undefined') ) {
    if ( typeof muut_must_fetch_signed !== 'undefined' && muut_must_fetch_signed && muut_fetch_signed_nonce ) {
      muut_fetch_signed_data(muut_fetch_signed_nonce, ajaxurl).done( function( data ) {
        $.extend(muut_conf, data);
        $('.muut_sso').muut(muut_conf);
      })
    } else {
      $('.muut_sso').muut(muut_conf);
    }
  }
});