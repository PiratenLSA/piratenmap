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

	static function MatchRegex(&$array, $index, $regex) {
		foreach($array as &$row) {
			$old = $row[$index];
			if (preg_match($regex, $old, $match)) {
				array_shift($match);
				array_splice($row, $index, 1, $match);
			}
		}
		return $array;
	}

	static function CreateMap($array, $key, $map_function) {
		$result = array();
		foreach($array as $row) {
			$k = $row[$key];
			$result[$k] = call_user_func($map_function, $row, isset($result[$k])?$result[$k]:null);
		}
		return $result;
	}

	static function CreateSimpleMap($array, $key, $value) {
		return self::CreateMap($array, $key, function($row, $old) use ($value) {
			return $row[$value];
		});
	}

	static function Reduce($A, $B, $superset, $map_function) {
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

	static function Substitute($origin, $substitutions) {
		if (count($substitutions)<1) {
			return $origin;
		}
		$dat = $origin;
		for ($i=0; $i<count($substitutions); $i++) {
			$op = $substitutions[$i];
			$result = array();
			foreach($dat as $k=>$v) {
				if (is_callable($op)) {
					$result[$k] = call_user_func($op, $v);
				} else {
					$result[$k] = $op[$v];
				}
			}
			$dat = $result;
		}
		return $dat;
	}

	static function Pivot($data) {
		$result = array();
		foreach($data as $k => $v) {
			if (isset($result[$v])) {
				$result[$v][] = $k;
			} else {
				$result[$v] = array($k);
			}
		}
		return $result;
	}

	static function PivotReduce($pivotmap, $refdata, $map_function) {
		$result = array();
		foreach ($pivotmap as $k => $v){
			$agg = null;
			if (is_array($v)) {
				// data format: a => array(1,2,3), ...
				foreach ($v as $ve){
					$d = empty($refdata)? $ve : $refdata[$ve];
					$agg = call_user_func($map_function, $agg, $d);
				}
			} else {
				// data format: a => 1, ...
				$d = empty($refdata)? $v : $refdata[$v];
				$agg = call_user_func($map_function, $agg, $d);
			}
			$result[$k] = $agg;
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

	static function KeyValToTable($array, $primary, $fieldname, $fieldvalue) {
		// map over raw grid data
		$dat = self::CreateMap($array, $primary, function($row, $old) use ($fieldname, $fieldvalue) {
			$arr = is_null($old)?array():$old;
			if (is_array($fieldvalue)) {
				$v = array();
				foreach ($fieldvalue as $fv){
					$v[] = $row[$fv];
				}
			} else {
				$v = $row[$fieldvalue];
			}
			$arr[$row[$fieldname]] = $v;
			return $arr;
		});

		// collect field names
		$fields = array();
		$displayfields = array('_PK');
		foreach ($dat as $row){
			foreach ($row as $k => $v){
				if (!in_array($k, $fields)) {
					$fields[] = $k;
					$displayfields[] = $k;
					// more than one element for this field
					if (is_array($v)) {
						for ($i=1; $i<count($v); $i++) {
							$displayfields[] = '';
						}
					}
				}
			}
		}

		// construct table from fields and data
		$ret = array();
		foreach ($dat as $pk => $data){
			$new = array($pk);
			foreach ($fields as $f){
				$a = $data[$f];
				if (is_array($a)) {
					$new = array_merge($new, $a);
				} else {
					$new[] = $a;
				}
			}
			$ret[] = $new;
		}
		return array($displayfields, $ret);
	}
}

?>