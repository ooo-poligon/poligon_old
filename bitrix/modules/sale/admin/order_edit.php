<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);


$ID = IntVal($ID);

if ($ID <= 0)
	LocalRedirect("sale_order.php?lang=".LANG."&".GetFilterParams("filter_", false));

$customTabber = new CAdminTabEngine("OnAdminSaleOrderEdit", array("ID" => $ID));

$errorMessage = "";

$bVarsFromForm = false;
$PARTIAL_SUBMIT = (($PARTIAL_SUBMIT == "Y") ? "Y" : "N");
if ($PARTIAL_SUBMIT == "Y")
	$bVarsFromForm = true;

$bUserCanViewOrder = CSaleOrder::CanUserViewOrder($ID, $GLOBALS["USER"]->GetUserGroupArray(), $GLOBALS["USER"]->GetID());
$bUserCanEditOrder = CSaleOrder::CanUserUpdateOrder($ID, $GLOBALS["USER"]->GetUserGroupArray());
$bUserCanCancelOrder = CSaleOrder::CanUserCancelOrder($ID, $GLOBALS["USER"]->GetUserGroupArray(), $GLOBALS["USER"]->GetID());
$bUserCanPayOrder = CSaleOrder::CanUserChangeOrderFlag($ID, "P", $GLOBALS["USER"]->GetUserGroupArray());
$bUserCanDeliverOrder = CSaleOrder::CanUserChangeOrderFlag($ID, "D", $GLOBALS["USER"]->GetUserGroupArray());
$bUserCanDeleteOrder = CSaleOrder::CanUserDeleteOrder($ID, $GLOBALS["USER"]->GetUserGroupArray(), $GLOBALS["USER"]->GetID());


$simpleForm = COption::GetOptionString("sale", "lock_catalog", "Y");
$bSimpleForm = (($simpleForm=="Y") ? True : False);

if (false && $bSimpleForm)
{
	$dbBasket = CSaleBasket::GetList(
			array(),
			array(
					"ORDER_ID" => $ID
				),
			false,
			false,
			array("ID", "MODULE", "CALLBACK_FUNC", "ORDER_CALLBACK_FUNC", "CANCEL_CALLBACK_FUNC", "PAY_CALLBACK_FUNC")
		);
	while ($arBasket = $dbBasket->Fetch())
	{
		if ($arBasket["MODULE"] != "catalog"
			|| $arBasket["CALLBACK_FUNC"] != "CatalogBasketCallback"
			|| $arBasket["ORDER_CALLBACK_FUNC"] != "CatalogBasketOrderCallback"
			|| $arBasket["CANCEL_CALLBACK_FUNC"] != "CatalogBasketCancelCallback"
			|| $arBasket["PAY_CALLBACK_FUNC"] != "CatalogPayOrderCallback")
		{
			$bSimpleForm = False;
			break;
		}
	}
}

