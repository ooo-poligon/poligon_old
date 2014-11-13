<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

/*
$CATALOG_RIGHT = $APPLICATION->GetGroupRight("catalog");
if ($CATALOG_RIGHT=="D") 
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
*/

if (!$USER->CanDoOperation('catalog_import_exec'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/include.php");

if ($ex = $APPLICATION->GetException())
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");
	
	$strError = $ex->GetString();
	ShowError($strError);
	
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/csv_data.php");

include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/lang/", "/data_import_new.php"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

set_time_limit(0);
$STEP = IntVal($STEP);
if ($STEP <= 0)
	$STEP = 1;
if ($REQUEST_METHOD == "POST" && strlen($backButton) > 0)
	$STEP = $STEP - 2;

$NUM_CATALOG_LEVELS = IntVal(COption::GetOptionString("catalog", "num_catalog_levels", 3));

$max_execution_time = IntVal($max_execution_time);
if ($max_execution_time <= 0)
	$max_execution_time = 0;

if (strlen($CUR_LOAD_SESS_ID) <= 0)
	$CUR_LOAD_SESS_ID = "CL".time();
$bAllLinesLoaded = True;
$CUR_FILE_POS = IntVal($CUR_FILE_POS);

/////////////////////////////////////////////////////////////////////

function GetValueByCodeTmp($code)
{
	global $NUM_FIELDS;
	for ($i = 0; $i < $NUM_FIELDS; $i++)
	{
		if ($GLOBALS["field_".$i] == $code)
		{
			return $i;
		}
	}
	return -1;
}

/////////////////////////////////////////////////////////////////////

if ($REQUEST_METHOD == "GET" && strlen($delscheme) > 0)
{
	CCatalogLoad::Delete(base64_decode($delscheme));
}

/////////////////////////////////////////////////////////////////////
if (($REQUEST_METHOD == "POST" || $CUR_FILE_POS > 0) && $STEP > 1 /*&& $CATALOG_RIGHT == "W"*/)
{
	//*****************************************************************//
	if ($STEP > 1)
	{
		//*****************************************************************//
		$DATA_FILE_NAME = "";

		if (is_uploaded_file($_FILES["DATA_FILE"]["tmp_name"]))
		{
			// Сделать проверку на тип CSV!
			copy($_FILES["DATA_FILE"]["tmp_name"], $_SERVER["DOCUMENT_ROOT"]."/upload/".$_FILES["DATA_FILE"]["name"]);
			$DATA_FILE_NAME = "/upload/".$_FILES["DATA_FILE"]["name"];
		}

		if (strlen($DATA_FILE_NAME) <= 0)
		{
			if (strlen($URL_DATA_FILE) > 0 && file_exists($_SERVER["DOCUMENT_ROOT"].$URL_DATA_FILE) && is_file($_SERVER["DOCUMENT_ROOT"].$URL_DATA_FILE) && $APPLICATION->GetFileAccessPermission($URL_DATA_FILE)>="W")
				$DATA_FILE_NAME = $URL_DATA_FILE;
		}

		if (strlen($DATA_FILE_NAME) <= 0)
			$strError .= GetMessage("CATI_NO_DATA_FILE")."<br>";

		$IBLOCK_ID = IntVal($IBLOCK_ID);
		$arIBlockres = CIBlock::GetList(Array("sort"=>"asc"), Array("ID"=>IntVal($IBLOCK_ID)));
		$arIBlockres = new CIBlockResult($arIBlockres);
		if ($IBLOCK_ID <= 0 || !($arIBlock = $arIBlockres->GetNext()))
			$strError .= GetMessage("CATI_NO_IBLOCK")."<br>";

		if (strlen($strError) <= 0)
		{
			$bIBlockIsCatalog = False;
			if (CCatalog::GetByID($IBLOCK_ID))
				$bIBlockIsCatalog = True;

			if (strlen($LOAD_SCHEME) > 0)
			{
				$load_schs = CCatalogLoad::GetList(array(), array("NAME" => $LOAD_SCHEME, "TYPE" => "I"));
				if ($load_schs->ExtractFields("f_"))
				{
					parse_str($f_VALUE);
					$STEP = 4;
					CCatalogLoad::SetLastUsed($LOAD_SCHEME, "I");
				}
			}

			if ($CUR_FILE_POS > 0 && is_set($_SESSION, $CUR_LOAD_SESS_ID) && is_set($_SESSION[$CUR_LOAD_SESS_ID], "LOAD_SCHEME"))
			{
				parse_str($_SESSION[$CUR_LOAD_SESS_ID]["LOAD_SCHEME"]);
				$STEP = 4;
			}
		}

		if (strlen($strError)>0)
		{
			$STEP = 1;
		}
		//*****************************************************************//
	}

	if ($STEP > 2)
	{
		//*****************************************************************//
		$csvFile = new CCSVData();
		$csvFile->LoadFile($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME);

		if ($fields_type!="F" && $fields_type!="R")
			$strError .= GetMessage("CATI_NO_FILE_FORMAT")."<br>";

		$arDataFileFields = array();
		if (strlen($strError)<=0)
		{
			$fields_type = (($fields_type=="F") ? "F" : "R" );

			$csvFile->SetFieldsType($fields_type);

			if ($fields_type == "R")
			{
				$first_names_r = (($first_names_r=="Y") ? "Y" : "N" );
				$csvFile->SetFirstHeader(($first_names_r=="Y") ? true : false);

				$delimiter_r_char = "";
				switch ($delimiter_r)
				{
					case "TAB":
						$delimiter_r_char = "\t";
						break;
					case "ZPT":
						$delimiter_r_char = ",";
						break;
					case "SPS":
						$delimiter_r_char = " ";
						break;
					case "OTR":
						$delimiter_r_char = substr($delimiter_other_r, 0, 1);
						break;
					case "TZP":
						$delimiter_r_char = ";";
						break;
				}

				if (strlen($delimiter_r_char) != 1)
					$strError .= GetMessage("CATI_NO_DELIMITER")."<br>";

				if (strlen($strError) <= 0)
				{
					$csvFile->SetDelimiter($delimiter_r_char);
				}
			}
			else
			{
				$first_names_f = (($first_names_f=="Y") ? "Y" : "N" );
				$csvFile->SetFirstHeader(($first_names_f=="Y") ? true : false);

				if (strlen($metki_f) <= 0)
					$strError .= GetMessage("CATI_NO_METKI")."<br>";

				if (strlen($strError) <= 0)
				{
					$arMetkiTmp = preg_split("/[\D]/i", $metki_f);

					$arMetki = array();
					for ($i = 0; $i < count($arMetkiTmp); $i++)
					{
						if (IntVal($arMetkiTmp[$i]) > 0)
						{
							$arMetki[] = IntVal($arMetkiTmp[$i]);
						}
					}

					if (!is_array($arMetki) || count($arMetki)<1)
						$strError .= GetMessage("CATI_NO_METKI")."<br>";

					if (strlen($strError)<=0)
					{
						$csvFile->SetWidthMap($arMetki);
					}

				}
			}

			if (strlen($strError)<=0)
			{
				$bFirstHeaderTmp = $csvFile->GetFirstHeader();
				$csvFile->SetFirstHeader(false);
				if ($arRes = $csvFile->Fetch())
				{
					for ($i = 0; $i < count($arRes); $i++)
					{
						$arDataFileFields[$i] = $arRes[$i];
					}
				}
				else
				{
					$strError .= GetMessage("CATI_NO_DATA")."<br>";
				}
				$NUM_FIELDS = count($arDataFileFields);
			}
		}

		if (strlen($strError)>0)
		{
			$STEP = 2;
		}
		//*****************************************************************//
	}

	if ($STEP>3)
	{
		//*****************************************************************//
		if (strlen($NEW_SCHEME_NAME)>0)
		{
			$load_schs = CCatalogLoad::GetList(array(), array("NAME" => $NEW_SCHEME_NAME, "TYPE" => "I"));
			if ($load_sch = $load_schs->Fetch())
			{
				$strError .= GetMessage("CATI_SCHEME_EXISTS")."<br>";
			}
			else
			{
				$paramsStr  = "fields_type=".urlencode($fields_type);
				$paramsStr .= "&first_names_r=".urlencode($first_names_r);
				$paramsStr .= "&delimiter_r=".urlencode($delimiter_r);
				$paramsStr .= "&delimiter_other_r=".urlencode($delimiter_other_r);
				$paramsStr .= "&first_names_f=".urlencode($first_names_f);
				$paramsStr .= "&metki_f=".urlencode($metki_f);
				for ($i = 0; $i < $NUM_FIELDS; $i++)
				{
					$paramsStr .= "&field_".$i."=".urlencode(${"field_".$i});
				}
				$paramsStr .= "&PATH2IMAGE_FILES=".urlencode($PATH2IMAGE_FILES);
				$paramsStr .= "&outFileAction=".urlencode($outFileAction);
				$paramsStr .= "&inFileAction=".urlencode($inFileAction);
				$paramsStr .= "&max_execution_time=".urlencode($max_execution_time);
				if (CCatalogLoad::Add(array("NAME"=>$NEW_SCHEME_NAME, "VALUE"=>$paramsStr, "TYPE"=>"I", "LAST_USED"=>"Y")))
					CCatalogLoad::SetLastUsed($NEW_SCHEME_NAME, "I");
			}
		}

		$bFieldsPres = False;
		for ($i = 0; $i < $NUM_FIELDS; $i++)
		{
			if (strlen(${"field_".$i})>0)
			{
				$bFieldsPres = True;
				break;
			}
		}
		if (!$bFieldsPres)
			$strError .= GetMessage("CATI_NO_FIELDS")."<br>";

		if (strlen($strError)<=0)
		{
			$csvFile->SetPos($CUR_FILE_POS);
			$arRes = $csvFile->Fetch();
			if ($CUR_FILE_POS<=0 && $bFirstHeaderTmp)
			{
				$arRes = $csvFile->Fetch();
			}

			if ($arRes)
			{
				$bs = new CIBlockSection;
				$el = new CIBlockElement;
				$tmpid = md5(uniqid(""));
				$line_num = 0;
				$correct_lines = 0;
				$error_lines = 0;
				$killed_lines = 0;
				$arIBlockProperty = array();
				$arIBlockPropertyValue = array();
				$bThereIsGroups = False;
				$arProductGroups = array();
				if ($CUR_FILE_POS>0 && is_set($_SESSION, $CUR_LOAD_SESS_ID))
				{
					if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "tmpid"))
						$tmpid = $_SESSION[$CUR_LOAD_SESS_ID]["tmpid"];
					if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "line_num"))
						$line_num = $_SESSION[$CUR_LOAD_SESS_ID]["line_num"];
					if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "correct_lines"))
						$correct_lines = $_SESSION[$CUR_LOAD_SESS_ID]["correct_lines"];
					if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "error_lines"))
						$error_lines = $_SESSION[$CUR_LOAD_SESS_ID]["error_lines"];
					if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "killed_lines"))
						$killed_lines = $_SESSION[$CUR_LOAD_SESS_ID]["killed_lines"];
					if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "arIBlockProperty"))
						$arIBlockProperty = $_SESSION[$CUR_LOAD_SESS_ID]["arIBlockProperty"];
					if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "arIBlockPropertyValue"))
						$arIBlockPropertyValue = $_SESSION[$CUR_LOAD_SESS_ID]["arIBlockPropertyValue"];
					if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "bThereIsGroups"))
						$bThereIsGroups = $_SESSION[$CUR_LOAD_SESS_ID]["bThereIsGroups"];
					if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "arProductGroups"))
						$arProductGroups = $_SESSION[$CUR_LOAD_SESS_ID]["arProductGroups"];
				}

				// Подготовим массивы для загрузки групп
				$strAvailGroupFields = COption::GetOptionString("catalog", "allowed_group_fields", $defCatalogAvailGroupFields);
				$arAvailGroupFields = split(",", $strAvailGroupFields);
				$arAvailGroupFields_names = array();
				for ($i = 0; $i < count($arAvailGroupFields); $i++)
				{
					for ($j = 0; $j < count($arCatalogAvailGroupFields); $j++)
					{
						if ($arCatalogAvailGroupFields[$j]["value"]==$arAvailGroupFields[$i])
						{
							$arAvailGroupFields_names[$arAvailGroupFields[$i]] = array(
								"field" => $arCatalogAvailGroupFields[$j]["field"],
								"important" => $arCatalogAvailGroupFields[$j]["important"]
								);
							break;
						}
					}
				}

				// Подготовим массивы для загрузки товаров
				$strAvailProdFields = COption::GetOptionString("catalog", "allowed_product_fields", $defCatalogAvailProdFields);
				$arAvailProdFields = split(",", $strAvailProdFields);
				$arAvailProdFields_names = array();
				for ($i = 0; $i < count($arAvailProdFields); $i++)
				{
					for ($j = 0; $j < count($arCatalogAvailProdFields); $j++)
					{
						if ($arCatalogAvailProdFields[$j]["value"]==$arAvailProdFields[$i])
						{
							$arAvailProdFields_names[$arAvailProdFields[$i]] = array(
								"field" => $arCatalogAvailProdFields[$j]["field"],
								"important" => $arCatalogAvailProdFields[$j]["important"]
								);
							break;
						}
					}
				}

				// Подготовим массивы для загрузки товаров (для каталога)
				$strAvailPriceFields = COption::GetOptionString("catalog", "allowed_product_fields", $defCatalogAvailPriceFields);
				$arAvailPriceFields = split(",", $strAvailProdFields);
				$arAvailPriceFields_names = array();
				for ($i = 0; $i < count($arAvailPriceFields); $i++)
				{
					for ($j = 0; $j < count($arCatalogAvailPriceFields); $j++)
					{
						if ($arCatalogAvailPriceFields[$j]["value"]==$arAvailPriceFields[$i])
						{
							$arAvailPriceFields_names[$arAvailPriceFields[$i]] = array(
								"field" => $arCatalogAvailPriceFields[$j]["field"],
								"important" => $arCatalogAvailPriceFields[$j]["important"]
								);
							break;
						}
					}
				}

				// Подготовим массивы для загрузки цен товаров
				$strAvailValueFields = COption::GetOptionString("catalog", "allowed_price_fields", $defCatalogAvailValueFields);
				$arAvailValueFields = split(",", $strAvailValueFields);
				$arAvailValueFields_names = array();
				for ($i = 0; $i < count($arAvailValueFields); $i++)
				{
					for ($j = 0; $j < count($arCatalogAvailValueFields); $j++)
					{
						if ($arCatalogAvailValueFields[$j]["value"] == $arAvailValueFields[$i])
						{
							$arAvailValueFields_names[$arAvailValueFields[$i]] = array(
									"field" => $arCatalogAvailValueFields[$j]["field"],
									"important" => $arCatalogAvailValueFields[$j]["important"]
								);
							break;
						}
					}
				}

				// Пошел главный цикл
				do
				{
					$strErrorR = "";
					$line_num++;

					// заполним массив групп, являющийся путем к товару по каталогу
					$arGroupsTmp = array();
					for ($i = 0; $i < $NUM_CATALOG_LEVELS; $i++)
					{
						$arGroupsTmp1 = array();
						foreach ($arAvailGroupFields_names as $key => $value)
						{
							$ind = GetValueByCodeTmp($key.$i);
							if ($ind>-1)
							{
								$arGroupsTmp1[$value["field"]] = Trim($arRes[$ind]);
								$bThereIsGroups = True;
							}
						}
						$arGroupsTmp[] = $arGroupsTmp1;
					}

					// грохнем конечные пустые группы
					$i = count($arGroupsTmp)-1;
					while ($i>=0)
					{
						foreach ($arAvailGroupFields_names as $key => $value)
						{
							if ($value["important"]=="Y" && strlen($arGroupsTmp[$i][$value["field"]])>0)
							{
								break 2;
							}
						}
						unset($arGroupsTmp[$i]);
						$i--;
					}

					// Обзавем не конечные безымянные группы именем "<Пустое название>"
					for ($i = 0; $i < count($arGroupsTmp); $i++)
					{
						if (strlen($arGroupsTmp[$i]["NAME"])<=0) $arGroupsTmp[$i]["NAME"] = GetMessage("CATI_NOMAME");
						$arGroupsTmp[$i]["TMP_ID"] = $tmpid;
					}

					// Наделаем дерево групп, если такого нет. При этом запомним код группы, в которую вставим товар
					$LAST_GROUP_CODE = 0;
					for ($i = 0; $i < count($arGroupsTmp); $i++)
					{
						$arFilter = array("IBLOCK_ID"=>$IBLOCK_ID);
						if (strlen($arGroupsTmp[$i]["XML_ID"])>0)
						{
							$arFilter["XML_ID"] = $arGroupsTmp[$i]["XML_ID"];
						}
						elseif (strlen($arGroupsTmp[$i]["NAME"])>0)
						{
							$arFilter["NAME"] = $arGroupsTmp[$i]["NAME"];
						}

						if ($LAST_GROUP_CODE>0)
						{
							$arFilter["SECTION_ID"] = $LAST_GROUP_CODE;
							$arGroupsTmp[$i]["IBLOCK_SECTION_ID"] = $LAST_GROUP_CODE;
						}
						else
						{
							$arFilter["SECTION_ID"] = 0;
							$arGroupsTmp[$i]["IBLOCK_SECTION_ID"] = false;
						}

						$res = CIBlockSection::GetList(array(), $arFilter);
						if ($arr = $res->Fetch())
						{
							$LAST_GROUP_CODE = $arr["ID"];
							$res = $bs->Update($LAST_GROUP_CODE, $arGroupsTmp[$i]);
						}
						else
						{
							$arGroupsTmp[$i]["IBLOCK_ID"] = $IBLOCK_ID;
							if ($arGroupsTmp[$i]["ACTIVE"]!="N") $arGroupsTmp[$i]["ACTIVE"] = "Y";
							$LAST_GROUP_CODE = $bs->Add($arGroupsTmp[$i]);
						}
					}

					//CIBlockSection::ReSort($IBLOCK_ID);

					// загрузим товар
					$arLoadProductArray = Array(
						"MODIFIED_BY"		=>	$USER->GetID(),
						"IBLOCK_ID"			=>	$IBLOCK_ID,
						"TMP_ID"				=> $tmpid
						);
					foreach ($arAvailProdFields_names as $key => $value)
					{
						$ind = GetValueByCodeTmp($key);
						if ($ind>-1)
						{
							$arLoadProductArray[$value["field"]] = Trim($arRes[$ind]);
						}
					}

					$arFilter = array("IBLOCK_ID"=>$IBLOCK_ID);
					if (strlen($arLoadProductArray["XML_ID"])>0)
					{
						$arFilter["XML_ID"] = $arLoadProductArray["XML_ID"];
					}
					else
					{
						if (strlen($arLoadProductArray["NAME"])>0)
						{
							$arFilter["NAME"] = $arLoadProductArray["NAME"];
						}
						else
						{
							$strErrorR .= GetMessage("CATI_LINE_NO")." ".$line_num.". ".GetMessage("CATI_NOIDNAME")."<br>";
						}
					}

					if (strlen($strErrorR)<=0)
					{
						if (is_set($arLoadProductArray, "PREVIEW_PICTURE"))
						{
							$bFilePres = False;
							if (strlen($arLoadProductArray["PREVIEW_PICTURE"])>0)
							{
								$strPictureName = $arLoadProductArray["PREVIEW_PICTURE"];
								if (file_exists($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName))
								{
									$arLoadProductArray["PREVIEW_PICTURE"] = array();
									$arLoadProductArray["PREVIEW_PICTURE"]["name"] = $_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName;
									$arImageProps = getimagesize($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName);
									if ($arImageProps[2]==1) $arLoadProductArray["PREVIEW_PICTURE"]["type"] = "image/gif";
									elseif ($arImageProps[2]==2) $arLoadProductArray["PREVIEW_PICTURE"]["type"] = "image/jpeg";
									elseif ($arImageProps[2]==3) $arLoadProductArray["PREVIEW_PICTURE"]["type"] = "image/png";
									$arLoadProductArray["PREVIEW_PICTURE"]["size"] = filesize($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName);
									$arLoadProductArray["PREVIEW_PICTURE"]["tmp_name"] = $_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName;
									$bFilePres = True;
								}
							}

							if (!$bFilePres)
							{
								unset($arLoadProductArray["PREVIEW_PICTURE"]);
							}
						}

						if (is_set($arLoadProductArray, "DETAIL_PICTURE"))
						{
							$bFilePres = False;
							if (strlen($arLoadProductArray["DETAIL_PICTURE"])>0)
							{
								$strPictureName = $arLoadProductArray["DETAIL_PICTURE"];
								if (file_exists($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName))
								{
									$arLoadProductArray["DETAIL_PICTURE"] = array();
									$arLoadProductArray["DETAIL_PICTURE"]["name"] = $_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName;
									$arImageProps = getimagesize($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName);
									if ($arImageProps[2]==1) $arLoadProductArray["DETAIL_PICTURE"]["type"] = "image/gif";
									elseif ($arImageProps[2]==2) $arLoadProductArray["DETAIL_PICTURE"]["type"] = "image/jpeg";
									elseif ($arImageProps[2]==3) $arLoadProductArray["DETAIL_PICTURE"]["type"] = "image/png";
									$arLoadProductArray["DETAIL_PICTURE"]["size"] = filesize($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName);
									$arLoadProductArray["DETAIL_PICTURE"]["tmp_name"] = $_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName;
									$bFilePres = True;
								}
							}

							if (!$bFilePres)
							{
								unset($arLoadProductArray["DETAIL_PICTURE"]);
							}
						}

						$res = CIBlockElement::GetList(Array(), $arFilter);
						if ($arr = $res->Fetch())
						{
							$PRODUCT_ID = $arr["ID"];
							if (is_set($arLoadProductArray, "PREVIEW_PICTURE") && IntVal($arr["PREVIEW_PICTURE"])>0)
							{
								$arLoadProductArray["PREVIEW_PICTURE"]["old_file"] = $arr["PREVIEW_PICTURE"];
							}
							if (is_set($arLoadProductArray, "DETAIL_PICTURE") && IntVal($arr["DETAIL_PICTURE"])>0)
							{
								$arLoadProductArray["DETAIL_PICTURE"]["old_file"] = $arr["DETAIL_PICTURE"];
							}
							if ($bThereIsGroups)
							{
								$LAST_GROUP_CODE_tmp = (($LAST_GROUP_CODE>0) ? $LAST_GROUP_CODE : false);
								if (!is_array($arProductGroups[$PRODUCT_ID]) || !in_array($LAST_GROUP_CODE_tmp, $arProductGroups[$PRODUCT_ID]))
								{
									$arProductGroups[$PRODUCT_ID][] = $LAST_GROUP_CODE_tmp;
								}
								$arLoadProductArray["IBLOCK_SECTION"] = $arProductGroups[$PRODUCT_ID];
							}
							$res = $el->Update($PRODUCT_ID, $arLoadProductArray);
						}
						else
						{
							if ($bThereIsGroups)
							{
								$arLoadProductArray["IBLOCK_SECTION"] = (($LAST_GROUP_CODE>0) ? $LAST_GROUP_CODE : false);
							}
							if ($arLoadProductArray["ACTIVE"]!="N") $arLoadProductArray["ACTIVE"] = "Y";
							$PRODUCT_ID = $el->Add($arLoadProductArray);
							if ($bThereIsGroups)
							{
								$arProductGroups[$PRODUCT_ID][] = (($LAST_GROUP_CODE>0) ? $LAST_GROUP_CODE : false);
							}
							$res = ($PRODUCT_ID>0);
						}

						if (!$res)
						{
							$strErrorR .= GetMessage("CATI_LINE_NO")." ".$line_num.". ".GetMessage("CATI_ERROR_LOADING")." ".$el->LAST_ERROR."<br>";
						}
					}

					if (strlen($strErrorR)<=0)
					{
						$PROP = array();
						for ($i = 0; $i < $NUM_FIELDS; $i++)
						{
							if (substr(${"field_".$i}, 0, 7) == "IP_PROP")
							{
								$cur_prop_id = IntVal(substr(${"field_".$i}, 7));
								if (!is_set($arIBlockProperty, $cur_prop_id))
								{
									$res1 = CIBlockProperty::GetByID($cur_prop_id, $IBLOCK_ID);
									if ($arRes1 = $res1->Fetch())
									{
										$arIBlockProperty[$cur_prop_id] = $arRes1;
									}
									else
									{
										$arIBlockProperty[$cur_prop_id] = array();
									}
								}
								if (is_array($arIBlockProperty[$cur_prop_id]) && count($arIBlockProperty[$cur_prop_id])>0)
								{
									if ($arIBlockProperty[$cur_prop_id]["PROPERTY_TYPE"]=="L")
									{
										$res2 = CIBlockProperty::GetPropertyEnum($cur_prop_id, Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "VALUE"=>Trim($arRes[$i])));
										if ($arRes2 = $res2->Fetch())
										{
											$arRes[$i] = $arRes2["ID"];
										}
										else
										{
											$arRes[$i] = CIBlockPropertyEnum::Add(array("PROPERTY_ID"=>$cur_prop_id, "VALUE"=>Trim($arRes[$i]), "TMP_ID"=>$tmpid));
										}
									}
									if ($arIBlockProperty[$cur_prop_id]["MULTIPLE"]=="Y")
									{
										if (!in_array(Trim($arRes[$i]), $arIBlockPropertyValue[$PRODUCT_ID][$cur_prop_id]))
											$arIBlockPropertyValue[$PRODUCT_ID][$cur_prop_id][] = Trim($arRes[$i]);
										$PROP[$cur_prop_id] = $arIBlockPropertyValue[$PRODUCT_ID][$cur_prop_id];
									}
									else
									{
										$PROP[$cur_prop_id][] = Trim($arRes[$i]);
									}
								}
							}
						}

						foreach ($PROP as $keyip => $valueip)
						{
							CIBlockElement::SetPropertyValues($PRODUCT_ID, $IBLOCK_ID, $valueip, $keyip);
						}
					}

					if (strlen($strErrorR) <= 0 && $bIBlockIsCatalog)
					{
						// загрузим цены
						$arLoadOfferArray = Array(
								"ID" => $PRODUCT_ID
							);
						foreach ($arAvailPriceFields_names as $key => $value)
						{
							$ind = GetValueByCodeTmp($key);
							if ($ind > -1)
								$arLoadOfferArray[$value["field"]] = Trim($arRes[$ind]);
						}
						CCatalogProduct::Add($arLoadOfferArray);

						$quantityFrom = 0;
						$quantityTo = 0;
						for ($j = 0; $j < $NUM_FIELDS; $j++)
						{
							if (${"field_".$j} == "CV_QUANTITY_FROM")
								$quantityFrom = IntVal($arRes[$j]);
							elseif (${"field_".$j} == "CV_QUANTITY_TO")
								$quantityTo = IntVal($arRes[$j]);
						}

						$arFields = array();
						for ($j = 0; $j < $NUM_FIELDS; $j++)
						{
							foreach ($arAvailValueFields_names as $key => $value)
							{
								if (substr(${"field_".$j}, 0, strlen($key) + 1) == $key."_")
								{
									if (!isset($arFields[IntVal(substr(${"field_".$j}, strlen($key) + 1))]))
									{
										$arFields[IntVal(substr(${"field_".$j}, strlen($key) + 1))] = array(
												"PRODUCT_ID" => $PRODUCT_ID,
												"CATALOG_GROUP_ID" => IntVal(substr(${"field_".$j}, strlen($key) + 1)),
												"QUANTITY_FROM" => (($quantityFrom > 0) ? $quantityFrom : False),
												"QUANTITY_TO" => (($quantityTo > 0) ? $quantityTo : False)
											);
									}

									$arFields[IntVal(substr(${"field_".$j}, strlen($key) + 1))][$value["field"]] = Trim($arRes[$j]);
								}
							}
						}

						foreach ($arFields as $key => $value)
						{
							if (isset($value["PRICE"]))
							{
								$value["PRICE"] = str_replace(",", ".", Trim($value["PRICE"]));
								$value["PRICE"] = preg_replace("/[^\d.]/i", "", $value["PRICE"]);
								$value["PRICE"] = DoubleVal($value["PRICE"]);
							}
							else
							{
								$value["PRICE"] = False;
							}

							$res = CPrice::GetList(
									array(),
									array(
											"PRODUCT_ID" => $PRODUCT_ID,
											"CATALOG_GROUP_ID" => $key,
											"QUANTITY_FROM" => $quantityFrom,
											"QUANTITY_TO" => $quantityTo
										)
								);
							if ($arr = $res->Fetch())
							{
								CPrice::Update($arr["ID"], $value);
							}
							else
							{
								CPrice::Add($value);
							}
						}
					}

					if (strlen($strErrorR)<=0)
					{
						$correct_lines++;
					}
					else
					{
						$error_lines++;
						$strError .= $strErrorR;
					}

					if (intval($max_execution_time)>0 && (getmicrotime()-START_EXEC_TIME)>intval($max_execution_time))
					{
						$bAllLinesLoaded = False;
						break;
					}
				}
				while ($arRes = $csvFile->Fetch());

				CIBlockSection::ReSort($IBLOCK_ID);

				// грохнем группы и товары, которых нет в файле данных. Свойства не грохаем
				if ($bAllLinesLoaded)
				{
					if (is_set($_SESSION, $CUR_LOAD_SESS_ID))
						unset($_SESSION[$CUR_LOAD_SESS_ID]);

					if ($bThereIsGroups)
					{
						$res = CIBlockSection::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "!TMP_ID"=>$tmpid));
						while($arr = $res->Fetch())
						{
							if ($outFileAction=="D")
							{
								CIBlockSection::Delete($arr["ID"]);
							}
							elseif ($outFileAction=="F")
							{
							}
							else	// H
							{
								$bs->Update($arr["ID"], Array("NAME"=>$arr["NAME"], "ACTIVE" => "N"));
							}
						}

						if ($inFileAction=="A")
						{
							$res = CIBlockSection::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "TMP_ID"=>$tmpid, "ACTIVE"=>"N"));
							while($arr = $res->Fetch())
							{
								$bs->Update($arr["ID"], Array("NAME"=>$arr["NAME"], "ACTIVE" => "Y"));
							}
						}
					}

					$res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "!TMP_ID"=>$tmpid));
					while($arr = $res->Fetch())
					{
						if ($outFileAction=="D")
						{
							CIBlockElement::Delete($arr["ID"], "Y", "N");
							$killed_lines++;
						}
						elseif ($outFileAction=="F")
						{
						}
						else	// H
						{
							$el->Update($arr["ID"], Array("ACTIVE" => "N"));
							$killed_lines++;
						}
					}

					if ($inFileAction=="A")
					{
						$res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "TMP_ID"=>$tmpid, "ACTIVE"=>"N"));
						while($arr = $res->Fetch())
						{
							$el->Update($arr["ID"], Array("ACTIVE" => "Y"));
						}
					}
				}
				else
				{
					if (strlen($CUR_LOAD_SESS_ID)<=0) $CUR_LOAD_SESS_ID = "CL".time();
					$_SESSION[$CUR_LOAD_SESS_ID]["tmpid"] = $tmpid;
					$_SESSION[$CUR_LOAD_SESS_ID]["line_num"] = $line_num;
					$_SESSION[$CUR_LOAD_SESS_ID]["correct_lines"] = $correct_lines;
					$_SESSION[$CUR_LOAD_SESS_ID]["error_lines"] = $error_lines;
					$_SESSION[$CUR_LOAD_SESS_ID]["killed_lines"] = $killed_lines;
					$_SESSION[$CUR_LOAD_SESS_ID]["arIBlockProperty"] = $arIBlockProperty;
					$_SESSION[$CUR_LOAD_SESS_ID]["bThereIsGroups"] = $bThereIsGroups;
					$_SESSION[$CUR_LOAD_SESS_ID]["arProductGroups"] = $arProductGroups;

					$paramsStr  = "fields_type=".urlencode($fields_type);
					$paramsStr .= "&first_names_r=".urlencode($first_names_r);
					$paramsStr .= "&delimiter_r=".urlencode($delimiter_r);
					$paramsStr .= "&delimiter_other_r=".urlencode($delimiter_other_r);
					$paramsStr .= "&first_names_f=".urlencode($first_names_f);
					$paramsStr .= "&metki_f=".urlencode($metki_f);
					for ($i = 0; $i < $NUM_FIELDS; $i++)
					{
						$paramsStr .= "&field_".$i."=".urlencode(${"field_".$i});
					}
					$paramsStr .= "&PATH2IMAGE_FILES=".urlencode($PATH2IMAGE_FILES);
					$paramsStr .= "&outFileAction=".urlencode($outFileAction);
					$paramsStr .= "&inFileAction=".urlencode($inFileAction);
					$paramsStr .= "&max_execution_time=".urlencode($max_execution_time);
					$_SESSION[$CUR_LOAD_SESS_ID]["LOAD_SCHEME"] = $paramsStr;

					$curFilePos = $csvFile->GetPos();
				}
			}
		}

		if (strlen($strError)>0)
		{
			$strError .= GetMessage("CATI_TOTAL_ERRS")." ".IntVal($error_lines).".<br>";
			$strError .= GetMessage("CATI_TOTAL_COR1")." ".IntVal($correct_lines)." ".GetMessage("CATI_TOTAL_COR2")."<br>";
			$STEP = 3;
		}
		//*****************************************************************//
	}
	//*****************************************************************//
}
/////////////////////////////////////////////////////////////////////

