<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(CModule::IncludeModule("iblock"))
{
	$arFilter = Array("IBLOCK_ID"=>4, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "PROPERTY_14_VALUE"=>1);
	$res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
	while($ar_res = $res->GetNext())
	{
		$mass[] = $ar_res;
	}
//	var_dump($ar_props);
	$i = rand(0,count($mass)-1);
	$db_props = CIBlockElement::GetProperty(4, $mass[$i]["ID"], "sort", "asc", Array("CODE"=>"link"));
	echo '<div style="display:block"><a href="'.$mass[$i]["DETAIL_PAGE_URL"].'">';
	if ($ar_props = $db_props->Fetch()){ 
		if ($ar_props["VALUE"])  echo '<img align="left" height="50" src="'.$ar_props["VALUE"].'">';
//		echo CFile::ShowImage($ar_props["VALUE"], '', '', "border=0 align=left height=50", "", true);
	}
	echo $mass[$i]["NAME"].'</a></div>'.$mass[$i]["PREVIEW_TEXT"];
}
?>


<!--
					<img src="/bitrix/templates/poligon/images/img.jpg" align="left" alt="" />
					<div><a href="#">�����-�� ����� ������ �����</a></div>
					����������� ���������������� ����� ������ �� ������ �������� ��� ������ "��� ����", �� �������������� �����
-->
