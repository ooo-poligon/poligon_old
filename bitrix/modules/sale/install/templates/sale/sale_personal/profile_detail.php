<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);

$ID = IntVal($_REQUEST["ID"]);

if (CModule::IncludeModule("sale")):
	$GLOBALS["APPLICATION"]->SetTitle(GetMessage("SPD_TITLE").$ID);

	if ($GLOBALS["USER"]->IsAuthorized()):
//*******************************************************

$PATH_TO_LIST = Trim($PATH_TO_LIST);
if (strlen($PATH_TO_LIST) <= 0)
	$PATH_TO_LIST = $GLOBALS["PATH_TO_LIST"];
if (strlen($PATH_TO_LIST) <= 0)
	$PATH_TO_LIST = "profiles.php";

$PATH_TO_SELF = Trim($PATH_TO_SELF);
if (strlen($PATH_TO_SELF) <= 0)
	$PATH_TO_SELF = $GLOBALS["PATH_TO_SELF"];
if (strlen($PATH_TO_SELF) <= 0)
	$PATH_TO_SELF = "profile_detail.php";


$errorMessage = "";
$bInitVars = false;

if ($_SERVER["REQUEST_METHOD"]=="POST"
	&& (strlen($_POST["save"]) > 0 || strlen($_POST["apply"]) > 0))
{
	$dbUserProps = CSaleOrderUserProps::GetList(
			array("DATE_UPDATE" => "DESC"),
			array(
					"ID" => $ID,
					"USER_ID" => IntVal($GLOBALS["USER"]->GetID())
				),
			false,
			false,
			array("ID", "PERSON_TYPE_ID", "DATE_UPDATE")
		);
	if (!($arUserProps = $dbUserProps->Fetch()))
		$errorMessage .= GetMessage("SALE_NO_PROFILE")."<br>";

	if (strlen($errorMessage) <= 0)
	{
		$NAME = Trim($_POST["NAME"]);
		if (strlen($NAME) <= 0)
			$errorMessage .= GetMessage("SALE_NO_NAME")."<br>";

		$dbOrderProps = CSaleOrderProps::GetList(
				array("SORT" => "ASC"),
				array(
						"PERSON_TYPE_ID" => $arUserProps["PERSON_TYPE_ID"],
						"USER_PROPS" => "Y"
					),
				false,
				false,
				array("ID", "PERSON_TYPE_ID", "NAME", "TYPE", "REQUIED", "DEFAULT_VALUE", "SORT", "USER_PROPS", "IS_LOCATION", "PROPS_GROUP_ID", "SIZE1", "SIZE2", "DESCRIPTION", "IS_EMAIL", "IS_PROFILE_NAME", "IS_PAYER", "IS_LOCATION4TAX", "CODE", "SORT")
			);
		while ($arOrderProps = $dbOrderProps->Fetch())
		{
			$bErrorField = False;
			$curVal = $_POST["ORDER_PROP_".$arOrderProps["ID"]];
			if ($arOrderProps["TYPE"] == "LOCATION" && $arOrderProps["IS_LOCATION"] == "Y")
			{
				$DELIVERY_LOCATION = IntVal($curVal);
				if (IntVal($curVal) <= 0)
					$bErrorField = True;
			}
			elseif ($arOrderProps["IS_PROFILE_NAME"] == "Y" || $arOrderProps["IS_PAYER"] == "Y" || $arOrderProps["IS_EMAIL"] == "Y")
			{
				if ($arOrderProps["IS_PROFILE_NAME"] == "Y")
				{
					$PROFILE_NAME = Trim($curVal);
					if (strlen($PROFILE_NAME) <= 0)
						$bErrorField = True;
				}
				if ($arOrderProps["IS_PAYER"] == "Y")
				{
					$PAYER_NAME = Trim($curVal);
					if (strlen($PAYER_NAME) <= 0)
						$bErrorField = True;
				}
				if ($arOrderProps["IS_EMAIL"] == "Y")
				{
					$USER_EMAIL = Trim($curVal);
					if (strlen($USER_EMAIL) <= 0 || !check_email($USER_EMAIL))
						$bErrorField = True;
				}
			}
			elseif ($arOrderProps["REQUIED"] == "Y")
			{
				if ($arOrderProps["TYPE"] == "TEXT" || $arOrderProps["TYPE"] == "TEXTAREA" || $arOrderProps["TYPE"] == "RADIO" || $arOrderProps["TYPE"] == "SELECT")
				{
					if (strlen($curVal) <= 0)
						$bErrorField = True;
				}
				elseif ($arOrderProps["TYPE"] == "LOCATION")
				{
					if (IntVal($curVal) <= 0)
						$bErrorField = True;
				}
				elseif ($arOrderProps["TYPE"] == "MULTISELECT")
				{
					if (!is_array($curVal) || count($curVal) <= 0)
						$bErrorField = True;
				}
			}
			if ($bErrorField)
				$errorMessage .= GetMessage("SALE_NO_FIELD")." \"".$arOrderProps["NAME"]."\".<br>";
		}
	}

	if (strlen($errorMessage) <= 0)
	{
		$arFields = array("NAME" => $NAME);
		if (!CSaleOrderUserProps::Update($ID, $arFields))
			$errorMessage .= GetMessage("SALE_ERROR_EDIT_PROF")."<br>";
	}

	if (strlen($errorMessage) <= 0)
	{
		CSaleOrderUserPropsValue::DeleteAll($ID);

		$dbOrderProps = CSaleOrderProps::GetList(
				array("SORT" => "ASC"),
				array(
						"PERSON_TYPE_ID" => $arUserProps["PERSON_TYPE_ID"],
						"USER_PROPS" => "Y"
					),
				false,
				false,
				array("ID", "PERSON_TYPE_ID", "NAME", "TYPE", "REQUIED", "DEFAULT_VALUE", "SORT", "USER_PROPS", "IS_LOCATION", "PROPS_GROUP_ID", "SIZE1", "SIZE2", "DESCRIPTION", "IS_EMAIL", "IS_PROFILE_NAME", "IS_PAYER", "IS_LOCATION4TAX", "CODE", "SORT")
			);
		while ($arOrderProps = $dbOrderProps->Fetch())
		{
			$curVal = $_POST["ORDER_PROP_".$arOrderProps["ID"]];
			if ($arOrderProps["TYPE"]=="MULTISELECT")
			{
				$curVal = "";
				for ($i = 0; $i < count($_POST["ORDER_PROP_".$arOrderProps["ID"]]); $i++)
				{
					if ($i > 0)
						$curVal .= ",";
					$curVal .= $_POST["ORDER_PROP_".$arOrderProps["ID"]][$i];
				}
			}

			if (strlen($curVal) > 0)
			{
				$arFields = array(
						"USER_PROPS_ID" => $ID,
						"ORDER_PROPS_ID" => $arOrderProps["ID"],
						"NAME" => $arOrderProps["NAME"],
						"VALUE" => $curVal
					);
				CSaleOrderUserPropsValue::Add($arFields);
			}
		}
	}

	if (strlen($errorMessage) > 0)
		$bInitVars = True;

	if (strlen($_POST["save"]) > 0 && strlen($errorMessage) <= 0)
		LocalRedirect($PATH_TO_LIST);
}



