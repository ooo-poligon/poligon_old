<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("������ �������");
?> 
<p>�� �������� <b>��������� ������������</b> ������������ ����� ����������� ������������� ������ ������, ��������������� ����������, ���������� � ������ � �. �. ����� ������ ����� ����������� � ������� ���������� <i>��������� ������������</i>.</p>

<p><?$APPLICATION->IncludeComponent(
	"bitrix:menu",
	"",
	Array(
		"ROOT_MENU_TYPE" => "left", 
		"MAX_LEVEL" => "1", 
		"CHILD_MENU_TYPE" => "left", 
		"USE_EXT" => "N" 
	)
);?> </p>
 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>