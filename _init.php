<?php
// Load Config File
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

if(function_exists('date_default_timezone_set'))
	date_default_timezone_set(TIMEZONE);

ini_set('log_errors', TRUE);
ini_set('error_log', './errors.log');
error_reporting(E_ALL); // Log all errors
switch (ENVIRONMENT) {
	case 'development':
		ini_set('display_errors', 1);
		break;
	case 'production':
		ini_set('display_errors', 0);
		break;
}
$startTime = microtime(true);
// Check PHP Version Number
if (version_compare(phpversion(), '5.6.0', '<') == true) {
	exit('PHP7.4+ Required');
}

// Windows IIS Compatibility
if (!isset($_SERVER['DOCUMENT_ROOT'])) {
	if (isset($_SERVER['SCRIPT_FILENAME'])) {
		$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
	}
}

if (!isset($_SERVER['DOCUMENT_ROOT'])) {
	if (isset($_SERVER['PATH_TRANSLATED'])) {
		$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));
	}
}

if (!isset($_SERVER['REQUEST_URI'])) {
	$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);

	if (isset($_SERVER['QUERY_STRING'])) {
		$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
	}
}

if (!isset($_SERVER['HTTP_HOST'])) {
	$_SERVER['HTTP_HOST'] = getenv('HTTP_HOST');
}

// Check If SSL or Not
if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
	$_SERVER['HTTPS'] = true;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
	$_SERVER['HTTPS'] = true;
} else {
	$_SERVER['HTTPS'] = false;
}

define('PROTOCOL', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on" ? 'https' : 'http');
$subdir = SUBDIRECTORY ? '/' . rtrim(SUBDIRECTORY, '/\\') : '';
define('ROOT_URL', PROTOCOL . '://' . rtrim($_SERVER['HTTP_HOST'], '/\\') . $subdir);

// Auto Load Library
function autoload($class)
{
	$file = DIR_INCLUDE . 'lib/' . str_replace('\\', '/', strtolower($class)) . '.php';
	if (file_exists($file)) {
		include($file);
		return true;
	} else {
		return false;
	}
}
spl_autoload_register('autoload');
spl_autoload_extensions('.php');

// Load Registry
$registry = new Registry();

// Loader
$loader = new Loader($registry);
$registry->set('loader', $loader);

// Session
if (!(PHP_SAPI === 'cli' OR defined('STDIN'))) {
    $session = new Session();
    $registry->set('session', $session);
}

// DB CONFIG.
$dbhost = $sql_details['host'];
$dbname = $sql_details['db'];
$dbuser = $sql_details['user'];
$dbpass = $sql_details['pass'];
$dbport = $sql_details['port'];

// Helper Functions
require_once DIR_HELPER . 'common.php';
require_once DIR_HELPER . 'validator.php';
require_once DIR_HELPER . 'sso.php';
require_once DIR_HELPER . 'user.php';
require_once DIR_HELPER . 'role.php';
require_once DIR_HELPER . 'module.php';
require_once DIR_HELPER . 'submodule.php';
require_once DIR_HELPER . 'permission.php';
require_once DIR_HELPER . 'dictionary.php';

// DB Connection
$db = new Database($dbuser, $dbpass, $dbname, $dbhost);
function db(){
	global $db;
	return $db;
}

$dblite = new PDO('sqlite:'.DIR_LOG.CACHEFILE);
$dblite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
function dblite(){
	global $dblite;
	return $dblite;
}

$registry->set('db', $db);
$registry->set('dblite', $dblite);

// Request Library
$request = new Request();
$registry->set('request', $request);

require_once(DIR_VENDOR . 'requests/src/Autoload.php');
WpOrg\Requests\Autoload::register();

if(!empty($session->data['token'])) {
	$url  = SSOURL . "/cek_token.php";
	$data = [	"token" => $session->data['token'], 
				"auth"  => APPID];
	$response = json_decode(make_request($url, $data));
	if ($response->code != 200) {
		$url  = SSOURL . "/logout.php";
		$data = ["token" => $session->data['token']];
		$response = json_decode(make_request($url, $data));
		if ($response->code == 200) {
			unset($session->data['token']);
			session_destroy();
			header('Location: ' . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), SSOURL."/login.php?auth=".APPID), true, 302);
		}else{
			print_r($response);
		}
	}
	$registry->set('sso', $response);
}

$user = new User();
$registry->set('user', $user);

// Device Detection
$detect = new mobiledetect;
$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
$userAgent = $detect->getUserAgent();

// Document
$document = new Document($registry);
$registry->set('document', $document);

function registry()
{
	global $registry;
	return $registry;
}