$dbUserProps = CSaleOrderUserProps::GetList(
		array("DATE_UPDATE" => "DESC"),
		array(
				"ID" => $ID,
				"USER_ID" => IntVal($GLOBALS["USER"]->GetID())
			),
		false,
		false,
		array("ID", "NAME", "USER_ID", "PERSON_TYPE_ID", "DATE_UPDATE")
	);
if ($arUserProps = $dbUserProps->Fetch())
{

	while (list($key, $val) = each($arUserProps))
		${"str_".$key} = htmlspecialchars($val);

	if ($bInitVars)
	{
		$arUserFields = &$DB->GetTableFieldsList("b_sale_user_props");
		for ($i = 0; $i < count($arUserFields); $i++)
			if (array_key_exists($arUserFields[$i], $_REQUEST))
				${"str_".$arUserFields[$i]} = htmlspecialchars($_REQUEST[$arUserFields[$i]]);
	}
	?>

	<?echo ShowError($errorMessage);?>

	<a name="tb"></a>
	<font class="text">
	<a href="<?= htmlspecialchars($PATH_TO_LIST) ?>"><?= GetMessage("SALE_RECORDS_LIST") ?></a>
	</font>
	<br><br>

	<form method="post" action="<?= $APPLICATION->GetCurPage() ?>">
	<input type="hidden" name="ID" value="<?= $ID ?>">
	<table border="0" cellspacing="0" cellpadding="0" width="100%" class="tableborder"><tr><td>
	<table border="0" cellspacing="1" cellpadding="3" width="100%">
		<tr>
			<td colspan="2" align="center" class="tablehead">
				<font class="tableheadtext"><b><?= str_replace("#ID#", $str_ID, GetMessage("STPPD_PROFILE_NO")) ?></b></font>
			</td>
		</tr>
		<tr>
			<td class="tablebody" width="40%" align="right">
				<font class="tablefieldtext"><?echo GetMessage("SALE_PERS_TYPE")?>:</font>
			</td>
			<td class="tablebody" width="60%" align="left">
				<font class="tablebodytext">
				<?
				$arPersType = CSalePersonType::GetByID($str_PERSON_TYPE_ID);
				echo $arPersType["NAME"];
				?>
				</font>
			</td>
		</tr>
		<tr>
			<td class="tablebody" width="40%" align="right">
				<font class="tablefieldtext"><?echo GetMessage("SALE_PNAME")?>:<font class="starrequired">*</font></font>
			</td>
			<td class="tablebody" width="60%" align="left">
				<font class="tablebodytext">
				<input type="text" name="NAME" value="<?echo $str_NAME;?>" size="40" class="inputtext">
				</font>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="tablebody">
				
			</td>
		</tr>

		<?
		if (!$bInitVars)
		{
			$db_propVals = CSaleOrderUserPropsValue::GetList(
					array("SORT" => "ASC"),
					array("USER_PROPS_ID" => $str_ID),
					false,
					false,
					array("ID", "ORDER_PROPS_ID", "VALUE", "SORT")
				);
			while ($arPropVals = $db_propVals->Fetch())
			{
				${"ORDER_PROP_".$arPropVals["ORDER_PROPS_ID"]} = $arPropVals["VALUE"];
			}
		}
		else
		{
			foreach ($_REQUEST as $key => $value)
			{
				if (substr($key, 0, strlen("ORDER_PROP_"))=="ORDER_PROP_")
					$$key = $value;
			}
		}

		$dbOrderPropsGroup = CSaleOrderPropsGroup::GetList(
				array("SORT" => "ASC"),
				array("PERSON_TYPE_ID" => $str_PERSON_TYPE_ID),
				false,
				false,
				array("ID", "PERSON_TYPE_ID", "NAME", "SORT")
			);
		while ($arOrderPropsGroup = $dbOrderPropsGroup->Fetch())
		{
			$dbOrderProps = CSaleOrderProps::GetList(
					array("SORT" => "ASC"),
					array(
							"PERSON_TYPE_ID" => $str_PERSON_TYPE_ID,
							"PROPS_GROUP_ID" => $arOrderPropsGroup["ID"],
							"USER_PROPS" => "Y"
						),
					false,
					false,
					array("ID", "PERSON_TYPE_ID", "NAME", "TYPE", "REQUIED", "DEFAULT_VALUE", "SORT", "USER_PROPS", "IS_LOCATION", "PROPS_GROUP_ID", "SIZE1", "SIZE2", "DESCRIPTION", "IS_EMAIL", "IS_PROFILE_NAME", "IS_PAYER", "IS_LOCATION4TAX", "CODE", "SORT")
				);
			if ($arOrderProps = $dbOrderProps->Fetch())
			{
				?>
				<tr>
					<td class="tablebody" colspan="2" align="center">
						<b><font class="tablebodytext"><?= $arOrderPropsGroup["NAME"]; ?></font></b>
					</td>
				</tr>
				<?
				do
				{
					?>
					<tr valign="middle">
						<td class="tablebody" width="50%" align="right" valign="top">
							<font class="tablefieldtext">
							<?= $arOrderProps["NAME"] ?>:
							<?
							if ($arOrderProps["REQUIED"]=="Y" || $arOrderProps["IS_EMAIL"]=="Y" || $arOrderProps["IS_PROFILE_NAME"]=="Y" || $arOrderProps["IS_LOCATION"]=="Y" || $arOrderProps["IS_PAYER"]=="Y")
							{
								?><font class="starrequired">*</font><?
							}
							?>
							</font>
						</td>
						<td class="tablebody" width="50%" align="left">
							<font class="tablebodytext">
							<?$curVal = ${"ORDER_PROP_".$arOrderProps["ID"]};?>
							<?if ($arOrderProps["TYPE"]=="CHECKBOX"):?>
								<input type="checkbox" name="ORDER_PROP_<?echo $arOrderProps["ID"] ?>" value="Y"<?if ($curVal=="Y" || !isset($curVal) && $arOrderProps["DEFAULT_VALUE"]=="Y") echo " checked";?> class="inputcheckbox">
							<?elseif ($arOrderProps["TYPE"]=="TEXT"):?>
								<input type="text" size="<?echo (IntVal($arOrderProps["SIZE1"])>0)?$arOrderProps["SIZE1"]:30; ?>" maxlength="250" value="<?echo (isset($curVal))?htmlspecialchars($curVal):htmlspecialchars($arOrderProps["DEFAULT_VALUE"]);?>" name="ORDER_PROP_<?echo $arOrderProps["ID"] ?>" class="inputtext">
							<?elseif ($arOrderProps["TYPE"]=="SELECT"):?>
								<select name="ORDER_PROP_<?echo $arOrderProps["ID"] ?>" size="<?echo (IntVal($arOrderProps["SIZE1"])>0)?$arOrderProps["SIZE1"]:1; ?>" class="inputselect">
									<?$db_vars = CSaleOrderPropsVariant::GetList(($by="SORT"), ($order="ASC"), Array("ORDER_PROPS_ID"=>$arOrderProps["ID"]))?>
									<?while ($vars = $db_vars->Fetch()):?>
										<option value="<?echo $vars["VALUE"]?>"<?if ($vars["VALUE"]==$curVal || !isset($curVal) && $vars["VALUE"]==$arOrderProps["DEFAULT_VALUE"]) echo " selected"?>><?echo htmlspecialchars($vars["NAME"])?></option>
									<?endwhile;?>
								</select>
							<?elseif ($arOrderProps["TYPE"]=="MULTISELECT"):?>
								<select multiple name="ORDER_PROP_<?echo $arOrderProps["ID"] ?>[]" size="<?echo (IntVal($arOrderProps["SIZE1"])>0)?$arOrderProps["SIZE1"]:5; ?>" class="inputselect">
									<?
									$arCurVal = array();
									$arCurVal = explode(",", $curVal);
									for ($i = 0; $i<count($arCurVal); $i++)
										$arCurVal[$i] = Trim($arCurVal[$i]);
									$arDefVal = Split(",", $arOrderProps["DEFAULT_VALUE"]);
									for ($i = 0; $i<count($arDefVal); $i++)
										$arDefVal[$i] = Trim($arDefVal[$i]);

									$db_vars = CSaleOrderPropsVariant::GetList(($by="SORT"), ($order="ASC"), Array("ORDER_PROPS_ID"=>$arOrderProps["ID"]));
									?>
									<?while ($vars = $db_vars->Fetch()):?>
										<option value="<?echo $vars["VALUE"]?>"<?if (in_array($vars["VALUE"], $arCurVal) || !isset($curVal) && in_array($vars["VALUE"], $arDefVal)) echo" selected"?>><?echo htmlspecialchars($vars["NAME"])?></option>
									<?endwhile;?>
								</select>
							<?elseif ($arOrderProps["TYPE"]=="TEXTAREA"):?>
								<textarea rows="<?echo (IntVal($arOrderProps["SIZE2"])>0)?$arOrderProps["SIZE2"]:4; ?>" cols="<?echo (IntVal($arOrderProps["SIZE1"])>0)?$arOrderProps["SIZE1"]:40; ?>" name="ORDER_PROP_<?echo $arOrderProps["ID"] ?>" class="inputtextarea"><?echo (isset($curVal))?htmlspecialchars($curVal):htmlspecialchars($arOrderProps["DEFAULT_VALUE"]);?></textarea>
							<?elseif ($arOrderProps["TYPE"]=="LOCATION"):?>
								<select name="ORDER_PROP_<?echo $arOrderProps["ID"] ?>" size="<?echo (IntVal($arOrderProps["SIZE1"])>0)?$arOrderProps["SIZE1"]:1; ?>" class="inputselect">
									<?$db_vars = CSaleLocation::GetList(Array("SORT"=>"ASC", "COUNTRY_NAME_LANG"=>"ASC", "CITY_NAME_LANG"=>"ASC"), array(), LANGUAGE_ID)?>
									<?while ($vars = $db_vars->Fetch()):?>
										<option value="<?echo $vars["ID"]?>"<?if (IntVal($vars["ID"])==IntVal($curVal) || !isset($curVal) && IntVal($vars["ID"])==IntVal($arOrderProps["DEFAULT_VALUE"])) echo " selected"?>><?echo htmlspecialchars($vars["COUNTRY_NAME"]." - ".$vars["CITY_NAME"])?></option>
									<?endwhile;?>
								</select>
							<?elseif ($arOrderProps["TYPE"]=="RADIO"):?>
								<?$db_vars = CSaleOrderPropsVariant::GetList(($by="SORT"), ($order="ASC"), Array("ORDER_PROPS_ID"=>$arOrderProps["ID"]))?>
								<?while ($vars = $db_vars->Fetch()):?>
									<input type="radio" name="ORDER_PROP_<?echo $arOrderProps["ID"] ?>" value="<?echo $vars["VALUE"]?>"<?if ($vars["VALUE"]==$curVal || !isset($curVal) && $vars["VALUE"]==$arOrderProps["DEFAULT_VALUE"]) echo " checked"?> class="inputradio"><?echo htmlspecialchars($vars["NAME"])?><br>
								<?endwhile;?>
							<?endif?>

							<?if (strlen($arOrderProps["DESCRIPTION"])>0):?>
								<br><small><?echo $arOrderProps["DESCRIPTION"] ?></small>
							<?endif?>
							</font>
						</td>
					</tr>
					<?
				}
				while ($arOrderProps = $dbOrderProps->Fetch());
			}
		}
		?>

	</table>
	</td></tr></table>

	<br>
	<div align="left">
		<input type="submit" name="save" value="<?echo GetMessage("SALE_SAVE") ?>" class="inputbuttonflat">
		&nbsp;
		<input type="submit" name="apply" value="<?=GetMessage("SALE_APPLY")?>" class="inputbuttonflat">
		&nbsp;
		<input type="reset" value="<?echo GetMessage("SALE_RESET")?>" class="inputbuttonflat">
	</div>
	</form>

	<?
}
else
{
	?>
	<font class="text"><?echo GetMessage("SALE_NO_PROFILE")?></font>
	<?
}

//*******************************************************
	else:
		?>
		<font class="text"><b><?= GetMessage("STPPD_NEED_AUTH") ?></b></font>
		<?
	endif;
else:
	?>
	<font class="text"><b><?echo GetMessage("SALE_NO_MODULE_X")?></b></font>
	<?
endif;
?>