<?php
class DirInfo extends UtilityClass {
	public static function ScanFiltered($path, $regex, $files=true, $directories=false) {
		$path = self::AppendSlash($path);
		$raw = scandir($path);
		$result = array();
		foreach ($raw as $file){
			if (preg_match($regex, $file, $m) &&
			    (($files && (is_file($path.$file) && is_readable($path.$file))) ||
				 ($directories && (is_dir($path.$file))))) {
				$result[]=$file;
			}
		}
		return $result;
	}
	public static function AppendSlash($name) {
		return preg_replace('#([^\\/])$#','\1'.DIRECTORY_SEPARATOR,$name);
	}
}
?>