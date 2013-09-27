<?php

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'../../autoloader.php';

class DetailFile{
	public $name = null;
	public $kreis = null;
	public $updated = null;
	public $GS = null;
	public $wk_btw = null;
	public $wk_ltw = null;
	public $area = null;
	public $people = null;

	function __construct($fname){
		$dom = new DOMDocument();
		$page = @file_get_contents($fname);
		@$dom->loadHTML($page);
		$ctr = $dom->getElementsByTagName('center');
		$ctr = $ctr->item(0);
		$str = $dom->saveXML($ctr);
		$xml = new SimpleXMLElement($str);
		#file_put_contents($fname.'.xml', $xml->saveXML());

		$t = $xml->xpath('/center/table/tr/td[2]');
		$t = explode("\n", (string)$t[0]);
		$this->name = trim($t[0]);
		$this->kreis = trim(substr($t[1],3));

		$t = $xml->xpath('/center/table[4]/tr/td[2]');
		$this->updated = trim(substr((string)$t[0], -10));

		$t = $xml->xpath('/center/table[2]/tr[last()-4]/td[2]');
		$this->GS = trim((string)$t[0]);

		$t = $xml->xpath('/center/table[2]/tr[last()-3]/td[2]');
		$t = explode('<br/>', substr($t[0]->asXML(), 4, -5));
		$this->wk_btw = trim($t[0]);
		$this->wk_ltw = trim($t[1]);

		$t = $xml->xpath('/center/table[2]/tr[last()-2]/td[2]');
		$this->area = str_replace(' ', '', (string)$t[0]);

		$t = $xml->xpath('/center/table[2]/tr[last()-1]/td[2]');
		$this->people = str_replace(' ', '', (string)$t[0]);
	}
}

class Worker{
	private $dirlist;

	function __construct($dir){
		$this->dirlist = array_filter(scandir($dir), function($fn) {
			return is_file($fn) && is_readable($fn) && preg_match('#^detail-\d{8}.htm$#', $fn);
		});
	}

	function run($target){
		if ($file = fopen($target, "w")) {
			fputs($file,join(';',array('',
				'Gemeindeschluessel',
				'Name',
				'Landkreis',
				'BTW',
				'LTW',
				'Flaeche',
				'Einwohner',
				'Stand'
				))."\n"
			);
			foreach($this->dirlist as $filename) {
				$det = new DetailFile($filename);
				fputs($file,join(';',array(
					$det->GS,
					$det->name,
					$det->kreis,
					$det->wk_btw,
					$det->wk_ltw,
					$det->area,
					$det->people,
					$det->updated
					))."\n"
				);
			}
			fclose($file);
		}
	}
}

$worker = new Worker(dirname(__file__));
$worker->run(DATA_DIR.'lsa-gemeinden.csv');
?>