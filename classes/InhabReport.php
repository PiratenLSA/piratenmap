<?php

class InhabReport extends Report {
	static function DisplayName() { return "Einwohnher pro Fläche"; }

	function getConfigHTML() { }

	function produceSVG($params) {
		$kreisdaten = CSVHelper::Load(DATA_DIR.'lsa-landkreise.csv', array('delim' => ';', 'empty_is_comment' => true));
		CSVHelper::MakeNumeric($kreisdaten,array(2,3));

		$map = new BundeslandMap(DATA_DIR.'LSA_basemap.svg', 'lsa');
		$count = CSVHelper::CreateFlatMap($kreisdaten, 0, 2);
		$area = CSVHelper::CreateMap($kreisdaten, 0, function($row, $old) {
			return $row[1] / $row[2];
		});

		$cs = new ColorScale();
		$cs->set(  0, 0x0000FF);
		$cs->set( 10, 0xFFFF00);
		$cs->set(100, 0x00FF00);

		$map->applyColor($area, $cs);
		$map->applyCount($count);
		$map->setDate('Datum: '.date('Y-m-d'));
		$map->setChangeSince('(Stand 31.12.2010)');
		$map->setExternal('');
		$map->setTitle(array('Einwohnerzahl', 'nach', 'Landkreisen'));
		$map->setLegend('Einwohner / km²');
		echo $map->svgContent();
	}
}

Report::RegisterClass('InhabReport');

?>