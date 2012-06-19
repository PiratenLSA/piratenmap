<?php
require "common.php";
if (!isset($_GET['report'])) die('Missing: report');

$class = $_GET['report'];
if (!Report::IsValidReportClass($class)) die('Wrong: class');
$instance = new $class();

?><html>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style type="text/css">
label {
	display: block;
}
form div input {
	width:80%;
}
</style>
<body><h1><?=$class::DisplayName()?></h1>
	<form action="render.php" method="get" target="view">
		<?$instance->getConfigHTML();?>
		<div>
			<input type="hidden" name="report" value="<?=$class?>"><br>
			<input type="submit" name="show" value="Rendern"><br>
			<input type="submit" name="download" value="Runterladen"><br>
		</div>
	</form>
</body>
</html>