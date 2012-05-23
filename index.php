<?php

/* system constants */
define('VIEW_FUNC_PREFIX', 'wc_view_'); /* prefix to use in public functions */
define('EXT', 'php');
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('PUBLICPATH', './');
define('APPPATH', PUBLICPATH.'./');
define('LIBPATH', APPPATH.'lib/');
define('VIEWPATH', APPPATH.'views/');
define('LOGPATH', APPPATH.'logs/');
define('FILESPATH', APPPATH.'files/');


/* put here application constants and definitions */
include_once APPPATH.'constants.'.EXT;

/* include app config */
include_once APPPATH.'config.'.EXT;

/* include php excel */
include_once APPPATH.'PHPExcel.'.EXT;

/* include wc first library */
include_once LIBPATH.'wc.'.EXT;

/* database connection */
$db_conn = NULL;

/* main function */
function main()
{
	global $config;
	global $db_conn;
	
	/* starting sessions */
	session_start();
	
	/* setting locales */
	setlocale(LC_ALL, $config['locales']);
	
	/* default php error reporting */
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	
	set_error_handler("wc_log_php", E_ALL); /* php errors will be printed into logfiles */
	set_exception_handler("wc_log_exceptions"); /* exceptions will be printed into logfiles */
	date_default_timezone_set($config['timezone']);	/* default timezone */

	wc_load_library(); /* loading libraries */

	/* connecting to the database */
	if(isset($config['db_host']))
	{
		$db_conn = new PDO('mysql:host='.$config['db_host'].';dbname='.$config['db_name'], $config['db_user'], $config['db_pass'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));

		/* all SQL errors must be handled by PDO's exception handler */
		$db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db_conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);
	}

	wc_route_dispatch(); /* dispatch request to function */
}

/* calls main */
main();

	
?>
