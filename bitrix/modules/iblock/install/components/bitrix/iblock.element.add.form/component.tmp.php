<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (CModule::IncludeModule("iblock"))
{
	$arParams["ID"] = intval($_REQUEST["CODE"]);
	//echo "<pre>"; print_r($arParams); echo "</pre>";

	$arGroups = $USER->GetUserGroupArray();

	//echo "<pre>"; print_r($arGroups); echo "</pre>";
	//echo "<pre>"; print_r($arParams["GROUPS"]); echo "</pre>";

	// check whether current user can have access to add/edit elements
	if ($USER->IsAdmin() || ($arParams["ELEMENT_ASSOC"] != "N"))
	{
		$bAllowAccess = true;
	}
	else
	{
		$bAllowAccess = false;
	}

	// adding and editing rights validation
	if ($arParams["ID"] == 0)
	{
		$bAllowAccess &= count(array_intersect($arGroups, $arParams["GROUPS"])) > 0;
	}
	else
	{
		// rights for editing current element will be in element get filter
		$bAllowAccess &= $USER->GetID() > 0;
	}

	//echo $bAllowAccess ? "access granted" : "access denied";

	if ($bAllowAccess)
	{
		$rsIBLockPropertyList = CIBlockProperty::GetList(array("sort"=>"asc", "name"=>"asc"), array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arParams["IBLOCK_ID"]));
		$arIBlockPropertyList = array(
			"PREVIEW_TEXT" => array(
				"PROPERTY_TYPE" => "T",
				"MULTIPLE" => "N",
			),
			"DETAIL_TEXT" => array(
				"PROPERTY_TYPE" => "T",
				"MULTIPLE" => "N",
			),
			"PREVIEW_PICTURE" => array(
				"PROPERTY_TYPE" => "F",
				"MULTIPLE" => "N",
			),
			"DETAIL_PICTURE" => array(
				"PROPERTY_TYPE" => "F",
				"MULTIPLE" => "N",
			),
		);

		// get properties list
		while ($arProperty = $rsIBLockPropertyList->Fetch())
		{
			// get list of property enum values
			if ($arProperty["PROPERTY_TYPE"] == "L") 
			{
				$rsPropertyEnum = CIBlockProperty::GetPropertyEnum($arProperty["ID"]);
				$arProperty["ENUM"] = array();
				while ($arPropertyEnum = $rsPropertyEnum->Fetch())
				{
					$arProperty["ENUM"][$arPropertyEnum["ID"]] = $arPropertyEnum;
				}
			}
			
			$arIBlockPropertyList[$arProperty["ID"]] = $arProperty;
		}

		if ($arParams["ID"] > 0) 
		{
			// set starting filter value
			$arFilter = array("IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"], "IBLOCK_ID" => $arParams["IBLOCK_ID"]);
			
			$arFilter["ID"] = $arParams["ID"];

			// check type of user association to iblock elements and add user association to filter
			if ($arParams["ELEMENT_ASSOC"] == "PROPERTY_ID" && strlen($arParams["ELEMENT_ASSOC_PROPERTY"] > 0) && is_array($arIBlockPropertyList[$arParams["ELEMENT_ASSOC_PROPERTY"]]))
			{
				$arFilter["PROPERTY_".$arParams["ELEMENT_ASSOC_PROPERTY"]] = $USER->GetID();
			}
			elseif ($USER->GetID())
			{
				$arFilter["CREATED_BY"] = $USER->GetID();
			}
			// additional bugcheck. situation can be found when property ELEMENT_ASSOC_PROPERTY does not exists and user can be is not registered
			else
			{
				$arFilter["ID"] = -1;
			}
			
			$rsIBlockElements = CIBlockElement::GetList(array("SORT" => "ASC"), $arFilter);		
			
			// get elements list using generated filter
			if ($arElement = $rsIBlockElements->Fetch())
			{
				$arResult["PROPERTIES_FULL"] = $arIBlockPropertyList;
				
				$rsElementProperties = CIBlockElement::GetProperty($arParams["IBLOCK_ID"], $arParams["ID"]);
				$arResult["ELEMENT"] = $arElement;
				$arResult["ELEMENT_PROPERTIES"] = array();
				$arResult["ELEMENT_PROPERTIES_FULL"] = array();
				while ($arElementProperty = $rsElementProperties->Fetch())
				{
					if (in_array($arElementProperty["ID"], $arParams["PROPERTY_CODES"])) 
						$arResult["ELEMENT_PROPERTIES"][] = $arElementProperty;
						
					$arResult["ELEMENT_PROPERTIES_FULL"][] = $arElementProperty;			
				}
				
				$this->IncludeComponentTemplate();			
			}
			else
			{
					echo ShowError(GetMessage("IBLOCK_ADD_ELEMENT_NOT_FOUND"));
			}
		}
		else
		{
			$this->IncludeComponentTemplate();
		}
	}
}
?>