<?php

$sql_details = array(
	'host' => 'YOUR_SQL_SERVER_HOST',
	'db' => 'YOUR_DATABASE_NAME',
	'user' => 'YOUR_DATABASE_USER',
	'pass' => 'YOUR_DATABASE_PASSWORD',
	'port' => '',
	'encrypt' => true,
	'cert' => true
);

/*
 *---------------------------------------------------------------
 * SYSTEM ENVIRONMENT
 *---------------------------------------------------------------
 *
 *     development
 *     production
*/

define('ENVIRONMENT', 'development');

/*
 * --------------------------------------------------------------------
 * GLOBAL CONSTANTS
 * --------------------------------------------------------------------
 */

define('APPNAME', 'HRS');
define('APPID', 'CHANGE_THIS_TO_A_PRIVATE_SSO_APP_ID');
define('TIMEZONE', 'Asia/Karachi');

define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0755);

define('ROOT', __DIR__);
define('APPDIRNAME', 'app');
define('DIR_INCLUDE', ROOT . '/_inc/');
define('DIR_LIBRARY', DIR_INCLUDE . '/lib/');
define('DIR_MODEL', DIR_INCLUDE . '/model/');
define('DIR_VENDOR', DIR_INCLUDE . '/vendor/');
define('DIR_APP', ROOT . '/' . APPDIRNAME . '/');
define('DIR_HELPER', ROOT . '/_inc/helper/');
define('DIR_LANGUAGE', ROOT . '/language/');
define('DIR_STORAGE', ROOT . '/storage/');
define('DIR_ASSET', ROOT . '/assets/');
define('DIR_EMAIL_TEMPLATE', DIR_INCLUDE . 'template/email/');
define('DIR_BACKUP', DIR_STORAGE . 'backups/');
define('DIR_LOG', DIR_STORAGE . 'logs/');

/*
 * --------------------------------------------------------------------
 * OFFLINE-ONLINE SYNCHRONIZATION
 * --------------------------------------------------------------------
 */

define('SYNCHRONIZATION', false);
define('SYNCSERVERURL', '');

/*
 * --------------------------------------------------------------------
 * SUB-DIRECTORY
 * --------------------------------------------------------------------
 *
 * This is useful when the app is hosted inside a web-root subdirectory.
 */
define('SUBDIRECTORY', 'hr');

/*
 * --------------------------------------------------------------------
 * USE FOR CACHE
 * --------------------------------------------------------------------
 */
define('CACHEFILE', 'hr.cache');

/*
 * --------------------------------------------------------------------
 * SSO URL
 * --------------------------------------------------------------------
 */
define('SSOURL', 'http://example.test/sso/');

/*
 * --------------------------------------------------------------------
 * ENABLE/DISABLE HOOKING SYSTEM
 * --------------------------------------------------------------------
 */
define('HOOK', 0);

/*
 * --------------------------------------------------------------------
 * ENABLE/DISABLE LOGGING SYSTEM
 *
 * To work properly, set HOOK as 1.
 * --------------------------------------------------------------------
 */
define('LOG', 0);

/*
 * --------------------------------------------------------------------
 * DENIED THESE IPs TO ACCESS THE SYSTEM
 * --------------------------------------------------------------------
 */
define('DENIED_IPS', array());

/*
 * --------------------------------------------------------------------
 * ALLOWED THESE IPs ONLY. IF EMPTY, ALLOW ALL IPs.
 * --------------------------------------------------------------------
 */
define('ALLOWED_ONLY_IPS', array());
