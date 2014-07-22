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
  if ( typeof muut_conf != 'undefined' && typeof muutObj() == 'undefined' ) {
    $('.muut_sso').muut(muut_conf);
  }
});