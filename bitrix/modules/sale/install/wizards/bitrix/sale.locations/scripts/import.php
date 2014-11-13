<?
define("STOP_STATISTICS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
{
	echo GetMessage('WSL_IMPORT_ERROR_ACCESS_DENIED');
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
	die();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php");

$lang = $_REQUEST['lang'];
if (preg_match('/^[a-z0-9_]{2}$/i', $lang) <= 0) $lang = 'en';

$wizard =  new CWizard("bitrix:sale.locations");
$wizard->IncludeWizardLang("scripts/import.php", $lang);

$step_length = intval($_REQUEST["STEP_LENGTH"]);
if ($step_length <= 0) $step_length = 10;

define('ZIP_WRITE_TO_LOG', 0);
define('ZIP_STEP_LENGTH', $step_length);
define('LOC_STEP_LENGTH', $step_length);

function writeToLog($cur_op)
{
	if (defined('ZIP_WRITE_TO_LOG') && ZIP_WRITE_TO_LOG === 1)
	{
		global $start_time;
	
		list($usec, $sec) = explode(" ", microtime());
		$cur_time = ((float)$usec + (float)$sec);

		$fp = fopen('log.txt', 'a');
		fwrite($fp, $cur_time.": ");
		fwrite($fp, $cur_op."\r\n");
		fclose($fp);
	}
}

CModule::IncludeModule('sale');

$STEP = intval($_REQUEST['STEP']);
$CSVFILE = $_REQUEST["CSVFILE"];
$bLoadLoc = $_REQUEST["LOADLOC"] == 'Y' ? 'Y' : 'N';
$bLoadZip = $_REQUEST["LOADZIP"] == 'Y' ? 'Y' : 'N';
$bSync = $_REQUEST["SYNC"] == "Y";

if (!in_array($CSVFILE, array('ussr', 'usa', 'cntr')))
{
	echo GetMessage('WSL_IMPORT_ERROR_FILES');
}
else
{
	$ZIPFILE = 'zip_'.$CSVFILE.'.csv';
	$CSVFILE = 'loc_'.$CSVFILE.'.csv';

	if ($STEP == 1 && $bLoadLoc != 'Y') 
	{
		if ($bLoadZip == 'Y') $STEP = 2;
		else $STEP = 3;
	}

	switch($STEP)
	{
		case 0:
			echo GetMessage('WSL_IMPORT_FILES_LOADING');
			echo "<script>Import(1)</script>";
		break;

		case 1:
		
			$time_limit = ini_get('max_execution_time');
			if ($time_limit < LOC_STEP_LENGTH) set_time_limit(LOC_STEP_LENGTH + 5);
		
			$start_time = time();
			$finish_time = $start_time + LOC_STEP_LENGTH;
		
			$file_url = "../upload/".$CSVFILE;
	
			if (!file_exists($file_url))
				$strWarning = GetMessage('WSL_IMPORT_ERROR_NO_LOC_FILE')."<br />";

			if (strlen($strWarning)<=0)
			{
				$bFinish = true;
			
				$db_lang = CLangAdmin::GetList(($b="sort"), ($o="asc"), array("ACTIVE" => "Y"));
				while ($arLang = $db_lang->Fetch())
				{
					$arSysLangs[] = $arLang["LID"];
				}

				$arLocations = array();

				if (!$bSync)
				{
					if (!is_set($_SESSION["LOC_POS"])) 
					{
						CSaleLocation::DeleteAll();
					}
				}
				else
				{
					$dbLocations = CSaleLocation::GetList(array(), array(), false, false, array("ID", "COUNTRY_ID", "CITY_ID"));
					while ($arLoc = $dbLocations->Fetch())
					{
						$arLocations[$arLoc["ID"]] = $arLoc;
					}
				}

				include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/csv_data.php");

				$csvFile = new CCSVDataSale();
				$csvFile->LoadFile($file_url);
				$csvFile->SetFieldsType("R");
				$csvFile->SetFirstHeader(false);
				$csvFile->SetDelimiter(",");

				$arRes = $csvFile->Fetch();
				if (!is_array($arRes) || count($arRes)<=0 || strlen($arRes[0])!=2)
				{
					$strWarning .= GetMessage('WSL_IMPORT_ERROR_WRONG_LOC_FILE')."<br />";
				}

				if (strlen($strWarning)<=0)
				{
					$DefLang = $arRes[0];
					if (!in_array($DefLang, $arSysLangs))
					{
						$strWarning .= GetMessage('WSL_IMPORT_ERROR_NO_LANG')."<br />";
					}
				}

				if (strlen($strWarning)<=0)
				{
				
					if (is_set($_SESSION["LOC_POS"]))
					{
						$csvFile->SetPos($_SESSION["LOC_POS"]);
						
						$CurCountryID = $_SESSION["CUR_COUNTRY_ID"];
						//WriteToLog('current step: '.$_SESSION["LOC_POS"]);
						//WriteToLog('current country: '.$CurCountryID);
					}
					else
					{
						$CurCountryID = 0;
						//WriteToLog('start');
					}
					
					$DB->StartTransaction();					
					
					while ($arRes = $csvFile->Fetch())
					{
						$arArrayTmp = array();
						for ($ind = 1; $ind < count($arRes); $ind+=2)
						{
							if (in_array($arRes[$ind], $arSysLangs))
							{
								$arArrayTmp[$arRes[$ind]] = array(
										"LID" => $arRes[$ind],
										"NAME" => $arRes[$ind + 1]
									);

								if ($arRes[$ind] == $DefLang)
								{
									$arArrayTmp["NAME"] = $arRes[$ind + 1];
								}
							}
						}

						//WriteToLog(print_r($arArrayTmp, true));
						
						if (is_array($arArrayTmp) && strlen($arArrayTmp["NAME"])>0)
						{
							if (ToUpper($arRes[0])=="S")
							{
								$bFound = false;
								foreach ($arArrayTmp as $key=>$arValue)
								{
									if ($key != 'NAME')
									{
										$db_contList = CSaleLocation::GetCountryList(
											Array(), 
											Array(
												"NAME" => $arValue["NAME"]
											), 
											$arValue["LID"]
										);
										
										if ($arContList = $db_contList->Fetch())
										{
											$bFound = true;
											break;
										}
									}
								}

								if ($bFound)
								{
									$CurCountryID = $arContList["ID"];
									$CurCountryID = IntVal($CurCountryID);
									WriteToLog('Country found: '.$CurCountryID);									
								}
								else
								{
									WriteToLog('Country add start');
									$CurCountryID = CSaleLocation::AddCountry($arArrayTmp);
									$CurCountryID = IntVal($CurCountryID);
									WriteToLog('Country add finish - '.$CurCountryID);
									if ($CurCountryID>0)
									{
										WriteToLog('Location add start');
										$LLL = CSaleLocation::AddLocation(array("COUNTRY_ID" => $CurCountryID));
										WriteToLog('Location add finish - '.$LLL);
									}
								}
							}
							elseif (ToUpper($arRes[0])=="T" && $CurCountryID>0)
							{
								$city_id = 0;
								$LLL = 0;
								
								if ($bSync)
								{
									WriteToLog('City search query start');
											
									$arCityQuery = array();
									foreach ($arArrayTmp as $key => $arValue)
									{
										if ($key != 'NAME')
										{
											$arCityQuery[] = $arValue['NAME'];
										}
									}
									
									$bFound = false;
									if ($arCityList = CSaleLocation::_GetCityImport($arCityQuery, $CurCountryID))
									{
										$bFound = true;
									}
									
									WriteToLog('City search query finish');
									
									if ($bFound)
									{
										$LLL = $arCityList["ID"];
										$city_id = $arCityList["CITY_ID"];
										$city_id = IntVal($city_id);
										WriteToLog('City found - '.$LLL.' - '.$city_id);
									}
								}

								if ($city_id <= 0)
								{
									WriteToLog('City add start');
									$city_id = CSaleLocation::AddCity($arArrayTmp);
									$city_id = IntVal($city_id);
									WriteToLog('City add finish - '.$city_id);
									//WriteToLog('city add: '.$city_id);
								}
								
								if ($city_id > 0)
								{
									if (IntVal($LLL) <= 0)
									{
										WriteToLog('Location add start');
										$LLL = CSaleLocation::AddLocation(
											array(
												"COUNTRY_ID" => $CurCountryID,
												"CITY_ID" => $city_id
											));
										WriteToLog('Location add finish - '.$LLL);
									}
								}
							}
						}
						
						$cur_time = time();
						
						if ($cur_time >= $finish_time)
						{
							$cur_step = $csvFile->GetPos();
							$amount = $csvFile->iFileLength;
							
							$_SESSION["LOC_POS"] = $cur_step;
							$_SESSION["CUR_COUNTRY_ID"] = $CurCountryID;
							$bFinish = false;
							
							//WriteToLog('proceed to next step: country - '.$_SESSION["CUR_COUNTRY_ID"].' - step - '.$_SESSION["LOC_POS"]);
							
							echo "<script>Import(1, {AMOUNT:".CUtil::JSEscape($amount).",POS:".CUtil::JSEscape($cur_step)."})</script>";
							
							break;
						}						
					}
					
					$DB->Commit();
				}
				else
				{
					echo $strWarning."<br />";
				}
			}
			

			if ($bFinish)
			{
				unset($_SESSION["LOC_POS"]);
				
				$strOK = GetMessage('WSL_IMPORT_LOC_DONE').'<br />';
				echo $strOK;
				echo '<script>Import('.($bLoadZip == "Y" ? 2 : 3).')</script>';
			}
		
		break;
		
		case 2:
			$time_limit = ini_get('max_execution_time');
			if ($time_limit < ZIP_STEP_LENGTH) set_time_limit(ZIP_STEP_LENGTH + 5);
			
			
			$start_time = time();
			$finish_time = $start_time + ZIP_STEP_LENGTH;

			if ($bLoadZip == "Y" && file_exists('../upload/'.$ZIPFILE))
			{
				$LID = $ZIPFILE == 'zip_usa.csv' ? 'en' : 'ru';
				//WriteToLog('locations load start');
				$rsLocations = CSaleLocation::GetList(array(), array("LID" => $LID), false, false, array("ID", "CITY_NAME_LANG"));
				$arLocationMap = array();
				while ($arLocation = $rsLocations->Fetch())
				{
					$arLocationMap[ToUpper($arLocation["CITY_NAME_LANG"])] = $arLocation["ID"];
				}
				//WriteToLog('locations load finish');

				$DB->StartTransaction();
				
				include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/csv_data.php");

				$csvFile = new CCSVDataSale();
				$csvFile->LoadFile("../upload/".$ZIPFILE);
				$csvFile->SetFieldsType("R");
				$csvFile->SetFirstHeader(false);
				$csvFile->SetDelimiter(";");

				if (is_set($_SESSION, 'ZIP_POS')) 
				{
					$csvFile->SetPos($_SESSION["ZIP_POS"]);
					
					//writeToLog('ZIP_POS = '.$_SESSION["ZIP_POS"]);
				}
				else
				{
					if (!$bSync)
						CSaleLocation::ClearAllLocationZIP();
				}

				$bFinish = true;
				
				$arLocationsZIP = array();
					
				while ($arRes = $csvFile->Fetch())
				{
					$CITY = ToUpper($arRes[1]);
					
					if (array_key_exists($CITY, $arLocationMap))
					{
						$ID = $arLocationMap[$CITY];
						//writeToLog('CITY: '.$CITY);
					}
					else
					{
						$ID = 0;
					}
					
					if ($ID)
					{
						//WriteToLog('Add location zip start');
						CSaleLocation::AddLocationZIP($ID, $arRes[2], $bSync);
						//WriteToLog('Add location zip finish');
					}
					
					$cur_time = time();
					if ($cur_time >= $finish_time)
					{
						$cur_step = $csvFile->GetPos();
						$amount = $csvFile->iFileLength;
						
						$_SESSION["ZIP_POS"] = $cur_step;
						
						$bFinish = false;
						
						echo "<script>Import(2, {AMOUNT:".CUtil::JSEscape($amount).",POS:".CUtil::JSEscape($cur_step)."})</script>";
						
						break;
					}
				}
				
				$DB->Commit();				
				
				if ($bFinish)
				{
					unset($_SESSION["ZIP_POS"]);
					
					//$numCity = CSaleLocation::_GetZIPImportStats();
					
					$strOK = GetMessage('WSL_IMPORT_ZIP_DONE').'<br />';
					
					echo $strOK;
					echo '<script>Import(3); jsPB.Remove(true);</script>';
				}
			}
			else
			{
				echo GetMessage('WSL_IMPORT_ERROR_NO_ZIP_FILE');
				echo '<script>Import(3);</script>';
			}

		break;

		case 3:
			echo GetMessage('WSL_IMPORT_ALL_DONE');
			echo '<script>EnableButton();</script>';
		break;
	}
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
?>