<?
if($_GET['action'] == 'order'
	&& $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
){
	
$officeMails = array('elcomp@poligon.info', 'it@poligon.info', 'dolgova@poligon.info', 'gnato@poligon.info');

foreach ($_POST as $key => $value)
	if($value == '')
		$_POST[$key] = '�� �������';
	else
		$_POST[$key] = iconv('UTF-8', 'WINDOWS-1251', $value);
		
$officeMsg  = "
<h1 style='font-size: 14px;'>��������� ������ �� ����� http://poligon.info/</h1>
<h2 style='font-size: 14px;'>��������� ������:</h2> 
<dl>
	<dt>�����: </dt>
	<dd><pre>{$_POST['order']}</pre></dd>
	<dt>��������: </dt>
	<dd>{$_POST['company']}</dd>	
	<dt>���: </dt>
	<dd>{$_POST['name']}</dd>
	<dt>email: </dt>
	<dd>{$_POST['email']}</dd>
	<dt>�������: </dt>
	<dd>{$_POST['telephone']}</dd>
</dl>";

$subject = '����� ����������� ����������� �� http://poligon.info';

/* ��� �������� HTML-����� �� ������ ���������� ����� Content-type. */
$headers= "MIME-Version: 1.0\n";
$headers .= "Content-type: text/html; charset=windows-1251\n";

/* �������������� ����� */
$headers .= "From: poligon.info <website@poligon.info>\n";
foreach($officeMails as $officeMail)
	mail($officeMail, $subject, $officeMsg, $headers);
	
if($_POST['email'] != ''){
	mail($_POST['email'], $subject, $officeMsg, $headers);
}
die(1);	
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("����������� ���������� FARNELL");
$APPLICATION->SetPageProperty("description", "����� ����������� ����������� �� �������� FARNELL, ����� �������� �� 3 ������");
?><style type="text/css">
.style12 {font-size: 14px; font-weight: bold; color: #FFFFFF; }

#farnell h3{
	margin: 6px 0px	0px	8px;
	font-size: 15px;
}
#farnell{
	background-color: silver;
	border: 1px black solid;
	#border-radius: 8px;
	width: 500px;
	height: 210px;
}
#farnell form textarea {
	width: 274px;
	height: 150px;
}
label {
	display: inline-block;
	width: 32px;
}
label[for=order]{
	float: left;
	display: block;
	width: 10em;
}
label[for=company] {
	background: url("/images/icons/application_home.png") center no-repeat;
}

label[for=name] {
	background: url('/images/icons/user_suit.png') center no-repeat;
}
label[for=email] {
	background: url('/images/icons/email.png') center no-repeat;
	border: 1px dotted black;
	margin: -1px;
}
label[for=telephone] {
	background: url('/images/icons/phone.png') center no-repeat;
}

#farnell p {
	margin: 0px;
}
#farnell input {
	margin: 6px;
	width: 142px;
	height: 16px;
}
#farnell input[type=image]{
	width: 150px;
	height: 24px;
	margin-left: 36px;
}

</style>
<script>
<!--
$(function(){
	$('#farnell form').bind('submit', function(){
		if($('#farnell textarea').val() == ''){
			$('label[for=order]').css({'background': 'red'});
			$('#farnell textarea').css('border', '1px red solid');
			return false;
		}else{
			$('label[for=order]').css({'background': 'silver'});
			$('#farnell textarea').css('border', 'none');
		}
		if($('input#email').val() == ''){
			$('label[for=email]').css('background-color', 'red');
			return false;	
		}else{
			$.post('/special/farnell.php?action=order', {
					order: $('#order').val(),
					name: $('#name').val(),
					company: $('#company').val(),
					email: $('#email').val(),
					telephone: $('#telephone').val()
				 }, function(data){
				$('<p style="margin: 24px;">���� ������ ����������. ����� ������ �������������� ����������: �&nbsp;9.30-18.00 �� ������� ����, ������� � 9.30-17.00. ������ ����� ���������� � �� �������� � ����.</p>').replaceAll('#farnell form');
			});			
		}
		return false;		
	});
});
//-->
</script>

<?php 
/**
 * ��������� �����
 */ 

?>
<img src="/images/farnell_logo.gif" alt="������� Farnell" style="float: right;"/>
<h3><strong>����������� �������� ������� �� �������� FARNELL</strong></h3>
<p><a href="/upload/FARNELL_current_stock.xls"><i>������� ������� �� ������.</i></a></p>
<p>(������ ��������������� ������ ����������� �����)</p>
<img style="float: right; width: 114px; height: 180px;" src="/images/farnell_catalogue.gif" alt="������� ����������� ����������� Farnell"/>
<div id="farnell">
<h3>������������ ����� Farnell</h3>
<form method="post">
<div style="float: left; margin: 0px 8px; width: 274px;">
<label for="order">���� ��������: </label><br/>
<textarea rows="10" cols="32" name="order" id="order"></textarea>
</div>
<div style="width: 210px; float: left;">
<p>��������� �� ����: </p>
<label for="company">&nbsp;</label><input type="text" name="company" id="company" placeholder="��������"/><br/>
<label for="name">&nbsp;</label><input type="text" name="name" id="name" placeholder="���"/><br/>
<label for="email">&nbsp;</label><input type="email" name="email" id="email" placeholder="email*" required="true"/><br/>
<label for="telephone">&nbsp;</label><input type="text" name="phone" id="telephone" placeholder="�������"/><br/>
<input type="image" name="image" src="/images/farnell-order.png" />
</div>
</form>
</div>
<p><strong>��� ���������� ������ ������ ���������:</strong></p>
<ul>
	<li> <strong>������������ �������</strong>, �����-��� Farnell (���� ��������), <strong>��������� ����������</strong>;</li>
	<li> <strong>������ ��������</strong> (���������, ������������, ��������-�����);</li>
	<li> <strong>��������� ��� ����������� �����</strong>;</li>
	<li> <strong>����� �������� � ���������� ������</strong>. </li>
