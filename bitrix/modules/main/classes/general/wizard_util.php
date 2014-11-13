<?
class CWizardUtil
{

	function GetRepositoryPath()
	{
		return BX_ROOT."/wizards";
	}

	function MakeWizardPath($wizardName)
	{
		if (!CWizardUtil::CheckName($wizardName))
			return "";

		return Rel2Abs("/", "/".str_replace(":", "/", $wizardName));
	}

	function CheckName($wizardName)
	{
		return (
			strlen($wizardName) > 0
			&& preg_match("#^([A-Za-z0-9_.-]+:)?([A-Za-z0-9_-]+\\.)*([A-Za-z0-9_-]+)$#i", $wizardName)
		);
	}

	function GetWizardList($filterNamespace = false)
	{
		$wizardPath = $_SERVER["DOCUMENT_ROOT"].CWizardUtil::GetRepositoryPath();
		$arWizards = Array();

		if ($handle = @opendir($wizardPath))
		{
			while (($dirName = readdir($handle)) !== false)
			{
				if ($dirName == "." || $dirName == ".." || !is_dir($wizardPath."/".$dirName))
					continue;

				if (file_exists($wizardPath."/".$dirName."/.description.php"))
				{
					//Skip component without namespace
					if ($filterNamespace !== false && strlen($filterNamespace) > 0)
						continue;

					if (LANGUAGE_ID != "en" && LANGUAGE_ID != "ru")
						if (file_exists($wizardPath."/".$dirName."/lang/en/.description.php"))
							__IncludeLang($wizardPath."/lang/en/.description.php");

					if (file_exists($wizardPath."/".$dirName."/lang/".LANGUAGE_ID."/.description.php"))
						__IncludeLang($wizardPath."/".$dirName."/lang/".LANGUAGE_ID."/.description.php");

					$arWizardDescription = Array();
					include($wizardPath."/".$dirName."/.description.php");
					$arWizards[] = Array("ID" => $dirName) + $arWizardDescription;
				}
				else
				{
					if ($filterNamespace !== false && (strlen($filterNamespace) <= 0 || $filterNamespace != $dirName))
							continue;

					if ($nspaceHandle = @opendir($wizardPath."/".$dirName))
					{
						while (($file = readdir($nspaceHandle)) !== false)
						{
							$pathToWizard = $wizardPath."/".$dirName."/".$file;

							if ($file == "." || $file == ".." || !is_dir($pathToWizard))
								continue;

							if (file_exists($pathToWizard."/.description.php"))
							{
								if (LANGUAGE_ID != "en" && LANGUAGE_ID != "ru")
									if (file_exists($pathToWizard."/lang/en/.description.php"))
										__IncludeLang($pathToWizard."/lang/en/.description.php");

								if (file_exists($pathToWizard."/lang/".LANGUAGE_ID."/.description.php"))
								__IncludeLang($pathToWizard."/lang/".LANGUAGE_ID."/.description.php");

								$arWizardDescription = Array();
								include($pathToWizard."/.description.php");
								$arWizards[] = Array("ID" => $dirName.":".$file) + $arWizardDescription;
							}
						}

						@closedir($nspaceHandle);
					}
				}
			}
			@closedir($handle);
		}

		return $arWizards;
	}

