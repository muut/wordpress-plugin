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
  if ( ( body.hasClass( 'muut-enabled' ) || body.hasClass( 'has-muut' ) || body.hasClass( 'has-moot' ) ) && typeof muut_conf != 'undefined' && typeof muut() == 'undefined' ) {
    $('.muut_sso').muut(muut_conf);
  }
});