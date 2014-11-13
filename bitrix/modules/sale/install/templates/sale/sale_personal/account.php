<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);

if (CModule::IncludeModule("sale")):
	$APPLICATION->SetTitle(GetMessage("STPA_TITLE"));

	if ($GLOBALS["USER"]->IsAuthorized()):
//*******************************************************

$dbAccountList = CSaleUserAccount::GetList(
		array("CURRENCY" => "ASC"),
		array("USER_ID" => IntVal($GLOBALS["USER"]->GetID())),
		false,
		false,
		array("ID", "CURRENT_BUDGET", "CURRENCY", "TIMESTAMP_X")
	);

if ($arAccountList = $dbAccountList->Fetch())
{
	?>
	<font class="text">
	<?= str_replace("#DATE#", date(CDatabase::DateFormatToPHP(CSite::GetDateFormat("SHORT", SITE_ID))), GetMessage("STPA_MY_ACCOUNT")) ?>
	</font><br>
	<ul>
	<?
	do
	{
		$arCurrency = CCurrencyLang::GetByID($arAccountList["CURRENCY"], LANGUAGE_ID);
		?>
		<li><font class="text"><?= str_replace("#CURRENCY#", $arCurrency["CURRENCY"]." (".$arCurrency["FULL_NAME"].")", str_replace("#SUM#", SaleFormatCurrency($arAccountList["CURRENT_BUDGET"], $arAccountList["CURRENCY"]), GetMessage("STPA_IN_CUR"))) ?></font></li>
		<?
	}
	while ($arAccountList = $dbAccountList->Fetch());
	?>
	</ul>
	<?
}
else
{
	?>
	<font class="text"><b><?= GetMessage("STPA_NO_ACCOUNT") ?></b></font>
	<?
}

//*******************************************************
	else:
		?>
		<font class="text"><b><?= GetMessage("STPA_NO_AUTH") ?></b></font>
		<?
	endif;
else:
	?>
	<font class="text"><b><?= GetMessage("STPA_NO_SALE") ?></b></font>
	<?
endif;
?>