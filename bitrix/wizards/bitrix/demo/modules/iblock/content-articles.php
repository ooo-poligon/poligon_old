<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

//Library
include_once(dirname(__FILE__)."/iblock_tools.php");
__IncludeLang(GetLangFileName(dirname(__FILE__)."/lang/", "/".basename(__FILE__)));

//Parameters
if(!is_array($arParams)) $arParams = array();
if(strlen($arParams["site_id"]) <= 0)
	$arParams["site_id"] = "s1";

//Install themes iblock
DEMO_IBlock_ImportXML("010_services_services-themes_ru.xml", $arParams["site_id"], false);

//Import XML
if($IBLOCK_ID = DEMO_IBlock_ImportXML("030_articles_content-articles_ru.xml", $arParams["site_id"], false))
{
	//Forum creation
	if(CModule::IncludeModule('forum'))
	{
		$rsForums = CForumNew::GetList();
		while($arForum = $rsForums->Fetch())
		{
			if($arForum["NAME"] == GetMessage("DEMO_IBLOCK_CONTENT_ARTICLES_FORUM_NAME"))
				break;
		}

		if(!$arForum)
		{
			$rsForumGroups = CForumGroup::GetList();
			while($arForumGroup = $rsForumGroups->Fetch())
			{
				$arForumGroup = CForumGroup::GetLangByID($arForumGroup["ID"], LANGUAGE_ID);
				if($arForumGroup["NAME"] === GetMessage("DEMO_IBLOCK_CONTENT_ARTICLES_FORUM_GROUP_NAME"))
					break;
			}
			if(!$arForumGroup)
			{
				$arFields = array(
					"SORT" => 150,
					"LANG" => array(),
				);
				$rsLanguages = CLanguage::GetList(($b="sort"), ($o="asc"));
				while($arLang = $rsLanguages->Fetch())
				{
					$file = dirname(__FILE__)."/lang/".$arLang["LANGUAGE_ID"]."/content-articles.php";
					include($file);
					$arFields["LANG"][] = array(
						"LID" => $arLang["LANGUAGE_ID"],
						"NAME" => GetMessage("DEMO_IBLOCK_CONTENT_ARTICLES_FORUM_GROUP_NAME"),
						"DESCRIPTION" => "",
					);
				}
				$arForumGroup = array("FORUM_GROUP_ID" => CForumGroup::Add($arFields));
			}
			if($arForumGroup["FORUM_GROUP_ID"])
			{
				$arFields = Array(
					"NAME" => GetMessage("DEMO_IBLOCK_CONTENT_ARTICLES_FORUM_NAME"),
					"DESCRIPTION" => "",
					"SORT" => 150,
					"ACTIVE" => "Y",
					"ALLOW_HTML" => "N",
					"ALLOW_ANCHOR" => "Y",
					"ALLOW_BIU" => "Y",
					"ALLOW_IMG" => "N",
					"ALLOW_LIST" => "Y",
					"ALLOW_QUOTE" => "Y",
					"ALLOW_CODE" => "Y",
					"ALLOW_FONT" => "Y",
					"ALLOW_SMILES" => "Y",
					"ALLOW_UPLOAD" => "N",
					"ALLOW_UPLOAD_EXT" => "",
					"ALLOW_NL2BR" => "Y",
					"MODERATION" => "N",
					"ALLOW_MOVE_TOPIC" => "Y",
					"ORDER_BY" => "P",
					"ORDER_DIRECTION" => "DESC",
					"PATH2FORUM_MESSAGE" => "",
					"FORUM_GROUP_ID" => $arForumGroup["FORUM_GROUP_ID"],
					"ASK_GUEST_EMAIL" => "N",
					"USE_CAPTCHA" => "N",
					"SITES" => array(
						$arParams["site_id"] => "/communication/forum/index.php?PAGE_NAME=read&FID=#FORUM_ID#&TID=#TOPIC_ID#&MID=#MESSAGE_ID##message#MESSAGE_ID#",
					),
				);

				$arFields["GROUP_ID"] = array(
					"2" => "M",
					"19" => "Q",
				);

				if (CModule::IncludeModule("statistic"))
				{
					$arFields["EVENT1"] = "forum";
					$arFields["EVENT2"] = "message";
					$arFields["EVENT3"] = "";
				}

				$arForum = array("ID" => CForumNew::Add($arFields));
			}
		}
	}
	else
	{
		$arForum = array("ID" => "");
	}

	//Include language one more time (after forum creation)
	__IncludeLang(GetLangFileName(dirname(__FILE__)."/lang/", "/".basename(__FILE__)));

	//Create directory and copy files
	$search = array(
		"#IBLOCK.ID(XML_ID=content-articles)#",
		"#IBLOCK.ID(XML_ID=content-news)#",
		"#MODULE.INSTALLED(ID=forum)#",
		"#FORUM.ID(NAME=content-articles)#",
	);
	$replace = array(
		$IBLOCK_ID,
		CIBlockCMLImport::GetIBlockByXML_ID("content-news"),
		(IsModuleInstalled("forum")? "Y": "N"),
		$arForum["ID"],
	);
	DEMO_IBlock_CopyFiles("/public/content/articles/","/content/articles/", false, $search, $replace);

	//Add menu item
	DEMO_IBlock_AddMenuItem("/content/.left.menu.php", Array(
		GetMessage("DEMO_IBLOCK_CONTENT_ARTICLES_MENU"),
		"/content/articles/",
		Array(),
		Array(),
		"",
	));
}
?>