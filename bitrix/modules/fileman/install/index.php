<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-18);
@include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));
IncludeModuleLangFile($strPath2Lang."/install/index.php");

Class fileman extends CModule
{
	var $MODULE_ID = "fileman";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function fileman()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else
		{
			$this->MODULE_VERSION = FILEMAN_VERSION;
			$this->MODULE_VERSION_DATE = FILEMAN_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("FILEMAN_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("FILEMAN_MODULE_DESCRIPTION");
	}

	function InstallDB()
	{
		global $DB, $DBType, $APPLICATION;

		RegisterModule("fileman");
		RegisterModuleDependences("main", "OnGroupDelete", "fileman", "CFileman", "OnGroupDelete");
		RegisterModuleDependences("main", "OnPanelCreate", "fileman", "CFileman", "OnPanelCreate");
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/tasks/install.php");

		return true;
	}

	function UnInstallDB()
	{
		global $DB, $DBType, $APPLICATION;

		UnRegisterModuleDependences("main", "OnGroupDelete", "fileman", "CFileman", "OnGroupDelete");
		UnRegisterModuleDependences("main", "OnPanelCreate", "fileman", "CFileman", "OnPanelCreate");
		UnRegisterModule("fileman");

		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/tasks/uninstall.php");

		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles()
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/fileman", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/images/1.gif", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/");
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");//css
		DeleteDirFilesEx("/bitrix/themes/.default/icons/fileman/");//icons
		DeleteDirFilesEx("/bitrix/images/fileman/");//images

		return true;
	}

	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step;
		$FM_RIGHT = $APPLICATION->GetGroupRight("fileman");
		
		if ($FM_RIGHT!="D")
		{
			$this->InstallDB();
			$this->InstallFiles();
		
			$APPLICATION->IncludeAdminFile(GetMessage("FILEMAN_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/step1.php");
		}
	}
	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step;
		$FM_RIGHT = $APPLICATION->GetGroupRight("fileman");
		if ($FM_RIGHT!="D")
		{
			$this->UnInstallDB();
			$this->UnInstallFiles();
		
			$APPLICATION->IncludeAdminFile(GetMessage("FILEMAN_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/install/unstep1.php");
		}
	}

	function GetModuleRightList()
	{
		$arr = array(
			"reference_id" => array("D","F","R"),
			"reference" => array(
				"[D] ".GetMessage("FILEMAN_DENIED"),
				"[F] ".GetMessage("FILEMAN_ACCESSABLE_FOLDERS"),
				"[R] ".GetMessage("FILEMAN_VIEW"))
			);
		return $arr;
	}
}
?>