$APPLICATION->SetTitle(GetMessage("CATI_PAGE_TITLE").$STEP);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
echo ShowError($strError);

if (!$bAllLinesLoaded)
{
	$strParams = "CUR_FILE_POS=".$curFilePos."&CUR_LOAD_SESS_ID=".$CUR_LOAD_SESS_ID."&STEP=4&URL_DATA_FILE=".urlencode($DATA_FILE_NAME)."&IBLOCK_ID=".$IBLOCK_ID."&fields_type=".urlencode($fields_type)."&max_execution_time=".IntVal($max_execution_time);
	if ($fields_type=="R")
		$strParams .= "&delimiter_r=".urlencode($delimiter_r)."&delimiter_other_r=".urlencode($delimiter_other_r)."&first_names_r=".urlencode($first_names_r);
	else
		$strParams .= "&metki_f=".urlencode($metki_f)."&first_names_f=".urlencode($first_names_f);
	?>
	<font class="text">
	<?echo GetMessage("CATI_AUTO_REFRESH");?>
	<a href="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANG?>&<?echo $strParams ?>"><?echo GetMessage("CATI_AUTO_REFRESH_STEP");?></a><br>
	</font>
	<script language="JavaScript" type="text/javascript">
	<!--
	function DoNext()
	{
		window.location="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANG?>&<?echo $strParams ?>";
	}
	setTimeout('DoNext()', 2000);
	//-->
	</script>
	<?
}
?>

