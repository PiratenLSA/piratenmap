<?php
require "common.php";
if (!isset($_GET['report'])) die('Missing: report');
if (!(isset($_GET['download']) || isset($_GET['show']))) die('Missing: mode');

$class = $_GET['report'];
if (!Report::IsValidReportClass($class)) die('Wrong: class');
$instance = new $class();

header('Content-Type: image/svg+xml');
ob_start('ob_gzhandler');
if (isset($_GET['download'])) {
	header('Content-Disposition: attachment; filename="'.$class.'.svg"');
}

$params = array();
foreach($_GET as $k=>$v) {
	if (!in_array($k, array('download','show'))) {
		$params[$k] = $v;
	}
}
$instance->produceSVG($params);
?>