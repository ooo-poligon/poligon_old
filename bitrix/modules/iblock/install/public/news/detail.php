<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?><?$APPLICATION->IncludeFile("iblock/news/detail.php", Array(
	"ID"	=>	$_REQUEST["ID"],		// ID новости
	"IBLOCK_TYPE"	=>	"news",		// Тип информационного блока (используется только для проверки)
	"IBLOCK_ID"	=>	"1",			// Код информационного блока
	"arrPROPERTY_CODE"	=>	Array(	// Свойства
					"AUTHOR",
					"SOURCE"
				),
	"META_KEYWORDS"	=>	"KEYWORDS",	// Установить ключевые слова страницы из свойства
	"META_DESCRIPTION"	=>	"DESCRIPTION",// Установить описание страницы из свойства
	"LIST_PAGE_URL"	=>	"",		// URL страницы просмотра списка элементов (по умолчанию - из настроек инфоблока)
	"INCLUDE_IBLOCK_INTO_CHAIN"	=>	"N",	// Включать инфоблок/группу в цепочку навигации
	"CACHE_TIME"	=>	"0",		// Время кэширования (0 - не кэшировать)
	"DISPLAY_PANEL"	=>	"Y",		// Добавлять в админ. панель кнопки для данного компонента
	)
);?><?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>