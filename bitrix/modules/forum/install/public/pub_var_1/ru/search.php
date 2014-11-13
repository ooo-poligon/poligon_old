<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!CModule::IncludeModule("forum")):
	LocalRedirect("index.php");
	die();
endif;


$APPLICATION->SetTitle("Поиск по форуму");
$APPLICATION->SetAdditionalCSS("/bitrix/php_interface/".LANG."/forum.css");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");

include("menu.php");

if (CModule::IncludeModule("search")):
	$q = Trim($q);
?>
<br>
	<table width="100%" border="0" cellspacing="1" cellpadding="4">
	<form action="search.php">
			<tr>
				<td class="forumbodynoborder"><font class="forumheadtext">Ключевые слова:</font></td>
				<td class="forumbodynoborder"><font class="forumbodynobordertext">
					<input type="text" name="q" value="<?echo htmlspecialchars($q)?>" size="40">
				</font></td>
			</tr>
			<tr>
				<td class="forumbodynoborder"><font class="forumheadtext">Искать в форуме:</font></td>
				<td class="forumbodynoborder"><font class="forumbodynobordertext">
					<select name="FORUM_ID">
						<option value="0">(во всех форумах)</option>
						<?
						$arFilter = array("LID" => LANG);
						if(!$USER->IsAdmin())
						{
							$arFilter["PERMS"] = array($USER->GetGroups(), 'A');
							$arFilter["ACTIVE"] = "Y";
						}
						$db_Forum = CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
						$currentGroupID = -1;
						while ($db_Forum->NavNext(true, "f_", false)):
							if($currentGroupID != IntVal($f_FORUM_GROUP_ID)):
								if(IntVal($f_FORUM_GROUP_ID)>0):
									$arCurForumGroup = CForumGroup::GetLangByID($f_FORUM_GROUP_ID, LANG);
						?>
							<option value="<?echo $f_ID?>"><?echo htmlspecialcharsex($arCurForumGroup["NAME"]);?></option>
						<?
								endif;
								$currentGroupID = IntVal($f_FORUM_GROUP_ID);
							endif;
							?><option value="<?echo $f_ID?>" <?if (IntVal($f_ID)==IntVal($FORUM_ID)) {echo "selected";}?>><?if($currentGroupID <> -1) echo " . . ";?><?echo $f_NAME?></option><?
						endwhile;
						?>
					</select>
				</font></td>
			</tr>
			<tr><td colspan="2" align="right" class="forumbodynoborder"><font class="forumbodynobordertext">
				<input type="submit" name="s" value="Поиск">
			</font></td></tr>
	</form>
		</table>

<?if($HELP <> "Y"):?>
<font style="font-size:8px;">&nbsp;<br></font>
<table border="0" cellspacing="0" cellpadding="0">
<tr valign="top">
	<td><img src="/bitrix/images/forum/arr.gif" width="4" height="7" border="0" alt="" hspace="0" vspace="5"></td>
	<td><font class="forumbodytext">&nbsp;</font></td>
	<td><font class="forumbodytext"><a href="search.php?HELP=Y">Помощь по поиску</a></font></td>
</tr>
</table>
<?else:?>
<p class="text">
<b>Синтаксис поискового запроса</b><br><br>

Обычно запрос представляет из себя просто одно или несколько слов, 
например: <br><br>

<i>контактная информация</i><br><br>
 
По такому запросу будут найдены страницы, на которых встречаются оба слова запроса. <br><br> 

Логические операторы позволяют строить более сложные запросы, например: <br><br>

<i>контактная информация или телефон</i><br><br>

По такому запросу будут найдены страницы, на которых встречаются либо слова 
&quot;контактная&quot; и &quot;информация&quot;, либо слово 
&quot;телефон&quot;.<br><br> 

<i>контактная информация не телефон</i><br><br>
 
По такому запросу будут найдены страницы, на которых встречаются либо 
слова &quot;контактная&quot; и &quot;информация&quot;, но не встречается 
слово &quot;телефон&quot;.<br><br>
 
Вы можете использовать скобки для 
построения более сложных запросов.<br><br> 

<b>Логические операторы</b> 
</p>
<table border="0" cellspacing="0" cellpadding="5">
	<tr>
		<td align="center" valign="top"><font class="text">Оператор</font></td>
		<td valign="top"><font class="text">Синонимы</font></td>
		<td><font class="text">Описание</font></td>
	</tr>
	<tr>
		<td align="center" valign="top"><font class="text">и</font></td>
		<td valign="top"><font class="text">and, &, +</font></td>
		<td><font class="text">Оператор <i>логическое &quot;и&quot;</i> подразумевается, его можно опускать: запрос &quot;контактная информация&quot; полностью эквивалентен запросу &quot;контактная и информация&quot;.</font></td>
	</tr>
	<tr>
		<td align="center" valign="top"><font class="text">или</font></td>
		<td valign="top"><font class="text">or, |</font></td>
		<td><font class="text">Оператор <i>логическое &quot;или&quot;</i> позволяет искать товары, содержащие хотя бы один из операндов. </font></td>
	</tr>
	<tr>
		<td align="center" valign="top"><font class="text">не</font></td>
		<td valign="top"><font class="text">not, ~</font></td>
		<td><font class="text">Оператор <i>логическое &quot;не&quot;</i> ограничивает поиск страниц, не содержащих слово, указанное после оператора. </font></td>
	</tr>
	<tr>
		<td align="center" valign="top"><font class="text">( )</font></td>
		<td valign="top"><font class="text">&nbsp;</font></td>
		<td><font class="text"><i>Круглые скобки</i> задают порядок действия логических операторов. </font></td>
	</tr>
</table>

<?endif; //HELP?>
	
<?
	if(strlen($q)>0):
		$FORUM_ID = IntVal($FORUM_ID);
		if ($FORUM_ID<=0) $FORUM_ID = false;
		$obSearch = new CSearch($q, LANG, "forum", false, $FORUM_ID);
		if ($obSearch->errorno!=0):
			?>
			<p class="text">В поисковой фразе обнаружена ошибка:</p> 
			<?echo ShowError($obSearch->error);?>
			<p class="text">Исправьте поисковую фразу и повторите поиск.</p>
			<?
		else:
			$obSearch->NavStart(20, false);
			?>
			<p><?$obSearch->NavPrint("Результаты поиска");?></p>
			<?
			$bEmptyFlag = True;
			while ($arResult = $obSearch->GetNext()):
				$bEmptyFlag = False;
				?>
				<font class="text">
				<a href="<?echo $arResult["URL"]?>"><?echo $arResult["TITLE_FORMATED"]?></a><br>
				<?echo $arResult["BODY_FORMATED"]?>
				<hr size="1">
				</font>
				<?
			endwhile;
			?>

			<p><?$obSearch->NavPrint("Результаты поиска");?></p>
			<?
			if ($bEmptyFlag)
			{
				?>
				<p class="text">
				На ваш запрос ничего не найдено. Попробуйте переформулировать запрос.
				</p>
				<?
			}
		endif;
	endif;
else:
	?><font class="text">Модуль поиска не установлен.</font><?
endif;
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");?>