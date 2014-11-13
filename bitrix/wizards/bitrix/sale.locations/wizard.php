<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class Step1 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('WSL_STEP1_TITLE'));
		$this->SetNextStep("step2");
		$this->SetStepID("step1");
		$this->SetCancelStep("cancel");
	}

	function OnPostForm()
	{
		$wizard = &$this->GetWizard();
		$install_type = $wizard->GetVar("install_type");
		$wizard->SetCurrentStep($install_type);
	}

	function ShowStep()
	{
		$this->content = GetMessage('WSL_STEP1_CONTENT');
	}
}

class Step2 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('WSL_STEP2_TITLE'));
		$this->SetNextStep("step2_params");
		$this->SetPrevStep("step1");		
		$this->SetStepID("step2");
		$this->SetCancelStep("cancel");
	}

	function ShowStep()
	{
		$this->content = '';
		
		$arVariants = array('ussr', 'usa', 'cntr');
		
		$wizard =& $this->GetWizard();
		$locations_csv = $wizard->GetVar('locations_csv');
		if (strlen($locations_csv) > 0)
		{
			foreach ($arVariants as $option)
			{
				${$option.'_load_loc'} = $wizard->GetVar($option.'_load_loc');
				${$option.'_load_zip'} = $wizard->GetVar($option.'_load_zip');
			}
		}
		else
		{
			$locations_csv = 'ussr';
			foreach ($arVariants as $option)
			{
				${$option.'_load_loc'} = 'Y';
				${$option.'_load_zip'} = 'Y';
			}
		}
	
		$this->content .= <<<EOT
<style type="text/css">
label {cursor: pointer}
ul {list-style: none}
</style>
<script type="text/javascript">
var last_option = '$locations_csv';
function checkOption(option_id)
{
	var prevOptions = document.getElementById(last_option + '_options');
	var curOptions = document.getElementById(option_id + '_options');
	
	prevOptions.style.display = 'none';
	curOptions.style.display = 'block';

	last_option = option_id;
	
	return;
}
</script>	
EOT;
		
		$arOptions = array(
			'ussr' => array(
				"TITLE" => GetMessage('WSL_STEP2_GFILE_USSR'),
				"DEFAULT" => $locations_csv == 'ussr' ? 'Y' : 'N',
				"OPTIONS" => array(
					'load_loc' => array(
						"TITLE" => GetMessage('WSL_STEP2_GFILE_LOC'),
						"DEFAULT" => $ussr_load_loc,
					), 
					'load_zip' => array(
						"TITLE" => GetMessage('WSL_STEP2_GFILE_ZIP_USSR'),
						"DEFAULT" => $ussr_load_zip,
					),
				),
			),

			'usa' => array(
				"TITLE" => GetMessage('WSL_STEP2_GFILE_USA'),
				"DEFAULT" => $locations_csv == 'usa' ? 'Y' : 'N',
				"OPTIONS" => array(
					'load_loc' => array(
						"TITLE" => GetMessage('WSL_STEP2_GFILE_LOC'),
						"DEFAULT" => $usa_load_loc,
					), 
					'load_zip' => array(
						"TITLE" => GetMessage('WSL_STEP2_GFILE_ZIP'),
						"DEFAULT" => $usa_load_zip,
					),
				),
			),
			
			'cntr' => array(
				"TITLE" => GetMessage('WSL_STEP2_GFILE_CNTR'),
				"DEFAULT" => $locations_csv == 'cntr' ? 'Y' : 'N',
				"OPTIONS" => array(
					'load_loc' => array(
						"TITLE" => GetMessage('WSL_STEP2_GFILE_LOC'),
						"DEFAULT" => "Y",
					), 
				),
			),
		);

		$this->content .= "<b>".GetMessage('WSL_STEP2_GFILE_TITLE')."</b><ul>";

		foreach ($arOptions as $option_id => $arOption)
		{
			$this->content .= '<li>';
		
			$arInputAttr = array("onclick" => "checkOption('".$option_id."')", "id" => $option_id);
			if ($arOption["DEFAULT"] == "Y")
				$arInputAttr['checked'] = 'checked';
			
			//$this->content .= '<pre>'.print_r($arInputAttr, true).'</pre>';
			
			$this->content .= $this->ShowRadioField("locations_csv", $option_id, $arInputAttr);
			$this->content .= '<label for="'.$option_id.'">'.$arOption["TITLE"].'</label>';
			
			if (is_array($arOption["OPTIONS"]))
			{
				$this->content .= '<ul id="'.$option_id.'_options" style="display: '.($arOption['DEFAULT'] == 'Y' ? 'block' : 'none').';">';
				
				foreach ($arOption['OPTIONS'] as $suboption_id => $arSubOption)
				{
					$this->content .= '<li>';
					$arInputAttr = array('id' => $option_id.'_'.$suboption_id);
					if ($arSubOption["DEFAULT"] == "Y")
						$arInputAttr['checked'] = 'checked';
					if ($arSubOption["DISABLED"] == "Y")
						$arInputAttr['disabled'] = 'disabled';
					
					$this->content .= $this->ShowCheckboxField($option_id.'_'.$suboption_id, 'Y', $arInputAttr).'<label for="'.$option_id.'_'.$suboption_id.'">'.$arSubOption['TITLE'].'</label>';
					
					$this->content .= '</li>';
				}
			
				$this->content .= '</ul>';
			}
			$this->content .= '</li>';
		}
		$this->content .= '</ul>';

		$this->content .= "<b>".GetMessage('WSL_STEP2_GSYNC_TITLE')."</b><ul>";
		
		$this->content .= '<li>'.$this->ShowRadioField("sync", 'Y', array("id" => "sync_Y", "checked" => "checked"))
			." <label for=\"sync_Y\">".GetMessage('WSL_STEP2_GSYNC_Y')."</label></li>";
		$this->content .= '<li>'.$this->ShowRadioField("sync", 'N', array("id" => "sync_N"))
			." <label for=\"sync_N\">".GetMessage('WSL_STEP2_GSYNC_N')."</label></li>";
		$this->content .= '</ul>';
		$this->content .= '<p><small>'.GetMessage('WSL_STEP2_GSYNC_HINT').'</small></p>';
	}
	
	function OnPostForm()
	{
		$wizard =& $this->GetWizard();
		
		if ($wizard->IsNextButtonClick() || $wizard->IsFinishButtonClick())
		{
			$locations_csv = $wizard->GetVar('locations_csv');
			$load_loc = $wizard->GetVar($locations_csv.'_load_loc');
			$load_zip = $wizard->GetVar($locations_csv.'_load_zip');
			
			if ($load_loc != 'Y' && $load_zip != 'Y')
				$this->SetError(GetMessage('WSL_STEP2_GFILE_ERROR'), 'locations_csv');
		}
	}
}

