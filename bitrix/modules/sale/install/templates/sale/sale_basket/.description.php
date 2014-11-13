<?
IncludeTemplateLangFile(__FILE__);

$arTemplateDescription =
	Array(
		".separator" =>
			Array(
				"NAME" => GetMessage("TSBD_BASKET"),
				"DESCRIPTION" => "",
				"SEPARATOR" => "Y"
			),
		"basket.php" =>
			Array(
				"NAME" => GetMessage("TSD_NAME"),
				"DESCRIPTION" => GetMessage("TSD_NAME_DESCR"),
				"ICON" => "/bitrix/images/sale/components/sale_basket.gif",
				"PARAMS" =>
					Array(
						"ORDER_PAGE" => Array("NAME"=>GetMessage("TSD_PARAM_ORDER_PAGE_NAME"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"/catalog/order.php", "COLS"=>25),
						"HIDE_COUPON" => Array("NAME"=>GetMessage("TSBD_HIDE_COUPON"), "TYPE"=>"LIST", "MULTIPLE"=>"N", "VALUES"=>array("N" => GetMessage("TSBD_NO"), "Y" => GetMessage("TSBD_YES")), "DEFAULT"=>"N", "COLS"=>25, "ADDITIONAL_VALUES"=>"N"),
						"COLUMNS_LIST" => Array("NAME"=>GetMessage("TSBD_COLUMNS_LIST"), "TYPE"=>"LIST", "MULTIPLE"=>"Y", "VALUES"=>array("NAME" => GetMessage("TSBD_BNAME"), "PRICE" => GetMessage("TSBD_BPRICE"), "TYPE" => GetMessage("TSBD_BTYPE"), "QUANTITY" => GetMessage("TSBD_BQUANTITY"), "DELETE" => GetMessage("TSBD_BDELETE"), "DELAY" => GetMessage("TSBD_BDELAY"), "WEIGHT" => GetMessage("TSBD_BWEIGHT")), "DEFAULT"=>array("NAME", "PRICE", "TYPE", "QUANTITY", "DELETE", "DELAY", "WEIGHT"), "COLS"=>25, "ADDITIONAL_VALUES"=>"N")
						)
				),
		"basket_small.php" =>
			Array(
				"NAME" => GetMessage("TSD_NAME_SMALL"),
				"DESCRIPTION" => GetMessage("TSD_NAME_SMALL_DESCR"),
				"ICON" => "/bitrix/images/sale/components/sale_small_basket.gif",
				"PARAMS" =>
					Array(
						"BASKET_PAGE" => Array("NAME"=>GetMessage("TSD_PARAM_BASKET_PAGE_NAME"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"/catalog/basket.php", "COLS"=>25),
						"ORDER_PAGE" => Array("NAME"=>GetMessage("TSD_PARAM_ORDER_PAGE_NAME"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"/catalog/order.php", "COLS"=>25)
						)
				),
		"basket_line.php" =>
			Array(
				"NAME" => GetMessage("TSD_NAME_LINE"),
				"DESCRIPTION" => GetMessage("TSD_NAME_LINE_DESCR"),
				"ICON" => "/bitrix/images/sale/components/sale_basket_link.gif",
				"PARAMS" =>
					Array(
						"BASKET_PAGE" => Array("NAME"=>GetMessage("TSD_PARAM_BASKET_PAGE_NAME"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"/catalog/basket.php", "COLS"=>25)
						)
				)
		);
?>