<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<!--<script type="text/javascript" src="/bitrix/templates/poligon/js/ajax.js"></script>-->
<?
$res = CIBlockSection::GetByID($arResult["IBLOCK_SECTION_ID"]);
if($ar_res = $res->GetNext())
  $section_name = $ar_res['NAME'];
$res1 = CIBlockSection::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>$arResult["IBLOCK_ID"], "ID"=>$ar_res["IBLOCK_SECTION_ID"]), false, array("NAME","UF_PROIZV"));
if($arSect = $res1->GetNext()){ 
	$proizv = $arSect["UF_PROIZV"];
	$section_name2 = $arSect['NAME'];
}

$title_main = $section_name2.' '.$proizv.' > '.$section_name.' > '.$arResult["NAME"];
$img_alt = $section_name2.' '.$proizv.' '.$arResult["NAME"];
$APPLICATION->SetTitle($title_main);?>

<!-- попап для быстрого обращения -->
<div id="orderPopup">
<h1><img src="/images/icons/basket_add.png" alt="Заказ электронных компанентов и НВА"/> заказать <?=$arResult["NAME"];?><img src="/images/icons/cross.png" id="close" alt="Х" title="Закрыть"/></h1>
<p><img src="/images/icons/phone.png" alt="телефон"/> Позвоните нашему менеджеру по телефону (812) 325-42-20. </p>
<p><img src="/images/icons/email.png" alt="email"/> Либо отправьте заявку на выставаление счёта <a href="mailto:elcomp@poligon.info?subject=<?=$arResult["NAME"];?>">e-mail</a>. </p>
<p><img src="/images/icons/box.png" alt="доставка"/> Узнайте про возможные <a href="/content/program/delivery.php" target="_blank">способы доставки</a>. </p>
</div>

<div class="catalog-element">
<table style="width: 100%;">
<tbody>
<tr>
	<td>
		<h1 class="name"><?=$arResult["NAME"]?> <? if ($arResult["PROPERTIES"]["article"]["VALUE"]) echo '('.$arResult["PROPERTIES"]["article"]["VALUE"].')';?></h1>
		<strong><?=$arResult["PREVIEW_TEXT"]?></strong><br/>
		<strong><?=$section_name?></strong><br/>
	</td>
	<td id="onStore">
	<?
	$APPLICATION->IncludeFile( // включаем файл сообщающий об остатках и ценах
		$APPLICATION->GetTemplatePath("include_areas/onStore.php"),
		Array("arResult" => $arResult),
		Array("MODE"=>"php"));			
	?>
	</td>
</tr>
</tbody>
</table>
<?if($arResult["PROPERTIES"]["preview_text"]["~VALUE"]["TEXT"]):?>
<b>Особенности:</b>
<div><?
	print $arResult["PROPERTIES"]["preview_text"]["~VALUE"]["TEXT"];
?></div>
<?endif;?>
<table class="product">
<thead>
	<tr>
		<th colspan="2">Технические характеристики</th>
	</tr>
</thead>
<?if($arResult["PROPERTIES"]["pdf"]["VALUE"]):?>
<tfoot>
	<tr>
	<td></td>
	<td>
		<table bgcolor="#EEEEEE">
		<tr>
			<td>Техническая документация в формате PDF</td>
			<td><a href="/PDF/<?=$arResult['PROPERTIES']['pdf']['VALUE'];?>" style="width: 16px; height: 16px; display: inline-block;"></a></td>
		</tr>
		</table>
	</td>
	</tr>
</tfoot>
<?endif;?>
<tbody>
	<tr>
		<td style="text-align: center; vertical-align: top;">
		<?
		// если не указан бренд, то лого полигона выведем =)
		$brandName = strtolower($arResult["PROPERTIES"]["producer_full"]["VALUE"]);
		if(empty($brandName)){
			$brandName = 'poligon';
		}
		?>
			<img src="/images/logo/transparent/<?=$brandName;?>.png" alt="логотип <?=$brandName;?>" style="width: 150px; display: block; padding: 2px; margin: 0 auto;"/>
			<img class="show product-image" src="/images/<?=($arResult["PROPERTIES"]["link"]["VALUE"]?"{$arResult["PROPERTIES"]["link"]["VALUE"]}.img":"not-found.png");?>" alt="<?=$img_alt;?>"/>		
		</td>
		<td>
		<?if($arResult["~DETAIL_TEXT"])
			print $arResult["~DETAIL_TEXT"];?>
		</td>
	</tr>
</tbody>
</table>
<?
if(!empty($arResult["PROPERTIES"]["addHtml"]["~VALUE"]["TEXT"])){
	print "<div>{$arResult["PROPERTIES"]["addHtml"]["~VALUE"]["TEXT"]}</div>";
}

