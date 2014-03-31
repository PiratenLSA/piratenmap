<?php

class ColorScale {
	private $table = array();

	function __construct(){
	}

	private static function clamp($x) {
		return max(0,min(1,$x));
	}

	private static function bgr($r,$g,$b) {
		$p = pack('c*', 0, $r, $g, $b);
		$n = unpack('N', $p);
		return $n[1];
	}

	public function set($stop, $color) {
		$this->table[$stop] = $color;
		ksort($this->table, SORT_NUMERIC);
	}

	public function getNormalized($value) {
		$p = (int)(self::clamp($value) * 100);
		// find smallest key that is just above value
		$ke = array_keys($this->table);
		$lower = null;
		$higher = null;
		for ($i=0; $i<count($ke); $i++) {
			if (is_null($higher) && ($ke[$i]>$p)) {
				// the first one that is larger
				$higher = $ke[$i];
			}
			if ($ke[$i]<=$p) {
				//the last one that was smaller/equal
				$lower = $ke[$i];
			}
		}
		if (!is_null($higher) && !is_null($lower)) {
			//both found, interpolate
			$pp = ($p-$lower)/($higher-$lower);
			// split channels
			$ch = unpack('C*',pack('N',$this->table[$higher]));
			$cl = unpack('C*',pack('N',$this->table[$lower]));
			return self::bgr(
				$cl[2] + ($ch[2]-$cl[2])*$pp,
				$cl[3] + ($ch[3]-$cl[3])*$pp,
				$cl[4] + ($ch[4]-$cl[4])*$pp);
		} else if (is_null($lower) && !is_null($higher)) {
			//nothing below
			return $this->table[$higher];
		} else if (is_null($higher) && !is_null($lower)) {
			//nothing above
			return $this->table[$lower];
		}
		// whoops, we have 0 points
		return self::bgr(0,0,0);
	}

	public function get($value, $min, $max) {
		return $this->getNormalized(($value-$min)/($max-$min));
	}

	public function getSqrt($value, $min, $max) {
		return $this->getPercent(sqrt($value-$min)/(sqrt($max)-sqrt($min)));
	}

	public function toXML($linearGradient) {
		while(count($linearGradient->stop)){
			unset($linearGradient->stop[0]);
		}
		// SVG wants stops in ascending order, so we have to reverse manually
		$copy = array();
		foreach($this->table as $percent=>$col) {
			$copy[100-$percent] = $col;
		}
		ksort($copy);
		foreach($copy as $percent=>$col) {
			$st = $linearGradient->addChild('stop');
			$st->addAttribute('offset', sprintf('%d%%',$percent));
			$st->addAttribute('style', 'stop-opacity:1;stop-color:#'.sprintf('%06x',$col));
		}
	}
}

?>