<?php
function dd($data)
{
	echo "<pre>" . print_r($data, true) . "</pre>";
	exit;
}

function device_type()
{
	global $deviceType;
	return $deviceType;
}

function redirect($url, $status = 302)
{
	if (function_exists('registry')) {
		if (registry()->get('user') && registry()->get('user')->isLogged() && isset(registry()->get('request')->get['redirect_to']) && registry()->get('request')->get['redirect_to']) {
			header('Location: ' . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), registry()->get('request')->get['redirect_to']), true, $status);
			exit();
		}
	}
	header('Location: ' . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $url), true, $status);
	exit();
}

function is_https()
{
	return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on" ? true : false;
}

function is_ajax()
{
	return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
}

function get_protocol()
{
	return PROTOCOL;
}

function root_url()
{
	return ROOT_URL;
}

function url()
{
	$request_uri = SUBDIRECTORY ? str_replace(SUBDIRECTORY, '', $_SERVER['REQUEST_URI']) : $_SERVER['REQUEST_URI'];
	return root_url() . str_replace('//', '/', $request_uri);
}

function relative_url()
{
	return strtok($_SERVER["REQUEST_URI"], '?');
}

function query_string($name)
{
	global $request;
	if (isset($request->get[$name])) {
		return htmlspecialchars($request->get[$name]);
	}
}

function is_cli()
{
	return (PHP_SAPI === 'cli' or defined('STDIN'));
}

function current_nav()
{
	return basename(relative_url(), ".php");
}

function year()
{
	return date('Y');
}

function month()
{
	return date('m');
}

function day()
{
	return date('d');
}

function current_time()
{
	return date('h:i:s');
}

function to_am_pm($time)
{
	return date("g:i A", strtotime($time));
}

function date_time()
{
	return date('Y-m-d H:i:s.v');
}

function format_date($date)
{
	return date("j M Y g:i A", strtotime($date));
}

function format_only_date($date)
{
	return date("j M Y", strtotime($date));
}

function format_input_number($val)
{
	return number_format($val, 2, '.', '');
}

function randomNumber($length)
{
	$result = '';

	for ($i = 0; $i < $length; $i++) {
		$result .= mt_rand(0, 9);
	}

	return $result;
}

function unique_id($limit = 8)
{
	return substr(md5(uniqid(mt_rand(), true)), 0, $limit);
}

function random_color_part()
{
	return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
}

function random_color()
{
	return random_color_part() . random_color_part() . random_color_part();
}

function get_months($index)
{
	$array = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
	return isset($array[$index]) ? $array[$index] : $index;
}

function get_total_day_in_month()
{
	if (function_exists('cal_days_in_month')) {
		return cal_days_in_month(CAL_GREGORIAN, month(), year());
	}
	return date('t', mktime(0, 0, 0, month() + 1, 0, year()));
}

function limit_char($string, $max = 255)
{
	if (mb_strlen($string, 'utf-8') >= $max) {
		$string = mb_substr($string, 0, $max - 5, 'utf-8') . '...';
	}

	return $string;
}

function play_sound($name, $path = null)
{
	$path = $path ? $path : root_url() . '/asset/mp3/' . $name;
	?>
	<audio style="display:none;" controls autoplay>
		<source src="<?php echo $path; ?>" type="audio/ogg">
		<source src="<?php echo $path; ?>" type="audio/mpeg">
		<source src="<?php echo $path; ?>" type="audio/mp3">
	</audio>
	<?php
}

function upper($state)
{
	return str_replace('_', ' ', ucwords($state));
}

function mergeArray($array1, $array2)
{
	$mergedArray = [];

	foreach ($array1 as $key => $value) {
		if (isset($array2[$key])) {
			$mergedArray[$key] = $array2[$key];
		} else {
			$mergedArray[$key] = $array1[$key];
		}
	}
	return $mergedArray;
}

function get_real_ip()
{
	if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') > 0) {
			$addr = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
			return trim($addr[0]);
		} else {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
	} else {
		return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
	}
}

function getMAC()
{
	ob_start();
	system('ipconfig /all');
	$mycom = ob_get_contents();
	ob_clean();
	$mac = array();
	foreach (preg_split("/(\r?\n)/", $mycom) as $line) {
		if (strstr($line, 'Physical Address')) {
			$mac[] = substr($line, 39, 18);
		}
	}
	return $mac;
}

function denied_ips()
{
	return DENIED_IPS;
}

function allowed_only_ips()
{
	return ALLOWED_ONLY_IPS;
}

// Convert DateTime Object to String
function date_normalizer($d, $format = 'Y-m-d h:i:s')
{

	if (!$d) {
		return false;
	}

	if ($d instanceof DateTime) {
		return date_format($d, $format);
	} else {
		return date($format, strtotime($d));
	}
}

