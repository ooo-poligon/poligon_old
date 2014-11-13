<?global $APPLICATION;
/*Добавление в корзину*/
if ($_REQUEST["action"]=="ADD2BASKET"&&intval($_REQUEST["id"])):
if(CModule::IncludeModule("iblock") && CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
		{
			if(Add2BasketByProductID(intval($_REQUEST["id"])))
			{
				LocalRedirect($APPLICATION->GetCurPageParam("", array("id", "action")));
			}
			else
			{
				if($ex = $GLOBALS["APPLICATION"]->GetException())
					$strError = $ex->GetString();
				else
					$strError = GetMessage("CATALOG_ERROR2BASKET").".ошибка";
			}
		}
endif;
/**/


if(CModule::IncludeModule("iblock")):
$body = '';
$i=0;
foreach($arResult["SEARCH"] as $arItem)
	{
	if ($i%2==1)  $st = 'class="grey"'; else $st='';

	if ($arItem["~PARAM1"]=='catalog'):		
		/*echo '<pre>';		
		var_dump($arItem);
		echo '</pre>';		*/
		$body = '';
		$arSelect = Array();
		$arFilter = Array("IBLOCK_ID"=>4, "ID"=>$arItem["ITEM_ID"], "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
		$res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
		if($obElement = $res->GetNextElement())
		{
			$arElement = $obElement->GetFields();
			$arElement["PROPERTIES"] = $obElement->GetProperties();
			if ($arElement["PROPERTIES"]["SPEC"]["VALUE"]==1) $st = 'class="spec"';
			if(CModule::IncludeModule("catalog"))
			{
//				$arResult["PRICES"] = CIBlockPriceTools::GetCatalogPrices(4, 'EUR');
				$arElement["PRICES"] = CPrice::GetBasePrice($arElement["ID"]);
			}
			$arElement["ADD_URL"] = htmlspecialchars($APPLICATION->GetCurPageParam("action=ADD2BASKET&id=".$arElement["ID"], array("id","action")));
		?>
<?$body .='<tr '.$st.'><td align="left" valign="top"><a href="'.$arElement['DETAIL_PAGE_URL'].'"><b>'.$arElement["NAME"];
if ($arElement["PROPERTIES"]["article"]["VALUE"])
$body .= ' ('.$arElement["PROPERTIES"]["article"]["VALUE"].')</b></a>';
else $body .= '</b></a>';
$body .= '<br>'.$arElement["PREVIEW_TEXT"].'</td>';
			$body .= '<td align="center">'.$arElement["PROPERTIES"]["producer_full"]["VALUE"].'&nbsp;</td><td align="center">';?>
				<?if($arElement["PROPERTIES"]["pdf"]["VALUE"]){
				$body .= '<a href="/PDF/'.$arElement['PROPERTIES']['pdf']['VALUE'].'"><img src="/images/pdf_doc.gif"></a>';
				} else $body .= '&nbsp;';?>				
			<?$body .= '</td><td align="center">';?>
				<?$db_res = CCatalogProduct::GetList(
					array(),
					array("ID" => $arElement["ID"]),
					false,
					array()
				    );
				if ($ar_res = $db_res->Fetch())
				{
				    if (!$ar_res["QUANTITY"]){
					if (!$arElement["PROPERTIES"]["srok"]["VALUE"])
						$body .=  '<img src="/images/grey.gif" alt="Нет данных" title="Нет данных">';
					else $body .=  $arElement["PROPERTIES"]["srok"]["VALUE"]; 					
					}
				    else $body .=  '<img src="/images/green.gif" alt="Есть на складе" title="Есть на складе">';
				}
			$body .= '</td>
			<td align="center"><span style="color:black">&nbsp;'.$arElement["PRICES"]["PRICE"].'&nbsp;'.$arElement["PRICES"]["CURRENCY"].'</span></td>
			<td width="30">
			<center>
				<a href="javascript:void(0)" onclick="run('.$arElement['ID'].')"><img src="/bitrix/templates/poligon/images/basket.gif"></a>
			</center>
			</td>	</tr>';?>
<?		
		}
		$arResult["SEARCH"][$i]["TEXT1"] = $body;
		//var_dump($arResult["SEARCH"]);
	endif;
		$i++;

	}
//$body .=  '</table>';
//echo $body;
//var_dump($arResult);
endif;
?>
