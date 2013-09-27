<?php

class GemeindenReport extends Report {
	static function DisplayName() { return "Auflösung auf Gemeinden"; }

	function getConfigHTML() {
	?>
			<label for="mode">Modus: </label><select name="mode">
				<option value="wahlbeteiligung">Wahlbeteiligung</option>
			</select>
	<?
	}

	function produceSVG($params) {
		$map = new GemeindenMap(DATA_DIR.'osm/lsa-gemeinden.osm.svg', 'lsa');
		$gemeinden = CSVHelper::Load(DATA_DIR.'lsa-gemeinden.csv', array('delim' => ';', 'empty_is_comment' => true));
		$gem_namen = CSVHelper::CreateFlatMap($gemeinden, 0, 1);
		switch($params['mode']) {
			case 'wahlbeteiligung': {
				$btwdat = CSVHelper::Load(DATA_DIR.'btw13/bt13dat3.csv', array('delim' => ';', 'skip_first_rows' => 1));
				$area = CSVHelper::CreateMap($btwdat, 6, function($row, $old) {
					return $row[8]/$row[7] * 100;
				});
			}
			break;
		}

		if (!isset($cs)) {
			$cs = new ColorScale();
			$cs->set(  0, 0xFFFFFF);
			$cs->set(100, 0xFF8800);
		}

		$map->applyColor($area, $cs);
		$map->applyNames($gem_namen);
		$map->setTitle(array('Wahlbeteiligung', 'nach', 'Gemeinden'));
		$map->setLegend('Wähler');
		echo $map->svgContent();
	}
}

Report::RegisterClass('GemeindenReport');

?>