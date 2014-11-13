<?
//<title>CommerceML</title>
include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/lang/", "/import_setup_templ.php"));
$startImportExecTime = getmicrotime();

global $USER;
$bTmpUserCreated = False;
if (!isset($USER))
{
	$bTmpUserCreated = True;
	$USER = new CUser;
}

$strImportErrorMessage = "";
$strImportOKMessage = "";

if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/1c_mutator.php"))
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/1c_mutator.php");


class XMLNode
{
	var $childs;
	var $parent;
	var $value;
	var $attributes;
	var $name;
	var $r;

	function XMLNode($parent, $attributes, $name)
	{
		$this->r=rand();
		$this->parent=&$parent;
		$this->attributes=$attributes;
		$this->name=$name;
		$this->childs=NULL;
		$this->value=rand();
		if($parent!=NULL)
			$this->parent->childs[$name][]=&$this;
	}

	function GetAttribute($name)
	{
		return $this->attributes[$name];
	}

	function XMLSetValue($new_val)
	{
		$this->r=rand();
//		echo " SetValue===".$this->name." [".$this->r."] = ".$new_val." \n";
		$this->value=$new_val;
	}

	function &select_nodes($str_node)
	{
		$tmp = trim($str_node, "/ \r\n\t\0\x0B");
		if (!($p = strpos($tmp, "/")))
			$p = strlen($tmp);
		$str_node = trim(substr($tmp, 0, $p));
		if(strlen($str_node)<=0)
			return $this;

		$tmp = trim(substr($tmp, $p+1));
		if(strlen($tmp)<=0)
			return $this->childs[$str_node];

		return $this->childs[$str_node][0]->select_nodes($tmp);

		if(!is_array($this->childs)) return array();
		$result=array(0=>$this->childs);

		for($i=1;$i<count($tmp);$i++)
		{
			if($tmp[$i]!="")
			{
				if(!is_array($result[0]->childs)) return array();
				$result=&$result[0]->childs[$tmp[$i]];
			}
		}

		return $result;
	}
}

class XMLParser
{
	var $parser;
	var $cur;
	var $xml;

	function Load($file)
	{
		if ($fd = fopen($file, "rb"))
		{
			$content = fread($fd, filesize($file));
			fclose ($fd);
			$this->parse($content);
			return true;
		}

		return false;
	}

	function LoadString(&$text)
	{
		if (strlen($text) > 0)
		{
			$this->parse($text);
			return true;
		}

		return false;
	}

	function unquote($str)
	{
		$search = array(
					"'&(quot|#34);'i",
					"'&(amp|#38);'i",
					"'&(lt|#60);'i",
					"'&(gt|#62);'i",
					"'&#(\d+);'e"
					);

		$replace = array(
					"\"",
					"&",
					"<",
					">",
					"chr(\\1)"
					);

		$str = preg_replace($search, $replace, $str);

		return $str;
	}

	function parse(&$data)		//, $last
	{
		$data = preg_replace("#<\!--.*?-->#s", "", $data);

		$pb = strpos($data, "<");
		while($pb!==false)
		{
			$pe = strpos($data, ">", $pb);
			if($pe === false) break;
			$tag_cont = substr($data, $pb+1, $pe-$pb-1);
			$pb = strpos($data, "<", $pe);
			
			$check_str = substr($tag_cont, 0, 1);
			if($check_str=="?")
				continue;
			elseif($check_str=="!")
				continue;
			elseif($check_str=="/")
				$this->endElement(substr($tag_cont, 1));
			else
			{
				$p=0;
				$ltag_cont = strlen($tag_cont);
				while(($p < $ltag_cont) && (strpos(" \t\n\r", substr($tag_cont, $p, 1))===false))
					$p++;
				$name = substr($tag_cont, 0, $p);
				$at = substr($tag_cont, $p);
				if(strpos($at, "&")!==false)
					$bAmp = true;
				else
					$bAmp = false;

				preg_match_all("/(\\S+)\\s*=\\s*[\"](.*?)[\"]/s".BX_UTF_PCRE_MODIFIER, $at, $attrs_tmp);
				$attrs = Array();
				for($i=0; $i<count($attrs_tmp[1]); $i++)
					$attrs[$attrs_tmp[1][$i]] = ($bAmp ? $this->unquote($attrs_tmp[2][$i]) : $attrs_tmp[2][$i]);
				$this->startElement($name, $attrs);
				if(substr($tag_cont, -1) === "/")
					$this->endElement($name);
			}
		}
	}


	function startElement($name, $attrs)
	{
		$bTemp=false;
		if($this->cur==NULL)
			$bTemp=true;

		$this->cur = &new XMLNode(&$this->cur, $attrs, $name);

		if($bTemp)
			$this->xml=&$this->cur;
	}

