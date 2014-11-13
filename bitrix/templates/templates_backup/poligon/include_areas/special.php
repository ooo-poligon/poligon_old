<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(CModule::IncludeModule("iblock"))
{
	$i=0;
        $arFilter = Array("IBLOCK_ID"=>8, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
	$res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
	while($ar_res = $res->GetNext())
	{
		$mass[] = $ar_res;
	}
//	var_dump($ar_props);
	$i = rand(0,count($mass)-1);
	$db_props = CIBlockElement::GetProperty(8, $mass[$i]["ID"], "sort", "asc", Array("CODE"=>"picture"));
	$db_props1 = CIBlockElement::GetProperty(8, $mass[$i]["ID"], "sort", "asc", Array("CODE"=>"link"));
	echo '<div style="display:block">';	
	if ($ar_props1 = $db_props1->Fetch()){ 
		echo '<a href="'.str_ireplace('&', '&amp;', $ar_props1["VALUE"]).'">';
	}
	if ($ar_props = $db_props->Fetch()){ 
		if ($ar_props["VALUE"])  echo '<img style="text-align: left;" height="50" src="'.$ar_props["VALUE"].'" alt="" />';
	}
	echo $mass[$i]["NAME"].'</a></div>'.$mass[$i]["PREVIEW_TEXT"];
}
?>
