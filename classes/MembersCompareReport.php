<?php

class MembersCompareReport extends Report {
	static function DisplayName() { return "LSA: Mitgliederzahl"; }

	function getConfigHTML() {
		$dir = CSVHelper::KeySort(DirInfo::ScanFiltered(DATA_DIR, '#^mitglieder-(.*?)\.csv$#i'), false);
?>
		<label for="file1">Aktueller Stand aus: </label><select name="file1">
			<? foreach ($dir as $file): ?>
			<option value="<?=$file?>"><?=$file?></option>
			<? endforeach; ?>
		</select>
		<label for="file2">Vergleichen mit: </label><select name="file2">
			<? foreach ($dir as $file): ?>
			<option value="<?=$file?>"><?=$file?></option>
			<? endforeach; ?>
		</select>
<?	}

	function produceSVG($params) {
		$kreisdaten = CSVHelper::Load(DATA_DIR.'lsa-landkreise.csv', array('delim' => ';', 'empty_is_comment' => true));
		CSVHelper::MakeNumeric($kreisdaten,array(2,3));

		$params['file1'] = basename($params['file1']);
		$params['file2'] = basename($params['file2']);

		$mitgl = CSVHelper::Load(DATA_DIR.$params['file1'], array('delim' => ';', 'empty_is_comment' => true));
		CSVHelper::MakeNumeric($mitgl,1);
		if ($params['file2']!==$params['file1']) {
			$mitgla = CSVHelper::Load(DATA_DIR.$params['file2'], array('delim' => ';', 'empty_is_comment' => true));
			CSVHelper::MakeNumeric($mitgla,1);
			$compare_date = true;
		} else {
			$mitgla = $mitgl;
			$compare_date = false;
		}

		$map = new BundeslandMap(DATA_DIR.'base-de-lsa.svg', 'lsa');
		$count = CSVHelper::Reduce(
			$current_memb = CSVHelper::CreateSimpleMap($mitgl, 0, 1),
			CSVHelper::CreateSimpleMap($mitgla, 0, 1),
			true, function($a,$b) use ($compare_date) {
				if ($compare_date) {
					if ($a == $b) {
						return sprintf("%d (±0)", $a);
					} else {
						return sprintf("%d (%+d)", $a, $a-$b);
					}
				} else {
					return sprintf("%d", $a);
				}
			}
		);
		$area = CSVHelper::Reduce(
			CSVHelper::CreateSimpleMap($kreisdaten, 0, 2),
			$current_memb,
			false, function($a,$b) {
				return $b / ($a/10000);
			}
		);

		$cs = new ColorScale();
		$cs->set(  0, 0xFFFFFF);
		$cs->set(100, 0xFF8800);

		$datumneu = preg_match('#^mitglieder-(\d+)-(\d+)-(\d+).csv$#',$params['file1'],$dt)?mktime(0,0,0,$dt[2],$dt[3],$dt[1]):0;
		$datumalt = ($compare_date &&preg_match('#^mitglieder-(\d+)-(\d+)-(\d+).csv$#',$params['file2'],$dt))?mktime(0,0,0,$dt[2],$dt[3],$dt[1]):0;

		$map->applyCount($count);
		$map->applyColor($area, $cs);
		$map->setDate('Stand: '.date('d.m.Y',$datumneu));
		$map->setChangeSince($compare_date?('(Vergleich zu '.date('d.m.Y',$datumalt).')'):'');
		$map->setExternal(isset($count['XXX'])?('außerhalb: '.$count['XXX']):'');
		$map->setTitle(array('Piraten', 'in', 'Sachsen-Anhalt'));
		$map->setLegend('Piraten pro 10000 Einwohner');
		echo $map->svgContent();

	}
}

Report::RegisterClass('MembersCompareReport');

?>