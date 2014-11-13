<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


$dbresult = $arResult["NAVIGATION_REF"];
$title = $arResult["NAVIGATION_TITLE"];
$show_allways = $arResult["SHOW_ALWAYS"];
$StyleText = "text";

$add_anchor = $dbresult->add_anchor;

$sBegin = GetMessage("nav_begin");
$sEnd = GetMessage("nav_end");
$sNext = GetMessage("nav_next");
$sPrev = GetMessage("nav_prev");
$sAll = GetMessage("nav_all");
$sPaged = GetMessage("nav_paged");

// окно, которое двигаем по страницам
$nPageWindow = $dbresult->nPageWindow;

if(!$show_allways)
{
	if ($dbresult->NavRecordCount == 0 || ($dbresult->NavPageCount == 1 && $dbresult->NavShowAll == false))
		return;
}

$sUrlPath = GetPagePath();

//global $HTTP_GET_VARS;
//print_r($HTTP_GET_VARS);

//Строка для формирования ссылки на следующие страницы навигации
$strNavQueryString = DeleteParam(array("PAGEN_".$dbresult->NavNum, "SIZEN_".$dbresult->NavNum, "SHOWALL_".$dbresult->NavNum, "PHPSESSID"));
if($strNavQueryString <> "")
	$strNavQueryString = htmlspecialchars("&".$strNavQueryString);

if($dbresult->bDescPageNumbering === true)
{
	if($dbresult->NavPageNomer + floor($nPageWindow/2) >= $dbresult->NavPageCount)
		$nStartPage = $dbresult->NavPageCount;
	else
	{
		if($dbresult->NavPageNomer + floor($nPageWindow/2) >= $nPageWindow)
			$nStartPage = $dbresult->NavPageNomer + floor($nPageWindow/2);
		else
		{
			if($dbresult->NavPageCount >= $nPageWindow)
				$nStartPage = $nPageWindow;
			else
				$nStartPage = $dbresult->NavPageCount;
		}
	}

	if($nStartPage - $nPageWindow >= 0)
		$nEndPage = $nStartPage - $nPageWindow + 1;
	else
		$nEndPage = 1;
	//echo "nEndPage = $nEndPage; nStartPage = $nStartPage;";
}
else
{
	// номер первой страницы в окне
	if($dbresult->NavPageNomer > floor($nPageWindow/2) + 1 && $dbresult->NavPageCount > $nPageWindow)
		$nStartPage = $dbresult->NavPageNomer - floor($nPageWindow/2);
	else
		$nStartPage = 1;

	// номер последней страницы в окне
	if($dbresult->NavPageNomer <= $dbresult->NavPageCount - floor($nPageWindow/2) && $nStartPage + $nPageWindow-1 <= $dbresult->NavPageCount)
		$nEndPage = $nStartPage + $nPageWindow - 1;
	else
	{
		$nEndPage = $dbresult->NavPageCount;
		if($nEndPage - $nPageWindow + 1 >= 1)
			$nStartPage = $nEndPage - $nPageWindow + 1;
	}
}

$dbresult->nStartPage = $nStartPage;
$dbresult->nEndPage = $nEndPage;

if($dbresult->bFirstPrintNav)
{
	$res .=  '<a name="nav_start'.$add_anchor.'"></a>';
	$dbresult->bFirstPrintNav = false;
}

