<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!CModule::IncludeModule("forum")):
	LocalRedirect("index.php");
	die();
endif;

ForumSetLastVisit();
define("FORUM_MODULE_PAGE", "SEARCH");
$APPLICATION->SetTitle("����� �� ������");
$APPLICATION->SetAdditionalCSS("/bitrix/php_interface/".LANG."/forum.css");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");

$path2curdir = str_replace("\\\\", "/", dirname(__FILE__)."/");
if (file_exists($path2curdir."menu.php"))
	include($path2curdir."menu.php");
elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/".LANG."/menu.php"))
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/".LANG."/menu.php");
else
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/en/menu.php");

if (CModule::IncludeModule("search")):
	$q = Trim($q);
	?>
	<form action="search.php">
	<table width="100%" border="0" cellspacing="1" cellpadding="0" class="forumborder"><tr><td>
		<table width="100%" border="0" cellspacing="1" cellpadding="1">
			<tr><td colspan="2" align="center" class="forumhead"><font class="forumheadtext"><b>�����</b></font></td></tr>
			<tr>
				<td class="forumbody" align="right"><font class="forumheadtext">�������� �����:</font></td>
				<td class="forumbody"><font class="forumbodytext">
					<input type="text" name="q" value="<?echo htmlspecialchars($q)?>" size="40">
				</font></td>
			</tr>
			<tr>
				<td class="forumbody" align="right"><font class="forumheadtext">������ � ������:</font></td>
				<td class="forumbody"><font class="forumbodytext">
					<select name="FORUM_ID">
						<option value="0">��� ������</option>
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
				<input type="submit" name="s" value="������">
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
			<font class="text">� ��������� ����� ���������� ������:</font> 
			<?echo ShowError($obSearch->error);?>
			<font class="text">��������� ��������� ����� � ��������� �����.</font><br><br>

			<font class="text">
			<b>��������� ���������� �������:</b><br><br>
			������ ������ ������������ �� ���� ������ ���� ��� ��������� ����, 
			��������: <br>	<i>���������� ����������</i><br> �� ������ ������� ����� 
			������� ��������, �� ������� ����������� ��� ����� �������. <br><br> 
			���������� ��������� ��������� ������� ����� ������� �������, ��������: 
			<br> <i>���������� ���������� ��� �������</i><br> �� ������ ������� 
			����� ������� ��������, �� ������� ����������� ���� ����� 
			&quot;����������&quot; � &quot;����������&quot;, ���� ����� 
			&quot;�������&quot;.<br><br> <i>���������� ���������� �� �������</i><br> 
			�� ������ ������� ����� ������� ��������, �� ������� ����������� ���� 
			����� &quot;����������&quot; � &quot;����������&quot;, �� �� ����������� 
			����� &quot;�������&quot;.<br> �� ������ ������������ ������ ��� 
			���������� ����� ������� ��������.<br><br> <b>���������� ���������:</b> 
			<table border="0" cellpadding="5">
				<tr>
					<td align="center" valign="top"><font class="text">��������</font></td>
					<td valign="top"><font class="text">��������</font></td>
					<td><font class="text">��������</font></td>
				</tr>
				<tr>
					<td align="center" valign="top"><font class="text">�</font></td>
					<td valign="top"><font class="text">and, &, +</font></td>
					<td><font class="text">�������� <i>���������� &quot;�&quot;</i> ���������������, ��� ����� ��������: ������ &quot;���������� ����������&quot; ��������� ������������ ������� &quot;���������� � ����������&quot;.</font></td>
				</tr>
				<tr>
					<td align="center" valign="top"><font class="text">���</font></td>
					<td valign="top"><font class="text">or, |</font></td>
					<td><font class="text">�������� <i>���������� &quot;���&quot;</i> ��������� ������ ������, ���������� ���� �� ���� �� ���������. </font></td>
				</tr>
				<tr>
					<td align="center" valign="top"><font class="text">��</font></td>
					<td valign="top"><font class="text">not, ~</font></td>
					<td><font class="text">�������� <i>���������� &quot;��&quot;</i> ������������ ����� �������, �� ���������� �����, ��������� ����� ���������. </font></td>
				</tr>
				<tr>
					<td align="center" valign="top"><font class="text">( )</font></td>
					<td valign="top"><font class="text">&nbsp;</font></td>
					<td><font class="text"><i>������� ������</i> ������ ������� �������� ���������� ����������. </font></td>
				</tr>
			</table>
			</font>			
			<?
		else:
			$obSearch->NavStart(20, false);
			$obSearch->NavPrint("���������� ������");
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

			$obSearch->NavPrint("���������� ������");

			if ($bEmptyFlag)
			{
				?>
				<font class="text">
				������ �� �������. ���������� ����������������� ������.
				</font>
				<?
			}
		endif;
	endif;
else:
	?><font class="text">������ ������ �� ����������.</font><?
endif;
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");?>