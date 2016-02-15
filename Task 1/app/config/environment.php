<?php
/**
 * Environment - environment configuration and functions.
 *
 * @category    Geonovum
 * @author      Ruben Woudenberg <ruben@spotzi.com>
 */

/**
 * Constants.
 */
// Debug mode settings
define('DEBUG_MODE', true);

// Email settings
define('EMAIL_ERROR_FROM', '****@****.com');
define('EMAIL_ERROR_FROM_PASSWORD', '****');
define('EMAIL_ERROR_RECIPIENT', EMAIL_ERROR_FROM);
define('EMAIL_HOST', '****');
define('EMAIL_FROM', '****');
define('EMAIL_FROM_PASSWORD', '****');
define('EMAIL_PORT', 587);

// Spotzi Webservice settings
define('WEBSERVICE_URL', (DEBUG_MODE ? '****' : '****'));
define('WEBSERVICE_USER', '****');
define('WEBSERVICE_PASSWORD', '****');

// Bit.ly API settings
define('BITLY_URL', 'https://api-ssl.bitly.com/v3/shorten');
define('BITLY_TOKEN', '63904a3cd26ced115ecc085befe188ac2ca517ac');