$res .=  '<font class="'.$StyleText.'">'.$title.' ';
if($dbresult->bDescPageNumbering === true)
{
	$makeweight = ($dbresult->NavRecordCount % $dbresult->NavPageSize);
	$NavFirstRecordShow = 0;
	if($dbresult->NavPageNomer != $dbresult->NavPageCount)
		$NavFirstRecordShow += $makeweight;

	$NavFirstRecordShow += ($dbresult->NavPageCount - $dbresult->NavPageNomer) * $dbresult->NavPageSize + 1;
	$NavLastRecordShow = $makeweight + ($dbresult->NavPageCount - $dbresult->NavPageNomer + 1) * $dbresult->NavPageSize;

	$res .=  $NavFirstRecordShow;
	$res .=  ' - '.$NavLastRecordShow;
	$res .=  ' '.GetMessage("nav_of").' ';
	$res .=  $dbresult->NavRecordCount;
	$res .=  "\n<br>\n</font>";

	$res .=  '<font class="'.$StyleText.'">';

	if($dbresult->NavPageNomer < $dbresult->NavPageCount)
		$res .=  '<a href="'.$sUrlPath.'?PAGEN_'.$dbresult->NavNum.'='.$dbresult->NavPageCount.$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sBegin.'</a>&nbsp;|&nbsp;<a href="'.$sUrlPath.'?PAGEN_'.$dbresult->NavNum.'='.($dbresult->NavPageNomer+1).$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sPrev.'</a>';
	else
		$res .=  $sBegin.'&nbsp;|&nbsp;'.$sPrev;

	$res .=  '&nbsp;|&nbsp;';

	$NavRecordGroup = $nStartPage;
	while($NavRecordGroup >= $nEndPage)
	{
		$NavRecordGroupPrint = $dbresult->NavPageCount - $NavRecordGroup + 1;
		if($NavRecordGroup == $dbresult->NavPageNomer)
			$res .=  '<b>'.$NavRecordGroupPrint.'</b>&nbsp';
		else
			$res .=  '<a href="'.$sUrlPath.'?PAGEN_'.$dbresult->NavNum.'='.$NavRecordGroup.$strNavQueryString.'#nav_start'.$add_anchor.'">'.$NavRecordGroupPrint.'</a>&nbsp;';
		$NavRecordGroup--;
	}
	$res .=  '|&nbsp;';
	if($dbresult->NavPageNomer > 1)
		$res .=  '<a href="'.$sUrlPath.'?PAGEN_'.$dbresult->NavNum.'='.($dbresult->NavPageNomer-1).$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sNext.'</a>&nbsp;|&nbsp;<a href="'.$sUrlPath.'?PAGEN_'.$dbresult->NavNum.'=1'.$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sEnd.'</a>&nbsp;';
	else
		$res .=  $sNext.'&nbsp;|&nbsp;'.$sEnd.'&nbsp;';
}
else
{
	$res .=  ($dbresult->NavPageNomer-1)*$dbresult->NavPageSize+1;
	$res .=  ' - ';
	if($dbresult->NavPageNomer != $dbresult->NavPageCount)
		$res .=  $dbresult->NavPageNomer * $dbresult->NavPageSize;
	else
		$res .=  $dbresult->NavRecordCount;
	$res .=  ' '.GetMessage("nav_of").' ';
	$res .=  $dbresult->NavRecordCount;
	$res .=  "\n<br>\n</font>";

	$res .=  '<font class="'.$StyleText.'">';

	if($dbresult->NavPageNomer > 1)
		$res .=  '<a href="'.$sUrlPath.'?PAGEN_'.$dbresult->NavNum.'=1'.$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sBegin.'</a>&nbsp;|&nbsp;<a href="'.$sUrlPath.'?PAGEN_'.$dbresult->NavNum.'='.($dbresult->NavPageNomer-1).$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sPrev.'</a>';
	else
		$res .=  $sBegin.'&nbsp;|&nbsp;'.$sPrev;

	$res .=  '&nbsp;|&nbsp;';

	$NavRecordGroup = $nStartPage;
	while($NavRecordGroup <= $nEndPage)
	{
		if($NavRecordGroup == $dbresult->NavPageNomer)
			$res .=  '<b>'.$NavRecordGroup.'</b>&nbsp';
		else
			$res .=  '<a href="'.$sUrlPath.'?PAGEN_'.$dbresult->NavNum.'='.$NavRecordGroup.$strNavQueryString.'#nav_start'.$add_anchor.'">'.$NavRecordGroup.'</a>&nbsp;';
		$NavRecordGroup++;
	}
	$res .=  '|&nbsp;';
	if($dbresult->NavPageNomer < $dbresult->NavPageCount)
		$res .=  '<a href="'.$sUrlPath.'?PAGEN_'.$dbresult->NavNum.'='.($dbresult->NavPageNomer+1).$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sNext.'</a>&nbsp;|&nbsp;<a href="'.$sUrlPath.'?PAGEN_'.$dbresult->NavNum.'='.$dbresult->NavPageCount.$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sEnd.'</a>&nbsp;';
	else
		$res .=  $sNext.'&nbsp;|&nbsp;'.$sEnd.'&nbsp;';
}

if($dbresult->bShowAll)
	$res .=  $dbresult->NavShowAll? '|&nbsp;<a href="'.$sUrlPath.'?SHOWALL_'.$dbresult->NavNum.'=0'.$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sPaged.'</a>&nbsp;' : '|&nbsp;<a href="'.$sUrlPath.'?SHOWALL_'.$dbresult->NavNum.'=1'.$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sAll.'</a>&nbsp;';

$res .=  '</font>';

echo $res;


?>