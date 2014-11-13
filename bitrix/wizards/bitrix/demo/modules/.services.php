<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arServices = Array(

	"main" => Array(
		"NAME" => "Базовые настройки",
		"STAGES" => Array("files.php", "settings.php"),
		"INSTALL_ONLY" => "Y",
	),

	"search" => Array(
		"NAME" => GetMessage("SERVICE_SEARCH"),
		"INSTALL_ONLY" => "Y",
	),

	"statistic" => Array(
		"NAME" => "Статистика",
		"INSTALL_ONLY" => "Y",
	),

	"catalog" => Array(
		"NAME" => "Каталог",
		"INSTALL_ONLY" => "Y",
	),

	"articles" => Array(
		"NAME" => "Статьи",
		"MODULE_ID" => "iblock",
		"ICON" => "images/services/content.gif",
	),

	"news" => Array(
		"NAME" => GetMessage("SERVICE_NEWS"),
		"MODULE_ID" => "iblock",
		"ICON" => "images/services/content.gif",
	),

	"books" => Array(
		"NAME" => "Каталог книг",
		"STAGES" => Array("step1.php", "step2.php", "step3.php", "step4.php", "step5.php", "step6.php"),
		"MODULE_ID" => "iblock",
		"ICON" => "images/services/content.gif",
	),

	"xmlcatalog" => Array(
		"NAME" => "Каталог 1C",
		"STAGES" => Array("step1.php", "step2.php", "step3.php", "step4.php", "step5.php", "step6.php", "step7.php", "step8.php", "step9.php"),
		"MODULE_ID" => "iblock",
		"ICON" => "images/services/content.gif",
	),

	/*"paid" => Array(
		"NAME" => "Платный контент",
		"MODULE_ID" => "iblock",
		"ICON" => "images/services/content.gif",
	),

	"links" => Array(
		"NAME" => "Каталог ресурсов",
		"MODULE_ID" => "iblock",
		"ICON" => "images/services/content.gif",
	),

	"board" => Array(
		"NAME" => "Доска объявлений",
		"MODULE_ID" => "iblock",
		"ICON" => "images/services/content.gif",
	),

	"faq" => Array(
		"NAME" => "Часто задаваемые вопросы",
		"MODULE_ID" => "iblock",
		"ICON" => "images/services/content.gif",
	),*/


	"subscribe" => Array(
		"NAME" => "Подписка",
		"ICON" => "images/services/subscribe.gif",
	),


	"forum" => array(
		'NAME' => "Форум",
		"ICON" => "images/services/forum.gif",
	),

	"sale" => array(
		"NAME" => "Интернет-магазин",
		"MODULE_ID" => Array("sale", "currency"),
		"STAGES" => Array("step1.php", "step2.php", "step3.php"),
		"ICON" => "images/services/sale.gif",
	),

	"advertising" => Array(
		"NAME" => "Реклама",
		"ICON" => "images/services/advertising.gif",
	),

	"photogallery" => Array(
		"NAME" => "Фотогалерея",
		"ICON" => "images/services/photogallery.gif",
		'MODULE_ID' => Array("photogallery", "iblock"),
		"STAGES" => Array("index.php", "index1.php"),
	),


	'form' => array(
		'NAME' => "Веб-формы",
		'MODULE_ID' => 'form',
		"STAGES" => Array("anketa.php", "feedback.php"),
		"ICON" => "images/services/form.gif",
	),

	/*'form_anketa' => array(
		'NAME' => GetMessage('FORM_ANKETA'),
		'MODULE_ID' => 'form',
		"ICON" => "images/services/form.gif",
	),

	'form_feedback' => array(
		'NAME' => GetMessage('FORM_FEEDBACK'),
		'MODULE_ID' => 'form',
		"ICON" => "images/services/form.gif",
	),*/

	'blog' => array(
		'NAME' => 'Блоги',
		"ICON" => "images/services/blog.gif",
	),

	'support' => array(
		'NAME' => 'Техподдержка',
		"ICON" => "images/services/support.gif",
	),

	'vote' => array(
		'NAME' => 'Опросы',
		"ICON" => "images/services/vote.gif",
	),

	"learning" => Array(
		"NAME" => "Обучение",
		"ICON" => "images/services/learning.gif",
	),

	"examples" => Array(
		"NAME" => "Типовые примеры",
		"MODULE_ID" => Array("main", "iblock"),
		"ICON" => "images/services/other.gif",
		"STAGES" => Array("index.php", "board.php", "links.php", "faq.php", "paid.php"),
		"DESCRIPTION" => "FAQ, Каталог ресурсов, Доска объявлений, Платный контент"
	),

);


?>