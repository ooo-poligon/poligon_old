<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);

$REGISTER_PAGE = Trim($REGISTER_PAGE);
if (StrLen($REGISTER_PAGE) <= 0)
	$REGISTER_PAGE = $GLOBALS["REGISTER_PAGE"];
if (StrLen($REGISTER_PAGE) <= 0)
	$REGISTER_PAGE = "register.php";

$SHOP_NAME = Trim($SHOP_NAME);
if (StrLen($SHOP_NAME) <= 0)
	$SHOP_NAME = $GLOBALS["SHOP_NAME"];

$SHOP_URL = Trim($SHOP_URL);
if (StrLen($SHOP_URL) <= 0)
	$SHOP_URL = $GLOBALS["SHOP_URL"];

if (StrLen($SHOP_NAME) <= 0 || StrLen($SHOP_URL) <= 0)
{
	$dbSite = CSite::GetList(($b="sort"), ($o="asc"), array("LID" => SITE_ID));
	if ($arSite = $dbSite->Fetch())
	{
		if (StrLen($SHOP_NAME) <= 0)
			$SHOP_NAME = $arSite["SITE_NAME"];
		if (StrLen($SHOP_URL) <= 0)
			$SHOP_URL = $arSite["SERVER_NAME"];
	}
}

if (StrLen($SHOP_NAME) <=0)
	$SHOP_NAME = COption::GetOptionString("main", "site_name", "");

if (StrLen($SHOP_URL) <=0)
{
	if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME) > 0)
		$SHOP_URL = SITE_SERVER_NAME;
	else
		$SHOP_URL = COption::GetOptionString("main", "server_name", "");
}

$AFF_REG_PAGE = Trim($AFF_REG_PAGE);
if (StrLen($AFF_REG_PAGE) <= 0)
	$AFF_REG_PAGE = $GLOBALS["AFF_REG_PAGE"];
if (StrLen($AFF_REG_PAGE) <= 0)
	$AFF_REG_PAGE = "/affiliate/register.php";


if (CModule::IncludeModule("sale"))
{
	$APPLICATION->SetTitle(GetMessage("SPCAT3_TECH_INSTR"));

	if ($GLOBALS["USER"]->IsAuthorized())
	{
		$dbAffiliate = CSaleAffiliate::GetList(
			array("TRANSACT_DATE" => "ASC"),
			array(
				"USER_ID" => IntVal($GLOBALS["USER"]->GetID()),
				"SITE_ID" => SITE_ID,
			),
			false,
			false,
			array("ID", "PLAN_ID", "ACTIVE", "PAID_SUM", "APPROVED_SUM", "PENDING_SUM", "LAST_CALCULATE")
		);
		if ($arAffiliate = $dbAffiliate->Fetch())
		{
			if ($arAffiliate["ACTIVE"] == "Y")
			{
				$affiliateParam = COption::GetOptionString("sale", "affiliate_param_name", "partner");
				?>
				<font class="text">
				<b><?echo GetMessage("SPCAT3_TEXT_LINK")?></b><br><br>

				<?echo GetMessage("SPCAT3_VIEW")?> <a href="http://<?= $SHOP_URL ?>/?<?= $affiliateParam ?>=<?= $arAffiliate["ID"] ?>"><?= $SHOP_NAME ?></a><br>
				<?echo GetMessage("SPCAT3_HTML")?> &lt;a href="http://<?= $SHOP_URL ?>/?<?= $affiliateParam ?>=<?= $arAffiliate["ID"] ?>"&gt;<?= $SHOP_NAME ?>&lt;/a&gt;<br><br>

				<?= $affiliateParam ?>=<?= $arAffiliate["ID"] ?> <?echo GetMessage("SPCAT3_PARTNER_ID")?><br><br>

				<?echo GetMessage("SPCAT3_NOTE")?>
				</font>

				<?
				$dbAffiliateTier = CSaleAffiliateTier::GetList(
					array(),
					array("SITE_ID" => SITE_ID),
					false,
					false,
					array("RATE1", "RATE2", "RATE3", "RATE4", "RATE5")
				);
				if (($arAffiliateTier = $dbAffiliateTier->Fetch()) && DoubleVal($arAffiliateTier["RATE1"]) > 0)
				{
					?>
					<br><br>
					<font class="text">
					<b><?echo GetMessage("SPCAT3_AFF_REG")?></b><br><br>

					<?echo GetMessage("SPCAT3_VIEW")?> <a href="http://<?= $SHOP_URL ?><?= $AFF_REG_PAGE ?>?<?= $affiliateParam ?>=<?= $arAffiliate["ID"] ?>"><?echo str_replace("#NAME#", $SHOP_NAME, GetMessage("SPCAT3_LINK_TEXT")) ?></a><br>
					<?echo GetMessage("SPCAT3_HTML")?> &lt;a href="http://<?= $SHOP_URL ?><?= $AFF_REG_PAGE ?>?<?= $affiliateParam ?>=<?= $arAffiliate["ID"] ?>"&gt;<?echo str_replace("#NAME#", $SHOP_NAME, GetMessage("SPCAT3_LINK_TEXT")) ?>&quot;&lt;/a&gt;<br><br>
					</font>
					<?
				}
			}
			else
			{
				?><font class="text"><b><?echo GetMessage("SPCAT3_UNACTIVE_AFF")?></b></font><?
			}
		}
		else
		{
			LocalRedirect($REGISTER_PAGE."?REDIRECT_PAGE=".UrlEncode($APPLICATION->GetCurPage()));
			die();
		}
	}
	else
	{
		LocalRedirect($REGISTER_PAGE."?REDIRECT_PAGE=".UrlEncode($APPLICATION->GetCurPage()));
		die();
	}
}
else
{
	?>
	<font class="text"><b><?echo GetMessage("SPCAT3_NO_SHOP")?></b></font>
	<?
}
?>