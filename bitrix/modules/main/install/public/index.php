<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("Пробная версия продукта &laquo;Битрикс: Управление сайтом 4.x&raquo;");
?><FONT class=text>
<P>Сайт разработан на основе ознакомительной версии программного продукта <A href="/bitrix/redirect.php?event1=Go_out&amp;event2=Bitrixsoft&amp;event3=main&amp;goto=http%3A//www.bitrixsoft.ru/%3Fr1%3Dtrial41%26r2%3Dindex">«Битрикс: Управление сайтом»</A>. Используя тестовый дизайн и структру этого сайта как пример, вы можете создать собственный интернет-проект. Справа размещены советы, которые помогут вам быстро научиться управлять каталогом, редактировать тексты, публиковать новости и многое другое.</P>
<P>Все материалы сайта - тестовые и предназначены только для демонстрации функциональных возможностей системы.</P></FONT>
<TABLE class=tableborders cellSpacing=0 cellPadding=0 width="95%" border=0>

<TR>
<TD class=tableheads width=0%><IMG height=19 alt="" src="/images/info.gif" width=19></TD>
<TD class=tablebodys width="100%"><FONT class=smalltext>Чтобы приступить к управлению структурой и содержанием сайта, авторизуйтесь на сайте с помощью формы в левой части страницы.</FONT></TD></TR></TABLE><BR>
<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>

<TR>
<TD vAlign=top width="33%"><A class=maininctitle href="/catalog/phone/">&nbsp;Каталог телефонов&nbsp;</A> 
<DIV class=mainincline style="WIDTH: 33%"><IMG height=1 alt="" src="/bitrix/images/1.gif" width=1></DIV><?$APPLICATION->IncludeFile("iblock/catalog/main_page.php", Array(
	"IBLOCK_TYPE"	=>	"catalog",		// Тип инфо-блока
	"IBLOCK_ID"	=>	"21",			// Инфо-блок
	"ELEMENT_COUNT"	=>	"4",		// Максимальное количество элементов выводимых в каждом разделе
	"LINE_ELEMENT_COUNT"	=>	"2",		// Количество элементов выводимых в одной строке таблицы
	"ELEMENT_SORT_FIELD"	=>	"shows",	// По какому полю сортируем элементы
	"ELEMENT_SORT_ORDER"	=>	"desc",	// Порядок сортировки элементов в разделе
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
	"BASKET_URL"	=>	"/personal/basket.php",// URL ведущий на страницу с корзиной покупателя
	"CACHE_TIME"	=>	0,			// Время кэширования (сек.)
	)
);?><BR>
<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>

<TR>
<TD vAlign=top width="33%"><A class=maininctitle href="/about/news/">&nbsp;Новости компании&nbsp;</A> 
<DIV class=mainincline style="WIDTH: 80%"><IMG height=1 alt="" src="/bitrix/images/1.gif" width=1></DIV>
<P><?$APPLICATION->IncludeFile("iblock/news/news_line.php", Array(
	"IBLOCK_TYPE"	=>	"news",	// Тип информационного блока (используется только для проверки)
	"IBLOCK"	=>	Array("1"),	// Код информационного блока
	"NEWS_COUNT"	=>	"20",	// Количество новостей в странице
	"SORT_BY1"	=>	"ACTIVE_FROM",// Поле для первой сортировки новостей
	"SORT_ORDER1"	=>	"DESC",	// Направление для первой сортировки новостей
	"SORT_BY2"	=>	"SORT",	// Поле для второй сортировки новостей
	"SORT_ORDER2"	=>	"ASC",	// Направление для второй сортировки новостей
	"CACHE_TIME"	=>	"0",	// Время кэширования (0 - не кэшировать)
	)
);?></P><A class=maininctitle href="/about/gallery/">&nbsp;Фото дня</A> 
<DIV class=mainincline style="WIDTH: 33%"><IMG height=1 alt="" src="/bitrix/images/1.gif" width=1></DIV><BR><?$APPLICATION->IncludeFile("iblock/photo/random.php", Array(
	"IBLOCK_TYPE"	=>	"photo",// Тип инфо-блока
	)
);?> </TD>
<TD vAlign=top width="33%"><A class=maininctitle href="/support/vote/index.php">&nbsp;Опросы&nbsp;</A> 
<DIV class=mainincline style="WIDTH: 80%"><IMG height=1 alt="" src="/bitrix/images/1.gif" width=1></DIV>
<P><?$APPLICATION->IncludeFile("vote/vote_new/current_channel.php", Array(
	"CHANNEL_SID"	=>	"PHONES_POLL",// Группа опросов
	)
);?><BR><FONT class=smalltext><A href="/support/vote/index.php">Архив опросов</A></FONT></P></TD></TR></TABLE></TD></TR></TABLE><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>