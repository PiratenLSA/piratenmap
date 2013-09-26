<?php

abstract class Report {
#region Class Management
	private static $classes = array();

	static function IsValidReportClass($className) {
		$r = new ReflectionClass($className);
		return get_class()!== $className && $r->isSubclassOf(get_class());
	}
	static function RegisterClass($className) {
		if (self::IsValidReportClass($className)) {
			self::$classes[] = $className;
		}
	}

	public static function GetClassNames()
	{
		return self::$classes;
	}
#endregion

	static function DisplayName() {
		// so apparently, abstract static function causes an E_STRICT now.
		// glad they don't randomly change warnings around.
		throw new BadFunctionCallException('Abstract Error');
	}
	abstract function getConfigHTML();
	abstract function produceSVG($params);
}

?>