<form method="POST" action="<?echo $sDocPath?>?lang=<?echo LANG ?>" ENCTYPE="multipart/form-data" name="dataload">
<?if ($STEP < 4):?>
<table border="0" cellspacing="1" cellpadding="0" width="99%">
	<tr>
		<td align="right">
			<font class="tableheadtext">
			<input type="submit" class="button" value="<?echo ($STEP==3)?GetMessage("CATI_NEXT_STEP_F"):GetMessage("CATI_NEXT_STEP") ?> &gt;&gt;" <?/*if ($CATALOG_RIGHT<"W") echo "disabled"*/ ?> name="submit_btn">
			</font>
		</td>
	</tr>
</table>
<?endif;?>

<table border="0" cellspacing="1" cellpadding="3" width="100%" class="edittable">
<?
//*****************************************************************//
if ($STEP==1):
//*****************************************************************//
?>
	<tr>
		<td valign="middle" colspan="2" align="center" nowrap class="tablehead"><font class="tableheadtext"><b><?echo GetMessage("CATI_DATA_LOADING") ?></b></font></td>
	</tr>
	<tr>
		<td align="right" nowrap class="tablebody">
			<font class="tablefieldtext"><?echo GetMessage("CATI_DATA_FILE") ?></font>
		</td>
		<td align="left" nowrap class="tablebody">
			<input type="hidden" name="MAX_FILE_SIZE" value="1000000000">
			<font class="tablebodytext">
			<input type="file" name="DATA_FILE" class="typefile">
			</font>
		</td>
	</tr>
	<?if (CModule::IncludeModule("fileman")):?>
		<?
		$FM_RIGHT = $APPLICATION->GetGroupRight("fileman");
		if ($FM_RIGHT!="D"):
			?>
			<tr>
				<td align="right" nowrap class="tablebody" valign="top">
					<font class="tablefieldtext"><?echo GetMessage("CATI_OR_DATA_FILE") ?><br><?echo GetMessage("CATI_FROM_DISK") ?></font>
				</td>
				<td align="left" nowrap class="tablebody">
					<?
					$path = Rel2Abs("/", $path);
					?>
					<script>
					<!--
					function filelist_OnLoad(strDir)
					{
						document.cookie = "xlopendir=" + escape(strDir) + ";expires=Fri, 31 Dec 2009 23:59:59 GMT;";
						dataload.URL_DATA_FILE.value = strDir+"/";
					}

					function filelist_OnFileSelect(strPath)
					{
						dataload.URL_DATA_FILE.value = strPath;
					}
					//-->
					</script>
					<input class="typeinput" type="text" name="URL_DATA_FILE" size="40" value=""><br>
					<iframe name="filelist" src="cat_file_list.php?datafiletype=CSV&path=<?echo urlencode(isset($xlopendir) ? $xlopendir : $path)?>&lang=<?echo LANG?>" width="400" height="250" border="0" frameBorder="0"></iframe>
				</td>
			</tr>
			<?
		endif;?>
	<?endif;?>
	<tr>
		<td align="right" nowrap class="tablebody">
			<font class="tablefieldtext"><?echo GetMessage("CATI_INFOBLOCK") ?></font>
		</td>
		<td align="left" nowrap class="tablebody">
			<font class="tablebodytext">
			<select name="IBLOCK_ID" class="typeselect">
				<option value=""><?echo GetMessage("CATI_INFOBLOCK_SELECT") ?></option>
				<?
				$iblocks = CIBlock::GetList(array("SORT"=>"ASC"));
				while ($iblocks->ExtractFields("f_"))
				{
					?><option value="<?echo $f_ID ?>" <?if (IntVal($f_ID)==$IBLOCK_ID) echo "selected";?>><?echo $f_NAME ?></option><?
				}
				?>
			</select>
			</font>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap class="tablebody" valign="top">
			<font class="tablefieldtext"><?echo GetMessage("CATI_LOADSCHEME") ?></font>
		</td>
		<td align="left" nowrap class="tablebody">
			<font class="tablebodytext">
			<input type="radio" name="LOAD_SCHEME" value="" <?if (strlen($LOAD_SCHEME)<=0) echo "checked";?>><?echo GetMessage("CATI_NOT") ?><br>
			<?
			$load_schs = CCatalogLoad::GetList(array("LAST_USED"=>"DESC", "NAME"=>"ASC"), array("TYPE" => "I"));
			while ($load_schs->ExtractFields("f_"))
			{
				?><input type="radio" name="LOAD_SCHEME" value="<?echo $f_NAME ?>" <?if ($f_NAME==$LOAD_SCHEME) echo "checked";?>><?
				if ($f_LAST_USED=="Y") echo "<b>";
				echo $f_NAME;
				if ($f_LAST_USED=="Y") echo "</b>";
				?> <a href="javascript:if(confirm('<?echo GetMessage("CATI_DEL_LOAD_SCHEME");?>')) window.location='<?echo $sDocPath?>?lang=<?=LANG?>&delscheme=<?=base64_encode($f_NAME)?>#tb'"><small>[<?echo GetMessage("CATI_DEL") ?>]</small></a><br><?
			}
			?>
			</font>
		</td>
	</tr>
