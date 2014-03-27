<?php

class BundMembersReport extends Report {
	static function DisplayName() { return "Bund: Mitglieder"; }

	function getConfigHTML() {
	}

	function produceSVG($params) {
		$map = new BundesMap(DATA_DIR.'base-de.svg');
		$data = CSVHelper::Load(DATA_DIR.'bund-mitglieder.csv', array('delim' => ';', 'empty_is_comment' => true));
		CSVHelper::MakeNumeric($data, BM_ANZAHL);
		$landdaten = CSVHelper::Load(DATA_DIR.'bund-bundeslaender.csv', array('delim' => ';', 'empty_is_comment' => true));
		CSVHelper::MakeNumeric($landdaten, array(BL_FLAECHE,BL_EW));
		
		$area = CSVHelper::CreateSimpleMap(
			$landdaten,
			BL_KURZ,
			BL_EW
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