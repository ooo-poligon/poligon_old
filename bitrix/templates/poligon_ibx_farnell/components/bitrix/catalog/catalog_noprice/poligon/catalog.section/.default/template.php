<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
				echo $arElement["DISPLAY_PROPERTIES"]["producer_full"]["DISPLAY_VALUE"];
			 	else echo '&nbsp;';?>
				<?$res = CIBlockSection::GetByID($arElement["IBLOCK_SECTION_ID"]);
					if($ar_res = $res->GetNext())
					{
					  $section_name = $ar_res['NAME'];
					  if ($ar_res["IBLOCK_SECTION_ID"])
					  {
	$rsSect1 = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$ar_res["IBLOCK_ID"], "ID"=>$ar_res["IBLOCK_SECTION_ID"]), false, array("NAME","UF_PROIZV"));
		if ($arSect1 = $rsSect1->GetNext())
			</td>
			<td align="center">
</table>
<?endif;?>