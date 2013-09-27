<?php

class BundeslandMap extends SVGMap {
	function __construct($svgfile, $land) {
		parent::__construct($svgfile, $land.'-', 'Kreise');
	}

	public function setTitle($lines) {
		$this->setMeta('meta-title', $lines);
	}

	public function setExternal($message) {
		$this->setMeta('meta-ext', $message);
	}

	public function setDate($dateStr) {
		$this->setMeta('meta-date', $dateStr);
	}

	public function setChangeSince($modStr) {
		$this->setMeta('meta-date-compare', $modStr);
	}
}

?>