if ($action == "update"
	&& $saleModulePermissions >= "U"
	&& $_SERVER["REQUEST_METHOD"] == "POST"
	&& check_bitrix_sessid()
	&& $bUserCanEditOrder
	&& $PARTIAL_SUBMIT != "Y"
	&& empty($dontsave))
{
	// *****************************************************************
	// *****  Preparing  ***********************************************
	// *****************************************************************
	$bTrabsactionStarted = False;

	// Order params
	$currentDate = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)));
	$dbOrderTmp = CSaleOrder::GetList(array(), array("ID" => $ID));//GetByID($ID);
	$arOrder = $dbOrderTmp->Fetch();
	if (!$arOrder)
		$errorMessage .= GetMessage("SOE_NO_ORDER").". ";

	if (CSaleOrder::IsLocked($ID, $lockedBY, $dateLock))
		$errorMessage .= str_replace("#DATE#", "$dateLock", str_replace("#ID#", "$lockedBY", GetMessage("SOE_ORDER_LOCKED"))).". ";

	if (!$customTabber->Check())
	{
		if ($ex = $APPLICATION->GetException())
			$errorMessage .= $ex->GetString();
		else
			$errorMessage .= "Error. ";
	}

	$LID = Trim($LID);
	if (strlen($LID) <= 0)
		$errorMessage .= GetMessage("SOE_EMPTY_SITE").". ";

	/*
	$PRICE = DoubleVal(str_replace(",", ".", $PRICE));
	if ($PRICE <= 0)
		$errorMessage .= GetMessage("SOE_EMPTY_PRICE").". ";
	*/

	/*
	$CURRENCY = Trim($CURRENCY);
	if (strlen($CURRENCY) <= 0)
		$errorMessage .= GetMessage("SOE_EMPTY_CURRENCY").". ";
	*/

	$USER_ID = IntVal($USER_ID);
	if ($USER_ID <= 0)
		$errorMessage .= GetMessage("SOE_EMPTY_USER").". ";

	$PERSON_TYPE_ID = IntVal($PERSON_TYPE_ID);
	if ($PERSON_TYPE_ID <= 0)
		$errorMessage .= GetMessage("SOE_EMPTY_PERS_TYPE").". ";

	if (($PERSON_TYPE_ID > 0) && !($arPersonType = CSalePersonType::GetByID($PERSON_TYPE_ID)))
		$errorMessage .= GetMessage("SOE_PERSON_NOT_FOUND")."<br>";

	$STATUS_ID = Trim($STATUS_ID);
	if (strlen($STATUS_ID) > 0)
	{
		if ($saleModulePermissions < "W")
		{
			$dbStatusList = CSaleStatus::GetList(
				array(),
				array(
					"GROUP_ID" => $GLOBALS["USER"]->GetUserGroupArray(),
					"PERM_STATUS" => "Y",
					"ID" => $STATUS_ID
				),
				array("ID", "MAX" => "PERM_STATUS"),
				false,
				array("ID")
			);
			if (!$dbStatusList->Fetch())
				$errorMessage .= str_replace("#STATUS_ID#", $STATUS_ID, GetMessage("SOE_NO_STATUS_PERMS")).". ";
		}
	}

	$CANCELED = (($CANCELED == "Y") ? "Y" : "N");
	$PAYED = (($PAYED == "Y") ? "Y" : "N");
	$ALLOW_DELIVERY = (($ALLOW_DELIVERY == "Y") ? "Y" : "N");

	$BASE_LANG_CURRENCY = CSaleLang::GetLangCurrency($LID);

	// Basket params
	$arBasketList = array();

	$arOrderPrice = array();
	$basketTotalPrice = 0;

	$arOrderWeight = array();
	$basketTotalWeight = 0;

	$arInd = array();

	$BASKET_COUNTER = IntVal($BASKET_COUNTER);
	for ($i = 0; $i <= $BASKET_COUNTER; $i++)
	{
		if(${"BASKET_PRODUCT_ID_".($i+2)} > 0 && $BASKET_COUNTER > $i)
			$BASKET_COUNTER++;
	}
	for ($i = 0; $i <= $BASKET_COUNTER; $i++)
	{
	
		${"BASKET_PRODUCT_ID_".$i} = IntVal(${"BASKET_PRODUCT_ID_".$i});

		if (${"BASKET_PRODUCT_ID_".$i} > 0)
		{		
			${"BASKET_MODULE_".$i} = Trim(${"BASKET_MODULE_".$i});
			if (strlen(${"BASKET_MODULE_".$i}) <= 0)
				$errorMessage .= str_replace("#ID#", ${"BASKET_PRODUCT_ID_".$i}, GetMessage("SOE_EMPTY_NODULE")).". ";

			${"BASKET_NAME_".$i} = Trim(${"BASKET_NAME_".$i});
			if (strlen(${"BASKET_NAME_".$i}) <= 0)
				$errorMessage .= str_replace("#ID#", ${"BASKET_PRODUCT_ID_".$i}, GetMessage("SOE_EMPTY_NAME")).". ";

			${"BASKET_CURRENCY_".$i} = Trim(${"BASKET_CURRENCY_".$i});
			if (strlen(${"BASKET_CURRENCY_".$i}) <= 0)
				$errorMessage .= str_replace("#ID#", ${"BASKET_PRODUCT_ID_".$i}, GetMessage("SOE_EMPTY_ITEM_CUR")).". ";
			
			${"BASKET_QUANTITY_".$i} = Trim(${"BASKET_QUANTITY_".$i});
			if (strlen(${"BASKET_QUANTITY_".$i}) <= 0)
				$errorMessage .= str_replace("#ID#", ${"BASKET_PRODUCT_ID_".$i}, GetMessage("SOE_EMPTY_ITEM_QUANTITY")).". ";
			$ind = 0;
			${"MOVE2NEW_ORDER_".$i} = ((${"MOVE2NEW_ORDER_".$i} == "Y") ? "Y" : "N");
			if ($BASKET_COUNTER == 0)
				${"MOVE2NEW_ORDER_".$i} = "N";

			if (${"MOVE2NEW_ORDER_".$i} == "Y")
				$ind = 1;

			if (!array_key_exists($ind, $arInd))
				$arInd[$ind] = -1;

			$arInd[$ind]++;

			if (!array_key_exists($ind, $arBasketList))
				$arBasketList[$ind] = array();

			$arBasketList[$ind][$arInd[$ind]] = array(
				"ID" => IntVal(${"BASKET_ID_".$i}),
				"IND" => IntVal($i),
				"PRODUCT_ID" => ${"BASKET_PRODUCT_ID_".$i},
				"PRODUCT_PRICE_ID" => ${"BASKET_PRODUCT_PRICE_ID_".$i},
				"MODULE" => ${"BASKET_MODULE_".$i},
				"NAME" => ${"BASKET_NAME_".$i},
				"DETAIL_PAGE_URL" => Trim(${"BASKET_DETAIL_PAGE_URL_".$i}),
				"PRICE" => roundEx(CCurrencyRates::ConvertCurrency(DoubleVal(str_replace(",", ".", ${"BASKET_PRICE_".$i})), ${"BASKET_CURRENCY_".$i}, $BASE_LANG_CURRENCY), SALE_VALUE_PRECISION),
				"CURRENCY" => $BASE_LANG_CURRENCY,
				"DISCOUNT_PRICE" => roundEx(CCurrencyRates::ConvertCurrency(DoubleVal(str_replace(",", ".", ${"BASKET_DISCOUNT_PRICE_".$i})), ${"BASKET_CURRENCY_".$i}, $BASE_LANG_CURRENCY), SALE_VALUE_PRECISION),
				"WEIGHT" => DoubleVal(${"BASKET_WEIGHT_".$i}),
				"QUANTITY" => DoubleVal(${"BASKET_QUANTITY_".$i}),
				"NOTES" => Trim(${"BASKET_NOTES_".$i}),
				"CALLBACK_FUNC" => Trim(${"BASKET_CALLBACK_FUNC_".$i}),
				"ORDER_CALLBACK_FUNC" => Trim(${"BASKET_ORDER_CALLBACK_FUNC_".$i}),
				"CANCEL_CALLBACK_FUNC" => Trim(${"BASKET_CANCEL_CALLBACK_FUNC_".$i}),
				"PAY_CALLBACK_FUNC" => Trim(${"BASKET_PAY_CALLBACK_FUNC_".$i}),
				"CATALOG_XML_ID" => Trim(${"BASKET_CATALOG_XML_ID_".$i}),
				"PRODUCT_XML_ID" => Trim(${"BASKET_PRODUCT_XML_ID_".$i}),
				"VAT_RATE" => DoubleVal(${"BASKET_VAT_RATE_".$i})
			);

			if (!array_key_exists($ind, $arOrderPrice))
				$arOrderPrice[$ind] = 0;
			$arOrderPrice[$ind] += $arBasketList[$ind][$arInd[$ind]]["PRICE"] * $arBasketList[$ind][$arInd[$ind]]["QUANTITY"];
			if (!array_key_exists($ind, $arOrderWeight))
				$arOrderWeight[$ind] = 0;
			$arOrderWeight[$ind] += $arBasketList[$ind][$arInd[$ind]]["WEIGHT"] * $arBasketList[$ind][$arInd[$ind]]["QUANTITY"];
			$basketTotalPrice += $arBasketList[$ind][$arInd[$ind]]["PRICE"] * $arBasketList[$ind][$arInd[$ind]]["QUANTITY"];
			$basketTotalWeight += $arBasketList[$ind][$arInd[$ind]]["WEIGHT"] * $arBasketList[$ind][$arInd[$ind]]["QUANTITY"];

			$arBasketProps = array();
			${"BASKET_PROP_COUNTER_".$i} = IntVal(${"BASKET_PROP_COUNTER_".$i});
			if (${"BASKET_PROP_COUNTER_".$i} > 0)
			{
				$jnd = -1;
				for ($j = 0; $j <= ${"BASKET_PROP_COUNTER_".$i}; $j++)
				{
					${"BASKET_PROP_NAME_".$i ."_".$j} = Trim(${"BASKET_PROP_NAME_".$i ."_".$j});
						$jnd++;
						$arBasketProps[$jnd] = array(
							//"ID" => IntVal(${"BASKET_PROP_ID_".$i."_".$j}),
							"NAME" => Trim(${"BASKET_PROP_NAME_".$i."_".$j}),
							"CODE" => Trim(${"BASKET_PROP_CODE_".$i."_".$j}),
							"VALUE" => ${"BASKET_PROP_VALUE_".$i."_".$j},
							"SORT" => IntVal(${"BASKET_PROP_SORT_".$i."_".$j})
						);
				}
			}
			$arBasketList[$ind][$arInd[$ind]]["PROPS"] = $arBasketProps;
		}			
	}
	if (count($arBasketList) <= 0)
		$errorMessage .= GetMessage("SOE_EMPTY_ITEMS").". ";

	// Order props
	$DELIVERY_LOCATION = 0;
	$TAX_LOCATION = 0;
	$arPropsList = array();
	$dbOrderProps = CSaleOrderProps::GetList(
		array("SORT" => "ASC"),
		array("PERSON_TYPE_ID" => $PERSON_TYPE_ID),
		false,
		false,
		array("ID", "NAME", "TYPE", "REQUIED", "IS_LOCATION", "IS_EMAIL", "IS_PROFILE_NAME", "IS_PAYER", "IS_LOCATION4TAX", "CODE", "SORT")
	);
	while ($arOrderProps = $dbOrderProps->Fetch())
	{
		$curVal = ${"ORDER_PROP_".$arOrderProps["ID"]};

		if (
			($arOrderProps["IS_LOCATION"]=="Y" || $arOrderProps["IS_LOCATION4TAX"]=="Y")
			&& IntVal($curVal) <= 0
			||
			($arOrderProps["IS_PROFILE_NAME"]=="Y" || $arOrderProps["IS_PAYER"]=="Y"/* || $arOrderProps["IS_EMAIL"]=="Y"*/)
			&& strlen($curVal) <= 0
			||
			$arOrderProps["REQUIED"]=="Y"
			&& $arOrderProps["TYPE"]=="LOCATION"
			&& IntVal($curVal) <= 0
			||
			$arOrderProps["REQUIED"]=="Y"
			&& ($arOrderProps["TYPE"]=="TEXT" || $arOrderProps["TYPE"]=="TEXTAREA" || $arOrderProps["TYPE"]=="RADIO" || $arOrderProps["TYPE"]=="SELECT")
			&& strlen($curVal) <= 0
			||
			$arOrderProps["REQUIED"]=="Y"
			&& $arOrderProps["TYPE"]=="MULTISELECT"
			&& (!is_array($curVal) || count($curVal) <= 0)
			)
		{
			$errorMessage .= str_replace("#NAME#", $arOrderProps["NAME"], GetMessage("SOE_EMPTY_PROP")).". ";
		}

		if ($arOrderProps["TYPE"] == "MULTISELECT")
		{
			$curVal = "";
			for ($i = 0; $i < count(${"ORDER_PROP_".$arOrderProps["ID"]}); $i++)
			{
				if ($i > 0)
					$curVal .= ",";
				$curVal .= ${"ORDER_PROP_".$arOrderProps["ID"]}[$i];
			}
		}

		if ($arOrderProps["TYPE"]=="LOCATION" && $arOrderProps["IS_LOCATION"] == "Y")
			$DELIVERY_LOCATION = IntVal($curVal);
		if ($arOrderProps["TYPE"]=="LOCATION" && $arOrderProps["IS_LOCATION4TAX"] == "Y")
			$TAX_LOCATION = IntVal($curVal);

		if (strlen($curVal)>0)
		{
			$arPropsList[] = array(
				"ORDER_PROPS_ID" => $arOrderProps["ID"],
				"NAME" => $arOrderProps["NAME"],
				"CODE" => $arOrderProps["CODE"],
				"VALUE" => $curVal
			);
		}
	}

	$bNeedReCount = ($RE_COUNT == "Y");
	$bFullOrderDivision = ($FULL_DIVISION == "Y");

	// *****************************************************************
	// *****  Saving  **************************************************
	// *****************************************************************
	if (strlen($errorMessage) <= 0)
	{
		$bTrabsactionStarted = True;
		$DB->StartTransaction();
	}

	if (strlen($errorMessage) <= 0)
	{
		// TAX EXEMPT ---------------------------------------------->
		if ($bNeedReCount)
		{
			$arTaxExempt = array();
			$arUserGroups = CUser::GetUserGroup($USER_ID);

			$dbTaxExemptList = CSaleTax::GetExemptList(array("GROUP_ID" => $arUserGroups));
			while ($arTaxExemptList = $dbTaxExemptList->Fetch())
				if (!in_array(IntVal($arTaxExemptList["TAX_ID"]), $arTaxExempt))
					$arTaxExempt[] = IntVal($arTaxExemptList["TAX_ID"]);
		}

		// DELIVERY ---------------------------------------------->
		if (strstr($DELIVERY_ID, ':') === false)
		{
			$DELIVERY_ID = IntVal($DELIVERY_ID);
			$bUseOldDelivery = true;
		}
		else
		{
			$bUseOldDelivery = false;
		}

		$arDeliveryPrice = array();
		for ($i = 0; $i < count($arBasketList); $i++)
			$arDeliveryPrice[$i] = 0;

		$deliveryPrice = 0;
		if ($bNeedReCount)
		{
			if ($bUseOldDelivery)
			{
				if ($DELIVERY_ID > 0)
				{
					if ($arDelivery = CSaleDelivery::GetByID($DELIVERY_ID))
						$deliveryPrice = roundEx(CCurrencyRates::ConvertCurrency($arDelivery["PRICE"], $arDelivery["CURRENCY"], $BASE_LANG_CURRENCY), SALE_VALUE_PRECISION);
					else
						$errorMessage .= GetMessage("SOE_DELIVERY_NOT_FOUND")."<br>";
				}
			}
			else
			{
				list($DELIVERY_SERVICE, $DELIVERY_PROFILE) = explode(':', $DELIVERY_ID);
				
				if (strlen($DELIVERY_SERVICE) > 0 && strlen($DELIVERY_PROFILE) > 0)
				{
					$arDeliveryOrder = array(
						"LOCATION_TO" => $DELIVERY_LOCATION,
						"LOCATION_FROM" => COption::GetOptionInt('sale', 'location_from', '', $LID),
						"WEIGHT" => $basketTotalWeight,
						"PRICE" => $basketTotalPrice,
					);
					$arDeliveryResult = CSaleDeliveryHandler::CalculateFull($DELIVERY_SERVICE, $DELIVERY_PROFILE, $arDeliveryOrder, $BASE_LANG_CURRENCY, $LID);
					
					if ($arDeliveryResult["RESULT" != 'OK'])
					{
						if ($ex = $APPLICATION->GetException())
							$errorMessage .= $ex->GetString().'<br>';
						else
							$errorMessage .= GetMessage("SOE_DELIVERY_ERROR").'<br>';
					}
					else
					{
						$deliveryPrice = roundEx($arDeliveryResult["VALUE"], SALE_VALUE_PRECISION);
					}
				}
			}
		}
		else
		{
			$PRICE_DELIVERY = DoubleVal(str_replace(",", ".", $PRICE_DELIVERY));

			if ($PRICE_DELIVERY > 0)
				$deliveryPrice = roundEx(CCurrencyRates::ConvertCurrency($PRICE_DELIVERY, $PRICE_DELIVERY_CURRENCY, $BASE_LANG_CURRENCY), SALE_VALUE_PRECISION);
		}

		if ($bFullOrderDivision)
		{
			if ($deliveryPrice > 0)
			{
				for ($i = 0; $i < count($arDeliveryPrice); $i++)
					$arDeliveryPrice[$i] = $deliveryPrice;
			}
		}
		else
		{
			// !!!!!!!!!!!!!!!!!!!!!!
			if ($deliveryPrice > 0)
			{
				if ($basketTotalWeight > 0)
				{
					for ($i = 0; $i < count($arDeliveryPrice); $i++)
						$arDeliveryPrice[$i] = roundEx($deliveryPrice * $arOrderWeight[$i] / $basketTotalWeight, SALE_VALUE_PRECISION);
				}
				else
				{
					for ($i = 0; $i < count($arDeliveryPrice); $i++)
						$arDeliveryPrice[$i] = roundEx($deliveryPrice / count($arDeliveryPrice), SALE_VALUE_PRECISION);
				}
			}

			$checkDeliverySum = 0;
			for ($i = 0; $i < count($arDeliveryPrice); $i++)
				$checkDeliverySum += $arDeliveryPrice[$i];
			if ($deliveryPrice > $checkDeliverySum)
				$arDeliveryPrice[0] += $deliveryPrice - $checkDeliverySum;
		}

		// PAY SYSTEM ---------------------------------------------->
		$PAY_SYSTEM_ID = IntVal($PAY_SYSTEM_ID);
		if ($PAY_SYSTEM_ID <= 0)
			$errorMessage .= GetMessage("SOE_PAYSYS_EMPTY")."<br>";
		if (($PAY_SYSTEM_ID > 0) && !($arPaySys = CSalePaySystem::GetByID($PAY_SYSTEM_ID, $PERSON_TYPE_ID)))
			$errorMessage .= GetMessage("SOE_PAYSYS_NOT_FOUND")."<br>";

		// DISCOUNT ---------------------------------------------->
		for ($i = 0; $i < count($arBasketList); $i++)
			for ($j = 0; $j < count($arBasketList[$i]); $j++)
				$arBasketList[$i][$j]["REAL_PRICE"] = $arBasketList[$i][$j]["PRICE"];

		$arDiscountPrice = array();
		for ($i = 0; $i < count($arBasketList); $i++)
			$arDiscountPrice[$i] = 0;

		if ($bNeedReCount)
		{
			if ($bFullOrderDivision)
			{
				for ($i = 0; $i < count($arBasketList); $i++)
				{
					$dbDiscount = CSaleDiscount::GetList(
							array("SORT" => "ASC"),
							array(
									"LID" => $LID,
									"ACTIVE" => "Y",
									"!>ACTIVE_FROM" => Date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL"))),
									"!<ACTIVE_TO" => Date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL"))),
									"<=PRICE_FROM" => $arOrderPrice[$i],
									">=PRICE_TO" => $arOrderPrice[$i]
								),
							false,
							false,
							array("*")
						);

					if ($arDiscount = $dbDiscount->Fetch())
					{
						if ($arDiscount["DISCOUNT_TYPE"] == "P")
						{
							for ($j = 0; $j < count($arBasketList[$i]); $j++)
							{
								$curDiscount = roundEx(DoubleVal($arBasketList[$i][$j]["PRICE"]) * $arDiscount["DISCOUNT_VALUE"] / 100, SALE_VALUE_PRECISION);
								$arBasketList[$i][$j]["REAL_PRICE"] = DoubleVal($arBasketList[$i][$j]["PRICE"]) - $curDiscount;
								$arDiscountPrice[$i] += $curDiscount * $arBasketList[$i][$j]["QUANTITY"];
							}
						}
						else
						{
							$discountPrice = CCurrencyRates::ConvertCurrency($arDiscount["DISCOUNT_VALUE"], $arDiscount["CURRENCY"], $BASE_LANG_CURRENCY);
							$discountPrice = roundEx($discountPrice, SALE_VALUE_PRECISION);
							for ($j = 0; $j < count($arBasketList[$i]); $j++)
							{
								$curDiscount = roundEx(DoubleVal($arBasketList[$i][$j]["PRICE"]) * $DISCOUNT_PRICE / $arOrderPrice[$i], SALE_VALUE_PRECISION);
								$arBasketList[$i][$j]["REAL_PRICE"] = DoubleVal($arBasketList[$i][$j]["PRICE"]) - $curDiscount;
								$arDiscountPrice[$i] += $curDiscount * $arBasketList[$i][$j]["QUANTITY"];
							}
						}
					}
				}
			}
			else
			{
				$dbDiscount = CSaleDiscount::GetList(
						array("SORT" => "ASC"),
						array(
								"LID" => $LID,
								"ACTIVE" => "Y",
								"!>ACTIVE_FROM" => Date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL"))),
								"!<ACTIVE_TO" => Date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL"))),
								"<=PRICE_FROM" => $basketTotalPrice,
								">=PRICE_TO" => $basketTotalPrice
							),
						false,
						false,
						array("*")
					);

				if ($arDiscount = $dbDiscount->Fetch())
				{
					if ($arDiscount["DISCOUNT_TYPE"] == "P")
					{
						for ($i = 0; $i < count($arBasketList); $i++)
						{
							for ($j = 0; $j < count($arBasketList[$i]); $j++)
							{
								$curDiscount = roundEx(DoubleVal($arBasketList[$i][$j]["PRICE"]) * $arDiscount["DISCOUNT_VALUE"] / 100, SALE_VALUE_PRECISION);
								$arBasketList[$i][$j]["REAL_PRICE"] = DoubleVal($arBasketList[$i][$j]["PRICE"]) - $curDiscount;
								$arDiscountPrice[$i] += $curDiscount * $arBasketList[$i][$j]["QUANTITY"];
							}
						}
					}
					else
					{
						$discountPrice = CCurrencyRates::ConvertCurrency($arDiscount["DISCOUNT_VALUE"], $arDiscount["CURRENCY"], $BASE_LANG_CURRENCY);
						$discountPrice = roundEx($discountPrice, SALE_VALUE_PRECISION);
						for ($i = 0; $i < count($arBasketList); $i++)
						{
							for ($j = 0; $j < count($arBasketList[$i]); $j++)
							{
								$curDiscount = roundEx(DoubleVal($arBasketList[$i][$j]["PRICE"]) * $DISCOUNT_PRICE / $basketTotalPrice, SALE_VALUE_PRECISION);
								$arBasketList[$i][$j]["REAL_PRICE"] = DoubleVal($arBasketList[$i][$j]["PRICE"]) - $curDiscount;
								$arDiscountPrice[$i] += $curDiscount * $arBasketList[$i][$j]["QUANTITY"];
							}
						}
					}
				}
			}
		}
		else
		{
			for ($i = 0; $i < count($arBasketList); $i++)
			{
				for ($j = 0; $j < count($arBasketList[$i]); $j++)
				{
					$arBasketList[$i][$j]["REAL_PRICE"] = $arBasketList[$i][$j]["PRICE"] - $arBasketList[$i][$j]["DISCOUNT_PRICE"];
					$arDiscountPrice[$i] += $arBasketList[$i][$j]["DISCOUNT_PRICE"] * $arBasketList[$i][$j]["QUANTITY"];
				}
			}
		}
		
		$bUseVat = false;
		for ($i = 0; $i < count($arBasketList); $i++)
		{
			for ($j = 0; $j < count($arBasketList[$i]); $j++)
			{
				if(DoubleVal($arBasketList[$i][$j]["VAT_RATE"]) > 0)
				{
					$bUseVat = true;
					if($arBasketList[$i][$j]["VAT_RATE"] > $vatRate)
						$vatRate = $arBasketList[$i][$j]["VAT_RATE"];
				}
			}
		}

		// TAX
		$arTaxPrice = array();
		for ($i = 0; $i < count($arBasketList); $i++)
			$arTaxPrice[$i] = 0;

		$arTaxList = array();
		$arOrderTaxList = array();
		for ($i = 0; $i < count($arBasketList); $i++)
			$arOrderTaxList[$i] = array();

		if ($bNeedReCount)
		{
			if(!$bUseVat)
			{
				$dbTaxRate = CSaleTaxRate::GetList(
					array("APPLY_ORDER" => "ASC"),
					array(
						"LID" => $LID,
						"PERSON_TYPE_ID" => $PERSON_TYPE_ID,
						"ACTIVE" => "Y",
						"LOCATION" => $TAX_LOCATION
					)
				);
				$i = -1;
				while ($arTaxRate = $dbTaxRate->Fetch())
				{
					if (!in_array(IntVal($arTaxRate["TAX_ID"]), $arTaxExempt))
					{
						$i++;
						$arTaxList[] = array(
							"ID" => 0,
							"IND" => $i,
							"TAX_NAME" => $arTaxRate["NAME"],
							"VALUE" => $arTaxRate["VALUE"],
							"VALUE_MONEY" => 0,
							"APPLY_ORDER" => $arTaxRate["APPLY_ORDER"],
							"CODE" => $arTaxRate["CODE"],
							"IS_IN_PRICE" => (($arTaxRate["IS_IN_PRICE"] == "Y") ? "Y" : "N")
						);
					}
				}
			}
			else
			{
				$arTaxList[] = Array(
							"ID" => 0,
							"TAX_NAME" => GetMessage("SOE_VAT"),
							"IS_PERCENT" => "Y",
							"VALUE" => $vatRate*100,
							"VALUE_MONEY" => 0,
							"APPLY_ORDER" => 100,
							"IS_IN_PRICE" => "Y",
							"CODE" => "VAT"
				);

			}
		
		}
		else
		{
			$TAX_COUNTER = IntVal($TAX_COUNTER);
			for ($i = 0; $i <= $TAX_COUNTER; $i++)
			{
				${"TAX_NAME_".$i} = Trim(${"TAX_NAME_".$i});

				if (strlen(${"TAX_NAME_".$i}) > 0)
				{
					${"TAX_VALUE_".$i} = DoubleVal(str_replace(",", ".", ${"TAX_VALUE_".$i}));
					if (${"TAX_VALUE_".$i} <= 0)
						$errorMessage .= str_replace("#NAME#", ${"TAX_NAME_".$i}, GetMessage("SOE_EMPTY_TAX_NAME")).". ";

					${"TAX_VALUE_".$i} = DoubleVal(str_replace(",", ".", ${"TAX_VALUE_".$i}));
					if (${"TAX_VALUE_".$i} <= 0)
						$errorMessage .= str_replace("#NAME#", ${"TAX_NAME_".$i}, GetMessage("SOE_EMPTY_TAX_SUM")).". ";

					$arTaxList[] = array(
						"ID" => IntVal(${"TAX_ID_".$i}),
						"IND" => IntVal($i),
						"TAX_NAME" => Trim(${"TAX_NAME_".$i}),
						"VALUE" => ${"TAX_VALUE_".$i},
						"VALUE_MONEY" => 0,
						"APPLY_ORDER" => IntVal(${"TAX_APPLY_ORDER_".$i}),
						"CODE" => Trim(${"TAX_CODE_".$i}),
						"IS_IN_PRICE" => ((${"TAX_IS_IN_PRICE_".$i} == "Y") ? "Y" : "N")
					);
				}
			}
		}

		if (count($arTaxList) > 0)
		{
			for ($i = 0; $i < count($arBasketList); $i++)
			{
				for ($j = 0; $j < count($arBasketList[$i]); $j++)
				{
					if(!$bUseVat)
					{
						$taxPrice = CSaleOrderTax::CountTaxes(
								$arBasketList[$i][$j]["REAL_PRICE"] * $arBasketList[$i][$j]["QUANTITY"],
								$arTaxList,
								$BASE_LANG_CURRENCY
							);

						for ($k = 0; $k < count($arTaxList); $k++)
							$arOrderTaxList[$i][$k]["VALUE_MONEY"] += $arTaxList[$k]["TAX_VAL"];
					}
					else
					{
						$arOrderTaxList[$i][0]["VALUE_MONEY"] += (($arBasketList[$i][$j]["REAL_PRICE"] / ($arBasketList[$i][$j]["VAT_RATE"] +1)) * $arBasketList[$i][$j]["VAT_RATE"]) * $arBasketList[$i][$j]["QUANTITY"];
					}
				}
			}

			for ($i = 0; $i < count($arBasketList); $i++)
			{
				for ($j = 0; $j < count($arTaxList); $j++)
				{
					if ($arTaxList[$j]["IS_IN_PRICE"] != "Y")
						$arTaxPrice[$i] += $arOrderTaxList[$i][$j]["VALUE_MONEY"];
				}
			}
			
			//print_r($arDeliveryPrice);
			if(!empty($deliveryPrice) && $COUNT_TAX_FOR_DELIVERY == "Y")
			{
				foreach($arDeliveryPrice as $i => $delPrice)
				{
					$taxPrice = CSaleOrderTax::CountTaxes(
							$delPrice,
							$arTaxList,
							$BASE_LANG_CURRENCY
						);

					for ($j = 0; $j < count($arTaxList); $j++)
					{
						$arOrderTaxList[$i][$j]["VALUE_MONEY"] += $arTaxList[$j]["TAX_VAL"];
						$arTaxPrice[$i] = $arOrderTaxList[$i][0]["VALUE_MONEY"];

						$arTaxList[$j]["VALUE_MONEY"] += $arTaxList[$j]["TAX_VAL"];
					}
				}
			}

		}
	}
	if (strlen($errorMessage) <= 0)
	{
		$arIDs = array();
		$arIDs[0] = $ID;

		$sumPaid = DoubleVal(str_replace(",", ".", $SUM_PAID));

		for ($i = 0; $i < count($arBasketList); $i++)
		{
			$totalOrderPrice = $arOrderPrice[$i] + $arDeliveryPrice[$i] + $arTaxPrice[$i] - $arDiscountPrice[$i];
			if ($sumPaid > $totalOrderPrice)
			{
				$sumPaid = $sumPaid - $totalOrderPrice;
				$sumPaid1 = $totalOrderPrice;
			}
			else
			{
				$sumPaid1 = $sumPaid;
				$sumPaid = 0;
			}

			$arFields = array(
				"LID" => $LID,
				"PERSON_TYPE_ID" => $PERSON_TYPE_ID,
				"PRICE" => $totalOrderPrice,
				"CURRENCY" => $BASE_LANG_CURRENCY,
				"USER_ID" => $USER_ID,
				"PAY_SYSTEM_ID" => $PAY_SYSTEM_ID,
				"PRICE_DELIVERY" => $arDeliveryPrice[$i],
				"DELIVERY_ID" => (strstr($DELIVERY_ID, ':') === false ? ($DELIVERY_ID > 0 ? $DELIVERY_ID : false) : $DELIVERY_ID),
				"DISCOUNT_VALUE" => $arDiscountPrice[$i],
				"TAX_VALUE" => $arTaxPrice[$i],
				"USER_DESCRIPTION" => $USER_DESCRIPTION,
				"SUM_PAID" => $sumPaid1,
				"ADDITIONAL_INFO" => $ADDITIONAL_INFO,
				"COMMENTS" => $COMMENTS,
				"RECOUNT_FLAG" => (($RE_COUNT == "Y") ? "Y" : "N"),
				"PAY_VOUCHER_NUM" => $PAY_VOUCHER_NUM,
				"PAY_VOUCHER_DATE" => $PAY_VOUCHER_DATE,
			);

			if ($i == 0)
			{
				$res = CSaleOrder::Update($arIDs[0], $arFields);
			}
			else
			{
				$arFields["PAYED"] = $arOrder["PAYED"];
				$arFields["DATE_PAYED"] = $arOrder["DATE_PAYED"];
				$arFields["PAY_VOUCHER_NUM"] = $arOrder["PAY_VOUCHER_NUM"];
				$arFields["PAY_VOUCHER_DATE"] = $arOrder["PAY_VOUCHER_DATE"];
				$arFields["EMP_PAYED_ID"] = $arOrder["EMP_PAYED_ID"];

				$arFields["CANCELED"] = $arOrder["CANCELED"];
				$arFields["REASON_CANCELED"] = $arOrder["REASON_CANCELED"];
				$arFields["DATE_CANCELED"] = $arOrder["DATE_CANCELED"];
				$arFields["EMP_CANCELED_ID"] = $arOrder["EMP_CANCELED_ID"];

				$arFields["STATUS_ID"] = $arOrder["STATUS_ID"];
				$arFields["DATE_STATUS"] = $arOrder["DATE_STATUS"];
				$arFields["EMP_STATUS_ID"] = $arOrder["EMP_STATUS_ID"];

				$arFields["ALLOW_DELIVERY"] = $arOrder["ALLOW_DELIVERY"];
				$arFields["DATE_ALLOW_DELIVERY"] = $arOrder["DATE_ALLOW_DELIVERY"];
				$arFields["EMP_ALLOW_DELIVERY_ID"] = $arOrder["EMP_ALLOW_DELIVERY_ID"];

				$arIDs[$i] = CSaleOrder::Add($arFields);
				$arIDs[$i] = IntVal($arIDs[$i]);
				$res = ($arIDs[$i] > 0);
			}

			if (!$res)
			{
				if ($ex = $APPLICATION->GetException())
					$errorMessage .= $ex->GetString();
				else
					$errorMessage .= GetMessage("SOE_ERROR_UPDATE").". ";
			}
		}
	}

	if (StrLen($errorMessage) <= 0)
	{
		for ($i = 0; $i < count($arIDs); $i++)
		{
			if (IntVal($arIDs[$i]) > 0 && strlen($STATUS_ID) > 0 && $arOrder["STATUS_ID"] != $STATUS_ID)
			{
				if (!CSaleOrder::StatusOrder($arIDs[$i], $STATUS_ID))
				{
					if ($ex = $APPLICATION->GetException())
						$errorMessage .= $ex->GetString();
					else
						$errorMessage .= GetMessage("SOE_ERROR_STATUS_EDIT").". ";
				}
			}
		}
	}

	if (StrLen($errorMessage) <= 0)
	{
		for ($i = 0; $i < count($arIDs); $i++)
		{
			if (IntVal($arIDs[$i]) > 0 && $bUserCanCancelOrder && $arOrder["CANCELED"] != $CANCELED)
			{
				if (!CSaleOrder::CancelOrder($arIDs[$i], $CANCELED, $REASON_CANCELED))
				{
					if ($ex = $APPLICATION->GetException())
						$errorMessage .= $ex->GetString();
					else
						$errorMessage .= GetMessage("SOE_ERROR_CANCEL_EDIT").". ";
				}
			}
		}
	}

	if (StrLen($errorMessage) <= 0)
	{
		for ($i = 0; $i < count($arIDs); $i++)
		{
			if (IntVal($arIDs[$i]) > 0 && $bUserCanDeliverOrder && $arOrder["ALLOW_DELIVERY"] != $ALLOW_DELIVERY)
			{
				if (!CSaleOrder::DeliverOrder($arIDs[$i], $ALLOW_DELIVERY))
				{
					if ($ex = $APPLICATION->GetException())
						$errorMessage .= $ex->GetString();
					else
						$errorMessage .= GetMessage("SOE_ERROR_DELIVERY_EDIT").". ";
				}
			}
		}
	}

	if (StrLen($errorMessage) <= 0)
	{
		for ($i = 0; $i < count($arIDs); $i++)
		{
			if (IntVal($arIDs[$i]) > 0 && $bUserCanPayOrder && $arOrder["PAYED"] != $PAYED)
			{
				$arAdditionalFields = array(
						"PAY_VOUCHER_NUM" => ((strlen($PAY_VOUCHER_NUM) > 0) ? $PAY_VOUCHER_NUM : False),
						"PAY_VOUCHER_DATE" => ((strlen($PAY_VOUCHER_DATE) > 0) ? $PAY_VOUCHER_DATE : False)
					);
				if (!CSaleOrder::PayOrder($arIDs[$i], $PAYED, false, false, 0, $arAdditionalFields))
				{
					if ($ex = $APPLICATION->GetException())
						$errorMessage .= $ex->GetString();
					else
						$errorMessage .= GetMessage("SOE_ERROR_PAY_EDIT").". ";
				}
			}
		}
	}

	if (strlen($errorMessage) <= 0)
	{
		$arOldBasketList = array();

		$dbBasket = CSaleBasket::GetList(
				array("NAME" => "ASC"),
				array("ORDER_ID" => $ID),
				false,
				false,
				array("ID", "NAME")
			);
		while ($arBasket = $dbBasket->Fetch())
			$arOldBasketList[IntVal($arBasket["ID"])] = "Y";

		for ($i = 0; $i < count($arIDs); $i++)
		{
			for ($j = 0; $j < count($arBasketList[$i]); $j++)
			{
				$arFields = array(
						"ORDER_ID" => $arIDs[$i],
						"PRODUCT_ID" => $arBasketList[$i][$j]["PRODUCT_ID"],
						"PRODUCT_PRICE_ID" => $arBasketList[$i][$j]["PRODUCT_PRICE_ID"],
						"PRICE" => $arBasketList[$i][$j]["PRICE"],
						"CURRENCY" => $arBasketList[$i][$j]["CURRENCY"],
						"WEIGHT" => $arBasketList[$i][$j]["WEIGHT"],
						"QUANTITY" => $arBasketList[$i][$j]["QUANTITY"],
						"LID" => $LID,
						"NAME" => $arBasketList[$i][$j]["NAME"],
						"MODULE" => $arBasketList[$i][$j]["MODULE"],
						"NOTES" => $arBasketList[$i][$j]["NOTES"],
						"DETAIL_PAGE_URL" => $arBasketList[$i][$j]["DETAIL_PAGE_URL"],
						"DISCOUNT_PRICE" => ($arBasketList[$i][$j]["PRICE"] - $arBasketList[$i][$j]["REAL_PRICE"]),
						"PROPS" => $arBasketList[$i][$j]["PROPS"],
						"CALLBACK_FUNC" => $arBasketList[$i][$j]["CALLBACK_FUNC"],
						"ORDER_CALLBACK_FUNC" => $arBasketList[$i][$j]["ORDER_CALLBACK_FUNC"],
						"CANCEL_CALLBACK_FUNC" => $arBasketList[$i][$j]["CANCEL_CALLBACK_FUNC"],
						"PAY_CALLBACK_FUNC" => $arBasketList[$i][$j]["PAY_CALLBACK_FUNC"],
						"CATALOG_XML_ID" => $arBasketList[$i][$j]["CATALOG_XML_ID"],
						"PRODUCT_XML_ID" => $arBasketList[$i][$j]["PRODUCT_XML_ID"],
						"VAT_RATE" => $arBasketList[$i][$j]["VAT_RATE"],
						"IGNORE_CALLBACK_FUNC" => "Y"
					);
				$res = False;
				if ($arBasketList[$i][$j]["ID"] > 0)
				{
					if (array_key_exists($arBasketList[$i][$j]["ID"], $arOldBasketList))
					{
						$res = CSaleBasket::Update($arBasketList[$i][$j]["ID"], $arFields);
						unset($arOldBasketList[$arBasketList[$i][$j]["ID"]]);
					}
					else
					{
						$errorMessage .= GetMessage("SOE_INTERNAL_RFITH67").". ";
					}
				}
				else
					$res = (CSaleBasket::Add($arFields) > 0);

				if (!$res)
				{
					if ($ex = $APPLICATION->GetException())
						$errorMessage .= $ex->GetString();
					else
						$errorMessage .= str_replace("#ID#", $arBasketList[$i][$j]["PRODUCT_ID"], GetMessage("SOE_ERROR_SAVE_ITEM")).". ";
				}
			}
		}

		foreach ($arOldBasketList as $key => $value)
			CSaleBasket::Delete($key);
	}

	if (strlen($errorMessage) <= 0)
	{
		$arOldTaxList = array();
		$dbTax = CSaleOrderTax::GetList(
				array("APPLY_ORDER" => "ASC"),
				array("ORDER_ID" => $ID),
				false,
				false,
				array("*")
			);
		while ($arTax = $dbTax->Fetch())
			$arOldTaxList[IntVal($arTax["ID"])] = "Y";

		for ($i = 0; $i < count($arIDs); $i++)
		{
			for ($j = 0; $j < count($arTaxList); $j++)
			{
				$arFields = array(
						"ORDER_ID" => $arIDs[$i],
						"TAX_NAME" => $arTaxList[$j]["TAX_NAME"],
						"VALUE" => $arTaxList[$j]["VALUE"],
						"VALUE_MONEY" => $arOrderTaxList[$i][$j]["VALUE_MONEY"],
						"APPLY_ORDER" => $arTaxList[$j]["APPLY_ORDER"],
						"IS_PERCENT" => "Y",
						"IS_IN_PRICE" => $arTaxList[$j]["IS_IN_PRICE"],
						"CODE" => $arTaxList[$j]["CODE"]
					);

				$res = False;
				if ($arTaxList[$j]["ID"] > 0)
				{
					if (array_key_exists($arTaxList[$j]["ID"], $arOldTaxList))
					{
						$res = CSaleOrderTax::Update($arTaxList[$j]["ID"], $arFields);
						unset($arOldTaxList[$arTaxList[$j]["ID"]]);
					}
					else
					{
						$errorMessage .= GetMessage("SOE_INTERNAL_RFITH68").". ";
					}
				}
				else
					$res = (CSaleOrderTax::Add($arFields) > 0);

				if (!$res)
				{
					if ($ex = $APPLICATION->GetException())
						$errorMessage .= $ex->GetString();
					else
						$errorMessage .= str_replace("#NAME#", $arTaxList[$j]["TAX_NAME"], GetMessage("SOE_ERROR_SAVE_TAX")).". ";
				}
			}
		}

		foreach ($arOldTaxList as $key => $value)
			CSaleOrderTax::Delete($key);
	}

	if (strlen($errorMessage) <= 0)
	{
		for ($i = 0; $i < count($arIDs); $i++)
		{
			CSaleOrderPropsValue::DeleteByOrder($arIDs[$i]);

			for ($j = 0; $j < count($arPropsList); $j++)
			{
				$arFields = array(
						"ORDER_ID" => $arIDs[$i],
						"ORDER_PROPS_ID" => $arPropsList[$j]["ORDER_PROPS_ID"],
						"NAME" => $arPropsList[$j]["NAME"],
						"CODE" => $arPropsList[$j]["CODE"],
						"VALUE" => $arPropsList[$j]["VALUE"]
					);

				$res = (CSaleOrderPropsValue::Add($arFields) > 0);

				if (!$res)
				{
					if ($ex = $APPLICATION->GetException())
						$errorMessage .= $ex->GetString();
					else
						$errorMessage .= str_replace("#NAME#", $arPropsList[$j]["NAME"], GetMessage("SOE_ERROR_SAVE_PROP")).". ";
				}
			}
		}
	}

	if (strlen($errorMessage) <= 0)
	{
		if (!$customTabber->Action())
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString();
			else
				$errorMessage .= "Error. ";
		}
	}

	if (strlen($errorMessage) <= 0)
	{
		$DB->Commit();
		CSaleOrder::UnLock($ID);

		if (strlen($apply) <= 0)
			LocalRedirect("/bitrix/admin/sale_order.php?lang=".LANG."&".GetFilterParams("filter_", false));
	}
	else
	{
		if ($bTrabsactionStarted)
			$DB->Rollback();

		$bVarsFromForm = True;
	}

	// *****************************************************************
	// *****  End  *****************************************************
	// *****************************************************************
}

