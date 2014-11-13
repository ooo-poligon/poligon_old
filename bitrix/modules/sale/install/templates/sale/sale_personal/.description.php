<?
IncludeTemplateLangFile(__FILE__);

$arTemplateDescription =
	Array(
		".separator" =>
			Array(
				"NAME" => GetMessage("STPD_PER_FOLDER"),
				"DESCRIPTION" => "",
				"SEPARATOR" => "Y"
			),
		"account.php" =>
			Array(
				"NAME" => GetMessage("STPD_ACCOUNT"),
				"DESCRIPTION" => GetMessage("STPD_ACCOUNT"),
				"ICON" => "/bitrix/images/sale/components/sale_account.gif",
				"PARAMS" => Array()
				),
		"cc_list.php" =>
			Array(
				"NAME" => GetMessage("STPD_CCARDS"),
				"DESCRIPTION" => GetMessage("STPD_CCARDS"),
				"ICON" => "/bitrix/images/sale/components/sale_cards.gif",
				"PARAMS" =>
					Array(
						"PATH_TO_DETAIL" => Array("NAME"=>GetMessage("STPD_CCARDS_DET"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"sale_cc_detail.php", "COLS"=>25)
						)
				),
		"sale_cc_detail.php" =>
			Array(
				"NAME" => GetMessage("STPD_CCARDS_DET_PAGE"),
				"DESCRIPTION" => GetMessage("STPD_CCARDS_DET_PAGE"),
				"ICON" => "/bitrix/images/sale/components/sale_card_edit.gif",
				"PARAMS" =>
					Array(
						"PATH_TO_LIST" => Array("NAME"=>GetMessage("STPD_CCARDS_LIST"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"sale_cc_list.php", "COLS"=>25)
						)
				),
		"order_list.php" =>
			Array(
				"NAME" => GetMessage("STPD_ORDER_LIST"),
				"DESCRIPTION" => GetMessage("STPD_ORDER_LIST"),
				"ICON" => "/bitrix/images/sale/components/sale_orders.gif",
				"PARAMS" =>
					Array(
						"PATH_TO_DETAIL" => Array("NAME"=>GetMessage("STPD_ORDER_DET_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"sale_order_detail.php", "COLS"=>25),
						"PATH_TO_COPY" => Array("NAME"=>GetMessage("STPD_ORDER_REP_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"index.php", "COLS"=>25),
						"PATH_TO_CANCEL" => Array("NAME"=>GetMessage("STPD_ORDER_CANC_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"sale_order_cancel.php", "COLS"=>25),
						"PATH_TO_BASKET" => Array("NAME"=>GetMessage("STPD_ORDER_BASK_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"basket.php", "COLS"=>25)
						)
				),
		"order_detail.php" =>
			Array(
				"NAME" => GetMessage("STPD_ORDER_DET"),
				"DESCRIPTION" => GetMessage("STPD_ORDER_DET_DESCR"),
				"ICON" => "/bitrix/images/sale/components/sale_order_detail.gif",
				"PARAMS" =>
					Array(
						"PATH_TO_LIST" => Array("NAME"=>GetMessage("STPD_ORDER_LIST_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"index.php", "COLS"=>25),
						"PATH_TO_CANCEL" => Array("NAME"=>GetMessage("STPD_ORDER_CANC_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"sale_order_cancel.php", "COLS"=>25),
						"PATH_TO_PAYMENT" => Array("NAME"=>GetMessage("STPD_ORDER_PAY_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"sale_payment.php", "COLS"=>25)
						)
				),
		"order_cancel.php" =>
			Array(
				"NAME" => GetMessage("STPD_CANSEL"),
				"DESCRIPTION" => GetMessage("STPD_CANCEL_DESCR"),
				"ICON" => "/bitrix/images/sale/components/sale_order_cancel.gif",
				"PARAMS" =>
					Array(
						"PATH_TO_DETAIL" => Array("NAME"=>GetMessage("STPD_ORDER_DET_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"sale_order_detail.php", "COLS"=>25),
						"PATH_TO_LIST" => Array("NAME"=>GetMessage("STPD_ORDER_LIST_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"index.php", "COLS"=>25)
						)
				),
		"order_table.php" =>
			Array(
				"NAME" => GetMessage("STPD_ORDER_TABLE"),
				"DESCRIPTION" => GetMessage("STPD_ORDER_TABLE"),
				"ICON" => "/bitrix/images/sale/components/sale_orders_tbl.gif",
				"PARAMS" =>
					Array(
						"PATH_TO_DETAIL" => Array("NAME"=>GetMessage("STPD_ORDER_DET_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"sale_order_detail.php", "COLS"=>25),
						"PATH_TO_COPY" => Array("NAME"=>GetMessage("STPD_ORDER_REP_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"index.php", "COLS"=>25),
						"PATH_TO_CANCEL" => Array("NAME"=>GetMessage("STPD_ORDER_CANC_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"sale_order_cancel.php", "COLS"=>25),
						"PATH_TO_BASKET" => Array("NAME"=>GetMessage("STPD_ORDER_BASK_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"basket.php", "COLS"=>25)
						)
				),
		"profile_list.php" =>
			Array(
				"NAME" => GetMessage("STPD_PROFILE_LIST"),
				"DESCRIPTION" => GetMessage("STPD_PROFILE_LIST"),
				"ICON" => "/bitrix/images/sale/components/sale_profile_list.gif",
				"PARAMS" =>
					Array(
						"PATH_TO_DETAIL" => Array("NAME"=>GetMessage("STPD_PROFILE_DET_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"sale_profile_detail.php", "COLS"=>25)
						)
				),
		"profile_detail.php" =>
			Array(
				"NAME" => GetMessage("STPD_PROFILE_DET"),
				"DESCRIPTION" => GetMessage("STPD_PROFILE_DET1"),
				"ICON" => "/bitrix/images/sale/components/sale_profile_edit.gif",
				"PARAMS" =>
					Array(
						"PATH_TO_LIST" => Array("NAME"=>GetMessage("STPD_PROFILE_LIST_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"sale_profile_list.php", "COLS"=>25)
						)
				),
		"subscribe_list.php" =>
			Array(
				"NAME" => GetMessage("STPD_SUBSCR_LIST"),
				"DESCRIPTION" => GetMessage("STPD_SUBSCR_LIST"),
				"ICON" => "/bitrix/images/sale/components/sale_subscr_list.gif",
				"PARAMS" =>
					Array(
						"PATH_TO_CANCEL" => Array("NAME"=>GetMessage("STPD_SUBSCR_CANCEL"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"sale_subscr_cancel.php", "COLS"=>25)
						)
				),
		"subscribe_cancel.php" =>
			Array(
				"NAME" => GetMessage("STPD_CANCEL"),
				"DESCRIPTION" => GetMessage("STPD_CANCEL_DESCR1"),
				"ICON" => "/bitrix/images/sale/components/sale_subscr_cancel.gif",
				"PARAMS" =>
					Array(
						"PATH_TO_LIST" => Array("NAME"=>GetMessage("STPD_CANCEL_LIST_PAGE"), "TYPE"=>"STRING", "MULTIPLE"=>"N", "DEFAULT"=>"sale_subscr_list.php", "COLS"=>25)
						)
				)
		);
?>