	function endElement($name)
	{
		if($this->cur->parent!=NULL)
			$this->cur = &$this->cur->parent;
	}

	function cdata($parser,$cdata)
	{
		if(trim($cdata)!="")
		{
			$this->cur->XMLSetValue(trim($cdata));
		}
	}

	function select_nodes_attrib($str_node)
	{
		$tmp=$this->select_nodes($str_node);
		$result=array();
		for($i=0;$i<count($tmp);$i++)
		{
			$arTemp=$tmp[$i]->attributes;
			$result[]=$arTemp;
		}

		return $result;
	}

	function select_nodes($str_node)
	{
		if(!is_object($this->xml)) return array();
		$result=array(0=>$this->xml);
		$tmp = explode ("/", $str_node);
		for($i=2;$i<count($tmp);$i++)
		{
			if($tmp[$i]!="")
			{
				if(!is_array($result[0]->childs)) return array();
				$result=&$result[0]->childs[$tmp[$i]];
			}
		}

		return $result;
	}
}



function AddSectionsRecursive($parent="", $parent_id=0)
{
	global $tmpid, $arGrTmp, $strImportErrorMessage, $IBLOCK_ID, $arGroups, $STT_GROUP_ADD, $STT_GROUP_UPDATE, $STT_GROUP_ERROR;

	$arChld = $arGrTmp[$tmpid.$parent];
	if(!is_array($arChld)) return;
	for($i=0; $i<count($arChld); $i++)
	{
		$GROUP_XML_ID = $arChld[$i]["GROUP_XML_ID"];
		$GROUP_NAME = $arChld[$i]["GROUP_NAME"];

		$bs = new CIBlockSection;
		$res = CIBlockSection::GetList(Array(), Array("XML_ID"=>$GROUP_XML_ID, "IBLOCK_ID"=>$IBLOCK_ID));
		$bNewGroup_tmp = False;
		if($arr = $res->Fetch())
		{
			$GROUP_ID = $arr["ID"];
			$res = $bs->Update($GROUP_ID, Array("NAME"=>$GROUP_NAME, "TMP_ID"=>$tmpid, "IBLOCK_SECTION_ID"=>$parent_id));
		}
		else
		{
			$bNewGroup_tmp = True;
			$arFields = Array(
				"ACTIVE"=>"Y",
				"IBLOCK_SECTION_ID"=>$parent_id,
				"IBLOCK_ID"=>$IBLOCK_ID,
				"NAME"=>$GROUP_NAME,
				"TMP_ID"=>$tmpid,
				"XML_ID"=>$GROUP_XML_ID
				);

			$GROUP_ID = $bs->Add($arFields);
			$res = ($GROUP_ID>0);
		}

		if(!$res)
		{
			$strImportErrorMessage .= str_replace("#ERROR#", $bs->LAST_ERROR, str_replace("#NAME#", "[".$GROUP_ID."] \"".$GROUP_NAME."\" (".$GROUP_XML_ID.")", GetMessage("CICML_ERROR_ADD_SECTION"))).".<br>";
			$STT_GROUP_ERROR++;
		}
		else
		{
			if ($bNewGroup_tmp) $STT_GROUP_ADD++;
			else $STT_GROUP_UPDATE++;

			$arGroups[$GROUP_XML_ID] = $GROUP_ID;
			AddSectionsRecursive($GROUP_XML_ID, $GROUP_ID);
		}
	}
}

if (!function_exists("file_get_contents"))
{
	function file_get_contents($filename)
	{
		$fd = fopen("$filename", "rb");
		$content = fread($fd, filesize($filename));
		fclose($fd);
		return $content;
	}
}


$DATA_FILE_NAME = "";

$STT_GROUP_ADD = 0;		$STT_GROUP_UPDATE = 0;		$STT_GROUP_ERROR = 0;
$STT_CATALOG_ADD = 0;	$STT_CATALOG_UPDATE = 0;	$STT_CATALOG_ERROR = 0;
$STT_PROP_ADD = 0;		$STT_PROP_UPDATE = 0;		$STT_PROP_ERROR = 0;
$STT_PRODUCT_ADD = 0;	$STT_PRODUCT_UPDATE = 0;	$STT_PRODUCT_ERROR = 0;

if (isset($_FILES["FILE_1C"]) && is_uploaded_file($_FILES["FILE_1C"]["tmp_name"]))
	$DATA_FILE_NAME = $_FILES["FILE_1C"]["tmp_name"];

if (strlen($DATA_FILE_NAME) <= 0)
{
	if (strlen($URL_FILE_1C) > 0)
	{
		$URL_FILE_1C = Rel2Abs("/", $URL_FILE_1C);
		if (file_exists($_SERVER["DOCUMENT_ROOT"].$URL_FILE_1C) && is_file($_SERVER["DOCUMENT_ROOT"].$URL_FILE_1C))
			$DATA_FILE_NAME = $_SERVER["DOCUMENT_ROOT"].$URL_FILE_1C;
	}
}

