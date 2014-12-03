<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>


<div id="catalog-section-list">
<?

// ������������ ��� �������-���������� (see parseForDynamicContent in /function.php)
//ob_start("parseForDynamicContent");



$rsSect1 = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>4, "ID"=>intval($_REQUEST["SECTION_ID"])), false, array("UF_PROIZV"));
		if ($arSect1 = $rsSect1->GetNext())
		{ 
			$proizv = $arSect1["UF_PROIZV"];
		}
		
/**
* ��������! ����� ������� �������. ��� ������ �������� ��������� �� ����� ����� ������ 
* bitrix/templates/poligon_ew/components/bitrix/catalog/catalog_noprice/bitrix/catalog.section.list/.default/template.php
* � ������ �������, ��������� �� �������� ��������, �.�. ����������� ������ ��������� �������
* ��� ������ �������� �������� �� �������. ���������������� ������� UF_WOD 
* ����������� � �������� ��������, �������� ������� ���� �������� �� ����� ��������, 
* ��� ������ � ��������, ������ ��������.
* ��������: ���������� ����� - 13 - ������������� ������� ����������� ����������. 
*/
// �����, ���� �� ����� � ���, ��� �������� �� �������� ������� (��� ����������� ������������)
$sectionResult = CIBlockSection::GetList(array("SORT" => "ASC"), array("IBLOCK_ID" => 4, "ID" => $_REQUEST["SECTION_ID"]), false, $arSelect = array("UF_WOD"));
$sectionProp = $sectionResult -> GetNext();


// ���� ���� �����, ������ ��� ��������� �������� �� �������� � ��������� ����. ������������
if($sectionProp["UF_WOD"] == 1 && $_REQUEST["SECTION_ID"] != 13)
{
print '<!-- test';
//var_dump($sectionProp['DESCRIPTION']);
print ' --> ';
print $sectionProp['DESCRIPTION'];
//$sectionProp
/**
	��� �������� �������� �� ������ � ��������� ������������ ��������
	��� � ����� �������� ������������ � ����, ����� ������ �������. 
*/
// ����������, ���� ������������ ������� ��� ������/������
	$fileContent = null; 
// ���� � �����
	$cacheFile = "{$_SERVER['DOCUMENT_ROOT']}/tmp/{$_REQUEST["SECTION_ID"]}.htm";
	// ���� ���� ���������� � �� �� ������ �����, �� ������ ����� ��� ����������
	if(file_exists($cacheFile) && (time()- filemtime($_SERVER['SCRIPT_FILENAME']))/(60*60) < 24 )
	{
		require_once $cacheFile;	
	}
	else  // ����� ������ � ������. 
	{
		// ������� ������ ����
		if(file_exists($cacheFile))
			unlink($cacheFile);
		
		// ������� ������ �������� �� ��������
		$alphaSectionsArr = GetIBlockSectionList(4, $_GET['SECTION_ID'], Array("SORT"=>"ASC"));
		
		while($alphaSection = $alphaSectionsArr->GetNext())
		{
			$alphaElements = GetIBlockElementList(4, $alphaSection["ID"]);
			$fileContent .= '<a id="'.$alphaSection['NAME'].'"><b style="display: block; width: 100%;">'.$alphaSection['NAME'].'</b></a>';
			$i = 0; $points = 12;
			while($element = $alphaElements->GetNext())
			{
				if($i == 0)
					$fileContent .= '<ul class="ec">';
				$i++;
				$fileContent .= "<li>{$element['NAME']}</li>";
				if($i == $points)
				{
					$fileContent .= "</ul>";
					$i = 0;				
				}
			}			
			$fileContent .= '</ul><hr style="width: 100%; clear: both;"/>';	
		}
		print $fileContent;
		print '<!-- from mysql -->';
		$fileContent .= '<!-- from file -->';
		$filePut = file_put_contents($cacheFile, $fileContent);
		print '<!-- filePut: '.$filePut.' -->';
	}
}

$res = CIBlockSection::GetByID($arResult["SECTION"]["IBLOCK_SECTION_ID"]);
if($ar_res = $res->GetNext())
{
	$section_name = $ar_res['NAME'];
        $APPLICATION->SetTitle($section_name.' > '.$arResult["SECTION"]["NAME"].' '.$proizv);
}
else
{
        $APPLICATION->SetTitle($arResult["SECTION"]["NAME"]);	
}
?>
<?if ($_REQUEST["SECTION_ID"]&&$arResult["SECTION"]["DESCRIPTION"]) echo $arResult["SECTION"]["DESCRIPTION"].'<!-- hr style="text-align:center; width:50%; color:#CCC" -->'?>

