<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<!--<script type="text/javascript" src="/bitrix/templates/poligon/js/ajax.js"></script>-->
<?
$res = CIBlockSection::GetByID($arResult["IBLOCK_SECTION_ID"]);
if($ar_res = $res->GetNext())
  $section_name = $ar_res['NAME'];
$res1 = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$arResult["IBLOCK_ID"], "ID"=>$ar_res["IBLOCK_SECTION_ID"]), false, array("NAME","UF_PROIZV"));
if($arSect = $res1->GetNext()){ 
	$proizv = $arSect["UF_PROIZV"];
	$section_name2 = $arSect['NAME'];
}

$title_main = $section_name2.' '.$proizv.' > '.$section_name.' > '.$arResult["NAME"];
$img_alt = $section_name2.' '.$proizv.' '.$arResult["NAME"];
$APPLICATION->SetTitle($title_main);?>
<div>
	<?
	$APPLICATION->IncludeFile( // включаем файл сообщающий об остатках и ценах
		$APPLICATION->GetTemplatePath("include_areas/onStore.php"),
		Array("arResult" => $arResult),
		Array("MODE"=>"php"));			
	?>
</div>