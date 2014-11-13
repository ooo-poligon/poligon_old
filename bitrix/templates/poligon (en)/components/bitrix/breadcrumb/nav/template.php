<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//delayed function must return a string
if(empty($arResult))
	return "";
for($index = 0, $itemSize = count($arResult); $index < $itemSize; $index++)
{
	if($index > 0 AND $index != ($itemSize-1))
		$strReturn .= '<span>&nbsp;/&nbsp;</span>';
	elseif ($index == 0)	{}
	else
		$strReturn .='&nbsp;/&nbsp;';

	$title = htmlspecialcharsex($arResult[$index]["TITLE"]);
	if($arResult[$index]["LINK"] <> "" AND $index != ($itemSize-1))
		$strReturn .= '<a href="'.$arResult[$index]["LINK"].'" title="'.$title.'">'.$title.'</a>';
	else
		$strReturn .= $title;
}

return $strReturn;
?>
