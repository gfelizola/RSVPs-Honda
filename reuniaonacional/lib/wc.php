<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/* load functions recursively in LIBPATH */
function wc_load_library($dir = LIBPATH)
{
	if(!is_dir($dir)) return FALSE;

	$handle = opendir($dir);
	
	while(FALSE !== ($file = readdir($handle))) {
		$path = $dir.$file;
		
		if(	$file != '.' && $file != '..')
		{
			if (is_dir($path.'/'))
			{
				wc_load_library($path.'/');
			}
			
			else if(is_file($path) && 
				pathinfo($path, PATHINFO_EXTENSION) == EXT)
			{
				include_once($path);
			} 
		}		
	}
	
	closedir($handle);
}


/* dispath /?f=function to function.VIEW_FUNC_SUFFIX
 * it supports hooking before call function (app_route_hook($func, $args, $ext)):
 * if it is defined, must returns TRUE */
function wc_route_dispatch($hook_before_call = NULL)
{
	global $config;
	
	$func = (isset($_REQUEST['f']) ? VIEW_FUNC_PREFIX.$_REQUEST['f'] : VIEW_FUNC_PREFIX.$config['home_index']);
	$args = (isset($_REQUEST['a']) && $_REQUEST['a'] !== "" ? preg_split('/\//', $_REQUEST['a']) : array());
	$ext  = (isset($_REQUEST['e']) && $_REQUEST['e'] !== "" ? $_REQUEST['e'] : EXT);
	$static_view = NULL;
	
	
	if(isset($_REQUEST['f'])) {
		$static_view = $_REQUEST['f'].(!empty($args) ? '/' : NULL).$_REQUEST['a'].'.'.$ext;
	} else {
		$static_view = $config['home_index'].'.'.$ext;
	}
	
	
	if(function_exists($func) && is_callable($func)) /* calls function if it exists */
	{
		/* call hook before function */
		if(($hook_before_call && call_user_func($hook_before_call, $func, $args, $ext) == TRUE) || !$hook_before_call) {
			call_user_func($func, $args, $ext); 
		}
	}
	else if($static_view && @file_exists(VIEWPATH.$static_view)) 
	{
		wc_render_view($static_view); /* treat as static page if file exists */
	}
	else 
	{
		$_REQUEST['f'] = (isset($_REQUEST['f']) ? $_REQUEST['f'] : NULL);
		wc_log_write("requested (".$_REQUEST['f'].") function ".$func."() not found.",
			 __FILE__, __FUNCTION__, __LINE__);
			 
		wc_http_404();
	}
}

function wc_site_uri($uri = '')
{
	global $config;
	
	if(isset($config['site_uri']))
	{
		$uri = $config['site_uri'].$uri;
	}
	
	return $uri;
}


/* render view content. $return allow content to be returned instead outputed */
function wc_render_view($path, $vars = array(), $return = FALSE)
{
	static $cached_vars = array(); /* allow to reuse vars in nested views */
	$cached_vars = array_merge($cached_vars, $vars);
	
	if($return)
	{
		ob_start();
	}
	
	$filename = VIEWPATH.$path;	
	
	if(!file_exists($filename))
	{
		/* allows to call views without extension */
		$filename .= '.'.EXT; 
	}
	
	if(!file_exists($filename))
	{
		wc_log_write("Can't load view file (".$filename.").", __FILE__, __FUNCTION__, __LINE__);
	}
	else
	{
		extract($cached_vars);
		include($filename);	
	}
	
	if($return)
	{
		$buffer = ob_get_contents();
		
		@ob_end_clean();
		return $buffer;		
	}
}


/* 
 * write content to the log 
 * thanks to PHP, it haven't macros like C 
 */
