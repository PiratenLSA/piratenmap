<?php

class GemeindenMap extends SVGMap {
	function __construct($svgfile, $land) {
		parent::__construct($svgfile, $land.'-', 'Gemeinden');
	}

	public function setTitle($lines) {
		$this->setMeta('meta-title', $lines);
	}

	public function applyNames($data) {
		foreach ($data as $kreis => $text){
			$id = strtolower($this->prefix.$kreis);
			$label = $this->xml->xpath("//svg:g[".self::xLower('@id')."='$id']");
			if(count($label)) {
				$label = $label[0];
				$label->addAttribute('title', $text);
			}
		}
	}

}

?>