class Step5 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('WSL_STEP5_TITLE'));
		$this->SetNextStep("step3");
		$this->SetPrevStep("step2");		
		$this->SetStepID("step2_params");
		$this->SetCancelStep("cancel");
	}

	function ShowStep()
	{
		$wizard = &$this->GetWizard();
		$wizard->SetDefaultVars(
			Array(
				"step_length" => 20,
			)
		);
	
		$this->content = '';
		$this->content .= '<p>'.GetMessage('WSL_STEP5_STEP_LENGTH_TITLE').": ".$this->ShowInputField("text", "step_length", Array("size" => "20")).'</p>';
		$this->content .= '<p><small>'.GetMessage('WSL_STEP5_STEP_LENGTH_HINT').'</small></p>';
	}
	
	function OnPostForm()
	{
		$wizard = &$this->GetWizard();
	
		if ($wizard->IsNextButtonClick())
		{
			$step_length = intval($wizard->GetVar("step_length"));

			if ($step_length <= 0)
				$this->SetError(GetMessage('WSL_STEP5_STEP_LENGTH_ERROR'), "step_length");
		}
	}
}

class Step3 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('WSL_STEP3_TITLE'));
		$this->SetNextStep("step4");
		$this->SetPrevStep("step2_params");		
		$this->SetStepID("step3");
		$this->SetCancelStep("cancel");
	}

	function ShowStep()
	{
		$wizard =& $this->GetWizard();
		$locations_csv = $wizard->GetVar('locations_csv');
		$bLoadLoc = $wizard->GetVar($locations_csv.'_load_loc');
		$bLoadZip = $wizard->GetVar($locations_csv.'_load_zip');		
		$path = $wizard->package->path;

		$this->content .= '<div style="padding: 17px;">';
		$this->content .= '<div id="output"></div>';
		$this->content .= '<div id="wait_message" style="display: none;"></div>';
		$this->content .= '<div id="error_message" style="display: none;"><br /><button onclick="RunAgain(); return false">'.GetMessage('WSL_STEP3_ERROR_TRY').'</button></div>';
		$this->content .= '</div>';
		$this->content .= '<script language="JavaScript" src="'.$path.'/js/import.js"></script>';
		$this->content .= '<script language="JavaScript">

var nextButtonID = "'.$wizard->GetNextButtonID().'";
var formID = "'.$wizard->GetFormName().'";
var ajaxMessages = {wait:\''.GetMessage('WSL_STEP3_LOADING').' <img src="'.$path.'/images/loading.gif">\'};
var obImageCache = new Image();
obImageCache.src = \''.$path.'/images/loading.gif\';
var LANG = \''.LANG.'\';
var filename = "'.CUtil::JSEscape($locations_csv).'";
var load_loc = "'.($bLoadLoc == 'Y' ? 'Y' : 'N').'";
var load_zip = "'.($bLoadZip == 'Y' ? 'Y' : 'N').'";
var path = "'.CUtil::JSEscape($path).'";

if (window.addEventListener) 
{
	window.addEventListener("load", DisableButton, false);
	window.addEventListener("load", Run, false);
}
else if (window.attachEvent) 
{
	window.attachEvent("onload", DisableButton);
	window.attachEvent("onload", Run);
}
</script>';
	}
}