if (!empty($dontsave))
{
	CSaleOrder::UnLock($ID);
	LocalRedirect("/bitrix/admin/sale_order.php?lang=".LANG."&".GetFilterParams("filter_", false));
}


$dbOrder = CSaleOrder::GetList(
	array("ID" => "DESC"),
	array("ID" => $ID),
	false,
	false,
	array("ID", "LID", "PERSON_TYPE_ID", "PAYED", "DATE_PAYED", "EMP_PAYED_ID", "CANCELED", "DATE_CANCELED", "EMP_CANCELED_ID", "REASON_CANCELED", "STATUS_ID", "DATE_STATUS", "PAY_VOUCHER_NUM", "PAY_VOUCHER_DATE", "EMP_STATUS_ID", "PRICE_DELIVERY", "ALLOW_DELIVERY", "DATE_ALLOW_DELIVERY", "EMP_ALLOW_DELIVERY_ID", "PRICE", "CURRENCY", "DISCOUNT_VALUE", "SUM_PAID", "USER_ID", "PAY_SYSTEM_ID", "DELIVERY_ID", "DATE_INSERT", "DATE_INSERT_FORMAT", "DATE_UPDATE", "USER_DESCRIPTION", "ADDITIONAL_INFO", "PS_STATUS", "PS_STATUS_CODE", "PS_STATUS_DESCRIPTION", "PS_STATUS_MESSAGE", "PS_SUM", "PS_CURRENCY", "PS_RESPONSE_DATE", "COMMENTS", "TAX_VALUE", "STAT_GID", "RECURRING_ID", "RECOUNT_FLAG", "LOCK_STATUS", "USER_LOGIN", "USER_NAME", "USER_LAST_NAME", "USER_EMAIL")
);

if (!($arOrderOldTmp = $dbOrder->ExtractFields("str_")))
	LocalRedirect("sale_order.php?lang=".LANG."&".GetFilterParams("filter_", false));

if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_sale_order", "", "str_");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("SOE_TITLE")));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/