<?
//*****************************************************************//
elseif($STEP==2):
//*****************************************************************//
?>
	<tr>
		<td valign="middle" colspan="2" align="center" nowrap class="tablehead">
			<font class="text">
				<b><?echo GetMessage("CATI_CHOOSE_APPR_FORMAT") ?></b>
			</font>
		</td>
	</tr>
	<tr>
		<td valign="middle" colspan="2" align="left" nowrap class="tablebody">
			<SCRIPT LANGUAGE="JavaScript">
			function DeactivateAllExtra()
			{
				document.getElementById("table_r").disabled = true;
				document.getElementById("table_f").disabled = true;

				document.dataload.metki_f.disabled = true;
				document.dataload.first_names_f.disabled = true;

				var i;
				for (i = 0 ; i < document.dataload.delimiter_r.length; i++)
				{
					document.dataload.delimiter_r[i].disabled = true;
				}
				document.dataload.delimiter_other_r.disabled = true;
				document.dataload.first_names_r.disabled = true;
			}

			function ChangeExtra()
			{
				if (document.dataload.fields_type[0].checked)
				{
					document.getElementById("table_r").disabled = false;
					document.getElementById("table_f").disabled = true;

					var i;
					for (i = 0 ; i < document.dataload.delimiter_r.length; i++)
					{
						document.dataload.delimiter_r[i].disabled = false;
					}
					document.dataload.delimiter_other_r.disabled = false;
					document.dataload.first_names_r.disabled = false;

					document.dataload.metki_f.disabled = true;
					document.dataload.first_names_f.disabled = true;

					document.dataload.submit_btn.disabled = false;
				}
				else
				{
					if (document.dataload.fields_type[1].checked)
					{
						document.getElementById("table_r").disabled = true;
						document.getElementById("table_f").disabled = false;

						var i;
						for (i = 0 ; i < document.dataload.delimiter_r.length; i++)
						{
							document.dataload.delimiter_r[i].disabled = true;
						}
						document.dataload.delimiter_other_r.disabled = true;
						document.dataload.first_names_r.disabled = true;

						document.dataload.metki_f.disabled = false;
						document.dataload.first_names_f.disabled = false;

						document.dataload.submit_btn.disabled = false;
					}
				}
			}
			</SCRIPT>

			<font class="text">
				<input type="radio" name="fields_type" value="R" <?if ($fields_type=="R" || strlen($fields_type)<=0) echo "checked";?> onClick="ChangeExtra()"><?echo GetMessage("CATI_RAZDELITEL") ?><br>
				<input type="radio" name="fields_type" value="F" <?if ($fields_type=="F") echo "checked";?> onClick="ChangeExtra()"><?echo GetMessage("CATI_FIXED") ?>
			</font>
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="center" nowrap class="tablebody">
			<table border="0" cellspacing="1" cellpadding="0" class="tableborder" width="95%">
			<tr valign="top"><td>
				<table id="table_r" border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td valign="middle" colspan="2" align="center" nowrap class="tablehead">
							<font class="tableheadtext">
								<?echo GetMessage("CATI_RAZDEL1") ?>
							</font>
						</td>
					</tr>
					<tr>
						<td valign="top" width="50%" align="right" class="tablebody">
							<font class="tablefieldtext"><?echo GetMessage("CATI_RAZDEL_TYPE") ?></font>
						</td>
						<td valign="top" width="50%" align="left" nowrap class="tablebody">
							<font class="tablebodytext">
							<input type="radio" name="delimiter_r" value="TZP" <?if ($delimiter_r=="TZP" || strlen($delimiter_r)<=0) echo "checked"?>><?echo GetMessage("CATI_TZP") ?><br>
							<input type="radio" name="delimiter_r" value="ZPT" <?if ($delimiter_r=="ZPT") echo "checked"?>><?echo GetMessage("CATI_ZPT") ?><br>
							<input type="radio" name="delimiter_r" value="TAB" <?if ($delimiter_r=="TAB") echo "checked"?>><?echo GetMessage("CATI_TAB") ?><br>
							<input type="radio" name="delimiter_r" value="SPS" <?if ($delimiter_r=="SPS") echo "checked"?>><?echo GetMessage("CATI_SPS") ?><br>
							<input type="radio" name="delimiter_r" value="OTR" <?if ($delimiter_r=="OTR") echo "checked"?>><?echo GetMessage("CATI_OTR") ?>
							<input type="text" class="typeinput" name="delimiter_other_r" size="3" value="<?echo htmlspecialchars($delimiter_other_r) ?>">
							</font>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right" width="50%" class="tablebody">
							<font class="tablefieldtext"><?echo GetMessage("CATI_FIRST_NAMES") ?></font>
						</td>
						<td valign="top" align="left" width="50%" class="tablebody">
							<font class="tablebodytext">
							<input type="checkbox" name="first_names_r" value="Y" <?if ($first_names_r=="Y" || strlen($strError)<=0) echo "checked"?>>
							</font>
						</td>
					</tr>
				</table>
			</td></tr>
			</table>
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="center" nowrap class="tablebody">
			<table border="0" cellspacing="1" cellpadding="0" class="tableborder" width="95%">
			<tr valign="top"><td>
				<table id="table_f" border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td valign="middle" colspan="2" align="center" nowrap class="tablehead">
							<font class="tableheadtext">
								<?echo GetMessage("CATI_FIX1") ?>
							</font>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right" width="50%" class="tablebody">
							<font class="tablefieldtext"><?echo GetMessage("CATI_FIX_MET") ?></font><br>
							<font class="tablefieldtext"><small><?echo GetMessage("CATI_FIX_MET_DESCR") ?></small></font>
						</td>
						<td valign="top" align="left" width="50%" class="tablebody">
							<font class="tablebodytext">
							<textarea name="metki_f" class="typearea" rows="7" cols="3"><?echo htmlspecialchars($metki_f) ?></textarea>
							</font>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right" width="50%" class="tablebody">
							<font class="tablefieldtext"><?echo GetMessage("CATI_FIRST_NAMES") ?></font>
						</td>
						<td valign="top" align="left" width="50%" class="tablebody">
							<font class="tablebodytext">
							<input type="checkbox" name="first_names_f" value="Y" <?if ($first_names_f=="Y") echo "checked"?>>
							</font>
						</td>
					</tr>
				</table>
			</td></tr>
			</table>
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="center" nowrap class="tablebody">
			<table border="0" cellspacing="1" cellpadding="0" class="tableborder" width="95%">
			<tr valign="top"><td>
				<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td valign="middle" align="center" nowrap class="tablehead">
							<font class="tableheadtext">
								<?echo GetMessage("CATI_DATA_SAMPLES") ?>
							</font>
						</td>
					</tr>
					<tr>
						<td valign="top" align="center" nowrap class="tablebody">
							<font class="tablebodytext">
							<?
							$sContent = "";
							if(strlen($DATA_FILE_NAME)>0)
							{
								$DATA_FILE_NAME = trim(str_replace("\\", "/", trim($DATA_FILE_NAME)), "/");
								$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$DATA_FILE_NAME);
								if((strlen($FILE_NAME) > 1) && ($FILE_NAME == "/".$DATA_FILE_NAME) && $APPLICATION->GetFileAccessPermission($FILE_NAME)>="W")
								{
									$file_id = fopen($_SERVER["DOCUMENT_ROOT"].$FILE_NAME, "rb");
									$sContent = fread($file_id, 10000);
									fclose($file_id);
								}

							}
							?>
							<textarea name="data" class="typearea" wrap="OFF" rows="7" cols="100"><?echo htmlspecialchars($sContent) ?></textarea>
							</font>
						</td>
					</tr>
				</table>
			</td></tr>
			</table>
		</td>
	</tr>
	<SCRIPT LANGUAGE="JavaScript">
		DeactivateAllExtra();
		ChangeExtra();
	</SCRIPT>

