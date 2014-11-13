<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/sites/template_edit.php");

$edit_php = $USER->CanDoOperation('edit_php');
if(!$edit_php && !$USER->CanDoOperation('view_other_settings') && !$USER->CanDoOperation('lpa_template_edit'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$lpa = ($USER->CanDoOperation('lpa_template_edit') && !$edit_php); // Limit PHP access: for non admin users
$lpa_view = !$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('lpa_template_edit'); //

$strError="";
$strOK="";
$bVarsFromForm = false;

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB1"), "ICON" => "template_edit", "TITLE" => GetMessage("MAIN_TAB1_TITLE")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB2"), "ICON" => "template_edit", "TITLE" => GetMessage("MAIN_TAB2_TITLE")),
	array("DIV" => "edit3", "TAB" => GetMessage("MAIN_TAB4"), "ICON" => "template_edit", "TITLE" => GetMessage("MAIN_TAB4_TITLE")),
	array("DIV" => "edit4", "TAB" => GetMessage("MAIN_TAB3"), "ICON" => "template_edit", "TITLE" => GetMessage("MAIN_TAB3_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($lpa && $edit != "Y" && strlen($ID) <= 0) // In lpa mode users can only edit existent templates
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$bEdit = false;
if(strlen($ID)>0)
{
	$templ = CSiteTemplate::GetByID($ID);
	if($x = $templ->ExtractFields("str_"))
		$bEdit=true;
}

if($REQUEST_METHOD == "POST" && (strlen($save) > 0 || strlen($apply) > 0) && check_bitrix_sessid() && ($edit_php || $lpa))
{
	$strError = "";
	// *  *  *  *  *  *  *  *  *  *  *  *  *  *  *   LPA  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *
	if ($lpa)
	{
		// Find all php fragments in $CONTENT
		// 1. Kill all non-component 2.0 fragments
		// 2. Get and check params of components
		$content_ = $CONTENT;
		$arPHP = PHPParser::ParseFile($content_);
		$l = count($arPHP);
		if ($l > 0)
		{
			$new_content = '';
			$end = 0;
			$php_count = 0;
			for ($n = 0; $n<$l; $n++)
			{
				$start = $arPHP[$n][0];
				$new_content .= finalLPAreplacer(substr($content_,$end,$start-$end));
				$end = $arPHP[$n][1];

				//Trim php tags
				$src = $arPHP[$n][2];
				if (substr($src, 0, 5) == "<?php")
					$src = '<?'.substr($src, 5);

				//If it's Component 2 - we handle it's params, non components2 will be erased
				$comp2_begin = '<?$APPLICATION->INCLUDECOMPONENT(';
				if (strtoupper(substr($src,0, strlen($comp2_begin))) == $comp2_begin)
				{
					$arRes = PHPParser::CheckForComponent2($src);
					if ($arRes)
					{
						$comp_name = _replacer($arRes['COMPONENT_NAME']);
						$template_name = _replacer($arRes['TEMPLATE_NAME']);
						$arParams = $arRes['PARAMS'];
						$arPHPparams = Array();
						LPAComponentChecker($arParams, $arPHPparams);
						
						$len = count($arPHPparams);
						$br = "\r\n";

						$code =  '$APPLICATION->IncludeComponent('.$br.
								"\t".'"'.$comp_name.'",'.$br.
								"\t".'"'.$template_name.'",'.$br;
						// If exist at least one parameter with php code inside
						if (count($arParams) > 0)
						{
							// Get array with description of component params
							$arCompParams = CComponentUtil::GetComponentProps($comp_name);
							$arTemplParams = CComponentUtil::GetTemplateProps($comp_name,$template_name,$template);

							$arParameters = array();
							if (isset($arCompParams["PARAMETERS"]) && is_array($arCompParams["PARAMETERS"]))
								$arParameters = $arParameters + $arCompParams["PARAMETERS"];
							if (is_array($arTemplParams))
								$arParameters = $arParameters + $arTemplParams;

							// Replace values from 'DEFAULT'
							for ($e = 0; $e < $len; $e++)
							{
								$par_name = $arPHPparams[$e];
								$arParams[$par_name] = isset($arParameters[$par_name]['DEFAULT']) ? $arParameters[$par_name]['DEFAULT'] : '';
							}

							CComponentUtil::PrepareVariables($arParams);
							//ReturnPHPStr
							$params = PHPParser::ReturnPHPStr2($arParams, $arParameters);

							$code .= "\t".'Array('.$br.
								"\t".$params.$br.
								"\t".')'.$br.
								');';
						}
						else
							$code .= "\t".'Array()'.$br.');';

						$code = '<?'.$code.'?>';
						$new_content .= $code;

					}
				}
			}

			$new_content .= finalLPAreplacer(substr($content_,$end));
			$CONTENT = $new_content;
		}
		else
			$CONTENT = finalLPAreplacer($new_content);

		// Get array of PHP scripts from original template src
		$old_content = htmlspecialcharsback($str_CONTENT);
		$arPHP = PHPParser::ParseFile($old_content);
		$l = count($arPHP);

		if ($l > 0)
		{
			$new_content = '';
			$end = 0;
			$php_count = 0;
			$wa = '#WORK_AREA#';

			for ($n = 0; $n<$l; $n++)
			{
				$start = $arPHP[$n][0];
				$s_cont = substr($old_content, $end, $start - $end);
				$end = $arPHP[$n][1];

				if ($n == 0)
					continue;

				$src = $arPHP[$n][2];
				if (strpos($s_cont, $wa) !== false)
				{
					$s2 = substr($s_cont, strpos($s_cont, $wa) + strlen($wa)).$src;
					continue;
				}
				//Trim php tags
				$src = SubStr($src, ((SubStr($src, 0, 5) == "<?"."php") ? 5 : 2));
				$src = SubStr($src, 0, -2);

				$comp2_begin = '$APPLICATION->INCLUDECOMPONENT(';

				$usrc = unifyPHPfragment($src);
				if ($usrc == unifyPHPfragment('$APPLICATION->ShowPanel()') ||
					strtoupper(substr($src,0, strlen($comp2_begin))) == $comp2_begin) // component 2.0 or another predefined fragment
					continue;
				$arPHPscripts[] = $src;
			}
			$s1 = $arPHP[0][2];
		}

		// Ok, so we already have array of php scripts lets check our new content
		// LPA-users CAN delete PHP fragments and swap them but CAN'T add new or modify existent:
		while (preg_match('/#PHP\d{4}#/i', $CONTENT, $res_php, PREG_OFFSET_CAPTURE))
		{
			$php_begin = $res_php[0][1];
			$php_fr_num = intval(substr($CONTENT, $php_begin + 4, 4)) - 1; // Number of PHP fragment from #PHPXXXX# conctruction
			if (isset($arPHPscripts[$php_fr_num]))
				$CONTENT = substr($CONTENT, 0, $php_begin).'<?'.$arPHPscripts[$php_fr_num].'?>'.substr($CONTENT, $php_begin + 9);
			else
				$CONTENT = substr($CONTENT, 0, $php_begin).substr($CONTENT, $php_begin + 9);
		}

		//Add ..->ShowPanel() & "Secutity Stubs"
		$sp = '<?$APPLICATION->ShowPanel();?>';
		$body = '<body>';
		$body_pos = strpos($CONTENT, $body);
		if ($body_pos > 0)
			$content_ = substr($CONTENT, 0, $body_pos + strlen($body)).$sp.substr($CONTENT, $body_pos + strlen($body));
		else
			$content_ = $CONTENT;

		$wa = '#WORK_AREA#';
		$wa_pos = strpos($content_, $wa, $body_pos);
		if ($wa_pos > 0)
			$content__ = $s1.substr($content_, 0, $wa_pos + strlen($wa)).$s2.substr($content_, $wa_pos + strlen($wa));

		$CONTENT = $content__;
	}
	// *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *

	if(!class_exists('CFileMan'))
		CModule::IncludeModule("fileman");
	if(class_exists('CFileMan') && method_exists(CFileMan, CheckOnAllowedComponents))
	{
		if (!CFileMan::CheckOnAllowedComponents($CONTENT))
		{
			$str_err = $APPLICATION->GetException();
			if($str_err && ($err = $str_err ->GetString()))
				$strError .= $err;
			$bVarsFromForm = true;
		}
	}

	if(strlen($strError) <= 0)
	{
		$ST = new CSiteTemplate();
		$arFields = Array(
			"ID" => $ID,
			"NAME" => $NAME,
			"DESCRIPTION" => $DESCRIPTION,
			"CONTENT" => $CONTENT,
			"STYLES" => $STYLES,
			"TEMPLATE_STYLES" => $TEMPLATE_STYLES,
		);

		if($edit=="Y")
			$res = $ST->Update($ID, $arFields);
		else
			$res = (strlen($ST->Add($arFields))>0);

		if(!$res)
		{
			$strError .= $ST->LAST_ERROR."<br>";
			$bVarsFromForm = true;
		}
		else
		{
			$maxind = $_POST['maxind'];
			$str_CONTENT_for_save = "<?\n\$arStyles = Array(\n";
			for ($i=0;$i<=$maxind;$i++)
			{
				if (trim($_POST["CODE_".$i]) == '')
					continue;
					
				$code = htmlspecialchars(ltrim($_POST["CODE_".$i], "."));
				$val = htmlspecialchars($_POST["VALUE_".$i]);
				$str_CONTENT_for_save .= "\t\"".
					str_replace(array("\\", "\$", "\""), array("\\\\", "\\\$", "\\"."\""), $code).
					'" => "'.
					str_replace(array("\\", "\$", "\""), array("\\\\", "\\\$", "\\"."\""), $val).
					"\",\n";
			}

			$str_CONTENT_for_save .= ");\nreturn \$arStyles;\n?>";
			$styles_path = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".preg_replace("/\.\.\//i", "", $ID)."/.styles.php";
			$APPLICATION->SaveFileContent($styles_path, $str_CONTENT_for_save);
			$useeditor_param = (isset($_REQUEST["CONTENTusehtmledit"]) && $_REQUEST["CONTENTusehtmledit"] == 'on') ? '&usehtmled=Y' : '';
			if (strlen($save)>0)
				LocalRedirect(BX_ROOT."/admin/template_admin.php?lang=".LANGUAGE_ID.$useeditor_param);
			else
				LocalRedirect(BX_ROOT."/admin/template_edit.php?lang=".LANGUAGE_ID."&ID=".$ID."&".$tabControl->ActiveTabParam().$useeditor_param);
		}
	}
}

if($REQUEST_METHOD=="POST" && $action=="export" && $edit_php && check_bitrix_sessid())
{
	if (strlen($tpath)<=0)
		$strError .= GetMessage("MAIN_T_EDIT_PATH_NA")."<br>";

	if (strlen($strError)<=0)
	{
		if (file_exists($tpath) && $tdel_old=="Y")
		{
			@unlink($tpath);
			clearstatcache();
		}

		if (file_exists($tpath))
			$strError .= str_replace("#FILE#", $tpath, GetMessage("MAIN_T_EDIT_FILE_EX"))."<br>";
	}

	if (strlen($strError)<=0)
	{
		$bUseCompression = True;
		if (!extension_loaded('zlib') || !function_exists("gzcompress"))
			$bUseCompression = False;

		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/tar_gz.php");
		$oArchiver = new CArchiver($tpath, $bUseCompression);
		$tres = $oArchiver->add("\"".$_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ID."\"", false, $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ID);
		if ($tres)
		{
			$strOK .= str_replace("#FILE#", $tpath, GetMessage("MAIN_T_EDIT_EXPORT_OK"));
			if (substr($tpath, 0, strlen($_SERVER["DOCUMENT_ROOT"]))==$_SERVER["DOCUMENT_ROOT"])
				$strOK .= str_replace("#ADDR#", substr($tpath, strlen($_SERVER["DOCUMENT_ROOT"])), " ".GetMessage("MAIN_T_EDIT_EXPORT_AND_EXISTS"));
			$strOK .= ".<br>";
		}
		else
		{
			$strError .= GetMessage("MAIN_T_EDIT_EXPORT_ERR");
			$arErrors = &$oArchiver->GetErrors();
			if (count($arErrors)>0)
			{
				$strError .= ":<br>";
				foreach ($arErrors as $value)
					$strError .= "[".$value[0]."] ".$value[1]."<br>";
			}
			else
				$strError .= ".<br>";
		}
	}
}


if($REQUEST_METHOD=="POST" && $action=="import" && $edit_php && check_bitrix_sessid())
{
	$DATA_FILE_NAME = "";

	if(is_uploaded_file($_FILES["tpath_file"]["tmp_name"]))
		$DATA_FILE_NAME = $_FILES["tpath_file"]["tmp_name"];

	if(strlen($DATA_FILE_NAME)<=0)
	{
		if(strlen($tpath)>0 && file_exists($tpath) && is_file($tpath))
			$DATA_FILE_NAME = $tpath;
	}

	if(strlen($DATA_FILE_NAME)<=0)
		$strError .= GetMessage("MAIN_T_EDIT_IMP_FILE_NA")."<br>";

	if(strlen($ID)<=0)
		$strError .= GetMessage("MAIN_T_EDIT_IMP_ID_NA")."<br>";

	if(strlen($strError)<=0)
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/tar_gz.php");
		$oArchiver = new CArchiver($DATA_FILE_NAME);
		$tres = $oArchiver->extractFiles($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ID);
		if($tres)
			$strOK .= str_replace("#FILE#", $tpath, GetMessage("MAIN_T_EDIT_IMP_OK"))."<br>";
		else
		{
			$strError .= GetMessage("MAIN_T_EDIT_IMP_ERR");
			$arErrors = &$oArchiver->GetErrors();
			if(count($arErrors)>0)
			{
				$strError .= ":<br>";
				foreach ($arErrors as $value)
					$strError .= "[".$value[0]."] ".$value[1]."<br>";
			}
			else
				$strError .= ".<br>";
		}
	}
	
	$useeditor_param = (isset($_REQUEST["CONTENTusehtmledit"]) && $_REQUEST["CONTENTusehtmledit"] == 'on') ? '&usehtmled=Y' : '';
	if(strlen($strError)>0)
		$bVarsFromForm = true;
	else
		LocalRedirect(BX_ROOT."/admin/template_edit.php?lang=".LANG."&ID=".$ID."&".$tabControl->ActiveTabParam().$useeditor_param);
}


if($bVarsFromForm)
{
	$str_ID = htmlspecialcharsex($_POST["ID"]);
	$str_NAME = htmlspecialcharsex($_POST["NAME"]);
	$str_DESCRIPTION = htmlspecialcharsex($_POST["DESCRIPTION"]);
	$str_CONTENT = htmlspecialcharsex($_POST["CONTENT"]);
	$str_STYLES = htmlspecialcharsex($_POST["STYLES"]);
	$str_TEMPLATE_STYLES = htmlspecialcharsex($_POST["TEMPLATE_STYLES"]);
	$usehtmled = (isset($_REQUEST["CONTENTusehtmledit"]) && $_REQUEST["CONTENTusehtmledit"] == 'on') ? 'Y' : 'N';
}

// *  *  *  *  *  *  *  *  *  *  *  *  *  *  *   LPA  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *
if ($lpa || $lpa_view)
{
	$str_CONTENT = htmlspecialcharsback($str_CONTENT);
	$arPHP = PHPParser::ParseFile($str_CONTENT);
	$l = count($arPHP);

	if ($l > 0)
	{
		$new_content = '';
		$end = 0;
		$php_count = 0;
		$wa = '#WORK_AREA#';

		for ($n = 0; $n < $l; $n++)
		{
			$start = $arPHP[$n][0];
			$s_cont = substr($str_CONTENT, $end, $start - $end);
			$end = $arPHP[$n][1];
			$new_content .= $s_cont;

			if ($n == 0 || strpos($s_cont, $wa) !== false)
				continue;

			$src = $arPHP[$n][2];
			//Trim php tags
			$src = SubStr($src, ((SubStr($src, 0, 5) == "<?"."php") ? 5 : 2));
			$src = SubStr($src, 0, -2);

			$comp2_begin = '$APPLICATION->INCLUDECOMPONENT(';
			$usrc = unifyPHPfragment($src);
			if ($usrc == unifyPHPfragment('$APPLICATION->ShowPanel()')) //..->ShowPanel() will be added automaticaly
				continue;
			elseif (strtoupper(substr($src,0, strlen($comp2_begin))) == $comp2_begin) //If it's Component 2, keep the php code
				$new_content .= $arPHP[$n][2];
			else //If it's component 1 or ordinary PHP - than replace code by #PHPXXXX# (XXXX - count of PHP scripts)
				$new_content .= '#PHP'.str_pad(++$php_count, 4, "0", STR_PAD_LEFT).'#';
		}
		$new_content .= substr($str_CONTENT,$end);
	}
	$str_CONTENT = htmlspecialcharsex($new_content);
}

function finalLPAreplacer($str)
{
	$str = str_replace(array("<?","?>"),array("&lt;?","?&gt;"), $str);
	$str =  ereg_replace('<(script[^>]*language[[:space:]]*=[[:space:]]*["\']*php["\']*[^>]*)>(.*)</script>', "&lt;\\1&gt;\\2&lt;/script&gt;", $str);
	return $str;
}

function LPAComponentChecker(&$arParams, &$arPHPparams, $parentParamName = false)
{
	//all php fragments wraped by ={}
	foreach ($arParams as $param_name => $paramval)
	{
		if (substr($param_name, 0, 2) == '={' && substr($param_name, -1) == '}')
		{
			$key = substr($param_name, 2, -1);
			if (strval($key) !== strval(intval($key)))
			{
				unset($arParams[$param_name]);
				continue;
			}
		}
		
		if (is_array($paramval))
		{
			LPAComponentChecker($paramval, $arPHPparams, $param_name);
			$arParams[$param_name] = $paramval;
		}
		elseif (substr($paramval, 0, 2) == '={' && substr($paramval, -1) == '}')
		{
			$arPHPparams[] = $parentParamName ? $parentParamName : $param_name;
		}
	}
}

function unifyPHPfragment($str)
{
	if (substr($str, -1) == ';')
		$str = substr($str, 0, -1);
	$str = strtolower($str);
	$str = preg_replace("/\s/i", "", $str);
	return $str;
}

function _replacer($str)
{
	$str = preg_replace("/[^a-zA-Z0-9_:\.]/i", "", $str);
	return $str;
}

// *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *

if($bEdit)
	$APPLICATION->SetTitle(GetMessage("MAIN_T_EDIT_TITLE_EDIT"));
else
	$APPLICATION->SetTitle(GetMessage("MAIN_T_EDIT_TITLE_NEW"));

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

echo CAdminMessage::ShowMessage($strError);
echo CAdminMessage::ShowNote($strOK);

$aMenu = array(
	array(
		"TEXT"	=> GetMessage("MAIN_T_EDIT_TEMPL_LIST"),
		"LINK"	=> "/bitrix/admin/template_admin.php?lang=".LANGUAGE_ID."&set_default=Y",
		"TITLE"	=> GetMessage("MAIN_T_EDIT_TEMPL_LIST_TITLE"),
		"ICON"	=> "btn_list"
	)
);

if (strlen($ID)>0 && $edit_php)
{
	$aMenu[] = array("NEWBAR"=>"Y");

	$aMenu[] = array(
		"TEXT"	=> GetMessage("MAIN_NEW_RECORD"),
		"LINK"	=> "/bitrix/admin/template_edit.php?lang=".LANGUAGE_ID,
		"TITLE"	=> GetMessage("MAIN_NEW_RECORD_TITLE"),
		"ICON"	=> "btn_new"
		);

	$aMenu[] = array(
		"TEXT"	=> GetMessage("MAIN_COPY_RECORD"),
		"LINK"	=> "/bitrix/admin/template_admin.php?lang=".LANGUAGE_ID."&ID=".urlencode($ID)."&action=copy&".bitrix_sessid_get(),
		"TITLE"	=> GetMessage("MAIN_COPY_RECORD_TITLE"),
		"ICON"	=> "btn_copy"
		);

	$aMenu[] = array(
		"TEXT"	=> GetMessage("MAIN_DELETE_RECORD"),
		"LINK"	=> "javascript:if(confirm('".GetMessage("MAIN_DELETE_RECORD_CONF")."')) window.location='/bitrix/admin/template_admin.php?ID=".urlencode($ID)."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&action=delete';",
		"TITLE"	=> GetMessage("MAIN_DELETE_RECORD_TITLE"),
		"ICON"	=> "btn_delete"
		);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="bform">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<?if($bEdit):?><input type="hidden" name="edit" value="Y"><?endif?>
<?
$tabControl->Begin();

$tabControl->BeginNextTab();
?>
	<tr valign="top">
		<td width="40%"><?if(!$bEdit):?><span class="required">*</span><?endif;?>ID:</td>
		<td width="60%"><?
			if($bEdit):
				echo $str_ID;
				?><input type="hidden" name="ID" value="<?echo $str_ID?>">
				(<a title="<?=GetMessage("MAIN_PREVIEW_FOLDER")?>" href="fileman_admin.php?lang=<?=LANG?>&path=<?=urlencode(BX_PERSONAL_ROOT."/templates/".$str_ID)?>"><?echo BX_PERSONAL_ROOT?>/templates/<?echo $str_ID;?>/</a>)
			<?
			else:
				?><input type="text" name="ID" size="20" maxlength="255" value="<? echo $str_ID?>"><?
			endif;
			?></td>
	</tr>
	<tr valign="top">
		<td><?echo GetMessage("MAIN_T_EDIT_NAME")?></td>
		<td><input type="text" name="NAME" size="40" maxlength="50" value="<? echo $str_NAME?>"></td>
	</tr>
	<tr valign="top">
		<td><?echo GetMessage("MAIN_T_EDIT_DESCRIPTION")?></td>
		<td><textarea name="DESCRIPTION" cols="30" rows="3"><?echo $str_DESCRIPTION?></textarea></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><span class="required">*</span><?echo GetMessage("MAIN_T_EDIT_CONTENT")?></td>
	</tr>

	<tr valign="top">
		<td align="center" colspan="2">
<?
$loadEditor = false;
if(COption::GetOptionString("main", "templates_visual_editor", "N") == "Y" && IsModuleInstalled('fileman'))
{
	if(COption::GetOptionString("fileman", "use_old_version", "N") != "Y")
	{
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/version.php");
		$loadEditor = CheckVersion($arModuleVersion['VERSION'],"6.5.7");
	}
}
if($loadEditor):
	if(!class_exists('CFileMan'))
		CModule::IncludeModule("fileman");

	AddEventHandler("fileman", "OnBeforeHTMLEditorScriptsGet", "__TE_editorScripts");
	function __TE_editorScripts($editorName,$arEditorParams)
	{
		return array(
			"JS" => array('template_edit_editor.js')
		);
	}
	?><script>
		TE_MESS = {};
		TE_MESS.FILEMAN_EDIT_HBF = "<?=GetMessage("FILEMAN_EDIT_HBF")?>";
		TE_MESS.FILEMAN_INSERT_WA = "<?=GetMessage("FILEMAN_INSERT_WA")?>";
		TE_MESS.FILEMAN_TOOLBAR_TITLE = "<?=GetMessage("FILEMAN_TOOLBAR_TITLE")?>";
		TE_MESS.FILEMAN_PREVIEW_TEMPLATE = "<?=GetMessage("FILEMAN_PREVIEW_TEMPLATE")?>";
		TE_MESS.FILEMAN_PREVIEW_TEMPLATE_TITLE = "<?=GetMessage("FILEMAN_PREVIEW_TEMPLATE_TITLE")?>";
		__ID = "<?=$ID?>";
		var SITE_TEMPLATE_PATH = "<?=BX_PERSONAL_ROOT.'/templates/'.$ID?>";
	</script>
	<?
	$template_styles_path = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ID."/template_styles.css";
	CFileMan::AddHTMLEditorFrame(
		"CONTENT", // Name
		$str_CONTENT, // Content
		false, // strTextTypeFieldName
		false, //strTextTypeValue
		550, //Array("height"=>600, "width"=>'100%'),
		"N", //CONVERT_FOR_WORKFLOW
		0, //WORKFLOW_DOCUMENT_ID
		"", //NEW_DOCUMENT_PATH
		"", //textarea_field
		false, //site
		false, //bWithoutPHP
		Array("BXPropertiesTaskbar","BXComponentsTaskbar", "BXComponents2Taskbar","BXSnippetsTaskbar"), //arTaskbars
		Array //arAdditionalParams
		(
			"additionalCSS" => Array($template_styles_path),
			"dontusecookie" => true,
			"limit_php_access" => ($lpa || $lpa_view),
			"dontshowta" => true
		)
	);
else:?>
	<textarea rows="28" cols="60" style="width:100%" id="CONTENT" name="CONTENT" wrap="off"><?echo htmlspecialchars(htmlspecialcharsback($str_CONTENT))?></textarea>
<?endif;?>
		</td>
	</tr>

<script type="text/javascript" src="/bitrix/js/main/template_edit.js?v=<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/main/template_edit.js')?>"></script>

<script>
<?if ($loadEditor):?>
setTimeout(function()
{
	var el = document.getElementById("CONTENT");
	if (document.getElementById("CONTENTusehtmledit").checked && el.style.display != "none")
		el.style.display = "none";
}
, 1000);
<?endif;?>

var oContent = document.getElementById('CONTENT');
<?
$lca = COption::GetOptionString('fileman', "use_lca", 'N');
if ($lca == 'Y'):?>
oContent.value = encodeContentForLCA(oContent.value);
document.bform.onsubmit = function(e)
{
	oContent.value = decodeContentFromLCA(oContent.value);
}
<?endif;?>
oContent.style.display = 'block';
</script>
<?
$tabControl->BeginNextTab();
?>
	<tr valign="top">
		<td align="center" colspan="2"><textarea rows="25" cols="60" style="width:100%" id="__STYLES" name="STYLES" wrap="off"><?echo $str_STYLES?></textarea></td>
	</tr>
	<tr valign="top" class="heading">
		<td align="center" colspan="2"><?echo GetMessage("STYLE_DESCRIPTIONS")?></td>
	</tr>
	<tr>
		<td align="center" colspan="2">
		<?
		echo $stylesPath;
		$stylesPath = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ID."/.styles.php";
		$arStyles = (file_exists($stylesPath)) ? CSiteTemplate::__GetByStylesTitle($stylesPath) : Array();
		?>
		<script>
		function _MoreRProps()
		{
			var prt = document.getElementById("proptab");
			var cnt = parseInt(document.getElementById("maxind").value)+1;
			var r = prt.insertRow(prt.rows.length-1);
			var c = r.insertCell(-1);
			c.innerHTML = '<input type="text" id="CODE_'+cnt+'" name="CODE_'+cnt+'" value="" size="30">:';
			c = r.insertCell(-1);
			c.innerHTML = '<input type="text" name="VALUE_'+cnt+'" id="VALUE_'+cnt+'" value="" size="60">';
			document.getElementById("maxind").value = cnt;
		}
	</script>
			<table border="0" cellspacing="1" cellpadding="3" id="proptab"  class="internal">
				<tr class="heading">
					<td width="210px"><?echo GetMessage("MAIN_STYLE_NAME")?></td>
					<td width="380px"><?echo GetMessage("MAIN_STYLE_DESCRIPTION")?></td>
				</tr>
				<?
				$arStylesDesc = Array();
				$i = 0;
				if (!is_array($arStyles))
					$arStyles = Array();

				foreach($arStyles as $style_ => $title_)
				{
					?>
					<tr>
						<td  valign="top" >
							<input type="hidden" name="CODE_<?=$i?>" id="CODE_<?=$i?>" value="<?=htmlspecialchars($style_)?>">
							<? echo htmlspecialchars($style_);?>
						</td>
						<td>
							<input type="text" name="VALUE_<?=$i?>" id="VALUE_<?=$i?>" value="<?=htmlspecialchars($title_)?>" size="60">
						</td>
					</tr>
					<?
					$i++;
				}

				$ind = $i-1;
				?>
				<tr>
					<td colspan="2">
						<input type="hidden" id="maxind" name="maxind" value="<?echo $ind; ?>">
						<input type="hidden" id="styles_path" name="styles_path" value="<?=htmlspecialchars($stylesPath)?>">
						<input type="button" name="propeditmore"  value="<?echo GetMessage("MAIN_STYLE_MORE")?>" onClick="_MoreRProps()">
					</td>
				</tr>
			</table>

		<?if (count($arStyles)<1):?>
			<script>_MoreRProps();</script>
		<?endif;?>

		</td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr valign="top">
		<td align="center" colspan="2"><textarea rows="25" cols="60" style="width:100%" id="__TEMPLATE_STYLES" name="TEMPLATE_STYLES" wrap="off"><?echo $str_TEMPLATE_STYLES?></textarea></td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<?if($bEdit):?>
	<tr valign="top">
		<td valign="top" colspan="2">
			<table cellspacing="0" class="internal">
			<?
			$dbFiles = CSiteTemplate::GetContent($ID);
			while($arFiles = $dbFiles->GetNext()):
				if($arFiles["NAME"]=="header.php" || $arFiles["NAME"]=="footer.php" || $arFiles["NAME"]=="styles.css" || $arFiles["NAME"]=="template_styles.css" || $arFiles["NAME"]=="description.php")
					continue;
				if($arFiles["TYPE"]<>"F")
					continue;
			?>
			<tr>
				<td><?=htmlspecialchars($arFiles["NAME"])?></td>
				<td><?=htmlspecialchars($arFiles["DESCRIPTION"])?></td>
				<td><a title ="<?=GetMessage("MAIN_MOD_FILE").htmlspecialchars($arFiles["NAME"])?>" href="fileman_file_edit.php?lang=<?=LANG?>&amp;full_src=Y&amp;path=<?=urlencode($arFiles["ABS_PATH"])?>&amp;back_url=<?=urlencode($_SERVER["REQUEST_URI"])?>"><?echo GetMessage("MAIN_T_EDIT_CHANGE")?></a></td>
			</tr>
			<?endwhile;?>
			</table>
		</td>
	</tr>
	<tr valign="top">
		<td valign="top" align="left" colspan="2"><a title="<?=GetMessage("MAIN_T_EDIT_ADD_TITLE")?>" href="fileman_file_edit.php?lang=<?=LANG?>&amp;full_src=Y&amp;back_url=<?=urlencode($_SERVER["REQUEST_URI"])?>&amp;path=<?=urlencode(BX_PERSONAL_ROOT."/templates/".$ID)?>&amp;new=y"><?echo GetMessage("MAIN_T_EDIT_ADD")?></a></td>
	</tr>
	<?endif?>
<?
$tabControl->Buttons();
$aParams = array("disabled" => (!$edit_php && !$lpa), "back_url" => "template_admin.php?lang=".LANGUAGE_ID);
$dis = (!$edit_php && !$lpa);
?>
<input <?echo ($dis ? "disabled":"")?> type="submit" name="save" value="<?=GetMessage("admin_lib_edit_save")?>" title="<?=GetMessage("admin_lib_edit_save_title")?>">
<input <?echo ($dis ? "disabled":"")?> type="submit" name="apply" value="<?=GetMessage("admin_lib_edit_apply")?>" title="<?GetMessage("admin_lib_edit_apply_title")?>">
<?if ($USER->CanDoOperation('edit_other_settings') || $USER->CanDoOperation('lpa_template_edit')):?>
<input type="button" value="<?=GetMessage('FILEMAN_PREVIEW_TEMPLATE')?>" name="cancel" onClick="preview_template('<?=$ID?>');" title="<?=GetMessage('FILEMAN_PREVIEW_TEMPLATE_TITLE')?>">
<?endif;?>
<input type="button" value="<?=GetMessage("admin_lib_edit_cancel")?>" name="cancel" onClick="window.location='<?=CUtil::addslashes($aParams["back_url"])?>'" title="<?=GetMessage("admin_lib_edit_cancel_title")?>">
<?$tabControl->End();?>
</form>
<?echo BeginNote();?>
<span class="required">*</span> - <?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();?>
<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
