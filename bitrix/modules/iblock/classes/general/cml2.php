<?
IncludeModuleLangFile(__FILE__);

function ImportXMLFile($file_name, $iblock_type="-", $site_id=false, $section_action="D", $element_action="D", $use_crc=false, $preview=false)
{
	global $APPLICATION;

	$ABS_FILE_NAME = false;
	$WORK_DIR_NAME = false;
	if(strlen($file_name)>0)
	{
		$filename = trim(str_replace("\\", "/", trim($file_name)), "/");
		$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"].$DIR_NAME, "/".$filename);
		if((strlen($FILE_NAME) > 1) && ($FILE_NAME === "/".$filename) && ($APPLICATION->GetFileAccessPermission($FILE_NAME) >= "W"))
		{
			$ABS_FILE_NAME = $_SERVER["DOCUMENT_ROOT"].$DIR_NAME.$FILE_NAME;
			$WORK_DIR_NAME = substr($ABS_FILE_NAME, 0, strrpos($ABS_FILE_NAME, "/")+1);
		}
	}

	if(!$ABS_FILE_NAME)
		return GetMessage("IBLOCK_XML2_FILE_ERROR");

	$NS = array("STEP"=>0);
	$obXMLFile = new CIBlockXMLFile;

	$obXMLFile->DropTemporaryTables();

	if(!$obXMLFile->CreateTemporaryTables())
		return GetMessage("IBLOCK_XML2_TABLE_CREATE_ERROR");

	if($fp = fopen($ABS_FILE_NAME, "rb"))
	{
		$obXMLFile->ReadXMLToDatabase($fp, $NS, 0);
		fclose($fp);
	}
	else
	{
		return GetMessage("IBLOCK_XML2_FILE_ERROR");
	}

	if(!CIBlockXMLFile::IndexTemporaryTables())
		return GetMessage("IBLOCK_XML2_INDEX_ERROR");

	$obCatalog = new CIBlockCMLImport;
	$obCatalog->Init($NS, $WORK_DIR_NAME, $use_crc, $preview);
	$result = $obCatalog->ImportMetaData(1, $iblock_type, $site_id);
	if($result !== true)
	{
		return GetMessage("IBLOCK_XML2_METADATA_ERROR").implode("\n", $result);
	}
	$obCatalog->ImportSections();
	$obCatalog->DeactivateSections($section_action);
	$obCatalog->SectionsResort();

	$obCatalog = new CIBlockCMLImport;
	$obCatalog->Init($NS, $WORK_DIR_NAME, $use_crc, $preview);
	$SECTION_MAP = false;
	$PRICES_MAP = false;
	$obCatalog->ReadCatalogData($SECTION_MAP, $PRICES_MAP);
	$result = $obCatalog->ImportElements(time(), 0);

	$obCatalog->DeactivateElement($element_action, time(), 0);

	return true;
}

/*
This class is used to parse and load an xml file into database table.
*/
class CAllIBlockXMLFile
{
	var $charset = false;
	var $element_stack = false;
	var $file_position = 0;

	var $read_size = 1024;
	var $buf = "";
	var $buf_position = 0;
	var $buf_len = 0;

	/*
	This function have to called once at the import start.

	return : result of the CDatabase::Query method
	We use drop due to mysql innodb slow truncate bug.
	*/
	function DropTemporaryTables()
	{
		global $DB;
		//This seq drop is Oracle only
		$DB->Query("drop sequence sq_b_xml_tree", true);
		if($DB->TableExists("b_xml_tree") || $DB->TableExists("B_XML_TREE"))
			return $DB->Query("drop table b_xml_tree");
		else
			return true;
	}

	/*
	This function indexes contents of the loaded data for future lookups.
	May be called after tables creation and loading will perform slowly.
	But it is recommented to call this function after all data load.
	This is much faster.

	return : result of the CDatabase::Query method
	*/
	function IndexTemporaryTables()
	{
		global $DB;
		if(!$DB->IndexExists("b_xml_tree", array("PARENT_ID")))
			$res = $DB->Query("CREATE INDEX ix_b_xml_tree_parent on b_xml_tree(PARENT_ID)");
		if($res && !$DB->IndexExists("b_xml_tree", array("LEFT_MARGIN")))
			$res = $DB->Query("CREATE INDEX ix_b_xml_tree_left on b_xml_tree(LEFT_MARGIN)");
		return $res;
	}

	function GetFilePosition()
	{
		return $this->file_position;
	}

	/*
	Reads portion of xml data.

	hFile - file handle opened with fopen function for reading
	NS - will be populated with to members
		charset parameter is used to recode file contents if needed.
		element_stack parameters save parsing stack of xml tree parents.
		file_position parameters marks current file position.
	time_limit - duration of one step in seconds.

	NS have to be preserved between steps.
	They automatically extracted from xml file and should not be modified!
	*/
	function ReadXMLToDatabase($fp, &$NS, $time_limit=0, $read_size = 1024)
	{
		global $APPLICATION;

		//Initialize object
		if(!array_key_exists("charset", $NS))
			$NS["charset"] = false;
		$this->charset = &$NS["charset"];

		if(!array_key_exists("element_stack", $NS))
			$NS["element_stack"] = array();
		$this->element_stack = &$NS["element_stack"];

		if(!array_key_exists("file_position", $NS))
			$NS["file_position"] = 0;
		$this->file_position = &$NS["file_position"];

		$this->read_size = $read_size;
		$this->buf = "";
		$this->buf_position = 0;
		$this->buf_len = 0;

		//This is an optimization. We assume than no step can take more than one year.
		if($time_limit > 0)
			$end_time = time() + $time_limit;
		else
			$end_time = time() + 365*24*3600; // One year

		$cs = $this->charset;
		$bMB = defined("BX_UTF");
		fseek($fp, $this->file_position);
		while(($xmlChunk = $this->_get_xml_chunk($fp, $bMB)) !== false)
		{
			if($cs)
			{
				$xmlChunk = $APPLICATION->ConvertCharset($xmlChunk, $cs, LANG_CHARSET);
			}

			if($xmlChunk[0] == "/")
			{
				$this->_end_element($xmlChunk);
				if(time() > $end_time)
					break;
			}
			elseif($xmlChunk[0] == "!" || $xmlChunk[0] == "?")
			{
				if(substr($xmlChunk, 0, 4) === "?xml")
				{
					if(preg_match('#encoding[\s]*=[\s]*"(.*?)"#i', $xmlChunk, $arMatch))
					{
						$this->charset = $arMatch[1];
						if(strtoupper($this->charset) === strtoupper(LANG_CHARSET))
							$this->charset = false;
						$cs = $this->charset;
					}
				}
			}
			else
			{
				$this->_start_element($xmlChunk);
			}

		}

		return feof($fp);
	}

	/*
	Internal function.
	Used to read an xml by chunks started with "<" and endex with "<"
	*/
	function _get_xml_chunk($fp, $bMB = false)
	{
		if($this->buf_position >= $this->buf_len)
		{
			if(!feof($fp))
			{
				$this->buf = fread($fp, $this->read_size);
				$this->buf_position = 0;
				$this->buf_len = $bMB? mb_strlen($this->buf, 'latin1'): strlen($this->buf);
			}
			else
				return false;
		}

		//Skip line delimiters (ltrim)
		$xml_position = $bMB? mb_strpos($this->buf, "<", $this->buf_position, 'latin1'): strpos($this->buf, "<", $this->buf_position);
		while($xml_position === $this->buf_position)
		{
			$this->buf_position++;
			$this->file_position++;
			//Buffer ended with white space so we can refill it
			if($this->buf_position >= $this->buf_len)
			{
				if(!feof($fp))
				{
					$this->buf = fread($fp, $this->read_size);
					$this->buf_position = 0;
					$this->buf_len = $bMB? mb_strlen($this->buf, 'latin1'): strlen($this->buf);
				}
				else
					return false;
			}
			$xml_position = $bMB? mb_strpos($this->buf, "<", $this->buf_position, 'latin1'): strpos($this->buf, "<", $this->buf_position);
		}

		//Let's find next line delimiter
		while($xml_position===false)
		{
			$next_search = $this->buf_len;
			//Delimiter not in buffer so try to add more data to it
			if(!feof($fp))
			{
				$this->buf .= fread($fp, $this->read_size);
				$this->buf_len = $bMB? mb_strlen($this->buf, 'latin1'): strlen($this->buf);
			}
			else
				break;

			//Let's find xml tag start
			$xml_position = $bMB? mb_strpos($this->buf, "<", $next_search, 'latin1'): strpos($this->buf, "<", $next_search);
		}
		if($xml_position===false)
			$xml_position = $this->buf_len+1;

		$len = $xml_position-$this->buf_position;
		$this->file_position += $len;
		$result = $bMB? mb_substr($this->buf, $this->buf_position, $len, 'latin1'): substr($this->buf, $this->buf_position, $len);
		$this->buf_position = $xml_position;

		return $result;
	}

	/*
	Internal function.
	Stores an element into xml database tree.
	*/
	function _start_element($xmlChunk)
	{
		global $DB;
		static $search = array(
				"'&(quot|#34);'i",
				"'&(lt|#60);'i",
				"'&(gt|#62);'i",
				"'&(amp|#38);'i",
			);

		static $replace = array(
				"\"",
				"<",
				">",
				"&",
			);

		$p = strpos($xmlChunk, ">");
		if($p !== false)
		{
			if(substr($xmlChunk, $p - 1, 1)=="/")
			{
				$bHaveChildren = false;
				$elementName = substr($xmlChunk, 0, $p-1);
				$DBelementValue = false;
			}
			else
			{
				$bHaveChildren = true;
				$elementName = substr($xmlChunk, 0, $p);
				$elementValue = substr($xmlChunk, $p+1);
				if(preg_match("/^\s*$/", $elementValue))
					$DBelementValue = false;
				elseif(strpos($elementValue, "&")===false)
					$DBelementValue = $elementValue;
				else
					$DBelementValue = preg_replace($search, $replace, $elementValue);
			}

			if(($ps = strpos($elementName, " "))!==false)
			{
				//Let's handle attributes
				$elementAttrs = substr($elementName, $ps+1);
				$elementName = substr($elementName, 0, $ps);
				preg_match_all("/(\\S+)\\s*=\\s*[\"](.*?)[\"]/s", $elementAttrs, $attrs_tmp);
				$attrs = array();
				if(strpos($elementAttrs, "&")===false)
				{
					foreach($attrs_tmp[1] as $i=>$attrs_tmp_1)
						$attrs[$attrs_tmp_1] = $attrs_tmp[2][$i];
				}
				else
				{
					foreach($attrs_tmp[1] as $i=>$attrs_tmp_1)
						$attrs[$attrs_tmp_1] = preg_replace($search, $replace, $attrs_tmp[2][$i]);
				}
				$DBelementAttrs = serialize($attrs);
			}
			else
				$DBelementAttrs = false;

			if($c = count($this->element_stack))
				$parent = $this->element_stack[$c-1];
			else
				$parent = array("ID"=>"NULL", "L"=>0, "R"=>1);

			$left = $parent["R"];
			$right = $left+1;

			$arFields = array(
				"~PARENT_ID" => $parent["ID"],
				"~LEFT_MARGIN" => $left,
				"~RIGHT_MARGIN" => $right,
				"~DEPTH_LEVEL" => $c,
				"NAME" => $elementName,
			);
			if($DBelementValue !== false)
			{
				$arFields["VALUE"] = $DBelementValue;
			}
			if($DBelementAttrs !== false)
			{
				$arFields["ATTRIBUTES"] = $DBelementAttrs;
			}

			$ID = $this->Add($arFields);

			if($bHaveChildren)
				$this->element_stack[] = array("ID"=>$ID, "L"=>$left, "R"=>$right, "RO"=>$right);
			else
				$this->element_stack[$c-1]["R"] = $right+1;
		}
	}

	/*
	Internal function.
	Winds tree stack back. Modifies (if neccessary) internal tree structure.
	*/
	function _end_element($xmlChunk)
	{
		global $DB;

		$child = array_pop($this->element_stack);
		$this->element_stack[count($this->element_stack)-1]["R"] = $child["R"]+1;
		if($child["R"] != $child["RO"])
			$DB->Query("UPDATE b_xml_tree SET RIGHT_MARGIN = ".$child["R"]." WHERE ID = ".$child["ID"]);
	}

	/*
	Returns an associative array of the part of xml tree.
	Elements with same name on the same level gets an additional suffix.
	For example
		<a>
			<b>123</b>
			<b>456</b>
		<a>
	will return
		array(
			"a => array(
				"b" => "123",
				"b1" => "456",
			),
		);
	*/
	function GetAllChildrenArray($arParent)
	{
		global $DB;

		//We will return
		$arResult = array();

		//So we get not parent itself but xml_id
		if(!is_array($arParent))
		{
			$rs = $DB->Query("select ID, LEFT_MARGIN, RIGHT_MARGIN from b_xml_tree where ID = ".intval($arParent));
			$arParent = $rs->Fetch();
			if(!$arParent)
				return $arResult;
		}

		//Array of the references to the arResult array members with xml_id as index.
		$arIndex = array();
		$rs = $DB->Query("select * from b_xml_tree where LEFT_MARGIN between ".($arParent["LEFT_MARGIN"]+1)." and ".($arParent["RIGHT_MARGIN"]-1)." order by ID");
		while($ar = $rs->Fetch())
		{
			if(isset($ar["VALUE_CLOB"]))
				$ar["VALUE"] = $ar["VALUE_CLOB"];
			if($ar["PARENT_ID"] == $arParent["ID"])
			{
				if(array_key_exists($ar["NAME"], $arResult))
				{
					$salt = 1;
					while(array_key_exists($ar["NAME"].$salt, $arResult))
						$salt++;
					$ar["NAME"].=$salt;
				}
				$arResult[$ar["NAME"]] = $ar["VALUE"];
				$arIndex[$ar["ID"]] = &$arResult[$ar["NAME"]];
			}
			else
			{
				$parent_id = $ar["PARENT_ID"];
				if(!is_array($arIndex[$parent_id]))
					$arIndex[$parent_id] = array();
				if(array_key_exists($ar["NAME"], $arIndex[$parent_id]))
				{
					$salt = 1;
					while(array_key_exists($ar["NAME"].$salt, $arIndex[$parent_id]))
						$salt++;
					$ar["NAME"].=$salt;
				}
				$arIndex[$parent_id][$ar["NAME"]] = $ar["VALUE"];
				$arIndex[$ar["ID"]] = &$arIndex[$parent_id][$ar["NAME"]];
			}
		}

		return $arResult;
	}

