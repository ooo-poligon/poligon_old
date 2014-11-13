<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arServices = Array(

	"main" => Array(
		"NAME" => "������� ���������",
		"STAGES" => Array("files.php", "settings.php"),
		"INSTALL_ONLY" => "Y",
	),

	"search" => Array(
		"NAME" => GetMessage("SERVICE_SEARCH"),
		"INSTALL_ONLY" => "Y",
	),

	"statistic" => Array(
		"NAME" => "����������",
		"INSTALL_ONLY" => "Y",
	),

	"catalog" => Array(
		"NAME" => "�������",
		"INSTALL_ONLY" => "Y",
	),

	"articles" => Array(
		"NAME" => "������",
		"MODULE_ID" => "iblock",
		"ICON" => "images/services/content.gif",
	),

	"news" => Array(
		"NAME" => GetMessage("SERVICE_NEWS"),
		"MODULE_ID" => "iblock",
		"ICON" => "images/services/content.gif",
	),

	"books" => Array(
		"NAME" => "������� ����",
		"STAGES" => Array("step1.php", "step2.php", "step3.php", "step4.php", "step5.php", "step6.php"),
		"MODULE_ID" => "iblock",
		"ICON" => "images/services/content.gif",
	),

	"xmlcatalog" => Array(
		"NAME" => "������� 1C",
		"STAGES" => Array("step1.php", "step2.php", "step3.php", "step4.php", "step5.php", "step6.php", "step7.php", "step8.php", "step9.php"),
		"MODULE_ID" => "iblock",
		"ICON" => "images/services/content.gif",
	),

	/*"paid" => Array(
		"NAME" => "������� �������",
		"MODULE_ID" => "iblock",
		"ICON" => "images/services/content.gif",
	),

	"links" => Array(
		"NAME" => "������� ��������",
		"MODULE_ID" => "iblock",
		"ICON" => "images/services/content.gif",
	),

	"board" => Array(
		"NAME" => "����� ����������",
		"MODULE_ID" => "iblock",
		"ICON" => "images/services/content.gif",
	),

	"faq" => Array(
		"NAME" => "����� ���������� �������",
		"MODULE_ID" => "iblock",
		"ICON" => "images/services/content.gif",
	),*/


	"subscribe" => Array(
		"NAME" => "��������",
		"ICON" => "images/services/subscribe.gif",
	),


	"forum" => array(
		'NAME' => "�����",
		"ICON" => "images/services/forum.gif",
	),

	"sale" => array(
		"NAME" => "��������-�������",
		"MODULE_ID" => Array("sale", "currency"),
		"STAGES" => Array("step1.php", "step2.php", "step3.php"),
		"ICON" => "images/services/sale.gif",
	),

	"advertising" => Array(
		"NAME" => "�������",
		"ICON" => "images/services/advertising.gif",
	),

	"photogallery" => Array(
		"NAME" => "�����������",
		"ICON" => "images/services/photogallery.gif",
		'MODULE_ID' => Array("photogallery", "iblock"),
		"STAGES" => Array("index.php", "index1.php"),
	),


	'form' => array(
		'NAME' => "���-�����",
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
		'NAME' => '�����',
		"ICON" => "images/services/blog.gif",
	),

	'support' => array(
		'NAME' => '������������',
		"ICON" => "images/services/support.gif",
	),

	'vote' => array(
		'NAME' => '������',
		"ICON" => "images/services/vote.gif",
	),

	"learning" => Array(
		"NAME" => "��������",
		"ICON" => "images/services/learning.gif",
	),

	"examples" => Array(
		"NAME" => "������� �������",
		"MODULE_ID" => Array("main", "iblock"),
		"ICON" => "images/services/other.gif",
		"STAGES" => Array("index.php", "board.php", "links.php", "faq.php", "paid.php"),
		"DESCRIPTION" => "FAQ, ������� ��������, ����� ����������, ������� �������"
	),

);


?>