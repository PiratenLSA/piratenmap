<?php

class BundMembersReport extends Report {
	static function DisplayName() { return "Bund: Mitglieder"; }

	function getConfigHTML() {
	?>
			<label for="mode">Modus: </label><select name="mode">
				<option value="zahlerquote">Zahlerquote</option>
				<option value="beitroffen">Beiträge offen</option>
				<option value="beitrmitgl">Offen/Mitgl.</option>
				<option value="beitrnicht">Offen/Nichtzahler</option>
				<option value="dichte">Piraten / km²</option>
				<option value="mitglew">Piraten / 10k Einwohner</option>
			</select>
	<?
	}

	function produceSVG($params) {
		$map = new BundesMap(DATA_DIR.'base-de.svg');
		$data = CSVHelper::Load(DATA_DIR.'bund-mitglieder.csv', array('delim' => ';', 'empty_is_comment' => true));
		CSVHelper::MakeNumeric($data, array(BM_ANZAHL,BM_STIMMBER,BM_OFFEN));
		$landdaten = CSVHelper::Load(DATA_DIR.'bund-bundeslaender.csv', array('delim' => ';', 'empty_is_comment' => true));
		CSVHelper::MakeNumeric($landdaten, array(BL_FLAECHE,BL_EW));

		$cs = new ColorScale();
		$cs->set(  0, 0xFFFFFF);
		$cs->set(100, 0xFF8800);

		switch($params['mode']) {
			case 'zahlerquote': {
				$area = CSVHelper::CreateMap($data, BM_KURZ, function($row,$old) {
					return $row[BM_STIMMBER]/$row[BM_ANZAHL] * 100;
				});
				$map->setLegend('Zahlerquote / %');
			}; break;
			case 'beitroffen': {
				$area = CSVHelper::CreateSimpleMap($data, BM_KURZ, BM_OFFEN);
				$map->setLegend('Beiträge offen / €');
			}; break;
			case 'beitrmitgl': {
				$area = CSVHelper::CreateMap($data, BM_KURZ, function($row,$old) {
					return $row[BM_OFFEN]/$row[BM_ANZAHL];
				});
				$map->setLegend('Offen/Mitglied / €');
			}; break;
			case 'beitrnicht': {
				$area = CSVHelper::CreateMap($data, BM_KURZ, function($row,$old) {
					return $row[BM_OFFEN]/($row[BM_ANZAHL] - $row[BM_STIMMBER]);
				});
				$map->setLegend('Offen/Nichtzahler / €');
			}; break;
			case 'dichte': {
				$area = CSVHelper::Reduce(
					CSVHelper::CreateSimpleMap($landdaten, BL_KURZ, BL_FLAECHE),
					CSVHelper::CreateSimpleMap($data, BM_KURZ, BM_ANZAHL),
					false,
					function($f, $a) {
						return $a / $f;
					}
				);
				$map->setLegend('Piraten / km²');
				$cs = new ColorScaleSqrt();
				$cs->set(  0, 0xAAAAFF);
				$cs->set( 10, 0xAAFFAA);
				$cs->set( 25, 0xFFFF88);
				$cs->set(100, 0xFF8800);
			}; break;
			case 'mitglew': {
				$area = CSVHelper::Reduce(
					CSVHelper::CreateSimpleMap($landdaten, BL_KURZ, BL_EW),
					CSVHelper::CreateSimpleMap($data, BM_KURZ, BM_ANZAHL),
					false,
					function($e, $a) {
						return $a / ($e / 10000);
					}
				);
				$map->setLegend('Piraten / 10k Einwohner');
				$cs = new ColorScale();
				$cs->set(  0, 0xFFFFFF);
				$cs->set( 33, 0xFFFF88);
				$cs->set(100, 0xFF8800);
			}; break;
		}

		$map->applyColor($area, $cs);
		echo $map->svgContent();

	}
}

Report::RegisterClass('BundMembersReport');

?>