<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CNewsTools
{
	function OnSearchGetURL($item)
	{
		global $BX_NEWS_DETAIL_URL;
		if(strpos($BX_NEWS_DETAIL_URL, "#SECTION_ID#")!==false)
		{
			$rsElements = CIBlockElement::GetList(array(), array("ID"=>$item["ITEM_ID"]), false, false, array("IBLOCK_SECTION_ID"));
			$arElement = $rsElements->Fetch();
		}
		else
			$arElement = false;
		if(!is_array($arElement))
			$arElement = array("IBLOCK_SECTION_ID"=>0);
		return str_replace(
				array("#SERVER_NAME#", "#SITE_DIR#", "#IBLOCK_ID#", "#SECTION_ID#", "#ELEMENT_ID#"),
				array(SITE_SERVER_NAME, SITE_DIR, $item["IBLOCK_ID"], $arElement["IBLOCK_SECTION_ID"], $item["ITEM_ID"]),
				$BX_NEWS_DETAIL_URL
		);
	}
}
?>
