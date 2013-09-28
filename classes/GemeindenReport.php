<?php

class GemeindenReport extends Report {
	static function DisplayName() { return "Auflösung auf Gemeinden"; }

	function getConfigHTML() {
	?>
			<label for="mode">Modus: </label><select name="mode">
				<option value="wahlbeteiligung">Wahlbeteiligung</option>
				<option value="erststimmen">Erststimmenanteil</option>
				<option value="zweitstimmen">Zweitstimmenanteil</option>
				<option value="gemkreis">Test: Gemeinde&lt;&gt;Landkreis</option>
				<option value="gemwahlkreis">Test: Gemeinde&lt;&gt;Wahlkreis</option>
				<option value="wahlerflach">Wähler pro Fläche</option>
				<option value="zweiterst">Zweitstimmen pro Erststimme</option>
				<option value="stimmenaktive">Generierte Stimmen aus Mitgliedern im Kreis</option>
			</select>
	<?
	}

	function produceSVG($params) {
		$func_kreis_name = function($row, $old) {
			return empty($row[GEM_KREIS])? $row[GEM_NAME] : $row[GEM_KREIS];
		};
		$cs = null;
		$names = null;

		$map = new GemeindenMap(DATA_DIR.'osm/lsa-gemeinden.osm.svg', 'lsa');
		$gemeinden = CSVHelper::Load(DATA_DIR.'lsa-gemeinden.csv', array('delim' => ';', 'empty_is_comment' => true));
		$gem_namen = CSVHelper::CreateSimpleMap($gemeinden, GEM_GS, GEM_NAME);
		switch($params['mode']) {
			case 'wahlbeteiligung': {
				$btwdat = CSVHelper::Load(DATA_DIR.'btw13/bt13dat3.csv', array('delim' => ';', 'skip_first_rows' => 1));
				$area = CSVHelper::CreateMap($btwdat, BTWDAT3_GS, function($row, $old) {
					return $row[BTWDAT3_WAEHLER]/$row[BTWDAT3_WAHLBERECHTIGT] * 100;
				});
				$map->setTitle(array('Wahlbeteiligung', 'nach', 'Gemeinden'));
				$map->setLegend('%');
			}; break;
			case 'erststimmen': {
				$btwdat = CSVHelper::Load(DATA_DIR.'btw13/bt13dat3.csv', array('delim' => ';', 'skip_first_rows' => 1));
				$area = CSVHelper::CreateMap($btwdat, BTWDAT3_GS, function($row, $old) {
					return $row[BTWDAT3_EST_PIRATEN]/$row[BTWDAT3_EST_GUELT] * 100;
				});
				$map->setTitle(array('Erststimmen Piraten', 'nach', 'Gemeinden'));
				$map->setLegend('%');
			}; break;
			case 'zweitstimmen': {
				$btwdat = CSVHelper::Load(DATA_DIR.'btw13/bt13dat3.csv', array('delim' => ';', 'skip_first_rows' => 1));
				$area = CSVHelper::CreateMap($btwdat, BTWDAT3_GS, function($row, $old) {
					return $row[BTWDAT3_ZST_PIRATEN]/$row[BTWDAT3_ZST_GUELT] * 100;
				});
				$map->setTitle(array('Zweitstimmen Piraten', 'nach', 'Gemeinden'));
				$map->setLegend('%');
			}; break;
			case 'gemkreis': {
				$kreise = CSVHelper::Load(DATA_DIR.'lsa-landkreise.csv', array('delim' => ';', 'empty_is_comment' => true));
				$area = CSVHelper::Substitute(
				CSVHelper::CreateMap($gemeinden, GEM_GS, $func_kreis_name),
				array(
					CSVHelper::CreateSimpleMap($kreise, KREIS_NAME, KREIS_EW)
				)
				);
				$map->setTitle(array('Gemeinde<>Kreise', '', '(Test)'));
				$map->setLegend('EW der Kreise');
				$cs = new ColorScale();
				$cs->set(  0, 0x0000FF);
				$cs->set( 50, 0x00ff00);
				$cs->set(100, 0xFF0000);
			}; break;
			case 'gemwahlkreis': {
				$kreise = CSVHelper::Load(DATA_DIR.'lsa-landkreise.csv', array('delim' => ';', 'empty_is_comment' => true));
				$area = CSVHelper::CreateSimpleMap($gemeinden, GEM_GS, GEM_BTWK);
				$map->setTitle(array('Gemeinde<>Wahlkreise', '', '(Test)'));
				$map->setLegend('WK-Nummer');
				$cs = new ColorScale();
				$cs->set(  0, 0x0000FF);
				$cs->set( 16, 0x00ffff);
				$cs->set( 32, 0x00ff00);
				$cs->set( 48, 0xFFff00);
				$cs->set( 64, 0xFF00ff);
				$cs->set( 80, 0xFF0000);
				$cs->set(100, 0x0800ff);
			}; break;
			case 'wahlerflach': {
				$btwdat = CSVHelper::Load(DATA_DIR.'btw13/bt13dat3.csv', array('delim' => ';', 'skip_first_rows' => 1));
				$area = CSVHelper::Reduce(
					CSVHelper::CreateSimpleMap($gemeinden, GEM_GS, GEM_FLAECHE),
					CSVHelper::CreateSimpleMap($btwdat, BTWDAT3_GS, BTWDAT3_WAEHLER),
					false,
					function($a, $w) {
						return $w / $a;
					});
				$map->setTitle(array('Wähler', 'pro', 'Fläche'));
				$map->setLegend('Wähler / ha');
				$cs = new ColorScale();
				$cs->set(  0, 0xFFFFFF);
				$cs->set( 20, 0xFFC380);
				$cs->set(100, 0xFF8800);
			}; break;
			case 'zweiterst': {
				$btwdat = CSVHelper::Load(DATA_DIR.'btw13/bt13dat3.csv', array('delim' => ';', 'skip_first_rows' => 1));
				$area = CSVHelper::CreateMap($btwdat, BTWDAT3_GS, function($row, $old) {
					return $row[BTWDAT3_ZST_PIRATEN] / $row[BTWDAT3_EST_PIRATEN];
				});
				$map->setTitle(array('Zweitstimmen Piraten', 'vs', 'Erststimmen'));
				$map->setLegend('Z : E');
			}; break;
			case 'stimmenaktive': {
				$kreise = CSVHelper::Load(DATA_DIR.'lsa-landkreise.csv', array('delim' => ';', 'empty_is_comment' => true));
				$kmitgl = CSVHelper::Load(DATA_DIR.'mitglieder-2013-09-01.csv', array('delim' => ';', 'empty_is_comment' => true));
				$btwdat = CSVHelper::Load(DATA_DIR.'btw13/bt13dat3.csv', array('delim' => ';', 'skip_first_rows' => 1));

				$area = CSVHelper::Reduce(
					CSVHelper::CreateMap($btwdat, BTWDAT3_GS, function($row, $old) {
						return $row[BTWDAT3_ZST_PIRATEN] / $row[BTWDAT3_ZST_GUELT];
					}),
					CSVHelper::Reduce(
						CSVHelper::Substitute(
							CSVHelper::CreateMap($gemeinden, GEM_GS, $func_kreis_name),
							array(
								CSVHelper::CreateSimpleMap($kreise, KREIS_NAME, KREIS_KFZ),
								CSVHelper::CreateSimpleMap($kmitgl, MITGL_KFZ, MITGL_ANZAHL)
							)
						),
						CSVHelper::Reduce(
							CSVHelper::CreateSimpleMap($btwdat, BTWDAT3_GS, BTWDAT3_WAHLBERECHTIGT),
							CSVHelper::Substitute(
								CSVHelper::CreateMap($gemeinden, GEM_GS, $func_kreis_name),
								array(
									CSVHelper::PivotReduce(
										CSVHelper::Pivot(CSVHelper::CreateMap($gemeinden, GEM_GS, $func_kreis_name)),
										CSVHelper::CreateSimpleMap($btwdat, BTWDAT3_GS, BTWDAT3_WAHLBERECHTIGT),
										function($agg, $t) {
											return $agg + $t;
										}
									)
								)
							),
							false,
							function($wb_gem, $wb_kreis) {
								return $wb_gem/$wb_kreis;
							}
						),
						false,
						function($mitg_kreis, $anteil_gem_wb) {
							return $mitg_kreis * $anteil_gem_wb;
						}
					),
					false,
					function($zwst, $pk) {
						return $zwst/$pk;
					}
				);
				$map->setTitle(array('Zweitstimmen aus', 'Mitgliedern im ', 'jeweiligen Landkreis'));
				$map->setLegend('Zweit% / Gemeindepirat');
				$cs = new ColorScale();
				$cs->set(  0, 0x0000FF);
				$cs->set(  5, 0xffff00);
				$cs->set(100, 0xFF0000);
			}; break;
		}

		if (is_null($cs)) {
			$cs = new ColorScale();
			$cs->set(  0, 0xFFFFFF);
			$cs->set(100, 0xFF8800);
		}

		if (is_null($names)) {
			$names = CSVHelper::Reduce(
				$area,
				CSVHelper::CreateSimpleMap($gemeinden, GEM_GS, GEM_NAME),
				false,
				function ($wert, $name){
					return "$name\n$wert";
				}
			);
		}

		$map->applyColor($area, $cs);
		$map->applyNames($names);
		echo $map->svgContent();
	}
}

Report::RegisterClass('GemeindenReport');

?>