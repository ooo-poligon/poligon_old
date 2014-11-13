<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");?><?$APPLICATION->IncludeFile("iblock/catalog/element_filter.php", Array(
	"IBLOCK_TYPE"	=>	"catalog",	// Тип инфо-блока
	"IBLOCK_ID"	=>	"21",		// Инфо-блок
	"arrFIELD_CODE"	=>	Array("NAME"),	// Поля
	"arrPROPERTY_CODE"	=>	Array(	// Свойства
					"YEAR",
					"WEIGHT",
					"STANDART",
					"BATTERY"
				),
	"arrPRICE_CODE"	=>	Array("RETAIL"),// Типы цен
	"CURRENCY_CODE"	=>	"\$",		// Код валюты
	"SAVE_IN_SESSION"	=>	"N",		// Сохранять значения полей фильтра в сессии
	"FILTER_NAME"	=>	"arrFilter",	// Имя выходящего массива для фильтрации
	"LIST_HEIGHT"	=>	"5",		// Высота списков множественного выбора
	"TEXT_WIDTH"	=>	"24",		// Ширина однострочных текстовых полей ввода
	"NUMBER_WIDTH"	=>	"5",		// Ширина полей ввода для числовых интервалов
	"CACHE_TIME"	=>	"0",		// Время кэширования (сек.)
	)
);?> <BR><?$APPLICATION->IncludeFile("iblock/catalog/compare_list.php", Array(
	"IBLOCK_TYPE"	=>	"catalog",			// Тип инфо-блока
	"IBLOCK_ID"	=>	"21",				// Инфо-блок
	"COMPARE_URL"	=>	"/catalog/phone/compare.php",// URL страницы с таблицей сравнения
	"NAME"	=>	"CATALOG_COMPARE_LIST",		// Уникальное имя для списка сравнения
	"ELEMENT_SORT_FIELD"	=>	"sort",		// По какому полю сортируем элементы
	"ELEMENT_SORT_ORDER"	=>	"asc",		// Порядок сортировки элементов в разделе
	"CACHE_TIME"	=>	"0",				// Время кэширования (сек.)
	)
);?> <BR><?$APPLICATION->IncludeFile("iblock/catalog/section.php", Array(
	"IBLOCK_TYPE"	=>	"catalog",		// Тип инфо-блока
	"IBLOCK_ID"	=>	"21",			// Инфо-блок
	"SECTION_ID"	=>	$_REQUEST["SECTION_ID"],// ID раздела
	"PAGE_ELEMENT_COUNT"	=>	"30",		// Количество элементов на странице
	"LINE_ELEMENT_COUNT"	=>	"2",		// Количество элементов выводимых в одной строке таблицы
	"ELEMENT_SORT_FIELD"	=>	"sort",		// По какому полю сортируем элементы
	"ELEMENT_SORT_ORDER"	=>	"asc",		// Порядок сортировки элементов в разделе
	"arrPROPERTY_CODE"	=>	Array(		// Свойства
					"YEAR",
					"STANDBY_TIME",
					"TALKTIME",
					"WEIGHT",
					"STANDART",
					"SIZE",
					"BATTERY"
				),
	"PRICE_CODE"	=>	"RETAIL",		// Тип цены
	"BASKET_URL"	=>	"/personal/basket.php",	// URL ведущий на страницу с корзиной покупателя
	"FILTER_NAME"	=>	"arrFilter",		// Имя массива со значениями фильтра для фильтрации элементов
	"CACHE_FILTER"	=>	"N",			// Кэшировать при установленом фильтре
	"CACHE_TIME"	=>	"0",			// Время кэширования (сек.)
	"DISPLAY_PANEL"	=>	"Y",			// Добавлять в админ. панель кнопки для данного компонента
	)
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>