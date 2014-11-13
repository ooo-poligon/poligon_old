<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

$PERSON_TYPE = IntVal($PERSON_TYPE);

include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lang/", "/order_props_view.php"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage("SALE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
	
<form method="post" action="" name="bform">
<?
function PrintPropsForm($PERSON_TYPE, $USER_PROPS = "Y", $PRINT_TITLE = "")
{
	$bPrintHeader = True;
	$db_propsGroup = CSaleOrderPropsGroup::GetList(($by="SORT"), ($order="ASC"), Array("PERSON_TYPE_ID"=>$PERSON_TYPE));
	while ($propsGroup = $db_propsGroup->Fetch())
	{
		$db_props = CSaleOrderProps::GetList(($by="SORT"), ($order="ASC"), Array("PERSON_TYPE_ID"=>$PERSON_TYPE, "PROPS_GROUP_ID"=>$propsGroup["ID"], "USER_PROPS"=>$USER_PROPS));
		if ($props = $db_props->Fetch())
		{
			if ($bPrintHeader && strlen($PRINT_TITLE)>0)
			{
				?>
				<tr>
					<td class="tablehead4" colspan="2">
						<font class="tableheadtext"><b><?echo $PRINT_TITLE ?></b></font>
					</td>
				</tr>
				<?
				$bPrintHeader = False;
			}
			?>
			<tr>
				<td class="tablebody4" colspan="2" align="center">
					<b><font class="tablebodytext"><?echo $propsGroup["NAME"];?></font></b>
				</td>
			</tr>
			<?
			do
			{
				?>
				<tr valign="middle">
					<td class="tablebody1" width="50%" align="right" valign="top">
						<?if ($props["REQUIED"]=="Y" || $props["IS_EMAIL"]=="Y" || $props["IS_PROFILE_NAME"]=="Y" || $props["IS_LOCATION"]=="Y" || $props["IS_PAYER"]=="Y"):?><font class="star required">*</font><?endif;?>
						<font class="tablebodytext"><?echo $props["NAME"] ?>:</font>
					</td>
					<td class="tablebody3" width="50%" align="left">
						<font class="tablebodytext">
						<?$curVal = $GLOBALS["ORDER_PROP_".$props["ID"]];?>
						<?if ($props["TYPE"]=="CHECKBOX"):?>
							<input type="checkbox" name="ORDER_PROP_<?echo $props["ID"] ?>" value="Y"<?if ($curVal=="Y" || !isset($curVal) && $props["DEFAULT_VALUE"]=="Y") echo " checked";?>>
						<?elseif ($props["TYPE"]=="TEXT"):?>
							<input type="text" size="<?echo (IntVal($props["SIZE1"])>0)?$props["SIZE1"]:30; ?>" maxlength="250" value="<?echo (isset($curVal))?htmlspecialchars($curVal):htmlspecialchars($props["DEFAULT_VALUE"]);?>" name="ORDER_PROP_<?echo $props["ID"] ?>">
						<?elseif ($props["TYPE"]=="SELECT"):?>
							<select name="ORDER_PROP_<?echo $props["ID"] ?>" size="<?echo (IntVal($props["SIZE1"])>0)?$props["SIZE1"]:1; ?>">
								<?$db_vars = CSaleOrderPropsVariant::GetList(($by="SORT"), ($order="ASC"), Array("ORDER_PROPS_ID"=>$props["ID"]))?>
								<?while ($vars = $db_vars->Fetch()):?>
									<option value="<?echo $vars["VALUE"]?>"<?if ($vars["VALUE"]==$curVal || !isset($curVal) && $vars["VALUE"]==$props["DEFAULT_VALUE"]) echo " selected"?>><?echo htmlspecialchars($vars["NAME"])?></option>
								<?endwhile;?>
							</select>
						<?elseif ($props["TYPE"]=="MULTISELECT"):?>
							<select multiple name="ORDER_PROP_<?echo $props["ID"] ?>[]" size="<?echo (IntVal($props["SIZE1"])>0)?$props["SIZE1"]:5; ?>">
								<?
								$arCurVal = array();
								for ($i = 0; $i<count($curVal); $i++)
									$arCurVal[$i] = Trim($curVal[$i]);
								$arDefVal = Split(",", $props["DEFAULT_VALUE"]);
								for ($i = 0; $i<count($arDefVal); $i++)
									$arDefVal[$i] = Trim($arDefVal[$i]);

								$db_vars = CSaleOrderPropsVariant::GetList(($by="SORT"), ($order="ASC"), Array("ORDER_PROPS_ID"=>$props["ID"]));
								?>
								<?while ($vars = $db_vars->Fetch()):?>
									<option value="<?echo $vars["VALUE"]?>"<?if (in_array($vars["VALUE"], $arCurVal) || !isset($curVal) && in_array($vars["VALUE"], $arDefVal)) echo" selected"?>><?echo htmlspecialchars($vars["NAME"])?></option>
								<?endwhile;?>
							</select>
						<?elseif ($props["TYPE"]=="TEXTAREA"):?>
							<textarea rows="<?echo (IntVal($props["SIZE2"])>0)?$props["SIZE2"]:4; ?>" cols="<?echo (IntVal($props["SIZE1"])>0)?$props["SIZE1"]:40; ?>" name="ORDER_PROP_<?echo $props["ID"] ?>"><?echo (isset($curVal))?htmlspecialchars($curVal):htmlspecialchars($props["DEFAULT_VALUE"]);?></textarea>
						<?elseif ($props["TYPE"]=="LOCATION"):?>
							<select name="ORDER_PROP_<?echo $props["ID"] ?>" size="<?echo (IntVal($props["SIZE1"])>0)?$props["SIZE1"]:1; ?>">
								<?$db_vars = CSaleLocation::GetList(Array("SORT"=>"ASC", "COUNTRY_NAME_LANG"=>"ASC", "CITY_NAME_LANG"=>"ASC"), array(), LANG)?>
								<?while ($vars = $db_vars->Fetch()):?>
									<option value="<?echo $vars["ID"]?>"<?if (IntVal($vars["ID"])==IntVal($curVal) || !isset($curVal) && IntVal($vars["ID"])==IntVal($props["DEFAULT_VALUE"])) echo " selected"?>><?echo htmlspecialchars($vars["COUNTRY_NAME"]." - ".$vars["CITY_NAME"])?></option>
								<?endwhile;?>
							</select>
						<?elseif ($props["TYPE"]=="RADIO"):?>
							<?$db_vars = CSaleOrderPropsVariant::GetList(($by="SORT"), ($order="ASC"), Array("ORDER_PROPS_ID"=>$props["ID"]))?>
							<?while ($vars = $db_vars->Fetch()):?>
								<input type="radio" name="ORDER_PROP_<?echo $props["ID"] ?>" value="<?echo $vars["VALUE"]?>"<?if ($vars["VALUE"]==$curVal || !isset($curVal) && $vars["VALUE"]==$props["DEFAULT_VALUE"]) echo " checked"?>><?echo htmlspecialchars($vars["NAME"])?><br>
							<?endwhile;?>
						<?endif?>

						<?if (strlen($props["DESCRIPTION"])>0):?>
							<br><small><?echo $props["DESCRIPTION"] ?></small>
						<?endif?>
						</font>
					</td>
				</tr>
				<?
			}
			while ($props = $db_props->Fetch());
		}
	}
}	// end function PrintPropsForm($PERSON_TYPE, $USER_PROPS = "Y")
?>

