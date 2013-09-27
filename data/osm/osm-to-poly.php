<?php

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'../../autoloader.php';

define('MERGE_LIMIT'   , 0.00001);
define('SIMPLIFY_LIMIT', 0.01);
class OSMXML{
	private $xml = null;
	private $gemeindenamen = array();
	private $gemeinden = array();
	private $cacheNodes = array();
	private $cacheWays = array();
	private $cacheWaysProcessed = array();

	function __construct($file){
		echo "Loading XML $file...\n";
		$this->xml = simplexml_load_file($file);
		$this->prefetch();
		$this->loadStatics();
	}

	function tag_value($key,$tagname){
		// extract a tag value
		$tag=$key->xpath('./tag[@k="'.$tagname.'"]');
		if (count($tag)) {
			return (string)$tag[0]->attributes()->v;
		}
		return null;
	}

	function processRelations(){
		// process all relations assuming there are only gemeindeumrisse in the XML
		echo "Processing relations...\n";
		$relations = $this->xml->xpath("//relation");
		$total = count($relations);
		$i=1;
		foreach ($relations as $rela){
			$gs= $this->tag_value($rela,'de:amtlicher_gemeindeschluessel');
			echo $i++ . "/$total r".$rela->attributes()->id." ";
			$this->processWays($rela, $gs);
		}
	}

	public function processWays($rela, $gs)
	{
		// Collects all ways in a relation and merges them to a shell, adds to $gemeinden
		echo "Collection for AGS$gs...  ";
		$parts = array();
		$ways = $rela->xpath('./member[@type="way" and @role="outer"]');
		foreach ($ways as $wayref){
			$wid = (string)$wayref->attributes()->ref;
			$way = $this->cacheWaysProcessed[$wid];
			$parts[] = $way;
		}
		$points = $this->combineParts($parts);
		$this->gemeinden[$gs] = $points;
	}

	public function combineParts($parts)
	{
		// Merge multiple Ways to a shell
		$begin_parts = count($parts);
		echo " Merging Boundary from ".$begin_parts." parts...";
		if (!count($parts)) {
			return array();
		}
		$areas = array();
		while(count($parts)){
			$arr = array_shift($parts);
			while(count($parts)) {
				//find the part that continues here
				$last = $arr[count($arr)-1];
				$merge_idx = -1;
				$merge_reverse = false;
				$merge_distance = 1E9;
				for ($i=0; $i<count($parts); $i++) {
					$pt = $parts[$i];
					//at beginning
					if (($d=point_distance($last, $pt[0])) < $merge_distance) {
						$merge_idx = $i;
						$merge_reverse = false;
						$merge_distance = $d;
					}
					//at end
					if (($d=point_distance($last, $pt[count($pt)-1])) < $merge_distance) {
						$merge_idx = $i;
						$merge_reverse = true;
						$merge_distance = $d;
					}
				}
				if ($merge_idx >= 0 && $merge_distance < MERGE_LIMIT) {
					$newparts = array();
					for ($i=0; $i<count($parts); $i++) {
						if ($i==$merge_idx) {
							$merge = $parts[$i];
						} else {
							$newparts[] = $parts[$i];
						}
					}
					$parts = $newparts;
					if ($merge_reverse) {
						$merge = array_reverse($merge);
					}
					$arr = array_merge($arr, $merge);
				} else {
					// nothing matching, start new area
					break;
				}
			}
			$areas[] = $arr;
		}
		echo " Created ".count($areas)." areas. \n";
		return $areas;
	}

	public function simplifyMesh($points)
	{
		// simplify complex ways to reduce points, always keep first and last point
		if (count($points)<2) {
			return $points;
		}

		$arr = array(array_shift($points));
		$last = $arr[0];
		for ($i=0; $i<count($points)-1; $i++) {
			$pt = $points[$i];
			if (point_distance($last, $pt) > SIMPLIFY_LIMIT) {
				$arr[] = $pt;
				$last = $pt;
			}
		}
		$arr[] = $points[count($points)-1];
		return $arr;
	}

