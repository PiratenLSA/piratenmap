<?php

class BundesMap extends SVGMap {
	function __construct($svgfile) {
		parent::__construct($svgfile, 'de-', 'Bundeslaender');
	}
}

?>