<table border="0" cellpadding="3" cellspacing="1" width="100%">
	<?
	PrintPropsForm($PERSON_TYPE, "N", GetMessage("SALE_INFO_TXT"));
	?>

	<tr>
		<td class="tablehead4" colspan="2">
			<font class="tableheadtext"><b><?echo GetMessage("SALE_PROFILE")?></b></font>
		</td>
	</tr>
	<tr valign="middle">
		<td class="tablebody1" width="50%" align="right" valign="top">
			<font class="star required">*</font>
			<font class="tablebodytext"><?echo GetMessage("SALE_PROFILE_PROMT")?>:</font>
		</td>
		<td class="tablebody3" width="50%" align="left">
			<font class="tablebodytext">
				<script language="JavaScript">
				function SetContact(enabled)
				{
					<?
					$db_props = CSaleOrderProps::GetList(($by="SORT"), ($order="ASC"), Array("PERSON_TYPE_ID"=>$PERSON_TYPE, "USER_PROPS"=>"Y"));
					while ($props = $db_props->Fetch())
					{
						?>
						if('['+document.bform.ORDER_PROP_<?echo $props["ID"] ?>.type+']'=="[undefined]")
							document.bform.ORDER_PROP_<?echo $props["ID"] ?>[document.bform.ORDER_PROP_<?echo $props["ID"] ?>.length-1].disabled = !enabled;
						else
							document.bform.ORDER_PROP_<?echo $props["ID"] ?>.disabled = !enabled;
						<?
					}
					?>
				}
				</script>

				<input type="radio" name="PROFILE_ID" value="1" checked onClick="SetContact(false)"><b><?echo GetMessage("SALE_PROFILE_NAME")?></b><br>
					<?echo GetMessage("SALE_PROFILE_PROP1")?><br>
					<?echo GetMessage("SALE_PROFILE_PROP2")?><br>
					<?echo GetMessage("SALE_PROFILE_PROP3")?><br>
				<hr size="1" width="90%">

				<input type="radio" name="PROFILE_ID" value="0" onClick="SetContact(true)"><b><?echo GetMessage("SALE_NEW_PROFILE")?>:</b><br>
			</font>
		</td>
	</tr>

	<?
	PrintPropsForm($PERSON_TYPE, "Y", GetMessage("SALE_NEW_PROFILE"));
	?>
	<script language="JavaScript">
		SetContact(<?echo (isset($PROFILE_ID) && IntVal($PROFILE_ID)==0 || !isset($PROFILE_ID) && $bFirstProfile)?"true":"false";?>);
	</script>

</table>
</form>

<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>