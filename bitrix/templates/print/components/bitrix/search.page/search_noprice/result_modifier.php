<?global $APPLICATION;
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
		?>
<?$body .='<tr '.$st.'><td align="left" valign="top"><a href="'.$arElement['DETAIL_PAGE_URL'].'"><b>'.$arElement["NAME"];
if ($arElement["PROPERTIES"]["article"]["VALUE"])
$body .= ' ('.$arElement["PROPERTIES"]["article"]["VALUE"].')</b></a>';
else $body .= '</b></a>';
$body .= '<br>'.$arElement["PREVIEW_TEXT"].'</td>';
			$body .= '<td align="center">'.$arElement["PROPERTIES"]["producer_full"]["VALUE"].'&nbsp;</td><td align="center">';?>
				<?if($arElement["PROPERTIES"]["pdf"]["VALUE"]){
				$body .= '<a href="/pdf/'.$arElement['PROPERTIES']['pdf']['VALUE'].'"><img src="/images/pdf_doc.gif"></a>';
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
			$body .= '</td></tr>';?>
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
