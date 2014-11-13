<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

/*
$CATALOG_RIGHT = $APPLICATION->GetGroupRight("catalog");
if ($CATALOG_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
*/

if (!$USER->CanDoOperation('catalog_export_exec'))
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

include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/lang/", "/data_export.php"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

set_time_limit(0);
$STEP = IntVal($STEP);
if ($STEP<=0) $STEP = 1;
if ($REQUEST_METHOD=="POST" && strlen($backButton)>0) $STEP = $STEP - 2;

$NUM_CATALOG_LEVELS = IntVal(COption::GetOptionString("catalog", "num_catalog_levels", 3));

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

if ($REQUEST_METHOD == "GET" && strlen($delscheme)>0 && check_bitrix_sessid())
{
	CCatalogLoad::Delete(urldecode($delscheme));
}

/////////////////////////////////////////////////////////////////////
if ($REQUEST_METHOD == "POST" && $STEP > 1 /*&& $CATALOG_RIGHT=="W"*/ && check_bitrix_sessid())
{
	//*****************************************************************//
	if ($STEP>1)
	{
		//*****************************************************************//
		$IBLOCK_ID = IntVal($IBLOCK_ID);
		$arIBlockres = CIBlock::GetList(Array("sort"=>"asc"), Array("ID"=>IntVal($IBLOCK_ID)));
		$arIBlockres = new CIBlockResult($arIBlockres);
		if ($IBLOCK_ID<=0 || !($arIBlock = $arIBlockres->GetNext()))
			$strError .= GetMessage("CATI_NO_IBLOCK")."<br>";

		if (strlen($strError)<=0)
		{
			if (strlen($LOAD_SCHEME)>0)
			{
				$load_schs = CCatalogLoad::GetList(array(), array("NAME" => $LOAD_SCHEME, "TYPE" => "E"));
				if ($load_schs->ExtractFields("f_"))
				{
					parse_str($f_VALUE);
					$STEP = 3;
				}
			}
		}

		if (strlen($strError)>0)
		{
			$STEP = 1;
		}
		//*****************************************************************//
	}

	if($STEP>2)
	{
		//*****************************************************************//
		$csvFile = new CCSVData();

		if ($fields_type!="F" && $fields_type!="R")
			$strError .= GetMessage("CATI_NO_FORMAT")."<br>";

		$csvFile->SetFieldsType($fields_type);

		$first_names_r = (($first_names_r=="Y") ? "Y" : "N" );
		$csvFile->SetFirstHeader(($first_names_r=="Y")?true:false);

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

		if (strlen($delimiter_r_char)!=1)
			$strError .= GetMessage("CATI_NO_DELIMITER")."<br>";

		if (strlen($strError)<=0)
		{
			$csvFile->SetDelimiter($delimiter_r_char);
		}

		if (strlen($DATA_FILE_NAME)<=0)
			$strError .= GetMessage("CATI_NO_SAVE_FILE")."<br>";

		if (strlen($strError)<=0)
		{
			if (!($fp = fopen($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME, "w")))
				$strError .= GetMessage("CATI_CANNOT_CREATE_FILE")."<br>";
			@fclose($fp);
		}

		$bFieldsPres = False;
		if (is_array($field_needed))
		{
			for ($i = 0; $i < count($field_needed); $i++)
			{
				if ($field_needed[$i]=="Y")
				{
					$bFieldsPres = True;
					break;
				}
			}
		}
		if (!$bFieldsPres)
			$strError .= GetMessage("CATI_NO_FIELDS")."<br>";

		// Мы не можем слинковать более 30 таблиц. Поэтому количество экспортируемых полей ограничено
		$tableLinksCount = 10;
		for ($i = 0; $i < count($field_code); $i++)
		{
			if (substr($field_code[$i], 0, strlen("CR_PRICE_"))=="CR_PRICE_" && $field_needed[$i]=="Y")
			{
				$tableLinksCount++;
			}
			elseif (substr($field_code[$i], 0, strlen("IP_PROP"))=="IP_PROP" && $field_needed[$i]=="Y")
			{
				$tableLinksCount+=2;
			}
		}
		if ($tableLinksCount>30)
			$strError .= GetMessage("CATI_TOO_MANY_TABLES")."<br>";

		$num_rows_writed = 0;
		if (strlen($strError)<=0)
		{
			$selectArray = array(
				"ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "ACTIVE", "ACTIVE_FROM", "ACTIVE_TO", 
				"NAME", "PREVIEW_PICTURE", "PREVIEW_TEXT", "DETAIL_PICTURE", "DETAIL_TEXT", 
				"XML_ID"
				);
			$bNeedGroups = False;
			for ($i = 0; $i < count($field_code); $i++)
			{
				if (substr($field_code[$i], 0, strlen("CR_PRICE_"))=="CR_PRICE_" && $field_needed[$i]=="Y")
				{
					$sPriceTmp = substr($field_code[$i], strlen("CR_PRICE_"));
					$arPriceTmp = Split("_", $sPriceTmp);
					if (IntVal($arPriceTmp[0])>0)
					{
						$selectArray[] = "CATALOG_GROUP_".IntVal($arPriceTmp[0]);
					}
				}
				elseif (substr($field_code[$i], 0, strlen("IP_PROP"))=="IP_PROP" && $field_needed[$i]=="Y")
				{
					$selectArray[] = "PROPERTY_".substr($field_code[$i], strlen("IP_PROP"));
				}
				elseif (substr($field_code[$i], 0, strlen("IC_GROUP"))=="IC_GROUP" && $field_needed[$i]=="Y")
				{
					$bNeedGroups = True;
				}
			}

			$arNeedFields = array();
			$field_neededTmp = $field_needed;
			$field_numTmp = $field_num;
			$field_codeTmp = $field_code;
			for ($i = 0; $i < count($field_numTmp); $i++)
			{
				for ($j = $i+1; $j < count($field_numTmp); $j++)
				{
					if (IntVal($field_numTmp[$i])>IntVal($field_numTmp[$j]))
					{
						$tmpVal = $field_neededTmp[$i];
						$field_neededTmp[$i] = $field_neededTmp[$j];
						$field_neededTmp[$j] = $tmpVal;

						$tmpVal = IntVal($field_numTmp[$i]);
						$field_numTmp[$i] = IntVal($field_numTmp[$j]);
						$field_numTmp[$j] = $tmpVal;

						$tmpVal = $field_codeTmp[$i];
						$field_codeTmp[$i] = $field_codeTmp[$j];
						$field_codeTmp[$j] = $tmpVal;
					}
				}
			}

			for ($i = 0; $i < count($field_numTmp); $i++)
			{
				if ($field_neededTmp[$i]=="Y")
				{
					$arNeedFields[] = $field_codeTmp[$i];
				}
			}

			if ($first_line_names=="Y")
			{
				$arResFields = array();
				for ($i = 0; $i < count($arNeedFields); $i++)
				{
					$arResFields[$i] = $arNeedFields[$i];
				}
				$csvFile->SaveFile($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME, $arResFields);
			}

			$res = CIBlockElement::GetList(array(), array("IBLOCK_ID" => $IBLOCK_ID), false, false, $selectArray);
			while ($res1 = $res->Fetch())
			{
				$arResSections = array();
				if ($bNeedGroups)
				{
					$indreseg = 0;
					$reseg = CIBlockElement::GetElementGroups($res1["ID"]);
					while ($reseg1 = $reseg->Fetch())
					{
						$sections_path = GetIBlockSectionPath($IBLOCK_ID, $reseg1["ID"]);
						while ($arSection = $sections_path->GetNext())
						{
							$arResSections[$indreseg][] = $arSection["NAME"];
						}
						$indreseg++;
					}
					if (count($arResSections)<=0)
						$arResSections[0] = array();
				}
				else
				{
					$arResSections[0] = array();
					$sections_path = GetIBlockSectionPath($IBLOCK_ID, $res1["IBLOCK_SECTION_ID"]);
					while ($arSection = $sections_path->GetNext())
					{
						$arResSections[0][] = $arSection["NAME"];
					}
				}

				for ($inds = 0; $inds < count($arResSections); $inds++)
				{
					$arResFields = array();
					for ($i = 0; $i < count($arNeedFields); $i++)
					{
						if ($arNeedFields[$i]=="IE_NAME") $arResFields[$i] = $res1["NAME"];
						elseif ($arNeedFields[$i]=="IE_ID") $arResFields[$i] = $res1["ID"];
						elseif ($arNeedFields[$i]=="IE_ACTIVE") $arResFields[$i] = $res1["ACTIVE"];
						elseif ($arNeedFields[$i]=="IE_ACTIVE_FROM") $arResFields[$i] = $res1["ACTIVE_FROM"];
						elseif ($arNeedFields[$i]=="IE_ACTIVE_TO") $arResFields[$i] = $res1["ACTIVE_TO"];
						elseif ($arNeedFields[$i]=="IE_PREVIEW_PICTURE")
						{
							if (IntVal($res1["PREVIEW_PICTURE"])>0)
							{
								$db_z = CFile::GetByID(IntVal($res1["PREVIEW_PICTURE"]));
								if ($z = $db_z->Fetch())
								{
									$arResFields[$i] = $z["FILE_NAME"];
								}
							}
							else
							{
								$arResFields[$i] = "";
							}
						}
						elseif ($arNeedFields[$i]=="IE_PREVIEW_TEXT") $arResFields[$i] = $res1["PREVIEW_TEXT"];
						elseif ($arNeedFields[$i]=="IE_DETAIL_PICTURE")
						{
							if (IntVal($res1["DETAIL_PICTURE"])>0)
							{
								$db_z = CFile::GetByID(IntVal($res1["DETAIL_PICTURE"]));
								if ($z = $db_z->Fetch())
								{
									$arResFields[$i] = $z["FILE_NAME"];
								}
							}
							else
							{
								$arResFields[$i] = "";
							}
						}
						elseif ($arNeedFields[$i]=="IE_DETAIL_TEXT") $arResFields[$i] = $res1["DETAIL_TEXT"];
						elseif ($arNeedFields[$i]=="IE_XML_ID") $arResFields[$i] = $res1["XML_ID"];
						elseif ($arNeedFields[$i]=="CP_QUANTITY") $arResFields[$i] = $res1["CATALOG_QUANTITY"];
						elseif ($arNeedFields[$i]=="CP_WEIGHT") $arResFields[$i] = $res1["CATALOG_WEIGHT"];
						elseif (substr($arNeedFields[$i], 0, strlen("IP_PROP"))=="IP_PROP") $arResFields[$i] = $res1["PROPERTY_".substr($arNeedFields[$i], strlen("IP_PROP"))."_VALUE"];
						elseif (substr($arNeedFields[$i], 0, strlen("CR_PRICE_"))=="CR_PRICE_")
						{
							$sPriceTmp = substr($arNeedFields[$i], strlen("CR_PRICE_"));
							$arPriceTmp = Split("_", $sPriceTmp);
							if ($res1["CATALOG_CURRENCY_".IntVal($arPriceTmp[0])]!=$arPriceTmp[1])
							{
								$arResFields[$i] = Round(CCurrencyRates::ConvertCurrency($res1["CATALOG_PRICE_".IntVal($arPriceTmp[0])], $res1["CATALOG_CURRENCY_".IntVal($arPriceTmp[0])], $arPriceTmp[1]), 2);
							}
							else
							{
								$arResFields[$i] = $res1["CATALOG_PRICE_".IntVal($arPriceTmp[0])];
							}
						}
						elseif (substr($arNeedFields[$i], 0, strlen("IC_GROUP"))=="IC_GROUP") $arResFields[$i] = $arResSections[$inds][IntVal(substr($arNeedFields[$i], strlen("IC_GROUP")))];
					}

					$csvFile->SaveFile($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME, $arResFields);
					$num_rows_writed++;
				}
			}

			if (strlen($NEW_SCHEME_NAME)>0)
			{
				$load_schs = CCatalogLoad::GetList(array(), array("NAME" => $NEW_SCHEME_NAME, "TYPE" => "E"));
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
					$paramsStr .= "&first_line_names=".urlencode($first_line_names);
					$paramsStr .= "&DATA_FILE_NAME=".urlencode($DATA_FILE_NAME);

					for ($i = 0; $i < count($field_num); $i++)
					{
						$paramsStr .= "&field_needed[".$i."]=".urlencode($field_needed[$i]);
						$paramsStr .= "&field_num[".$i."]=".urlencode($field_num[$i]);
						$paramsStr .= "&field_code[".$i."]=".urlencode($field_code[$i]);
					}
					CCatalogLoad::Add(array("NAME"=>$NEW_SCHEME_NAME, "VALUE"=>$paramsStr, "TYPE"=>"E"));
				}
			}
		}

		if (strlen($strError)>0)
		{
			$STEP = 2;
		}
		//*****************************************************************//
	}

	//*****************************************************************//
}
/////////////////////////////////////////////////////////////////////

