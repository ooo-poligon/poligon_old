<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
if (!$USER->CanDoOperation('fileman_view_file_structure') && !$USER->CanDoOperation('edit_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

function _addslashes($str)
{
	$pos = strpos(strtolower($str), "script");
	if ($pos!==FALSE)
		$str = str_replace("</script","&lt;/script",$str);

	$pos2 = strpos(strtolower($str), "\n");
	if ($pos2!==FALSE)
	{
		$str = str_replace("\r","",$str);
		$str = str_replace("\n","\\n",$str);
	}
	return CUtil::addslashes($str);
}

function GetProperties($componentName)
{
	$stid = (isset($_GET['stid'])) ? $_GET['stid'] : '';
	$arTemplates = CComponentUtil::GetTemplatesList($componentName, $stid);
	$arCurVals = isset($_POST['curval']) ? $_POST['curval'] : Array();

	$loadHelp = (isset($_GET['loadhelp']) && $_GET['loadhelp']=="Y") ? true : false;

	foreach ($arTemplates as $k => $arTemplate)
	{
		push2arComp2Templates($arTemplate['NAME'],$arTemplate['TEMPLATE'],$arTemplate['TITLE'],$arTemplate['DESCRIPTION']);

		if ($arTemplate['NAME'] == '.default' || $arTemplate['NAME'] =='')
		{
			$arTemplateProps = CComponentUtil::GetTemplateProps($componentName, $arTemplate['NAME'], $stid, $arCurVals);
			foreach ($arTemplateProps as $k => $arTemplateProp)
				push2arComp2TemplateProps($componentName,$k,$arTemplateProp);
		}
	}

	$arProps = CComponentUtil::GetComponentProps($componentName, $arCurVals);

	if ($loadHelp)
		fetchPropsHelp($componentName);

	$bGroup = (isset($arProps['GROUPS']) && count($arProps['GROUPS'])>0);

	foreach ($arProps['PARAMETERS'] as $k => $arParam)
		push2arComp2Props($k,$arParam,(($bGroup) ? $arProps['GROUPS'] : false));
}


function fetchPropsHelp($componentName)
{
	$path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/".str_replace(":","/",$componentName)."/help/.tooltips.php";
	$lang_path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/".str_replace(":","/",$componentName)."/lang/".$_GET["lang"]."/help/.tooltips.php";
	global $MESS;

	@include($lang_path);
	@include($path);
	if (!is_array($arTooltips))
		return;
	?>var arTT = {};<?
	foreach($arTooltips as $propName => $tooltip)
	{
		?>arTT["<?=$propName?>"] = '<?echo _addslashes($tooltip);?>';<?
	}
	?>window.arComp2Tooltips["<?=$componentName?>"] = arTT;<?
}

function push2arComp2Props($name,$arParam,$arGroup)
{
	?>
		var tempAr = {};
		tempAr.param_name = '<? echo _addslashes($name);?>';
	<?
	if ($arGroup!==false && isset($arParam['PARENT']))
	{
		foreach ($arGroup as $k =>$group)
		{
			if ($k == $arParam['PARENT'])
			{
				?>tempAr.group_title = '<? echo(($group['NAME']) ? _addslashes($group['NAME']) : $k);?>';<?
				break;
			}
		}
	}
	foreach ($arParam  as $k => $prop)
	{
		if (is_array($prop))
		{
			?>tempAr.<? echo$k;?> = {<?
					echo "\n";
					$__i=true;
					foreach ($prop as $k2 => $prop_)
					{
						if (!$__i)
							echo ",\n";
						else
							$__i = false;
						echo '\''._addslashes($k2).'\' : \''._addslashes($prop_).'\'';
					}
				echo "\n";
				?>}<?
		}
		else
		{
			?>tempAr.<? echo$k;?> = '<? echo _addslashes($prop);?>';<?
		}
		echo "\n";
	}
?>window.arComp2Props.push(tempAr);<?
}


function push2arComp2Templates($name,$template,$title,$description)
{
?>
window.arComp2Templates.push({
name : '<?=$name;?>',
template : '<?=$template;?>',
title	 : '<?=_addslashes($title);?>',
description : '<?=_addslashes($description);?>'
});
<?
}


function push2arComp2TemplateProps($componentName,$paramName,$arParam)
{
	?>var tempAr2 = {param_name: '<?=$paramName;?>'};<?
	foreach ($arParam  as $k => $prop)
	{
		if (is_array($prop))
		{
?>tempAr2.<? echo$k;?> = {<?
		echo "\n";
				$__i=true;
				foreach ($prop as $k2 => $prop_)
				{
					if (!$__i)
						echo",\n";
					else
						$__i = false;

					echo '\''._addslashes($k2).'\' : \''._addslashes($prop_).'\'';
				}
			echo "\n";
?>}<?
		}
		else
		{
?>tempAr2.<? echo$k;?> = '<? echo $prop;?>';<?
		}
		echo "\n";
	}
?>window.arComp2TemplateProps.push(tempAr2);<?
}
?>
<script>
window.arComp2Templates = [];
window.arComp2Props = [];
window.arComp2TemplateProps = [];
<?if (isset($_GET['cname'])) GetProperties($_GET['cname']);?>
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");?>