function wc_log_write($message, $filename = NULL, $function = NULL, $line = NULL) 
{
	global $config;
	
	if(!isset($config['print_log']) || !$config['print_log']) {
		return;
	}
	
	$log_filename = LOGPATH."messages.log";

	$handle = fopen($log_filename, 'a');
	
	if(!$handle) {
		echo "wc error: Cannot open file (".$log_filename.")";
		exit;
	}	
	
	$log_message = date('Y-m-d H:i:s');
	
	if($filename)
	{
		$log_message .= ' - '.$filename;
	}
	
	if($function)
	{
		$log_message .= ' in function '.$function.'()';
	}
	
	if($line)
	{
		$log_message .= ' at line '.$line;
	}
	
	if(is_array($message) || is_object($message)) {
		$log_message .= ': '.var_export($message, TRUE)."\n";
	} else {
		$log_message .= ': '.$message."\n";
	}
	
	flock($handle, LOCK_EX);
	
	if(fwrite($handle, $log_message."\n") === FALSE) {
		echo "wc error: Cannot write to file (".$log_filename.")";
		exit;		
	}
	
	flock($handle, LOCK_UN);
	fclose($handle);

	//@chmod($log_filename, FILE_WRITE_MODE);
}


/* write php errors to the log */
function wc_log_php($level, $message, $file, $line)
{
	global $config;
	
	if(!isset($config['print_log']) || !$config['print_log']) {
		restore_error_handler();
		return;
	}	
	
	$message = '(Level '.$level.'): '.$message;
	//wc_log_write($message, $file, NULL, $line);
	wc_http_500();
	exit;
}

/* write php exceptions to the log */
function wc_log_exceptions($exception)
{
	global $config;
	
	if(!isset($config['print_log']) || !$config['print_log']) {
		restore_exception_handler();
		return;
	}	

	$filename = $exception->getFile();
	$line = $exception->getLine();
	$message = $exception->getMessage();

	/* getting first function from trace */
	$trace = $exception->getTraceAsString();

	wc_log_write($message, $filename, $trace, $line);
	wc_http_500();
	exit;
}


/* common http codes */
function wc_http_404()
{
	wc_http_set_code(404);
	
	if(!headers_sent()) {
		wc_render_view("wc_error_404");
	}
}


function wc_http_500()
{
	wc_http_set_code(500);
	
	if(!headers_sent()) {
		wc_render_view("wc_error_500");
	}
}


function wc_http_redirect($uri = '', $http_code = 302)
{
	header("Location: ".$uri, TRUE, $http_code);
	exit;
}


/* set http header*/
function wc_http_set_code($code = 200, $text = "")
{
	$stati = array(
		200	=> 'OK',
		201	=> 'Created',
		202	=> 'Accepted',
		203	=> 'Non-Authoritative Information',
		204	=> 'No Content',
		205	=> 'Reset Content',
		206	=> 'Partial Content',

		300	=> 'Multiple Choices',
		301	=> 'Moved Permanently',
		302	=> 'Found',
		304	=> 'Not Modified',
		305	=> 'Use Proxy',
		307	=> 'Temporary Redirect',

		400	=> 'Bad Request',
		401	=> 'Unauthorized',
		403	=> 'Forbidden',
		404	=> 'Not Found',
		405	=> 'Method Not Allowed',
		406	=> 'Not Acceptable',
		407	=> 'Proxy Authentication Required',
		408	=> 'Request Timeout',
		409	=> 'Conflict',
		410	=> 'Gone',
		411	=> 'Length Required',
		412	=> 'Precondition Failed',
		413	=> 'Request Entity Too Large',
		414	=> 'Request-URI Too Long',
		415	=> 'Unsupported Media Type',
		416	=> 'Requested Range Not Satisfiable',
		417	=> 'Expectation Failed',

		500	=> 'Internal Server Error',
		501	=> 'Not Implemented',
		502	=> 'Bad Gateway',
		503	=> 'Service Unavailable',
		504	=> 'Gateway Timeout',
		505	=> 'HTTP Version Not Supported'
	);
	

	if (isset($stati[$code]) AND $text == '')
	{				
		$text = $stati[$code];
	}
	
	$server_protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : FALSE;

	if (substr(php_sapi_name(), 0, 3) == 'cgi')
	{
		header("Status: {$code} {$text}", TRUE);
	}
	elseif ($server_protocol == 'HTTP/1.1' OR $server_protocol == 'HTTP/1.0')
	{
		header($server_protocol." {$code} {$text}", TRUE, $code);
	}
	else
	{
		header("HTTP/1.1 {$code} {$text}", TRUE, $code);
	}			
}



