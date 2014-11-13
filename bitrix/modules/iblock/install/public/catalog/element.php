<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");?><TABLE cellSpacing=0 cellPadding=0 align=right border=0>

<TR>
<TD><?$APPLICATION->IncludeFile("catalog/price_table.php", Array(
	"PRODUCT_ID"	=>	$_REQUEST["ID"],// Код товара
	"PRICE_TYPE_OLD"	=>	"BASE",	// Тип "старой" цены
	"PRICE_TYPE_NEW"	=>	"RETAIL",	// Тип "новой" цены
	"BASKET_PAGE"	=>	SITE_DIR."personal/basket.php",	// Страница корзины
	"CACHE_TIME"	=>	"0",		// Время кэширования данных (секунд)
	)
);?></TD></TR></TABLE><?$APPLICATION->IncludeFile("iblock/catalog/element.php", Array(
	"IBLOCK_TYPE"	=>	"catalog",				// Тип инфо-блока
	"IBLOCK_ID"	=>	"21",					// Инфо-блок
	"ELEMENT_ID"	=>	$_REQUEST["ID"],				// ID элемента
	"SECTION_URL"	=>	"/catalog/phone/section.php?",		// URL ведущий на страницу с содержимым раздела
	"LINK_IBLOCK_TYPE"	=>	"catalog",				// Тип инфо-блока, элементы которого связаны с текущим элементом
	"LINK_IBLOCK_ID"	=>	"22",				// ID инфо-блока, элементы которого связаны с текущим элементом
	"LINK_PROPERTY_SID"	=>	"PHONE_ID",			// Свойство в котором хранится связь
	"LINK_ELEMENTS_URL"	=>	"/catalog/accessory/byphone.php?",// URL на страницу где будут показан список связанных элементов
	"arrFIELD_CODE"	=>	Array(				// Поля
					"NAME",
					"DETAIL_TEXT",
					"DETAIL_PICTURE"
				),
	"arrPROPERTY_CODE"	=>	Array(				// Свойства
					"YEAR",
					"STANDBY_TIME",
					"TALKTIME",
					"WEIGHT",
					"STANDART",
					"SIZE",
					"BATTERY",
					"SCREEN",
					"MORE_PHOTO",
					"FORUM_TOPIC_ID"
				),
	"CACHE_TIME"	=>	"0",					// Время кэширования (сек.)
	"DISPLAY_PANEL"	=>	"Y",					// Добавлять в админ. панель кнопки для данного компонента
	)
);?> 
<HR class=tinyblue>
<?$APPLICATION->IncludeFile("forum/forum_pieces/reviews.php", Array(
	"FORUM_ID"	=>	"2",		// Форум
	"PRODUCT_ID"	=>	$_REQUEST["ID"],// Код элемента инфоблока
	"CACHE_TIME"	=>	"0",		// Время кэширования данных (секунд)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>