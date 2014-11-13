<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
IncludeTemplateLangFile(__FILE__);

$REDIRECT_PAGE = Trim($REDIRECT_PAGE);
if (StrLen($REDIRECT_PAGE) <= 0)
	$REDIRECT_PAGE = $_REQUEST["REDIRECT_PAGE"];
if (StrLen($REDIRECT_PAGE) <= 0)
	$REDIRECT_PAGE = "index.php";

if (CModule::IncludeModule("sale"))
{
	CSaleAffiliate::GetAffiliate();

	$errorMessage = "";

	if (!$GLOBALS["USER"]->IsAuthorized())
	{
		if ($_REQUEST["do_authorize"] == "Y")
		{
			$USER_LOGIN = $_REQUEST["USER_LOGIN"];
			if (strlen($USER_LOGIN) <= 0)
				$errorMessage .= GetMessage("SPCR1_ON_LOGIN").".<br>";

			$USER_PASSWORD = $_REQUEST["USER_PASSWORD"];

			if (strlen($errorMessage) <= 0)
			{
				$arAuthResult = $GLOBALS["USER"]->Login($USER_LOGIN, $USER_PASSWORD, "N");
				if ($arAuthResult != False && $arAuthResult["TYPE"] == "ERROR")
					$errorMessage .= GetMessage("SPCR1_ERR_REG").((strlen($arAuthResult["MESSAGE"]) > 0) ? ": ".$arAuthResult["MESSAGE"] : ".<br>" );
			}
		}
		elseif ($_REQUEST["do_register"] == "Y")
		{
			$NEW_NAME = $_REQUEST["NEW_NAME"];
			if (strlen($NEW_NAME) <= 0)
				$errorMessage .= GetMessage("SPCR1_NO_NAME").".<br>";

			$NEW_LAST_NAME = $_REQUEST["NEW_LAST_NAME"];
			if (strlen($NEW_LAST_NAME) <= 0)
				$errorMessage .= GetMessage("SPCR1_NO_LASTNAME").".<br>";

			$NEW_EMAIL = $_REQUEST["NEW_EMAIL"];
			if (strlen($NEW_EMAIL) <= 0)
				$errorMessage .= GetMessage("SPCR1_NO_EMAIL").".<br>";
			elseif (!check_email($NEW_EMAIL))
				$errorMessage .= GetMessage("SPCR1_BAD_EMAIL").".<br>";

			$NEW_LOGIN = $_REQUEST["NEW_LOGIN"];
			if (strlen($NEW_LOGIN) <= 0)
				$errorMessage .= GetMessage("SPCR1_NO_LOGIN").".<br>";

			$NEW_PASSWORD = $_REQUEST["NEW_PASSWORD"];
			if (strlen($NEW_PASSWORD) <= 0)
				$errorMessage .= GetMessage("SPCR1_NO_PASSWORD").".<br>";

			$NEW_PASSWORD_CONFIRM = $_REQUEST["NEW_PASSWORD_CONFIRM"];
			if (strlen($NEW_PASSWORD_CONFIRM) <= 0)
				$errorMessage .= GetMessage("SPCR1_NO_PASSWORD_CONF").".<br>";

			if (strlen($NEW_PASSWORD) > 0 && strlen($NEW_PASSWORD_CONFIRM) > 0 && $NEW_PASSWORD != $NEW_PASSWORD_CONFIRM)
				$errorMessage .= GetMessage("SPCR1_NO_CONF").".<br>";

			if (strlen($errorMessage) <= 0)
			{
				$arAuthResult = $GLOBALS["USER"]->Register($NEW_LOGIN, $NEW_NAME, $NEW_LAST_NAME, $NEW_PASSWORD, $NEW_PASSWORD_CONFIRM, $NEW_EMAIL, LANG, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
				if ($arAuthResult != False && $arAuthResult["TYPE"] == "ERROR")
					$errorMessage .= GetMessage("SPCR1_ERR_REGISTER").((strlen($arAuthResult["MESSAGE"]) > 0) ? ": ".$arAuthResult["MESSAGE"] : ".<br>" );
				else
					if ($GLOBALS["USER"]->IsAuthorized())
						CUser::SendUserInfo($GLOBALS["USER"]->GetID(), SITE_ID, GetMessage("INFO_REQ"));
			}
		}
	}

	$bAlreadyAffiliate = False;
	if ($GLOBALS["USER"]->IsAuthorized())
	{
		$dbAffiliate = CSaleAffiliate::GetList(
			array("TRANSACT_DATE" => "ASC"),
			array(
				"USER_ID" => IntVal($GLOBALS["USER"]->GetID()),
				"SITE_ID" => SITE_ID
			),
			false,
			false,
			array("ID", "ACTIVE")
		);
		if ($arAffiliate = $dbAffiliate->Fetch())
		{
			$bAlreadyAffiliate = True;
			if ($arAffiliate["ACTIVE"] == "Y")
			{
				LocalRedirect($REDIRECT_PAGE);
				die();
			}
			else
			{
				?><font class="text"><b><?echo GetMessage("SPCR1_UNACTIVE_AFF")?></b></font><?
			}
		}
	}

	if (!$bAlreadyAffiliate)
	{
		$APPLICATION->SetTitle(GetMessage("SPCR1_REGISTER_AFF"));

		/****************************************************************/
		/*********     ACTIONS    ***************************************/
		/****************************************************************/

		if ($_REQUEST["do_agree"] == "Y")
		{
			if ($_REQUEST["agree_agreement"] != "Y")
				$errorMessage .= GetMessage("SPCR1_NO_AGREE").".<br>";

			$AFF_SITE = Trim($_REQUEST["AFF_SITE"]);
			if (StrLen($AFF_SITE) <= 0)
				$errorMessage .= GetMessage("SPCR1_NO_SITE").".<br>";

			$AFF_DESCRIPTION = Trim($_REQUEST["AFF_DESCRIPTION"]);
			if (StrLen($AFF_DESCRIPTION) <= 0)
				$errorMessage .= GetMessage("SPCR1_NO_DESCR").".<br>";

			if (StrLen($errorMessage) <= 0)
			{
				$dbPlan = CSaleAffiliatePlan::GetList(
					array("MIN_PLAN_VALUE" => "ASC"),
					array(
						"SITE_ID" => SITE_ID,
						"ACTIVE" => "Y",
					),
					false,
					false,
					array("ID", "MIN_PLAN_VALUE")
				);
				$arPlan = $dbPlan->Fetch();

				if (!$arPlan)
					$errorMessage .= GetMessage("SPCR1_NO_PLANS").".<br>";
			}

			if (StrLen($errorMessage) <= 0)
			{
				$arFields = array(
					"SITE_ID" => SITE_ID,
					"USER_ID" => IntVal($GLOBALS["USER"]->GetID()),
					"PLAN_ID" => $arPlan["ID"],
					"ACTIVE" => ((DoubleVal($arPlan["MIN_PLAN_VALUE"]) > 0) ? "N" : "Y"),
					"DATE_CREATE" => date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time()),
					"PAID_SUM" => 0,
					"PENDING_SUM" => 0,
					"LAST_CALCULATE" => false,
					"FIX_PLAN" => "N",
					"AFF_SITE" => $AFF_SITE,
					"AFF_DESCRIPTION" => $AFF_DESCRIPTION
				);

				$affiliateID = CSaleAffiliate::GetAffiliate();
				if ($affiliateID > 0)
					$arFields["AFFILIATE_ID"] = $affiliateID;
				else
					$arFields["AFFILIATE_ID"] = false;

				if (!CSaleAffiliate::Add($arFields))
				{
					if ($ex = $GLOBALS["APPLICATION"]->GetException())
						$errorMessage .= $ex->GetString().".<br>";
					else
						$errorMessage .= GetMessage("SPCR1_ERR_AFF").".<br>";
				}
				else
				{
					LocalRedirect($REDIRECT_PAGE);
					die();
				}
			}
		}

		/****************************************************************/
		/*********     FORMS    *****************************************/
		/****************************************************************/

		if (!$GLOBALS["USER"]->IsAuthorized())
		{
			?>
			<?= ShowError($errorMessage) ?>
			<table border="0" cellspacing="0" cellpadding="1">
				<tr>
					<td width="45%" valign="top">
						<font class="tableheadtext">
						<b><?echo GetMessage("SPCR1_IF_REG")?></b>
						</font>
					</td>
					<td width="10%">
						&nbsp;
					</td>
					<td width="45%" valign="top">
						<font class="tableheadtext">
						<b><?echo GetMessage("SPCR1_IF_NOT_REG")?></b>
						</font>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<table border="0" cellspacing="0" cellpadding="1"><tr><td class="tableborder">
						<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<form method="post" action="<?= $APPLICATION->GetCurPage() ?>" name="sale_auth_form">
								<tr>
									<td class="tablebody">
										<font class="tablebodytext">
										<?echo GetMessage("SPCR1_IF_REMEMBER")?>
										</font>
									</td>
								</tr>
								<tr>
									<td class="tablebody" nowrap>
										<font class="tablebodytext">
										<?echo GetMessage("SPCR1_LOGIN")?> <font color="#FF0000">*</font><br>
										<input type="text" name="USER_LOGIN" maxlength="30" size="30" value="<?= ((strlen($USER_LOGIN) > 0) ? htmlspecialchars($USER_LOGIN) : htmlspecialchars(${COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"})) ?>" class="inputtext">&nbsp;&nbsp;&nbsp;
										</font>
									</td>
								</tr>
								<tr>
									<td class="tablebody" nowrap>
										<font class="tablebodytext">
										<?echo GetMessage("SPCR1_PASSWORD")?> <font color="#FF0000">*</font><br>
										<input type="password" name="USER_PASSWORD" maxlength="30" size="30" class="inputtext">&nbsp;&nbsp;&nbsp;
										</font>
									</td>
								</tr>
								<tr>
									<td class="tablebody" nowrap>
										<font class="tablebodytext">
										<a href="auth.php?forgot_password=yes&back_url=<?= $APPLICATION->GetCurPage() ?>"><?echo GetMessage("SPCR1_FORG_PASSWORD")?></a>
										</font>
									</td>
								</tr>
								<tr>
									<td class="tablebody" nowrap align="center">
										<font class="tablebodytext">
										<input type="submit" value="<?echo GetMessage("SPCR1_NEXT")?>" class="inputbuttonflat">
										<input type="hidden" name="do_authorize" value="Y">
										<input type="hidden" name="REDIRECT_PAGE" value="<?= htmlspecialchars($REDIRECT_PAGE) ?>">
										</font>
									</td>
								</tr>
							</form>
						</table>
						</td></tr></table>
					</td>
					<td>
						&nbsp;
					</td>
					<td valign="top">
						<table border="0" cellspacing="0" cellpadding="1"><tr><td class="tableborder">
						<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<form method="post" action="<?= $APPLICATION->GetCurPage() ?>" name="sale_reg_form">
								<tr>
									<td class="tablebody" nowrap>
										<font class="tablebodytext">
										<?echo GetMessage("SPCR1_NAME")?> <font color="#FF0000">*</font><br>
										<input type="text" name="NEW_NAME" size="40" value="<?= htmlspecialchars($NEW_NAME) ?>" class="inputtext">&nbsp;&nbsp;&nbsp;
										</font>
									</td>
								</tr>
								<tr>
									<td class="tablebody" nowrap>
										<font class="tablebodytext">
										<?echo GetMessage("SPCR1_LASTNAME")?> <font color="#FF0000">*</font><br>
										<input type="text" name="NEW_LAST_NAME" size="40" class="inputtext" value="<?= htmlspecialchars($NEW_LAST_NAME) ?>">&nbsp;&nbsp;&nbsp;
										</font>
									</td>
								</tr>
								<tr>
									<td class="tablebody" nowrap>
										<font class="tablebodytext">
										E-Mail <font color="#FF0000">*</font><br>
										<input type="text" name="NEW_EMAIL" size="40" class="inputtext" value="<?= htmlspecialchars($NEW_EMAIL) ?>">&nbsp;&nbsp;&nbsp;
										</font>
									</td>
								</tr>
								<tr>
									<td class="tablebody" nowrap>
										<font class="tablebodytext">
										<?echo GetMessage("SPCR1_LOGIN")?> <font color="#FF0000">*</font><br>
										<input type="text" name="NEW_LOGIN" size="30" class="inputtext" value="<?= htmlspecialchars($NEW_LOGIN) ?>">&nbsp;&nbsp;&nbsp;
										</font>
									</td>
								</tr>
								<tr>
									<td class="tablebody" nowrap>
										<font class="tablebodytext">
										<?echo GetMessage("SPCR1_PASSWORD")?> <font color="#FF0000">*</font><br>
										<input type="password" name="NEW_PASSWORD" size="30" class="inputtext">&nbsp;&nbsp;&nbsp;
										</font>
									</td>
								</tr>
								<tr>
									<td class="tablebody" nowrap>
										<font class="tablebodytext">
										<?echo GetMessage("SPCR1_PASS_CONF")?> <font color="#FF0000">*</font><br>
										<input type="password" name="NEW_PASSWORD_CONFIRM" size="30" class="inputtext">&nbsp;&nbsp;&nbsp;
										</font>
									</td>
								</tr>
								<?
								/* CAPTCHA */
								if (COption::GetOptionString("main", "captcha_registration", "N") == "Y")
								{
									?>
									<tr>
										<td class="tablebody"><br>
											<font class="tableheadtext"><b><?echo GetMessage("SPCR1_CAPTCHA")?></b></font>
										</td>
									</tr>
									<tr>
										<td class="tablebody">
											<?
											$capCode = $GLOBALS["APPLICATION"]->CaptchaGetCode();
											?>
											<input type="hidden" name="captcha_sid" value="<?= htmlspecialchars($capCode) ?>">
											<img src="/bitrix/tools/captcha.php?captcha_sid=<?= htmlspecialchars($capCode) ?>" width="180" height="40">
										</td>
									</tr>
									<tr valign="middle">
										<td class="tablebody">
											<font class="starrequired">*</font><font class="tablebodytext"><?echo GetMessage("SPCR1_CAPTCHA_WRD")?></font><br>
											<input type="text" name="captcha_word" size="30" maxlength="50" value="" class="inputtext">
										</td>
									</tr>
									<?
								}
								/* CAPTCHA */
								?>

								<tr>
									<td class="tablebody" align="center">
										<font class="tablebodytext">
										<input type="submit" value="<?echo GetMessage("SPCR1_NEXT")?>" class="inputbuttonflat">
										<input type="hidden" name="do_register" value="Y">
										<input type="hidden" name="REDIRECT_PAGE" value="<?= htmlspecialchars($REDIRECT_PAGE) ?>">
										</font>
									</td>
								</tr>
							</form>
						</table>
						</td></tr></table>
					</td>
				</tr>
			</table>
			<?
		}
		else
		{
			?>
			<?= ShowError($errorMessage) ?>
			<form method="post" action="<?= $APPLICATION->GetCurPage() ?>">
				<font class="text">

				<table border="0" cellspacing="0" cellpadding="1"><tr><td class="tableborder">
				<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td class="tablebody" nowrap valign="top">
							<font class="tablebodytext">
							<?echo GetMessage("SPCR1_SITE_URL")?><font color="#FF0000">*</font><br>
							<input type="text" name="AFF_SITE" maxlength="200" size="60" value="<?= htmlspecialchars($AFF_SITE) ?>" class="inputtext">&nbsp;&nbsp;&nbsp;
							</font>
						</td>
					</tr>
					<tr>
						<td class="tablebody" nowrap valign="top">
							<font class="tablebodytext">
							<?echo GetMessage("SPCR1_SITE_DESCR")?><font color="#FF0000">*</font><br>
							<textarea name="AFF_DESCRIPTION" rows="5" cols="55"><?= htmlspecialchars($AFF_DESCRIPTION) ?></textarea>
							</font>
						</td>
					</tr>

					<?
					$agreementTextFile = "agreement-".SITE_ID.".htm";
					if (!file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/".$agreementTextFile))
						$agreementTextFile = "agreement.htm";
					if (file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/".$agreementTextFile))
					{
						?>
						<tr>
							<td class="tablebody" nowrap valign="top">
								<iframe name="agreement_text" src="<?= BX_PERSONAL_ROOT."/php_interface/".$agreementTextFile ?>" width="620" height="400" border="0" frameBorder="1" scrolling="yes"></iframe>
							</td>
						</tr>
						<?
					}
					?>

					<tr>
						<td class="tablebody" nowrap valign="top">
							<font class="tablebodytext">
							<input class="typeinput" type="checkbox" name="agree_agreement" value="Y" id="agree_agreement_id">
							&nbsp;<label for="agree_agreement_id"><?echo GetMessage("SPCR1_I_AGREE")?></label>
							</font>
						</td>
					</tr>

					<tr>
						<td class="tablebody" nowrap valign="top">
							<input type="hidden" name="do_agree" value="Y">
							<input type="hidden" name="REDIRECT_PAGE" value="<?= htmlspecialchars($REDIRECT_PAGE) ?>">
							<input type="submit" value="<?echo GetMessage("SPCR1_REGISTER")?>" class="inputbuttonflat">
						</td>
					</tr>

				</table>
				</td></tr></table>
				<br><br>

				</font>
			</form>
			<?
		}
	}
}
else
{
	?>
	<font class="text"><b><?echo GetMessage("SPCR1_NO_SHOP")?></b></font>
	<?
}
?>