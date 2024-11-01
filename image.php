<?
require_once(dirname(__FILE__)."/../../../wp-config.php");
nocache_headers();

$imagequery = array(
	'ymid' => $_GET['ymid'],
	'button' => $_GET['button'],
);

$ymstatus->get_options();

$ymids = $ymstatus->options['ids'];
if($imagequery['id']) {
	$id = $_GET['id'] - 1;
	$ymid = $ymids[$id];
} elseif($ymstatus->is_specified_ym($imagequery['ymid']) || $ymstatus->is_wpuser_ym($imagequery['ymid'])) {
	$ymid = $imagequery['ymid'];
} elseif($imagequery['ymid']) {
	$ymid = '';
} else {
	$ymid = $ymids[0];
}
if($imagequery['button']) {
	$filename_suffix = $imagequery['button'];
} else {
	$filename_suffix = $ymstatus->options['button'];
}
$fileinfo = pathinfo($filename_suffix);
$extension = $fileinfo['extension'];
if($extension == 'jpg') {
	$imageformat = 'jpeg';
} else {
	$imageformat = $extension;
}

$yahoo_url = "http://opi.yahoo.com/online?u={$ymid}&m=a&t=1";

if (ini_get('allow_url_fopen')) {
	error_reporting(0);
	$yahoo = file_get_contents($yahoo_url);
} elseif(function_exists('curl_init')) {
	$ch = curl_init($yahoo_url);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_HEADER, 0);
	$yahoo = curl_exec($ch);
	curl_close($ch);
}
$yahoo = trim($yahoo);
if(empty($yahoo)) {
	/* Maybe failed connection.*/
	$imgsrc = "./images/offline-" . $filename_suffix;
} elseif($yahoo == "01") {
	$imgsrc = "./images/online-" . $filename_suffix;
} elseif($yahoo == "00") {
	$imgsrc = "./images/offline-" . $filename_suffix;
} else {
	/* We don't know what happen but we'll use offline button anyway. */
	$imgsrc = "./images/offline-" . $filename_suffix;
}
header("Content-type: image/".$imageformat);
readfile($imgsrc);
?>
