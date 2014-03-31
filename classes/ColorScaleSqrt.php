<?php

class ColorScaleSqrt extends ColorScale {

	public function get($value, $min, $max) {
		return $this->getNormalized(sqrt($value-$min)/(sqrt($max)-sqrt($min)));
	}

}

?>