	function GetNamespaceList()
	{
		$arNamespaces = array();
		$namespacePath = $_SERVER["DOCUMENT_ROOT"].CWizardUtil::GetRepositoryPath();

		if ($handle = @opendir($namespacePath))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..")
					continue;

				if (is_dir($namespacePath."/".$file))
				{
					if (!file_exists($namespacePath."/".$file."/.description.php"))
						$arNamespaces[] = $file;
				}
			}
			@closedir($handle);
		}

		return $arNamespaces;
	}

	function DeleteWizard($wizardName)
	{
		if (!CWizardUtil::CheckName($wizardName))
			return false;

		$wizardPath = CWizardUtil::GetRepositoryPath().CWizardUtil::MakeWizardPath($wizardName);
		if (!file_exists($_SERVER["DOCUMENT_ROOT"].$wizardPath))
			return false;

		$success = DeleteDirFilesEx($wizardPath);
		return $success;
	}

	function CopyWizard($wizardName, $newName)
	{
		if (!CWizardUtil::CheckName($wizardName) || !CWizardUtil::CheckName($newName))
			return false;

		$wizardPath = $_SERVER["DOCUMENT_ROOT"].CWizardUtil::GetRepositoryPath().CWizardUtil::MakeWizardPath($wizardName);
		$newNamePath = $_SERVER["DOCUMENT_ROOT"].CWizardUtil::GetRepositoryPath().CWizardUtil::MakeWizardPath($newName);
		if (!file_exists($wizardPath) || file_exists($newNamePath))
			return false;

		CopyDirFiles(
			$wizardPath, 
			$newNamePath, 
			$rewrite = false, 
			$recursive = true
		);

		return true;
	}

	function ReplaceMacros($filePath, $arReplace, $skipSharp = false)
	{
		clearstatcache();

		if (!is_file($filePath) || !is_writable($filePath) || !is_array($arReplace))
			return;

		@chmod($filePath, BX_FILE_PERMISSIONS);

		if (!$handle = @fopen($filePath, "rb"))
			return;

		$content = @fread($handle, filesize($filePath));
		@fclose($handle);

		$handle = false;
		if (!$handle = @fopen($filePath, "wb"))
			return;

		if (flock($handle, LOCK_EX))
		{
			$arSearch = Array();
			$arValue = Array();

			foreach ($arReplace as $search => $replace)
			{
				if ($skipSharp)
					$arSearch[] = $search;
				else
					$arSearch[] = "#".$search."#";

				$arValue[] = $replace;
			}

			$content = str_replace($arSearch, $arValue, $content);
			@fwrite($handle, $content);
			@flock($fp, LOCK_UN);
		}
		@fclose($handle);
	}

	function CopyFile($fileID, $destPath, $deleteAfterCopy = true)
	{
		$arFile = CFile::GetFileArray($fileID);
		if (!$arFile)
			return false;

		$filePath = $_SERVER["DOCUMENT_ROOT"].$arFile["SRC"];
		if (!is_file($filePath))
			return false;

		CheckDirPath($_SERVER["DOCUMENT_ROOT"].$destPath);
		if(!@copy($filePath, $_SERVER["DOCUMENT_ROOT"].$destPath))
			return false;

		if ($deleteAfterCopy)
			CFile::Delete($fileID);

		return true;
	}

	function GetModules()
	{
		$arModules = array();

		$arModules["main"] = Array(
			"MODULE_ID" => "main",
			"MODULE_NAME" => GetMessage("MAIN_WIZARD_MAIN_MODULE_NAME"),
			"MODULE_DESCRIPTION" => GetMessage("MAIN_WIZARD_MAIN_MODULE_DESC"),
			"MODULE_VERSION" => SM_VERSION,
			"MODULE_VERSION_DATE" => SM_VERSION_DATE,
			"IsInstalled" => true,
		);

		$handle=@opendir($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules");
		if($handle)
		{
			while (false !== ($dir = readdir($handle)))
			{
				if(is_dir($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/".$dir) && $dir!="." && $dir!="..")
				{
					$module_dir = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/".$dir;
					if(file_exists($module_dir."/install/index.php"))
					{
						include_once($module_dir."/install/index.php");
						$info = new $dir;
						$arModules[$dir]["MODULE_ID"] = $info->MODULE_ID;
						$arModules[$dir]["MODULE_NAME"] = $info->MODULE_NAME;
						$arModules[$dir]["MODULE_DESCRIPTION"] = $info->MODULE_DESCRIPTION;
						$arModules[$dir]["MODULE_VERSION"] = $info->MODULE_VERSION;
						$arModules[$dir]["MODULE_VERSION_DATE"] = $info->MODULE_VERSION_DATE;
						$arModules[$dir]["MODULE_SORT"] = $info->MODULE_SORT;
						$arModules[$dir]["IsInstalled"] = $info->IsInstalled();
					}
				}
			}
			closedir($handle);
		}

		return $arModules;
	}

	function CreateThumbnail($sourcePath, $previewPath, $maxWidth, $maxHeight)
	{
		if (!is_file($sourcePath))
			return false;

		$maxWidth = intval($maxWidth);
		$maxHeight = intval($maxHeight);

		if ($maxWidth <= 0 || $maxHeight <= 0)
			return false;

		list($sourceWidth, $sourceHeight, $type, $attr) = @getimagesize($sourcePath);

		//Image type
		if ($type == 1)
			$imageType = "gif";
		elseif ($type == 2)
			$imageType = "jpeg";
		elseif ($type == 3)
			$imageType = "png";
		else
			return false;

		$imageFunction = "imagecreatefrom".$imageType;
		$sourceImage = @$imageFunction($sourcePath);

		if (!$sourceImage)
			return false;

		$ratioWidth = $sourceWidth / $maxWidth;
		$ratioHeight = $sourceHeight / $maxHeight;
		$ratio = ($ratioWidth > $ratioHeight) ? $ratioWidth : $ratioHeight;

		//Biggest side
		if ($ratio > 0)
		{
			$previewWidth = $sourceWidth / $ratio;
			$previewHeight = $sourceHeight / $ratio;
		}
		else
		{
			$previewWidth = $maxWidth;
			$previewHeight = $maxHeight;
		}

		//GD library version
		$bGD2 = false;
		if (function_exists("gd_info"))
		{
			$arGDInfo = gd_info();
			$bGD2 = ((strpos($arGDInfo['GD Version'], "2.") !== false) ? true : false);
		}

		//Create Preview
		if ($bGD2)
		{
			$previewImage = imagecreatetruecolor($previewWidth, $previewHeight);
			imagecopyresampled($previewImage, $sourceImage, 0, 0, 0, 0, $previewWidth, $previewHeight, $sourceWidth, $sourceHeight);
		}
		else
		{
			$previewImage = imagecreate($previewWidth, $previewHeight);
			imagecopyresized($previewImage, $sourceImage, 0, 0, 0, 0, $previewWidth, $previewHeight, $sourceWidth, $sourceHeight);
		}

		//Save preview
		$imageFunction = "image".$imageType;

		if ($imageType == "jpeg")
			$success = @$imageFunction($previewImage, $previewPath, 95);
		else
			$success = @$imageFunction($previewImage, $previewPath);

		@imagedestroy($previewImage);
		@imagedestroy($sourceImage);

		return $success;

	}

}

?>