if (strlen($DATA_FILE_NAME)<=0)
	$strImportErrorMessage .= GetMessage("CICML_NO_LOAD_FILE")."<br>";

if (strlen($IBLOCK_TYPE_ID) <= 0)
{
	$IBLOCK_TYPE_ID = COption::GetOptionString("catalog", "default_catalog_1c", "");
	if (strlen($IBLOCK_TYPE_ID) <= 0)
	{
		$iblocks = CIBlockType::GetList(Array($by=>$order));
		if ($iblocks->ExtractFields("f_"))
			$IBLOCK_TYPE_ID = $f_ID;
	}
}
if (strlen($IBLOCK_TYPE_ID) <= 0)
	$strImportErrorMessage .= GetMessage("CICML_NO_IBLOCK")."<br>";

if ($outFileAction!="F" && $outFileAction!="H" && $outFileAction!="D")
	$outFileAction = COption::GetOptionString("catalog", "default_outfile_action", "D");

if ($outFileAction!="F" && $outFileAction!="H")
	$outFileAction = "D";


if (strlen($strImportErrorMessage) <= 0)
{
	$xml = new XMLParser();
	$xml_content = file_get_contents($DATA_FILE_NAME);

	if (!$xml_content || strlen($xml_content) <= 0)
		$strImportErrorMessage .= GetMessage("CICML_NO_LOAD_DATA")."<br>";
}

if (strlen($strImportErrorMessage) <= 0)
{
	if ($CONVERT_UTF8 != "Y" && $CONVERT_UTF8 != "N")
		$CONVERT_UTF8 = COption::GetOptionString("catalog", "default_convert_utf8", "N");

	if (true)
	{
		$xml_content = $GLOBALS['APPLICATION']->ConvertCharset($xml_content, $CONVERT_UTF8 == "Y"? "utf-8": "windows-1251", LANG_CHARSET);
		
		if (!$xml_content || strlen($xml_content) <= 0)
		{
			if ($ex = $APPLICATION->GetException())
				$strImportErrorMessage .= $ex->GetString();
			else
				$strImportErrorMessage .= "Error converting from UTF-8"."<br>";
		}
		/*
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/charset_converter.php");
		$converter = new CharsetConverter();
		$xml_content = $converter->Convert($xml_content, "utf-8", "windows-1251");

		if (!$xml_content || strlen($xml_content) <= 0)
			$strImportErrorMessage .= $converter->errorMessage."<br>";
		*/
	}
}

global $nameUTF;
include(dirname(__FILE__).'/ru/commerceml_run_name.php');

if (strlen($strImportErrorMessage) <= 0)
{
	$xml->LoadString($xml_content);

	$comm = $xml->select_nodes("/BizTalk/Body/".$nameUTF['CommerceInfo']);
	if (!is_object($comm[0]))
	{
		$comm = $xml->select_nodes("/".$nameUTF['CommerceInfo']);
		if (!is_object($comm[0]))
			$strImportErrorMessage .= GetMessage("CICML_INVALID_FILE")."<br>";
	}
}

