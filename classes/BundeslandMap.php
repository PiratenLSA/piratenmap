<?php

class BundeslandMap{
	private $land;
	private $xml;

	static function xLower($d) {
		return "translate($d,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')";
	}

	function __construct($svgfile, $land) {
		$this->xml = simplexml_load_file($svgfile);
		$namespaces = $this->xml->getDocNamespaces();
		$this->xml->registerXPathNamespace('svg', $namespaces['']);

		$this->land = $land;
	}

	public function svgContent() {
		return $this->xml->saveXML();
	}

	/**
	 * BundeslandMap::applyCount()
	 *
	 * @param array $dat - array(kreis => string)
	 */
	public function applyCount($data) {
		foreach ($data as $kreis => $count){
			$id = strtolower("{$this->land}-$kreis-count");
			$label = $this->xml->xpath("//svg:text[".self::xLower('@id')."='$id']/*");
			if(count($label)) {
				$label = $label[0];
				$label[0] = $count;
			}
		}
	}

	/**
	 * BundeslandMap::applyColor()
	 *
	 * @param array $dat - array(kreis => absvalue)
	 */
	public function applyColor($data, $scale) {
		$mxv = max(array_values($data));
		$miv = min(array_values($data));
		foreach ($data as $kreis => $val){
			$id = strtolower("{$this->land}-$kreis");
			$area = $this->xml->xpath("//svg:g[@id='Kreise']/svg:g[".self::xLower('@id')."='$id']");
			if(count($area)) {
				$area = $area[0];

				$parts = $area->xpath(".//*[starts-with(@style,'fill:#')]");
				$col = $scale->get($val, $miv, $mxv);
				foreach($parts as $a) {
					$a['style'] = 'fill:#'.sprintf("%06x",$col);
				}
			}
		}
		$this->setMeta('meta-legend-min', sprintf("%.2f",$miv));
		$this->setMeta('meta-legend-max', sprintf("%.2f",$mxv));
		$grad = $this->xml->xpath("//svg:linearGradient[@id='legend-gradient']");
		$scale->toXML($grad[0]);
	}

	private function setMeta($meta, $value) {
		$label = $this->xml->xpath("//svg:text[@id='$meta']/*");
		if (!is_array($value)) {
			$value = array($value);
		}
		for ($i=0;$i<count($value);$i++) {
			$label[$i][0] = $value[$i];
		}
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

	public function setLegend($title) {
		if ($title!==false) {
			$this->setMeta('meta-legend-title', $title);
		} else {
			$legend = $this->xml->xpath("//svg:g[@id='legend']");
			if(count($legend)) {
				$legend = $legend[0];
				$legend['style'] = 'display:none';
			}
		}
	}
}

?>