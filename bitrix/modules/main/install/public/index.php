<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("������� ������ �������� &laquo;�������: ���������� ������ 4.x&raquo;");
?><FONT class=text>
<P>���� ���������� �� ������ ��������������� ������ ������������ �������� <A href="/bitrix/redirect.php?event1=Go_out&amp;event2=Bitrixsoft&amp;event3=main&amp;goto=http%3A//www.bitrixsoft.ru/%3Fr1%3Dtrial41%26r2%3Dindex">��������: ���������� ������</A>. ��������� �������� ������ � �������� ����� ����� ��� ������, �� ������ ������� ����������� ��������-������. ������ ��������� ������, ������� ������� ��� ������ ��������� ��������� ���������, ������������� ������, ����������� ������� � ������ ������.</P>
<P>��� ��������� ����� - �������� � ������������� ������ ��� ������������ �������������� ������������ �������.</P></FONT>
<TABLE class=tableborders cellSpacing=0 cellPadding=0 width="95%" border=0>

<TR>
<TD class=tableheads width=0%><IMG height=19 alt="" src="/images/info.gif" width=19></TD>
<TD class=tablebodys width="100%"><FONT class=smalltext>����� ���������� � ���������� ���������� � ����������� �����, ������������� �� ����� � ������� ����� � ����� ����� ��������.</FONT></TD></TR></TABLE><BR>
<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>

<TR>
<TD vAlign=top width="33%"><A class=maininctitle href="/catalog/phone/">&nbsp;������� ���������&nbsp;</A> 
<DIV class=mainincline style="WIDTH: 33%"><IMG height=1 alt="" src="/bitrix/images/1.gif" width=1></DIV><?$APPLICATION->IncludeFile("iblock/catalog/main_page.php", Array(
	"IBLOCK_TYPE"	=>	"catalog",		// ��� ����-�����
	"IBLOCK_ID"	=>	"21",			// ����-����
	"ELEMENT_COUNT"	=>	"4",		// ������������ ���������� ��������� ��������� � ������ �������
	"LINE_ELEMENT_COUNT"	=>	"2",		// ���������� ��������� ��������� � ����� ������ �������
	"ELEMENT_SORT_FIELD"	=>	"shows",	// �� ������ ���� ��������� ��������
	"ELEMENT_SORT_ORDER"	=>	"desc",	// ������� ���������� ��������� � �������
	"arrPROPERTY_CODE"	=>	Array(		// ��������
					"YEAR",
					"STANDBY_TIME",
					"TALKTIME",
					"WEIGHT",
					"STANDART",
					"SIZE",
					"BATTERY"
				),
	"PRICE_CODE"	=>	"RETAIL",		// ��� ����
	"BASKET_URL"	=>	"/personal/basket.php",// URL ������� �� �������� � �������� ����������
	"CACHE_TIME"	=>	0,			// ����� ����������� (���.)
	)
);?><BR>
<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>

<TR>
<TD vAlign=top width="33%"><A class=maininctitle href="/about/news/">&nbsp;������� ��������&nbsp;</A> 
<DIV class=mainincline style="WIDTH: 80%"><IMG height=1 alt="" src="/bitrix/images/1.gif" width=1></DIV>
<P><?$APPLICATION->IncludeFile("iblock/news/news_line.php", Array(
	"IBLOCK_TYPE"	=>	"news",	// ��� ��������������� ����� (������������ ������ ��� ��������)
	"IBLOCK"	=>	Array("1"),	// ��� ��������������� �����
	"NEWS_COUNT"	=>	"20",	// ���������� �������� � ��������
	"SORT_BY1"	=>	"ACTIVE_FROM",// ���� ��� ������ ���������� ��������
	"SORT_ORDER1"	=>	"DESC",	// ����������� ��� ������ ���������� ��������
	"SORT_BY2"	=>	"SORT",	// ���� ��� ������ ���������� ��������
	"SORT_ORDER2"	=>	"ASC",	// ����������� ��� ������ ���������� ��������
	"CACHE_TIME"	=>	"0",	// ����� ����������� (0 - �� ����������)
	)
);?></P><A class=maininctitle href="/about/gallery/">&nbsp;���� ���</A> 
<DIV class=mainincline style="WIDTH: 33%"><IMG height=1 alt="" src="/bitrix/images/1.gif" width=1></DIV><BR><?$APPLICATION->IncludeFile("iblock/photo/random.php", Array(
	"IBLOCK_TYPE"	=>	"photo",// ��� ����-�����
	)
);?> </TD>
<TD vAlign=top width="33%"><A class=maininctitle href="/support/vote/index.php">&nbsp;������&nbsp;</A> 
<DIV class=mainincline style="WIDTH: 80%"><IMG height=1 alt="" src="/bitrix/images/1.gif" width=1></DIV>
<P><?$APPLICATION->IncludeFile("vote/vote_new/current_channel.php", Array(
	"CHANNEL_SID"	=>	"PHONES_POLL",// ������ �������
	)
);?><BR><FONT class=smalltext><A href="/support/vote/index.php">����� �������</A></FONT></P></TD></TR></TABLE></TD></TR></TABLE><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>