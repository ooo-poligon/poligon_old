<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

if (!$USER->CanDoOperation('fileman_view_file_structure') && !$USER->CanDoOperation('edit_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();


function __GetTemplateLists($siteTemplate, $arComponents)
{
	$len = count($arComponents);
	for ($i=0; $i<$len; $i++)
	{
		$arTemplates = CComponentUtil::GetTemplatesList($arComponents[$i], $siteTemplate);
		push2arComp2TemplateLists($arComponents[$i],$arTemplates);
	}
}

function push2arComp2TemplateLists($componentName,$arTemplates)
{?>
<script>
window.arComp2TemplateLists['<?=$componentName;?>'] = [];
<?for ($i = 0, $len = count($arTemplates); $i<$len; $i++):?>
window.arComp2TemplateLists['<?=$componentName;?>']['<? echo$arTemplates[$i]["NAME"];?>'] = {
	name : '<?=$arTemplates[$i]["NAME"];?>',
	template : '<?=$arTemplates[$i]["TEMPLATE"];?>',
	title : '<?=CUtil::addslashes($arTemplates[$i]["TITLE"]);?>',
	description : '<?=CUtil::addslashes($arTemplates[$i]["DESCRIPTION"]);?>'
};
<?endfor;?>
</script>
<?}

function __GetTemplateProps($componentName, $templateName,$siteTemplate="",$arCurrentValues = array())
{
	$arTemplateProps = CComponentUtil::GetTemplateProps($componentName, $templateName,$siteTemplate,$arCurrentValues);
	foreach ($arTemplateProps as $k => $arTemplateProp)
		push2arComp2TemplateProps($componentName,$k,$arTemplateProp);
}


function push2arComp2TemplateProps($componentName,$paramName,$arParam)
{
	?><script>var tempAr2 = {};
tempAr2.param_name = '<?=$paramName;?>';
<?
	foreach ($arParam  as $k => $prop)
	{
		if (is_array($prop))
		{
?>tempAr2.<? echo$k;?> = {<?
			echo "\n";
			$f = true;
			foreach ($prop as $k2 => $prop_)
			{
				if (!$f)
					echo",\n";
				else
					$f = false;
				echo '\''.CUtil::addslashes($k2).'\' : \''.CUtil::addslashes($prop_).'\'';
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
?>window.arComp2TemplateProps.push(tempAr2);</script><?
}

if (isset($_GET['cname']) && isset($_GET['stid']) && isset($_GET['tname']) && isset($_GET['mode']) && $_GET['mode']=='params')
{
	?><script>window.arComp2TemplateProps = [];</script><?
	$arCurrentValues = (isset($_POST['curval'])) ? $_POST['curval'] : array();
	__GetTemplateProps($_GET['cname'], $_GET['tname'], $_GET['stid'],$arCurrentValues);
}
else if (isset($_GET['stid']) && isset($_GET['mode']) && $_GET['mode']=='list')
{
	?><script>window.arComp2TemplateLists = {};</script>
	<?
	$arComponents = (isset($_POST['complist'])) ? $_POST['complist'] : array();
	__GetTemplateLists($_GET['stid'], $arComponents);
}


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
?>