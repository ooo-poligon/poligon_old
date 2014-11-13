<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!CModule::IncludeModule("forum")):
	LocalRedirect("index.php");
	die();
endif;


$APPLICATION->SetTitle("����� �� ������");
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
				<td class="forumbodynoborder"><font class="forumheadtext">�������� �����:</font></td>
				<td class="forumbodynoborder"><font class="forumbodynobordertext">
					<input type="text" name="q" value="<?echo htmlspecialchars($q)?>" size="40">
				</font></td>
			</tr>
			<tr>
				<td class="forumbodynoborder"><font class="forumheadtext">������ � ������:</font></td>
				<td class="forumbodynoborder"><font class="forumbodynobordertext">
					<select name="FORUM_ID">
						<option value="0">(�� ���� �������)</option>
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
				<input type="submit" name="s" value="�����">
			</font></td></tr>
	</form>
		</table>

<?if($HELP <> "Y"):?>
<font style="font-size:8px;">&nbsp;<br></font>
<table border="0" cellspacing="0" cellpadding="0">
<tr valign="top">
	<td><img src="/bitrix/images/forum/arr.gif" width="4" height="7" border="0" alt="" hspace="0" vspace="5"></td>
	<td><font class="forumbodytext">&nbsp;</font></td>
	<td><font class="forumbodytext"><a href="search.php?HELP=Y">������ �� ������</a></font></td>
</tr>
</table>
<?else:?>
<p class="text">
<b>��������� ���������� �������</b><br><br>

������ ������ ������������ �� ���� ������ ���� ��� ��������� ����, 
��������: <br><br>

<i>���������� ����������</i><br><br>
 
�� ������ ������� ����� ������� ��������, �� ������� ����������� ��� ����� �������. <br><br> 

���������� ��������� ��������� ������� ����� ������� �������, ��������: <br><br>

<i>���������� ���������� ��� �������</i><br><br>

�� ������ ������� ����� ������� ��������, �� ������� ����������� ���� ����� 
&quot;����������&quot; � &quot;����������&quot;, ���� ����� 
&quot;�������&quot;.<br><br> 

<i>���������� ���������� �� �������</i><br><br>
 
�� ������ ������� ����� ������� ��������, �� ������� ����������� ���� 
����� &quot;����������&quot; � &quot;����������&quot;, �� �� ����������� 
����� &quot;�������&quot;.<br><br>
 
�� ������ ������������ ������ ��� 
���������� ����� ������� ��������.<br><br> 

<b>���������� ���������</b> 
</p>
<table border="0" cellspacing="0" cellpadding="5">
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

<?endif; //HELP?>
	
<?
	if(strlen($q)>0):
		$FORUM_ID = IntVal($FORUM_ID);
		if ($FORUM_ID<=0) $FORUM_ID = false;
		$obSearch = new CSearch($q, LANG, "forum", false, $FORUM_ID);
		if ($obSearch->errorno!=0):
			?>
			<p class="text">� ��������� ����� ���������� ������:</p> 
			<?echo ShowError($obSearch->error);?>
			<p class="text">��������� ��������� ����� � ��������� �����.</p>
			<?
		else:
			$obSearch->NavStart(20, false);
			?>
			<p><?$obSearch->NavPrint("���������� ������");?></p>
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

			<p><?$obSearch->NavPrint("���������� ������");?></p>
			<?
			if ($bEmptyFlag)
			{
				?>
				<p class="text">
				�� ��� ������ ������ �� �������. ���������� ����������������� ������.
				</p>
				<?
			}
		endif;
	endif;
else:
	?><font class="text">������ ������ �� ����������.</font><?
endif;
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");?>