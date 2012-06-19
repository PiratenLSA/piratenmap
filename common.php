<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

define('APPLICATION_DIR',dirname(__FILE__).DIRECTORY_SEPARATOR);
define('CLASS_LOADER_DIR',APPLICATION_DIR.'classes'.DIRECTORY_SEPARATOR);
define('DATA_DIR',APPLICATION_DIR.'data'.DIRECTORY_SEPARATOR);


#region Class Loader Foo

function __autoload($classname) {
	if (is_readable(CLASS_LOADER_DIR.$classname.'.php')) {
		require_once CLASS_LOADER_DIR.$classname.'.php';
	} else
		die('Classfile not found: '.$classname.'.php');
}

function load_classes() {
	/* This loads all classes defined in the CLASS_LOADER_DIR.

	   Why do that, when we have an autoloader?
	   Because this way, *all* classes will be loaded when the script executes,
	   meaning we can enumerate them as defined classes. The autoloader merely
	   does dependency resolution for us (i.e., loads base classes if we include
	   descendants first).
	   Be sure to use require_once to make sure we don't redefine anything.
	   Also, this isolates globals even inside the class definiton files, meaning
	   *anything* the class files declare has to be made global on purpose.
	   Keeps the global scope clean.
   */
	$classes = scandir(CLASS_LOADER_DIR);
	foreach($classes as $class) {
		if (preg_match("#\.php$#i", $class, $m) &&
		    is_file(CLASS_LOADER_DIR.$class) && is_readable(CLASS_LOADER_DIR.$class)) {
			require_once CLASS_LOADER_DIR.$class;
		}
	}
}

load_classes();

#endregion

?>