<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'autoloader.php';

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

?>