?>
<!-- close all tags please!!!!!!!!!!! -->
	
	
	<?
	/** рисуем таблицу с функциями */
	// запилили объект для работы с БД
	$mysql = new Mysql();
	$sql = "SELECT * FROM `rele_functions` rf 
			WHERE 1
			AND rf.type = 'реле времени' /* пока наполнены функции только для реле времени теле, 
										в таблицах есть также реле контроля мощности, 
										но надо снабдить функции описаниями/картинками,
										устройствам прописать карактеристики и связи,
										тогда можно использовать весь функционал для обоих групп,
										без этого условия. как-то так. удачи. 
										*/
			AND rf.id IN (SELECT `function_id` FROM `rele_functions_sect` 
			WHERE 1 
			AND	`rele_id` = (SELECT id FROM `rele_ac_dc` WHERE 1
							AND `article` = '{$arResult["PROPERTIES"]["article"]["VALUE"]}'))";
	
	
	$functionsData = $mysql->select_array($sql);
	// если есть связанные с артикулом функции, выводим их в красивой табличке
	if(count($functionsData)):?>
	<table class="benefits">
	<thead><tr>
		<th colspan="2">Описание функций</th>
	</tr><thead>
	<tbody>
	<?foreach($functionsData as $function):?>
		<tr>
			<td><strong><?="{$function['title']} ({$function['code']})"?></strong>
			<p><?=($function['description']?$function['description']:$function['about']);?></p>
			</td>
			<td><img src="/images/TELE/functions/function_<?=$function['code'];?>.jpg"/></td>
		</tr>
	<?endforeach;?>
	</tbody>
	</table>
	<?endif;
		
	
	
	
	/* похоже этот кусок ниже не нужен */
	if(count($arResult["LINKED_ELEMENTS"])>0):?>
		<br /><b><?=$arResult["LINKED_ELEMENTS"][0]["IBLOCK_NAME"]?>:</b>
		<ul>
		<?foreach($arResult["LINKED_ELEMENTS"] as $arElement):?>
			<li><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement["NAME"]?></a></li>
		<?endforeach;?>
		</ul>
	<?endif?>
	<?
	// additional photos
	$LINE_ELEMENT_COUNT = 2; // number of elements in a row
	if(count($arResult["MORE_PHOTO"])>0):?>
		<a name="more_photo"></a>
		<?foreach($arResult["MORE_PHOTO"] as $PHOTO):?>
			<img src="<?=$PHOTO["SRC"]?>" width="<?=$PHOTO["WIDTH"]?>" height="<?=$PHOTO["HEIGHT"]?>" alt="<?=$PHOTO["ALT"]?>" title="<?=$arResult["NAME"]?>"/><br />
		<?endforeach?>
	<?endif?>
	<?
	// additional photos
//	$LINE_ELEMENT_COUNT = 2; // number of elements in a row
	$photosArr = array($arResult["PROPERTIES"]['img1']["VALUE"] => $arResult["PROPERTIES"]['imgTitle1']["VALUE"], 
						$arResult["PROPERTIES"]['img2']["VALUE"] => $arResult["PROPERTIES"]['imgTitle2']["VALUE"], 
						$arResult["PROPERTIES"]['img3']["VALUE"] => $arResult["PROPERTIES"]['imgTitle3']["VALUE"]);

	unset($photosArr[""]);
	
	if(count($photosArr)>0):?>
	<?foreach($photosArr as $img => $title):?>
	<figure class="add-images">
	<figcaption><?=$title;?></figcaption>
	<img src="/images/<?=$img;?>" alt="<?print "{$arResult["NAME"]} {$title}";?>" title="<?print "{$arResult["NAME"]} {$title}";?>" class="show"/>
	</figure>
	<?endforeach?>
	<br style="clear: both;"/>
	<?endif?>
	<?				
	$APPLICATION->IncludeFile(
		$APPLICATION->GetTemplatePath("include_areas/benefits.php"),
		Array("SECTION_ID" => $arResult["SECTION"]["ID"], "ELEMENT_ID" => $arResult["ID"]),
		Array("MODE"=>"php"));
	/* вывод элементов см. также, по артикулам */
	relatedElementsByArticles(array($arResult["PROPERTIES"]["relatedElement1"]["VALUE"],
	$arResult["PROPERTIES"]["relatedElement2"]["VALUE"],
	$arResult["PROPERTIES"]["relatedElement3"]["VALUE"],
	$arResult["PROPERTIES"]["relatedElement4"]["VALUE"],
	$arResult["PROPERTIES"]["relatedElement5"]["VALUE"]));
	?>
	<?if(is_array($arResult["SECTION"])):?>
		<br style="clear: both;"/><a href="<?=$arResult["SECTION"]["SECTION_PAGE_URL"]?>"><?=GetMessage("CATALOG_BACK")?></a>
	<?endif?>

</div>