<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Корзина");
?><?$APPLICATION->IncludeComponent("bitrix:sale.basket.basket", "basket", Array(
	"COUNT_DISCOUNT_4_ALL_QUANTITY"	=>	"Y",
	"COLUMNS_LIST"	=>	array(
		0	=>	"NAME",
		1	=>	"PRICE",
		2	=>	"QUANTITY",
		3	=>	"DELETE",
	),
	"PATH_TO_ORDER"	=>	"/personal/order/make/",
	"HIDE_COUPON"	=>	"Y",
	"QUANTITY_FLOAT"	=>	"N",
	"PRICE_VAT_SHOW_VALUE"	=>	"N",
	"SET_TITLE"	=>	"Y"
	)
);?>
<?
/*include ('form.php');
$APPLICATION->IncludeFile(
				$APPLICATION->GetTemplatePath("/personal/cart/text.php"),
				Array(),
				Array("MODE"=>"html")
);*/
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
