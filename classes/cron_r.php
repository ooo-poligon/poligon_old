<?php 
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
if (PHP_SAPI == 'cli'){
	$DBType = "mysql";
	$DBHost = "localhost";
	$DBLogin = "poliinfo_bitrix";
	$DBPassword = "Y2Gd75q";
	$DBName = "poliinfo_bitrix";
	mysql_connect($DBHost, $DBLogin, $DBPassword); 
	mysql_selectdb($DBName);
	$query = "SELECT * FROM `download_pdf`
		WHERE 1
		AND `datetime` > '".date('Y-m-d', time()-24*60*60*7)."'";
//	print $query;
	
	$site = "poligon.info";
	$content = $rows = null;
	$content = "<p>Файлы pdf загруженные пользователями с сайта {$site} за прошедшую неделю: </p>\n\n";
	$content.= "<table><thead>";
	$content.= "<tr>
		<th>файл</th>
		<th>страница</th>
		<th>время</th>
	</tr>";
	$content.= "</thead>";
	$data = mysql_query($query);
	$num = mysql_num_rows($data);
	
	$files = $pages = array();
	while($row = mysql_fetch_assoc($data)){
		if(!in_array($row['file'], $files))
			$files[] = $row['file'];
		if(!in_array($row['page'], $pages))
			$pages[] = $row['page'];

		$rows.= "<tr>
					<td>{$row['file']}</td>
					<td>{$row['page']}</td>
					<td>{$row['datetime']}</td>
				</tr>\n";
	}
	$content.= "<tfoot>
		<tr>
			<th>Разных файлов: </th>
			<th>Уникальных страниц: </th>
			<th>Всего загрузок:</th>
		</tr>
		<tr>
			<td>".count($files)."</td>
			<td>".count($pages)."</td>
			<td>{$num}</td>
		</tr>
	</tfoot>";
	$content.= "<tbody>";
	$content.= $rows;
	$content.= "</tbody></table>";

	$content.= "<h2>csv-данные</h2>";
	$content.= "<pre>\nфайл;страница;время;\n";
	$data = mysql_query($query);
	while($row = mysql_fetch_assoc($data))
		$content.= "{$row['file']};{$row['page']};{$row['datetime']};\n";
		
	
	$officeMails = array('gnato@poligon.info', 'it@poligon.info', 'Kruten@poligon.info');
	$subject = 'загрузка pdf-файлов с сайта poligon.info';

	/* Для отправки HTML-почты вы можете установить шапку Content-type. */
	$headers= "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/html; charset=windows-1251\n";

	/* дополнительные шапки */
	$headers .= "From: poligon.info <website@poligon.info>\n";
	foreach($officeMails as $officeMail)
		mail($officeMail, $subject, $content, $headers);
}else{
	die;
}