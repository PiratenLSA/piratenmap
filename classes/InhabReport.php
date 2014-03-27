<?php

class InhabReport extends Report {
	static function DisplayName() { return "Einwohnher pro Fläche"; }

	function getConfigHTML() { }

	function produceSVG($params) {
		$kreisdaten = CSVHelper::Load(DATA_DIR.'lsa-landkreise.csv', array('delim' => ';', 'empty_is_comment' => true));
		CSVHelper::MakeNumeric($kreisdaten,array(2,3));

		$map = new BundeslandMap(DATA_DIR.'base-de-lsa.svg', 'lsa');
		$count = CSVHelper::CreateSimpleMap($kreisdaten, KREIS_KFZ, KREIS_EW);
		$area = CSVHelper::CreateMap($kreisdaten, KREIS_KFZ, function($row, $old) {
			return $row[KREIS_EW] / $row[KREIS_FLAECHE];
		});

		$cs = new ColorScale();
		$cs->set(  0, 0x0000FF);
		$cs->set( 10, 0xFFFF00);
		$cs->set(100, 0x00FF00);

		$map->applyColor($area, $cs);
		$map->applyCount($count);
		$map->setDate('Datum: '.date('Y-m-d'));
		$map->setChangeSince('(Stand 25.02.2013)');
		$map->setExternal('');
		$map->setTitle(array('Einwohnerzahl', 'nach', 'Landkreisen'));
		$map->setLegend('Einwohner / km²');
		echo $map->svgContent();
	}
}

Report::RegisterClass('InhabReport');

?>