if (!$bUserCanViewOrder)
{
	CAdminMessage::ShowMessage(str_replace("#ID#", $ID, GetMessage("SOE_NO_VIEW_PERMS")).". ");
}
else
{
	if (!CSaleOrder::IsLocked($ID, $lockedBY, $dateLock))
		CSaleOrder::Lock($ID);

	$aMenu = array(
			array(
				"TEXT" => GetMessage("SOE_TO_LIST"),
				"LINK" => "/bitrix/admin/sale_order_edit.php?ID=".$ID."&dontsave=Y&lang=".LANGUAGE_ID."&".GetFilterParams("filter_", false)
			)
		);
	$aMenu[] = array("SEPARATOR" => "Y");

	if ($bUserCanViewOrder)
	{
		$aMenu[] = array(
				"TEXT" => GetMessage("SOE_TO_DETAIL"),
				"LINK" => "/bitrix/admin/sale_order_detail.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_", false)
			);
	}
	$aMenu[] = array(
			"TEXT" => GetMessage("SOE_TO_PRINT"),
			"LINK" => "/bitrix/admin/sale_order_print.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_", false)
		);

	if ($saleModulePermissions == "W" || $str_PAYED != "Y" && $bUserCanDeleteOrder)
	{
		$aMenu[] = array(
				"TEXT" => GetMessage("SOEN_CONFIRM_DEL"),
				"LINK" => "javascript:if(confirm('".GetMessage("SOEN_CONFIRM_DEL_MESSAGE")."')) window.location='sale_order.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."&".GetFilterParams("filter_")."'",
				"WARNING" => "Y"
			);
	}

	$context = new CAdminContextMenu($aMenu);
	$context->Show();
	?>

	<?
	CAdminMessage::ShowMessage($errorMessage);

	$customOrderEdit = COption::GetOptionString("sale", "path2custom_edit_order", "");
	if (strlen($customOrderEdit) > 0
		&& file_exists($_SERVER["DOCUMENT_ROOT"].$customOrderEdit)
		&& is_file($_SERVER["DOCUMENT_ROOT"].$customOrderEdit))
	{
		include($_SERVER["DOCUMENT_ROOT"].$customOrderEdit);
	}
	else
	{
		?>
		<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="forder_edit" OnSubmit="OrderFormSubmit()">
		<?= GetFilterHiddens("filter_");?>
		<?= bitrix_sessid_post(); ?>
		<input type="hidden" name="action" value="update">
		<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
		<input type="hidden" name="ID" value="<?= $ID ?>">

		<?
		$aTabs = array(
			array("DIV" => "edit1", "TAB" => GetMessage("SOEN_TAB_ORDER"), "ICON" => "sale", "TITLE" => GetMessage("SOEN_TAB_ORDER_DESCR")),
			array("DIV" => "edit2", "TAB" => GetMessage("SOE_TAB_PROPS"), "ICON" => "sale", "TITLE" => GetMessage("SOE_TAB_PROPS_DESCR")),
			array("DIV" => "edit3", "TAB" => GetMessage("SOE_TAB_PAY_DEL"), "ICON" => "sale", "TITLE" => GetMessage("SOE_TAB_PAY_DEL_DESCR")),
			array("DIV" => "edit4", "TAB" => GetMessage("SOEN_TAB_BASKET"), "ICON" => "sale", "TITLE" => GetMessage("SOEN_TAB_BASKET_DESCR")),
			array("DIV" => "edit5", "TAB" => GetMessage("SOE_TAB_COMMENT"), "ICON" => "sale", "TITLE" => GetMessage("SOE_TAB_COMMENT_DESCR")),
		);

		$tabControl = new CAdminTabControl("tabControl", $aTabs);
		$customTabber->SetErrorState($bVarsFromForm);
		$tabControl->AddTabs($customTabber);
		$tabControl->Begin();
		?>

		<?
		$tabControl->BeginNextTab();
		?>

			<tr class="heading">
				<td colspan="2">
					<?= str_replace("#ID#", $ID, str_replace("#DATE#", $str_DATE_INSERT, GetMessage("SOE_ORDER_TBL_HEAD"))) ?>
				</td>
			</tr>
			<tr>
				<td width="40%"><?= GetMessage("SOE_DATE_UPDATE") ?>:</td>
				<td width="60%"><?echo $str_DATE_UPDATE ?></td>
			</tr>
			<tr>
				<td width="40%">
					<span class="required">*</span><?= GetMessage("SOE_SITE") ?>:
				</td>
				<td width="60%">
					<input type="hidden" name="PARTIAL_SUBMIT" value="N">
					<select name="LID" OnChange="document.forder_edit.PARTIAL_SUBMIT.value='Y'; document.forder_edit.submit();">
						<?
						$dbPersTypeList = CSalePersonType::GetList(
							array(),
							array(),
							Array("LID"),
							false,
							array("ID", "LID")
							);
						while($arTypeList = $dbPersTypeList->Fetch())
						{
							$arTypeListLang[$arTypeList["LID"]] = $arTypeList["CNT"];
						}

						$dbSitesList = CLang::GetList(($b1="sort"), ($o1="asc"));
						while ($arSitesList = $dbSitesList->Fetch())
						{
							if(IntVal($arTypeListLang[$arSitesList["LID"]]) > 0)
							{
								?><option value="<?= $arSitesList["LID"] ?>"<?if ($arSitesList["LID"] == $str_LID) echo " selected";?>>[<?= htmlspecialcharsex($arSitesList["LID"]) ?>]&nbsp;<?= htmlspecialcharsex($arSitesList["NAME"]) ?></option><?
								}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td width="40%">
					<span class="required">*</span><?= GetMessage("SOE_STATUS") ?>:
				</td>
				<td width="60%">
					<?
					$arSL = false;
					$arFilter = array("LID" => LANGUAGE_ID);
					$arGroupByTmp = false;
					if ($saleModulePermissions < "W")
					{
						$arFilter["GROUP_ID"] = $GLOBALS["USER"]->GetUserGroupArray();
						$arFilter["PERM_STATUS_FROM"] = "Y";
						$arFilter["ID"] = $str_STATUS_ID;
						$arGroupByTmp = array("ID", "NAME", "MAX" => "PERM_STATUS_FROM");
					}
					$dbStatusList = CSaleStatus::GetList(
							array(),
							$arFilter,
							$arGroupByTmp,
							false,
							array("ID", "NAME", "SORT")
						);
					$arSL = $dbStatusList->GetNext();
						
					if ($arSL)
					{
						?>
						<select name="STATUS_ID">
							<?
							$arFilter = array("LID" => LANG);
							$arGroupByTmp = false;
							if ($saleModulePermissions < "W")
							{
								$arFilter["GROUP_ID"] = $GLOBALS["USER"]->GetUserGroupArray();
								$arFilter["PERM_STATUS"] = "Y";
								//$arGroupByTmp = array("ID", "NAME", "MAX" => "PERM_STATUS");
							}
							$dbStatusListTmp = CSaleStatus::GetList(
									array("SORT" => "ASC"),
									$arFilter,
									$arGroupByTmp,
									false,
									array("ID", "NAME")
								);
							while($arStatusListTmp = $dbStatusListTmp->GetNext())
							{
								?><option value="<?echo $arStatusListTmp["ID"] ?>"<?if ($arStatusListTmp["ID"]==$str_STATUS_ID) echo " selected"?>>[<?echo $arStatusListTmp["ID"] ?>] <?echo $arStatusListTmp["NAME"] ?></option><?
							}
							
							/*
							if (!array_key_exists($str_STATUS_ID, $arStatusList))
							{
								?><option value="" selected><?= GetMessage("SOE_STATUS_NO_CHANGE") ?></option><?
							}
							foreach ($arStatusList as $key => $value)
							{
								?><option value="<?= $key ?>"<?if ($key == $str_STATUS_ID) echo " selected"?>>[<?= $key ?>] <?= $value["NAME"] ?></option><?
							}
							*/
							?>
						</select>
						<?
					}
					else
					{
						$arStatusLand = CSaleStatus::GetLangByID($str_STATUS_ID, LANGUAGE_ID);
						echo htmlspecialcharsEx("[".$str_STATUS_ID."] ".$arStatusLand["NAME"]);
					}
					?>
				</td>
			</tr>
			<tr>
				<td width="40%">
					<?= GetMessage("SOE_SUM") ?>:
				</td>
				<td width="60%">
					<script language="JavaScript">
					<!--
					function OnCurrencyChange()
					{
						var oCur = document.forder_edit.CURRENCY;
						if (!oCur)
							return;

						var cur = oCur[oCur.selectedIndex].value;

						//var el = document.getElementById("PRICE_DELIVERY_CURRENCY");
						//if (el)
						//	el.innerHTML = cur;

						el = document.getElementById("DISCOUNT_VALUE_CURRENCY");
						if (el)
							el.innerHTML = cur;

						el = document.getElementById("SUM_PAID_CURRENCY");
						if (el)
							el.innerHTML = cur;
					}
					//-->
					</script>
					<?= SaleFormatCurrency($str_PRICE, $str_CURRENCY) ?>
					<!--
					<input type="text" name="PRICE" size="10" maxlength="20" disabled value="<?= roundEx($str_PRICE, SALE_VALUE_PRECISION) ?>">
					<?= CCurrency::SelectBox("CURRENCY", $str_CURRENCY, "", false, "OnCurrencyChange()", "")?>
					//-->
				</td>
			</tr>
			<tr>
				<td width="40%">
					<?= GetMessage("SOE_ALREADY_PAID") ?>:
				</td>
				<td width="60%">
					<input type="text" name="SUM_PAID" size="10" maxlength="20" value="<?= roundEx($str_SUM_PAID, SALE_VALUE_PRECISION) ?>">
					<span id="SUM_PAID_CURRENCY" class="tablebodytext"><?= htmlspecialchars($str_CURRENCY) ?></span>
				</td>
			</tr>
			<tr>
				<td width="40%">
					<?= GetMessage("SOE_DISCOUNT") ?>:
				</td>
				<td width="60%">
					<?= SaleFormatCurrency($str_DISCOUNT_VALUE, $str_CURRENCY) ?>
					<!--
					<input type="text" name="DISCOUNT_VALUE" size="10" disabled maxlength="20" value="<?= roundEx($str_DISCOUNT_VALUE, SALE_VALUE_PRECISION) ?>">
					<span id="DISCOUNT_VALUE_CURRENCY" class="tablebodytext"><?= htmlspecialchars($str_CURRENCY) ?></span>
					//-->
				</td>
			</tr>
			<tr>
				<td width="40%">
					<?= GetMessage("SOE_CANCELED") ?>:
				</td>
				<td width="60%">
					<input type="checkbox"<?if (!$bUserCanCancelOrder) echo " disabled";?> name="CANCELED" value="Y"<?if ($str_CANCELED == "Y") echo " checked";?>>
				</td>
			</tr>
			<tr>
				<td width="40%" valign="top">
					<?= GetMessage("SOE_CANCEL_REASON") ?>:
				</td>
				<td width="60%" valign="top">
					<textarea name="REASON_CANCELED"<?if (!$bUserCanCancelOrder) echo " disabled";?> rows="2" cols="40"><?= $str_REASON_CANCELED ?></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<img src="/bitrix/images/1.gif" width="1" height="8">
				</td>
			</tr>

			<tr class="heading">
				<td colspan="2"><?= GetMessage("SOE_BUYER") ?></td>
			</tr>
			<tr>
				<td width="40%">
					<span class="required">*</span><?= GetMessage("SOE_USER") ?>:
				</td>
				<td width="60%"><?
					if ($ID > 0)
						$user_name = "[<a class=\"tablebodylink\" title=\"".GetMessage("SOE_PROFILE_ALT")."\" href=\"/bitrix/admin/user_edit.php?lang=".LANGUAGE_ID."&ID=".$str_USER_ID."\">".$str_USER_ID."</a>] (".$str_USER_LOGIN.") ".$str_USER_NAME." ".$str_USER_LAST_NAME;

					echo FindUserID("USER_ID", $str_USER_ID, $user_name, "forder_edit");
					?></td>
			</tr>

		<?
		$tabControl->BeginNextTab();
		?>

			<tr>
				<td width="40%">
					<span class="required">*</span><?= GetMessage("SOE_PERSON_TYPE") ?>:
				</td>
				<td width="60%">
					<?
					$dbPersTypeList = CSalePersonType::GetList(
						array("SORT" => "ASC"),
						array(),
						false,
						false,
						array("*")
					);
					?>
					<select name="PERSON_TYPE_ID" OnChange="document.forder_edit.PARTIAL_SUBMIT.value='Y'; document.forder_edit.submit();">
						<?
						while ($arPersTypeList = $dbPersTypeList->GetNext())
						{
							if ($arPersTypeList["LID"] != $str_LID && IntVal($arPersTypeList["ID"]) != IntVal($str_PERSON_TYPE_ID))
								continue;
							?><option value="<?echo $arPersTypeList["ID"] ?>"<?if (IntVal($arPersTypeList["ID"])==IntVal($str_PERSON_TYPE_ID)) echo " selected"?>>[<?echo $arPersTypeList["ID"] ?>] <?echo $arPersTypeList["NAME"] ?></option><?
						}
						?>
					</select>
				</td>
			</tr>

			<?
			$arPropValues = array();
			if (!$bVarsFromForm || $PARTIAL_SUBMIT == "Y")
			{
				$dbPropValuesList = CSaleOrderPropsValue::GetList(
						array(),
						array("ORDER_ID" => $ID),
						false,
						false,
						array("ID", "ORDER_PROPS_ID", "NAME", "VALUE", "CODE")
					);
				while ($arPropValuesList = $dbPropValuesList->Fetch())
				{
					$arPropValues[IntVal($arPropValuesList["ORDER_PROPS_ID"])] = $arPropValuesList["VALUE"];
				}
			}
			else
			{
				foreach ($_REQUEST as $key => $value)
				{
					if (substr($key, 0, strlen("ORDER_PROP_")) == "ORDER_PROP_")
						$arPropValues[IntVal(substr($key, strlen("ORDER_PROP_")))] = htmlspecialcharsEx($value);
				}
			}

			$propertyGroupID = -1;

			$dbProperties = CSaleOrderProps::GetList(
					array("GROUP_SORT" => "ASC", "PROPS_GROUP_ID" => "ASC", "SORT" => "ASC", "NAME" => "ASC"),
					array("PERSON_TYPE_ID" => $str_PERSON_TYPE_ID),
					false,
					false,
					array("*")
				);

			while ($arProperties = $dbProperties->Fetch())
			{
				if (IntVal($arProperties["PROPS_GROUP_ID"]) != $propertyGroupID)
				{
					?>
					<tr class="heading">
						<td colspan="2">
							<?= htmlspecialcharsEx($arProperties["GROUP_NAME"]) ?>
						</td>
					</tr>
					<?
					$propertyGroupID = IntVal($arProperties["PROPS_GROUP_ID"]);
				}
				?>
				<tr>
					<td align="right" valign="top">
						<?
						if ($arProperties["REQUIED"]=="Y" || /*$arProperties["IS_EMAIL"]=="Y" || */$arProperties["IS_PROFILE_NAME"]=="Y" || $arProperties["IS_LOCATION"]=="Y" || $arProperties["IS_LOCATION4TAX"]=="Y" || $arProperties["IS_PAYER"]=="Y")
						{
							?><span class="required">*</span><?
						}
						?><?echo htmlspecialcharsEx($arProperties["NAME"]) ?>:
					</td>
					<td align="left">
						
						<?
						$curVal = $arPropValues[IntVal($arProperties["ID"])];
						?>
						<?
						if ($arProperties["TYPE"] == "CHECKBOX")
						{
							echo '<input type="checkbox" class="inputcheckbox" ';
							echo 'name="ORDER_PROP_'.$arProperties["ID"].'" value="Y"';
							if ($curVal=="Y" || !isset($curVal) && $arProperties["DEFAULT_VALUE"]=="Y")
								echo " checked";
							echo '>';
						}
						elseif ($arProperties["TYPE"] == "TEXT")
						{
							echo '<input type="text" maxlength="250" ';
							echo 'size="'.((IntVal($arProperties["SIZE1"]) > 0) ? $arProperties["SIZE1"] : 30).'" ';
							echo 'value="'.((isset($curVal)) ? htmlspecialcharsEx($curVal) : htmlspecialcharsex($arProperties["DEFAULT_VALUE"])).'" ';
							echo 'name="ORDER_PROP_'.$arProperties["ID"].'">';
						}
						elseif ($arProperties["TYPE"] == "SELECT")
						{
							echo '<select name="ORDER_PROP_'.$arProperties["ID"].'" ';
							echo 'size="'.((IntVal($props["SIZE1"]) > 0) ? $arProperties["SIZE1"] : 1).'" ';
							echo 'class="typeselect">';
							$dbVariants = CSaleOrderPropsVariant::GetList(
									array("SORT" => "ASC"),
									array("ORDER_PROPS_ID" => $arProperties["ID"]),
									false,
									false,
									array("*")
								);
							while ($arVariants = $dbVariants->Fetch())
							{
								echo '<option value="'.htmlspecialcharsex($arVariants["VALUE"]).'"';
								if ($arVariants["VALUE"] == $curVal || !isset($curVal) && $arVariants["VALUE"] == $arProperties["DEFAULT_VALUE"])
									echo " selected";
								echo '>'.htmlspecialcharsEx($arVariants["NAME"]).'</option>';
							}
							echo '</select>';
						}
						elseif ($arProperties["TYPE"] == "MULTISELECT")
						{
							echo '<select multiple name="ORDER_PROP_'.$arProperties["ID"].'[]" ';
							echo 'size="'.((IntVal($props["SIZE1"]) > 0) ? $props["SIZE1"] : 5).'" ';
							echo 'class="typeselect">';
							$arCurVal = array();
							for ($i = 0; $i < count($curVal); $i++)
								$arCurVal[$i] = Trim($curVal[$i]);
							$arDefVal = Split(",", $arProperties["DEFAULT_VALUE"]);
							for ($i = 0; $i < count($arDefVal); $i++)
								$arDefVal[$i] = Trim($arDefVal[$i]);

							$dbVariants = CSaleOrderPropsVariant::GetList(
									array("SORT" => "ASC"),
									array("ORDER_PROPS_ID" => $arProperties["ID"]),
									false,
									false,
									array("*")
								);
							while ($arVariants = $dbVariants->Fetch())
							{
								echo '<option value="'.htmlspecialcharsex($arVariants["VALUE"]).'"';
								if (in_array($arVariants["VALUE"], $arCurVal) || !isset($curVal) && in_array($arVariants["VALUE"], $arDefVal))
									echo " selected";
								echo '>'.htmlspecialcharsEx($arVariants["NAME"]).'</option>';
							}
							echo '</select>';
						}
						elseif ($arProperties["TYPE"] == "TEXTAREA")
						{
							echo '<textarea ';
							echo 'rows="'.((IntVal($arProperties["SIZE2"]) > 0) ? $arProperties["SIZE2"] : 4).'" ';
							echo 'cols="'.((IntVal($arProperties["SIZE1"]) > 0) ? $arProperties["SIZE1"] : 40).'" ';
							echo 'name="ORDER_PROP_'.$arProperties["ID"].'">';
							echo ((isset($curVal)) ? htmlspecialcharsex($curVal) : htmlspecialcharsex($arProperties["DEFAULT_VALUE"]));
							echo '</textarea>';
						}
						elseif ($arProperties["TYPE"] == "LOCATION")
						{
							echo '<select name="ORDER_PROP_'.$arProperties["ID"].'" ';
							echo 'size="'.((IntVal($arProperties["SIZE1"]) > 0) ? $arProperties["SIZE1"] : 1).'" ';
							echo 'class="typeselect">';
							$dbVariants = CSaleLocation::GetList(
									array("SORT" => "ASC", "COUNTRY_NAME_LANG" => "ASC", "CITY_NAME_LANG" => "ASC"),
									array("LID" => LANG),
									false,
									false,
									array("ID", "COUNTRY_NAME", "CITY_NAME", "SORT")
								);
							while ($arVariants = $dbVariants->Fetch())
							{
								echo '<option value="'.htmlspecialcharsex($arVariants["ID"]).'"';
								if (IntVal($arVariants["ID"]) == IntVal($curVal) || !isset($curVal) && IntVal($arVariants["ID"]) == IntVal($arProperties["DEFAULT_VALUE"]))
									echo " selected";
								echo '>'.htmlspecialcharsex($arVariants["COUNTRY_NAME"].((strlen($arVariants["CITY_NAME"]) > 0) ? " - " : "").$arVariants["CITY_NAME"]).'</option>';
							}
							
							$DELIVERY_LOCATION = $curVal;
							
							echo '</select>';
						}
						elseif ($arProperties["TYPE"] == "RADIO")
						{
							$dbVariants = CSaleOrderPropsVariant::GetList(
									array("SORT" => "ASC"),
									array("ORDER_PROPS_ID" => $arProperties["ID"]),
									false,
									false,
									array("*")
								);
							while ($arVariants = $dbVariants->Fetch())
							{
								echo '<input type="radio" class="inputradio" ';
								echo 'name="ORDER_PROP_'.$arProperties["ID"].'" ';
								echo 'value="'.htmlspecialcharsex($arVariants["VALUE"]).'"';
								if ($arVariants["VALUE"] == $curVal || !isset($curVal) && $arVariants["VALUE"] == $arProperties["DEFAULT_VALUE"])
									echo " checked";
								echo '>'.htmlspecialcharsEx($arVariants["NAME"]).'<br>';
							}
						}

						if (strlen($arProperties["DESCRIPTION"]) > 0)
						{
							?><br><small><?echo htmlspecialcharsEx($arProperties["DESCRIPTION"]) ?></small><?
						}
						?>
						
					</td>
				</tr>
				<?
			}
			?>

			<tr>
				<td width="40%" valign="top">
					<?= GetMessage("SOE_BUYER_COMMENT") ?>:
				</td>
				<td width="60%" valign="top">
					<textarea name="USER_DESCRIPTION" rows="2" cols="40"><?= $str_USER_DESCRIPTION ?></textarea>
				</td>
			</tr>

		<?
		$tabControl->BeginNextTab();
		?>

			<tr class="heading">
				<td colspan="2"><?= GetMessage("SOE_PAYMENT") ?></td>
			</tr>
			<tr>
				<td width="40%">
					<?= GetMessage("SOE_PAY_SYSTEM") ?>:
				</td>
				<td width="60%">
					<?
					$dbPaySystem = CSalePaySystem::GetList(
							array("SORT" => "ASC", "NAME" => "ASC"),
							array(
									//"LID" => $str_LID, 
									//"PSA_PERSON_TYPE_ID" => $str_PERSON_TYPE_ID, 
									"ACTIVE" => "Y"
									),
							false,
							false,
							array("ID", "LID", "NAME", "SORT", "ACTIVE", "PSA_PERSON_TYPE_ID")
						);
						?>
					<select name="PAY_SYSTEM_ID">
						<option value="">(<?= GetMessage("SOE_NO") ?>)</option>
						<?
						while ($arPaySystem = $dbPaySystem->GetNext())
						{

							if (($arPaySystem["PSA_PERSON_TYPE_ID"] == $str_PERSON_TYPE_ID && $arPaySystem["LID"] == $str_LID)
									|| (IntVal($arPaySystem["ID"]) == IntVal($str_PAY_SYSTEM_ID) && $arPaySystem["PSA_PERSON_TYPE_ID"] == $str_PERSON_TYPE_ID && $arPaySystem["LID"] == $arOrderOldTmp["LID"])
									|| (IntVal($arPaySystem["ID"]) == IntVal($str_PAY_SYSTEM_ID) && $arPaySystem["PSA_PERSON_TYPE_ID"] == $arOrderOldTmp["PERSON_TYPE_ID"] && $arPaySystem["LID"] == $str_LID)
									|| (IntVal($arPaySystem["ID"]) == IntVal($str_PAY_SYSTEM_ID) && $arPaySystem["LID"] == $arOrderOldTmp["LID"] && $arPaySystem["PSA_PERSON_TYPE_ID"] == $arOrderOldTmp["PERSON_TYPE_ID"])
									
								)
							{

							
								?><option value="<?echo $arPaySystem["ID"] ?>"<?if (IntVal($arPaySystem["ID"])==IntVal($str_PAY_SYSTEM_ID)) echo " selected"?>>[<?echo $arPaySystem["ID"] ?>] <?echo $arPaySystem["NAME"] ?></option><?
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td width="40%">
					<script language="JavaScript">
					<!--
					function PayedClicked()
					{
						document.forder_edit.PAY_VOUCHER_NUM.disabled = !document.forder_edit.PAYED.checked;
						document.forder_edit.PAY_VOUCHER_DATE.disabled = !document.forder_edit.PAYED.checked;
					}
					//-->
					</script>
					<?= GetMessage("SOE_ORDER_PAID") ?>:
				</td>
				<td width="60%">
					<input type="checkbox"<?if (!$bUserCanPayOrder) echo " disabled";?> name="PAYED" OnClick="PayedClicked()" value="Y"<?if ($str_PAYED == "Y") echo " checked";?>>
				</td>
			</tr>
			<tr>
				<td width="40%">
					<?= GetMessage("SOE_VOUCHER_NUM") ?>:
				</td>
				<td width="60%">
					<input type="text" name="PAY_VOUCHER_NUM"<?if (!$bUserCanPayOrder) echo " disabled";?> value="<?= $str_PAY_VOUCHER_NUM ?>" size="20" maxlength="20">
				</td>
			</tr>
			<tr>
				<td width="40%">
					<?= GetMessage("SOE_VOUCHER_DATE") ?> (<?= CSite::GetDateFormat("SHORT", LANG); ?>):
				</td>
				<td width="60%">
					<?= CalendarDate("PAY_VOUCHER_DATE", $str_PAY_VOUCHER_DATE, "change_pay_form", "20", "class=\"typeinput\"".((!$bUserCanPayOrder) ? " disabled" : "")); ?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<img src="/bitrix/images/1.gif" width="1" height="8">
				</td>
			</tr>

			<tr class="heading">
				<td colspan="2"><?= GetMessage("SOE_DELIVERY") ?></td>
			</tr>
			<tr>
				<td width="40%">
					<?= GetMessage("SOE_DELIVERY_COM") ?>:
				</td>
				<td width="60%">
				<?
				if (!empty($str_DELIVERY_ID) && strpos($str_DELIVERY_ID, ":") === false):
				?>

					<script language="JavaScript">
					<!--
					var arDeliveryPrice = new Array();
					var arDeliveryCurrency = new Array();
					<?
					$dbDelivery = CSaleDelivery::GetList(
							array("SORT" => "ASC", "NAME" => "ASC"),
							array("LID" => $str_LID),
							false,
							false,
							array("ID", "NAME", "SORT", "PRICE", "CURRENCY")
						);
					
					$arDeliveries = array();
					while ($arDelivery = $dbDelivery->GetNext())
					{
						$arDeliveries[] = $arDelivery;
						?>arDeliveryPrice[<?= $arDelivery["ID"] ?>]=<?= $arDelivery["PRICE"] ?>;<?
						?>arDeliveryCurrency[<?= $arDelivery["ID"] ?>]='<?= $arDelivery["CURRENCY"] ?>';<?
					}
					
					$bUseDeliveryHandlers = false;
				?>

					function DeliveryChange()
					{
						var idTmp = document.forder_edit.DELIVERY_ID[document.forder_edit.DELIVERY_ID.selectedIndex].value;

						arDeliveryPrice[idTmp] = parseInt(arDeliveryPrice[idTmp]);
						if (true || arDeliveryPrice[idTmp])
							document.forder_edit.PRICE_DELIVERY.value = arDeliveryPrice[idTmp];
						else
							document.forder_edit.PRICE_DELIVERY.value = 'NA';

						if (arDeliveryCurrency[idTmp])
						{
							for (var i = 0; i < document.forder_edit.PRICE_DELIVERY_CURRENCY.options.length; i++)
							{
								if (document.forder_edit.PRICE_DELIVERY_CURRENCY.options[i].value == arDeliveryCurrency[idTmp])
								{
									document.forder_edit.PRICE_DELIVERY_CURRENCY.selectedIndex = i;
									break;
								}
							}
							document.forder_edit.PRICE_DELIVERY_CURRENCY.value = arDeliveryCurrency[idTmp];
						}
						else
							document.forder_edit.PRICE_DELIVERY_CURRENCY.selectedIndex = -1;
					}
					//-->
					</script>
				<?
				else:
					if (!$bVarsFromForm)
					{
						$rsBasket = CSaleBasket::GetList(array(), array("ORDER_ID" => $ID), false, false, array("WEIGHT", "QUANTITY"));
						$ORDER_WEIGHT = 0;
						while ($arItem = $rsBasket->Fetch())
						{
							$ORDER_WEIGHT += $arItem["WEIGHT"] * $arItem["QUANTITY"];
						}
					}
					else
					{
					
					}
				
					$arFilter = array(
						"SITE_ID" => $str_LID,
						/*
						"COMPABILITY" => array(
							"WEIGHT" => $ORDER_WEIGHT,
							"PRICE" => $str_PRICE,
							"LOCATION" => $DELIVERY_LOCATION,
						)
						*/
					);
					
					$dbDeliveries = CSaleDeliveryHandler::GetList(array("SORT" => "ASC"), $arFilter);
					
					$arDeliveries = array();
					while ($arDelivery = $dbDeliveries->GetNext())
					{
						$arDeliveries[$arDelivery["SID"]] = $arDelivery;
					}
				
					$bUseDeliveryHandlers = true;
				endif;
				?>
				
					<select name="DELIVERY_ID" OnChange="recalcDelivery(this.value)">
						<option value="">(<?= GetMessage("SOE_NO") ?>)</option>
						<?
						if ($bUseDeliveryHandlers)
						{
							foreach ($arDeliveries as $SID => $arDelivery)
							{
								foreach ($arDelivery["PROFILES"] as $profile_id => $arProfile)
								{
									$DELIVERY_ID = $SID.":".$profile_id;
							?><option value="<?=$DELIVERY_ID?>"<?if ($DELIVERY_ID == $str_DELIVERY_ID) echo " selected=\"selected\""?>>[<?=$DELIVERY_ID?>] <?=$arDelivery["NAME"]?> (<?=$arProfile["TITLE"]?>)</option><?
								}
							}
						}
						else
						{
							for ($i = 0; $i < count($arDeliveries); $i++)
							{
							?><option value="<?echo $arDeliveries[$i]["ID"] ?>"<?if (IntVal($arDeliveries[$i]["ID"])==IntVal($str_DELIVERY_ID)) echo " selected"?>>[<?echo $arDeliveries[$i]["ID"] ?>] <?echo $arDeliveries[$i]["NAME"] ?></option><?
							}
						}
						?>
					</select> <a href="javascript:void(0)" onclick="recalcDelivery(document.forms.forder_edit.DELIVERY_ID.value)"><?=GetMessage('SOE_AJAX_RECALC')?></a>
				</td>
			</tr>
			<tr>
				<td width="40%">
					<?= GetMessage("SOE_DELIVERY_PRICE") ?>:
				</td>
				<td width="60%">
					<?
					if (!$bUseDeliveryHandlers):
					?>
					<input type="text" name="PRICE_DELIVERY" size="10" maxlength="20" value="<?= roundEx($str_PRICE_DELIVERY, SALE_VALUE_PRECISION) ?>">
					<?= CCurrency::SelectBox("PRICE_DELIVERY_CURRENCY", $str_CURRENCY, "", false, "", "")?>
					<script language="JavaScript">
					<!--
					OnCurrencyChange();
					//-->
					</script>
					<?
					else:
						list($DELIVERY_ID, $DELIVERY_PROFILE) = explode(":", $str_DELIVERY_ID);

						$arParams = array(
							"AJAX_CALL" => "N",
							"DELIVERY" => $DELIVERY_ID,
							"PROFILE" => $DELIVERY_PROFILE,
							"ORDER_WEIGHT" => doubleval($ORDER_WEIGHT),
							"ORDER_PRICE" => doubleval($str_PRICE),
							"LOCATION_TO" => intval($DELIVERY_LOCATION),
							"CURRENCY" => $str_CURRENCY,
							"INPUT_NAME" => "PRICE_DELIVERY",
							"START_VALUE" => $str_PRICE_DELIVERY,
						);

						?>
						<script language="JavaScript">
						function recalcDelivery(value)
						{
							var pos = value.indexOf(':');
							if (pos == -1) return;
							delivery_id = value.substring(0, pos);
							profile_id = value.substring(pos+1);

							arData = {
								STEP:1,
								DELIVERY:delivery_id,
								PROFILE:profile_id,
								WEIGHT:'<?=$arParams["ORDER_WEIGHT"]?>',
								PRICE:'<?=$arParams["ORDER_PRICE"]?>',
								LOCATION:'<?=$arParams["LOCATION_TO"]?>',
								CURRENCY:'<?=CUtil::JSEscape($arParams["CURRENCY"])?>',
								INPUT_NAME:'<?=$arParams["INPUT_NAME"]?>'
							}
							
							deliveryCalcProceed(arData);
						}
						</script>
						<script>
						var ajaxMessages = {wait:'<?=CUtil::JSEscape(GetMessage('SOE_AJAX_WAIT'))?>'};
						</script>
						<?
						$APPLICATION->IncludeComponent('bitrix:sale.ajax.delivery.calculator', 'input', $arParams);
						?>
						<script language="JavaScript">
						/*deliveryCalcProceed({
								STEP:1,
								DELIVERY:'<?=CUtil::JSEscape($arParams["DELIVERY"])?>',
								PROFILE:'<?=CUtil::JSEscape($arParams["PROFILE"])?>',
								WEIGHT:'<?=$arParams["ORDER_WEIGHT"]?>',
								PRICE:'<?=$arParams["ORDER_PRICE"]?>',
								LOCATION:'<?=$arParams["LOCATION_TO"]?>',
								CURRENCY:'<?=CUtil::JSEscape($arParams["CURRENCY"])?>',
								INPUT_NAME:'<?=$arParams["INPUT_NAME"]?>'
							});
						*/
						</script>
						<?
					
					endif;
					?>
				</td>
			</tr>
			<tr>
				<td width="40%">
					<?= GetMessage("SOE_DELIVERY_ALLOWED") ?>:
				</td>
				<td width="60%">
					<input type="checkbox" name="ALLOW_DELIVERY"<?if (!$bUserCanDeliverOrder) echo " disabled";?> value="Y"<?if ($str_ALLOW_DELIVERY == "Y") echo " checked";?>>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<img src="/bitrix/images/1.gif" width="1" height="8">
				</td>
			</tr>

		<?
		$tabControl->EndTab();
		?>

		<?
		$tabControl->BeginNextTab();
		?>

			<tr>
				<td colspan="2" id="ID_BASKET_CONTAINER">

					<script language="JavaScript">
					<!--
					var currentBrowserDetected = "";
					if (window.opera)
						currentBrowserDetected = "Opera";
					else if (navigator.userAgent)
					{
						if (navigator.userAgent.indexOf("MSIE") != -1)
							currentBrowserDetected = "IE";
						else if (navigator.userAgent.indexOf("Firefox") != -1)
							currentBrowserDetected = "Firefox";
					}

					var editedRecord = -1;

					function ModuleChange(ind)
					{
						var m = document.forder_edit["BASKET_MODULE1_"+ind];
						if (!m)
							return;

						if (m.tagName.toUpperCase() == "SELECT")
						{
							if (m[m.selectedIndex].value == "catalog")
								document.getElementById("basket_product_button_"+ind).disabled = false;
							else
								document.getElementById("basket_product_button_"+ind).disabled = true;
						}
						else
						{
							if (m.value == "catalog")
								document.getElementById("basket_product_button_"+ind).disabled = false;
							else
								document.getElementById("basket_product_button_"+ind).disabled = true;
						}
					}

					function BasketAddNewProduct(moduleID, productID, productName, productPath, arProductProps, productPrice, productCurrency, productDiscount, productWeight, productQuantity, productDescr, basketCallbackFunc, basketOrderCallbackFunc, basketCancelCallbackFunc, basketPayCallbackFunc, catalogXmlID, productXmlID, basketID, productPriceID, vatRate)
					{
						if (!moduleID)
							moduleID = "";
						<?if (false && $bSimpleForm):?>
							moduleID = "catalog";
						<?endif;?>
						if (moduleID.length == 0)
							moduleID = "catalog";
						if (!productID)
							productID = "";
						if (!productXmlID)
							productXmlID = "";
						if (!catalogXmlID)
							catalogXmlID = "";
						if (!productID)
							productID = "";
						if (!productName)
							productName = "";
						if (!productPath)
							productPath = "";
						if (!productPrice)
							productPrice = "";
						if (!productCurrency)
							productCurrency = "";
						if (!productDiscount)
							productDiscount = "";
						if (!productWeight)
							productWeight = "";
						if (!productQuantity)
							productQuantity = "";
						if (!productDescr)
							productDescr = "";
						if (!basketCallbackFunc)
							basketCallbackFunc = "";
						if (!basketOrderCallbackFunc)
							basketOrderCallbackFunc = "";
						if (!basketCancelCallbackFunc)
							basketCancelCallbackFunc = "";
						if (!basketPayCallbackFunc)
							basketPayCallbackFunc = "";
						if (!vatRate)
							vatRate = "";
						if (!basketID)
							basketID = 0;
						<?if (false && $bSimpleForm):?>
							basketCallbackFunc = "CatalogBasketCallback";
							basketOrderCallbackFunc = "CatalogBasketOrderCallback";
							basketCancelCallbackFunc = "CatalogBasketCancelCallback";
							basketPayCallbackFunc = "CatalogPayOrderCallback";
						<?endif;?>
						if (basketCallbackFunc.length == 0)
							basketCallbackFunc = "CatalogBasketCallback";
						if (basketOrderCallbackFunc.length == 0)
							basketOrderCallbackFunc = "CatalogBasketOrderCallback";
						if (basketCancelCallbackFunc.length == 0)
							basketCancelCallbackFunc = "CatalogBasketCancelCallback";
						if (basketPayCallbackFunc.length == 0)
							basketPayCallbackFunc = "CatalogPayOrderCallback";
						if (!productPriceID)
							productPriceID = "";

						var oCntr = document.getElementById("BASKET_COUNTER");
						var cnt = parseInt(oCntr.value) + 1;
						oCntr.value = cnt;

						var oTbl = document.getElementById("ID_BASKET_TABLE");
						var oRow = oTbl.insertRow(-1);
						oRow.id = "ID_BASKET_ROW_" + cnt;

						var oCell = oRow.insertCell(-1);
						oCell.vAlign = 'top';
						oCell.align = 'center';
						oCell.style.width = '0%';

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_MODULE_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_MODULE_' + cnt;
						fld.value = moduleID;
						oCell.appendChild(fld);

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_PRODUCT_ID_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_PRODUCT_ID_' + cnt;
						fld.value = productID;
						oCell.appendChild(fld);

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_PRODUCT_PRICE_ID_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_PRODUCT_PRICE_ID_' + cnt;
						fld.value = productPriceID;
						oCell.appendChild(fld);

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_PRODUCT_XML_ID_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_PRODUCT_XML_ID_' + cnt;
						fld.value = productXmlID;
						oCell.appendChild(fld);

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_CATALOG_XML_ID_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_CATALOG_XML_ID_' + cnt;
						fld.value = catalogXmlID;
						oCell.appendChild(fld);

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_NAME_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_NAME_' + cnt;
						fld.value = productName;
						oCell.appendChild(fld);
						
						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_DETAIL_PAGE_URL_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_DETAIL_PAGE_URL_' + cnt;
						fld.value = productPath;
						oCell.appendChild(fld);

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_PRICE_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_PRICE_' + cnt;
						fld.value = productPrice;
						oCell.appendChild(fld);

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_CURRENCY_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_CURRENCY_' + cnt;
						fld.value = productCurrency;
						oCell.appendChild(fld);

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_DISCOUNT_PRICE_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_DISCOUNT_PRICE_' + cnt;
						fld.value = productDiscount;
						oCell.appendChild(fld);
						
						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_VAT_RATE_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_VAT_RATE_' + cnt;
						fld.value = vatRate;
						oCell.appendChild(fld);


						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_WEIGHT_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_WEIGHT_' + cnt;
						fld.value = productWeight;
						oCell.appendChild(fld);

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_QUANTITY_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_QUANTITY_' + cnt;
						fld.value = productQuantity;
						oCell.appendChild(fld);

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_NOTES_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_NOTES_' + cnt;
						fld.value = productDescr;
						oCell.appendChild(fld);

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_CALLBACK_FUNC_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_CALLBACK_FUNC_' + cnt;
						fld.value = basketCallbackFunc;
						oCell.appendChild(fld);

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_ORDER_CALLBACK_FUNC_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_ORDER_CALLBACK_FUNC_' + cnt;
						fld.value = basketOrderCallbackFunc;
						oCell.appendChild(fld);

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_CANCEL_CALLBACK_FUNC_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_CANCEL_CALLBACK_FUNC_' + cnt;
						fld.value = basketCancelCallbackFunc;
						oCell.appendChild(fld);

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_PAY_CALLBACK_FUNC_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_PAY_CALLBACK_FUNC_' + cnt;
						fld.value = basketPayCallbackFunc;
						oCell.appendChild(fld);

						var propsCnt = -1;
						if (arProductProps)
						{
							propsCnt = arProductProps.length - 1;
							for (var i = 0; i < arProductProps.length; i++)
							{
								var fld;
								if (currentBrowserDetected == "IE")
									fld = document.createElement('<input name="BASKET_PROP_NAME_' + cnt + '_' + i + '">');
								else
									fld = document.createElement('input');
								fld.type = "hidden";
								fld.name = 'BASKET_PROP_NAME_' + cnt + '_' + i;
								fld.value = arProductProps[i][0];
								oCell.appendChild(fld);

								var fld;
								if (currentBrowserDetected == "IE")
									fld = document.createElement('<input name="BASKET_PROP_VALUE_' + cnt + '_' + i + '">');
								else
									fld = document.createElement('input');
								fld.type = "hidden";
								fld.name = 'BASKET_PROP_VALUE_' + cnt + '_' + i;
								fld.value = arProductProps[i][1];
								oCell.appendChild(fld);

								var fld;
								if (currentBrowserDetected == "IE")
									fld = document.createElement('<input name="BASKET_PROP_CODE_' + cnt + '_' + i + '">');
								else
									fld = document.createElement('input');
								fld.type = "hidden";
								fld.name = 'BASKET_PROP_CODE_' + cnt + '_' + i;
								fld.value = arProductProps[i][2];
								oCell.appendChild(fld);

								var fld;
								if (currentBrowserDetected == "IE")
									fld = document.createElement('<input name="BASKET_PROP_SORT_' + cnt + '_' + i + '">');
								else
									fld = document.createElement('input');
								fld.type = "hidden";
								fld.name = 'BASKET_PROP_SORT_' + cnt + '_' + i;
								fld.value = arProductProps[i][3];
								oCell.appendChild(fld);

								var fld;
								if (currentBrowserDetected == "IE")
									fld = document.createElement('<input name="BASKET_PROP_ID_' + cnt + '_' + i + '">');
								else
									fld = document.createElement('input');
								fld.type = "hidden";
								fld.name = 'BASKET_PROP_ID_' + cnt + '_' + i;
								fld.value = arProductProps[i][4];
								oCell.appendChild(fld);
							}
						}

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_PROP_COUNTER_' + cnt + '" id="BASKET_PROP_COUNTER_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_PROP_COUNTER_' + cnt;
						fld.id = 'BASKET_PROP_COUNTER_' + cnt;
						fld.value = propsCnt;
						
						oCell.appendChild(fld);

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="MOVE2NEW_ORDER_' + cnt + '" id="MOVE2NEW_ORDER_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "checkbox";
						fld.name = 'MOVE2NEW_ORDER_' + cnt;
						fld.id = 'MOVE2NEW_ORDER_' + cnt;
						fld.value = "Y";
						oCell.appendChild(fld);

						var fld;
						if (currentBrowserDetected == "IE")
							fld = document.createElement('<input name="BASKET_ID_' + cnt + '" id="BASKET_ID_' + cnt + '">');
						else
							fld = document.createElement('input');
						fld.type = "hidden";
						fld.name = 'BASKET_ID_' + cnt;
						fld.id = 'BASKET_ID_' + cnt;
						fld.value = basketID;
						oCell.appendChild(fld);

						var fld = document.getElementById("MOVE2NEW_ORDER_" + cnt);
						if (fld.addEventListener)
							fld.addEventListener('click', CheckFullDivision, false);
						else
							fld.attachEvent('onclick', CheckFullDivision);

						return cnt;
					}

					function BasketModifyProduct(index, moduleID, productID, productName, productPath, arProductProps, productPrice, productCurrency, productDiscount, productWeight, productQuantity, productDescr, basketCallbackFunc, basketOrderCallbackFunc, basketCancelCallbackFunc, basketPayCallbackFunc, catalogXmlID, productXmlID, productPriceID, vatRate)
					{
						if (moduleID != null)
						{
							if (!moduleID)
								moduleID = "";

							var fld = eval("document.forder_edit.BASKET_MODULE_" + index);
							fld.value = moduleID;
						}

						if (!productID)
							productID = "";
						var fld = eval("document.forder_edit.BASKET_PRODUCT_ID_" + index);
						fld.value = productID;

						if (!catalogXmlID)
							catalogXmlID = "";
						var fld = eval("document.forder_edit.BASKET_CATALOG_XML_ID_" + index);
						fld.value = catalogXmlID;

						if (!productXmlID)
							productXmlID = "";
						var fld = eval("document.forder_edit.BASKET_PRODUCT_XML_ID_" + index);
						fld.value = productXmlID;

						if (!productName)
							productName = "";
						var fld = eval("document.forder_edit.BASKET_NAME_" + index);
						fld.value = productName;
						
						if (!productPath)
							productPath = "";
						var fld = eval("document.forder_edit.BASKET_DETAIL_PAGE_URL_" + index);
						fld.value = productPath;

						if (!productPrice)
							productPrice = "";
						var fld = eval("document.forder_edit.BASKET_PRICE_" + index);
						fld.value = productPrice;

						if (!productCurrency)
							productCurrency = "";
						var fld = eval("document.forder_edit.BASKET_CURRENCY_" + index);
						fld.value = productCurrency;

						if (!productDiscount)
							productDiscount = "";
						var fld = eval("document.forder_edit.BASKET_DISCOUNT_PRICE_" + index);
						fld.value = productDiscount;
					
						if (!vatRate)
							vatRate = "";
						var fld = eval("document.forder_edit.BASKET_VAT_RATE_" + index);
						fld.value = vatRate;

						if (!productWeight)
							productWeight = "";
						var fld = eval("document.forder_edit.BASKET_WEIGHT_" + index);
						fld.value = productWeight;

						if (!productQuantity)
							productQuantity = "";
						var fld = eval("document.forder_edit.BASKET_QUANTITY_" + index);
						fld.value = productQuantity;

						if (!productDescr)
							productDescr = "";
						var fld = eval("document.forder_edit.BASKET_NOTES_" + index);
						fld.value = productDescr;

						if (basketCallbackFunc != null)
						{
							if (!basketCallbackFunc)
								basketCallbackFunc = "";

							var fld = eval("document.forder_edit.BASKET_CALLBACK_FUNC_" + index);
							fld.value = basketCallbackFunc;
						}

						if (basketOrderCallbackFunc != null)
						{
							if (!basketOrderCallbackFunc)
								basketOrderCallbackFunc = "";

							var fld = eval("document.forder_edit.BASKET_ORDER_CALLBACK_FUNC_" + index);
							fld.value = basketOrderCallbackFunc;
						}

						if (basketCancelCallbackFunc != null)
						{
							if (!basketCancelCallbackFunc)
								basketCancelCallbackFunc = "";

							var fld = eval("document.forder_edit.BASKET_CANCEL_CALLBACK_FUNC_" + index);
							fld.value = basketCancelCallbackFunc;
						}

						if (basketPayCallbackFunc != null)
						{
							if (!basketPayCallbackFunc)
								basketPayCallbackFunc = "";

							var fld = eval("document.forder_edit.BASKET_PAY_CALLBACK_FUNC_" + index);
							fld.value = basketPayCallbackFunc;
						}

						if (productPriceID != null)
						{
							if (!productPriceID)
								productPriceID = "";

							var fld = eval("document.forder_edit.BASKET_PRODUCT_PRICE_ID_" + index);
							fld.value = productPriceID;
						}

						var oPropCntr = document.getElementById("BASKET_PROP_COUNTER_" + index);
						var propCnt = parseInt(oPropCntr.value);

						if (arProductProps)
						{
							var oRow = document.getElementById("ID_BASKET_ROW_" + index);
							var oCell = oRow.cells[0];

							for (var i = 0; i < arProductProps.length; i++)
							{
								var fld = eval("document.forder_edit.BASKET_PROP_NAME_" + index + "_" + i);
								if (fld)
								{
									fld.value = arProductProps[i][0];
									var fld = eval("document.forder_edit.BASKET_PROP_VALUE_" + index + "_" + i);
									fld.value = arProductProps[i][1];
									var fld = eval("document.forder_edit.BASKET_PROP_CODE_" + index + "_" + i);
									fld.value = arProductProps[i][2];
									var fld = eval("document.forder_edit.BASKET_PROP_SORT_" + index + "_" + i);
									fld.value = arProductProps[i][3];
								}
								else
								{
									propCnt++;

									var fld;
									if (currentBrowserDetected == "IE")
										fld = document.createElement('<input name="BASKET_PROP_NAME_' + index + '_' + i + '">');
									else
										fld = document.createElement('input');
									fld.type = "hidden";
									fld.name = 'BASKET_PROP_NAME_' + index + '_' + i;
									fld.value = arProductProps[i][0];
									oCell.appendChild(fld);

									var fld;
									if (currentBrowserDetected == "IE")
										fld = document.createElement('<input name="BASKET_PROP_VALUE_' + index + '_' + i + '">');
									else
										fld = document.createElement('input');
									fld.type = "hidden";
									fld.name = 'BASKET_PROP_VALUE_' + index + '_' + i;
									fld.value = arProductProps[i][1];
									oCell.appendChild(fld);

									var fld;
									if (currentBrowserDetected == "IE")
										fld = document.createElement('<input name="BASKET_PROP_CODE_' + index + '_' + i + '">');
									else
										fld = document.createElement('input');
									fld.type = "hidden";
									fld.name = 'BASKET_PROP_CODE_' + index + '_' + i;
									fld.value = arProductProps[i][2];
									oCell.appendChild(fld);

									var fld;
									if (currentBrowserDetected == "IE")
										fld = document.createElement('<input name="BASKET_PROP_SORT_' + index + '_' + i + '">');
									else
										fld = document.createElement('input');
									fld.type = "hidden";
									fld.name = 'BASKET_PROP_SORT_' + index + '_' + i;
									fld.value = arProductProps[i][3];
									oCell.appendChild(fld);
								}
							}
						}

						//oPropCntr.value = propCnt;
					}

					var queryBasketParams = '';
					<?
					$events = GetModuleEvents("sale", "OnBasketItemSave");
					if ($arEvent = $events->Fetch())
					{
						$str = ExecuteModuleEvent($arEvent, $ID);
						?>queryBasketParams = '<?= $str ?>';<?
					}
					?>
					//queryBasketParams = '/wizard/10.php';

  					function BasketSaveProduct(index)
					{
						
						editedRecord = -1;

						<?if ($bSimpleForm):?>
							var moduleID = null;
						<?else:?>
							var fld = eval("document.forder_edit.BASKET_MODULE1_" + index);
							var moduleID = fld[fld.selectedIndex].value;
						<?endif;?>

						var fld = eval("document.forder_edit.BASKET_PRODUCT_ID1_" + index);
						var productID = fld.value;
						var fld = eval("document.forder_edit.BASKET_CATALOG_XML_ID1_" + index);
						var catalogXmlID = fld.value;
						var fld = eval("document.forder_edit.BASKET_PRODUCT_XML_ID1_" + index);
						var productXmlID = fld.value;
						var fld = eval("document.forder_edit.BASKET_NAME1_" + index);
						var productName = fld.value;
						if(productID.length <= 0 && productName.length <= 0)
						{
							BasketDeleteProduct(index);
							return;
						}				
						var fld = eval("document.forder_edit.BASKET_DETAIL_PAGE_URL1_" + index);
						var productPath = fld.value;
						var fld = eval("document.forder_edit.BASKET_PRICE1_" + index);
						var productPrice = fld.value;
						var fld = eval("document.forder_edit.BASKET_CURRENCY1_" + index);
						var productCurrency = fld.value;
						var fld = eval("document.forder_edit.BASKET_DISCOUNT_PRICE1_" + index);
						var productDiscount = fld.value;
						var fld = eval("document.forder_edit.BASKET_VAT_RATE1_" + index);
						var vatRate = fld.value;
						var fld = eval("document.forder_edit.BASKET_WEIGHT1_" + index);
						var productWeight = fld.value;
						var fld = eval("document.forder_edit.BASKET_QUANTITY1_" + index);
						var productQuantity = fld.value;
						var fld = eval("document.forder_edit.BASKET_NOTES1_" + index);
						var productDescr = fld.value;

						<?if ($bSimpleForm):?>
							var basketCallbackFunc = null;
							var basketOrderCallbackFunc = null;
							var basketCancelCallbackFunc = null;
							var basketPayCallbackFunc = null;
						<?else:?>
							var fld = eval("document.forder_edit.BASKET_CALLBACK_FUNC1_" + index);
							var basketCallbackFunc = fld.value;
							var fld = eval("document.forder_edit.BASKET_ORDER_CALLBACK_FUNC1_" + index);
							var basketOrderCallbackFunc = fld.value;
							var fld = eval("document.forder_edit.BASKET_CANCEL_CALLBACK_FUNC1_" + index);
							var basketCancelCallbackFunc = fld.value;
							var fld = eval("document.forder_edit.BASKET_PAY_CALLBACK_FUNC1_" + index);
							var basketPayCallbackFunc = fld.value;
						<?endif;?>

						var arProductProps = new Array();

						var oPropCntr = document.getElementById("BASKET_PROP_COUNTER_" + index);
						var propCnt = parseInt(oPropCntr.value);
						if (propCnt >= 0)
						{
							for (var i = 0; i <= propCnt; i++)
							{
								arProductProps[i] = new Array();
								var fld = eval("document.forder_edit.BASKET_PROP_NAME1_" + index + "_" + i);
								if(fld)
								{
									arProductProps[i][0] = fld.value;
									var fld = eval("document.forder_edit.BASKET_PROP_VALUE1_" + index + "_" + i);
									arProductProps[i][1] = fld.value;
									var fld = eval("document.forder_edit.BASKET_PROP_CODE1_" + index + "_" + i);
									arProductProps[i][2] = fld.value;
									var fld = eval("document.forder_edit.BASKET_PROP_SORT1_" + index + "_" + i);
									arProductProps[i][3] = fld.value;
								}
							}
						}
							BasketModifyProduct(index, moduleID, productID, productName, productPath, arProductProps, productPrice, productCurrency, productDiscount, productWeight, productQuantity, productDescr, basketCallbackFunc, basketOrderCallbackFunc, basketCancelCallbackFunc, basketPayCallbackFunc, catalogXmlID, productXmlID, '',  vatRate);
							if (queryBasketParams.length > 0)
							{
								var strProps = '';
								for (var i = 0; i < arProductProps.length; i++)
								{
									strProps += '&arProductProps[' + i + '][0]=' + escape(arProductProps[i][0]);
									strProps += '&arProductProps[' + i + '][1]=' + escape(arProductProps[i][1]);
									strProps += '&arProductProps[' + i + '][2]=' + escape(arProductProps[i][2]);
									strProps += '&arProductProps[' + i + '][3]=' + escape(arProductProps[i][3]);
								}

								window.frames["hiddenframe_basket"].location.replace(queryBasketParams + '?ID=<?= $ID ?>&index=' + escape(index) + '&moduleID=' + escape(moduleID) + '&productID=' + escape(productID) + '&productName=' + escape(productName) + '&productPath=' + escape(productPath) + '&productPrice=' + escape(productPrice) + '&productCurrency=' + escape(productCurrency) + '&productDiscount=' + escape(productDiscount) + '&vatRate=' + escape(vatRate) + '&productWeight=' + escape(productWeight) + '&productQuantity=' + escape(productQuantity) + '&productDescr=' + escape(productDescr) + '&basketCallbackFunc=' + escape(basketCallbackFunc) + '&basketOrderCallbackFunc=' + escape(basketOrderCallbackFunc) + '&basketCancelCallbackFunc=' + escape(basketCancelCallbackFunc) + '&basketPayCallbackFunc=' + escape(basketPayCallbackFunc) + '&catalogXmlID=' + escape(catalogXmlID) + '&productXmlID=' + escape(productXmlID) + '&productPriceID=' + escape(eval("document.forder_edit.BASKET_PRODUCT_PRICE_ID_" + index + ".value")) + strProps);
							}
						
					}

					function BasketDeleteProduct(index)
					{
						var oTbl = document.getElementById("ID_BASKET_TABLE");
						ind = -1;
						for (var i = 0; i < oTbl.rows.length; i++)
						{
							if (oTbl.rows[i].id == "ID_BASKET_ROW_" + index)
							{
								ind = i;
								break;
							}
						}
						if (ind >= 0)
							oTbl.deleteRow(ind);
						
						if(oRowEdit = document.getElementById("ID_BASKET_ROW_" + index + "_edit"))
						{
							oTbl.deleteRow(oRowEdit.rowIndex);
						}
						var oCntr = document.getElementById("BASKET_COUNTER");
						var cnt = parseInt(oCntr.value) - 1;
						editedRecord =  -1;
						oCntr.value = cnt;
					
						CheckFullDivision();
					}

					var iblockIDTmp = 0;

					function FillProductFields(index, arParams, iblockID)
					{
						for (key in arParams)
						{
							var fld = null;

							if (key == "id")
								fld = eval("document.forder_edit.BASKET_PRODUCT_ID1_" + index);
							else if (key == "catalogXmlID")
								fld = eval("document.forder_edit.BASKET_CATALOG_XML_ID1_" + index);
							else if (key == "productXmlID")
								fld = eval("document.forder_edit.BASKET_PRODUCT_XML_ID1_" + index);
							else if (key == "name")
								fld = eval("document.forder_edit.BASKET_NAME1_" + index);
							else if (key == "url")
								fld = eval("document.forder_edit.BASKET_DETAIL_PAGE_URL1_" + index);
							else if (key == "price")
								fld = eval("document.forder_edit.BASKET_PRICE1_" + index);
							else if (key == "weight")
								fld = eval("document.forder_edit.BASKET_WEIGHT1_" + index);
							else if (key == "priceType")
								fld = eval("document.forder_edit.BASKET_NOTES1_" + index);
							else if (key == "discountPrice")
								fld = eval("document.forder_edit.BASKET_DISCOUNT_PRICE1_" + index);
							else if (key == "vatRate")
								fld = eval("document.forder_edit.BASKET_VAT_RATE1_" + index);
							else if (key == "quantity")
								fld = eval("document.forder_edit.BASKET_QUANTITY1_" + index);
							<?if ($bSimpleForm):?>
								else if (key == "callback")
									fld = eval("document.forder_edit.BASKET_CALLBACK_FUNC_" + index);
								else if (key == "orderCallback")
									fld = eval("document.forder_edit.BASKET_ORDER_CALLBACK_FUNC_" + index);
								else if (key == "cancelCallback")
									fld = eval("document.forder_edit.BASKET_CANCEL_CALLBACK_FUNC_" + index);
								else if (key == "payCallback")
									fld = eval("document.forder_edit.BASKET_PAY_CALLBACK_FUNC_" + index);
								else if (key == "module")
									fld = eval("document.forder_edit.BASKET_MODULE_" + index);
							<?else:?>
								else if (key == "callback")
									fld = eval("document.forder_edit.BASKET_CALLBACK_FUNC1_" + index);
								else if (key == "orderCallback")
									fld = eval("document.forder_edit.BASKET_ORDER_CALLBACK_FUNC1_" + index);
								else if (key == "cancelCallback")
									fld = eval("document.forder_edit.BASKET_CANCEL_CALLBACK_FUNC1_" + index);
								else if (key == "payCallback")
									fld = eval("document.forder_edit.BASKET_PAY_CALLBACK_FUNC1_" + index);
							<?endif;?>

							if (fld != null)
								fld.value = arParams[key];
						}

						for (key in arParams)
						{
							var fld = null;

							if (key == "currency")
								fld = eval("document.forder_edit.BASKET_CURRENCY1_" + index);
							<?if (!$bSimpleForm):?>
								else if (key == "module")
									fld = eval("document.forder_edit.BASKET_MODULE1_" + index);
							<?endif;?>

							if (fld != null)
							{
								for (var i = 0; i < fld.options.length; i++)
								{
									if (fld.options[i].value == arParams[key])
									{
										fld.selectedIndex = i;
										break;
									}
								}
								fld.value = arParams[key];
							}
						}

						if (arParams["props"])
						{
							for (var i = 0; i < arParams["props"].length; i++)
								BasketAddPropSection(-1, index, arParams["props"][i][0], arParams["props"][i][1], arParams["props"][i][2], arParams["props"][i][3]);
						}

						iblockIDTmp = iblockID;
					}

					function FillProductFields111(id, name, url, price, currency, weight, priceType, catalogXmlID, productXmlID, index, iblockID)
					{
						var fld = eval("document.forder_edit.BASKET_PRODUCT_ID1_" + index);
						fld.value = id;
						var fld = eval("document.forder_edit.BASKET_CATALOG_XML_ID1_" + index);
						fld.value = catalogXmlID;
						var fld = eval("document.forder_edit.BASKET_PRODUCT_XML_ID1_" + index);
						fld.value = productXmlID;
						var fld = eval("document.forder_edit.BASKET_NAME1_" + index);
						fld.value = name;
						var fld = eval("document.forder_edit.BASKET_DETAIL_PAGE_URL1_" + index);
						fld.value = url;
						var fld = eval("document.forder_edit.BASKET_PRICE1_" + index);
						fld.value = price;
						var fld = eval("document.forder_edit.BASKET_CURRENCY1_" + index);
						for (var i = 0; i < fld.options.length; i++)
						{
							if (fld.options[i].value == currency)
							{
								fld.selectedIndex = i;
								break;
							}
						}
						fld.value = currency;
						var fld = eval("document.forder_edit.BASKET_WEIGHT1_" + index);
						fld.value = weight;
						var fld = eval("document.forder_edit.BASKET_NOTES1_" + index);
						fld.value = priceType;

						iblockIDTmp = iblockID;
					}

					function ProductSearchOpen(index)
					{
						var quantity = eval("document.forder_edit.BASKET_QUANTITY1_" + index + ".value");
						window.open('sale_product_search.php?func_name=FillProductFields&index=' + index + '&QUANTITY=' + quantity + '&BUYER_ID=' + document.forder_edit.USER_ID.value + '&IBLOCK_ID=' + iblockIDTmp, '', 'scrollbars=yes,resizable=yes,width=600,height=500,top='+Math.floor((screen.height - 500)/2-14)+',left='+Math.floor((screen.width - 600)/2-5));
					}

					function BasketShowProduct(index, type)
					{
						if (type == 1 && editedRecord >= 0 && editedRecord != index)
						{
							var editedRecordTmp = editedRecord;
							BasketSaveProduct(editedRecordTmp);
							BasketShowProduct(editedRecordTmp, 2);
						}

						var oRow = document.getElementById("ID_BASKET_ROW_" + index);
						if(!oRow)
							return false;

						var oTbl = document.getElementById("ID_BASKET_TABLE");
						if (type == 1)
						{
							var oRowCheck = document.getElementById("ID_BASKET_ROW_" + index + "_edit");
							if(oRowCheck)
							{
								oTbl.deleteRow(oRowCheck.rowIndex);
							}
							
							var oRow = oTbl.insertRow(oRow.rowIndex + 1);
							oRow.id = "ID_BASKET_ROW_" + index + "_edit";
							editedRecord = index;

								if (oRow.removeEventListener)
								{
									oRow.removeEventListener('dblclick', eval("BasketShowProduct_" + index), false);
									oRow.removeEventListener('onmouseover', eval("BasketShowProductOver_" + index), false);
									oRow.removeEventListener('onmouseout', eval("BasketShowProductOut_" + index), false);
								}
								else
								{
									oRow.detachEvent('ondblclick', eval("BasketShowProduct_" + index));
									oRow.detachEvent('onmouseover', eval("BasketShowProductOver_" + index));
									oRow.detachEvent('onmouseout', eval("BasketShowProductOut_" + index));
								}
							
								var oCell = oRow.insertCell(-1);
								oCell.colSpan = 8;
								oCell.innerHTML = '<table id="ID_TEMP_TABLE" width="100%"></table>';

								var oTbl = document.getElementById("ID_TEMP_TABLE");

								var oRow = oTbl.insertRow(-1);
								var oCell = oRow.insertCell(-1);
								oCell.className = 'field-name';
								oCell.width = '40%';
								oCell.innerHTML = '<span class="required">*</span><b><?= GetMessage("SOE_MODULE") ?>:';

								var oCell = oRow.insertCell(-1);
								oCell.width = '60%';
								<?if ($bSimpleForm):?>
									oCell.innerHTML = '<?= GetMessage("SOE_MODULE_CATALOG") ?>';
								<?else:?>
									str = '<select name="BASKET_MODULE1_' + index + '" OnChange="ModuleChange(' + index + ')" id="ID_BASKET_MODULE1_' + index + '">';
									<?
									$dbModuleList = CModule::GetList();
									while ($arModuleList = $dbModuleList->Fetch())
									{
										?>str += '<option value="<?= $arModuleList["ID"] ?>"><?= htmlspecialcharsEx($arModuleList["ID"]) ?></option>';<?
									}
									?>
									str += '</select>';
									oCell.innerHTML = str;

									var fld = eval("document.forder_edit.BASKET_MODULE_" + index);
									var moduleID = fld.value;
									if (moduleID.length > 0)
									{
										var sBasketModule = document.getElementById("ID_BASKET_MODULE1_" + index);
										for (var i = 0; i < sBasketModule.options.length; i++)
										{
											if (sBasketModule.options[i].value == moduleID)
											{
												sBasketModule.selectedIndex = i;
												break;
											}
										}
									}
								<?endif;?>

								var oRow = oTbl.insertRow(-1);
								var oCell = oRow.insertCell(-1);
								oCell.className = 'field-name';
								oCell.innerHTML = '<span class="required">*</span><?= GetMessage("SOE_PRODUCT_ID") ?>:';

								var oCell = oRow.insertCell(-1);
								var fld = eval("document.forder_edit.BASKET_PRODUCT_ID_" + index);
								var productID = fld.value;
								oCell.innerHTML = '<input name="BASKET_PRODUCT_ID1_' + index + '" value="' + productID + '" size="5" type="text">&nbsp;<input type="button" id="basket_product_button_' + index + '" value="..." onClick="ProductSearchOpen(' + index + ')">';
								ModuleChange(index);

								var oRow = oTbl.insertRow(-1);
								var oCell = oRow.insertCell(-1);
								oCell.className = 'field-name';
								oCell.innerHTML = '<span class="required">*</span><?= GetMessage("SOE_ITEM_NAME") ?>:';

								var oCell = oRow.insertCell(-1);
								var fld = eval("document.forder_edit.BASKET_NAME_" + index);
								var productName = fld.value;
								if (currentBrowserDetected == "IE")
									var __input = document.createElement('<input name="BASKET_NAME1_' + index + '" type="text">');
								else
									var __input = document.createElement('INPUT');
								
								__input.type = 'text';
								__input.name = 'BASKET_NAME1_' + index;
								__input.value = productName;
								__input.setAttribute('size', '40');
								oCell.appendChild(__input);
								
								//oCell.innerHTML = '<input name="BASKET_NAME1_' + index + '" size="40" type="text">';
								//document.forms.forder_edit['BASKET_NAME1_' + index].value = productName;
								
								var oRow = oTbl.insertRow(-1);
								var oCell = oRow.insertCell(-1);
								oCell.className = 'field-name';
								oCell.innerHTML = '<?= GetMessage("SOE_ITEM_PATH") ?>:';

								var oCell = oRow.insertCell(-1);
								var fld = eval("document.forder_edit.BASKET_DETAIL_PAGE_URL_" + index);
								var productPath = fld.value;
								oCell.innerHTML = '<input name="BASKET_DETAIL_PAGE_URL1_' + index + '" value="' + productPath + '" size="40" type="text">';

								var oRow = oTbl.insertRow(-1);
								var oCell = oRow.insertCell(-1);
								oCell.className = 'field-name';
								oCell.innerHTML = '<?= GetMessage("SOE_JS_CAT_XML_ID") ?>:';

								var oCell = oRow.insertCell(-1);
								var fld = eval("document.forder_edit.BASKET_CATALOG_XML_ID_" + index);
								var catalogXmlID = fld.value;
								oCell.innerHTML = '<input name="BASKET_CATALOG_XML_ID1_' + index + '" value="' + catalogXmlID + '" size="40" type="text">';

								var oRow = oTbl.insertRow(-1);
								var oCell = oRow.insertCell(-1);
								oCell.className = 'field-name';
								oCell.innerHTML = '<?= GetMessage("SOE_JS_PROD_XML_ID") ?>:';

								var oCell = oRow.insertCell(-1);
								var fld = eval("document.forder_edit.BASKET_PRODUCT_XML_ID_" + index);
								var productXmlID = fld.value;
								oCell.innerHTML = '<input name="BASKET_PRODUCT_XML_ID1_' + index + '" value="' + productXmlID + '" size="40" type="text">';

								var oRow = oTbl.insertRow(-1);
								var oCell = oRow.insertCell(-1);
								oCell.className = 'field-name';
								oCell.vAlign = 'top';
								oCell.innerHTML = '<?= GetMessage("SOE_ITEM_PROPS") ?>:';

								var oCell = oRow.insertCell(-1);
								str  = '<table border="0" cellpadding="3" cellspacing="1" id="BASKET_PROP_TABLE_' + index + '" class="internal">';
								str += '<tr class="heading"><td><span class="required">*</span><?= GetMessage("SOE_IP_NAME") ?></td>';
								str += '<td><?= GetMessage("SOE_IP_VALUE") ?></td>';
								str += '<td><?= GetMessage("SOE_IP_CODE") ?></td>';
								str += '<td><?= GetMessage("SOE_IP_SORT") ?></td></tr>';
								str += '</table>';
								str += '<input type="button" value="<?= GetMessage("SOE_IP_MORE") ?>" OnClick="BasketAddPropSection(-1, ' + index + ')">';
								oCell.innerHTML = str;

								var fld = eval("document.forder_edit.BASKET_PROP_NAME_" + index + "_0");
								if(!fld)
									document.getElementById("BASKET_PROP_COUNTER_" + index).value = -1;
								var oPropCntr = document.getElementById("BASKET_PROP_COUNTER_" + index);
								var propCnt = parseInt(oPropCntr.value);

								if (propCnt > 0)
									for (var i = 0; i <= propCnt; i++)
									{
										var fld = eval("document.forder_edit.BASKET_PROP_NAME_" + index + "_" + i);
										if(fld)
										{
											var fld1 = eval("document.forder_edit.BASKET_PROP_VALUE_" + index + "_" + i);
											var fld2 = eval("document.forder_edit.BASKET_PROP_CODE_" + index + "_" + i);
											var fld3 = eval("document.forder_edit.BASKET_PROP_SORT_" + index + "_" + i);
											BasketAddPropSection(i, index, fld.value, fld1.value, fld2.value, fld3.value);
										}

									}
								else
									BasketAddPropSection(-1, index);

								var oRow = oTbl.insertRow(-1);
								var oCell = oRow.insertCell(-1);
								oCell.className = 'field-name';
								oCell.innerHTML = '<?= GetMessage("SOE_ITEM_PRICE") ?>:';

								var oCell = oRow.insertCell(-1);
								var fld = eval("document.forder_edit.BASKET_PRICE_" + index);
								var productPrice = fld.value;
								str = '<input type="text" name="BASKET_PRICE1_' + index + '" size="10" maxlength="20" value="' + productPrice + '">';
								str += '<select name="BASKET_CURRENCY1_' + index + '" id="ID_BASKET_CURRENCY1_' + index + '">';
								<?
								$dbCurrency = CCurrency::GetList(($by="sort"), ($order="asc"));
								while ($arCurrency = $dbCurrency->Fetch())
								{
									?>str += '<option value="<?= $arCurrency["CURRENCY"] ?>"><?= $arCurrency["CURRENCY"] ?></option>';<?
								}
								?>
								str += '</select>';
								oCell.innerHTML = str;

								var fld = eval("document.forder_edit.BASKET_CURRENCY_" + index);
								var productCurrency = fld.value;
								if (productCurrency.length > 0)
								{
									var sBasketCurrency = document.getElementById("ID_BASKET_CURRENCY1_" + index);
									for (var i = 0; i < sBasketCurrency.options.length; i++)
									{
										if (sBasketCurrency.options[i].value == productCurrency)
										{
											sBasketCurrency.selectedIndex = i;
											break;
										}
									}
								}

								var oRow = oTbl.insertRow(-1);
								var oCell = oRow.insertCell(-1);
								oCell.className = 'field-name';
								oCell.innerHTML = '<?= GetMessage("SOE_ITEM_DISCOUNT") ?>:';

								var oCell = oRow.insertCell(-1);
								var fld = eval("document.forder_edit.BASKET_DISCOUNT_PRICE_" + index);
								var productDiscount = fld.value;
								oCell.innerHTML = '<input type="text" name="BASKET_DISCOUNT_PRICE1_' + index + '" id="ID_BASKET_DISCOUNT_PRICE1_' + index + '" size="10" maxlength="20" value="' + productDiscount + '">';

								var fld = document.getElementById("ID_BASKET_DISCOUNT_PRICE1_" + index);
								fld.disabled = document.forder_edit.RE_COUNT.checked;
								

								var oRow = oTbl.insertRow(-1);
								var oCell = oRow.insertCell(-1);
								oCell.className = 'field-name';
								oCell.innerHTML = '<?= GetMessage("SOE_VAT") ?>:';

								var oCell = oRow.insertCell(-1);
								var fld = eval("document.forder_edit.BASKET_VAT_RATE_" + index);
								var vatRate = fld.value;
								oCell.innerHTML = '<input type="text" name="BASKET_VAT_RATE1_' + index + '" id="ID_BASKET_VAT_RATE1_' + index + '" size="10" maxlength="20" value="' + vatRate + '">';

								var oRow = oTbl.insertRow(-1);
								var oCell = oRow.insertCell(-1);
								oCell.className = 'field-name';
								oCell.innerHTML = '<?= GetMessage("SOE_WEIGHT") ?>:';

								var oCell = oRow.insertCell(-1);
								var fld = eval("document.forder_edit.BASKET_WEIGHT_" + index);
								var productWeight = fld.value;
								oCell.innerHTML = '<input type="text" name="BASKET_WEIGHT1_' + index + '" size="4" maxlength="20" value="' + productWeight + '">';

								var oRow = oTbl.insertRow(-1);
								var oCell = oRow.insertCell(-1);
								oCell.className = 'field-name';
								oCell.innerHTML = '<span class="required">*</span><?= GetMessage("SOE_ITEM_QUANTITY") ?>:';

								var oCell = oRow.insertCell(-1);
								var fld = eval("document.forder_edit.BASKET_QUANTITY_" + index);
								var productQuantity = fld.value;
								oCell.innerHTML = '<input type="text" name="BASKET_QUANTITY1_' + index + '" size="4" maxlength="20" value="' + productQuantity + '">';

								var oRow = oTbl.insertRow(-1);
								var oCell = oRow.insertCell(-1);
								oCell.className = 'field-name';
								oCell.innerHTML = '<?= GetMessage("SOE_ITEM_DESCR") ?>:';

								var oCell = oRow.insertCell(-1);
								var fld = eval("document.forder_edit.BASKET_NOTES_" + index);
								var productDescr = fld.value;
								oCell.innerHTML = '<input type="text" name="BASKET_NOTES1_' + index + '" size="40" maxlength="250" value="' + productDescr + '">';

								<?if ($bSimpleForm):?>
									var oRow = oTbl.insertRow(-1);
									var oCell = oRow.insertCell(-1);
									oCell.className = 'field-name';
									oCell.colSpan = 2;
									oCell.innerHTML = '<img src="/bitrix/images/1.gif" width="1" height="8">';
								<?else:?>
									var oRow = oTbl.insertRow(-1);
									var oCell = oRow.insertCell(-1);
									oCell.className = 'field-name';
									oCell.innerHTML = '<?= GetMessage("SOE_BASKET_CALLBACK_FUNC") ?>:';

									var oCell = oRow.insertCell(-1);
									var fld = eval("document.forder_edit.BASKET_CALLBACK_FUNC_" + index);
									var basketCallbackFunc = fld.value;
									oCell.innerHTML = '<input type="text" name="BASKET_CALLBACK_FUNC1_' + index + '" size="40" maxlength="250" value="' + basketCallbackFunc + '">';

									var oRow = oTbl.insertRow(-1);
									var oCell = oRow.insertCell(-1);
									oCell.className = 'field-name';
									oCell.innerHTML = '<?= GetMessage("SOE_BASKET_ORDER_CALLBACK_FUNC") ?>:';

									var oCell = oRow.insertCell(-1);
									var fld = eval("document.forder_edit.BASKET_ORDER_CALLBACK_FUNC_" + index);
									var basketOrderCallbackFunc = fld.value;
									oCell.innerHTML = '<input type="text" name="BASKET_ORDER_CALLBACK_FUNC1_' + index + '" size="40" maxlength="250" value="' + basketOrderCallbackFunc + '">';

									var oRow = oTbl.insertRow(-1);
									var oCell = oRow.insertCell(-1);
									oCell.className = 'field-name';
									oCell.innerHTML = '<?= GetMessage("SOE_BASKET_CANCEL_CALLBACK_FUNC") ?>:';

									var oCell = oRow.insertCell(-1);
									var fld = eval("document.forder_edit.BASKET_CANCEL_CALLBACK_FUNC_" + index);
									var basketCancelCallbackFunc = fld.value;
									oCell.innerHTML = '<input type="text" name="BASKET_CANCEL_CALLBACK_FUNC1_' + index + '" size="40" maxlength="250" value="' + basketCancelCallbackFunc + '">';

									var oRow = oTbl.insertRow(-1);
									var oCell = oRow.insertCell(-1);
									oCell.className = 'field-name';
									oCell.innerHTML = '<?= GetMessage("SOE_BASKET_PAY_CALLBACK_FUNC") ?>:';

									var oCell = oRow.insertCell(-1);
									var fld = eval("document.forder_edit.BASKET_PAY_CALLBACK_FUNC_" + index);
									var basketPayCallbackFunc = fld.value;
									oCell.innerHTML = '<input type="text" name="BASKET_PAY_CALLBACK_FUNC1_' + index + '" size="40" maxlength="250" value="' + basketPayCallbackFunc + '">';

									var oRow = oTbl.insertRow(-1);
									var oCell = oRow.insertCell(-1);
									oCell.className = 'field-name';
									oCell.colSpan = 2;
									oCell.innerHTML = '<img src="/bitrix/images/1.gif" width="1" height="8">';
								<?endif;?>

								var oRow = oTbl.insertRow(-1);
								var oCell = oRow.insertCell(-1);
								oCell.align = 'center';
								oCell.colSpan = 2;
								oCell.innerHTML = '<input type="button" name="btn1" value="<?= GetMessage("SOE_JS_SAVE") ?>" OnClick="BasketSaveProduct(' + index + ');BasketShowProduct(' + index + ', 2);">';
						}
						else	// type == 2
						{
							if(oRowEdit = document.getElementById("ID_BASKET_ROW_" + index + "_edit"))
							{
								oTbl.deleteRow(oRowEdit.rowIndex)
								var i = oRow.cells.length - 1;
								while (i > 0)
								{
									oRow.deleteCell(i);
									i = i - 1;
								}
							}

							eval("BasketShowProduct_" + index + " = function () { BasketShowProduct(index, 1); }");
							eval("BasketShowProductOver_" + index + " = function () { var oRow1 = document.getElementById('ID_BASKET_ROW_' + index); for (var i1 = 0; i1 < oRow1.cells.length; i1++) oRow1.cells[i1].style.backgroundColor = '#fefdea'; }");
							eval("BasketShowProductOut_" + index + " = function () { var oRow1 = document.getElementById('ID_BASKET_ROW_' + index); for (var i1 = 0; i1 < oRow1.cells.length; i1++) oRow1.cells[i1].style.backgroundColor = ''; }");

								if (oRow.addEventListener)
								{
									oRow.addEventListener('dblclick', eval("BasketShowProduct_" + index), false);
									oRow.addEventListener('onmouseover', eval("BasketShowProductOver_" + index), false);
									oRow.addEventListener('onmouseout', eval("BasketShowProductOut_" + index), false);
								}
								else
								{
									oRow.attachEvent('ondblclick', eval("BasketShowProduct_" + index));
									oRow.attachEvent('onmouseover', eval("BasketShowProductOver_" + index));
									oRow.attachEvent('onmouseout', eval("BasketShowProductOut_" + index));
								}

								var oCell = oRow.insertCell(-1);
								oCell.vAlign = 'top';

								var fld = eval("document.forder_edit.BASKET_DETAIL_PAGE_URL_" + index);
								var fld1 = eval("document.forder_edit.BASKET_NAME_" + index);
								
								if (fld.value > 0)
								{
									var __anchor = document.createElement('A');
									__anchor.href = fld.value;
									if (fld1.value > 0)
										__anchor.appendChild(document.createTextNode(fld1.value));
								}
								else
								{
									__anchor = document.createTextNode(fld1.value);
								}
								
								oCell.appendChild(__anchor);

								var oCell = oRow.insertCell(-1);
								oCell.vAlign = 'top';
								oCell.innerHTML = eval("document.forder_edit.BASKET_PRODUCT_XML_ID_" + index + ".value");

								var oCell = oRow.insertCell(-1);
								oCell.vAlign = 'top';
								str = '';

								var oPropCntr = document.getElementById("BASKET_PROP_COUNTER_" + index);
								var propCnt = parseInt(oPropCntr.value);
							
								if (propCnt >= 0)
								{
									for (var i = 0; i <= propCnt; i++)
									{
										var fld = eval("document.forder_edit.BASKET_PROP_NAME_" + index + "_" + i);
										var fld1 = eval("document.forder_edit.BASKET_PROP_VALUE_" + index + "_" + i);
										if (fld.value.length > 0 || fld1.value.length > 0)
											str += '<i>' + fld.value + ':</i> ' + fld1.value + '<br>';
									}
								}
								oCell.innerHTML = str;

								var oCell = oRow.insertCell(-1);
								oCell.vAlign = 'top';
								var fld = eval("document.forder_edit.BASKET_NOTES_" + index);
								oCell.innerHTML = fld.value;

								var oCell = oRow.insertCell(-1);
								oCell.vAlign = 'top';
								var fld = eval("document.forder_edit.BASKET_QUANTITY_" + index);
								oCell.innerHTML = fld.value;

								var oCell = oRow.insertCell(-1);
								oCell.vAlign = 'top';
								var fld = eval("document.forder_edit.BASKET_CURRENCY_" + index);
								var fld1 = eval("document.forder_edit.BASKET_PRICE_" + index);
								oCell.innerHTML = fld.value + ' ' + fld1.value;

								var oCell = oRow.insertCell(-1);
								oCell.vAlign = 'top';
								var str = '<a href="javascript: BasketShowProduct(' + index + ', 1);"><?= GetMessage("SOE_JS_EDIT") ?></a><br>';
								str += '<a href="javascript:if(confirm(\'<?= GetMessage("SOE_JS_DEL_CONF") ?>\')) BasketDeleteProduct(' + index + ')"><?= GetMessage("SOE_JS_DEL") ?></a>';
								oCell.innerHTML = str;
						}
					}

					function BasketAddPropSection(index, ind, propName, propValue, propCode, propSort)
					{
						var oTbl = document.getElementById("BASKET_PROP_TABLE_" + ind);
						if (!oTbl)
							return;

						if (!propName)
							propName = "";
						if (!propValue)
							propValue = "";
						if (!propCode)
							propCode = "";
						if (!propSort)
							propSort = "";
						if (index < 0)
						{
							var oCntr = document.getElementById("BASKET_PROP_COUNTER_" + ind);
							var index = parseInt(oCntr.value) + 1;
							oCntr.value = index;
						}

						var oRow = oTbl.insertRow(-1);
						var oCell = oRow.insertCell(-1);
						oCell.innerHTML = '<input type="text" name="BASKET_PROP_NAME1_' + ind + '_' + index + '" size="15" maxlength="250" value="' + propName + '">';

						var oCell = oRow.insertCell(-1);
						oCell.innerHTML = '<input type="text" name="BASKET_PROP_VALUE1_' + ind + '_' + index + '" size="20" maxlength="250" value="' + propValue + '">';

						var oCell = oRow.insertCell(-1);
						oCell.innerHTML = '<input type="text" name="BASKET_PROP_CODE1_' + ind + '_' + index + '" size="3" maxlength="250" value="' + propCode + '">';

						var oCell = oRow.insertCell(-1);
						oCell.innerHTML = '<input type="text" name="BASKET_PROP_SORT1_' + ind + '_' + index + '" size="2" maxlength="10" value="' + propSort + '">';
					}

					function CheckFullDivision()
					{
						var oCntr = document.getElementById("BASKET_COUNTER");
						var cnt = parseInt(oCntr.value);

						var bNeedFullDivision = false;
						var bAllChecked = true;
						for (var i = 0; i <= cnt; i++)
						{
							var fld = document.getElementById("MOVE2NEW_ORDER_" + i);
							if (fld)
							{
								if (fld.checked)
									bNeedFullDivision = true;
								else
									bAllChecked = false;
							}
						}

						if (bAllChecked)
						{
							for (var i = 0; i <= cnt; i++)
							{
								var fld = document.getElementById("MOVE2NEW_ORDER_" + i);
								if (fld)
									fld.checked = false;
							}
							var fld = document.getElementById("ID_FULL_DIVISION_TD");
							fld.disabled = true;
							document.forder_edit.ID_FULL_DIVISION.disabled = true;
						}
						else
						{
							var fld = document.getElementById("ID_FULL_DIVISION_TD");
							fld.disabled = !bNeedFullDivision;
							document.forder_edit.ID_FULL_DIVISION.disabled = !bNeedFullDivision;
							if (bNeedFullDivision)
								alert('<?echo GetMessage("SALE_F_ALERT_ORDER_DIV")?>');
						}
					}

					function OrderFormSubmit()
					{
						if (editedRecord >= 0)
						{
							var editedRecordTmp = editedRecord;
							BasketSaveProduct(editedRecordTmp);
							BasketShowProduct(editedRecordTmp, 2);
						}
						//document.forder_edit.submit();
					}
					//-->
					</script>

					<input type="hidden" name="BASKET_COUNTER" id="BASKET_COUNTER" value="-1">

					<table cellpadding="3" cellspacing="1" border="0" width="100%" class="internal" id="ID_BASKET_TABLE">
						<tr class="heading">
							<td width="0"><?= GetMessage("SOE_F_DIVIDE") ?><sup>1)</sup></td>
							<td><?echo GetMessage("SALE_F_NAME")?></td>
							<td><?echo GetMessage("SALE_F_XML_ID")?></td>
							<td><?echo GetMessage("SALE_F_PROPS")?></td>
							<td><?echo GetMessage("SALE_F_PTYPE")?></td>
							<td><?echo GetMessage("SALE_F_QUANTITY")?></td>
							<td><?echo GetMessage("SALE_F_PRICE")?></td>
							<td>&nbsp;</td>
						</tr>
					</table>
					<IFRAME name="hiddenframe_basket" id="id_hiddenframe_basket" src="" width="0" height="0" style="width:0px; height:0px; border: 0px"></IFRAME>
				</td>
			</tr>
			<script language="JavaScript">
			<!--
			document.forder_edit.BASKET_COUNTER.value = -1;
			//-->
			</script>
			<?
			$ind = -1;
			if ($bVarsFromForm)
			{
				for ($i = 0; $i <= IntVal($BASKET_COUNTER); $i++)
				{
					if (!isset(${"BASKET_ID_".$i}))
						continue;

					$ind++;

					$str_BASKET_PRODUCT_ID = htmlspecialchars(${"BASKET_PRODUCT_ID_".$ind}, ENT_QUOTES);
					$str_BASKET_PRODUCT_PRICE_ID = htmlspecialchars(${"BASKET_PRODUCT_PRICE_ID_".$ind}, ENT_QUOTES);
					$str_BASKET_PRICE = htmlspecialchars(${"BASKET_PRICE_".$ind}, ENT_QUOTES);
					$str_BASKET_CURRENCY = htmlspecialchars(${"BASKET_CURRENCY_".$ind}, ENT_QUOTES);
					$str_BASKET_WEIGHT = htmlspecialchars(${"BASKET_WEIGHT_".$ind}, ENT_QUOTES);
					$str_BASKET_QUANTITY = htmlspecialchars(${"BASKET_QUANTITY_".$ind}, ENT_QUOTES);
					//$str_BASKET_NAME = htmlspecialchars(${"BASKET_NAME_".$ind}, ENT_QUOTES);
					$str_BASKET_NAME = CUtil::JSEscape(${"BASKET_NAME_".$ind});
					$str_BASKET_MODULE = htmlspecialchars(${"BASKET_MODULE_".$ind}, ENT_QUOTES);
					$str_BASKET_NOTES = htmlspecialchars(${"BASKET_NOTES_".$ind}, ENT_QUOTES);
					$str_BASKET_DETAIL_PAGE_URL = htmlspecialchars(${"BASKET_DETAIL_PAGE_URL_".$ind}, ENT_QUOTES);
					$str_BASKET_DISCOUNT_PRICE = htmlspecialchars(${"BASKET_DISCOUNT_PRICE_".$ind}, ENT_QUOTES);
					$str_BASKET_CALLBACK_FUNC = htmlspecialchars(${"BASKET_CALLBACK_FUNC_".$ind}, ENT_QUOTES);
					$str_BASKET_ORDER_CALLBACK_FUNC = htmlspecialchars(${"BASKET_ORDER_CALLBACK_FUNC_".$ind}, ENT_QUOTES);
					$str_BASKET_CANCEL_CALLBACK_FUNC = htmlspecialchars(${"BASKET_CANCEL_CALLBACK_FUNC_".$ind}, ENT_QUOTES);
					$str_BASKET_PAY_CALLBACK_FUNC = htmlspecialchars(${"BASKET_PAY_CALLBACK_FUNC_".$ind}, ENT_QUOTES);
					$str_CATALOG_XML_ID = htmlspecialchars(${"BASKET_CATALOG_XML_ID_".$ind}, ENT_QUOTES);
					$str_PRODUCT_XML_ID = htmlspecialchars(${"BASKET_PRODUCT_XML_ID_".$ind}, ENT_QUOTES);
					$str_BASKET_VAT_RATE = htmlspecialchars(${"BASKET_VAT_RATE_".$ind}, ENT_QUOTES);
					$str_BASKET_BASKET_ID = IntVal(${"BASKET_ID_".$i});

					$str_BASKET_DELETE = ((${"BASKET_DELETE_".$ind} == "Y") ? "Y" : "N");

					$jnd = -1;
					$basketProps = "";
					if ($jnd < IntVal(${"BASKET_PROP_COUNTER_".$ind}))
					{
						for ($j = $jnd + 1; $j <= IntVal(${"BASKET_PROP_COUNTER_".$ind}); $j++)
						{
							if(strlen(${"BASKET_PROP_NAME_".$ind ."_".$jnd}) > 0)
							{
								$jnd++;

								$str_BASKET_PROP_NAME = htmlspecialchars(${"BASKET_PROP_NAME_".$ind ."_".$jnd}, ENT_QUOTES);
								$str_BASKET_PROP_VALUE = htmlspecialchars(${"BASKET_PROP_VALUE_".$ind ."_".$jnd}, ENT_QUOTES);
								$str_BASKET_PROP_CODE = htmlspecialchars(${"BASKET_PROP_CODE_".$ind ."_".$jnd}, ENT_QUOTES);
								$str_BASKET_PROP_SORT = htmlspecialchars(${"BASKET_PROP_SORT_".$ind ."_".$jnd}, ENT_QUOTES);

								if (strlen($basketProps) > 0)
									$basketProps .= ",";
								$basketProps .= "['".$str_BASKET_PROP_NAME."', '".$str_BASKET_PROP_VALUE."', '".$str_BASKET_PROP_CODE."', ".IntVal($str_BASKET_PROP_SORT).", 0]";
							}
						}
					}
					$basketProps = "[".$basketProps."]";
					?>
					<script language="JavaScript">
					<!--
					var ind = BasketAddNewProduct('<?= $str_BASKET_MODULE ?>', '<?= $str_BASKET_PRODUCT_ID ?>', '<?= $str_BASKET_NAME ?>', '<?= $str_BASKET_DETAIL_PAGE_URL ?>', <?= $basketProps ?>, '<?= $str_BASKET_PRICE ?>', '<?= $str_BASKET_CURRENCY ?>', '<?= $str_BASKET_DISCOUNT_PRICE ?>', '<?= $str_BASKET_WEIGHT ?>', '<?= $str_BASKET_QUANTITY ?>', '<?= $str_BASKET_NOTES ?>', '<?= $str_BASKET_CALLBACK_FUNC ?>', '<?= $str_BASKET_ORDER_CALLBACK_FUNC ?>', '<?= $str_BASKET_CANCEL_CALLBACK_FUNC ?>', '<?= $str_BASKET_PAY_CALLBACK_FUNC ?>', '<?= $str_CATALOG_XML_ID ?>', '<?= $str_PRODUCT_XML_ID ?>', <?= $str_BASKET_BASKET_ID ?>, '<?= $str_BASKET_PRODUCT_PRICE_ID ?>', '<?=$str_BASKET_VAT_RATE?>');
					BasketShowProduct(ind, 2);
					//-->
					</script>
					<?
				}
			}
			else
			{
				$dbBasket = CSaleBasket::GetList(
						array("NAME" => "ASC"),
						array("ORDER_ID" => $ID),
						false,
						false,
						array("ID", "PRODUCT_ID", "PRODUCT_PRICE_ID", "PRICE", "CURRENCY", "WEIGHT", "QUANTITY", "NAME", "MODULE", "CALLBACK_FUNC", "NOTES", "DETAIL_PAGE_URL", "DISCOUNT_PRICE", "ORDER_CALLBACK_FUNC", "CANCEL_CALLBACK_FUNC", "PAY_CALLBACK_FUNC", "CATALOG_XML_ID", "PRODUCT_XML_ID", "VAT_RATE")
					);
				while ($arBasket = $dbBasket->Fetch())
				{
					$ind++;
					$str_BASKET_PRODUCT_ID = htmlspecialchars($arBasket["PRODUCT_ID"], ENT_QUOTES);
					$str_BASKET_PRODUCT_PRICE_ID = htmlspecialchars($arBasket["PRODUCT_PRICE_ID"], ENT_QUOTES);
					$str_BASKET_PRICE = htmlspecialchars($arBasket["PRICE"], ENT_QUOTES);
					$str_BASKET_CURRENCY = htmlspecialchars($arBasket["CURRENCY"], ENT_QUOTES);
					$str_BASKET_WEIGHT = htmlspecialchars($arBasket["WEIGHT"], ENT_QUOTES);
					$str_BASKET_QUANTITY = htmlspecialchars($arBasket["QUANTITY"], ENT_QUOTES);
					//$str_BASKET_NAME = htmlspecialchars($arBasket["NAME"], ENT_QUOTES);
					$str_BASKET_NAME = CUtil::JSEscape($arBasket["NAME"]);
					$str_BASKET_MODULE = htmlspecialchars($arBasket["MODULE"], ENT_QUOTES);
					$str_BASKET_NOTES = htmlspecialchars($arBasket["NOTES"], ENT_QUOTES);
					$str_BASKET_DETAIL_PAGE_URL = htmlspecialchars($arBasket["DETAIL_PAGE_URL"], ENT_QUOTES);
					$str_BASKET_DISCOUNT_PRICE = htmlspecialchars($arBasket["DISCOUNT_PRICE"], ENT_QUOTES);
					$str_BASKET_CALLBACK_FUNC = htmlspecialchars($arBasket["CALLBACK_FUNC"], ENT_QUOTES);
					$str_BASKET_ORDER_CALLBACK_FUNC = htmlspecialchars($arBasket["ORDER_CALLBACK_FUNC"], ENT_QUOTES);
					$str_BASKET_CANCEL_CALLBACK_FUNC = htmlspecialchars($arBasket["CANCEL_CALLBACK_FUNC"], ENT_QUOTES);
					$str_BASKET_PAY_CALLBACK_FUNC = htmlspecialchars($arBasket["PAY_CALLBACK_FUNC"], ENT_QUOTES);
					$str_CATALOG_XML_ID = htmlspecialchars($arBasket["CATALOG_XML_ID"], ENT_QUOTES);
					$str_PRODUCT_XML_ID = htmlspecialchars($arBasket["PRODUCT_XML_ID"], ENT_QUOTES);
					$str_BASKET_DELETE = "N";
					$str_BASKET_VAT_RATE = htmlspecialchars($arBasket["VAT_RATE"], ENT_QUOTES);

					if ($bVarsFromForm)
					{
						$str_BASKET_PRODUCT_ID = htmlspecialchars(${"BASKET_PRODUCT_ID_".$ind}, ENT_QUOTES);
						$str_BASKET_PRICE = htmlspecialchars(${"BASKET_PRICE_".$ind}, ENT_QUOTES);
						$str_BASKET_CURRENCY = htmlspecialchars(${"BASKET_CURRENCY_".$ind}, ENT_QUOTES);
						$str_BASKET_WEIGHT = htmlspecialchars(${"BASKET_WEIGHT_".$ind}, ENT_QUOTES);
						$str_BASKET_QUANTITY = htmlspecialchars(${"BASKET_QUANTITY_".$ind}, ENT_QUOTES);
						//$str_BASKET_NAME = htmlspecialchars(${"BASKET_NAME_".$ind}, ENT_QUOTES);
						$str_BASKET_NAME = CUtil::JSEscape(${"BASKET_NAME_".$ind});
						$str_BASKET_MODULE = htmlspecialchars(${"BASKET_MODULE_".$ind}, ENT_QUOTES);
						$str_BASKET_NOTES = htmlspecialchars(${"BASKET_NOTES_".$ind}, ENT_QUOTES);
						$str_BASKET_DETAIL_PAGE_URL = htmlspecialchars(${"BASKET_DETAIL_PAGE_URL_".$ind}, ENT_QUOTES);
						$str_BASKET_DISCOUNT_PRICE = htmlspecialchars(${"BASKET_DISCOUNT_PRICE_".$ind}, ENT_QUOTES);
						$str_BASKET_CALLBACK_FUNC = htmlspecialchars(${"BASKET_CALLBACK_FUNC_".$ind}, ENT_QUOTES);
						$str_BASKET_ORDER_CALLBACK_FUNC = htmlspecialchars(${"BASKET_ORDER_CALLBACK_FUNC_".$ind}, ENT_QUOTES);
						$str_BASKET_CANCEL_CALLBACK_FUNC = htmlspecialchars(${"BASKET_CANCEL_CALLBACK_FUNC_".$ind}, ENT_QUOTES);
						$str_BASKET_PAY_CALLBACK_FUNC = htmlspecialchars(${"BASKET_PAY_CALLBACK_FUNC_".$ind}, ENT_QUOTES);
						$str_CATALOG_XML_ID = htmlspecialchars(${"BASKET_CATALOG_XML_ID_".$ind}, ENT_QUOTES);
						$str_PRODUCT_XML_ID = htmlspecialchars(${"BASKET_PRODUCT_XML_ID_".$ind}, ENT_QUOTES);
						$str_BASKET_DELETE = ((${"BASKET_DELETE_".$ind} == "Y") ? "Y" : "N");
						$str_BASKET_VAT_RATE = htmlspecialchars(${"BASKET_VAT_RATE_".$ind}, ENT_QUOTES);
					}

					$jnd = -1;
					$basketProps = "";
					$dbBasketProps = CSaleBasket::GetPropsList(
							array("SORT" => "ASC"),
							array("BASKET_ID" => $arBasket["ID"]),
							false,
							false,
							array("ID", "BASKET_ID", "NAME", "VALUE", "CODE", "SORT")
						);
					while ($arBasketProps = $dbBasketProps->Fetch())
					{
						$jnd++;

						$str_BASKET_PROP_NAME = htmlspecialchars($arBasketProps["NAME"], ENT_QUOTES);
						$str_BASKET_PROP_VALUE = htmlspecialchars($arBasketProps["VALUE"], ENT_QUOTES);
						$str_BASKET_PROP_CODE = htmlspecialchars($arBasketProps["CODE"], ENT_QUOTES);
						$str_BASKET_PROP_SORT = htmlspecialchars($arBasketProps["SORT"], ENT_QUOTES);

						if ($bVarsFromForm)
						{
							$str_BASKET_PROP_NAME = htmlspecialchars(${"BASKET_PROP_NAME_".$ind ."_".$jnd}, ENT_QUOTES);
							$str_BASKET_PROP_VALUE = htmlspecialchars(${"BASKET_PROP_VALUE_".$ind ."_".$jnd}, ENT_QUOTES);
							$str_BASKET_PROP_CODE = htmlspecialchars(${"BASKET_PROP_CODE_".$ind ."_".$jnd}, ENT_QUOTES);
							$str_BASKET_PROP_SORT = htmlspecialchars(${"BASKET_PROP_SORT_".$ind ."_".$jnd}, ENT_QUOTES);
						}

						if (strlen($basketProps) > 0)
							$basketProps .= ",";
						$basketProps .= "['".$str_BASKET_PROP_NAME."', '".$str_BASKET_PROP_VALUE."', '".$str_BASKET_PROP_CODE."', ".IntVal($str_BASKET_PROP_SORT).", ".$arBasketProps["ID"]."]";
					}

					if ($bVarsFromForm && $jnd < IntVal(${"BASKET_PROP_COUNTER_".$ind}))
					{
						for ($i = $jnd + 1; $i <= IntVal(${"BASKET_PROP_COUNTER_".$ind}); $i++)
						{
							$jnd++;

							$str_BASKET_PROP_NAME = htmlspecialchars(${"BASKET_PROP_NAME_".$ind ."_".$jnd}, ENT_QUOTES);
							$str_BASKET_PROP_VALUE = htmlspecialchars(${"BASKET_PROP_VALUE_".$ind ."_".$jnd}, ENT_QUOTES);
							$str_BASKET_PROP_CODE = htmlspecialchars(${"BASKET_PROP_CODE_".$ind ."_".$jnd}, ENT_QUOTES);
							$str_BASKET_PROP_SORT = htmlspecialchars(${"BASKET_PROP_SORT_".$ind ."_".$jnd}, ENT_QUOTES);

							if (strlen($basketProps) > 0)
								$basketProps .= ",";
							$basketProps .= "['".$str_BASKET_PROP_NAME."', '".$str_BASKET_PROP_VALUE."', '".$str_BASKET_PROP_CODE."', ".IntVal($str_BASKET_PROP_SORT).", 0]";
						}
					}

					$basketProps = "[".$basketProps."]";
					?>
					<script language="JavaScript">
					<!--
					var ind = BasketAddNewProduct('<?= $str_BASKET_MODULE ?>', '<?= $str_BASKET_PRODUCT_ID ?>', '<?= $str_BASKET_NAME ?>', '<?= $str_BASKET_DETAIL_PAGE_URL ?>', <?= $basketProps ?>, '<?= $str_BASKET_PRICE ?>', '<?= $str_BASKET_CURRENCY ?>', '<?= $str_BASKET_DISCOUNT_PRICE ?>', '<?= $str_BASKET_WEIGHT ?>', '<?= $str_BASKET_QUANTITY ?>', '<?= $str_BASKET_NOTES ?>', '<?= $str_BASKET_CALLBACK_FUNC ?>', '<?= $str_BASKET_ORDER_CALLBACK_FUNC ?>', '<?= $str_BASKET_CANCEL_CALLBACK_FUNC ?>', '<?= $str_BASKET_PAY_CALLBACK_FUNC ?>', '<?= $str_CATALOG_XML_ID ?>', '<?= $str_PRODUCT_XML_ID ?>', <?= IntVal($arBasket["ID"]) ?>, '<?= $str_BASKET_PRODUCT_PRICE_ID ?>', '<?=$str_BASKET_VAT_RATE?>');
					BasketShowProduct(ind, 2);
					//-->
					</script>
					<?
				}
			}
			if($ind < 1)
			{
				?>
				<script>
				<!--
				if(chFullDiv = document.getElementById('MOVE2NEW_ORDER_0'))
					chFullDiv.disabled = true;
				//-->
				</script>
				<?
			}
			?>
			<tr>
				<td valign="top" align="center" colspan="2">
					<input type="button" value="<?= GetMessage("SOE_MORE_ITEMS") ?>" OnClick="if(editedRecord > 0) BasketSaveProduct(editedRecord); var ind = BasketAddNewProduct();  BasketShowProduct(ind, 2); BasketShowProduct(ind, 1);">
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<img src="/bitrix/images/1.gif" width="1" height="8">
				</td>
			</tr>

			<tr class="heading">
				<td colspan="2"><?= GetMessage("SOE_TAXES") ?></td>
			</tr>
			<tr id="ID_TAX_SECTION">
				<td colspan="2">
					<script language="JavaScript">
					<!--
					function CloneTaxSection()
					{
						var oTbl = document.getElementById("TAX_TABLE");
						if (!oTbl)
							return;

						var oCntr = document.getElementById("TAX_COUNTER");
						var cnt = parseInt(oCntr.value);
						cnt = cnt + 1;

						var oRow = oTbl.insertRow(oTbl.rows.length - 1);
						var oCell = oRow.insertCell(-1);
						oCell.innerHTML = '<input type="text" name="TAX_NAME_' + cnt + '" size="20" maxlength="250" value="">';

						var oCell = oRow.insertCell(-1);
						oCell.innerHTML = '<input type="text" name="TAX_VALUE_' + cnt + '" size="5" maxlength="250" value="">';

						var oCell = oRow.insertCell(-1);
						oCell.innerHTML = '<input type="text" name="TAX_VALUE_MONEY_' + cnt + '" size="5" disabled maxlength="10" value="">';

						var oCell = oRow.insertCell(-1);
						oCell.innerHTML = '<input type="text" name="TAX_APPLY_ORDER_' + cnt + '" size="5" maxlength="10" value="">';

						var oCell = oRow.insertCell(-1);
						oCell.innerHTML = '<input type="text" name="TAX_CODE_' + cnt + '" size="10" maxlength="250" value="">';

						var oCell = oRow.insertCell(-1);
						oCell.align = "center";
						oCell.innerHTML = '<input type="checkbox" name="TAX_IS_IN_PRICE_' + cnt + '" value="Y">';

						oCntr.value = cnt;
					}
					//-->
					</script>
					<table cellpadding="3" cellspacing="1" border="0" width="100%" id="TAX_TABLE" class="internal">
						<tr class="heading">
							<td>
								<span class="required">*</span><?= GetMessage("SOE_TAX_NAME") ?>
							</td>
							<td>
								<span class="required">*</span><?= GetMessage("SOE_TAX_VALUE") ?>
							</td>
							<td>
								<?= GetMessage("SOE_TAX_SUM") ?>
							</td>
							<td>
								<?= GetMessage("SOE_TAX_SORT") ?>
							</td>
							<td>
								<?= GetMessage("SOE_TAX_CODE") ?>
							</td>
							<td>
								<?= GetMessage("SOE_TAX_IN_PRICE") ?>
							</td>
						</tr>
						<?
						$dbTaxList = CSaleOrderTax::GetList(
								array("APPLY_ORDER" => "ASC"),
								array("ORDER_ID" => $ID),
								false,
								false,
								array("*")
							);
						$ind = -1;
						while ($arTaxList = $dbTaxList->Fetch())
						{
							$ind++;

							$str_TAX_NAME = htmlspecialchars($arTaxList["TAX_NAME"]);
							$str_TAX_VALUE = htmlspecialchars($arTaxList["VALUE"]);
							$str_TAX_VALUE_MONEY = htmlspecialchars($arTaxList["VALUE_MONEY"]);
							$str_TAX_APPLY_ORDER = htmlspecialchars($arTaxList["APPLY_ORDER"]);
							$str_TAX_CODE = htmlspecialchars($arTaxList["CODE"]);
							$str_TAX_IS_IN_PRICE = htmlspecialchars($arTaxList["IS_IN_PRICE"]);

							if ($bVarsFromForm)
							{
								$str_TAX_NAME = htmlspecialchars(${"TAX_NAME_".$ind});
								$str_TAX_VALUE = htmlspecialchars(${"TAX_VALUE_".$ind});
								$str_TAX_VALUE_MONEY = htmlspecialchars(${"TAX_VALUE_MONEY_".$ind});
								$str_TAX_APPLY_ORDER = htmlspecialchars(${"TAX_APPLY_ORDER_".$ind});
								$str_TAX_CODE = htmlspecialchars(${"TAX_CODE_".$ind});
								$str_TAX_IS_IN_PRICE = htmlspecialchars(${"TAX_IS_IN_PRICE_".$ind});
							}
							?>
							<tr>
								<td>
									<input type="hidden" name="TAX_ID_<?= $ind ?>" value="<?= $arTaxList["ID"] ?>">
									<input type="text" name="TAX_NAME_<?= $ind ?>" size="20" maxlength="250" value="<?= $str_TAX_NAME ?>">
								</td>
								<td>
									<input type="text" name="TAX_VALUE_<?= $ind ?>" size="5" maxlength="250" value="<?= $str_TAX_VALUE ?>">
								</td>
								<td>
									<input type="text" name="TAX_VALUE_MONEY_<?= $ind ?>" disabled size="5" maxlength="10" value="<?= $str_TAX_VALUE_MONEY ?>">
								</td>
								<td>
									<input type="text" name="TAX_APPLY_ORDER_<?= $ind ?>" size="5" maxlength="10" value="<?= $str_TAX_APPLY_ORDER ?>">
								</td>
								<td>
									<input type="text" name="TAX_CODE_<?= $ind ?>" size="10" maxlength="250" value="<?= $str_TAX_CODE ?>">
								</td>
								<td align="center">
									<input type="checkbox" name="TAX_IS_IN_PRICE_<?= $ind ?>" value="Y"<?if ($str_TAX_IS_IN_PRICE == "Y") echo " checked";?>>
								</td>
							</tr>
							<?
						}

						if ($bVarsFromForm && $ind < IntVal($TAX_COUNTER))
						{
							for ($i = $ind + 1; $i <= IntVal($TAX_COUNTER); $i++)
							{
								$ind++;

								$str_TAX_NAME = htmlspecialchars(${"TAX_NAME_".$ind});
								$str_TAX_VALUE = htmlspecialchars(${"TAX_VALUE_".$ind});
								$str_TAX_VALUE_MONEY = htmlspecialchars(${"TAX_VALUE_MONEY_".$ind});
								$str_TAX_APPLY_ORDER = htmlspecialchars(${"TAX_APPLY_ORDER_".$ind});
								$str_TAX_CODE = htmlspecialchars(${"TAX_CODE_".$ind});
								$str_TAX_IS_IN_PRICE = htmlspecialchars(${"TAX_IS_IN_PRICE_".$ind});
								?>
								<tr>
									<td>
										<input type="hidden" name="TAX_ID_<?= $ind ?>" value="0">
										<input type="text" name="TAX_NAME_<?= $ind ?>" size="20" maxlength="250" value="<?= $str_TAX_NAME ?>">
									</td>
									<td>
										<input type="text" name="TAX_VALUE_<?= $ind ?>" size="5" maxlength="250" value="<?= $str_TAX_VALUE ?>">
									</td>
									<td>
										<input type="text" name="TAX_VALUE_MONEY_<?= $ind ?>" size="5" disabled maxlength="10" value="<?= $str_TAX_VALUE_MONEY ?>">
									</td>
									<td>
										<input type="text" name="TAX_APPLY_ORDER_<?= $ind ?>" size="5" maxlength="10" value="<?= $str_TAX_APPLY_ORDER ?>">
									</td>
									<td>
										<input type="text" name="TAX_CODE_<?= $ind ?>" size="10" maxlength="250" value="<?= $str_TAX_CODE ?>">
									</td>
									<td align="center">
										<input type="checkbox" name="TAX_IS_IN_PRICE_<?= $ind ?>" value="Y"<?if ($str_TAX_IS_IN_PRICE == "Y") echo " checked";?>>
									</td>
								</tr>
								<?
							}
						}

						if ($ind == -1)
						{
							$ind++;
							?>
							<tr>
								<td>
									<input type="hidden" name="TAX_ID_<?= $ind ?>" value="0">
									<input type="text" name="TAX_NAME_<?= $ind ?>" size="20" maxlength="250" value="<?= $str_TAX_NAME ?>">
								</td>
								<td>
									<input type="text" name="TAX_VALUE_<?= $ind ?>" size="5" maxlength="250" value="<?= $str_TAX_VALUE ?>">
								</td>
								<td>
									<input type="text" name="TAX_VALUE_MONEY_<?= $ind ?>" size="5" disabled maxlength="10" value="<?= $str_TAX_VALUE_MONEY ?>">
								</td>
								<td>
									<input type="text" name="TAX_APPLY_ORDER_<?= $ind ?>" size="5" maxlength="10" value="<?= $str_TAX_APPLY_ORDER ?>">
								</td>
								<td>
									<input type="text" name="TAX_CODE_<?= $ind ?>" size="10" maxlength="250" value="<?= $str_TAX_CODE ?>">
								</td>
								<td align="center">
									<input type="checkbox" name="TAX_IS_IN_PRICE_<?= $ind ?>" value="Y"<?if ($str_TAX_IS_IN_PRICE == "Y") echo " checked";?>>
								</td>
							</tr>
							<?
						}
						?>
						<tr>
							<td colspan="6" align="center">
								<input type="hidden" name="TAX_COUNTER" id="TAX_COUNTER" value="<?= $ind ?>">
								<input type="button" name="tax_clonner" value="<?= GetMessage("SOE_TAX_MORE") ?>" OnClick="CloneTaxSection()">
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<img src="/bitrix/images/1.gif" width="1" height="8">
				</td>
			</tr>

		<?
		$tabControl->BeginNextTab();
		?>

			<tr class="heading">
				<td colspan="2"><?= GetMessage("SOE_ADDITIONAL") ?></td>
			</tr>
			<tr>
				<td width="40%" valign="top"><?= GetMessage("SOE_ADDITIONAL") ?>:</td>
				<td width="60%" valign="top">
					<textarea name="ADDITIONAL_INFO" rows="2" cols="40"><?= $str_ADDITIONAL_INFO ?></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<img src="/bitrix/images/1.gif" width="1" height="8">
				</td>
			</tr>

			<tr class="heading">
				<td colspan="2"><?= GetMessage("SOE_COMMENT") ?></td>
			</tr>
			<tr>
				<td width="40%" valign="top">
					<?= GetMessage("SOE_COMMENT") ?>:<br><small><?= GetMessage("SOE_COMMENT_NOTE") ?></small>
				</td>
				<td width="60%" valign="top">
					<textarea name="COMMENTS" rows="3" cols="60"><?= $str_COMMENTS ?></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<img src="/bitrix/images/1.gif" width="1" height="8">
				</td>
			</tr>

		<?
		$tabControl->EndTab();
		?>

		<?
		$tabControl->Buttons(false);
		?>

		<script language="JavaScript">
		<!--
		function ReCountClicked(val)
		{
			var bCanChange = true;
			if (val && document.forder_edit.RE_COUNT.checked)
			{
				bCanChange = false;
				if (confirm('<?= GetMessage("SOE_RECOUNT_CONF") ?>'))
					bCanChange = true;
			}

			if (bCanChange)
			{
				if (document.forder_edit.PRICE_DELIVERY)
				{
					document.forder_edit.PRICE_DELIVERY.disabled = document.forder_edit.RE_COUNT.checked;
				}
				
				var oTaxSect = document.getElementById("ID_TAX_SECTION");
				oTaxSect.disabled = document.forder_edit.RE_COUNT.checked;

				var oCntr = document.getElementById("BASKET_COUNTER");
				var cnt = parseInt(oCntr.value);

				for (var i = 0; i <= cnt; i++)
				{
					var fld = document.getElementById("ID_BASKET_DISCOUNT_PRICE1_" + i);
					if (fld)
						fld.disabled = document.forder_edit.RE_COUNT.checked;
				}

				var oCntr = document.getElementById("TAX_COUNTER");
				var cnt = parseInt(oCntr.value);

				for (var i = 0; i <= cnt; i++)
				{
					/*
					eval("document.forder_edit.TAX_NAME_" + i + ".disabled = document.forder_edit.RE_COUNT.checked;");
					eval("document.forder_edit.TAX_VALUE_" + i + ".disabled = document.forder_edit.RE_COUNT.checked;");
					eval("document.forder_edit.TAX_APPLY_ORDER_" + i + ".disabled = document.forder_edit.RE_COUNT.checked;");
					eval("document.forder_edit.TAX_CODE_" + i + ".disabled = document.forder_edit.RE_COUNT.checked;");
					eval("document.forder_edit.TAX_IS_IN_PRICE_" + i + ".disabled = document.forder_edit.RE_COUNT.checked;");
					*/
					document.forms.forder_edit['TAX_NAME_' + i].disabled = document.forder_edit.RE_COUNT.checked;
					document.forms.forder_edit['TAX_VALUE_' + i].disabled = document.forder_edit.RE_COUNT.checked;
					document.forms.forder_edit['TAX_APPLY_ORDER_' + i].disabled = document.forder_edit.RE_COUNT.checked;
					document.forms.forder_edit['TAX_CODE_' + i].disabled = document.forder_edit.RE_COUNT.checked;
					document.forms.forder_edit['TAX_IS_IN_PRICE_' + i].disabled = document.forder_edit.RE_COUNT.checked;
				}

				document.forder_edit.tax_clonner.disabled = document.forder_edit.RE_COUNT.checked;
			}
			else
			{
				document.forder_edit.RE_COUNT.checked = !document.forder_edit.RE_COUNT.checked;
			}
		}
		//-->
		</script>

		<table>
		<tr>
			<td>
				<small><input type="checkbox" name="RE_COUNT" id="ID_RE_COUNT" value="Y" OnClick="ReCountClicked(true)"<?if ($str_RECOUNT_FLAG == "Y") echo " checked";?>> <label for="ID_RE_COUNT"><?= GetMessage("SOE_RECOUNT_ORDER") ?><sup>2)<sup></label></small>
			</td>
		</tr>
		<tr>
			<td id="ID_FULL_DIVISION_TD">
				<small><input type="checkbox" name="FULL_DIVISION" id="ID_FULL_DIVISION" value="Y" <?if (isset($FULL_DIVISION) && ($FULL_DIVISION == "Y")) echo " checked";?>> <label for="ID_FULL_DIVISION"><?= GetMessage("SOE_RECOUNT_SUBORDERS") ?><sup>3)<sup></label></small>
			</td>
		</tr>
		<tr>
			<td id="ID_COUNT_TAX_FOR_DELIVERY_TD">
				<small><input type="checkbox" name="COUNT_TAX_FOR_DELIVERY" id="ID_COUNT_TAX_FOR_DELIVERY" value="Y" <?if (isset($COUNT_TAX_FOR_DELIVERY) && ($COUNT_TAX_FOR_DELIVERY == "Y")) echo " checked";?>> <label for="ID_COUNT_TAX_FOR_DELIVERY"><?= GetMessage("SOE_COUNT_TAX_FOR_DELIVERY") ?></label></small>
			</td>
		</tr>
		</table>
		<br>

		<input<?= ($bUserCanEditOrder && ($str_LOCK_STATUS != "red")) ? "" : " disabled" ?> type="submit" name="save" value="<?= GetMessage("SOE_BUT_SAVE") ?>" title="<?= GetMessage("SOE_BUT_SAVE_ALT") ?>">
		<input<?= ($bUserCanEditOrder && ($str_LOCK_STATUS != "red")) ? "" : " disabled" ?> type="submit" name="apply" value="<?= GetMessage("SOE_BUT_APPLY") ?>" title="<?= GetMessage("SOE_BUT_APPLY_ALT") ?>">
		<input<?= ($bUserCanEditOrder && ($str_LOCK_STATUS != "red")) ? "" : " disabled" ?> type="submit" name="dontsave" value="<?= GetMessage("SOE_BUT_CANCEL") ?>" title="<?= GetMessage("SOE_BUT_CANCEL_ALT") ?>">

		<?
		$tabControl->End();
		?>

		</form>
		<script language="JavaScript">
		<!--
		PayedClicked();
		ReCountClicked(false);
		CheckFullDivision();
		//-->
		</script>
		<?
	}		// if (strlen($customOrderView) > 0 ...
}
?>

<br>
<?echo BeginNote();?>
<span class="required">*</span><font class="legendtext"> - <?echo GetMessage("REQUIRED_FIELDS")?><br>
1) - <?echo GetMessage("SOE_ORDER_DIVIDE_HINT")?><br>
2) - <?echo GetMessage("SOE_ORDER_RECOUNT_HINT")?><br>
3) - <?echo GetMessage("SOE_ORDER_FULL_DIVIDE_HINT")?><br>
<?echo EndNote(); ?>

<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>