if (strlen($strImportErrorMessage) <= 0)
{
	$arPriceType = Array();
	$offerlists = $comm[0]->select_nodes("/".$nameUTF['OffersList']);
	for ($i = 0; $i < count($offerlists); $i++)
	{
		$xOfferListNode = $offerlists[$i];

		$props = $xOfferListNode->select_nodes("/".$nameUTF['PropertyValue']);
		for($j=0; $j<count($props); $j++)
		{
			$arPriceType[$props[0]->GetAttribute($nameUTF['CatalogID'])] = $props[0]->GetAttribute($nameUTF['PropertyId']);
			break;
		}
	}
	
	$SITE_ID = 'ru';
	$dbSite = CSite::GetByID($SITE_ID);
	if (!$dbSite->Fetch())
	{
		$dbSite = CSite::GetList();
		$arSite = $dbSite->Fetch();
		$SITE_ID = $arSite['ID'];
	}

	$tmpid = md5(uniqid(""));
	$arCatalogs = Array();
	$arCatalogsParams = Array();
	$catalogs = $comm[0]->select_nodes("/".$nameUTF['Catalog']);
	for ($i = 0; $i < count($catalogs); $i++)
	{
		$xCatNode = $catalogs[$i];

		$IBLOCK_XML_ID = $xCatNode->GetAttribute($nameUTF['ID']);
		$IBLOCK_NAME = $xCatNode->GetAttribute($nameUTF['Name']);
		$IBLOCK_DESC = $xCatNode->GetAttribute($nameUTF['Description']);

		$ib = new CIBlock;
		$res = CIBlock::GetList(Array(), Array("XML_ID"=>$IBLOCK_XML_ID));
		$bNewRecord_tmp = False;
		if ($res_arr = $res->Fetch())
		{
			$IBLOCK_ID = $res_arr["ID"];
			$res = $ib->Update($IBLOCK_ID,
				Array(
					"NAME"=>$IBLOCK_NAME,
					"TMP_ID"=>$tmpid,
					"DESCRIPTION"=>$IBLOCK_DESC
				)
			);
		}
		else
		{
			$bNewRecord_tmp = True;
			$arFields = Array(
				"ACTIVE"=>"Y",
				"NAME"=>$IBLOCK_NAME,
				"XML_ID"=>$IBLOCK_XML_ID,
				"IBLOCK_TYPE_ID"=>$IBLOCK_TYPE_ID,
				//"LID"=>"ru"
				"LID" => $SITE_ID,
				);

			$IBLOCK_ID = $ib->Add($arFields);
			$res = ($IBLOCK_ID>0);
		}

		if(!$res)
		{
			$strImportErrorMessage .= str_replace("#ERROR#", $ib->LAST_ERROR, str_replace("#NAME#", "[".$IBLOCK_ID."] \"".$IBLOCK_NAME."\" (".$IBLOCK_XML_ID.")", GetMessage("CICML_ERROR_ADDING_CATALOG"))).".<br>";
			$STT_CATALOG_ERROR++;
		}
		else
		{
			if ($bNewRecord_tmp) $STT_CATALOG_ADD++;
			else $STT_CATALOG_UPDATE++;

			$arCatalogs[$IBLOCK_XML_ID] = $IBLOCK_ID;

			if(!CCatalog::GetByID($IBLOCK_ID))
				CCatalog::Add(Array("IBLOCK_ID"=>$IBLOCK_ID));

			$arProperties = Array();
			$ibp = new CIBlockProperty;
			$props = $xCatNode->select_nodes("/".$nameUTF['Property']);

			for($j=0; $j<count($props); $j++)
			{
				$xPropNode = $props[$j];

				$PROP_XML_ID = $xPropNode->GetAttribute($nameUTF['ID']);
				$PROP_TYPE = $xPropNode->GetAttribute($nameUTF['DataType']);
				$PROP_MULTIPLE = ($xPropNode->GetAttribute($nameUTF['Multiple'])=="1"?"Y":"N");
				$PROP_NAME = $xPropNode->GetAttribute($nameUTF['Name']);
				$PROP_DEF = $xPropNode->GetAttribute($nameUTF['DefaultValue']);
				if($PROP_TYPE == "enumeration")
					$PROP_TYPE = "L";
				else
					$PROP_TYPE = "S";

				if($arPriceType[$IBLOCK_XML_ID]==$PROP_XML_ID)
					continue;

				$res = CIBlock::GetProperties($IBLOCK_ID, Array(), Array("XML_ID"=>$PROP_XML_ID, "IBLOCK_ID"=>$IBLOCK_ID));
				$bNewRecord_tmp = False;
				if($res_arr = $res->Fetch())
				{
					$PROP_ID = $res_arr["ID"];
					$res = $ibp->Update($PROP_ID,
							Array(
								"NAME" => $PROP_NAME,
								"TYPE" => $PROP_TYPE,
								"MULTIPLE" => $PROP_MULTIPLE,
								"DEFAULT_VALUE" => $PROP_DEF,
								"TMP_ID"		=> $tmpid
							)
						);
				}
				else
				{
					$bNewRecord_tmp = True;
					$arFields = Array(
						"NAME" 			=> $PROP_NAME,
						"ACTIVE" 		=> "Y",
						"SORT" 			=> "500",
						"DEFAULT_VALUE" => $PROP_DEF,
						"XML_ID" 		=> $PROP_XML_ID,
						"TMP_ID"		=> $tmpid,
						"MULTIPLE"	 	=> $PROP_MULTIPLE,
						"PROPERTY_TYPE"	=> $PROP_TYPE,
						"IBLOCK_ID" 	=> $IBLOCK_ID
						);
					$PROP_ID = $ibp->Add($arFields);
					$res = (IntVal($PROP_ID)>0);
				}

				if (!$res)
				{
					$strImportErrorMessage .= str_replace("#ERROR#", $ibp->LAST_ERROR, str_replace("#NAME#", "[".$PROP_ID."] \"".$PROP_NAME."\" (".$PROP_XML_ID.")", GetMessage("CICML_ERROR_ADD_PROPS"))).".<br>";
					$STT_PROP_ERROR++;
				}
				else
				{
					if ($bNewRecord_tmp) $STT_PROP_ADD++;
					else $STT_PROP_UPDATE++;

					$arProperties[$PROP_XML_ID] = $PROP_ID;
				}

				if($PROP_TYPE=="L")
				{
					$pren = new CIBlockPropertyEnum();
					$arPropertiesEnum[$PROP_XML_ID] = Array();
					$prop_enums = $xPropNode->select_nodes("/".$nameUTF['PropertyVariant']);
					for($k=0; $k<count($prop_enums); $k++)
					{
						$xPropEnum = $prop_enums[$k];
						$PROP_ENUM_XML_ID = $xPropEnum->GetAttribute($nameUTF['ID']);
						$PROP_ENUM_NAME = $xPropEnum->GetAttribute($nameUTF['Name']);
						$PROP_ENUM_DEF = ($PROP_ENUM_XML_ID==$PROP_DEF?"Y":"N");

						$arFields = Array(
							"DEF"=>$PROP_ENUM_DEF,
							"TMP_ID"=>$tmpid,
							"VALUE"=>$PROP_ENUM_NAME,
							"PROPERTY_ID"=>$PROP_ID,
							"XML_ID"=>$PROP_ENUM_XML_ID
						);
						$res = CIBlockPropertyEnum::GetList(Array(), Array("PROPERTY_ID"=>$PROP_ID, "XML_ID"=>$PROP_ENUM_XML_ID));
						if($arr = $res->Fetch())
						{
							$PROP_ENUM_ID = $arr["ID"];
							$pren->Update($PROP_ENUM_ID, $arFields);
						}
						else
						{
							$PROP_ENUM_ID = $pren->Add($arFields);
						}
						$arPropertiesEnum[$PROP_XML_ID][$PROP_ENUM_XML_ID] = $PROP_ENUM_ID;
					}
				}
			}

			if (function_exists("catalog_property_mutator_1c"))
				catalog_property_mutator_1c();

			$arGrTmp = Array();
			$groups = $xCatNode->select_nodes("/".$nameUTF['Category']);
			for ($j = 0; $j < count($groups); $j++)
			{
				$xGroupNode = $groups[$j];

				$GROUP_XML_ID = $xGroupNode->GetAttribute($nameUTF['ID']);
				$GROUP_PARENT_XML_ID = $xGroupNode->GetAttribute($nameUTF['ParentCategory']);
				$GROUP_NAME = $xGroupNode->GetAttribute($nameUTF['Name']);

				$arGrTmp[$tmpid.$GROUP_PARENT_XML_ID][] = Array(
						"GROUP_XML_ID"=>$GROUP_XML_ID,
						"GROUP_PARENT_XML_ID"=>$GROUP_PARENT_XML_ID,
						"GROUP_NAME"=>$GROUP_NAME
						);
			}

			$arGroups = Array();
			AddSectionsRecursive();
			CIBlockSection::ReSort($IBLOCK_ID);

			$el = new CIBlockElement;
			$arProducts = Array();
			$products = $xCatNode->select_nodes("/".$nameUTF['Product']);
			for ($j = 0; $j < count($products); $j++)
			{
				$xProductNode = $products[$j];

				$PRODUCT_XML_ID = $xProductNode->GetAttribute($nameUTF['ID']);
				$PRODUCT_PARENT_XML_ID = $xProductNode->GetAttribute($nameUTF['ParentCategory']);
				$PRODUCT_NAME = $xProductNode->GetAttribute($nameUTF['Name']);

				$PROP = Array();
				$GROUPS_ID = Array();
				if(strlen($PRODUCT_PARENT_XML_ID)>0 && strlen($arGroups[$PRODUCT_PARENT_XML_ID])>0)
					$GROUPS_ID[] = $arGroups[$PRODUCT_PARENT_XML_ID];

				$prod_groups = $xProductNode->select_nodes("/".$nameUTF['CategoryReference']);
				for($k=0; $k<count($prod_groups); $k++)
				{
					$xProductGroupsNode = $prod_groups[$k];
					$PRODUCT_GROUP_XML_ID = $xProductGroupsNode->GetAttribute($nameUTF['IdInCatalog']);
					if(strlen($arGroups[$PRODUCT_GROUP_XML_ID])>0)
						$GROUPS_ID[] = $arGroups[$PRODUCT_GROUP_XML_ID];
				}

				$prop_vals = $xProductNode->select_nodes("/".$nameUTF['PropertyValue']);
				for($k=0; $k<count($prop_vals); $k++)
				{
					$xPropertyValueNode = $prop_vals[$k];

					$PROP_VAL_PROPERTY_XML_ID = $xPropertyValueNode->GetAttribute($nameUTF['PropertyId']);
					$PROP_VAL_VALUE = $xPropertyValueNode->GetAttribute($nameUTF['Value']);
					if(strlen($arProperties[$PROP_VAL_PROPERTY_XML_ID])>0)
					{
						if(is_array($arPropertiesEnum[$PROP_VAL_PROPERTY_XML_ID]))
						{
							if(strlen($arPropertiesEnum[$PROP_VAL_PROPERTY_XML_ID][$PROP_VAL_VALUE])>0)
								$PROP[$arProperties[$PROP_VAL_PROPERTY_XML_ID]][] = $arPropertiesEnum[$PROP_VAL_PROPERTY_XML_ID][$PROP_VAL_VALUE];
						}
						else
							$PROP[$arProperties[$PROP_VAL_PROPERTY_XML_ID]][] = $PROP_VAL_VALUE;
					}
				}

				$arLoadProductArray = Array(
						"MODIFIED_BY"		=>	$USER->GetID(),
						"IBLOCK_SECTION"	=>	$GROUPS_ID,
						"IBLOCK_ID"			=>	$IBLOCK_ID,
						"NAME"				=>	$PRODUCT_NAME,
						"XML_ID"				=>	$PRODUCT_XML_ID,
						"TMP_ID"				=> $tmpid,
						"PROPERTY_VALUES"	=>	$PROP
						);

				$res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$PRODUCT_XML_ID));
				$bNewRecord_tmp = False;
				if ($arr = $res->Fetch())
				{
					$PRODUCT_ID = $arr["ID"];
					if (function_exists("catalog_product_mutator_1c"))
					{
						$arLoadProductArray = catalog_product_mutator_1c($arLoadProductArray, $xProductNode, false);
					}
					$res = $el->Update($PRODUCT_ID, $arLoadProductArray);
/*					$res = $el->Update($PRODUCT_ID,
								Array(
									"MODIFIED_BY" 	=> $USER->GetID(),
									"IBLOCK_SECTION"=> $GROUPS_ID,
									"NAME" 			=> $PRODUCT_NAME,
									"TMP_ID"		=> $tmpid,
									"PROPERTY_VALUES" => $PROP
								)
							);*/
				}
				else
				{
					$bNewRecord_tmp = True;
					$arLoadProductArray["ACTIVE"] = "Y";
					if (function_exists("catalog_product_mutator_1c"))
						$arLoadProductArray = catalog_product_mutator_1c($arLoadProductArray, $xProductNode, true);
/*					$arFields = Array(
						"ACTIVE"				=>	"Y",
						"MODIFIED_BY"			=>	$USER->GetID(),
						"IBLOCK_SECTION"		=>	$GROUPS_ID,
						"IBLOCK_ID"				=>	$IBLOCK_ID,
						"NAME"					=>	$PRODUCT_NAME,
						"XML_ID"				=>	$PRODUCT_XML_ID,
						"TMP_ID"				=> 	$tmpid,
						"PROPERTY_VALUES"		=>	$PROP
						);
					$PRODUCT_ID = $el->Add($arFields);*/
					$PRODUCT_ID = $el->Add($arLoadProductArray);
					$res = ($PRODUCT_ID>0);
				}

				if(!$res)
				{
					$strImportErrorMessage .= str_replace("#ERROR#", $el->LAST_ERROR, str_replace("#NAME#", "[".$PRODUCT_ID."] \"".$PRODUCT_NAME."\" (".$PRODUCT_XML_ID.")", GetMessage("CICML_ERROR_ADDING_PRODUCT"))).".<br>";
					$STT_PRODUCT_ERROR++;
				}
				else
				{
					if ($bNewRecord_tmp) $STT_PRODUCT_ADD++;
					else $STT_PRODUCT_UPDATE++;

					$arProducts[$PRODUCT_XML_ID] = $PRODUCT_ID;
				}


				$arCatalogsParams[$IBLOCK_XML_ID] = Array(
						"arProperties" => $arProperties,
						"arPropertiesEnum" => $arPropertiesEnum,
						"arGroups" => $arGroups,
						"arProducts" => $arProducts
						);
			}

			if ($outFileAction!="F")
			{
				$res = CIBlockProperty::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "!TMP_ID"=>$tmpid));
				while($arr = $res->Fetch())
				{
					if ($outFileAction!="H")
					{
						CIBlockProperty::Delete($arr["ID"]);
					}
				}

				$res = CIBlockPropertyEnum::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "!TMP_ID"=>$tmpid));
				while($arr = $res->Fetch())
				{
					if ($outFileAction!="H")
					{
						CIBlockPropertyEnum::Delete($arr["ID"]);
					}
				}

				$bs = new CIBlockSection;
				$res = CIBlockSection::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "!TMP_ID"=>$tmpid));
				while($arr = $res->Fetch())
				{
					if ($outFileAction!="H")
					{
						CIBlockSection::Delete($arr["ID"]);
					}
					else
					{
						$bs->Update($arr["ID"], Array("NAME"=>$arr["NAME"], "ACTIVE" => "N"));
					}
				}

				$res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_ID, "!TMP_ID"=>$tmpid));
				while($arr = $res->Fetch())
				{
					if ($outFileAction!="H")
					{
						CIBlockElement::Delete($arr["ID"], "Y", "N");
					}
					else
					{
						$el->Update($arr["ID"], Array("ACTIVE" => "N"));
					}
				}
			}

		}
	}

	/*$arCurrencies = Array(
			"USD" => "USD",
			"RUR" => "RUR",
			"EUR" => "EUR",
			"���." => "RUR"
			);
	*/
	global $arCurrencies;
	include(dirname(__FILE__).'/ru/commerceml_run_cur.php');