<?
//*****************************************************************//
elseif($STEP==3):
//*****************************************************************//
?>
	<tr>
		<td valign="middle" colspan="2" align="center" nowrap class="tablehead">
			<font class="tableheadtext">
				<b><?echo GetMessage("CATI_FIELDS_SOOT") ?></b>
			</font>
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="left" nowrap class="tablebody">
			<table width="100%" border="0" cellspacing="2" cellpadding="3">
			<?
			$arAvailFields = array();

			$strVal = COption::GetOptionString("catalog", "allowed_product_fields", $defCatalogAvailProdFields.",".$defCatalogAvailPriceFields);
			$arVal = split(",", $strVal);
			$arCatalogAvailProdFields_tmp = array_merge($arCatalogAvailProdFields, $arCatalogAvailPriceFields);
			for ($i = 0; $i < count($arVal); $i++)
			{
				for ($j = 0; $j < count($arCatalogAvailProdFields_tmp); $j++)
				{
					if ($arVal[$i]==$arCatalogAvailProdFields_tmp[$j]["value"]
						&& $arVal[$i]!="IE_ID")
					{
						$arAvailFields[] = array("value"=>$arCatalogAvailProdFields_tmp[$j]["value"], "name"=>$arCatalogAvailProdFields_tmp[$j]["name"]);
						break;
					}
				}
			}

			$strAvailValueFields = COption::GetOptionString("catalog", "allowed_price_fields", $defCatalogAvailValueFields);
			$arAvailValueFields = split(",", $strAvailValueFields);
			$arAvailValueFields_names = array();
			for ($i = 0; $i < count($arAvailValueFields); $i++)
			{
				for ($j = 0; $j < count($arCatalogAvailValueFields); $j++)
				{
					if ($arCatalogAvailValueFields[$j]["value"] == $arAvailValueFields[$i])
					{
						$arAvailFields[] = array("value"=>$arCatalogAvailValueFields[$j]["value"], "name"=>$arCatalogAvailValueFields[$j]["name"]);
						break;
					}
				}
			}

			$properties = CIBlockProperty::GetList(
					array("sort" => "asc", "name" => "asc"),
					array("ACTIVE" => "Y", "IBLOCK_ID" => $IBLOCK_ID)
				);
			while ($prop_fields = $properties->Fetch())
			{
				$arAvailFields[] = array("value"=>"IP_PROP".$prop_fields["ID"], "name"=>GetMessage("CATI_FI_PROPS")." \"".$prop_fields["NAME"]."\"");
			}

			for ($k = 0; $k < $NUM_CATALOG_LEVELS; $k++)
			{
				$strVal = COption::GetOptionString("catalog", "allowed_group_fields", $defCatalogAvailGroupFields);
				$arVal = split(",", $strVal);
				for ($i = 0; $i < count($arVal); $i++)
				{
					for ($j = 0; $j < count($arCatalogAvailGroupFields); $j++)
					{
						if ($arVal[$i]==$arCatalogAvailGroupFields[$j]["value"])
						{
							$arAvailFields[] = array("value"=>$arCatalogAvailGroupFields[$j]["value"].$k, "name"=>GetMessage("CATI_FI_GROUP_LEV")." ".($k+1).": ".$arCatalogAvailGroupFields[$j]["name"]);
							break;
						}
					}
				}
			}


			$arAvailFields[] = array("value"=>"CV_QUANTITY_FROM", "name"=>GetMessage("DIN_QUANTITY_FROM"));
			$arAvailFields[] = array("value"=>"CV_QUANTITY_TO", "name"=>GetMessage("DIN_QUANTITY_TO"));

			$strVal = COption::GetOptionString("catalog", "allowed_price_fields", $defCatalogAvailValueFields);
			$arVal = split(",", $strVal);
			$db_prgr = CCatalogGroup::GetList(array("NAME" => "ASC"), array());
			while ($prgr = $db_prgr->Fetch())
			{
				for ($i = 0; $i < count($arVal); $i++)
				{
					for ($j = 0; $j < count($arCatalogAvailValueFields); $j++)
					{
						if ($arVal[$i]==$arCatalogAvailValueFields[$j]["value"])
						{
							$arAvailFields[] = array("value"=>$arCatalogAvailValueFields[$j]["value"]."_".$prgr["ID"], "name"=>str_replace("#NAME#", $prgr["NAME"], GetMessage("DIN_PRICE_TYPE")).": ".$arCatalogAvailValueFields[$j]["name"]);
							break;
						}
					}
				}
			}


			/*
			$strVal = COption::GetOptionString("catalog", "allowed_currencies", $defCatalogAvailCurrencies);
			$arVal = split(",", $strVal);
			$lcur = CCurrency::GetList(($by1="sort"), ($order1="asc"));
			$arCurList = array();
			while ($lcur_res = $lcur->Fetch())
			{
				if (in_array($lcur_res["CURRENCY"], $arVal))
				{
					$arCurList[] = $lcur_res["CURRENCY"];
				}
			}

			$db_prgr = CCatalogGroup::GetList(($by1="NAME"), ($order1="ASC"), Array());
			while ($prgr = $db_prgr->Fetch())
			{
				for ($i = 0; $i < count($arCurList); $i++)
				{
					$arAvailFields[] = array("value"=>"CR_PRICE_".$prgr["ID"]."_".$arCurList[$i], "name"=>GetMessage("CATI_FI_PRICE_TYPE")." \"".$prgr["NAME"]."\" - ".$arCurList[$i]);
				}
			}
			*/

			for ($i = 0; $i < count($arDataFileFields); $i++)
			{
				?>
				<tr>
					<td valign="top">
						<font class="tablefieldtext">
							<b><?echo GetMessage("CATI_FIELD") ?> <?echo $i+1 ?></b> (<?echo htmlspecialchars(TruncateText($arDataFileFields[$i], 15));?>)
						</font>
					</td>
					<td valign="top">
						<select name="field_<?echo $i ?>" class="typeselect">
							<option value=""> - </option>
							<?
							for ($j = 0; $j < count($arAvailFields); $j++)
							{
								?>
								<option value="<?echo $arAvailFields[$j]["value"] ?>" <?if ($arAvailFields[$j]["value"]=="IE_XML_ID" || $arAvailFields[$j]["value"]=="IE_NAME") echo "style=\"background-color:#FFCCCC\"" ?> <?if (${"field_".$i}==$arAvailFields[$j]["value"] || !isset(${"field_".$i}) && $arAvailFields[$j]["value"]==$arDataFileFields[$i]) echo "selected" ?>><?echo htmlspecialchars($arAvailFields[$j]["name"]) ?></option>
								<?
							}
							?>
						</select>
					</td>
				</tr>
				<?
			}
			?>
			</table>
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="center" nowrap class="tablebody">
			<table border="0" cellspacing="1" cellpadding="0" class="tableborder" width="95%">
			<tr valign="top"><td>
				<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td valign="middle" align="center" colspan="2" nowrap class="tablehead">
							<font class="tableheadtext">
								<?echo GetMessage("CATI_ADDIT_SETTINGS") ?>
							</font>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right" nowrap class="tablebody">
							<font class="tablefieldtext"><?echo GetMessage("CATI_IMG_PATH") ?></font>
						</td>
						<td valign="top" align="left" class="tablebody">
							<input type="text" class="typeinput" name="PATH2IMAGE_FILES" size="40" value="<?echo htmlspecialchars($PATH2IMAGE_FILES)?>"><br>
							<font class="tablebodytext"><small><?echo GetMessage("CATI_IMG_PATH_DESCR") ?><br></small></font>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right" class="tablebody">
							<font class="tablefieldtext"><?echo GetMessage("CATI_OUTFILE") ?></font>
						</td>
						<td valign="top" align="left" nowrap class="tablebody">
							<font class="tablebodytext">
							<input type="radio" name="outFileAction" value="H" <?if (strlen($outFileAction)<=0 || ($outFileAction=="H")) echo "checked";?>> <?echo GetMessage("CATI_OF_DEACT") ?><br>
							<input type="radio" name="outFileAction" value="D" <?if ($outFileAction=="D") echo "checked";?>> <?echo GetMessage("CATI_OF_DEL") ?><br>
							<input type="radio" name="outFileAction" value="F" <?if ($outFileAction=="F") echo "checked";?>> <?echo GetMessage("CATI_OF_KEEP") ?>
							</font>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right" class="tablebody">
							<font class="tablefieldtext"><?echo GetMessage("CATI_INACTIVE_PRODS");?></font>
						</td>
						<td valign="top" align="left" nowrap class="tablebody">
							<font class="tablebodytext">
							<input type="radio" name="inFileAction" value="F" <?if (strlen($inFileAction)<=0 || ($inFileAction=="F")) echo "checked";?>> <?echo GetMessage("CATI_KEEP_AS_IS");?><br>
							<input type="radio" name="inFileAction" value="A" <?if ($inFileAction=="A") echo "checked";?>> <?echo GetMessage("CATI_ACTIVATE_PROD");?>
							</font>
						</td>
					</tr>
					<tr>
						<td valign="top" align="right" nowrap class="tablebody">
							<font class="tablefieldtext"><?echo GetMessage("CATI_AUTO_STEP_TIME");?></font>
						</td>
						<td valign="top" align="left" class="tablebody">
							<input type="text" class="typeinput" name="max_execution_time" size="40" value="<?echo htmlspecialchars($max_execution_time)?>"><br>
							<font class="tablebodytext"><small><?echo GetMessage("CATI_AUTO_STEP_TIME_NOTE");?><br></small></font>
						</td>
					</tr>
				</table>
			</td></tr>
			</table>
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="center" nowrap class="tablebody">
			<table border="0" cellspacing="1" cellpadding="0" class="tableborder" width="95%">
			<tr valign="top"><td>
				<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td valign="middle" align="center" nowrap class="tablehead">
							<font class="tableheadtext">
								<?echo GetMessage("CATI_SAVE_SCHEME_AS") ?>
							</font>
						</td>
					</tr>
					<tr>
						<td valign="top" align="center" nowrap class="tablebody">
							<input type="text" class="typeinput" name="NEW_SCHEME_NAME" size="40" value="<?echo htmlspecialchars($NEW_SCHEME_NAME)?>">
						</td>
					</tr>
				</table>
			</td></tr>
			</table>
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="center" nowrap class="tablebody">
			<table border="0" cellspacing="1" cellpadding="0" class="tableborder" width="95%">
			<tr valign="top"><td>
				<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td valign="middle" align="center" nowrap class="tablehead">
							<font class="tableheadtext">
								<?echo GetMessage("CATI_DATA_SAMPLES") ?>
							</font>
						</td>
					</tr>
					<tr>
						<td valign="top" align="center" nowrap class="tablebody">
							<font class="tablebodytext">
							<?
							$sContent = "";
							if(strlen($DATA_FILE_NAME)>0)
							{
								$DATA_FILE_NAME = trim(str_replace("\\", "/", trim($DATA_FILE_NAME)), "/");
								$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$DATA_FILE_NAME);
								if((strlen($FILE_NAME) > 1) && ($FILE_NAME == "/".$DATA_FILE_NAME) && $APPLICATION->GetFileAccessPermission($FILE_NAME)>="W")
								{
									$file_id = fopen($_SERVER["DOCUMENT_ROOT"].$FILE_NAME, "rb");
									$sContent = fread($file_id, 10000);
									fclose($file_id);
								}

							}
							?>
							<textarea name="data" class="typearea" wrap="OFF" rows="7" cols="60"><?echo htmlspecialchars($sContent) ?></textarea>
							</font>
						</td>
					</tr>
				</table>
			</td></tr>
			</table>
		</td>
	</tr>
