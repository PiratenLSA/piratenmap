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

	abstract static function DisplayName();
	abstract function getConfigHTML();
	abstract function produceSVG($params);
}

?>