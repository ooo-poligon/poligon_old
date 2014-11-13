<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);

if (CModule::IncludeModule("sale"))
{
	$APPLICATION->SetTitle(GetMessage("SPCAT1_TARIF_PLANS"));

	$affiliatePlanType = COption::GetOptionString("sale", "affiliate_plan_type", "N");
	$affiliateCurrency = CSaleLang::GetLangCurrency(SITE_ID);

	$dbPlan = CSaleAffiliatePlan::GetList(
		array("NAME" => "ASC"),
		array("SITE_ID" => SITE_ID, "ACTIVE" => "Y"),
		false,
		false,
		array("ID", "NAME", "DESCRIPTION", "BASE_RATE", "BASE_RATE_TYPE", "BASE_RATE_CURRENCY", "MIN_PLAN_VALUE")
	);
	if ($arPlan = $dbPlan->Fetch())
	{
		?>
		<ul>
		<?
		do
		{
			?>
			<li><font class="tablebodytext"><b><?= htmlspecialcharsex($arPlan["NAME"]) ?></b><br>
			<?
			if (StrLen($arPlan["DESCRIPTION"]) > 0)
			{
				?><small><?= htmlspecialcharsex($arPlan["DESCRIPTION"]) ?></small><br><?
			}
			?>
			<?echo GetMessage("SPCAT1_TARIF")?>
			<?
			if ($arPlan["BASE_RATE_TYPE"] == "P")
				echo round($arPlan["BASE_RATE"], SALE_VALUE_PRECISION)."%";
			else
				echo SaleFormatCurrency($arPlan["BASE_RATE"], $arPlan["BASE_RATE_CURRENCY"]);
			?>
			<br>
			<?
			if ($arPlan["MIN_PLAN_VALUE"] > 0)
			{
				if ($affiliatePlanType == "N")
					echo str_replace("#NUM#", IntVal($arPlan["MIN_PLAN_VALUE"]), GetMessage("SPCAT1_LIMIT1"));
				else
					echo str_replace("#SUM#", SaleFormatCurrency($arPlan["MIN_PLAN_VALUE"], $affiliateCurrency), GetMessage("SPCAT1_LIMIT2"));
				?>
				<br>
				<?
			}
			?>
			<br></font>
			<?
		}
		while ($arPlan = $dbPlan->Fetch());
		?>
		</ul>
		<?
	}
}
else
{
	?>
	<font class="text"><b><?echo GetMessage("SPCAT1_NO_SHOP")?></b></font>
	<?
}
?>