<?
class Finish extends CWizardStep
{
	function InitStep()
	{
		$this->SetStepID("finish");
		$this->SetCancelStep("finish");
		$this->SetTitle("Мастер создания сайта успешно завершен");
		$this->SetCancelCaption("Перейти на сайт");
	}


	function OnPostForm()
	{
	}

	function ShowStep()
	{
		$wizard =& $this->GetWizard();
		$wizard->SetFormActionScript("/?finish");

		$this->CreateNewIndex();
		$this->content .= 'Поздравляем! Мастер создания сайта успешно выполнен.';
	}


	function CreateNewIndex()
	{
		if ($_SERVER["PHP_SELF"] != "/index.php")
			return;

		$wizard =& $this->GetWizard();
		$templateID = $wizard->GetSiteTemplateID();

		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/classes/".strtolower($GLOBALS["DB"]->type)."/cml2.php");

		if ($templateID == "books" && ($iblockID = $this->GetIBlockID("books-books", "/e-store/books/index.php")) )
		{
			$arReplace = Array("SERVICE_IBLOCK_ID" => $iblockID);
			$index = "books";
		}
		elseif ($templateID == "xml_catalog" && ($iblockID = $this->GetIBlockID("FUTURE-1C-CATALOG", "/e-store/xml_catalog/index.php")) )
		{
			$arReplace = Array("SERVICE_IBLOCK_ID" => $iblockID);
			$index = "xml_catalog";
		}
		elseif ($templateID == "web20" && $this->GetIBlockID("content-news", "/content/news/index.php") || $this->GetIBlockID("content-articles", "/content/articles/index.php"))
		{

			$newsID = $this->GetIBlockID("content-news", "/content/news/index.php");
			$articlesID = $this->GetIBlockID("content-articles", "/content/articles/index.php");

			$articleReplace = "";
			if ($articlesID)
			{
				$title = "Статьи";

				$articleReplace = '
				<?$APPLICATION->IncludeComponent("bitrix:news.list", "articles", Array(
					"IBLOCK_TYPE"	=>	"articles",
					"IBLOCK_ID"	=>	"'.$articlesID.'",
					"NEWS_COUNT"	=>	"5",
					"SORT_BY1"	=>	"ACTIVE_FROM",
					"SORT_ORDER1"	=>	"DESC",
					"SORT_BY2"	=>	"SORT",
					"SORT_ORDER2"	=>	"ASC",
					"FILTER_NAME"	=>	"",
					"FIELD_CODE"	=>	array(
					),
					"PROPERTY_CODE"	=>	array(
						0	=>	"FORUM_MESSAGE_CNT",
						1	=>	"rating",
					),
					"DETAIL_URL"	=>	"/content/articles/index.php?article=#ELEMENT_ID#",
					"CACHE_TYPE"	=>	"A",
					"CACHE_TIME"	=>	"3600",
					"CACHE_FILTER"	=>	"N",
					"PREVIEW_TRUNCATE_LEN"	=>	"0",
					"ACTIVE_DATE_FORMAT"	=>	"M j, Y, H:m",
					"DISPLAY_PANEL"	=>	"N",
					"SET_TITLE"	=>	"N",
					"INCLUDE_IBLOCK_INTO_CHAIN"	=>	"Y",
					"ADD_SECTIONS_CHAIN"	=>	"Y",
					"HIDE_LINK_WHEN_NO_DETAIL"	=>	"N",
					"PARENT_SECTION"	=>	"",
					"DISPLAY_TOP_PAGER"	=>	"N",
					"DISPLAY_BOTTOM_PAGER"	=>	"N",
					"PAGER_TITLE"	=>	"Статьи",
					"PAGER_SHOW_ALWAYS"	=>	"N",
					"PAGER_TEMPLATE"	=>	"",
					"PAGER_DESC_NUMBERING"	=>	"N",
					"PAGER_DESC_NUMBERING_CACHE_TIME"	=>	"36000",
					"DISPLAY_DATE"	=>	"Y",
					"DISPLAY_NAME"	=>	"Y",
					"DISPLAY_PICTURE"	=>	"N",
					"DISPLAY_PREVIEW_TEXT"	=>	"Y"
					)
					);?> 
				';
			}
			
			$newsReplace = "";
			if ($newsID)
			{
				$newsReplace = '
					<?$APPLICATION->IncludeComponent(
						"bitrix:news.list",
						"",
						Array(
							"DISPLAY_DATE" => "Y", 
							"DISPLAY_NAME" => "Y", 
							"DISPLAY_PICTURE" => "N", 
							"DISPLAY_PREVIEW_TEXT" => "Y", 
							"IBLOCK_TYPE" => "news", 
							"IBLOCK_ID" => "'.$newsID.'", 
							"NEWS_COUNT" => "5", 
							"SORT_BY1" => "ACTIVE_FROM", 
							"SORT_ORDER1" => "DESC", 
							"SORT_BY2" => "SORT", 
							"SORT_ORDER2" => "ASC", 
							"FILTER_NAME" => "", 
							"FIELD_CODE" => Array("",""), 
							"PROPERTY_CODE" => Array("",""), 
							"DETAIL_URL" => "/content/news/index.php?news=#ELEMENT_ID#", 
							"PREVIEW_TRUNCATE_LEN" => "0", 
							"ACTIVE_DATE_FORMAT" => "d.m.Y", 
							"DISPLAY_PANEL" => "N", 
							"SET_TITLE" => "N", 
							"INCLUDE_IBLOCK_INTO_CHAIN" => "Y", 
							"CACHE_TIME" => "3600", 
							"CACHE_FILTER" => "N", 
							"DISPLAY_TOP_PAGER" => "N", 
							"DISPLAY_BOTTOM_PAGER" => "N", 
							"PAGER_TITLE" => "Новости", 
							"PAGER_SHOW_ALWAYS" => "N", 
							"PAGER_TEMPLATE" => "", 
							"PAGER_DESC_NUMBERING" => "N" 
						)
					);?> 
				';

				if ($articlesID)
					$newsReplace = "<h1>Новости</h1>".$newsReplace;
				else
					$title = "Новости";
			}

			$arReplace = Array(
				"TITLE" => $title,
				"ARTICLES" => "-->".$articleReplace."<!--", 
				"NEWS" => "-->".$newsReplace."<!--"
			);

			$index = "web20";
		}
		else
		{
			$arReplace = false;
			$index = "static_page";
		}

		//Copy index page
		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"].$wizard->GetPath()."/indexes/".LANGUAGE_ID."/".$index,
			$_SERVER["DOCUMENT_ROOT"],
			$rewrite = true, 
			$recursive = true
		);

		CWizardUtil::ReplaceMacros($_SERVER["DOCUMENT_ROOT"]."/index.php", $arReplace);

	}


	function GetIBlockID($xmlID, $filePath)
	{
		if (!CModule::IncludeModule("iblock"))
			return false;

		if (!file_exists($_SERVER["DOCUMENT_ROOT"].$filePath))
			return false;

		return CIBlockCMLImport::GetIBlockByXML_ID($xmlID);
	}

}

?>