/* returns TRUE if REQUEST_METHOD is POST, FALSE otherwise  */
function wc_http_is_post()
{
	return (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST");
}



/* simple form helper functions */
function wc_form_input_value($field_name, $default_value = NULL)
{
	return (isset($_REQUEST[$field_name]) ? $_REQUEST[$field_name] : $default_value);
}

/* 
 * this version uses array as default value
 * prevent errors like: undefined index
 */
function wc_form_input_value_arr($field_name, $default_value_key, &$default_values_arr)
{
	$value = NULL;
	if(array_key_exists($default_value_key, $default_values_arr)) {
		$value = $default_values_arr[$default_value_key];
	}

	return wc_form_input_value($field_name, $value);
}


function wc_form_options($field_name, $collection = array(), $default_value = NULL, $collection_text_is_value = FALSE)
{
	$response_html = "";
	$value = (isset($_REQUEST[$field_name]) ? $_REQUEST[$field_name] : $default_value);
	
	foreach($collection as $option_value => $option_text)
	{
		if($collection_text_is_value) {
			$option_value = $option_text;
		}

		if(is_array($value)) /* deal with select multiple */
		{
			$selected = (array_search($option_value, $value) !== FALSE ? " selected" : NULL);
		}
		else /* deal with dropdown menu */
		{
			$selected = ($option_value == $value ? " selected" : NULL);
		}
		
		if($collection_text_is_value) {
			$response_html .= '<option value="'.$option_text.'"'.
				$selected.'>'.$option_text.'</option>'."\n";
		} else {
			$response_html .= '<option value="'.$option_value.'"'.
				$selected.'>'.$option_text.'</option>'."\n";
		}
	}
	
	return $response_html;
}

function wc_form_options_arr(	$field_name,
								$collection = array(),
								$default_value_key,
								&$default_values_arr,
								$collection_text_is_value = FALSE)
{
	$value = NULL;
	if(array_key_exists($default_value_key, $default_values_arr)) {
		$value = $default_values_arr[$default_value_key];
	}

	return wc_form_options($field_name, $collection, $value, $collection_text_is_value);
}



function wc_form_checkbox($field_name, $checked = FALSE)
{
	if((wc_http_is_post() && isset($_REQUEST[$field_name]) && $_REQUEST[$field_name])
	 	|| (!wc_http_is_post() && $checked))
	{
		return 'checked="checked"';
	}	
}

function wc_form_checkbox_arr($field_name, $default_value_key, &$default_values_arr)
{
	$checked_value = NULL;
	if(array_key_exists($default_value_key, $default_values_arr)) {
		$checked_value = $default_values_arr[$default_value_key];
	}
	
	return wc_form_checkbox($field_name, $checked_value);
}

function wc_form_radiobox($field_name, $value, $checked = FALSE)
{
	if((wc_http_is_post() && isset($_REQUEST[$field_name]) && $_REQUEST[$field_name] == $value)
	 	|| (!wc_http_is_post() && $checked))
	{
		return 'checked="checked"';
	}
}

function wc_form_radiobox_arr($field_name, $value, $default_value_key, &$default_values_arr)
{
	$checked_value = FALSE;
	
	if(array_key_exists($default_value_key, $default_values_arr) 
		&& $default_values_arr[$default_value_key] == $value) {
		$checked_value = TRUE;
	}
	
	return wc_form_radiobox($field_name, $value, $checked_value);	
}

/*
 * Form validation
 * Checks if $_REQUEST[$field_name] with callback. Append new error message in $message_array.
 * Returns: $message_array with all previous messages plus new (if occour)
 */
function wc_validation(	$field_name,
						$callback,
						$callback_add_param_arr,
						$message,
						&$message_arr = array()) /* reference */
{
	$value = NULL;
	
	if(isset($_REQUEST[$field_name])) {	
		$value = $_REQUEST[$field_name];
	}
		
	return wc_validation_value(
		$field_name,
		$value,
		$callback,
		$callback_add_param_arr,
		$message,
		$message_arr
	);
	
	return $message_arr;
}

/* use this function directly if you dont want to use $_REQUEST[] */
function wc_validation_value(	$field_name,
								$value,
								$callback,
								$callback_add_param_arr,
								$message,
								&$message_arr = array()) /* reference */
{
	
	/* prevent two checks to same field */
	if(!array_key_exists($field_name, $message_arr)) 
	{	
		
		if(function_exists($callback) && is_callable($callback))
		{
			$callback_add_param_arr = array_merge(array($value), $callback_add_param_arr);
			
			if(call_user_func_array($callback, $callback_add_param_arr) === FALSE)
			{
				$message_arr[$field_name] = $message;
			}
		}
		else
		{
			wc_log_write("Validation can't call callback ".$callback."()",	__FILE__, 
				__FUNCTION__, __LINE__);
		}
	}
	
	return $message_arr;
}


/* return error message specific for the $field_name */
function wc_validation_error_in_field($field_name, &$message_arr)
{
	if(isset($message_arr[$field_name]))
	{
		return $message_arr[$field_name];
	}
}


/* validation callbacks */
function wc_validation_required($str)
{
	//return ($str !== NULL && $str !== "");
	return ($str ? TRUE : FALSE);
}


function wc_validation_min_length($str, $desired)
{
	if(wc_validation_required($str))
	{
		return (strlen($str) >= $desired);
	}
}


function wc_validation_max_length($str, $desired)
{
	if(wc_validation_required($str))
	{
		return (strlen($str) <= $desired);
	}
}


function wc_validation_exact_length($str, $desired)
{
	if(wc_validation_required($str))
	{	
		return (strlen($str) == $desired);
	}		
}

function wc_validation_matches_request($str, $field_name)
{
	return wc_validation_matches($str, @$_REQUEST[$field_name]);
}


function wc_validation_matches($str, $value)
{
	if(wc_validation_required($str))
	{
		return ($str == $value);
	}
}

function wc_validation_alpha($str)
{
	if(wc_validation_required($str))
	{
		return ( ! preg_match("/^([a-z])+$/i", $str)) ? FALSE : TRUE;
	}
}

function wc_validation_alpha_numeric($str)
{
	if(wc_validation_required($str))
	{
		return ( ! preg_match("/^([a-z0-9])+$/i", $str)) ? FALSE : TRUE;
	}
}

function wc_validation_alpha_dashes($str)
{
	if(wc_validation_required($str))
	{
		return ( ! preg_match("/^([-a-z0-9_-])+$/i", $str)) ? FALSE : TRUE;
	}
}

function wc_validation_integer($str)
{
	if(wc_validation_required($str))
	{
		return (bool)preg_match( '/^[\-+]?[0-9]+$/', $str);
	}
}

function wc_validation_unsigned_integer($str)
{
	if(wc_validation_required($str))
	{
		return (bool)preg_match( '/^[0-9]+$/', $str);
	}
}


function wc_validation_numeric($str)
{
	
	if(wc_validation_required($str))
	{
		$locale_conv = localeconv();
		
		$decimal_sep = $locale_conv['mon_decimal_point'];
		$thousands_sep = $locale_conv['mon_thousands_sep'];		
		
		return (bool)preg_match( '/^[\-+]?[0-9\\'.$thousands_sep.']*\\'.$decimal_sep.'?[0-9]+$/', $str);
	}
}

function wc_validation_email($str)
{
	if(wc_validation_required($str))
	{	
		return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
	}
}

function wc_validation_emails($str)
{
	if(wc_validation_required($str))
	{	
		if (strpos($str, ',') === FALSE)
		{
			return wc_validation_email(trim($str));
		}
		
		foreach(explode(',', $str) as $email)
		{
			if (trim($email) != '' && wc_validation_email(trim($email)) === FALSE)
			{
				return FALSE;
			}
		}
		
		return TRUE;
	}
}

function wc_validation_latin_date($str) /* use dateformat dd/mm/yyyy */
{
	if(wc_validation_required($str))
	{
		$date = explode('/', $str);
		
		if(sizeof($date) < 3) return FALSE;
		
		for($i = 0; $i < 3; $i++) {
			if(!is_numeric($date[$i])) {
				return FALSE;
			}
		}
		
		return (sizeof($date) == 3 && @checkdate($date[1], $date[0], $date[2]));
	}
}

function wc_validation_url($str)
{
	$str = wc_conv_prep_url($str);
	
	if(wc_validation_required($str))
	{
		return ( ! preg_match("/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i", $str)) ? FALSE : TRUE;		
	}
}

function wc_validation_regexp($str, $regexp)
{
	$str = wc_conv_prep_url($str);
	
	if(wc_validation_required($str))
	{
		return (!preg_match($regexp, $str)) ? FALSE : TRUE;		
	}
}

function wc_conv_prep_url($str = '')
{
	if ($str == 'http://' OR $str == '')
	{
		return '';
	}
	
	if (substr($str, 0, 7) != 'http://' && substr($str, 0, 8) != 'https://')
	{
		$str = 'http://'.$str;
	}
	
	return $str;	
}

function wc_conv_br_date_to_sql($br_date)
{
	if($br_date)
	{
		$date_arr = explode('/', $br_date);
		if(count($date_arr) != 3) return;
		return $date_arr[2].'-'.$date_arr[1].'-'.$date_arr[0];
	}

}

function wc_conv_sql_date_to_br($sql_date)
{
	if($sql_date)
	{
		$date_arr = explode('-', $sql_date);
		if(count($date_arr) != 3) return;
		return $date_arr[2].'/'.$date_arr[1].'/'.$date_arr[0];
	}

}

function wc_conv_sql_date_to_br_text($sql_date)
{
	$meses = array(
		'janeiro',
		'fevereiro',
		'março',
		'abril',
		'maio',
		'junho',
		'julho',
		'agosto',
		'setembro',
		'outubro',
		'novembro',
		'dezembro'
	);

	if($sql_date)
	{
		$date_arr = explode('-', $sql_date);
		if(count($date_arr) != 3) return;
		return $date_arr[2].'/'.$meses[max($date_arr[1]-1, 0)].'/'.$date_arr[0];
	}
}


/* extracted from url_helper of CodeIgniter */
function wc_conv_auto_link($str, $popup = FALSE)
{
	if (preg_match_all("#(^|\s|\()((http(s?)://)|(www\.))(\w+[^\s\)\<]+)#i", $str, $matches))
	{
		$pop = ($popup == TRUE) ? " target=\"_blank\" " : "";

		for ($i = 0; $i < count($matches['0']); $i++)
		{
			$period = '';
			if (preg_match("|\.$|", $matches['6'][$i]))
			{
				$period = '.';
				$matches['6'][$i] = substr($matches['6'][$i], 0, -1);
			}

			$str = str_replace($matches['0'][$i],
								$matches['1'][$i].'<a href="http'.
								$matches['4'][$i].'://'.
								$matches['5'][$i].
								$matches['6'][$i].'"'.$pop.'>http'.
								$matches['4'][$i].'://'.
								$matches['5'][$i].
								$matches['6'][$i].'</a>'.
								$period, $str);
		}
	}

	if (preg_match_all("/([a-zA-Z0-9_\.\-\+]+)@([a-zA-Z0-9\-]+)\.([a-zA-Z0-9\-\.]*)/i", $str, $matches))
	{
		for ($i = 0; $i < count($matches['0']); $i++)
		{
			$period = '';
			if (preg_match("|\.$|", $matches['3'][$i]))
			{
				$period = '.';
				$matches['3'][$i] = substr($matches['3'][$i], 0, -1);
			}

			$email = $matches['1'][$i].'@'.$matches['2'][$i].'.'.$matches['3'][$i].$period;
			$str = str_replace($matches['0'][$i], "<a href=\"mailto:".$email."\">".$email."</a>", $str);
		}
	}

	return $str;
}

function wc_conv_currency_to_number($currency)
{
	$locale_conv = localeconv();
	$currency = str_replace($locale_conv['mon_thousands_sep'], '', $currency);
	$currency = str_replace($locale_conv['mon_decimal_point'], '.', $currency);
	
	return $currency;
}

function wc_conv_number_to_currency($number)
{
	$locale_conv = localeconv();
	return number_format($number, 2, $locale_conv['mon_decimal_point'], $locale_conv['mon_thousands_sep']);
}

function wc_conv_decimal_to_human($decimal)
{
	$locale_conv = localeconv();
	return str_replace('.', $locale_conv['decimal_point'], $decimal);
}

function wc_conv_human_to_decimal($number)
{
	$locale_conv = localeconv();
	return str_replace($locale_conv['decimal_point'], '.', $number);
}

/* creates SQL snippet to INSERT */
function wc_sql_insert($table_name, $fields_values = array())
{
	$sql = "INSERT INTO ".$table_name." ";
	$sql .= wc_sql_fields($fields_values)." VALUES ";
	$sql .= wc_sql_values($fields_values);

	return $sql;
}

/* creates SQL snippet to UPDATE */
function wc_sql_update($table_name, $fields_values = array())
{
	$sql = "UPDATE ".$table_name." SET ";
	$sql .= wc_sql_fields_values($fields_values);

	return $sql;
}


/* creates SQL snippet with fields like: (field1, field2, ...) */
function wc_sql_fields($fields_values = array())
{
	if(empty($fields_values)) {
		return;
	}

	$sql = '(';

	$comma = ", ";

	// loop through fields
	foreach($fields_values as $field_name => $value)
	{
		// append field name
		$sql .= $field_name.$comma;
	}

	// remove last comma
	$sql = substr($sql, 0, max(strlen($sql) - strlen($comma), 0));
	$sql .= ')';
	return $sql;
}


/* creates SQL snippet with values like: ("value 1", "value 2", ...) */
function wc_sql_values($fields_values = array())
{
	global $db_conn;

	if(empty($fields_values)) {
		return;
	}

	$sql = '(';

	$comma = ", ";

	// loop through fields
	foreach($fields_values as $field_name => $value)
	{
		// append field name
		if($value === NULL || trim($value) === "") {
			$sql .= "NULL".$comma;
		} else {
			$sql .= $db_conn->quote($value).$comma;
		}
	}

	// remove last comma
	$sql = substr($sql, 0, max(strlen($sql) - strlen($comma), 0));
	$sql .= ')';
	return $sql;
}

/* creates SQL snippet with fields and values like:
 * field1 = "value", field2 = "value" ... 
 */
function wc_sql_fields_values($fields_values = array())
{
	global $db_conn;

	if(empty($fields_values)) {
		return;
	}

	$sql = '';

	$comma = ", ";

	foreach($fields_values as $field => $value)
	{
		$sql .= $field." = ";
		
		if($value === NULL || trim($value) === "") {
			$sql .= "NULL".$comma;
		} else {
			$sql .= $db_conn->quote($value).$comma;
		}		
	}
	
	// remove last comma
	$sql = substr($sql, 0, max(strlen($sql) - strlen($comma), 0));
	return $sql;
}

/* session */
function wc_session_get($key)
{
	if(isset($_SESSION[$key])) {
		return $_SESSION[$key];
	}
}

function wc_session_set($key, $value)
{
	$_SESSION[$key] = $value;
}

/* request */
function wc_request_get($key)
{
	if(isset($_REQUEST[$key])) {
		return $_REQUEST[$key];
	}
}

/* get */
function wc_get_get($key)
{
	if(isset($_GET[$key])) {
		return $_GET[$key];
	}
}

/* post */
function wc_post_get($key)
{
	if(isset($_POST[$key])) {
		return $_POST[$key];
	}
}

?>
