<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(CModule::IncludeModule("iblock"))
{
	$arFilter = Array("IBLOCK_ID"=>8, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
	$res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
	while($ar_res = $res->GetNext())
	{		
		$mass[] = $ar_res;
	}
	if(!$showAll){
		$i = rand(0,count($mass)-1);
		
		$db_props = CIBlockElement::GetProperty(8, $mass[$i]["ID"], "sort", "asc", Array("CODE"=>"picture"));
		$db_props1 = CIBlockElement::GetProperty(8, $mass[$i]["ID"], "sort", "asc", Array("CODE"=>"link"));
		echo '<div style="display:block">';	
		if ($ar_props1 = $db_props1->Fetch()){ 
			echo '<a href="'.str_replace('&', '&amp;', $ar_props1["VALUE"]).'">';
		}
		if ($ar_props = $db_props->Fetch()){ 
			if ($ar_props["VALUE"])  echo '<img style="text-align: left;" height="50" src="'.$ar_props["VALUE"].'" alt="" />';
		}
		require_once "{$_SERVER['DOCUMENT_ROOT']}/functions.php";
		$bannerText = $mass[$i]["PREVIEW_TEXT"];		
		$bannerText = parseForDynamicContent($mass[$i]["PREVIEW_TEXT"]);
		echo $mass[$i]["NAME"].'</a></div>'.$bannerText;

	}else{
		foreach ($mass as $item){
//			var_dump($item);
			$db_props = CIBlockElement::GetProperty(8, $item["ID"], "sort", "asc", Array("CODE"=>"picture"));
			$db_props1 = CIBlockElement::GetProperty(8, $item["ID"], "sort", "asc", Array("CODE"=>"link"));
			print '<div style="background: #dddddd;">';
			if ($ar_props = $db_props->Fetch()){ 
				if ($ar_props["VALUE"])  echo '<div style="float: right;"><img height="55" src="'.$ar_props["VALUE"].'" alt="'.$item["NAME"].'" /></div>';
			}			
			if ($ar_props1 = $db_props1->Fetch()){ 
				print '<h3><a href="'.str_replace('&', '&amp;', $ar_props1["VALUE"]).'">'.$item["NAME"].'</a></h3>';
			}
			print '<p style="font-style: italic;">'.$item["PREVIEW_TEXT"].'</p>';
			print '<div>'.$item['DETAIL_TEXT'].'</div>';
			print '</div>';
		}
	}
}
?>