</ul>

<p><i><a href="/content/program/delivery.php">������� ��������</a>: "������������", "����-��������".</i></p>
<table width="100%" border="0" cellpadding="0" cellspacing="3">
<tr>
<td background="/images/bg_circuit_fw.gif" bgcolor="#000000">
	<div align="center"><span class="style12">�������� ����������� ����������� � �������������� �� �������� Farnell</span>
	</div>
</td>
</tr>
</table>
<br/>
<table border="0" cellspacing="0" cellpadding="0">
<tr>
	<td>
		<p>�������� Farnell, ���������� � 1939 ����, �������� ������� ������� � ������� �������� 
		<a href="/catalog/index.php?SECTION_ID=13">����������� �����������</a>, �������������������, ������������� ������������, ��������� ��� ������������ ������������ � �������. 
		Farnell �������� ������� <strong>������� �������������� ����������� �����������</strong> � ����������� �������.</p>
		<p>����� <b>510 000</b> ������� �� <b>800 �����������</b>��������� �������������� �� ������� Farnell. 
		������������ ��������� ����������� (����� 50 000 ������������ � ���).
		� ������ Farnell ��� �� ������ �������� Newark � CPC, ���������� ������������ ��������.
		<br /><img src="/images/cpc_logo.gif" alt="CPC logo" width="80" height="49"><img src="/images/newark_logo.gif" alt="Newark logo" width="101" height="37"></p>
	</td>
</tr>
</table>
<br/>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td height="38" align="center" background="/images/bg_circuit_fw.gif" bgcolor="#000000" class="style12"><strong>�������� ������� ����� 14 ��� ���������� ��������� �� ������� Farnell</strong></td>
	</tr>
	<tr>
		<td>
			<ul>
				<li>���� �������� - 3-4 ������</li>
				<li>������ ��������, �������� ������������ ���������</li>
				<li>�������� ����������� �������� � ���������� ������� </li>
				<li>����������� <a href="/content/program/delivery.php">�������� �� ������</a> (������������, ��������-�����)</li>
			</ul>
		</td>
</tr>
</table>
<br/>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr bgcolor="#000000" background="/images/bg_circuit_fw.gif">
		<td height="38" colspan="2" align="center"><span class="style12"><strong>������ ����������� ����������� � �������, ������������ �� �������� Farnell: </strong></span></td>
	</tr>
	<tr>
	<td>
		<ul class="mark px400">
			<li><a href="/catalog/index.php?SECTION_ID=5232">���������� ���������</a></li>
			<li><a href="/catalog/index.php?SECTION_ID=5205">�����, �����������, ���������, ������� ������</a></li>
			<li>��������������� ��������</li>
			<li>���������, ������������, �������������</li>
			<li>������, ����������, ����������</li>
			<li>�����������, �������, ��-�������</li>
			<li>������, ������, ��������� ������</li>
			<li>��������������� ����������</li>
			<li>��������� �������, �������������� </li>
			<li>������������, ����������, ������</li>
			<li>�������, ����������, �������� �����������</li>
			<li>�������, �������� ���������</li>
			<li>����, ����������, �������������� ���������� </li>
			<li>�����������, �������� ������������� </li>
			<li>�����������, �������������, ������</li>
			<li>�������, ������</li>
			<li>����������������, ������ </li>
			<li>������������ � �������� ������������ </li>
			<li>����������</li>
			<li>����������</li>
			<li>��������� ��������� </li>
			<li>������</li>
		</ul>
	</td>
	<td valign="top"><img src="/images/farnell_elcomp.jpg" alt="����������� ���������� �� �������� Farnell" width="200" height="229">
	<img src="/images/farnell-2.gif" width="60" height="68"> <img src="/images/farnell-3.gif" width="60" height="68"><img src="/images/farnell-4.gif" width="60" height="68"><img src="/images/farnell-5.gif" width="60" height="68"><img src="/images/farnell-6.gif" width="60" height="68">
	<img src="/images/farnell-7.gif" width="60" height="68"><img src="/images/farnell-8.gif" width="60" height="68"><img src="/images/farnell-9.gif" width="60" height="68"><img src="/images/farnell-10.gif" width="60" height="68">
	<img src="/images/farnell-11.gif" width="60" height="68"><img src="/images/farnell-12.gif" width="60" height="68"><img src="/images/farnell-13.gif" width="60" height="68"></td>
	</tr>
</table>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td height="38" align="center" background="/images/bg_circuit_fw.gif" bgcolor="#000000"><span class="style12"><strong>����� �������� ������� �� �������� Farnell ��� ������ � ����������� �������� �������:</strong></span></td>
	</tr>
	<tr>
	<td>
		<ol>
			<li>��������� �� ��������������� ��������/����� (812) 3254220, 3256420 (����� ����������) </li>
			<li>��������� <a href="#">����� ������</a> �� ����� (��������� ��������� �������� � ���� ��. ����� �������������� ���������)</li>
		</ol>

		<p>�� ���������� ���������� �� ���� ������, �������� ����,���������� ��������.</p>
	</td>
</tr>
</table>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>