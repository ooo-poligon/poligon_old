<?
$US_HOST_PROCESS_MAIN = ($US_HOST_PROCESS_MAIN ? True : False);

define("US_CALL_TYPE", "DB");
define("US_SAVE_UPDATERS_DIR", "/bitrix/updaters");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php");

if (!function_exists("DBUpdaterCheckUpdates"))
{
	function DBUpdaterCheckUpdates($US_HOST_PROCESS_MAIN)
	{
		if (!file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/versions.php"))
			DBUpdaterCollectDBVersions();

		$arDBVersions = array();
		include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/versions.php");
		if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/versions.php"))
			return array();

		$arVersions = array();
		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/versions.php");

		$arResult = array();
		foreach ($arDBVersions as $moduleID => $dbVersion)
		{
			if ($US_HOST_PROCESS_MAIN && $moduleID != "main" || !$US_HOST_PROCESS_MAIN && $moduleID == "main")
				continue;

			if (array_key_exists($moduleID, $arVersions))
			{
				if (CUpdateClient::CompareVersions($arVersions[$moduleID], $dbVersion) > 0)
					$arResult[$moduleID] = $dbVersion;
			}
		}

		return $arResult;
	}

	function DBUpdaterCollectDBVersions()
	{
		$fileName = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/versions.php";

		@unlink($fileName);

		$errorMessage = "";
		$arDBVersions = CUpdateClient::GetCurrentModules($errorMessage, false);

		if (StrLen($errorMessage) <= 0)
		{
			$f = fopen($fileName, "w");
			fwrite($f, "<"."?\n");
			fwrite($f, "\$arDBVersions = array(\n");
			foreach ($arDBVersions as $moduleID => $version)
				fwrite($f, "\t\"".htmlspecialchars($moduleID)."\" => \"".htmlspecialchars($version)."\",\n");
			fwrite($f, ");\n");
			fwrite($f, "?".">");
			fclose($f);
		}
		else
		{
			CControllerClient::SendMessage("SITE_UPDATE_KERNEL_DB", "N", $errorMessage);
		}
	}

	function DBUpdaterUpdateFromVersion($moduleID, $dbVersion)
	{
		if (StrLen($moduleID) <= 0)
			return;
		if (StrLen($dbVersion) <= 0)
			return;

		$errorMessage = "";

		if (file_exists($_SERVER["DOCUMENT_ROOT"].US_SAVE_UPDATERS_DIR."/".$moduleID) && is_dir($_SERVER["DOCUMENT_ROOT"].US_SAVE_UPDATERS_DIR."/".$moduleID))
		{
			$arUpdaters = array();

			if ($handle = @opendir($_SERVER["DOCUMENT_ROOT"].US_SAVE_UPDATERS_DIR."/".$moduleID))
			{
				while (false !== ($dir = readdir($handle)))
				{
					if ($dir == "." || $dir == "..")
						continue;

					if (substr($dir, 0, 7) == "updater")
					{
						if (is_file($_SERVER["DOCUMENT_ROOT"].US_SAVE_UPDATERS_DIR."/".$moduleID."/".$dir))
						{
							$num = substr($dir, 7, strlen($dir) - 11);
							if (substr($dir, strlen($dir) - 9) == "_post.php")
								$num = substr($dir, 7, strlen($dir) - 16);

							$arUpdaters[] = array("/".$dir, Trim($num));
						}
						elseif (file_exists($_SERVER["DOCUMENT_ROOT"].US_SAVE_UPDATERS_DIR."/".$moduleID."/".$dir."/index.php"))
						{
							$num = substr($dir, 7);
							if (substr($dir, strlen($dir) - 5) == "_post")
								$num = substr($dir, 7, strlen($dir) - 12);

							$arUpdaters[] = array("/".$dir."/index.php", Trim($num));
						}
					}
				}
				closedir($handle);
			}

			for ($i1 = 0; $i1 < count($arUpdaters) - 1; $i1++)
			{
				for ($j1 = $i1 + 1; $j1 < count($arUpdaters); $j1++)
				{
					if (CUpdateClient::CompareVersions($arUpdaters[$i1][1], $arUpdaters[$j1][1]) > 0)
					{
						$tmp1 = $arUpdaters[$i1];
						$arUpdaters[$i1] = $arUpdaters[$j1];
						$arUpdaters[$j1] = $tmp1;
					}
				}
			}

			for ($i1 = 0; $i1 < count($arUpdaters); $i1++)
			{
				if (CUpdateClient::CompareVersions($arUpdaters[$i1][1], $dbVersion) <= 0)
					continue;

				$errorMessageTmp = "";

				CUpdateClient::RunUpdaterScript($_SERVER["DOCUMENT_ROOT"].US_SAVE_UPDATERS_DIR."/".$moduleID.$arUpdaters[$i1][0], $errorMessageTmp, "", $moduleID);
				if (strlen($errorMessageTmp) > 0)
					$errorMessage .= str_replace("#MODULE#", $moduleID, str_replace("#VER#", $arUpdaters[$i1][1], GetMessage("SUPP_UK_UPDN_ERR"))).": ".$errorMessageTmp.".<br>";
			}
		}

		if (StrLen($errorMessage) > 0)
			CControllerClient::SendMessage("SITE_UPDATE_KERNEL_DB", "N", $errorMessage);
	}
}

$arDBVersions = DBUpdaterCheckUpdates($US_HOST_PROCESS_MAIN);

if (count($arDBVersions) > 0)
{
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/controller_member.php");

	foreach ($arDBVersions as $moduleID => $dbVersion)
		DBUpdaterUpdateFromVersion($moduleID, $dbVersion);

	DBUpdaterCollectDBVersions();

	CControllerClient::SendMessage("SITE_UPDATE_KERNEL_DB", "Y", "");

	LocalRedirect($_SERVER["REQUEST_URI"]);
}
?>