	function UnZip($file_name, $last_zip_entry = "", $start_time = 0, $interval = 0)
	{
		//Function and securioty checks
		if(!function_exists("zip_open"))
			return false;
		$dir_name = substr($file_name, 0, strrpos($file_name, "/")+1);
		if(strlen($dir_name) <= strlen($_SERVER["DOCUMENT_ROOT"]))
			return false;

		$hZip = zip_open($file_name);
		if(!$hZip)
			return false;
		//Skip from last step
		if($last_zip_entry)
		{
			while($entry = zip_read($hZip))
				if(zip_entry_name($entry) == $last_zip_entry)
					break;
		}

		//Continue unzip
		while($entry = zip_read($hZip))
		{
			$entry_name = zip_entry_name($entry);
			//Check for directory
			zip_entry_open($hZip, $entry);
			if(zip_entry_filesize($entry))
			{
				$file_name = $dir_name.$entry_name;
				CheckDirPath($file_name);
				$fout = fopen($file_name, "wb");
				if(!$fout)
					return false;
				while($data = zip_entry_read($entry, 102400))
				{
					$result = fwrite($fout, $data);
					if($result !== strlen($data))
						return false;
				}
			}
			zip_entry_close($entry);

			//Jump to next step
			if($interval > 0 && (time()-$start_time) > ($interval))
			{
				zip_close($hZip);
				return $entry_name;
			}
		}
		zip_close($hZip);
		return true;
	}
}

class CIBlockCMLImport
{
	var $next_step = false;
	var $files_dir = false;
	var $use_offers = true;
	var $use_iblock_type_id = false;
	var $use_crc = true;
	var $preview = false;
	var $detail = false;
	var $bCatalog = false;
	var $PROPERTY_MAP = false;
	var $SECTION_MAP = false;
	var $PRICES_MAP = false;
	var $arProperties = false;
	var $arSectionCache = array();
	var $arElementCache = array();
	var $arEnumCache = array();
	var $arCurrencyCache = array();
	var $arTaxCache = array();

	var $arTempFiles = array();
	var $arLinkedProps = false;

	function Init(&$next_step, $files_dir = false, $use_crc = true, $preview = false, $detail = false, $use_offers = false, $use_iblock_type_id = false)
	{
		$this->next_step = &$next_step;
		$this->files_dir = $files_dir;
		$this->use_offers = $use_offers;
		$this->use_iblock_type_id = $use_iblock_type_id;
		$this->use_crc = $use_crc;
		if(is_array($preview) && count($preview)==2)
			$this->preview = $preview;
		else
			$this->preview = false;
		if(is_array($detail) && count($detail)==2)
			$this->detail = $detail;
		else
			$this->detail = false;
		$this->bCatalog = CModule::IncludeModule('catalog');
		$this->arProperties = array();
		$this->PROPERTY_MAP = array();
		$obProperty = new CIBlockProperty;
		$rsProperties = $obProperty->GetList(array(), array("IBLOCK_ID"=>$this->next_step["IBLOCK_ID"], "ACTIVE"=>"Y"));
		while($arProperty = $rsProperties->Fetch())
		{
			$this->PROPERTY_MAP[$arProperty["XML_ID"]] = $arProperty["ID"];
			$this->arProperties[$arProperty["ID"]] = $arProperty;
		}
		$this->arTempFiles = array();
		$this->arLinkedProps = false;
	}

	function CleanTempFiles()
	{
		foreach($this->arTempFiles as $file)
			@unlink($file);
		$this->arTempFiles = array();
	}

	function MakeFileArray($file)
	{
		if((strlen($file)>0) && is_file($this->files_dir.$file))
			return CFile::MakeFileArray($this->files_dir.$file);
		else
			return array("tmp_name"=>"", "del"=>"Y");
	}

