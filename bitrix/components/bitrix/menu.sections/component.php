<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

//if($GLOBALS["APPLICATION"]->GetShowIncludeAreas() && $USER->IsAdmin())
//	echo "<br />";

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;
if($arParams["CACHE_TYPE"] == "N" || $arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "N")
	$arParams["CACHE_TIME"] = 0;

$arParams["ID"] = intval($arParams["ID"]);
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);

$arParams["DEPTH_LEVEL"] = intval($arParams["DEPTH_LEVEL"]);
if($arParams["DEPTH_LEVEL"]<=0)
	$arParams["DEPTH_LEVEL"]=1;

$aMenuLinksNew = array();

$CACHE_ID = __FILE__.$arParams["IBLOCK_ID"].$arParams["DEPTH_LEVEL"];
$obMenuCache = new CPHPCache;
if($obMenuCache->StartDataCache($arParams["CACHE_TIME"], $CACHE_ID, "/".SITE_ID.$componentPath))
{
	$arSections = array();
	$arElementLinks = array();
	if(CModule::IncludeModule("iblock"))
	{
		$arFilter = array(
			"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
			"ACTIVE"=>"Y",
			"IBLOCK_ACTIVE"=>"Y",
			"<="."DEPTH_LEVEL" => $arParams["DEPTH_LEVEL"],
		);
		$arOrder = array(
			"left_margin"=>"asc",
		);
		$rsSections = CIBlockSection::GetList($arOrder, $arFilter);
		while ($arSection = $rsSections->Fetch())
		{
			$arSections[]=$arSection;
			$arElementLinks[$arSection["ID"]] = array();
		}
	}
	$obMenuCache->EndDataCache(Array("arSections" => $arSections, "arElementLinks"=>$arElementLinks));
}
else
{
	$arVars = $obMenuCache->GetVars();
	$arSections = $arVars["arSections"];
	$arElementLinks = $arVars["arElementLinks"];
}

if(CModule::IncludeModule("iblock"))
{
	//In "SEF" mode we'll try to parse URL and get ELEMENT_ID from it
	if($arParams["IS_SEF"] === "Y")
	{
		$componentPage = CComponentEngine::ParseComponentPath(
			$arParams["SEF_BASE_URL"],
			array(
				"section" => $arParams["SECTION_PAGE_URL"],
				"detail" => $arParams["DETAIL_PAGE_URL"],
			),
			$arVariables
		);
		if($componentPage === "detail")
		{
			CComponentEngine::InitComponentVariables(
				$componentPage,
				array("SECTION_ID", "ELEMENT_ID"),
				array(
					"section" => array("SECTION_ID" => "SECTION_ID"),
					"detail" => array("SECTION_ID" => "SECTION_ID", "ELEMENT_ID" => "ELEMENT_ID"),
				),
				$arVariables
			);
			$arParams["ID"] = intval($arVariables["ELEMENT_ID"]);
		}
	}

	if(($arParams["ID"] > 0) && (intval($arVariables["SECTION_ID"]) <= 0))
	{
		$arSelect = array("ID", "IBLOCK_ID", "DETAIL_PAGE_URL", "IBLOCK_SECTION_ID");
		$arFilter = array(
			"ID" => $arParams["ID"],
			"ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		);
		if($rsElements = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect))
		{
			while($arElement = $rsElements->GetNext())
			{
				if(($arParams["IS_SEF"] === "Y") && (strlen($arParams["DETAIL_PAGE_URL"]) > 0))
				{
					$arElement["DETAIL_PAGE_URL"] = str_replace(
						array("#SERVER_NAME#", "#SITE_DIR#", "#IBLOCK_ID#", "#SECTION_ID#", "#ELEMENT_ID#"),
						array(SITE_SERVER_NAME, SITE_DIR, $arElement["IBLOCK_ID"], $arElement["IBLOCK_SECTION_ID"], $arElement["ID"]),
						$arParams["SEF_BASE_URL"].$arParams["DETAIL_PAGE_URL"]
					);
				}
				$arElementLinks[$arElement["IBLOCK_SECTION_ID"]][] = $arElement["DETAIL_PAGE_URL"];
			}
		}
	}
}

foreach($arSections as $arSection)
{
	if($arParams["IS_SEF"] !== "Y")
		$section_url = $arParams["SECTION_URL"];
	else
		$section_url = $arParams["SEF_BASE_URL"].$arParams["SECTION_PAGE_URL"];

	$aMenuLinksNew[] = array(
		$arSection["NAME"],
		str_replace(
			array("#SECTION_ID#", "#ID#", "#SITE_DIR#"),
			array($arSection["ID"], $arSection["ID"], SITE_DIR),
			$section_url
		),
		$arElementLinks[$arSection["ID"]],
		array(
			"FROM_IBLOCK" => true,
			"IS_PARENT" => ( ($arSection["RIGHT_MARGIN"] - $arSection["LEFT_MARGIN"]) > 1 && ($arSection["DEPTH_LEVEL"] < $arParams["DEPTH_LEVEL"]) ),
			"DEPTH_LEVEL" => $arSection["DEPTH_LEVEL"],
		),
	);
}

return $aMenuLinksNew;
?>
