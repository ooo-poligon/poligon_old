<?global $APPLICATION;
		?>
<?$body .='<tr '.$st.'><td align="left" valign="top"><a href="'.$arElement['DETAIL_PAGE_URL'].'"><b>'.$arElement["NAME"].' ('.$arElement["PROPERTIES"]["article"]["VALUE"].')</b></a><br><br>'.$arElement["PREVIEW_TEXT"].'</td>';
			$body .= '<td align="center">'.$arElement["PROPERTIES"]["producer_full"]["VALUE"].'</td><td align="center">';?>
				<?if($arElement["PROPERTIES"]["pdf"]["VALUE"]){
				$body .= '<a href="'.$arElement['PROPERTIES']['pdf']['VALUE'].'"><img src="/images/pdf_doc.gif"></a>';
				}?>				
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
						$body .=  '<img src="/images/grey.gif" alt="��� ������" title="��� ������">';
					else $body .=  $arElement["PROPERTIES"]["srok"]["VALUE"]; 					
					}
				    else $body .=  '<img src="/images/green.gif" alt="���� �� ������" title="���� �� ������">';
				}
			$body .= '</td>
				</tr>';?>
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