	private function prefetch()
	{
		// Cache Nodes for faster lookup
		echo "Building caches...\n";
		$this->cacheNodes = array();
		$osm = $this->xml->xpath('/osm');
		$nodes = $osm[0]->children();
		$w=1;
		foreach ($nodes as $node){
			$w++;
			if (!($w%5000)) {
				echo " XMLNode ".($w++)."/".count($nodes)."...\n";
			}
			switch($node->getName()){
				case 'node':
					$na = $node->attributes();
					$id = (string)$na->id;
					$lat = (float)$na->lat;
					$lon = (float)$na->lon;
					$this->cacheNodes[$id] = array($lat,$lon);
					break;
				case 'way':
					$na = $node->attributes();
					$id = (string)$na->id;
					$wnodes = array();
					foreach ($node->children() as $wn){
						if ($wn->getName()=='nd') {
							$wnodes[] = (string)$wn->attributes()->ref;
						}
					}
					$this->cacheWays[$id] = $wnodes;
					break;
			}
		}
		echo "Cached ".count($this->cacheWays)." ways and ".count($this->cacheNodes)." nodes.\n";

		// simplify Ways ahead of time so they're all the same
		echo "Simplifying geometry...\n";

		$this->cacheWaysProcessed = array();
		$tnodes = 0;
		foreach ($this->cacheWays as $wayid => $waydef){
			$way = array();
			foreach ($waydef as $noderef){
				$way[] = $this->cacheNodes[$noderef];
			}
			$way = $this->simplifyMesh($way);
			$this->cacheWaysProcessed[$wayid] = $way;
			$tnodes += count($way);
		}
		echo " Simplified to ".$tnodes." nodes.\n";
	}

	private function loadStatics()
	{
		// load names from common CSV
		echo "Loading static info...\n";
		$gemeinden = CSVHelper::Load(DATA_DIR.'lsa-gemeinden.csv', array('delim' => ';', 'empty_is_comment' => true));
		$this->gemeindenamen = CSVHelper::CreateFlatMap($gemeinden, 0, 1);
	}

	public function formatPointsList($ge, $setdelim=';', $pointdelim=';')
	{
		// flexible joining of point lists
		return join($setdelim, array_map(function($ge) use($pointdelim){
			return join($pointdelim,$ge);
		}, $ge));
	}

	public function writeCSV($target, $setdelim=';', $pointdelim=';')
	{
		echo " Writing CSV...\n";
		if ($file = fopen($target, "w")) {
			fputs($file,
				'Gemeindeschluessel' . ';'.
				'Name' . ';'.
				'LoopNr' . ';'.
				'Koordinaten...'.
				"\n"
				);
			foreach ($this->gemeinden as $gs => $ge){
				$lc = 1;
				foreach($ge as $lo) {
					fputs($file,
						$gs . ';'.
						$this->gemeindenamen[$gs] . ';'.
						$lc++ . ';'.
						$this->formatPointsList($lo, ';', ',').
						"\n"
						);
				}
			}
			fclose($file);
		}
	}

	public function writeSVG($target)
	{
		echo " Writing SVG from template...\n";
		$polys = array();
		foreach ($this->gemeinden as $gs => $ge){
			$loops = array();
			foreach($ge as $lo) {
				$lo = array_map(function($p) {
					return array_reverse(LLtoUTM($p));
				}, $lo);
				$loops[] = $this->formatPointsList($lo, ' ', ',');
			}

			$polys[] = array(
				'id' => $gs,
				'name' => $this->gemeindenamen[$gs],
				'loops' => $loops
			);
		}
		ob_start();
		include "basetest.svg";
		$svg = ob_get_contents();
		ob_end_clean();
		file_put_contents($target, $svg);
	}


	public function writeKML($target)
	{
		echo " Writing KML from template...\n";
		$polys = array();
		foreach ($this->gemeinden as $gs => $ge){
			$loops = array();
			foreach($ge as $lo) {
				$lo = array_map('array_reverse', $lo);
				$loops[] = $this->formatPointsList($lo, ' ', ',');
			}

			$polys[] = array(
				'id' => $gs,
				'name' => $this->gemeindenamen[$gs],
				'loops' => $loops
			);
		}
		ob_start();
		include "basetest.kml";
		$svg = ob_get_contents();
		ob_end_clean();
		file_put_contents($target, $svg);
	}
}

function point_distance($p1, $p2) {
	// TODO: should return metres on an ellipsoid or something
	$dx = ($p1[0]-$p2[0]);
	$dy = ($p1[1]-$p2[1]);
	return sqrt($dx*$dx + $dy*$dy);
}

function LLtoUTM($ll) {
	$gp = new gPoint();
	$gp->setLongLat($ll[1], $ll[0]);
	$gp->convertLLtoTM(12.0);
	return array(-$gp->N()/1000.0, $gp->E()/1000.0);
}

$osm = new OSMXML($argv[1]);
$osm->processRelations();
#$osm->writeCSV($argv[1].'.csv');
$osm->writeSVG($argv[1].'.svg');
$osm->writeKML($argv[1].'.kml');
?>