<?
define("NO_AGENT_STATISTIC", "Y");
define("NO_KEEP_STATISTIC", "Y");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");

if (!$USER->CanDoOperation('fileman_edit_existent_files'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
?>
<HTML>
<HEAD>
<STYLE TYPE="text/css">
BODY   {margin:0px; padding:0px; padding-top:4px; padding-right:2px; font-family:Arial; font-size:9px; background:buttonface; BORDER:0px;"}
TD.text {font-size:3px; }
</STYLE>
<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>">
</HEAD>
<BODY onload="GetValues()">
<?
parse_str($_POST["values"], $arValues);
if(get_magic_quotes_gpc()==1)
	UnQuoteArr($arValues);

$arParameters = Array();
$arTemplates = CTemplates::GetByID($_REQUEST["scrid"], $arValues);
$folder = substr($_REQUEST["scrid"], 0, strpos($_REQUEST["scrid"], "/"));

if($arTemplates)
	$arParameters = $arTemplates["PARAMS"];
?>
<script language="JavaScript" type="text/javascript">
var allcntrls = Array();
function GetValues()
{
	var values = document.values;
	var res = '';
	for(i=0; i<allcntrls.length; i++)
	{
		var nm = allcntrls[i];
		var el = false;
		if(values[nm])
		{
			if(values[nm+'_alt'] && values[nm].selectedIndex==0)
				el = values[nm+'_alt'];
			else
				el = values[nm];
		}
		else if(values[nm+'[]'])
		{
			el = values[nm+'[]'];
			nm = nm+'[]';
		}
		if(el)
		{
			if(el.length>0 && !el.tagName)
			{
				var bWVal = false;
				for(j=0; j<el.length; j++)
				{
					if(el[j].tagName == "SELECT")
					{
						var el_select = el[j];
						for(k=1; k<el_select.length; k++)
						{
							if(el_select[k].selected && el_select[k].value!='')
							{
								if(res!='') res = res + '&';
								res = res + nm + '=' + escape(el_select[k].value);
								bWVal = true;
							}
						}
					}
					else
					{
						if(el[j].value == '') continue;
						if(res!='') res = res + '&';
						res = res + nm + '=' + escape(el[j].value);
						bWVal = true;
					}
				}
				if(!bWVal)
				{
					if(res!='') res = res + '&';
					res = res + nm + '=';
				}
			}
			else
			{
				if(el.tagName == "SELECT")
				{
					var el_select = el;
					for(k=0; k<el_select.length; k++)
					{
						if(el_select[k].selected && el_select[k].value!='')
						{
							if(res!='') res = res + '&';
							res = res + nm + '=' + escape(el_select[k].value);
							bWVal = true;
						}
					}
				}
				else
				{
					if(res!='') res = res + '&';
					res = res + nm + '=' + escape(el.value);
				}
			}
		}
	}
	parent.bDocumentChanged = true;
	parent.document.UploadForm["<?=htmlspecialchars($_POST["field_name"])?>"].value = res;
	document.values.values.value = res;
}
</script>
<?
$all_cntrl = Array();
function __GetPropertyHTML($ID, $prop, $arValues)
{
	global $all_cntrl;
	if($arValues===false && isset($prop["DEFAULT"]))
		$arValues = $prop["DEFAULT"];

	$all_cntrl[] = $ID;

	if($prop["MULTIPLE"]=='Y' && !is_array($arValues))
	{
		if(isset($arValues))
			$val = Array($arValues);
		else
			$val = Array();
	}
	elseif($prop["TYPE"]=="LIST" && !is_array($arValues))
		$val = Array($arValues);
	else
		$val = $arValues;

	$res = "";
	if($prop["COLS"]<1)
		$prop["COLS"] = '30';

	if($prop["MULTIPLE"]=='Y')
	{
		$prop["CNT"] = IntVal($prop["CNT"]);
		if($prop["CNT"]<1)
			$prop["CNT"] = 1;
	}

	switch(strtoupper($prop["TYPE"]))
	{
	case "LIST":
		$prop["SIZE"] = ($prop["MULTIPLE"]=='Y' && IntVal($prop["SIZE"])<=1 ? '3' : $prop["SIZE"]);
		if(intval($prop["SIZE"])<=0)
			$prop["SIZE"] = 1;

		$res .= '<select name="'.$ID.($prop["MULTIPLE"]=="Y"?'[]':'').'"'.
			($prop["MULTIPLE"]=="Y"?
				' multiple ':
				($prop['ADDITIONAL_VALUES']!=='N'?
				' onChange="document.all(\''.$ID.'_alt\').disabled = (this.selectedIndex!=0);GetValues();" '
				:'')
			).
			' size="'.$prop["SIZE"].'">';

		if(!is_array($prop["VALUES"]))
			$prop["VALUES"] = Array();

		$tmp = ''; $bFound = false;
		foreach($prop["VALUES"] as $v_id=>$v_name)
		{
			$key = array_search($v_id, $val);
			if($key===FALSE || $key===NULL)
				$tmp .= '<option value="'.htmlspecialchars($v_id).'">'.htmlspecialchars($v_name).'</option>';
			else
			{
				unset($val[$key]);
				$bFound = true;
				$tmp .= '<option value="'.htmlspecialchars($v_id).'" selected>'.htmlspecialchars($v_name).'</option>';
			}
		}
		if($prop['ADDITIONAL_VALUES']!=='N')
			$res .= '<option value=""'.(!$bFound?' selected':'').'>'.($prop["MULTIPLE"]=="Y"?GetMessage("FILEMANT_PHPSTAN_NOT"):GetMessage("FILEMANT_PHPSTAN_OTH").' -&gt;').'</option>';
		//else
		//	$res .= '<option value=""'.(!$bFound?' selected':'').'>'.($prop["MULTIPLE"]=="Y"?GetMessage("FILEMANT_PHPSTAN_NOT"):'').'</option>';
		$res .= $tmp;
		$res .= '</select>';
		if($prop['ADDITIONAL_VALUES']!=='N')
		{
			if($prop["MULTIPLE"]=='Y')
			{
				reset($val);
				foreach($val as $v)
				{
					$res .= '<br>';
					if($prop['ROWS']>1)
						$res .= '<textarea name="'.$ID.'[]" cols='.$prop["COLS"].'>'.htmlspecialchars($v).'</textarea>';
					else
						$res .= '<input type="text" name="'.$ID.'[]" size='.$prop["COLS"].' value="'.htmlspecialchars($v).'">';
				}

				for($i=0; $i<$prop["CNT"]; $i++)
				{
					$res .= '<br>';
					if($prop['ROWS']>1)
						$res .= '<textarea name="'.$ID.'[]" cols='.$prop["COLS"].'>'.htmlspecialchars('').'</textarea>';
					else
						$res .= '<input type="text" name="'.$ID.'[]" size='.$prop["COLS"].' value="'.htmlspecialchars('').'">';
				}
				$res .= '<span id="'.$ID.'_x"></span>'.
						'<input type="button" value="+" onClick="document.all(\''.$ID.'_x\').outerHTML=\''.
						'<br>';
				if($prop['ROWS']>1)
					$res .= '<textarea name=\\\''.$ID.'[]\\\' cols=\\\''.$prop["COLS"].'\\\'></textarea>';
				else
					$res .= '<input type=\\\'text\\\' name=\\\''.$ID.'[]\\\' size=\\\''.$prop["COLS"].'\\\'>';

				$res .= '<span id=\\\''.$ID.'_x\\\'></span>\'">';
			}
			else
			{
				if($prop['ROWS']>1)
					$res .= '<textarea name="'.$ID.'_alt" '.($bFound?' disabled ':'').' cols='.$prop["COLS"].'>'.htmlspecialchars(count($val)>0?$val[0]:'').'</textarea>';
				else
					$res .= '<input type="text" name="'.$ID.'_alt" '.($bFound?' disabled ':'').'size='.$prop["COLS"].' value="'.htmlspecialchars(count($val)>0?$val[0]:'').'">';
			}
		}
		break;
	default:
		if($prop["MULTIPLE"]=='Y')
		{
			$bBr = false;
			foreach($val as $v)
			{
				if($bBr)
					$res .= '<br>';
				else
					$bBr = true;
				if($prop['ROWS']>1)
					$res .= '<textarea name="'.$ID.'[]" cols='.$prop["COLS"].'>'.htmlspecialchars($v).'</textarea>';
				else
					$res .= '<input type="text" name="'.$ID.'[]" size='.$prop["COLS"].' value="'.htmlspecialchars($v).'">';
			}

			for($i=0; $i<$prop["CNT"]; $i++)
			{
				if($bBr)
					$res .= '<br>';
				else
					$bBr = true;
				if($prop['ROWS']>1)
					$res .= '<textarea name="'.$ID.'[]" cols='.$prop["COLS"].'>'.htmlspecialchars('').'</textarea>';
				else
					$res .= '<input type="text" name="'.$ID.'[]" size='.$prop["COLS"].' value="'.htmlspecialchars('').'">';
			}

			$res .= '<span id="'.$ID.'_x"></span>'.
					'<input type="button" value="+" onClick="document.all(\''.$ID.'_x\').outerHTML=\''.
					'<br>';
			if($prop['ROWS']>1)
				$res .= '<textarea name=\\\''.$ID.'[]\\\' cols=\\\''.$prop["COLS"].'\\\'></textarea>';
			else
				$res .= '<input type=\\\'text\\\' name=\\\''.$ID.'[]\\\' size=\\\''.$prop["COLS"].'\\\'>';

			$res .= '<span id=\\\''.$ID.'_x\\\'></span>\'">';
		}
		else
		{
			if($prop['ROWS']>1)
				$res .= '<textarea name="'.$ID.'" cols='.$prop["COLS"].'>'.htmlspecialchars($val).'</textarea>';
			else
				$res .= '<input name="'.$ID.'" size='.$prop["COLS"].' value="'.htmlspecialchars($val).'" type="text">';
		}
		break;
	}
	if($prop["REFRESH"]=="Y")
		$res .= '<input type="button" value="OK" onclick="document.values.submit();">';
	return $res;
}

if(!is_array($arParameters))
	$arParameters = Array();
?>
<form name="values" method="POST">
<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr valign="top">
<td colspan="2" style="background-color:buttonshadow; padding:3px;">
<?
$arTemplateFolders = CTemplates::GetFolderList();
if(strlen($arTemplates["ICON"])<=0 || !is_file($_SERVER["DOCUMENT_ROOT"].$arTemplates["ICON"]))
	$arTemplates["ICON"] = "/bitrix/images/fileman/htmledit/component.gif";
?>
	<table cellpadding="0" border="0" cellspacing="0"><tr>
		<td><img src="<?=$arTemplates["ICON"]?>" hspace="0" vspace="0" alt="<?=htmlspecialcharsex($arTemplates["DESCRIPTION"])?>"></td>
		<td style="font-size:12px; color:white;">&nbsp;&nbsp;<b><?=htmlspecialcharsex($arTemplates["NAME"]." - [".$arTemplateFolders[$folder]."]")?></b></td>
 	</tr></table>
</td>
</tr>
<?
foreach($arParameters as $ID=>$prop):
?>
<tr valign="top">
	<td style="font-size:12px; border-bottom:1px solid buttonshadow;border-left:1px solid buttonshadow;"><?=htmlspecialchars($prop["NAME"])?>:</td>
	<td style="font-size:11px; border-bottom:1px solid buttonshadow;border-left:1px solid buttonshadow;border-right:1px solid buttonshadow;">
	<?=__GetPropertyHTML($ID, $prop, (is_set($arValues, $ID)?$arValues[$ID]:false))?>
	</td>
</tr>
<?endforeach;?>
</table>
<input type="hidden" name="field_name" value="<?=htmlspecialchars($_REQUEST["field_name"])?>">
<input type="hidden" name="lang" value="<?=LANG?>">
<input type="hidden" name="values" value="<?=htmlspecialchars($_REQUEST["values"])?>">
<input type="hidden" name="site" value="<?=htmlspecialchars($site)?>">
<input type="hidden" name="scrid" value="<?=htmlspecialchars($_REQUEST["scrid"])?>">
<input type="hidden" name="scredit" value="<?=htmlspecialchars($_REQUEST["scredit"])?>">
</form>
<script language="JavaScript" type="text/javascript">
allcntrls = Array(<?
	for($i=0; $i<count($all_cntrl); $i++)
	{
		if($i>0)
			echo ", ";
		echo "'".$all_cntrl[$i]."'";
	}
	?>);
var el=document.values.elements;
if(el!=null)
{
    for (i=0; i<el.length; i++)
	{
		if(!el[i].onchange)
			el[i].onchange=GetValues;
	}
}
</script>
</BODY>
</HTML>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>
