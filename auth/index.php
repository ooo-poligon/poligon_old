<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if (isset($_REQUEST["backurl"]) && strlen($_REQUEST["backurl"])>0) 
	LocalRedirect($backurl);

$APPLICATION->SetTitle("�����������");
?>
<p>�� ���������������� � ������� ��������������.</p>
<?
if ($USER->IsAuthorized()):
$APPLICATION->IncludeComponent(
	"bitrix:system.auth.form",
	"",
	Array(
		"REGISTER_URL" => "", 
		"PROFILE_URL" => "", 
		"SHOW_ERRORS" => "N" 
	)
);
endif;
?>
 
 
<p><a href="<?=SITE_DIR?>">��������� �� ������� ��������</a></p>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>