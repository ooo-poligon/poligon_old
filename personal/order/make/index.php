<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы");
?><?$APPLICATION->IncludeComponent("bitrix:sale.order.full", "orderfull", Array(
	"ALLOW_PAY_FROM_ACCOUNT"	=>	"N",
	"SHOW_MENU"	=>	"N",
	"COUNT_DELIVERY_TAX"	=>	"Y",
	"COUNT_DISCOUNT_4_ALL_QUANTITY"	=>	"Y",
	"PATH_TO_BASKET"	=>	"/personal/cart/",
	"PATH_TO_PERSONAL"	=>	"/personal/order/",
	"PATH_TO_AUTH"	=>	"/auth/",
	"PATH_TO_PAYMENT"	=>	"/personal/order/payment/",
	"USE_AJAX_LOCATIONS"	=>	"Y",
	"SHOW_AJAX_DELIVERY_LINK"	=>	"Y",
	"SET_TITLE"	=>	"Y",
	"PRICE_VAT_INCLUDE"	=>	"Y",
	"PRICE_VAT_SHOW_VALUE"	=>	"Y"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>