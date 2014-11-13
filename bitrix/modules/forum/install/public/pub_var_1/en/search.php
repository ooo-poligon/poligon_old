<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!CModule::IncludeModule("forum")):
	LocalRedirect("index.php");
	die();
endif;


$APPLICATION->SetTitle("Search");
$APPLICATION->SetAdditionalCSS("/bitrix/php_interface/".LANG."/forum.css");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");

if(!@include("menu.php"))
	if(!@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/".LANG."/menu.php"))
		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/en/menu.php");

if (CModule::IncludeModule("search")):
	$q = Trim($q);
	?>
	<form action="search.php">
	<table width="100%" border="0" cellspacing="1" cellpadding="0" class="forumborder"><tr><td>
		<table width="100%" border="0" cellspacing="1" cellpadding="1">
			<tr><td colspan="2" align="center" class="forumhead"><font class="forumheadtext"><b>Search</b></font></td></tr>
			<tr>
				<td class="forumbody" align="right"><font class="forumheadtext">Search Keywords:</font></td>
				<td class="forumbody"><font class="forumbodytext">
					<input type="text" name="q" value="<?echo htmlspecialchars($q)?>" size="40">
				</font></td>
			</tr>
			<tr>
				<td class="forumbody" align="right"><font class="forumheadtext">Search Where:</font></td>
				<td class="forumbody"><font class="forumbodytext">
					<select name="FORUM_ID">
						<option value="0">All forums</option>
						<?
						$arFilter = array("LID" => LANG);
						if (!$USER->IsAdmin())
						{
							$arFilter["PERMS"] = array($USER->GetGroups(), 'A');
							$arFilter["ACTIVE"] = "Y";
						}
						$db_Forum = CForumNew::GetListEx(array("SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
						while ($db_Forum->NavNext(true, "f_", false)):
							?><option value="<?echo $f_ID?>" <?if (IntVal($f_ID)==IntVal($FORUM_ID)) {echo "selected";}?>><?echo $f_NAME?></option><?
						endwhile;
						?>
					</select>
				</font></td>
			</tr>
			<tr><td colspan="2" align="center" class="forumbody"><font class="forumbodytext">
				<input type="submit" name="s" value="Search">
			</font></td></tr>
		</table>
	</td></tr></table>
	</form>

	<?
	if (strlen($q)>0):
		$FORUM_ID = IntVal($FORUM_ID);
		if ($FORUM_ID<=0) $FORUM_ID = false;
		$obSearch = new CSearch($q, LANG, "forum", false, $FORUM_ID);
		if ($obSearch->errorno!=0):
			?>
			<font class="text">The search query contains an error:</font> 
			<?echo ShowError($obSearch->error);?>
			<font class="text">Please correct the search query and try again.</font><br><br>

			<font class="text">
			<b>Search query syntax:</b><br><br>
			A common search query consists of one or more words: <br>	<i>contact information</i><br>This search query will find pages containing both query words. <br><br>Logical operators allow building more sophisticated queries, for example: <br> <i>contact information or phone</i><br>This search query will find pages containing either both &quot;contact&quot; and &quot;information&quot; words or &quot;phone&quot; word.<br><br> <i>contact information not phone</i><br>This search query will find pages containing either both &quot;contact&quot; and &quot;information&quot; words but not &quot;phone&quot;.<br>You can also use brackets to build even more sophisticated queries.<br><br> <b>Logical operators:</b> <table border="0" cellpadding="5"><tr><td align="center" valign="top"><font class="text">Operator</font></td><td valign="top"><font class="text">Synonims</font></td><td><font class="text">Description</font></td></tr><tr><td align="center" valign="top"><font class="text">and</font></td><td valign="top"><font class="text">and, &, +</font></td><td><font class="text">The <i>logical &quot;and&quot;</i> operator is implied and can be omitted: the query &quot;contact information&quot; is equivalent to the query &quot;contact and information&quot;.</font></td></tr><tr><td align="center" valign="top"><font class="text">or</font></td><td valign="top"><font class="text">or, |</font></td><td><font class="text">The <i>logical &quot;or&quot;</i> operator entails product search containing at least one of the operands. </font></td></tr><tr><td align="center" valign="top"><font class="text">not</font></td><td valign="top"><font class="text">not, ~</font></td><td><font class="text">The <i>logical &quot;not&quot;</i> operator limits the search to pages not containing the operand. </font></td></tr><tr><td align="center" valign="top"><font class="text">( )</font></td><td valign="top"><font class="text">&nbsp;</font></td><td><font class="text:"><i>Round brackets</i> define the logical operator execution sequence. </font></td></tr></table>
			</font>			
			<?
		else:
			$obSearch->NavStart(20, false);
			$obSearch->NavPrint("Search results");
			?>
			<br><br>
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

			$obSearch->NavPrint("Search results");

			if ($bEmptyFlag)
			{
				?>
				<font class="text">
				Your search did not match any documents. Try different keywords.
				</font>
				<?
			}
		endif;
	endif;
else:
	?><font class="text">Search module is not installed.</font><?
endif;
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");?>