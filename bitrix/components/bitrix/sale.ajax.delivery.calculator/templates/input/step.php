<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<?=ShowNote($arResult["RESULT"]["TEXT"])?>
<script>deliveryCalcProceed('<?=$arParams["STEP"]+1?>', '<?=$arParams["DELIVERY"]?>', '<?=$arParams["PROFILE"]?>', '<?=$arParams["ORDER_WEIGHT"]?>', '<?=$arParams["ORDER_PRICE"]?>', '<?=$arParams["LOCATION_TO"]?>', '<?=$arParams["CURRENCY"]?>', '<?=$arParams["INPUT_NAME"]?>', '<?=$arResult["RESULT"]["TEMP"]?>');</script>