<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Личный кабинет");
?> 
<p>На странице <b>Настройка пользователя</b> пользователь имеет возможность редактировать личные данные, регистрационную информацию, информацию о работе и т. д. Вывод данной формы осуществлен с помощью компонента <i>Параметры пользователя</i>.</p>

<p><?$APPLICATION->IncludeComponent(
	"bitrix:menu",
	"",
	Array(
		"ROOT_MENU_TYPE" => "left", 
		"MAX_LEVEL" => "1", 
		"CHILD_MENU_TYPE" => "left", 
		"USE_EXT" => "N" 
	)
);?> </p>
 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>