class Step4 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('WSL_STEP4_TITLE'));
		$this->SetNextStep("final");
		$this->SetStepID("step4");
		//$this->SetFinishStep("final");
	}
	
	function ShowStep()
	{
		unset($_SESSION["ZIP_POS"]);	
		unset($_SESSION["LOC_POS"]);
	
		$wizard =& $this->GetWizard();
		$locations_csv = $wizard->GetVar('locations_csv');
		$bLoadLoc = $wizard->GetVar($locations_csv.'_load_loc');
		$bLoadZip = $wizard->GetVar($locations_csv.'_load_zip');
		
		$bSync = $wizard->GetVar('sync');
		$step_length = intval($wizard->GetVar('step_length'));
		$path = $wizard->package->path;
	
		$this->content = '';
		$this->content .= '<div style="padding: 20px;">';
		$this->content .= '<div id="progress" style="height: 20px; width: 500px;"></div>';
		$this->content .= '<div id="wait_message" style="display: none;"></div>';
		$this->content .= '<div id="output"><br /></div>';
		$this->content .= '</div>';
		$this->content .= '<script type="text/javascript" src="'.$path.'/js/import.js"></script>';
		$this->content .= '<script type="text/javascript">

var nextButtonID = "'.$wizard->GetNextButtonID().'";
var formID = "'.$wizard->GetFormName().'";
var ajaxMessages = {wait:\''.GetMessage('WSL_STEP4_LOADING').'\'};
var LANG = \''.LANG.'\';
var filename = "'.CUtil::JSEscape($locations_csv).'";
var load_loc = "'.($bLoadLoc == 'Y' ? 'Y' : 'N').'";
var load_zip = "'.($bLoadZip == 'Y' ? 'Y' : 'N').'";
var sync = "'.($bSync == 'Y' ? 'Y' : 'N').'";
var path = "'.CUtil::JSEscape($path).'";
var step_length = "'.$step_length.'";

if (window.addEventListener) 
{
	window.addEventListener("load", Import, false);
	window.addEventListener("load", DisableButton, false);
}
else if (window.attachEvent) 
{
	window.attachEvent("onload", Import);
	window.attachEvent("onload", DisableButton);
}
</script>';
	}
	
	function OnPostForm()
	{
		$wizard = &$this->GetWizard();
	
		if ($wizard->IsNextButtonClick())
		{
			$path = dirname(__FILE__);
			$path = strtolower(str_replace("\\", '/', $path));
		
			$locations_csv = $wizard->GetVar('locations_csv');
			$bLoadLOC = $wizard->GetVar($locations_csv.'_load_zip');
			$bLoadZIP = $wizard->GetVar($locations_csv.'_load_zip');
			
			if ($bLoadLOC == 'Y' && file_exists($path.'/upload/loc_'.$locations_csv.'.csv'))
			{
				@unlink($path.'/upload/loc_'.$locations_csv.'.csv');
			}
			
			if ($bLoadZIP == "Y" && file_exists($path.'/upload/zip_'.$locations_csv.'.csv'))
			{
				@unlink($path.'/upload/zip_'.$locations_csv.'.csv');
			}
		}
	}
}

class FinalStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('WSL_FINALSTEP_TITLE'));
		$this->SetStepID("final");
		$this->SetCancelStep("final");
		$this->SetCancelCaption(GetMessage('WSL_FINALSTEP_BUTTONTITLE'));
	}

	function ShowStep()
	{
		CModule::IncludeModule('sale');
		
		$rsLocations = CSaleLocation::GetList(array(), array(), array("COUNTRY_ID", "COUNT" => "CITY_ID"));

		$numLocations = 0;
		$numCountries = 0;
		$numCities = 0;

		while ($arStat = $rsLocations->Fetch())
		{
			$numCountries++;
			$numCities += $arStat["CITY_ID"];
			$numLocations += $arStat['CNT'];
		}
	
		$this->content = '<p>'.GetMessage('WSL_FINALSTEP_CONTENT').'</p>';
		
		$this->content .= '<div style="margin-top: 20px; padding: 5px; font-size: 80%; border: solid 1px #CCCCCC;">';

		$locations_stats = GetMessage('WSL_FINALSTEP_LOC_STATS');
		$locations_stats = str_replace(
			array("#NUMCOUNTRIES#", "#NUMCITIES#", "#NUMLOCATIONS#"),
			array($numCountries, $numCities, $numLocations),
			$locations_stats
		);

		$this->content .= $locations_stats;
		
		$arZIPStats = CSaleLocation::_GetZIPImportStats();
		
		$zip_stats = GetMessage('WSL_FINALSTEP_ZIP_STATS');
		$zip_stats = str_replace(
			array("#NUMZIP#", "#NUMCITIES#"),
			array($arZIPStats['CNT'], $arZIPStats['CITY_CNT']),
			$zip_stats
		);
		
		$this->content .= $zip_stats;
		$this->content .= '</div>';
	}
}

class CancelStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('WSL_CANCELSTEP_TITLE'));
		$this->SetStepID("cancel");
		$this->SetCancelStep("cancel");
		$this->SetCancelCaption(GetMessage('WSL_CANCELSTEP_BUTTONTITLE'));
	}

	function ShowStep()
	{
		$this->content = GetMessage('WSL_CANCELSTEP_CONTENT');
	}
}
?>