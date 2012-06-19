<?php
require "common.php";

$reports = Report::GetClassNames();
?><html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Piraten LSA Karten</title>
</head>
<body>
	<table style="width: 100%; height: 800px;">
		<tr>
			<td style="height: 20px;" colspan="2">
				<form action="configure.php" method="get" target="configure">
					<select name="report">
						<? foreach($reports as $report):?>
						<option value="<?=$report?>"><?=$report::DisplayName()?></option>
						<? endforeach; ?>
					</select>
					<input value="Los --&gt;" type="submit">
				</form>
			</td>
		</tr>
		<tr>
			<td style="width: 300px;"><iframe src="about:blank" style="width: 100%; height: 100%;" name="configure"></iframe></td>
			<td><iframe src="about:blank" style="width: 100%; height: 100%;" name="view"></iframe></td>
		</tr>
	</table>
</body>
</html>