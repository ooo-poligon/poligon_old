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
	if ($ar_props = $db_props->Fetch()){ echo CFile::ShowImage($ar_props["VALUE"], 200, 200, "border=0 align=left", "", true);}
	echo '<div><a href="'.$mass[$i]["DETAIL_PAGE_URL"].'">'.$mass[$i]["NAME"].'</a></div>'.$mass[$i]["PREVIEW_TEXT"];
}
?>


<!--
					<img src="/bitrix/templates/poligon/images/img.jpg" align="left" alt="" />
					<div><a href="#">Какая-то очень крутая штука</a></div>
					Ритмическая организованность таких стихов не всегда очевидна при чтении "про себя", но полифонический роман
-->
