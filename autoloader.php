<?php
define('APPLICATION_DIR',dirname(__FILE__).DIRECTORY_SEPARATOR);
define('CLASS_LOADER_DIR',APPLICATION_DIR.'classes'.DIRECTORY_SEPARATOR);
define('DATA_DIR',APPLICATION_DIR.'data'.DIRECTORY_SEPARATOR);

function __autoload($classname) {
	if (is_readable(CLASS_LOADER_DIR.$classname.'.php')) {
		require_once CLASS_LOADER_DIR.$classname.'.php';
	} else
		die('Classfile not found: '.$classname.'.php');
}
?>