<?


		
/**
*	��������, ��� �������! 
*	���� ������������� ����� ������ ��������� � ������, ������������ ���������� �����, ������� �������� ������������� �������� ������� ������
*	12/10/2011 ������� ������
*
**/
if($_REQUEST["SECTION_ID"] == 77){
	
}	// ���� ����� ���, ������� ������ � ������� ��� ������ 
elseif($sectionProp["UF_WOD"] != 1)
{
?>
	<table class="section-list"><tr><td><ul>
	<?
	$i=0;
	$CURRENT_DEPTH=$arResult["SECTION"]["DEPTH_LEVEL"]+1;
	foreach($arResult["SECTIONS"] as $arSection):
		if($CURRENT_DEPTH<$arSection["DEPTH_LEVEL"])
			echo "<ul style='margin-bottom: 12px;'>";
		elseif($CURRENT_DEPTH>$arSection["DEPTH_LEVEL"])
			echo str_repeat("</ul>", $CURRENT_DEPTH - $arSection["DEPTH_LEVEL"]);
	$CURRENT_DEPTH = $arSection["DEPTH_LEVEL"];
	$img_alt='';
	$sec_preview='';
		if($arSection["DEPTH_LEVEL"]==$arResult["SECTION"]["DEPTH_LEVEL"]+1)
		{
			if ($i==3&&$arResult["SECTION"]["DEPTH_LEVEL"]<1) echo '</ul></td><td style="vertical-align: top"><ul>';
			$rsSect = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$arSection["IBLOCK_ID"], "ID"=>$arSection["ID"]), false, array("UF_*"));
			if ($arSect = $rsSect->GetNext())
			{ 
				$img_alt = $arSect["UF_SECT"];
				$sec_preview = $arSect["UF_SECT_PREVIEW"];
			}
			echo '<li><table><tr>';
			echo '<td><a href="'.$arSection["SECTION_PAGE_URL"].'"><b>'.$arSection["NAME"].'</b>';
			echo '</a><p><b>'.$sec_preview.'</b></p></td></tr></table></li>';
			$i++;
		?>
		  <?}else{?>
		<li><a href="<?=$arSection["SECTION_PAGE_URL"]?>"><?=$arSection["NAME"]?>
	<?if ($arResult["SECTION"]["DEPTH_LEVEL"]>=1)
	{
			$rsSect = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$arSection["IBLOCK_ID"], "ID"=>$arSection["ID"]), false, array("UF_*"));
			if ($arSect = $rsSect->GetNext())
			{ 
				$sec_preview = $arSect["UF_SECT_PREVIEW"];
			}
	}
	?>
	</a><?if ($sec_preview) echo ' � '.$sec_preview;?></li>
	<?}?>
	<?endforeach?>
	</ul></td></tr></table>
<?
} 
/**
 ��������� ����� ������� - ������������ ������ � ������� �� ������� ������, 
 ������ ����������� ������ �� �������� �������
*/
elseif($sectionProp["UF_WOD"] == 1 && $_REQUEST["SECTION_ID"] == 13)
{
?>
	<!-- it's work!! -->
	<ul>
	<?
	$i=0;
	$CURRENT_DEPTH=$arResult["SECTION"]["DEPTH_LEVEL"]+1;
	foreach($arResult["SECTIONS"] as $arSection):
		if($CURRENT_DEPTH<$arSection["DEPTH_LEVEL"])
			echo '<ul style="text-align: center; width: 75%;">';
		elseif($CURRENT_DEPTH>$arSection["DEPTH_LEVEL"])
			echo str_repeat("</ul><br>", $CURRENT_DEPTH - $arSection["DEPTH_LEVEL"]);
		$CURRENT_DEPTH = $arSection["DEPTH_LEVEL"];
	$img_alt='';
	$sec_preview='';
		if($arSection["DEPTH_LEVEL"]==$arResult["SECTION"]["DEPTH_LEVEL"]+1)
		{
			$sectionLink = $arSection["SECTION_PAGE_URL"];
			if ($i==3&&$arResult["SECTION"]["DEPTH_LEVEL"]<1) echo '</ul><ul>';
			$rsSect = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$arSection["IBLOCK_ID"], "ID"=>$arSection["ID"]), false, array("UF_*"));
			if ($arSect = $rsSect->GetNext())
			{ 
				$img_alt = $arSect["UF_SECT"];
				$sec_preview = $arSect["UF_SECT_PREVIEW"];
			}
					echo '<li>';
			echo '<a title="����������� ��� '.$arSection["NAME"].'" href='.$sectionLink.'><b>'.$arSection["NAME"].'</b></a> �� ��������: ';
			echo '<br/><b>'.$sec_preview.'</b></li>';
			$i++;
		}else{?>
		<li style="display: inline; "><a href="<?=$sectionLink?>#<?=$arSection["NAME"]?>"><?=$arSection["NAME"]?>
	<?if ($arResult["SECTION"]["DEPTH_LEVEL"]>=1)
	{
			$rsSect = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$arSection["IBLOCK_ID"], "ID"=>$arSection["ID"]), false, array("UF_*"));
			if ($arSect = $rsSect->GetNext())
			{ 
				$sec_preview = $arSect["UF_SECT_PREVIEW"];
			}
	}
	?>
	</a><?if ($sec_preview) echo '&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;&nbsp;'.$sec_preview;?></li>
	<?}?>
	<?endforeach?>
	</ul>

<?
}
// ����� ����������� ��� parseForDynamicContent();
//ob_end_flush();
?>
</div>