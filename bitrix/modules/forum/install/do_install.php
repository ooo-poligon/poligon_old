<?
include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/lang/", "/install/index.php"));
UnRegisterModuleDependences("main", "OnGroupDelete", "forum", "CForum", "OnGroupDelete");
UnRegisterModuleDependences("main", "OnBeforeLangDelete", "forum", "CForum", "OnBeforeLangDelete");
UnRegisterModuleDependences("search", "OnReindex", "forum", "CForum", "OnReindex");
UnRegisterModuleDependences("main", "OnUserDelete", "forum", "CForum", "OnUserDelete");
UnRegisterModule("forum");

CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/forum/install/admin", $DOCUMENT_ROOT."/bitrix/admin");

$errors = $DB->RunSQLBatch($DOCUMENT_ROOT."/bitrix/modules/forum/install/".$DBType."/install.sql");

if ($errors===False)
{
	$errors = $DB->RunSQLBatch($DOCUMENT_ROOT."/bitrix/modules/forum/install/".$DBType."/install1.sql");
}

RegisterModule("forum");
RegisterModuleDependences("main", "OnGroupDelete", "forum", "CForum", "OnGroupDelete");
RegisterModuleDependences("main", "OnBeforeLangDelete", "forum", "CForum", "OnBeforeLangDelete");
RegisterModuleDependences("search", "OnReindex", "forum", "CForum", "OnReindex");
RegisterModuleDependences("main", "OnUserDelete", "forum", "CForum", "OnUserDelete");

CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/forum/images", $DOCUMENT_ROOT."/bitrix/images/forum", False);
CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/forum/images/icon", $DOCUMENT_ROOT."/bitrix/images/forum/icon", False);
CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/forum/images/smile", $DOCUMENT_ROOT."/bitrix/images/forum/smile", False);
CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/forum/images/help", $DOCUMENT_ROOT."/bitrix/images/forum/help", False);

if ($errors===False)
{
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");
}
$langs = CLang::GetList($by, $order, Array("ACTIVE"=>"Y"));
while ($lang = $langs->Fetch())
{
	if (file_exists($DOCUMENT_ROOT."/bitrix/modules/forum/install/public/".$lang["LID"]))
	{
		CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/forum/install/public/".$lang["LID"], $DOCUMENT_ROOT.$lang["DIR"]."forum", False);
		CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/forum/install/bitrix/php_interface/".$lang["LID"], $DOCUMENT_ROOT."/bitrix/php_interface/".$lang["LID"], False);
	}
	else
	{
		CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/forum/install/public/ru", $DOCUMENT_ROOT.$lang["DIR"]."forum", False);
		CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/forum/install/bitrix/php_interface/ru", $DOCUMENT_ROOT."/bitrix/php_interface/".$lang["LID"], False);
	}

	if ($errors===False)
	{
		$arFields = Array(
			"NAME"=>"Test forum",
			"ACTIVE"=>"Y",
			"MODERATION"=>"N",
			"ORDER_BY"=>"P",
			"ORDER_DIRECTION"=>"DESC",
			"LID"=>$lang["LID"],
			"ACTION"=>"ADD"
		);
		CForum::AddForum($arFields);
	}
}

if(CModule::IncludeModule("search"))
	CSearch::ReIndexModule("forum");

?>
<font class="text">
	<?echo GetMessage("FORUM_NEEDHELP")?><br>
	<br>
	<form action="<?echo $APPLICATION->GetCurPage()?>">
		<input type="hidden" name="lang" value="<?echo LANG?>">
		<input type="submit" class="button" name="" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">	
	<form>
</font>
<?if ($errors!==false):?>
	<?echo ShowError(GetMessage("FORUM_ERRORS_INSTALLATION"))?>
	<br><br><font class="text">
		<?for($i=0; $i<count($errors); $i++):?>
			<?echo $errors[$i]?><br>
		<?endfor;?>
		<p>
			<a href="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANG?>"><?echo GetMessage("FORUM_BACK")?></a>
		</p>
	</font>
<?endif;?>