<?
//*****************************************************************//
elseif($STEP==4):
//*****************************************************************//
?>
	<tr>
		<td valign="middle" colspan="2" align="center" nowrap class="tablehead">
			<font class="tableheadtext">
				<b><?
				if (!$bAllLinesLoaded)
				{
					echo GetMessage("CATI_AUTO_REFRESH_CONTINUE");
				}
				else
				{
					echo GetMessage("CATI_SUCCESS");
				}
				?></b>
			</font>
		</td>
	</tr>
	<tr>
		<td valign="middle" colspan="2" align="center" nowrap class="tablebody">
			<font class="tablebodytext">
			<?echo GetMessage("CATI_SU_ALL") ?> <b><?echo $line_num ?></b><br>
			<?echo GetMessage("CATI_SU_CORR") ?> <b><?echo $correct_lines ?></b><br>
			<?echo GetMessage("CATI_SU_ER") ?> <b><?echo $error_lines ?></b><br>
			<?
			if ($outFileAction=="D")
			{
				echo GetMessage("CATI_SU_KILLED")." <b>".$killed_lines."</b>";
			}
			elseif ($outFileAction=="F")
			{
			}
			else	// H
			{
				echo GetMessage("CATI_SU_HIDED")." <b>".$killed_lines."</b>";
			}
			?>
			</font>
		</td>
	</tr>