	function ResizePicture($file, $resize)
	{
		if(strlen($file) <= 0)
			return array("tmp_name"=>"", "del"=>"Y");

		if(file_exists($this->files_dir.$file) && is_file($this->files_dir.$file))
			$file = $this->files_dir.$file;
		elseif(file_exists($file) && is_file($file))
			$file = $file;
		else
			return array("tmp_name"=>"", "del"=>"Y");

		if(!is_array($resize))
			return CFile::MakeFileArray($file);

		$width = $resize[0];
		$height = $resize[1];

		$orig = @getimagesize($file);
		if(is_array($orig) && (($orig[0] > $resize[0]) || ($orig[1] > $resize[1])))
		{
			$width_orig = $orig[0];
			$height_orig = $orig[1];
			if($width && ($width_orig < $height_orig))
				$width = ($height / $height_orig) * $width_orig;
			else
				$height = ($width / $width_orig) * $height_orig;

			$image_type = $orig[2];
			if($image_type == IMAGETYPE_JPEG)
				$image = imagecreatefromjpeg($file);
			elseif($image_type == IMAGETYPE_GIF)
				$image = imagecreatefromgif($file);
			elseif($image_type == IMAGETYPE_PNG)
				$image = imagecreatefrompng($file);
			else
				$image = false;

			if($image)
			{
				$image_p = imagecreatetruecolor($width, $height);
				if($image_type == IMAGETYPE_JPEG)
				{
					imagecopyresized($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

					$i = 1;
					while(file_exists($file.".resize".$i.".jpg"))
						$i++;
					$new_file = $file.".resize".$i.".jpg";

					imagejpeg($image_p, $new_file);
				}
				elseif($image_type == IMAGETYPE_GIF && function_exists("imagegif"))
				{
					imagetruecolortopalette($image_p, true, imagecolorstotal($image));
					imagepalettecopy($image_p, $image);

					//Save transparency for GIFs
					$transparentcolor = imagecolortransparent($image);
					if($transparentcolor >= 0 && $transparentcolor < imagecolorstotal($image))
					{
						$transparentcolor = imagecolortransparent($image_p, $transparentcolor);
						imagefilledrectangle($image_p, 0, 0, $width, $height, $transparentcolor);
					}
					imagecopyresized($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

					$i = 1;
					while(file_exists($file.".resize".$i.".gif"))
						$i++;
					$new_file = $file.".resize".$i.".gif";

					imagegif($image_p, $new_file);
				}
				else
				{
					//Save transparency for PNG
					$transparentcolor = imagecolorallocate($image_p, 0, 0, 0);
					$transparentcolor = imagecolortransparent($image_p, $transparentcolor);

					imagecopyresized($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

					$i = 1;
					while(file_exists($file.".resize".$i.".png"))
						$i++;
					$new_file = $file.".resize".$i.".png";

					imagepng($image_p, $new_file);
				}

				$this->arTempFiles[] = $new_file;

				imagedestroy($image);
				imagedestroy($image_p);

				return CFile::MakeFileArray($new_file);
			}
			else
			{
				return array("tmp_name"=>"", "del"=>"Y");
			}
		}
		else
		{
			return CFile::MakeFileArray($file);
		}

	}

	function GetIBlockByXML_ID($XML_ID)
	{
		if(strlen($XML_ID) > 0)
		{
			$obIBlock = new CIBlock;
			$rsIBlock = $obIBlock->GetList(array(), array("XML_ID"=>$XML_ID));
			if($arIBlock = $rsIBlock->Fetch())
				return $arIBlock["ID"];
			else
				return false;
		}
		return false;
	}

	function GetSectionByXML_ID($IBLOCK_ID, $XML_ID)
	{
		if(!array_key_exists($IBLOCK_ID, $this->arSectionCache))
			$this->arSectionCache[$IBLOCK_ID] = array();
		if(!array_key_exists($XML_ID, $this->arSectionCache[$IBLOCK_ID]))
		{
			$obSection = new CIBlockSection;
			$rsSection = $obSection->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "EXTERNAL_ID"=>$XML_ID));
			if($arSection = $rsSection->Fetch())
				$this->arSectionCache[$IBLOCK_ID][$XML_ID] = $arSection["ID"];
			else
				$this->arSectionCache[$IBLOCK_ID][$XML_ID] = false;
		}
		return $this->arSectionCache[$IBLOCK_ID][$XML_ID];
	}

	function GetElementByXML_ID($IBLOCK_ID, $XML_ID)
	{
		if(!array_key_exists($IBLOCK_ID, $this->arElementCache))
			$this->arElementCache[$IBLOCK_ID] = array();
		if(!array_key_exists($XML_ID, $this->arElementCache[$IBLOCK_ID]))
		{
			$obElement = new CIBlockElement;
			$rsElement = $obElement->GetList(
					Array("ID"=>"asc"),
					Array("=XML_ID" => $XML_ID, "IBLOCK_ID" => $IBLOCK_ID),
					false, false,
					Array("ID", "XML_ID")
			);
			if($arElement = $rsElement->Fetch())
				$this->arElementCache[$IBLOCK_ID][$XML_ID] = $arElement["ID"];
			else
				$this->arElementCache[$IBLOCK_ID][$XML_ID] = false;
		}
		return $this->arElementCache[$IBLOCK_ID][$XML_ID];
	}

	function GetEnumByXML_ID($IBLOCK_ID, $XML_ID)
	{
		if(!array_key_exists($IBLOCK_ID, $this->arEnumCache))
			$this->arEnumCache[$IBLOCK_ID] = array();
		if(!array_key_exists($XML_ID, $this->arEnumCache[$IBLOCK_ID]))
		{
			$rsEnum = CIBlockPropertyEnum::GetList(
					Array(),
					Array("EXTERNAL_ID" => $XML_ID, "IBLOCK_ID" => $IBLOCK_ID)
			);
			if($arEnum = $rsEnum->Fetch())
				$this->arEnumCache[$IBLOCK_ID][$XML_ID] = $arEnum["ID"];
			else
				$this->arEnumCache[$IBLOCK_ID][$XML_ID] = false;
		}
		return $this->arEnumCache[$IBLOCK_ID][$XML_ID];
	}

	function GetPropertyByXML_ID($IBLOCK_ID, $XML_ID)
	{
		$obProperty = new CIBlockProperty;
		$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$XML_ID));
		if($arProperty = $rsProperty->Fetch())
			return $arProperty["ID"];
		else
			return false;
	}

	function CheckProperty($IBLOCK_ID, $code, $xml_name)
	{
		$obProperty = new CIBlockProperty;
		$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$code));
		if(!$rsProperty->Fetch())
		{
			$arProperty = array(
				"IBLOCK_ID" => $IBLOCK_ID,
				"NAME" => is_array($xml_name)? $xml_name["NAME"]: $xml_name,
				"CODE" => $code,
				"XML_ID" => $code,
				"MULTIPLE" => "N",
				"PROPERTY_TYPE" => "S",
				"ACTIVE" => "Y",
			);
			if(is_array($xml_name))
			{
				foreach($xml_name as $name => $value)
					$arProperty[$name] = $value;
			}
			$ID = $obProperty->Add($arProperty);
			if(!$ID)
				return $obProperty->LAST_ERROR;
		}
		return true;
	}

	function CheckTax($title, $rate)
	{
		$tax_name = $title." ".$rate."%";
		if(!array_key_exists($tax_name, $this->arTaxCache))
		{
			$rsVat = CCatalogVat::GetList(array('CSORT' => 'ASC'), array("NAME" => $tax_name, "NAME_EXACT_MATCH" => "Y", "RATE" => $rate), array("ID"));
			if($arVat = $rsVat->Fetch())
				$this->arTaxCache[$tax_name] = $arVat["ID"];
			else
				$this->arTaxCache[$tax_name] = CCatalogVat::Set(array(
					"ACTIVE" => "Y",
					"NAME" => $tax_name,
					"RATE" => $rate,
				));
		}
		return $this->arTaxCache[$tax_name];
	}

	function CheckCurrency($currency)
	{
		if($currency==GetMessage("IBLOCK_XML2_RUB"))
			$currency="RUB";
		if(!array_key_exists($currency, $this->arCurrencyCache))
		{
			if($this->bCatalog && CModule::IncludeModule('currency'))
			{
				CCurrency::Add(array(
					"CURRENCY" => $currency,
				));
			}
			$this->arCurrencyCache[$currency] = true;
		}
		return $currency;
	}

	function CheckIBlockType($ID)
	{
		$obType = new CIBlockType;
		$rsType = $obType->GetByID($ID);
		if($arType = $rsType->Fetch())
		{
			return $arType["ID"];
		}
		else
		{
			$rsType = $obType->GetByID("1c_catalog");
			if($arType = $rsType->Fetch())
			{
				return $arType["ID"];
			}
			else
			{
				$result = $obType->Add(array(
					"ID" => "1c_catalog",
					"SECTIONS" => "Y",
					"LANG" => array(
						"ru" => array(
							"NAME" => GetMessage("IBLOCK_XML2_CATALOG_NAME"),
							"SECTION_NAME" => GetMessage("IBLOCK_XML2_CATALOG_SECTION_NAME"),
							"ELEMENT_NAME" => GetMessage("IBLOCK_XML2_CATALOG_ELEMENT_NAME"),
						),
					),
				));
				if($result)
					return $result;
				else
					return false;
			}
		}
	}

	function CheckSites($arSite)
	{
		$arResult = array();
		if(!is_array($arSite))
			$arSite = array($arSite);
		foreach($arSite as $site_id)
		{
			$rsSite = CSite::GetByID($site_id);
			if($rsSite->Fetch())
				$arResult[] = $site_id;
		}
		if(!defined("ADMIN_SECTION"))
		{
			$rsSite = CSite::GetByID(SITE_ID);
			if($rsSite->Fetch())
				$arResult[] = SITE_ID;
		}
		if(count($arResult)<1)
			$arResult[] = CSite::GetDefSite();
		return $arResult;
	}

	function ImportMetaData($xml_root_id, $IBLOCK_TYPE, $IBLOCK_LID)
	{
		global $DB;

		$meta_data_xml_id = false;
		$XML_ELEMENTS_PARENT = false;
		$XML_SECTIONS_PARENT = false;
		$XML_PROPERTIES_PARENT = false;
		$XML_PRICES_PARENT = false;

		$this->next_step["bOffer"] = false;
		$rs = $DB->Query("select ID, ATTRIBUTES from b_xml_tree where PARENT_ID = ".intval($xml_root_id)." and NAME='".GetMessage("IBLOCK_XML2_CATALOG")."'");
		$ar = $rs->Fetch();
		if(!$ar)
		{
			$rs = $DB->Query("select ID, ATTRIBUTES from b_xml_tree where PARENT_ID = ".intval($xml_root_id)." and NAME='".GetMessage("IBLOCK_XML2_OFFER_LIST")."'");
			$ar = $rs->Fetch();
			$this->next_step["bOffer"] = true;
		}

		if($ar)
		{
			if(strlen($ar["ATTRIBUTES"]) > 0)
			{
				$attrs = unserialize($ar["ATTRIBUTES"]);
				if(is_array($attrs))
				{
					if(array_key_exists(GetMessage("IBLOCK_XML2_UPDATE_ONLY"), $attrs))
						$this->next_step["bUpdateOnly"] = ($attrs[GetMessage("IBLOCK_XML2_UPDATE_ONLY")]=="true") || intval($attrs["IBLOCK_XML2_UPDATE_ONLY"])? true: false;
				}
			}

			//Information block fields with following Add/Update
			$arIBlock = array(
			);
			$rs = $DB->Query("select * from b_xml_tree where PARENT_ID = ".$ar["ID"]." order by ID");
			while($ar = $rs->Fetch())
			{
				if(isset($ar["VALUE_CLOB"]))
					$ar["VALUE"] = $ar["VALUE_CLOB"];
				if($ar["NAME"] == GetMessage("IBLOCK_XML2_ID"))
					$arIBlock["XML_ID"] = ($this->use_iblock_type_id? $IBLOCK_TYPE."-": "").$ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_CATALOG_ID"))
					$arIBlock["CATALOG_XML_ID"] = ($this->use_iblock_type_id? $IBLOCK_TYPE."-": "").$ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_NAME"))
					$arIBlock["NAME"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_DESCRIPTION"))
				{
					$arIBlock["DESCRIPTION"] = $ar["VALUE"];
					$arIBlock["DESCRIPTION_TYPE"] = "html";
				}
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_POSITIONS") || $ar["NAME"] == GetMessage("IBLOCK_XML2_OFFERS"))
					$XML_ELEMENTS_PARENT = $ar["ID"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_PRICE_TYPES"))
					$XML_PRICES_PARENT = $ar["ID"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_METADATA_ID"))
					$meta_data_xml_id = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_UPDATE_ONLY"))
					$this->next_step["bUpdateOnly"] = ($ar["VALUE"]=="true") || intval($ar["VALUE"])? true: false;
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_CODE"))
					$arIBlock["CODE"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_SORT"))
					$arIBlock["SORT"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_LIST_URL"))
					$arIBlock["LIST_PAGE_URL"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_DETAIL_URL"))
					$arIBlock["DETAIL_PAGE_URL"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_SECTION_URL"))
					$arIBlock["SECTION_PAGE_URL"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_INDEX_ELEMENTS"))
					$arIBlock["INDEX_ELEMENT"] = ($ar["VALUE"]=="true") || intval($ar["VALUE"])? "Y": "N";
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_INDEX_SECTIONS"))
					$arIBlock["INDEX_SECTION"] = ($ar["VALUE"]=="true") || intval($ar["VALUE"])? "Y": "N";
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_SECTIONS_NAME"))
					$arIBlock["SECTIONS_NAME"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_SECTION_NAME"))
					$arIBlock["SECTION_NAME"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_ELEMENTS_NAME"))
					$arIBlock["ELEMENTS_NAME"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_ELEMENT_NAME"))
					$arIBlock["ELEMENT_NAME"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_PICTURE"))
					$arIBlock["PICTURE"] = $this->MakeFileArray($ar["VALUE"]);
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_WORKFLOW"))
					$arIBlock["WORKFLOW"] = ($ar["VALUE"]=="true") || intval($ar["VALUE"])? "Y": "N";
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_LABELS"))
				{
					$arLabels = CIBlockXMLFile::GetAllChildrenArray($ar["ID"]);
					foreach($arLabels as $key => $arLabel)
					{
						$id = $arLabel[GetMessage("IBLOCK_XML2_ID")];
						$label = $arLabel[GetMessage("IBLOCK_XML2_VALUE")];
						if(strlen($id) > 0 && strlen($label) > 0)
							$arIBlock[$id] = $label;
					}
				}
			}
			if($this->next_step["bOffer"] && !$this->use_offers)
			{
				if(strlen($arIBlock["CATALOG_XML_ID"]) > 0)
				{
					$arIBlock["XML_ID"] = $arIBlock["CATALOG_XML_ID"];
					$this->next_step["bUpdateOnly"] = true;
				}
			}

			$obIBlock = new CIBlock;
			$rsIBlocks = $obIBlock->GetList(array(), array("XML_ID"=>$arIBlock["XML_ID"]));
			$ar = $rsIBlocks->Fetch();

			//Also check for non bitrix xml file
			if(!$ar && !array_key_exists("CODE", $arIBlock))
			{
				if($this->next_step["bOffer"] && $this->use_offers)
					$rsIBlocks = $obIBlock->GetList(array(), array("XML_ID"=>"FUTURE-1C-OFFERS"));
				else
					$rsIBlocks = $obIBlock->GetList(array(), array("XML_ID"=>"FUTURE-1C-CATALOG"));
				$ar = $rsIBlocks->Fetch();
			}
			if($ar)
			{
				if(!$this->next_step["bOffer"] || $this->use_offers)
				{
					if($obIBlock->Update($ar["ID"], $arIBlock))
						$arIBlock["ID"] = $ar["ID"];
					else
						return $obIBlock->LAST_ERROR;
				}
				else
				{
					$arIBlock["ID"] = $ar["ID"];
				}
			}
			else
			{
				$arIBlock["IBLOCK_TYPE_ID"] = $this->CheckIBlockType($IBLOCK_TYPE);
				if(!$arIBlock["IBLOCK_TYPE_ID"])
					return GetMessage("IBLOCK_XML2_TYPE_ADD_ERROR");
				$arIBlock["GROUP_ID"] = array(2=>"R");
				$arIBlock["LID"] = $this->CheckSites($IBLOCK_LID);
				$arIBlock["ACTIVE"] = "Y";
				$arIBlock["WORKFLOW"] = "N";
				$arIBlock["ID"] = $obIBlock->Add($arIBlock);
				if(!$arIBlock["ID"])
					return $obIBlock->LAST_ERROR;
			}

			//Make this catalog
			if($this->bCatalog && $this->next_step["bOffer"])
			{
				$rs = CCatalog::GetList(array(),array("IBLOCK_ID"=>$arIBlock["ID"]));
				if(!($rs->Fetch()))
					CCatalog::Add(array("IBLOCK_ID"=>$arIBlock["ID"], "YANDEX_EXPORT"=>"N", "SUBSCRIPTION"=>"N"));
			}

			//For non bitrix xml file
			//Check for mandatory properties and add them as necessary
			if(!array_key_exists("CODE", $arIBlock))
			{
				$arProperties = array(
					"CML2_BAR_CODE" => GetMessage("IBLOCK_XML2_BAR_CODE"),
					"CML2_ARTICLE" => GetMessage("IBLOCK_XML2_ARTICLE"),
					"CML2_ATTRIBUTES" => array(
						"NAME" => GetMessage("IBLOCK_XML2_ATTRIBUTES"),
						"MULTIPLE" => "Y",
						"WITH_DESCRIPTION" => "Y",
						"MULTIPLE_CNT" => 1,
					),
					"CML2_TRAITS" => array(
						"NAME" => GetMessage("IBLOCK_XML2_TRAITS"),
						"MULTIPLE" => "Y",
						"WITH_DESCRIPTION" => "Y",
						"MULTIPLE_CNT" => 1,
					),
					"CML2_BASE_UNIT" => GetMessage("IBLOCK_XML2_BASE_UNIT_NAME"),
					"CML2_TAXES" => array(
						"NAME" => GetMessage("IBLOCK_XML2_TAXES"),
						"MULTIPLE" => "Y",
						"WITH_DESCRIPTION" => "Y",
						"MULTIPLE_CNT" => 1,
					),
				);
				foreach($arProperties as $k=>$v)
				{
					$result = $this->CheckProperty($arIBlock["ID"], $k, $v);
					if($result!==true)
						return $result;
				}
				//For offers make special property: link to catalog
				if(isset($arIBlock["CATALOG_XML_ID"]) && $this->use_offers)
					$result = $this->CheckProperty($arIBlock["ID"], "CML2_LINK", array(
						"NAME" => GetMessage("IBLOCK_XML2_CATALOG_ELEMENT"),
						"PROPERTY_TYPE" => "E",
						"LINK_IBLOCK_ID" => $this->GetIBlockByXML_ID($arIBlock["CATALOG_XML_ID"]),
						"FILTRABLE" => "Y",
					));
			}

			$this->next_step["IBLOCK_ID"] = $arIBlock["ID"];
			$this->next_step["XML_ELEMENTS_PARENT"] = $XML_ELEMENTS_PARENT;

		}

		if($meta_data_xml_id)
		{
			$rs = $DB->Query("select ID from b_xml_tree where PARENT_ID = ".intval($xml_root_id)." and NAME='".GetMessage("IBLOCK_XML2_METADATA")."'");
			while($ar = $rs->Fetch())
			{
				//Find referenced metadata
				$bMetaFound = false;
				$meta_roots = array();
				$rsMetaRoots = $DB->Query("select * from b_xml_tree where PARENT_ID = ".$ar["ID"]." order by ID");
				while($ar = $rsMetaRoots->Fetch())
				{
					if(isset($ar["VALUE_CLOB"]))
						$ar["VALUE"] = $ar["VALUE_CLOB"];
					if($ar["NAME"] == GetMessage("IBLOCK_XML2_ID") && $ar["VALUE"] == $meta_data_xml_id)
						$bMetaFound = true;
					$meta_roots[] = $ar;
				}
				//Get xml parents of the properties and sections
				if($bMetaFound)
				{
					foreach($meta_roots as $ar)
					{
						if($ar["NAME"] == GetMessage("IBLOCK_XML2_GROUPS"))
							$XML_SECTIONS_PARENT = $ar["ID"];
						elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_PROPERTIES"))
							$XML_PROPERTIES_PARENT = $ar["ID"];
					}
					break;
				}
			}
		}

		if($XML_PROPERTIES_PARENT)
		{
			$result = $this->ImportProperties($XML_PROPERTIES_PARENT, $arIBlock["ID"]);
			if($result!==true)
				return $result;
		}

		if($XML_PRICES_PARENT)
		{
			if($this->bCatalog)
			{
				$result = $this->ImportPrices($XML_PRICES_PARENT, $arIBlock["ID"], $IBLOCK_LID);
				if($result!==true)
					return $result;
			}
		}

		$this->next_step["section_sort"] = 100;
		$this->next_step["XML_SECTIONS_PARENT"] = $XML_SECTIONS_PARENT;

		return true;
	}

	function ImportSections()
	{
		global $DB;

		if($this->next_step["XML_SECTIONS_PARENT"])
		{
			$rs = $DB->Query("select ID from b_xml_tree where PARENT_ID = ".$this->next_step["XML_SECTIONS_PARENT"]." order by ID");
			while($ar = $rs->Fetch())
				$this->ImportSection($ar["ID"], $this->next_step["IBLOCK_ID"], false);
		}
	}

	function DeactivateSections($action)
	{
		global $DB;

		if(array_key_exists("bUpdateOnly", $this->next_step) && $this->next_step["bUpdateOnly"])
			return;

		if($action!="D" && $action!="A")
			return;

		$bDelete = $action=="D";

		//This will protect us from deactivating when next_step is lost
		$IBLOCK_ID = intval($this->next_step["IBLOCK_ID"]);
		if($IBLOCK_ID < 1)
			return $counter;

		$arFilter = array(
			"IBLOCK_ID" => $IBLOCK_ID,
		);
		if(!$bDelete)
			$arFilter["ACTIVE"] = "Y";

		$obSection = new CIBlockSection;
		$rsSection = $obSection->GetList(array("ID"=>"asc"), $arFilter);

		while($arSection = $rsSection->Fetch())
		{
			$rs = $DB->Query("select ID from b_xml_tree where PARENT_ID+0 = 0 AND LEFT_MARGIN = ".$arSection["ID"]);
			if(!$rs->Fetch())
			{
				if($bDelete)
				{
					$obSection->Delete($arSection["ID"]);
				}
				else
				{
					$obSection->Update($arSection["ID"], array("ACTIVE"=>"N"));
				}
			}
			else
			{
				$rs = $DB->Query("delete from b_xml_tree where PARENT_ID+0 = 0 AND LEFT_MARGIN = ".$arSection["ID"]);
			}
		}
		return;
	}

	function SectionsResort()
	{
		CIBlockSection::ReSort($this->next_step["IBLOCK_ID"]);
	}

	function ImportPrices($XML_PRICES_PARENT, $IBLOCK_ID, $IBLOCK_LID)
	{
		$price_sort = 0;
		$this->next_step["XML_PRICES_PARENT"] = $XML_PRICES_PARENT;

		$arLang = array();
		foreach($IBLOCK_LID as $site_id)
		{
			$rsSite = CSite::GetList($by = "sort",$order = "asc", array("ID" => $site_id));
			while ($site = $rsSite->Fetch())
				$arLang[$site["LANGUAGE_ID"]] = $site["LANGUAGE_ID"];
		}

		$arXMLPrices = CIBlockXMLFile::GetAllChildrenArray($XML_PRICES_PARENT);
		foreach($arXMLPrices as $key => $arXMLPrice)
		{
			$PRICE_NAME = $arXMLPrice[GetMessage("IBLOCK_XML2_NAME")];
			$rsPrice = CCatalogGroup::GetList(array(), array("NAME"=>$PRICE_NAME));
			if(!$rsPrice->Fetch())
			{
				$price_sort += 100;
				$arPrice = array(
					"NAME" => $PRICE_NAME,
					"XML_ID" => $arXMLPrice[GetMessage("IBLOCK_XML2_ID")],
					"SORT" => $price_sort,
					"USER_LANG" => array(),
					"USER_GROUP" => array(2),
					"USER_GROUP_BUY" => array(2),
				);
				foreach($arLang as $lang)
				{

					$arPrice["USER_LANG"][$lang] = $arXMLPrice[GetMessage("IBLOCK_XML2_NAME")];
				}
				$ID = CCatalogGroup::Add($arPrice);
			}
		}
		return true;
	}

	function ImportProperties($XML_PROPERTIES_PARENT, $IBLOCK_ID)
	{
		global $DB;
		$obProperty = new CIBlockProperty;
		$sort = 100;

		$arElementFields = array(
			"CML2_CODE" => GetMessage("IBLOCK_XML2_SYMBOL_CODE"),
			"CML2_SORT" => GetMessage("IBLOCK_XML2_SORT"),
			"CML2_ACTIVE_FROM" => GetMessage("IBLOCK_XML2_START_TIME"),
			"CML2_ACTIVE_TO" => GetMessage("IBLOCK_XML2_END_TIME"),
			"CML2_PREVIEW_TEXT" => GetMessage("IBLOCK_XML2_ANONS"),
			"CML2_PREVIEW_PICTURE" => GetMessage("IBLOCK_XML2_PREVIEW_PICTURE"),
		);

		$rs = $DB->Query("select ID from b_xml_tree where PARENT_ID = ".$XML_PROPERTIES_PARENT." order by ID");
		while($ar = $rs->Fetch())
		{
			$XML_ENUM_PARENT = false;
			$arProperty = array(
			);
			$rsP = $DB->Query("select * from b_xml_tree where PARENT_ID = ".$ar["ID"]." order by ID");
			while($arP = $rsP->Fetch())
			{
				if(isset($arP["VALUE_CLOB"]))
					$arP["VALUE"] = $arP["VALUE_CLOB"];
				if($arP["NAME"]==GetMessage("IBLOCK_XML2_ID"))
				{
					$arProperty["XML_ID"] = $arP["VALUE"];
					if(array_key_exists($arProperty["XML_ID"], $arElementFields))
						break;
				}
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_NAME"))
					$arProperty["NAME"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_MULTIPLE"))
					$arProperty["MULTIPLE"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_SORT"))
					$arProperty["SORT"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_CODE"))
					$arProperty["CODE"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_DEFAULT_VALUE"))
					$arProperty["DEFAULT_VALUE"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_PROERTY_TYPE"))
					$arProperty["PROPERTY_TYPE"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_ROWS"))
					$arProperty["ROW_COUNT"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_COLUMNS"))
					$arProperty["COL_COUNT"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_LIST_TYPE"))
					$arProperty["LIST_TYPE"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_FILE_EXT"))
					$arProperty["FILE_TYPE"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_FIELDS_COUNT"))
					$arProperty["MULTIPLE_CNT"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_USER_TYPE"))
					$arProperty["USER_TYPE"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_WITH_DESCRIPTION"))
					$arProperty["WITH_DESCRIPTION"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_SEARCH"))
					$arProperty["SEARCHABLE"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_FILTER"))
					$arProperty["FILTRABLE"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_LINKED_IBLOCK"))
					$arProperty["LINK_IBLOCK_ID"] = $this->GetIBlockByXML_ID($arP["VALUE"]);
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_CHOICE_VALUES"))
					$XML_ENUM_PARENT = $arP["ID"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_IS_REQUIRED"))
					$arProperty["IS_REQUIRED"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
			}

			if(array_key_exists($arProperty["XML_ID"], $arElementFields))
				continue;

			$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$arProperty["XML_ID"]));
			if($arDBProperty = $rsProperty->Fetch())
			{
				$bChanged = false;
				foreach($arProperty as $key=>$value)
				{
					if($arDBProperty[$key] !== $value)
					{
						$bChanged = true;
						break;
					}
				}
				if(!$bChanged)
					$arProperty["ID"] = $arDBProperty["ID"];
				elseif($obProperty->Update($arDBProperty["ID"], $arProperty))
					$arProperty["ID"] = $arDBProperty["ID"];
				else
					return $obProperty->LAST_ERROR;
			}
			else
			{
				$arProperty["IBLOCK_ID"] = $IBLOCK_ID;
				$arProperty["ACTIVE"] = "Y";
				if(!array_key_exists("PROPERTY_TYPE", $arProperty))
					$arProperty["PROPERTY_TYPE"] = "S";
				if(!array_key_exists("SORT", $arProperty))
					$arProperty["SORT"] = $sort;
				$arProperty["ID"] = $obProperty->Add($arProperty);
				if(!$arProperty["ID"])
					return $obProperty->LAST_ERROR;
			}

			if($XML_ENUM_PARENT)
			{
				$arEnumMap = array();
				$arProperty["VALUES"] = array();
				$rsEnum = CIBlockProperty::GetPropertyEnum($arProperty["ID"]);
				while($arEnum = $rsEnum->Fetch())
				{
					$arProperty["VALUES"][$arEnum["ID"]] = $arEnum;
					$arEnumMap[$arEnum["XML_ID"]] = &$arProperty["VALUES"][$arEnum["ID"]];
				}
				$rsE = $DB->Query("select * from b_xml_tree where PARENT_ID = ".$XML_ENUM_PARENT." order by ID");
				$i = 0;
				while($arE = $rsE->Fetch())
				{
					if(isset($arE["VALUE_CLOB"]))
						$arE["VALUE"] = $arE["VALUE_CLOB"];
					if($arE["NAME"]==GetMessage("IBLOCK_XML2_CHOICE"))
					{
						$arE = CIBlockXMLFile::GetAllChildrenArray($arE);
						if(isset($arE[GetMessage("IBLOCK_XML2_ID")]))
						{
							$xml_id = $arE[GetMessage("IBLOCK_XML2_ID")];
							if(!array_key_exists($xml_id, $arEnumMap))
							{
								$arProperty["VALUES"]["n".$i] = array();
								$arEnumMap[$xml_id] = &$arProperty["VALUES"]["n".$i];
								$i++;
							}
							$arEnumMap[$xml_id]["CML2_EXPORT_FLAG"] = true;
							$arEnumMap[$xml_id]["XML_ID"] = $xml_id;
							if(isset($arE[GetMessage("IBLOCK_XML2_VALUE")]))
								$arEnumMap[$xml_id]["VALUE"] = $arE[GetMessage("IBLOCK_XML2_VALUE")];
							if(isset($arE[GetMessage("IBLOCK_XML2_BY_DEFAULT")]))
								$arEnumMap[$xml_id]["DEF"] = ($arE[GetMessage("IBLOCK_XML2_BY_DEFAULT")]=="true") || intval($arE[GetMessage("IBLOCK_XML2_BY_DEFAULT")])? "Y": "N";
							if(isset($arE[GetMessage("IBLOCK_XML2_SORT")]))
								$arEnumMap[$xml_id]["SORT"] = intval($arE[GetMessage("IBLOCK_XML2_SORT")]);
						}
					}
				}
				foreach($arProperty["VALUES"] as $id=>$arEnum)
				{
					if(!isset($arEnum["CML2_EXPORT_FLAG"]))
						$arProperty["VALUES"][$id]["VALUE"] = "";
				}
				$obProperty->UpdateEnum($arProperty["ID"], $arProperty["VALUES"]);
			}
			$sort += 100;
		}
		return true;
	}

	function ReadCatalogData(&$SECTION_MAP, &$PRICES_MAP)
	{
		global $DB;

		if(!is_array($SECTION_MAP))
		{
			$SECTION_MAP = array();
			$obSection = new CIBlockSection;
			$rsSections = $obSection->GetList(array(), array("IBLOCK_ID"=>$this->next_step["IBLOCK_ID"]), false);
			while($ar = $rsSections->Fetch())
				$SECTION_MAP[$ar["XML_ID"]] = $ar["ID"];
		}
		$this->SECTION_MAP = $SECTION_MAP;

		if(!is_array($PRICES_MAP))
		{
			$PRICES_MAP = array();
			if(isset($this->next_step["XML_PRICES_PARENT"]))
			{
				$rs = $DB->Query("select * from b_xml_tree where PARENT_ID = ".$this->next_step["XML_PRICES_PARENT"]." order by ID");
				while($arParent = $rs->Fetch())
				{
					if(isset($arParent["VALUE_CLOB"]))
						$arParent["VALUE"] = $arParent["VALUE_CLOB"];
					$arXMLPrice = CIBlockXMLFile::GetAllChildrenArray($arParent);
					$arPrice = array(
						"NAME" => $arXMLPrice[GetMessage("IBLOCK_XML2_NAME")],
						"XML_ID" => $arXMLPrice[GetMessage("IBLOCK_XML2_ID")],
						"CURRENCY" => $arXMLPrice[GetMessage("IBLOCK_XML2_CURRENCY")],
						"TAX_NAME" => $arXMLPrice[GetMessage("IBLOCK_XML2_TAX")][GetMessage("IBLOCK_XML2_NAME")],
						"TAX_IN_SUM" => $arXMLPrice[GetMessage("IBLOCK_XML2_TAX")][GetMessage("IBLOCK_XML2_IN_SUM")],
					);
					if($this->bCatalog)
					{
						$rsPrice = CCatalogGroup::GetList(array(), array("NAME"=>$arPrice["NAME"]));
						if($ar = $rsPrice->Fetch())
							$arPrice["ID"] = $ar["ID"];
						else
							$arPrice["ID"] = 0;
					}
					else
					{
						$obProperty = new CIBlockProperty;
						$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$this->next_step["IBLOCK_ID"], "XML_ID"=>$arPrice["XML_ID"]));
						if($ar = $rsProperty->Fetch())
							$arPrice["ID"] = $ar["ID"];
						else
							$arPrice["ID"] = 0;
					}
					$PRICES_MAP[$arPrice["XML_ID"]] = $arPrice;
				}
			}
		}
		$this->PRICES_MAP = $PRICES_MAP;
	}

	function GetElementCRC($arElement)
	{
		$c = crc32(print_r($arElement, true));
		if($c > 0x7FFFFFFF)
			$c = -(0xFFFFFFFF - $c + 1);
		return $c;
	}

	function ImportElements($start_time, $interval)
	{
		global $DB;

		$counter = array(
			"ADD" => 0,
			"UPD" => 0,
			"DEL" => 0,
			"DEA" => 0,
			"ERR" => 0,
		);
		if($this->next_step["XML_ELEMENTS_PARENT"])
		{
			$obElement = new CIBlockElement();
			$bWF = CModule::IncludeModule("workflow");
			$rsParents = $DB->Query("select ID, LEFT_MARGIN, RIGHT_MARGIN from b_xml_tree where PARENT_ID = ".$this->next_step["XML_ELEMENTS_PARENT"]." AND ID > ".intval($this->next_step["XML_LAST_ID"])." order by ID");
			while($arParent = $rsParents->Fetch())
			{
				$arXMLElement = CIBlockXMLFile::GetAllChildrenArray($arParent);
				if(!$this->next_step["bOffer"] && $this->use_offers)
				{
					$p = strrpos($arXMLElement[GetMessage("IBLOCK_XML2_ID")], "#");
					if($p !== false)
						continue;
				}
				if(array_key_exists(GetMessage("IBLOCK_XML2_STATUS"), $arXMLElement) && ($arXMLElement[GetMessage("IBLOCK_XML2_STATUS")] == GetMessage("IBLOCK_XML2_DELETED")))
				{
					$ID = $this->GetElementByXML_ID($this->next_step["IBLOCK_ID"], $arXMLElement[GetMessage("IBLOCK_XML2_ID")]);
					if($ID && $obElement->Update($ID, array("ACTIVE"=>"N"), $bWF))
					{
						if($this->use_offers)
							$this->ChangeOffersStatus($ID, "N", $bWF);
						$counter["DEA"]++;
					}
					else
					{
						$counter["ERR"]++;
					}
				}
				else
				{
					if($this->next_step["bOffer"] && !$this->use_offers)
						$ID = $this->ImportElementPrices($arXMLElement, $counter, $bWF);
					else
						$ID = $this->ImportElement($arXMLElement, $counter, $bWF);
				}
				if($ID)
					$DB->Query("INSERT INTO b_xml_tree (PARENT_ID, LEFT_MARGIN) values (0, ".$ID.")");

				$this->next_step["XML_LAST_ID"] = $arParent["ID"];

				if($interval > 0 && (time()-$start_time) > $interval)
					break;
			}
		}
		$this->CleanTempFiles();
		return $counter;
	}

	function ChangeOffersStatus($ELEMENT_ID, $STATUS = "Y", $bWF = true)
	{
		if($this->arLinkedProps === false)
		{
			$this->arLinkedProps = array();
			$obProperty = new CIBlockProperty;
			$rsProperty = $obProperty->GetList(array(), array("LINK_IBLOCK_ID"=>$this->next_step["IBLOCK_ID"], "XML_ID"=>"CML2_LINK"));
			while($arProperty = $rsProperty->Fetch())
				$this->arLinkedProps[] = $arProperty;
		}
		$obElement = new CIBlockElement;
		foreach($this->arLinkedProps as $arProperty)
		{
			$rsElements = $obElement->GetList(
				Array("ID"=>"asc"),
				Array(
					"PROPERTY_".$arProperty["ID"] => $ELEMENT_ID,
					"IBLOCK_ID" => $arProperty["IBLOCK_ID"],
					"ACTIVE" => $STATUS=="Y"? "N": "Y",
				),
				false, false,
				Array("ID", "TMP_ID")
			);
			while($arElement = $rsElements->Fetch())
				$obElement->Update($arElement["ID"], array("ACTIVE"=>$STATUS), $bWF);
		}
	}

	function ImportElement($arXMLElement, &$counter, $bWF)
	{
		$arElement = array(
			"ACTIVE" => "Y",
			"TMP_ID" => $this->GetElementCRC($arXMLElement),
			"PROPERTY_VALUES" => array(),
		);
		if(isset($arXMLElement[GetMessage("IBLOCK_XML2_ID")]))
			$arElement["XML_ID"] = $arXMLElement[GetMessage("IBLOCK_XML2_ID")];

		$obElement = new CIBlockElement;
		$rsElement = $obElement->GetList(
			Array("ID"=>"asc"),
			Array("=XML_ID" => $arElement["XML_ID"], "IBLOCK_ID" => $this->next_step["IBLOCK_ID"]),
			false, false,
			Array("ID", "TMP_ID", "ACTIVE")
		);

		$bMatch = false;
		if($arDBElement = $rsElement->Fetch())
			$bMatch = ($arElement["TMP_ID"] == $arDBElement["TMP_ID"]);

		if($bMatch && $this->use_crc)
		{
			//In case element is not active in database we have to activate it and its offers
			if($arDBElement["ACTIVE"] != "Y")
			{
				$obElement->Update($arDBElement["ID"], array("ACTIVE"=>"Y"), $bWF);
				$this->ChangeOffersStatus($arDBElement["ID"], "Y", $bWF);
			}
			$arElement["ID"] = $arDBElement["ID"];
			$counter["UPD"]++;
		}
		else
		{
			if($arDBElement)
			{
				$rsProperties = $obElement->GetProperty($this->next_step["IBLOCK_ID"], $arDBElement["ID"], "sort", "asc");
				while($arProperty = $rsProperties->Fetch())
				{
					if(!array_key_exists($arProperty["ID"], $arElement["PROPERTY_VALUES"]))
						$arElement["PROPERTY_VALUES"][$arProperty["ID"]] = array(
							"bOld" => true,
						);

					$arElement["PROPERTY_VALUES"][$arProperty["ID"]][$arProperty['PROPERTY_VALUE_ID']] = array(
						"VALUE"=>$arProperty['VALUE'],
						"DESCRIPTION"=>$arProperty["DESCRIPTION"]
					);
				}
			}

			if($this->bCatalog && $this->next_step["bOffer"])
			{
				$p = strpos($arXMLElement[GetMessage("IBLOCK_XML2_ID")], "#");
				if($p !== false)
					$link_xml_id = substr($arXMLElement[GetMessage("IBLOCK_XML2_ID")], 0, $p);
				else
					$link_xml_id = $arXMLElement[GetMessage("IBLOCK_XML2_ID")];
				$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_LINK"]] = $this->GetElementByXML_ID($this->arProperties[$this->PROPERTY_MAP["CML2_LINK"]]["LINK_IBLOCK_ID"], $link_xml_id);
			}

			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_NAME")]))
				$arElement["NAME"] = $arXMLElement[GetMessage("IBLOCK_XML2_NAME")];
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_BX_TAGS")]))
				$arElement["TAGS"] = $arXMLElement[GetMessage("IBLOCK_XML2_BX_TAGS")];
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_DESCRIPTION")]))
			{
				$arElement["DETAIL_TEXT"] = $arXMLElement[GetMessage("IBLOCK_XML2_DESCRIPTION")];
				if(strpos($arElement["DETAIL_TEXT"], "<")!==false)
					$arElement["DETAIL_TEXT_TYPE"] = "html";
				else
					$arElement["DETAIL_TEXT_TYPE"] = "text";
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_FULL_TITLE")]))
			{
				$arElement["PREVIEW_TEXT"] = $arXMLElement[GetMessage("IBLOCK_XML2_FULL_TITLE")];
				if(strpos($arElement["PREVIEW_TEXT"], "<")!==false)
					$arElement["PREVIEW_TEXT_TYPE"] = "html";
				else
					$arElement["PREVIEW_TEXT_TYPE"] = "text";
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_BAR_CODE")]))
				$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_BAR_CODE"]] = $arXMLElement[GetMessage("IBLOCK_XML2_BAR_CODE")];
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_ARTICLE")]))
				$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_ARTICLE"]] = $arXMLElement[GetMessage("IBLOCK_XML2_ARTICLE")];

			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_PICTURE")]))
			{
				$arElement["DETAIL_PICTURE"] = $this->ResizePicture($arXMLElement[GetMessage("IBLOCK_XML2_PICTURE")], $this->detail);
				$arElement["PREVIEW_PICTURE"] = $this->ResizePicture($arElement["DETAIL_PICTURE"]["tmp_name"], $this->preview);
			}

			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_GROUPS")]))
			{
				$arElement["IBLOCK_SECTION"] = array();
				foreach($arXMLElement[GetMessage("IBLOCK_XML2_GROUPS")] as $key=>$value)
				{
					if(array_key_exists($value, $this->SECTION_MAP))
						$arElement["IBLOCK_SECTION"][] = $this->SECTION_MAP[$value];
				}
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_PRICES")]))
			{//Collect price information for future use
				$arElement["PRICES"] = array();
				foreach($arXMLElement[GetMessage("IBLOCK_XML2_PRICES")] as $key=>$price)
				{
					if(isset($price[GetMessage("IBLOCK_XML2_PRICE_TYPE_ID")]) && array_key_exists($price[GetMessage("IBLOCK_XML2_PRICE_TYPE_ID")], $this->PRICES_MAP))
					{
						$price["PRICE"] = $this->PRICES_MAP[$price[GetMessage("IBLOCK_XML2_PRICE_TYPE_ID")]];
						$arElement["PRICES"][] = $price;
					}
				}
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_AMOUNT")]))
				$arElement["QUANTITY"] = $arXMLElement[GetMessage("IBLOCK_XML2_AMOUNT")];
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_ITEM_ATTRIBUTES")]))
			{
				$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_ATTRIBUTES"]] = array();
				$i = 0;
				foreach($arXMLElement[GetMessage("IBLOCK_XML2_ITEM_ATTRIBUTES")] as $key => $value)
				{
					$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_ATTRIBUTES"]]["n".$i] = array(
						"VALUE" => $value[GetMessage("IBLOCK_XML2_VALUE")],
						"DESCRIPTION" => $value[GetMessage("IBLOCK_XML2_NAME")],
					);
					$i++;
				}
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_TRAITS_VALUES")]))
			{
				$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_TRAITS"]] = array();
				$i = 0;
				foreach($arXMLElement[GetMessage("IBLOCK_XML2_TRAITS_VALUES")] as $key => $value)
				{
					$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_TRAITS"]]["n".$i] = array(
						"VALUE" => $value[GetMessage("IBLOCK_XML2_VALUE")],
						"DESCRIPTION" => $value[GetMessage("IBLOCK_XML2_NAME")],
					);
					$i++;
				}
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_TAXES_VALUES")]))
			{
				$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_TAXES"]] = array();
				$i = 0;
				foreach($arXMLElement[GetMessage("IBLOCK_XML2_TAXES_VALUES")] as $key => $value)
				{
					$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_TAXES"]]["n".$i] = array(
						"VALUE" => $value[GetMessage("IBLOCK_XML2_TAX_VALUE")],
						"DESCRIPTION" => $value[GetMessage("IBLOCK_XML2_NAME")],
					);
					$i++;
				}
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_BASE_UNIT")]))
			{
				$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_BASE_UNIT"]] = $arXMLElement[GetMessage("IBLOCK_XML2_BASE_UNIT")];
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_PROPERTIES_VALUES")]))
			{
				foreach($arXMLElement[GetMessage("IBLOCK_XML2_PROPERTIES_VALUES")] as $key=>$value)
				{
					if(!array_key_exists(GetMessage("IBLOCK_XML2_ID"), $value))
						continue;

					$prop_id = $value[GetMessage("IBLOCK_XML2_ID")];
					unset($value[GetMessage("IBLOCK_XML2_ID")]);

					//Handle properties which is actually element fields
					if(!array_key_exists($prop_id, $this->PROPERTY_MAP))
					{
						if($prop_id == "CML2_CODE")
							$arElement["CODE"] = array_pop($value);
						elseif($prop_id == "CML2_SORT")
							$arElement["SORT"] = array_pop($value);
						elseif($prop_id == "CML2_ACTIVE_FROM")
							$arElement["ACTIVE_FROM"] = CDatabase::FormatDate(array_pop($value), "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("FULL"));
						elseif($prop_id == "CML2_ACTIVE_TO")
							$arElement["ACTIVE_TO"] = CDatabase::FormatDate(array_pop($value), "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("FULL"));
						elseif($prop_id == "CML2_PREVIEW_TEXT")
						{
							$arElement["PREVIEW_TEXT"] = array_pop($value);
							$arElement["PREVIEW_TEXT_TYPE"] = "html";
						}
						elseif($prop_id == "CML2_PREVIEW_PICTURE")
							$arElement["PREVIEW_PICTURE"] = $this->MakeFileArray(array_pop($value));

						continue;
					}

					$prop_id = $this->PROPERTY_MAP[$prop_id];
					$prop_type = $this->arProperties[$prop_id]["PROPERTY_TYPE"];

					if(!array_key_exists($prop_id, $arElement["PROPERTY_VALUES"]))
						$arElement["PROPERTY_VALUES"][$prop_id] = array();

					//check for bitrix extended format
					if(array_key_exists(GetMessage("IBLOCK_XML2_PROPERTY_VALUE"), $value))
					{
						$i = 1;
						foreach($value as $k=>$prop_value)
						{
							if(substr($k, 0, 16) === GetMessage("IBLOCK_XML2_PROPERTY_VALUE"))
							{
								if(array_key_exists(GetMessage("IBLOCK_XML2_SERIALIZED"), $prop_value))
									$prop_value[GetMessage("IBLOCK_XML2_VALUE")] = unserialize($prop_value[GetMessage("IBLOCK_XML2_VALUE")]);
								if($prop_type=="F")
									$prop_value[GetMessage("IBLOCK_XML2_VALUE")] = $this->MakeFileArray($prop_value[GetMessage("IBLOCK_XML2_VALUE")]);
								elseif($prop_type=="G")
									$prop_value[GetMessage("IBLOCK_XML2_VALUE")] = $this->GetSectionByXML_ID($this->arProperties[$prop_id]["LINK_IBLOCK_ID"], $prop_value[GetMessage("IBLOCK_XML2_VALUE")]);
								elseif($prop_type=="E")
									$prop_value[GetMessage("IBLOCK_XML2_VALUE")] = $this->GetElementByXML_ID($this->arProperties[$prop_id]["LINK_IBLOCK_ID"], $prop_value[GetMessage("IBLOCK_XML2_VALUE")]);
								elseif($prop_type=="L")
									$prop_value[GetMessage("IBLOCK_XML2_VALUE")] = $this->GetEnumByXML_ID($this->arProperties[$prop_id]["IBLOCK_ID"], $prop_value[GetMessage("IBLOCK_XML2_VALUE")]);
								if(array_key_exists("bOld", $arElement["PROPERTY_VALUES"][$prop_id]))
								{
									if($prop_type=="F")
									{
										foreach($arElement["PROPERTY_VALUES"][$prop_id] as $PROPERTY_VALUE_ID => $PROPERTY_VALUE)
											$arElement["PROPERTY_VALUES"][$prop_id][$PROPERTY_VALUE_ID] = array(
												"tmp_name" => "",
												"del" => "Y",
											);
										unset($arElement["PROPERTY_VALUES"][$prop_id]["bOld"]);
									}
									else
										$arElement["PROPERTY_VALUES"][$prop_id] = array();
								}
								$arElement["PROPERTY_VALUES"][$prop_id]["n".$i] = array(
									"VALUE" => $prop_value[GetMessage("IBLOCK_XML2_VALUE")],
									"DESCRIPTION" => $prop_value[GetMessage("IBLOCK_XML2_DESCRIPTION")],
								);
							}
							$i++;
						}
					}
					else
					{
						foreach($value as $k=>$prop_value)
						{
							if(array_key_exists("bOld", $arElement["PROPERTY_VALUES"][$prop_id]))
							{
								if($prop_type=="F")
								{
									foreach($arElement["PROPERTY_VALUES"][$prop_id] as $PROPERTY_VALUE_ID => $PROPERTY_VALUE)
										$arElement["PROPERTY_VALUES"][$prop_id][$PROPERTY_VALUE_ID] = array(
											"tmp_name" => "",
											"del" => "Y",
										);
									unset($arElement["PROPERTY_VALUES"][$prop_id]["bOld"]);
								}
								else
									$arElement["PROPERTY_VALUES"][$prop_id] = array();
							}
							$arElement["PROPERTY_VALUES"][$prop_id][] = $prop_value;
						}
					}
				}
			}

			if($arDBElement)
			{
				foreach($arElement["PROPERTY_VALUES"] as $prop_id=>$prop)
				{
					if(is_array($arElement["PROPERTY_VALUES"][$prop_id]) && array_key_exists("bOld", $arElement["PROPERTY_VALUES"][$prop_id]))
					{
						if($this->arProperties[$prop_id]["PROPERTY_TYPE"]=="F")
							unset($arElement["PROPERTY_VALUES"][$prop_id]);
						else
							unset($arElement["PROPERTY_VALUES"][$prop_id]["bOld"]);
					}
				}

				$obElement->Update($arDBElement["ID"], $arElement, $bWF);
				//In case element was not active in database we have to activate its offers
				if($arDBElement["ACTIVE"] != "Y")
				{
					$this->ChangeOffersStatus($arDBElement["ID"], "Y", $bWF);
				}
				$arElement["ID"] = $arDBElement["ID"];
				if($arElement["ID"])
					$counter["UPD"]++;
				else
					$counter["ERR"]++;
			}
			else
			{
				$arElement["IBLOCK_ID"] = $this->next_step["IBLOCK_ID"];
				$arElement["ID"] = $obElement->Add($arElement, $bWF);
				if($arElement["ID"])
					$counter["ADD"]++;
				else
					$counter["ERR"]++;
			}

			if($arElement["ID"] && isset($arElement["PRICES"]) && $this->bCatalog)
			{
				$arProduct = array(
					"ID" => $arElement["ID"],
				);
				if(isset($arElement["QUANTITY"]))
					$arProduct["QUANTITY"] = $arElement["QUANTITY"];

				//Here start VAT handling

				//Check if all the taxes exists in BSM catalog
				$arTaxMap = array();
				$CML_LINK = $this->PROPERTY_MAP["CML2_LINK"];
				$rsTaxProperty = CIBlockElement::GetProperty($this->arProperties[$CML_LINK]["LINK_IBLOCK_ID"], $arElement["PROPERTY_VALUES"][$CML_LINK], "sort", "asc", array("CODE" => "CML2_TAXES"));
				while($arTaxProperty = $rsTaxProperty->Fetch())
				{
					if(
						strlen($arTaxProperty["VALUE"]) > 0
						&& strlen($arTaxProperty["DESCRIPTION"]) > 0
						&& !array_key_exists($arTaxProperty["DESCRIPTION"], $arTaxMap)
					)
					{
						$arTaxMap[$arTaxProperty["DESCRIPTION"]] = array(
							"RATE" => doubleval($arTaxProperty["VALUE"]),
							"ID" => $this->CheckTax($arTaxProperty["DESCRIPTION"], doubleval($arTaxProperty["VALUE"])),
						);
					}
				}

				//First find out if all the prices have TAX_IN_SUM true
				$TAX_IN_SUM = "Y";
				foreach($arElement["PRICES"] as $key=>$price)
				{
					if($price["PRICE"]["TAX_IN_SUM"] !== "true")
					{
						$TAX_IN_SUM = "N";
						break;
					}
				}
				//If there was found not insum tax we'll make shure
				//that all prices has the same flag
				if($TAX_IN_SUM === "N")
				{
					foreach($arElement["PRICES"] as $key=>$price)
					{
						if($price["PRICE"]["TAX_IN_SUM"] !== "false")
						{
							$TAX_IN_SUM = "Y";
							break;
						}
					}
					//Check if there is a mix of tax in sum
					//and correct it by recalculating all the prices
					if($TAX_IN_SUM === "Y")
					{
						foreach($arElement["PRICES"] as $key=>$price)
						{
							if($price["PRICE"]["TAX_IN_SUM"] !== "true")
							{
								$TAX_NAME = $price["PRICE"]["TAX_NAME"];
								if(array_key_exists($TAX_NAME, $arTaxMap))
								{
									$PRICE_WO_TAX = DoubleVal(str_replace(",", ".", $price[GetMessage("IBLOCK_XML2_PRICE_FOR_ONE")]));
									$PRICE = $PRICE_WO_TAX + ($PRICE_WO_TAX / 100.0 * $arTaxMap[$TAX_NAME]["RATE"]);
									$arElement["PRICES"][$key][GetMessage("IBLOCK_XML2_PRICE_FOR_ONE")] = $PRICE;
								}
							}
						}
					}
				}
				foreach($arElement["PRICES"] as $key=>$price)
				{
					$TAX_NAME = $price["PRICE"]["TAX_NAME"];
					if(array_key_exists($TAX_NAME, $arTaxMap))
					{
						$arProduct["VAT_ID"] = $arTaxMap[$TAX_NAME]["ID"];
						break;
					}
				}
				$arProduct["VAT_INCLUDED"] = $TAX_IN_SUM;

				CCatalogProduct::Add($arProduct);

				foreach($arElement["PRICES"] as $key=>$price)
				{

					if(!isset($price[GetMessage("IBLOCK_XML2_CURRENCY")]))
						$price[GetMessage("IBLOCK_XML2_CURRENCY")] = $price["PRICE"]["CURRENCY"];

					$arPrice = Array(
						"PRODUCT_ID" => $arElement["ID"],
						"CATALOG_GROUP_ID" => $price["PRICE"]["ID"],
						"PRICE" => DoubleVal(str_replace(",", ".", $price[GetMessage("IBLOCK_XML2_PRICE_FOR_ONE")])),
						"CURRENCY" => $this->CheckCurrency($price[GetMessage("IBLOCK_XML2_CURRENCY")]),
					);

					$rsPrice = CPrice::GetList(
						array(),
						array(
							"PRODUCT_ID" => $arElement["ID"],
							"CATALOG_GROUP_ID" => $price["PRICE"]["ID"],
						)
					);
					if($ar = $rsPrice->Fetch())
						CPrice::Update($ar["ID"], $arPrice);
					else
						CPrice::Add($arPrice);
				}
			}
		}

		return $arElement["ID"];
	}

	function ImportElementPrices($arXMLElement, &$counter, $bWF)
	{
		$arElement = array(
			"ID" => 0,
			"XML_ID" => $arXMLElement[GetMessage("IBLOCK_XML2_ID")],
		);

		$obElement = new CIBlockElement;
		$rsElement = $obElement->GetList(
			Array("ID"=>"asc"),
			Array("=XML_ID" => $arElement["XML_ID"], "IBLOCK_ID" => $this->next_step["IBLOCK_ID"]),
			false, false,
			Array("ID", "TMP_ID", "ACTIVE")
		);

		if($arDBElement = $rsElement->Fetch())
		{
			$arElement["ID"] = $arDBElement["ID"];

			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_PRICES")]))
			{//Collect price information for future use
				$arElement["PRICES"] = array();
				foreach($arXMLElement[GetMessage("IBLOCK_XML2_PRICES")] as $key=>$price)
				{
					if(isset($price[GetMessage("IBLOCK_XML2_PRICE_TYPE_ID")]) && array_key_exists($price[GetMessage("IBLOCK_XML2_PRICE_TYPE_ID")], $this->PRICES_MAP))
					{
						$price["PRICE"] = $this->PRICES_MAP[$price[GetMessage("IBLOCK_XML2_PRICE_TYPE_ID")]];
						$arElement["PRICES"][] = $price;
					}
				}
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_AMOUNT")]))
				$arElement["QUANTITY"] = $arXMLElement[GetMessage("IBLOCK_XML2_AMOUNT")];

			if(isset($arElement["PRICES"]) && $this->bCatalog)
			{
				$arProduct = array(
					"ID" => $arElement["ID"],
				);
				if(isset($arElement["QUANTITY"]))
					$arProduct["QUANTITY"] = $arElement["QUANTITY"];

				//Here start VAT handling

				//Check if all the taxes exists in BSM catalog
				$arTaxMap = array();
				$rsTaxProperty = CIBlockElement::GetProperty($this->next_step["IBLOCK_ID"], $arElement["ID"], "sort", "asc", array("CODE" => "CML2_TAXES"));
				while($arTaxProperty = $rsTaxProperty->Fetch())
				{
					if(
						strlen($arTaxProperty["VALUE"]) > 0
						&& strlen($arTaxProperty["DESCRIPTION"]) > 0
						&& !array_key_exists($arTaxProperty["DESCRIPTION"], $arTaxMap)
					)
					{
						$arTaxMap[$arTaxProperty["DESCRIPTION"]] = array(
							"RATE" => doubleval($arTaxProperty["VALUE"]),
							"ID" => $this->CheckTax($arTaxProperty["DESCRIPTION"], doubleval($arTaxProperty["VALUE"])),
						);
					}
				}

				//First find out if all the prices have TAX_IN_SUM true
				$TAX_IN_SUM = "Y";
				foreach($arElement["PRICES"] as $key=>$price)
				{
					if($price["PRICE"]["TAX_IN_SUM"] !== "true")
					{
						$TAX_IN_SUM = "N";
						break;
					}
				}
				//If there was found not insum tax we'll make shure
				//that all prices has the same flag
				if($TAX_IN_SUM === "N")
				{
					foreach($arElement["PRICES"] as $key=>$price)
					{
						if($price["PRICE"]["TAX_IN_SUM"] !== "false")
						{
							$TAX_IN_SUM = "Y";
							break;
						}
					}
					//Check if there is a mix of tax in sum
					//and correct it by recalculating all the prices
					if($TAX_IN_SUM === "Y")
					{
						foreach($arElement["PRICES"] as $key=>$price)
						{
							if($price["PRICE"]["TAX_IN_SUM"] !== "true")
							{
								$TAX_NAME = $price["PRICE"]["TAX_NAME"];
								if(array_key_exists($TAX_NAME, $arTaxMap))
								{
									$PRICE_WO_TAX = DoubleVal(str_replace(",", ".", $price[GetMessage("IBLOCK_XML2_PRICE_FOR_ONE")]));
									$PRICE = $PRICE_WO_TAX + ($PRICE_WO_TAX / 100.0 * $arTaxMap[$TAX_NAME]["RATE"]);
									$arElement["PRICES"][$key][GetMessage("IBLOCK_XML2_PRICE_FOR_ONE")] = $PRICE;
								}
							}
						}
					}
				}
				foreach($arElement["PRICES"] as $key=>$price)
				{
					$TAX_NAME = $price["PRICE"]["TAX_NAME"];
					if(array_key_exists($TAX_NAME, $arTaxMap))
					{
						$arProduct["VAT_ID"] = $arTaxMap[$TAX_NAME]["ID"];
						break;
					}
				}
				$arProduct["VAT_INCLUDED"] = $TAX_IN_SUM;

				CCatalogProduct::Add($arProduct);


				foreach($arElement["PRICES"] as $key=>$price)
				{

					if(!isset($price[GetMessage("IBLOCK_XML2_CURRENCY")]))
						$price[GetMessage("IBLOCK_XML2_CURRENCY")] = $price["PRICE"]["CURRENCY"];

					$arPrice = Array(
						"PRODUCT_ID" => $arElement["ID"],
						"CATALOG_GROUP_ID" => $price["PRICE"]["ID"],
						"PRICE" => DoubleVal(str_replace(",", ".", $price[GetMessage("IBLOCK_XML2_PRICE_FOR_ONE")])),
						"CURRENCY" => $this->CheckCurrency($price[GetMessage("IBLOCK_XML2_CURRENCY")]),
					);

					$rsPrice = CPrice::GetList(
						array(),
						array(
							"PRODUCT_ID" => $arElement["ID"],
							"CATALOG_GROUP_ID" => $price["PRICE"]["ID"],
						)
					);
					if($ar = $rsPrice->Fetch())
						CPrice::Update($ar["ID"], $arPrice);
					else
						CPrice::Add($arPrice);
				}
				$counter["UPD"]++;
			}
		}

		return $arElement["ID"];
	}

	function ImportSection($xml_tree_id, $IBLOCK_ID, $parent_section_id)
	{
		global $DB;
		$this->next_step["section_sort"] += 10;
		$arSection = array(
			"IBLOCK_SECTION_ID" => $parent_section_id,
			"ACTIVE" => "Y",
		);
		$rsS = $DB->Query("select * from b_xml_tree where PARENT_ID = ".$xml_tree_id." order by ID");
		$XML_SECTIONS_PARENT = false;
		while($arS = $rsS->Fetch())
		{
			if(isset($arS["VALUE_CLOB"]))
				$arS["VALUE"] = $arS["VALUE_CLOB"];
			if($arS["NAME"]==GetMessage("IBLOCK_XML2_ID"))
				$arSection["XML_ID"] = $arS["VALUE"];
			elseif($arS["NAME"]==GetMessage("IBLOCK_XML2_NAME"))
				$arSection["NAME"] = $arS["VALUE"];
			elseif($arS["NAME"]==GetMessage("IBLOCK_XML2_DESCRIPTION"))
			{
				$arSection["DESCRIPTION"] = $arS["VALUE"];
				$arSection["DESCRIPTION_TYPE"] = "html";
			}
			elseif($arS["NAME"]==GetMessage("IBLOCK_XML2_GROUPS"))
				$XML_SECTIONS_PARENT = $arS["ID"];
			elseif($arS["NAME"]==GetMessage("IBLOCK_XML2_BX_SORT"))
				$arSection["SORT"] = intval($arS["VALUE"]);
			elseif($arS["NAME"]==GetMessage("IBLOCK_XML2_BX_CODE"))
				$arSection["CODE"] = $arS["VALUE"];
			elseif($arS["NAME"] == GetMessage("IBLOCK_XML2_BX_PICTURE"))
				$arSection["PICTURE"] = $this->MakeFileArray($arS["VALUE"]);
			elseif($arS["NAME"] == GetMessage("IBLOCK_XML2_BX_DETAIL_PICTURE"))
				$arSection["DETAIL_PICTURE"] = $this->MakeFileArray($arS["VALUE"]);
			elseif($arS["NAME"]==GetMessage("IBLOCK_XML2_PROPERTIES"))
			{
				$this->ImportProperties($arS["ID"], $IBLOCK_ID);
			}
		}
		$obSection = new CIBlockSection;
		$rsSection = $obSection->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$arSection["XML_ID"]), false);
		if($arDBSection = $rsSection->Fetch())
		{
			$bChanged = false;
			foreach($arSection as $key=>$value)
			{
				if(is_array($arDBSection[$key]) || ($arDBSection[$key] != $value))
				{
					$bChanged = true;
					break;
				}
			}
			if($bChanged)
			{
				$obSection->Update($arDBSection["ID"], $arSection, false);
			}
			$arSection["ID"] = $arDBSection["ID"];
		}
		else
		{
			$arSection["IBLOCK_ID"] = $IBLOCK_ID;
			$arSection["ACTIVE"] = "Y";
			if(!isset($arSection["SORT"]))
				$arSection["SORT"] = $this->next_step["section_sort"];
			$arSection["ID"] = $obSection->Add($arSection, false);
		}

		if($arSection["ID"])
			$DB->Query("INSERT INTO b_xml_tree (PARENT_ID, LEFT_MARGIN) values (0, ".$arSection["ID"].")");

		if($XML_SECTIONS_PARENT)
		{
			$rs = $DB->Query("select ID from b_xml_tree where PARENT_ID = ".$XML_SECTIONS_PARENT." order by ID");
			while($ar = $rs->Fetch())
				$this->ImportSection($ar["ID"], $IBLOCK_ID, $arSection["ID"]);
		}
	}

	function DeactivateElement($action, $start_time, $interval)
	{
		global $DB;

		$counter = array(
			"DEL" => 0,
			"DEA" => 0,
			"NON" => 0,
		);

		if(array_key_exists("bUpdateOnly", $this->next_step) && $this->next_step["bUpdateOnly"])
			return $counter;

		if($action!="D" && $action!="A")
			return $counter;

		$bDelete = $action=="D";

		//This will protect us from deactivating when next_step is lost
		$IBLOCK_ID = intval($this->next_step["IBLOCK_ID"]);
		if($IBLOCK_ID < 1)
			return $counter;

		$arFilter = array(
			">ID" => $this->next_step["LAST_ID"],
			"IBLOCK_ID" => $IBLOCK_ID,
		);
		if(!$bDelete)
			$arFilter["ACTIVE"] = "Y";

		$obElement = new CIBlockElement;
		$rsElement = $obElement->GetList(
			Array("ID"=>"asc"),
			$arFilter,
			false, false,
			Array("ID", "ACTIVE")
		);

		while($arElement = $rsElement->Fetch())
		{
			$rs = $DB->Query("select ID from b_xml_tree where PARENT_ID+0 = 0 AND LEFT_MARGIN = ".$arElement["ID"]);
			if(!$rs->Fetch())
			{
				if($bDelete)
				{
					$obElement->Delete($arElement["ID"]);
					$counter["DEL"]++;
				}
				else
				{
					$obElement->Update($arElement["ID"], array("ACTIVE"=>"N"));
					$counter["DEA"]++;
				}
			}
			else
				$counter["NON"]++;

			$this->next_step["LAST_ID"] = $arElement["ID"];

			if($interval > 0 && (time()-$start_time) > $interval)
				break;

		}
		return $counter;
	}

}

class CIBlockCMLExport
{
	var $fp = false;
	var $IBLOCK_ID = false;
	var $bExtended = false;
	var $work_dir = false;
	var $file_dir = false;
	var $next_step = false;
	var $arIBlock = false;
	var $prices = false;
	var $only_price = false;

	function Init($fp, $IBLOCK_ID, $next_step, $bExtended=false, $work_dir=false, $file_dir=false)
	{
		$this->fp = $fp;
		$this->IBLOCK_ID = intval($IBLOCK_ID);
		$this->bExtended = $bExtended;
		$this->work_dir = $work_dir;
		$this->file_dir = $file_dir;
		$this->next_step = $next_step;
		$this->only_price = false;

		$rsIBlock = CIBlock::GetList(array(), array("ID"=>$this->IBLOCK_ID, "MIN_PERMISSION"=>"W"));
		if(($this->arIBlock = $rsIBlock->Fetch()) && ($this->arIBlock["ID"]==$this->IBLOCK_ID))
		{
			$this->next_step["catalog"] = CModule::IncludeModule('catalog');
			if($this->next_step["catalog"])
			{
				$rs = CCatalog::GetList(array(),array("IBLOCK_ID"=>$this->arIBlock["ID"]));
				if($rs->Fetch())
				{
					$this->next_step["catalog"] = true;
					$this->prices = array();
					$rsPrice = CCatalogGroup::GetList(array(), array());
					while($arPrice = $rsPrice->Fetch())
					{
						$this->prices[$arPrice["ID"]] = $arPrice["NAME"];
					}
				}
				else
				{
					$this->next_step["catalog"] = false;
				}
			}
			return true;
		}
		else
			return false;
	}

	function GetIBlockXML_ID($IBLOCK_ID, $XML_ID=false)
	{
		if($XML_ID === false)
		{
			$IBLOCK_ID = intval($IBLOCK_ID);
			if($IBLOCK_ID>0)
			{
				$obIBlock = new CIBlock;
				$rsIBlock = $obIBlock->GetList(array(), array("ID"=>$IBLOCK_ID));
				if($arIBlock = $rsIBlock->Fetch())
					$XML_ID = $arIBlock["XML_ID"];
				else
					return "";
			}
			else
				return "";
		}
		if(strlen($XML_ID) <= 0)
		{
			$XML_ID = $IBLOCK_ID;
			$obIBlock = new CIBlock;
			$rsIBlock = $obIBlock->GetList(array(), array("XML_ID"=>$XML_ID));
			while($rsIBlock->Fetch())
			{
				$XML_ID = md5(uniqid(mt_rand(), true));
				$rsIBlock = $obIBlock->GetList(array(), array("XML_ID"=>$XML_ID));
			}
			$obIBlock->Update($IBLOCK_ID, array("XML_ID" => $XML_ID));
		}
		return $XML_ID;
	}

	function GetSectionXML_ID($IBLOCK_ID, $SECTION_ID, $XML_ID = false)
	{
		if($XML_ID === false)
		{
			$obSection = new CIBlockSection;
			$rsSection = $obSection->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "ID"=>$SECTION_ID));
			if($arSection = $rsSection->Fetch())
			{
				$XML_ID = $arSection["XML_ID"];
			}
		}
		if(strlen($XML_ID) <= 0)
		{
			$XML_ID = $SECTION_ID;
			$obSection = new CIBlockSection;
			$rsSection = $obSection->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "EXTERNAL_ID"=>$XML_ID));
			while($rsSection->Fetch())
			{
				$XML_ID = md5(uniqid(mt_rand(), true));
				$rsSection = $obSection->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "EXTERNAL_ID"=>$XML_ID));
			}
			$obSection->Update($SECTION_ID, array("XML_ID" => $XML_ID), false, false);
		}
		return $XML_ID;
	}

	function GetElementXML_ID($IBLOCK_ID, $ELEMENT_ID, $XML_ID = false)
	{
		if($XML_ID === false)
		{
			$arFilter = array(
				"ID" => $ELEMENT_ID,
				"SHOW_HISTORY"=>"Y",
			);
			if($IBLOCK_ID > 0)
				$arFilter["IBLOCK_ID"] = $IBLOCK_ID;
			$obElement = new CIBlockElement;
			$rsElement = $obElement->GetList(
					Array("ID"=>"asc"),
					$arFilter,
					false, false,
					Array("ID", "XML_ID")
			);
			if($arElement = $rsElement->Fetch())
			{
				$XML_ID = $arElement["XML_ID"];
			}
		}
		return $XML_ID;
	}

	function GetPropertyXML_ID($IBLOCK_ID, $NAME, $PROPERTY_ID, $XML_ID)
	{
		if(strlen($XML_ID) <= 0)
		{
			$XML_ID = $PROPERTY_ID;
			$obProperty = new CIBlockProperty;
			$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$XML_ID));
			while($rsProperty->Fetch())
			{
				$XML_ID = md5(uniqid(mt_rand(), true));
				$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$XML_ID));
			}
			$obProperty->Update($PROPERTY_ID, array("NAME"=>$NAME, "XML_ID" => $XML_ID));
		}
		return $XML_ID;
	}

	function StartExport()
	{
		fwrite($this->fp, "<"."?xml version=\"1.0\" encoding=\"".LANG_CHARSET."\"?".">\n");
		fwrite($this->fp, "<".GetMessage("IBLOCK_XML2_COMMERCE_INFO")." ".GetMessage("IBLOCK_XML2_SCHEMA_VERSION")."=\"2.021\" ".GetMessage("IBLOCK_XML2_TIMESTAMP")."=\"".date("Y-m-d")."T".date("H:i:s")."\">\n");
	}

	function ExportFile($FILE_ID)
	{
		if($this->work_dir)
		{
			$rsFile = CFile::GetByID($FILE_ID);
			if($arFile = $rsFile->Fetch())
			{
				$strFile = $arFile["SUBDIR"]."/".$arFile["FILE_NAME"];

				$strOldFile = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir", "upload")."/".$strFile;
				$strOldFile = str_replace("//","/",$strOldFile);

				$strNewFile = $this->work_dir.$this->file_dir.$strFile;
				$strNewFile = str_replace("//","/",$strNewFile);

				CheckDirPath($strNewFile);
				if(@copy($strOldFile, $strNewFile))
					return $this->file_dir.$strFile;
			}
		}
		return "";
	}

	function StartExportMetadata()
	{
		$xml_id = $this->GetIBlockXML_ID($this->arIBlock["ID"], $this->arIBlock["XML_ID"]);
		$this->arIBlock["XML_ID"] = $xml_id;
		fwrite($this->fp, "\t<".GetMessage("IBLOCK_XML2_METADATA").">\n");
		fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialchars($xml_id)."</".GetMessage("IBLOCK_XML2_ID").">\n");
		fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_NAME").">".htmlspecialchars($this->arIBlock["NAME"])."</".GetMessage("IBLOCK_XML2_NAME").">\n");
		if(strlen($this->arIBlock["DESCRIPTION"])>0)
			fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_DESCRIPTION").">".htmlspecialchars(FormatText($this->arIBlock["DESCRIPTION"], $this->arIBlock["DESCRIPTION_TYPE"]))."</".GetMessage("IBLOCK_XML2_DESCRIPTION").">\n");
	}

	function ExportSections(&$SECTION_MAP, $start_time, $INTERVAL)
	{
		$counter = 0;
		if(!array_key_exists("CURRENT_DEPTH", $this->next_step))
			$this->next_step["CURRENT_DEPTH"]=0;
		else // this makes second "step"
			return $counter;
		$SECTION_MAP = array();
		$arSort = array(
			"left_margin"=>"asc",
		);
		$arFilter = array(
			"IBLOCK_ID" => $this->arIBlock["ID"],
			"GLOBAL_ACTIVE" => "Y",
		);
		$rsSections = CIBlockSection::GetList($arSort, $arFilter);
		fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_GROUPS").">\n");
		while($arSection = $rsSections->Fetch())
		{
			$white_space = str_repeat("\t\t", $arSection["DEPTH_LEVEL"]);

			while($this->next_step["CURRENT_DEPTH"] >= $arSection["DEPTH_LEVEL"])
			{
				fwrite($this->fp, str_repeat("\t\t", $this->next_step["CURRENT_DEPTH"])."\t\t</".GetMessage("IBLOCK_XML2_GROUPS").">\n");
				fwrite($this->fp, str_repeat("\t\t", $this->next_step["CURRENT_DEPTH"]-1)."\t\t\t</".GetMessage("IBLOCK_XML2_GROUP").">\n");
				$this->next_step["CURRENT_DEPTH"]--;
			}

			$xml_id = $this->GetSectionXML_ID($this->arIBlock["ID"], $arSection["ID"], $arSection["XML_ID"]);
			$SECTION_MAP[$arSection["ID"]] = $xml_id;

			fwrite($this->fp,
				$white_space."\t<".GetMessage("IBLOCK_XML2_GROUP").">\n".
				$white_space."\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialchars($xml_id)."</".GetMessage("IBLOCK_XML2_ID").">\n".
				$white_space."\t\t<".GetMessage("IBLOCK_XML2_NAME").">".htmlspecialchars($arSection["NAME"])."</".GetMessage("IBLOCK_XML2_NAME").">\n"
			);
			if(strlen($arSection["DESCRIPTION"])>0)
				fwrite($this->fp, $white_space."\t\t<".GetMessage("IBLOCK_XML2_DESCRIPTION").">".htmlspecialchars(FormatText($arSection["DESCRIPTION"], $arSection["DESCRIPTION_TYPE"]))."</".GetMessage("IBLOCK_XML2_DESCRIPTION").">\n");
			if($this->bExtended)
			{
				fwrite($this->fp, $white_space."\t\t<".GetMessage("IBLOCK_XML2_BX_SORT").">".intval($arSection["SORT"])."</".GetMessage("IBLOCK_XML2_BX_SORT").">\n");
				fwrite($this->fp, $white_space."\t\t<".GetMessage("IBLOCK_XML2_BX_CODE").">".htmlspecialchars($arSection["CODE"])."</".GetMessage("IBLOCK_XML2_BX_CODE").">\n");
				fwrite($this->fp, $white_space."\t\t<".GetMessage("IBLOCK_XML2_BX_PICTURE").">".htmlspecialchars($this->ExportFile($arSection["PICTURE"]))."</".GetMessage("IBLOCK_XML2_BX_PICTURE").">\n");
				fwrite($this->fp, $white_space."\t\t<".GetMessage("IBLOCK_XML2_BX_DETAIL_PICTURE").">".htmlspecialchars($this->ExportFile($arSection["DETAIL_PICTURE"]))."</".GetMessage("IBLOCK_XML2_BX_DETAIL_PICTURE").">\n");
			}

			fwrite($this->fp, $white_space."\t\t<".GetMessage("IBLOCK_XML2_GROUPS").">\n");

			$this->next_step["CURRENT_DEPTH"] = $arSection["DEPTH_LEVEL"];

			$counter++;
		}
		while($this->next_step["CURRENT_DEPTH"] > 0)
		{
			fwrite($this->fp, str_repeat("\t\t", $this->next_step["CURRENT_DEPTH"])."\t\t</".GetMessage("IBLOCK_XML2_GROUPS").">\n");
			fwrite($this->fp, str_repeat("\t\t", $this->next_step["CURRENT_DEPTH"]-1)."\t\t\t</".GetMessage("IBLOCK_XML2_GROUP").">\n");
			$this->next_step["CURRENT_DEPTH"]--;
		}
		fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_GROUPS").">\n");
		return $counter;
	}

	function ExportProperties(&$PROPERTY_MAP)
	{
		$PROPERTY_MAP = array();

		fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_PROPERTIES").">\n");

		if($this->bExtended)
		{
			$arElementFields = array(
				"CML2_CODE" => GetMessage("IBLOCK_XML2_SYMBOL_CODE"),
				"CML2_SORT" => GetMessage("IBLOCK_XML2_SORT"),
				"CML2_ACTIVE_FROM" => GetMessage("IBLOCK_XML2_START_TIME"),
				"CML2_ACTIVE_TO" => GetMessage("IBLOCK_XML2_END_TIME"),
				"CML2_PREVIEW_TEXT" => GetMessage("IBLOCK_XML2_ANONS"),
				"CML2_PREVIEW_PICTURE" => GetMessage("IBLOCK_XML2_PREVIEW_PICTURE"),
			);

			foreach($arElementFields as $key => $value)
				fwrite($this->fp,
					"\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY").">\n".
					"\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".$key."</".GetMessage("IBLOCK_XML2_ID").">\n".
					"\t\t\t\t<".GetMessage("IBLOCK_XML2_NAME").">".$value."</".GetMessage("IBLOCK_XML2_NAME").">\n".
					"\t\t\t\t<".GetMessage("IBLOCK_XML2_MULTIPLE").">false</".GetMessage("IBLOCK_XML2_MULTIPLE").">\n".
					"\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY").">\n"
				);
		}

		$arFilter = array(
			"IBLOCK_ID" => $this->arIBlock["ID"],
			"ACTIVE" => "Y",
		);
		$arSort = array(
			"sort" => "asc",
		);
		$arProps = array();
		$obProp = new CIBlockProperty();
		$rsProp = $obProp->GetList($arSort, $arFilter);
		while($arProp = $rsProp->Fetch())
		{
			fwrite($this->fp, "\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY").">\n");

			$xml_id = $this->GetPropertyXML_ID($this->arIBlock["ID"], $arProp["NAME"], $arProp["ID"], $arProp["XML_ID"]);
			$PROPERTY_MAP[$arProp["ID"]] = $xml_id;
			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialchars($xml_id)."</".GetMessage("IBLOCK_XML2_ID").">\n");

			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_NAME").">".htmlspecialchars($arProp["NAME"])."</".GetMessage("IBLOCK_XML2_NAME").">\n");
			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_MULTIPLE").">".($arProp["MULTIPLE"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_MULTIPLE").">\n");
			if($arProp["PROPERTY_TYPE"]=="L")
			{
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_CHOICE_VALUES").">\n");
				$rsEnum = CIBlockProperty::GetPropertyEnum($arProp["ID"]);
				while($arEnum = $rsEnum->Fetch())
				{
					fwrite($this->fp, "\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialchars($arEnum["VALUE"])."</".GetMessage("IBLOCK_XML2_VALUE").">\n");
					if($this->bExtended)
					{
						fwrite($this->fp, "\t\t\t\t\t<".GetMessage("IBLOCK_XML2_CHOICE").">\n");
						fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialchars($arEnum["XML_ID"])."</".GetMessage("IBLOCK_XML2_ID").">\n");
						fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialchars($arEnum["VALUE"])."</".GetMessage("IBLOCK_XML2_VALUE").">\n");
						fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_BY_DEFAULT").">".($arEnum["DEF"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BY_DEFAULT").">\n");
						fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_SORT").">".intval($arEnum["SORT"])."</".GetMessage("IBLOCK_XML2_SORT").">\n");
						fwrite($this->fp, "\t\t\t\t\t</".GetMessage("IBLOCK_XML2_CHOICE").">\n");
					}
				}
				fwrite($this->fp, "\t\t\t\t</".GetMessage("IBLOCK_XML2_CHOICE_VALUES").">\n");
			}
			if($this->bExtended)
			{
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_SORT").">".intval($arProp["SORT"])."</".GetMessage("IBLOCK_XML2_BX_SORT").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_CODE").">".htmlspecialchars($arProp["CODE"])."</".GetMessage("IBLOCK_XML2_BX_CODE").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_DEFAULT_VALUE").">".htmlspecialchars($arProp["DEFAULT_VALUE"])."</".GetMessage("IBLOCK_XML2_BX_DEFAULT_VALUE").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_PROERTY_TYPE").">".htmlspecialchars($arProp["PROPERTY_TYPE"])."</".GetMessage("IBLOCK_XML2_BX_PROERTY_TYPE").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_ROWS").">".htmlspecialchars($arProp["ROW_COUNT"])."</".GetMessage("IBLOCK_XML2_BX_ROWS").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_COLUMNS").">".htmlspecialchars($arProp["COL_COUNT"])."</".GetMessage("IBLOCK_XML2_BX_COLUMNS").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_LIST_TYPE").">".htmlspecialchars($arProp["LIST_TYPE"])."</".GetMessage("IBLOCK_XML2_BX_LIST_TYPE").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_FILE_EXT").">".htmlspecialchars($arProp["FILE_TYPE"])."</".GetMessage("IBLOCK_XML2_BX_FILE_EXT").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_FIELDS_COUNT").">".htmlspecialchars($arProp["MULTIPLE_CNT"])."</".GetMessage("IBLOCK_XML2_BX_FIELDS_COUNT").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_LINKED_IBLOCK").">".htmlspecialchars($this->GetIBlockXML_ID($arProp["LINK_IBLOCK_ID"]))."</".GetMessage("IBLOCK_XML2_BX_LINKED_IBLOCK").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_WITH_DESCRIPTION").">".($arProp["WITH_DESCRIPTION"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_WITH_DESCRIPTION").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_SEARCH").">".($arProp["SEARCHABLE"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_SEARCH").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_FILTER").">".($arProp["FILTRABLE"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_FILTER").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_USER_TYPE").">".htmlspecialchars($arProp["USER_TYPE"])."</".GetMessage("IBLOCK_XML2_BX_USER_TYPE").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_IS_REQUIRED").">".($arProp["IS_REQUIRED"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_IS_REQUIRED").">\n");
			}
			fwrite($this->fp, "\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY").">\n");
		}
		fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_PROPERTIES").">\n");
	}

	function ExportPrices()
	{
		if($this->next_step["catalog"])
		{
			$rsPrice = CCatalogGroup::GetList(array(), array());
			if($arPrice = $rsPrice->Fetch())
			{
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_PRICE_TYPES").">\n");
				do {
					fwrite($this->fp, "\t\t\t<".GetMessage("IBLOCK_XML2_PRICE_TYPE").">\n");
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialchars($arPrice["NAME"])."</".GetMessage("IBLOCK_XML2_ID").">\n");
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_NAME").">".htmlspecialchars($arPrice["NAME"])."</".GetMessage("IBLOCK_XML2_NAME").">\n");
					fwrite($this->fp, "\t\t\t</".GetMessage("IBLOCK_XML2_PRICE_TYPE").">\n");
				} while ($arPrice = $rsPrice->Fetch());
				fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_PRICE_TYPES").">\n");
			}
		}
	}

	function EndExportMetadata()
	{
		fwrite($this->fp, "\t</".GetMessage("IBLOCK_XML2_METADATA").">\n");
	}

	function StartExportCatalog($with_metadata = true)
	{
		if($this->next_step["catalog"])
			fwrite($this->fp, "\t<".GetMessage("IBLOCK_XML2_OFFER_LIST").">\n");
		else
			fwrite($this->fp, "\t<".GetMessage("IBLOCK_XML2_CATALOG").">\n");

		$xml_id = $this->GetIBlockXML_ID($this->arIBlock["ID"], $this->arIBlock["XML_ID"]);
		$this->arIBlock["XML_ID"] = $xml_id;

		fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialchars($xml_id)."</".GetMessage("IBLOCK_XML2_ID").">\n");
		if($with_metadata)
		{
			fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_METADATA_ID").">".htmlspecialchars($xml_id)."</".GetMessage("IBLOCK_XML2_METADATA_ID").">\n");
			fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_NAME").">".htmlspecialchars($this->arIBlock["NAME"])."</".GetMessage("IBLOCK_XML2_NAME").">\n");

			if(strlen($this->arIBlock["DESCRIPTION"])>0)
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_DESCRIPTION").">".htmlspecialchars(FormatText($this->arIBlock["DESCRIPTION"], $this->arIBlock["DESCRIPTION_TYPE"]))."</".GetMessage("IBLOCK_XML2_DESCRIPTION").">\n");

			if($this->bExtended)
			{
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_CODE").">".htmlspecialchars($this->arIBlock["CODE"])."</".GetMessage("IBLOCK_XML2_BX_CODE").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_SORT").">".intval($this->arIBlock["SORT"])."</".GetMessage("IBLOCK_XML2_BX_SORT").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_LIST_URL").">".htmlspecialchars($this->arIBlock["LIST_PAGE_URL"])."</".GetMessage("IBLOCK_XML2_BX_LIST_URL").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_DETAIL_URL").">".htmlspecialchars($this->arIBlock["DETAIL_PAGE_URL"])."</".GetMessage("IBLOCK_XML2_BX_DETAIL_URL").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_SECTION_URL").">".htmlspecialchars($this->arIBlock["SECTION_PAGE_URL"])."</".GetMessage("IBLOCK_XML2_BX_SECTION_URL").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_PICTURE").">".htmlspecialchars($this->ExportFile($this->arIBlock["PICTURE"]))."</".GetMessage("IBLOCK_XML2_BX_PICTURE").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_INDEX_ELEMENTS").">".($this->arIBlock["INDEX_ELEMENT"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_INDEX_ELEMENTS").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_INDEX_SECTIONS").">".($this->arIBlock["INDEX_SECTION"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_INDEX_SECTIONS").">\n");
				//fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_SECTIONS_NAME").">".htmlspecialchars($this->arIBlock["SECTIONS_NAME"])."</".GetMessage("IBLOCK_XML2_BX_SECTIONS_NAME").">\n");
				//fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_SECTION_NAME").">".htmlspecialchars($this->arIBlock["SECTION_NAME"])."</".GetMessage("IBLOCK_XML2_BX_SECTION_NAME").">\n");
				//fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_ELEMENTS_NAME").">".htmlspecialchars($this->arIBlock["ELEMENTS_NAME"])."</".GetMessage("IBLOCK_XML2_BX_ELEMENTS_NAME").">\n");
				//fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_ELEMENT_NAME").">".htmlspecialchars($this->arIBlock["ELEMENT_NAME"])."</".GetMessage("IBLOCK_XML2_BX_ELEMENT_NAME").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_WORKFLOW").">".($this->arIBlock["WORKFLOW"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_WORKFLOW").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_LABELS").">\n");
				$arLabels = CIBlock::GetMessages($this->arIBlock["ID"]);
				foreach($arLabels as $id => $label)
				{
					fwrite($this->fp, "\t\t\t<".GetMessage("IBLOCK_XML2_LABEL").">\n");
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".$id."</".GetMessage("IBLOCK_XML2_ID").">\n");
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".$label."</".GetMessage("IBLOCK_XML2_VALUE").">\n");
					fwrite($this->fp, "\t\t\t</".GetMessage("IBLOCK_XML2_LABEL").">\n");
				}
				fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_LABELS").">\n");
			}
		}

		if($with_metadata || $this->only_price)
		{
			$this->ExportPrices();
		}

		if($this->next_step["catalog"])
			fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_OFFERS").">\n");
		else
			fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_POSITIONS").">\n");
	}

	function ExportElements($PROPERTY_MAP, $SECTION_MAP, $start_time, $INTERVAL, $counter_limit = 0)
	{
		$bWF = CModule::IncludeModule("workflow");
		$counter = 0;
		$arSelect = array(
			"ID",
			"IBLOCK_ID",
			"XML_ID",
			"CODE",
			"NAME",
			"PREVIEW_TEXT",
			"PREVIEW_TEXT_TYPE",
			"ACTIVE_FROM",
			"ACTIVE_TO",
			"SORT",
			"TAGS",
			"DETAIL_TEXT",
			"DETAIL_TEXT_TYPE",
			"PREVIEW_PICTURE",
			"DETAIL_PICTURE",
		);
		$arFilter = array (
			"IBLOCK_ID"=> $this->arIBlock["ID"],
			"ACTIVE" => "Y",
			">ID" => $this->next_step["LAST_ID"],
		);
		$arOrder = array(
			"ID" => "ASC",
		);
		$rsElements = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);
		while($obElement = $rsElements->GetNextElement())
		{
			$arElement = $obElement->GetFields();
			$arProps = $obElement->GetProperties(false, array("ACTIVE"=>"Y"));

			if($this->next_step["catalog"])
				fwrite($this->fp, "\t\t\t<".GetMessage("IBLOCK_XML2_OFFER").">\n");
			else
				fwrite($this->fp, "\t\t\t<".GetMessage("IBLOCK_XML2_POSITION").">\n");

			if(strlen($arElement["XML_ID"])>0)
				$xml_id = $arElement["XML_ID"];
			else
				$xml_id = $arElement["ID"];

			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".$xml_id."</".GetMessage("IBLOCK_XML2_ID").">\n");

			if(!$this->only_price)
			{
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_NAME").">".$arElement["NAME"]."</".GetMessage("IBLOCK_XML2_NAME").">\n");
				if($this->bExtended)
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_TAGS").">".$arElement["TAGS"]."</".GetMessage("IBLOCK_XML2_BX_TAGS").">\n");

				$arSections = array();
				$rsSections = CIBlockElement::GetElementGroups($arElement["ID"]);
				while($arSection = $rsSections->Fetch())
					if(array_key_exists($arSection["ID"], $SECTION_MAP))
						$arSections[] = $SECTION_MAP[$arSection["ID"]];
				if(count($arSections)>0)
				{
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_GROUPS").">\n");
					foreach($arSections as $xml_id)
					{
						fwrite($this->fp, "\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialchars($xml_id)."</".GetMessage("IBLOCK_XML2_ID").">\n");
					}
					fwrite($this->fp, "\t\t\t\t</".GetMessage("IBLOCK_XML2_GROUPS").">\n");
				}
				if(strlen($arElement["DETAIL_TEXT"])>0)
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_DESCRIPTION").">".htmlspecialchars(FormatText($arElement["~DETAIL_TEXT"], $arElement["DETAIL_TEXT_TYPE"]))."</".GetMessage("IBLOCK_XML2_DESCRIPTION").">\n");

				if($value = $this->ExportFile($arElement["DETAIL_PICTURE"]))
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_PICTURE").">".htmlspecialchars($value)."</".GetMessage("IBLOCK_XML2_PICTURE").">\n");

				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTIES_VALUES").">\n");

				if($this->bExtended)
				{
					fwrite($this->fp,
						"\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">CML2_CODE</".GetMessage("IBLOCK_XML2_ID").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialchars($arElement["CODE"])."</".GetMessage("IBLOCK_XML2_VALUE").">\n".
						"\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n"
					);
					fwrite($this->fp,
						"\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">CML2_SORT</".GetMessage("IBLOCK_XML2_ID").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".intval($arElement["SORT"])."</".GetMessage("IBLOCK_XML2_VALUE").">\n".
						"\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n"
					);
					fwrite($this->fp,
						"\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">CML2_ACTIVE_FROM</".GetMessage("IBLOCK_XML2_ID").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".CDatabase::FormatDate($arElement["ACTIVE_FROM"], CLang::GetDateFormat("FULL"), "YYYY-MM-DD HH:MI:SS")."</".GetMessage("IBLOCK_XML2_VALUE").">\n".
						"\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n"
					);
					fwrite($this->fp,
						"\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">CML2_ACTIVE_TO</".GetMessage("IBLOCK_XML2_ID").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".CDatabase::FormatDate($arElement["ACTIVE_TO"], CLang::GetDateFormat("FULL"), "YYYY-MM-DD HH:MI:SS")."</".GetMessage("IBLOCK_XML2_VALUE").">\n".
						"\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n"
					);
					fwrite($this->fp,
						"\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">CML2_PREVIEW_TEXT</".GetMessage("IBLOCK_XML2_ID").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialchars(FormatText($arElement["~PREVIEW_TEXT"], $arElement["PREVIEW_TEXT_TYPE"]))."</".GetMessage("IBLOCK_XML2_VALUE").">\n".
						"\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n"
					);
					fwrite($this->fp,
						"\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">CML2_PREVIEW_PICTURE</".GetMessage("IBLOCK_XML2_ID").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialchars($this->ExportFile($arElement["PREVIEW_PICTURE"]))."</".GetMessage("IBLOCK_XML2_VALUE").">\n".
						"\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n"
					);
				}

				foreach($arProps as $arProp)
				{
					fwrite($this->fp, "\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n");
					fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialchars($PROPERTY_MAP[$arProp["ID"]])."</".GetMessage("IBLOCK_XML2_ID").">\n");
					if($arProp["MULTIPLE"]=="N" || !is_array($arProp["VALUE"]))
					{
						$arProp["VALUE"] = array($arProp["VALUE"]);
						$arProp["DESCRIPTION"] = array($arProp["DESCRIPTION"]);
						if(isset($arProp["VALUE_ENUM_ID"]))
							$arProp["VALUE_ENUM_ID"] = array($arProp["VALUE_ENUM_ID"]);
					}
					foreach($arProp["VALUE"] as $i=>$value)
					{
						if(strlen($value)>0)
						{
							if($this->bExtended)
							{
								if($arProp["PROPERTY_TYPE"]=="L")
								{
									$value = CIBlockPropertyEnum::GetByID($arProp["VALUE_ENUM_ID"][$i]);
									$value = $value["XML_ID"];
								}
								elseif($arProp["PROPERTY_TYPE"]=="F")
								{
									$value = $this->ExportFile($value);
								}
								elseif($arProp["PROPERTY_TYPE"]=="G")
								{
									$value = $this->GetSectionXML_ID($arProp["LINK_IBLOCK_ID"], $value);
								}
								elseif($arProp["PROPERTY_TYPE"]=="E")
								{
									$value = $this->GetElementXML_ID($arProp["LINK_IBLOCK_ID"], $value);
								}
								if(is_array($value))
								{
									$bSerialized = true;
									$value = serialize($value);
								}
								else
								{
									$bSerialized = false;
								}
							}
							fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialchars($value)."</".GetMessage("IBLOCK_XML2_VALUE").">\n");
							if($this->bExtended)
							{
								fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUE").">\n");
								if($bSerialized)
									fwrite($this->fp, "\t\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_SERIALIZED").">true</".GetMessage("IBLOCK_XML2_SERIALIZED").">\n");
								fwrite($this->fp, "\t\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialchars($value)."</".GetMessage("IBLOCK_XML2_VALUE").">\n");
								fwrite($this->fp, "\t\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_DESCRIPTION").">".htmlspecialchars($arProp["DESCRIPTION"][$i])."</".GetMessage("IBLOCK_XML2_DESCRIPTION").">\n");
								fwrite($this->fp, "\t\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUE").">\n");
							}
						}
					}
					fwrite($this->fp, "\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n");
				}
				fwrite($this->fp, "\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTIES_VALUES").">\n");
			}

			if($this->next_step["catalog"])
			{
				$arPrices = array();
				$rsPrices = CPrice::GetList(array(), array("PRODUCT_ID" => $arElement["ID"]));
				while($arPrice = $rsPrices->Fetch())
				{
					if(!$arPrice["QUANTITY_FROM"] && !$arPrice["QUANTITY_TO"])
					{
						$arPrices[] = array(
							GetMessage("IBLOCK_XML2_PRICE_TYPE_ID") => $this->prices[$arPrice["CATALOG_GROUP_ID"]],
							GetMessage("IBLOCK_XML2_PRICE_FOR_ONE") => $arPrice["PRICE"],
							GetMessage("IBLOCK_XML2_CURRENCY") => $arPrice["CURRENCY"],
							GetMessage("IBLOCK_XML2_MEASURE") => GetMessage("IBLOCK_XML2_PCS"),
						);
					}
				}
				if(count($arPrices)>0)
				{
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_PRICES").">\n");
					foreach($arPrices as $arPrice)
					{
						fwrite($this->fp, "\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PRICE").">\n");
						foreach($arPrice as $key=>$value)
						{
							fwrite($this->fp, "\t\t\t\t\t\t<".$key.">".htmlspecialchars($value)."</".$key.">\n");
						}
						fwrite($this->fp, "\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PRICE").">\n");
					}
					fwrite($this->fp, "\t\t\t\t</".GetMessage("IBLOCK_XML2_PRICES").">\n");
				}
			}

			if($this->next_step["catalog"])
				fwrite($this->fp, "\t\t\t</".GetMessage("IBLOCK_XML2_OFFER").">\n");
			else
				fwrite($this->fp, "\t\t\t</".GetMessage("IBLOCK_XML2_POSITION").">\n");

			$this->next_step["LAST_ID"] = $arElement["ID"];
			$counter++;
			if($INTERVAL > 0 && (time()-$start_time) > $INTERVAL)
				break;
			if($counter_limit > 0 && ($counter >= $counter_limit))
				break;
		}
		return $counter;
	}

	function EndExportCatalog()
	{
		if($this->next_step["catalog"])
		{
			fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_OFFERS").">\n");
			fwrite($this->fp, "\t</".GetMessage("IBLOCK_XML2_OFFER_LIST").">\n");
		}
		else
		{
			fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_POSITIONS").">\n");
			fwrite($this->fp, "\t</".GetMessage("IBLOCK_XML2_CATALOG").">\n");
		}
	}

	function EndExport()
	{
		fwrite($this->fp, "</".GetMessage("IBLOCK_XML2_COMMERCE_INFO").">\n");
	}
}
?>