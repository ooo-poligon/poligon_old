<?
/***************************************************************************/
/* Two-Step order wizard template                                          */
/* IMPORTANT! Properties codes of billing address should be the            */
/* same as properties codes of shiping address but enging with '_billing'. */
/* IMPORTANT! You should change this template to match real parameters     */
/* (property code, person type, property group id, shopping cart url etc.) */
/* This is NOT a universal template. It should be modified before use.     */
/* Pay attention to the lines with "Change this" comments.                 */
/***************************************************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");
?>
<?
$APPLICATION->IncludeFile("sale/sale_tmpl_2/order.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");
?>