<?
//*****************************************************************//
endif;
//*****************************************************************//
?>
</table>

<?if ($STEP < 4):?>
	<table border="0" cellspacing="1" cellpadding="0" width="99%">
		<tr>
			<td align="right" nowrap colspan="2">
				<input type="hidden" name="STEP" value="<?echo $STEP+1;?>">

				<?if ($STEP>1):?>
					<input type="hidden" name="URL_DATA_FILE" value="<?echo htmlspecialchars($DATA_FILE_NAME) ?>">
					<input type="hidden" name="IBLOCK_ID" value="<?echo $IBLOCK_ID ?>">
				<?endif;?>

				<?if ($STEP>2):?>
					<input type="hidden" name="fields_type" value="<?echo htmlspecialchars($fields_type) ?>">
					<?if ($fields_type=="R"):?>
						<input type="hidden" name="delimiter_r" value="<?echo htmlspecialchars($delimiter_r) ?>">
						<input type="hidden" name="delimiter_other_r" value="<?echo htmlspecialchars($delimiter_other_r) ?>">
						<input type="hidden" name="first_names_r" value="<?echo htmlspecialchars($first_names_r) ?>">
					<?else:?>
						<input type="hidden" name="metki_f" value="<?echo htmlspecialchars($metki_f) ?>">
						<input type="hidden" name="first_names_f" value="<?echo htmlspecialchars($first_names_f) ?>">
					<?endif;?>
				<?endif;?>

				<font class="tableheadtext">
				<?if ($STEP>1):?>
				<input type="submit" class="button" name="backButton" value="&lt;&lt; <?echo GetMessage("CATI_BACK") ?>">
				<?endif?>
				<input type="submit" class="button" value="<?echo ($STEP==3)?GetMessage("CATI_NEXT_STEP_F"):GetMessage("CATI_NEXT_STEP") ?> &gt;&gt;" <?/* if ($CATALOG_RIGHT<"W") echo "disabled" */?> name="submit_btn">
				</font>
			</td>
		</tr>
	</table>
<?endif;?>
</form>
<?
require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");
?>