$APPLICATION->SetTitle(GetMessage("CATI_PAGE_TITLE")." ".$STEP);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
echo ShowError($strError);
?>

<form method="POST" action="<?echo $sDocPath?>?lang=<?echo LANG ?>" ENCTYPE="multipart/form-data" name="dataload">
<?if ($STEP < 3):?>
<table border="0" cellspacing="1" cellpadding="0" width="99%">
	<tr>
		<td align="right">
			<font class="tableheadtext">
			<input type="submit" class="button" value="<?echo ($STEP==2)?GetMessage("CATI_NEXT_STEP_F"):GetMessage("CATI_NEXT_STEP") ?> &gt;&gt;" <?/*if ($CATALOG_RIGHT<"W") echo "disabled" */?> name="submit_btn">
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
		<td valign="middle" colspan="2" align="center" nowrap class="tablehead">
			<font class="tableheadtext"><b><?echo GetMessage("CATI_DATA_EXPORT") ?></b></font>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap class="tablebody">
			<font class="tablefieldtext"><?echo GetMessage("CATI_INFOBLOCK") ?></font>
		</td>
		<td align="left" nowrap class="tablebody">
			<font class="tablebodytext">
			<select name="IBLOCK_ID" class="typeselect">
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
			<font class="tablefieldtext"><?echo GetMessage("CATI_SCHEME_NAME") ?></font>
		</td>
		<td align="left" nowrap class="tablebody">
			<font class="tablebodytext">
			<input type="radio" name="LOAD_SCHEME" value="" <?if (strlen($LOAD_SCHEME)<=0) echo "checked";?>><?echo GetMessage("CATI_NOT") ?><br>
			<?
			$load_schs = CCatalogLoad::GetList(array("TYPE" => "E"));
			while ($load_schs->ExtractFields("f_"))
			{
				?><input type="radio" name="LOAD_SCHEME" value="<?echo $f_NAME ?>" <?if ($f_NAME==$LOAD_SCHEME) echo "checked";?>><?echo $f_NAME ?> <a href="<?echo $sDocPath?>?lang=<?echo LANG ?>&delscheme=<?echo urlencode($f_NAME) ?>&<?=bitrix_sessid_get()?>"><small>[<?echo GetMessage("CATI_DELETE") ?>]</small></a><br><?
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
			<font class="tableheadtext">
				<b><?echo GetMessage("CATI_FORMAT_PROPS") ?></b>
			</font>
			<input type="hidden" name="fields_type" value="R">
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
								<?echo GetMessage("CATI_DELIMITERS") ?>
							</font>
						</td>
					</tr>
					<tr>
						<td valign="top" width="50%" align="right" class="tablebody">
							<font class="tablefieldtext"><?echo GetMessage("CATI_DELIMITER_TYPE") ?></font>
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
							<input type="hidden" name="first_names_r" value="N">
						</td>
					</tr>
					<tr>
						<td valign="top" align="right" width="50%" class="tablebody">
							<font class="tablefieldtext"><?echo GetMessage("CATI_FIRST_LINE_NAMES") ?></font>
						</td>
						<td valign="top" align="left" width="50%" class="tablebody">
							<font class="text">
							<input type="checkbox" name="first_line_names" value="Y" <?if ($first_line_names=="Y" || strlen($strError)<=0) echo "checked"?>>
							</font>
						</td>
					</tr>
				</table>
			</td></tr>
			</table><br>
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="center" nowrap class="tablehead">
			<font class="tableheadtext">
				<b><?echo GetMessage("CATI_FIELDS") ?></b>
			</font>
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="left" nowrap class="tablebody">
			<table width="100%" border="0" cellspacing="2" cellpadding="3">
				<tr>
					<td valign="top" class="tablehead">
						<font class="tableheadtext">
						<?echo GetMessage("CATI_FIELDS_NEEDED") ?>
						</font>
					</td>
					<td valign="top" class="tablehead">
						<font class="tableheadtext">
						<?echo GetMessage("CATI_FIELDS_NAMES") ?>
						</font>
					</td>
					<td valign="top" class="tablehead">
						<font class="tableheadtext">
						<?echo GetMessage("CATI_FIELDS_SORTING") ?>
						</font>
					</td>
				</tr>
				<?
				$arAvailFields = array(
					array("value"=>"IE_XML_ID", "name"=>GetMessage("CATI_FI_UNIXML")." (B_IBLOCK_ELEMENT.XML_ID)"),
					array("value"=>"IE_NAME", "name"=>GetMessage("CATI_FI_NAME")." (B_IBLOCK_ELEMENT.NAME)"),
					array("value"=>"IE_ID", "name"=>GetMessage("CATI_FI_ID")." (B_IBLOCK_ELEMENT.ID)"),
					array("value"=>"IE_ACTIVE", "name"=>GetMessage("CATI_FI_ACTIV")." (B_IBLOCK_ELEMENT.ACTIVE)"),
					array("value"=>"IE_ACTIVE_FROM", "name"=>GetMessage("CATI_FI_ACTIVFROM")." (B_IBLOCK_ELEMENT.ACTIVE_FROM)"),
					array("value"=>"IE_ACTIVE_TO", "name"=>GetMessage("CATI_FI_ACTIVTO")." (B_IBLOCK_ELEMENT.ACTIVE_TO)"),
					array("value"=>"IE_PREVIEW_PICTURE", "name"=>GetMessage("CATI_FI_CATIMG")." (B_IBLOCK_ELEMENT.PREVIEW_PICTURE)"),
					array("value"=>"IE_PREVIEW_TEXT", "name"=>GetMessage("CATI_FI_CATDESCR")." (B_IBLOCK_ELEMENT.PREVIEW_TEXT)"),
					array("value"=>"IE_DETAIL_PICTURE", "name"=>GetMessage("CATI_FI_DETIMG")." (B_IBLOCK_ELEMENT.DETAIL_PICTURE)"),
					array("value"=>"IE_DETAIL_TEXT", "name"=>GetMessage("CATI_FI_DETDESCR")." (B_IBLOCK_ELEMENT.DETAIL_TEXT)"),
					array("value"=>"CP_QUANTITY", "name"=>GetMessage("CATI_FI_QUANT")." (B_CATALOG_PRODUCT.QUANTITY)"),
					array("value"=>"CP_WEIGHT", "name"=>GetMessage("CATI_FI_WEIGHT")." (B_CATALOG_PRODUCT.WEIGHT)")
					);
				$properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$IBLOCK_ID));
				while ($prop_fields = $properties->Fetch())
				{
					$arAvailFields[] = array("value"=>"IP_PROP".$prop_fields["ID"], "name"=>GetMessage("CATI_FI_PROPS")." \"".$prop_fields["NAME"]."\"");
				}
				for ($i = 0; $i < $NUM_CATALOG_LEVELS; $i++)
				{
					$arAvailFields[] = array("value"=>"IC_GROUP".$i, "name"=>GetMessage("CATI_FI_GROUP_LEV")." ".($i+1));
				}

				$lcur = CCurrency::GetList(($by1="sort"), ($order1="asc"));
				while ($lcur_res = $lcur->Fetch())
				{
					$arCurList[] = $lcur_res["CURRENCY"];
				}

				$db_prgr = CCatalogGroup::GetList(array("NAME" => "ASC"), array());
				while ($prgr = $db_prgr->Fetch())
				{
					for ($i = 0; $i < count($arCurList); $i++)
					{
						$arAvailFields[] = array("value"=>"CR_PRICE_".$prgr["ID"]."_".$arCurList[$i], "name"=>GetMessage("CATI_FI_PRICE_TYPE")." \"".$prgr["NAME"]."\" - ".$arCurList[$i]);
					}
				}

				for ($i = 0; $i < count($arAvailFields); $i++)
				{
					?>
					<tr>
						<td valign="top" class="tablebody">
							<font class="tablefieldtext">
							<input type="checkbox" name="field_needed[<?echo $i ?>]" <?if ($field_needed[$i]=="Y" || strlen($strError)<=0) echo "checked";?> value="Y">
							</font>
						</td>
						<td valign="top" class="tablebody">
							<font class="tablefieldtext">
								<?if ($i<2) echo "<b>";?>
								<?echo $arAvailFields[$i]["name"] ?>
								<?if ($i<2) echo "</b>";?>
							</font>
						</td>
						<td valign="top" class="tablebody">
							<?if ($i<2) echo "<b>";?>
							<input type="text" class="typeinput" name="field_num[<?echo $i ?>]" value="<?echo (is_array($field_num)?$field_num[$i]:(10*($i+1))) ?>" size="2">
							<input type="hidden" name="field_code[<?echo $i ?>]" value="<?echo $arAvailFields[$i]["value"] ?>">
							<!--<font class="tablebodytext"><?echo GetMessage("CATI_FIELD") ?></font>-->
							<?if ($i<2) echo "</b>";?>
						</td>
					</tr>
					<?
				}
				?>
			</table>
			<br><br>
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="center" nowrap class="tablehead">
			<font class="tableheadtext">
				<b><?echo GetMessage("CATI_SAVE_SCHEME") ?></b>
			</font>
		</td>
	</tr>
	<tr>
		<td valign="middle" align="right" nowrap class="tablebody">
			<font class="tablefieldtext">
				<?echo GetMessage("CATI_SSCHEME_NAME") ?>
			</font>
		</td>
		<td valign="top" align="left" nowrap class="tablebody">
			<input type="text" class="typeinput" name="NEW_SCHEME_NAME" size="40" value="<?echo htmlspecialchars($NEW_SCHEME_NAME)?>">
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="center" nowrap class="tablehead">
			<font class="tableheadtext">
				<b><?echo GetMessage("CATI_DATA_FILE_NAME") ?></b>
			</font>
		</td>
	</tr>
	<tr>
		<td valign="middle" align="right" nowrap class="tablebody">
			<font class="tablefieldtext">
				<?echo GetMessage("CATI_DATA_FILE_NAME1") ?>
			</font>
		</td>
		<td valign="top" align="left" nowrap class="tablebody">
			<font class="tablebodytext">
			<input type="text" class="typeinput" name="DATA_FILE_NAME" size="40" value="<?echo (strlen($DATA_FILE_NAME)>0)?htmlspecialchars($DATA_FILE_NAME):"/upload/export_file.csv"?>"><br>
			<small><?echo GetMessage("CATI_DATA_FILE_NAME1_DESC") ?></small>
			</font>
		</td>
	</tr>