function birth_date($date, $type = "full", )
{
	$bday = new DateTime($date); // Your date of birth
	$today = new Datetime(date('Y-m-d'));
	$diff = $today->diff($bday);
	if ($type == "year") {
		if ($diff->y <= 1) {
			return $diff->y . " Year ";
		} else {
			return $diff->y . " Years ";
		}
	} elseif ($type == "yd") {
		return $diff->y;
	} elseif ($type == "month") {
		if ($diff->m <= 1) {
			return $diff->m . " Month ";
		} else {
			return $diff->m . " Months ";
		}
	} elseif ($type == "day") {
		if ($diff->d <= 1) {
			return $diff->d . " Day ";
		} else {
			return $diff->d . " Days ";
		}
	} else {
		return $diff->y . " years, " . $diff->m . " month, " . $diff->d . " days";
	}
}

function date_difference($start, $end)
{
	$date1 = new DateTime($start);
	$date2 = new DateTime($end);
	$interval = new DateInterval('P1D'); // 1 day interval
	$period = new DatePeriod($date1, $interval, $date2->modify('+1 day')); // include end date
	$workingDays = 0;
	foreach ($period as $date) {
		$weekday = $date->format('N'); // 1 (for Monday) through 7 (for Sunday)
		if ($weekday <= 6) { // weekday is Mon-Sat
			$workingDays++;
		}
	}
	return $workingDays;
}

function from()
{
	global $request;
	$from = null;
	if (isset($request->get['from']) && $request->get['from'] && ($request->get['from'] != 'null')) {
		$from = $request->get['from'];
	}
	return $from;
}

function to()
{
	global $request;
	$to = null;
	if (isset($request->get['to']) && isset($request->get['from']) && ($request->get['to'] != 'null') && ($request->get['from'] != 'null')) {
		$to = $request->get['to'];
	} elseif (isset($request->get['from']) && ($request->get['from'] != 'null')) {
		$to = date('Y-m-d 23:59:59', strtotime($request->get['from']));
	}
	return $to;
}

function removeWhiteSpace($text)
{
	$text = preg_replace('/[\t\n\r\0\x0B]/', '', $text);
	$text = preg_replace('/([\s])\1+/', ' ', $text);
	$text = trim($text);
	return $text;
}

function highlightKeywords($text, $keyword)
{
	$wordsAry = explode(" ", $keyword);
	$wordsCount = count($wordsAry);

	for ($i = 0; $i < $wordsCount; $i++) {
		$highlighted_text = "<a href=\"javascript:void(0);\" class=\"text-decoration-none\">" . $wordsAry[$i] . "</a>";
		$text = str_ireplace($wordsAry[$i], $highlighted_text, $text);
	}

	return $text;
}

function parameter($parameter)
{

	if (!$parameter) {
		return false;
	}

	$query = "SELECT [Value] FROM Parameter WHERE Parameter = ? LIMIT 1";
	$stmt = dblite()->prepare($query);
	$stmt->execute([$parameter]);
	$row = $stmt->fetch(PDO::FETCH_OBJ);

	if ($row) {
		return $row->Value;
	} else {
		return false;
	}
}

function readDirectoryFiles($dir, $includeDirs = false)
{
	// Check if the directory exists
	if (!is_dir($dir)) {
		return "Directory does not exist.";
	}

	// Open the directory
	$handle = opendir($dir);
	if ($handle === false) {
		return "Unable to open directory.";
	}

	$files = [];

	// Read each entry in the directory
	while (($file = readdir($handle)) !== false) {
		// Skip the special entries '.' and '..'
		if ($file != '.' && $file != '..') {
			$filePath = $dir . DIRECTORY_SEPARATOR . $file;
			if ($includeDirs || is_file($filePath)) {
				$files[] = $file;
			}
		}
	}

	// Close the directory
	closedir($handle);
	return $files;
}

function system_log_dictionary($field = null){

    $dictionary  = array(
        "1" => "Screen",
        "2" => "Indexing",
        "3" => "Searching",
        "4" => "Uploading",
        "5" => "Open PDF",
        "6" => "Delete PDF",
    );

    if($field !== null){
        return $dictionary[$field] ? $dictionary[$field] : NULL;
    }else{
        return $dictionary;
    }

}

function insert_system_time_log($type, $source = null){
	global $startTime;
	$endTime = microtime(true);
	$pageLoadTime = $endTime - $startTime;

	if (user_role_id() != 1) {
		$insertQuery = "INSERT INTO System_Process_Time_log ([Type], Source, [Time], Date_Time, User) VALUES (?, ?, ?, ?, ?)";
		$insertStmt = dblite()->prepare($insertQuery);
		$insertStmt->execute([$type, $source, number_format($pageLoadTime, 4), date_time(), user_id()]);
	}
}

function getHostnameFromIP($ip) {
    return gethostbyaddr($ip);
}

function current_year(): int
{
	return parameter('ration_working_year') ? parameter('ration_working_year') : year();
}
