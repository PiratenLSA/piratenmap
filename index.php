<?php
require "common.php";

$reports = Report::GetClassNames();
?><html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Piraten LSA Karten</title>
</head>
<body style="margin:2px">
	<table style="width: 100%; height: 800px;">
		<tr>
			<td colspan="2" valign="top" style="height: 1.5em;">
				<form action="configure.php" method="get" target="configure">
					<select name="report" onchange="this.form.submit()">
						<? foreach($reports as $report):?>
						<option value="<?=$report?>"><?=$report::DisplayName()?></option>
						<? endforeach; ?>
					</select>
					<input value="Los --&gt;" type="submit">
				</form>
			</td>
			<td valign="top" align="right">
				<a href="https://github.com/PiratenLSA/piratenmap/blob/master/README.md">README</a>.
				Ein Projekt von <a href="https://twitter.com/martok_sh">@martok_sh</a>. (<a href="mailto:webmaster@martoks-place.de">Mail</a>)</td>
		</tr>
		<tr>
			<td style="width: 300px;"><iframe src="about:blank" style="width: 100%; height: 100%;" name="configure"></iframe></td>
			<td colspan="2"><iframe src="about:blank" style="width: 100%; height: 100%;" name="view"></iframe></td>
		</tr>
	</table>
</body>
</html>