<?
//*****************************************************************//
elseif($STEP==3):
//*****************************************************************//
?>
	<tr>
		<td valign="middle" colspan="2" align="center" nowrap class="tablehead">
			<font class="tableheadtext">
				<b><?echo GetMessage("CATI_SUCCESS") ?></b>
			</font>
		</td>
	</tr>
	<tr>
		<td valign="middle" colspan="2" align="center" nowrap class="tablebody">
			<font class="tablebodytext">
			<?echo GetMessage("CATI_SU_ALL") ?> <b><?echo $num_rows_writed ?></b><br><br>
			<?echo str_replace("%DATA_URL%", "<a href=\"".$DATA_FILE_NAME."\" target=\"_blank\">".$DATA_FILE_NAME."</a>", GetMessage("CATI_SU_ALL1")) ?>
			</font>
		</td>
	</tr>
<?
//*****************************************************************//
endif;
//*****************************************************************//
?>
</table>

<?if ($STEP < 3):?>
	<table border="0" cellspacing="1" cellpadding="0" width="99%">
		<tr>
			<td align="right" nowrap colspan="2">
				<input type="hidden" name="STEP" value="<?echo $STEP+1;?>">
				<?=bitrix_sessid_post()?>

				<?if ($STEP>1):?>
					<input type="hidden" name="IBLOCK_ID" value="<?echo $IBLOCK_ID ?>">
				<?endif;?>

				<font class="tableheadtext">
				<?if ($STEP>1):?>
				<input type="submit" class="button" name="backButton" value="&lt;&lt; <?echo GetMessage("CATI_BACK") ?>">
				<?endif?>
				<input type="submit" class="button" value="<?echo ($STEP==2)?GetMessage("CATI_NEXT_STEP_F"):GetMessage("CATI_NEXT_STEP") ?> &gt;&gt;" <?/*if ($CATALOG_RIGHT<"W") echo "disabled" */?> name="submit_btn">
				</font>
			</td>
		  </tr>
	</table>
<?endif;?>
</form>
<?
require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");
?>