<?
define("NEED_AUTH", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!CModule::IncludeModule("forum") || !$USER->IsAuthorized())
{
	LocalRedirect("index.php");
	die();
}

ForumSetLastVisit();
define("FORUM_MODULE_PAGE", "SUBSCRIPTION");
$UID = IntVal($UID);
if (!$USER->IsAdmin() || $UID<=0)
{
	$UID = IntVal($USER->GetParam("USER_ID"));
}

$bUserFound = False;
$db_userX = CUser::GetByID($UID);
if ($db_userX->ExtractFields("f_", True))
{
	$bUserFound = True;
}


$strErrorMessage = "";
$strOKMessage = "";
$bVarsFromForm = false;

$ID = IntVal($ID);
if ($REQUEST_METHOD=="GET" && $ACTION=="DEL" && $ID>0)
{
	CForumSubscribe::Delete($ID);
}

$APPLICATION->AddChainItem("Profile", "profile.php");
$APPLICATION->SetTitle("Subscription list");
$APPLICATION->SetAdditionalCSS("/bitrix/php_interface/".LANG."/forum.css");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");

$path2curdir = str_replace("\\\\", "/", dirname(__FILE__)."/");
if (file_exists($path2curdir."menu.php"))
	include($path2curdir."menu.php");
elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/".LANG."/menu.php"))
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/".LANG."/menu.php");
else
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/install/public/en/menu.php");

if (!$bUserFound)
{
	$strErrorMessage .= "User #$UID is not found. \n";
}
?>

<?echo ShowMessage(array("MESSAGE" => $strErrorMessage, "TYPE" => "ERROR"));?>
<?echo ShowMessage(array("MESSAGE" => $strOKMessage, "TYPE" => "OK"));?>

<?
$db_res = CForumSubscribe::GetList(array("FORUM_ID"=>"ASC", "TOPIC_ID"=>"ASC", "START_DATE"=>"ASC"), array("USER_ID"=>$UID));
?>
<form action="<?echo $APPLICATION->GetCurPage();?>" method="post">

<font class="text">
<?
$FID = IntVal($FID);
$TID = IntVal($TID);
if ($TID>0)
{
	?><a href="read.php?FID=<?echo $FID?>&TID=<?echo $TID?>">Back</a><?
}
elseif ($FID>0)
{
	?><a href="list.php?FID=<?echo $FID?>">Back</a><?
}
?>
</font><br><br>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="forumborder"><tr><td>
<table border="0" cellpadding="1" cellspacing="1" width="100%">
	<tr>
		<td class="forumhead" align="center">
			<font class="forumheadtext"><b>Forum title</b></font>
		</td>
		<td class="forumhead" align="center">
			<font class="forumheadtext"><b>Topic title</b></font>
		</td>
		<td class="forumhead" align="center">
			<font class="forumheadtext"><b>Date Subscribed</b></font>
		</td>
		<td class="forumhead" align="center">
			<font class="forumheadtext"><b>Last sent message</b></font>
		</td>
		<td class="forumhead" align="center">
			<font class="forumheadtext"><b>Actions</b></font>
		</td>
	</tr>
	<?
	while ($res = $db_res->Fetch()):
		$arForum_tmp = CForumNew::GetByID($res["FORUM_ID"]);
		$arTopic_tmp = CForumTopic::GetByID($res["TOPIC_ID"]);
		?>
		<tr>
			<td class="forumbody">
				<font class="forumbodytext"><a href="list.php?FID=<?echo $res["FORUM_ID"];?>"><?echo $arForum_tmp["NAME"];?></a></font>
			</td>
			<td class="forumbody">
				<font class="forumbodytext">
					<?
					if (IntVal($res["TOPIC_ID"])>0)
					{
						echo "<a href=\"read.php?FID=".$res["FORUM_ID"]."&TID=".$res["TOPIC_ID"]."\">".$arTopic_tmp["TITLE"]."</a>";
					}
					else
					{
						echo "All topics";
					}
					?>
				</font>
			</td>
			<td class="forumbody">
				<font class="forumbodytext"><?echo $res["START_DATE"];?></font>
			</td>
			<td class="forumbody" align="center">
				<font class="forumbodytext">
				<?if (IntVal($res["LAST_SEND"])>0):?>
					<a href="read.php?MID=<?echo $res["LAST_SEND"];?>#message<?echo $res["LAST_SEND"];?>">Here</a>
				<?endif;?></font>
			</td>
			<td class="forumbody">
				<font class="forumbodytext"><a href="subscr_list.php?ID=<?echo $res["ID"];?>&ACTION=DEL">Delete</a></font>
			</td>
		</tr>
	<?endwhile;?>
</table>
<td><tr></table>

</form>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog.php");
?>