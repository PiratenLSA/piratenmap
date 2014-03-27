<?php

class BundMembersReport extends Report {
	static function DisplayName() { return "Bund: Mitglieder"; }

	function getConfigHTML() {
	}

	function produceSVG($params) {
		$map = new BundesMap(DATA_DIR.'base-de.svg');
		$area = CSVHelper::CreateSimpleMap(
			array_map(
				function($e) {
					return array($e, rand(0,100));
				},
				explode(',','TH,SH,ST,SN,SA,RP,NW,NI,MV,HE,HH,BR,BB,BE,BY,BW')
			),
			0,
			1
		);

		$cs = new ColorScale();
		$cs->set(  0, 0xFFFFFF);
		$cs->set(100, 0xFF8800);

		$map->applyColor($area, $cs);
		$map->setLegend('Pretty Colours');
		echo $map->svgContent();

	}
}

Report::RegisterClass('BundMembersReport');

?>