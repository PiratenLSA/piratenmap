<?php

class CSVHelper extends UtilityClass {
	static function Load($filename, $options) {
		$options = array_merge(array(
			'delim' => ',',
			'quote' => '"',
			'escape' => '\\',
			'empty_is_comment' => false,
			'skip_first_rows' => 0
		), $options);
		if (($handle = fopen($filename, "r")) !== FALSE) {
			$result = array();
			$skip = (int)$options['skip_first_rows'];
			while (($data = fgetcsv($handle, 1000, $options['delim'], $options['quote'], $options['escape'])) !== FALSE) {
				if ($skip > 0) {
					$skip--;
					continue;
				}
				if (!$options['empty_is_comment'] || !empty($data[0])) {
					$result[] = $data;
				}
			}
			fclose($handle);
			return $result;
		}
		return null;
	}

	static function MakeNumeric(&$array, $indices) {
		if (!is_array($indices)) {
			$indices = array($indices);
		}
		foreach($array as &$row) {
			foreach($indices as $i) {
				$row[$i] = floatval($row[$i]);
			}
		}
		return $array;
	}

	static function CreateMap($array, $key, $map_function) {
		$result = array();
		foreach($array as $row) {
			$k = $row[$key];
			array_splice($row, $key, 1);
			$result[$k] = call_user_func($map_function, $row, isset($result[$k])?$result[$k]:null);
		}
		return $result;
	}

	static function CreateFlatMap($array, $key, $value) {
		if ($key < $value) {
			$value--;
		}
		return self::CreateMap($array, $key, function($row, $old) use ($value) {
			return $row[$value];
		});
	}

	static function CombineMaps($A, $B, $superset, $map_function) {
		$result = array();
		$k1 = array_keys($A);
		$k2 = array_keys($B);
		if ($superset) {
			$keys = array_merge($k1, $k2);
		} else {
			$keys = array_intersect($k1, $k2);
		}
		$keys = array_unique($keys);
		sort($keys);
		foreach($keys as $key) {
			$a = isset($A[$key])?$A[$key]:null;
			$b = isset($B[$key])?$B[$key]:null;
			$result[$key] = call_user_func($map_function, $a, $b);
		}
		return $result;
	}

	static function KeySort($array, $asc) {
		if ($asc) {
			ksort($array);
		} else {
			krsort($array);
		}
		return $array;
	}
}

?>