<?php

class SVGMap {
	protected $prefix;
	protected $areaContainer;
	protected $xml;

	static function xLower($d) {
		return "translate($d,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')";
	}

	function __construct($svgfile, $prefix, $areaContainer) {
		$this->xml = simplexml_load_file($svgfile);
		$namespaces = $this->xml->getDocNamespaces();
		$this->xml->registerXPathNamespace('svg', $namespaces['']);

		$this->prefix = $prefix;
		$this->areaContainer = $areaContainer;
	}

	public function svgContent() {
		return $this->xml->saveXML();
	}

	/**
	 * SVGMap::applyCount()
	 *
	 * @param array $dat - array(kreis => string)
	 */
	public function applyCount($data) {
		foreach ($data as $kreis => $count){
			$id = strtolower($this->prefix.$kreis.'-count');
			$label = $this->xml->xpath("//svg:text[".self::xLower('@id')."='$id']/*");
			if(count($label)) {
				$label = $label[0];
				$label[0] = $count;
			}
		}
	}

	/**
	 * SVGMap::applyColor()
	 *
	 * @param array $dat - array(kreis => absvalue)
	 */
	public function applyColor($data, $scale) {
		$mxv = max(array_values($data));
		$miv = min(array_values($data));
		foreach ($data as $kreis => $val){
			$id = strtolower($this->prefix.$kreis);
			$area = $this->xml->xpath("//svg:g[@id='".$this->areaContainer."']/svg:g[".self::xLower('@id')."='$id']");
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

	protected function setMeta($meta, $value) {
		$label = $this->xml->xpath("//svg:text[@id='$meta']/*");
		if (!is_array($value)) {
			$value = array($value);
		}
		for ($i=0;$i<count($value);$i++) {
			$label[$i][0] = $value[$i];
		}
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