//	$arProductsTmp = array();
//	foreach ($arCatalogsParams as $key=>$Val)
//	{
//		$arProductsTmp[$key] = $Val["arProducts"];
//	}
//	$arProductsTmp = $arCatalogsParams[$IBLOCK_XML_ID]["arProducts"];
	$arProductsTmpA = $arCatalogsParams;

	for ($i = 0; $i < count($offerlists); $i++)
	{
		$xOfferListNode = $offerlists[$i];

		$IBLOCK_XML_ID = $xOfferListNode->GetAttribute($nameUTF['CatalogID']);
		$OFFER_LIST_DESC = $xOfferListNode->GetAttribute($nameUTF['Description']);
		$OFFER_LIST_CURRENCY = $arCurrencies[$xOfferListNode->GetAttribute($nameUTF['Currency'])];
		if (strlen($OFFER_LIST_CURRENCY)<=0)
			$OFFER_LIST_CURRENCY = "USD";

		//��������� c������� "��� ����"
		$props = $xOfferListNode->select_nodes("/".$nameUTF['PropertyValue']);
		if (!is_object($props[0]))
			continue;

		$PRICE_TYPE = $props[0]->GetAttribute($nameUTF['Value']);
		$res = CCatalogGroup::GetList(array(), array("NAME"=>$PRICE_TYPE));
		if ($arr = $res->Fetch())
			$PRICE_ID = $arr["ID"];
		else
			$PRICE_ID = CCatalogGroup::Add(
					Array(
						"NAME"=>$PRICE_TYPE,
						"USER_LANG"=>Array("ru"=>$PRICE_TYPE)
					)
				);

		$arProducts = $arCatalogsParams[$IBLOCK_XML_ID]["arProducts"];

		$arOffers = Array();
		$offers = $xOfferListNode->select_nodes("/".$nameUTF['Offer']);

		for ($j = 0; $j < count($offers); $j++)
		{
			$xOfferNode = $offers[$j];

			$PRODUCT_XML_ID = $xOfferNode->GetAttribute($nameUTF['ProductId']);

			// �� ������� �� continue, � ������ ����� � ���� �� XML_ID, ���� ��� �
			// �������. ����� ����� ����� ������� ������ ���� ��� �������.
			$PRODUCT_ID = 0;
			if (!isset($arProducts[$PRODUCT_XML_ID]))
			{
				if (!isset($arIBlockCacheTmp[$IBLOCK_XML_ID])
					|| IntVal($arIBlockCacheTmp[$IBLOCK_XML_ID])<=0)
				{
					$db_res_tmp = CIBlock::GetList(Array(), Array("XML_ID"=>$IBLOCK_XML_ID));
					if ($ar_res_tmp = $db_res_tmp->Fetch())
					{
						$arIBlockCacheTmp[$IBLOCK_XML_ID] = IntVal($ar_res_tmp["ID"]);
					}
				}

				$db_res_tmp = CIBlockElement::GetList(Array(), Array("XML_ID"=>$PRODUCT_XML_ID, "IBLOCK_ID"=>$arIBlockCacheTmp[$IBLOCK_XML_ID]));
				if ($ar_res_tmp = $db_res_tmp->Fetch())
				{
					$PRODUCT_ID = IntVal($ar_res_tmp["ID"]);
					$arCatalogsParams[$IBLOCK_XML_ID]["arProducts"][$PRODUCT_XML_ID] = $PRODUCT_ID;
				}
			}
			else
			{
				$PRODUCT_ID = IntVal($arProducts[$PRODUCT_XML_ID]);
			}

			if ($PRODUCT_ID <= 0)
				continue;

			unset($arProductsTmpA[$IBLOCK_XML_ID]["arProducts"][$PRODUCT_XML_ID]);

			$OFFER_PRICE = DoubleVal(str_replace(",", ".", $xOfferNode->GetAttribute($nameUTF['Price'])));
			$OFFER_UPAK = $xOfferNode->GetAttribute($nameUTF['Package']);
			$OFFER_QUANTITY = IntVal($xOfferNode->GetAttribute($nameUTF['Amount']));
			$OFFER_UNIT = $xOfferNode->GetAttribute($nameUTF['Unit']);
			$OFFER_CURRENCY = $arCurrencies[$xOfferNode->GetAttribute($nameUTF['Currency'])];
			if (strlen($OFFER_CURRENCY)<=0)
				$OFFER_CURRENCY = $OFFER_LIST_CURRENCY;

			$arLoadOfferArray = array(
					"ID" => $PRODUCT_ID,
					"QUANTITY" => $OFFER_QUANTITY
				);
			if (function_exists("catalog_offer_mutator_1c"))
			{
				$arLoadOfferArray = catalog_offer_mutator_1c($arLoadOfferArray, $xOfferNode);
			}

			$khjk = CCatalogProduct::Add($arLoadOfferArray);

			$arFields = Array(
					"PRODUCT_ID" => $PRODUCT_ID,
					"CATALOG_GROUP_ID" => $PRICE_ID,
					"PRICE" => $OFFER_PRICE,
					"CURRENCY" => $OFFER_CURRENCY
				);

			$res = CPrice::GetList(
					array(),
					array(
							"PRODUCT_ID" => $PRODUCT_ID,
							"CATALOG_GROUP_ID" => $PRICE_ID
						)
				);
			if ($arr = $res->Fetch())
				$khjk = CPrice::Update($arr["ID"], $arFields);
			else
				$khjk = CPrice::Add($arFields);

			/* �������������
			���������������������
			����������������������*/
		}
	}

	if (COption::GetOptionString("catalog", "deactivate_1c_no_price", "N")=="Y")
	{
		foreach ($arProductsTmpA as $keyA=>$valA)
		{
			foreach ($valA["arProducts"] as $keyB=>$valB)
			{
				$res = $el->Update(IntVal($valB), Array("ACTIVE" => "N"));
			}
		}
	}

	$strImportOKMessage .= str_replace("#TIME#", RoundEx(getmicrotime() - $startImportExecTime, 2), GetMessage("CICML_LOAD_TIME"))."<br>";
	$strImportOKMessage .= str_replace("#NUM#", ($STT_CATALOG_UPDATE + $STT_CATALOG_ADD), GetMessage("CICML_LOAD_CATALOG"))." ";
	$strImportOKMessage .= str_replace("#NUM_UPD#", $STT_CATALOG_UPDATE, str_replace("#NUM_NEW#", $STT_CATALOG_ADD, GetMessage("CICML_LOAD_NEW_UPD")))." ";

	if (IntVal($STT_CATALOG_ERROR) > 0)
		$strImportOKMessage .= str_replace("#NUM#", $STT_CATALOG_ERROR, GetMessage("CICML_LOAD_ERROR"));
	$strImportOKMessage .= "<br>";

	$strImportOKMessage .= str_replace("#NUM#", ($STT_GROUP_UPDATE + $STT_GROUP_ADD), GetMessage("CICML_LOAD_GROUP"))." ";
	$strImportOKMessage .= str_replace("#NUM_UPD#", $STT_GROUP_UPDATE, str_replace("#NUM_NEW#", $STT_GROUP_ADD, GetMessage("CICML_LOAD_GROUP_NEW_UPD")))." ";

	if (IntVal($STT_GROUP_ERROR) > 0)
		$strImportOKMessage .= str_replace("#NUM#", $STT_GROUP_ERROR, GetMessage("CICML_LOAD_GROUP_ERROR"));
	$strImportOKMessage .= "<br>";

	$strImportOKMessage .= str_replace("#NUM#", ($STT_PROP_UPDATE + $STT_PROP_ADD), GetMessage("CICML_LOAD_PROPS"))." ";
	$strImportOKMessage .= str_replace("#NUM_UPD#", $STT_PROP_UPDATE, str_replace("#NUM_NEW#", $STT_PROP_ADD, GetMessage("CICML_LOAD_PROPS_NEW_UPD")))." ";

	if (IntVal($STT_PROP_ERROR) > 0)
		$strImportOKMessage .= str_replace("#NUM#", $STT_PROP_ERROR, GetMessage("CICML_LOAD_PROPS_ERROR"));
	$strImportOKMessage .= "<br>";

	$strImportOKMessage .= str_replace("#NUM#", ($STT_PRODUCT_UPDATE + $STT_PRODUCT_ADD), GetMessage("CICML_LOAD_PROD"))." ";
	$strImportOKMessage .= str_replace("#NUM_UPD#", $STT_PRODUCT_UPDATE, str_replace("#NUM_NEW#", $STT_PRODUCT_ADD, GetMessage("CICML_LOAD_PROD_NEW_UPD")))." ";

	if (IntVal($STT_PRODUCT_ERROR) > 0)
		$strImportOKMessage .= str_replace("#NUM#", $STT_PRODUCT_ERROR, GetMessage("CICML_LOAD_PROD_ERROR"));
	$strImportOKMessage .= "<br>";
}

if ($bTmpUserCreated)
	unset($USER);
?>