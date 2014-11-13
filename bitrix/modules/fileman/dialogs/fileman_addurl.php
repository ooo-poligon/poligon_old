<?
if(!defined("FROMDIALOGS") || FROMDIALOGS!==true)
	die("");
IncludeModuleLangFile(__FILE__);
?>
<style>
.tb{BORDER-BOTTOM: buttonface solid 1px;BORDER-LEFT: buttonface solid 1px;BORDER-RIGHT: buttonface solid 1px;BORDER-TOP: buttonface solid 1px;HEIGHT: 19px;WIDTH: 19px;}
.button{BORDER-BOTTOM: buttonshadow solid 1px; BORDER-LEFT: buttonhighlight solid 1px; BORDER-RIGHT: buttonshadow solid 1px; BORDER-TOP:  buttonhighlight solid 1px; HEIGHT: 19px; WIDTH: 19px;}
.buttonDown{BACKGROUND-COLOR: buttonface;BORDER-BOTTOM: buttonhighlight solid 1px;BORDER-LEFT: buttonshadow solid 1px;BORDER-RIGHT: buttonhighlight solid 1px;BORDER-TOP:  buttonshadow solid 1px; HEIGHT: 19px; WIDTH: 19px;}
</style>
<SCRIPT LANGUAGE=JavaScript FOR=window EVENT=onload>
<!--
url.value = window.dialogArguments["url"];
// -->
</SCRIPT>
<SCRIPT LANGUAGE=JavaScript FOR=Ok EVENT=onclick>
<!--
  var arr = new Array();
  arr["url"] = url.value;
  window.returnValue = arr;
  window.close();
// -->
</SCRIPT>

<script language="JavaScript">
<!--
function MkAdvUrl()
{
	var args = new Array();
	args["url"] = url.value;
	arr = showModalDialog("fileman_dialog.php?dtype=mkadvurl&lang=<?echo LANG?>", args, "dialogWidth:460px; dialogHeight:220px");
	if (arr != null)
		url.value = arr["url"];
}

function FilesBrowse()
{

	var args = new Array();
	args["filename"] = url.value;
	arr = showModalDialog("fileman_dialog.php?dtype=openfile&lang=<?echo LANG?>&path="+url.value, args, "dialogWidth:550px; dialogHeight:380px");
	if (arr != null)
		url.value = arr["filename"];
}
//-->
</script>

<TABLE CELLPADDING="0" align="center" border="0">
  <TR>
   <TD>URL:&nbsp;&nbsp;</td>
   <TD><input type="text" size="45" name="url" value=""></TD>
   <TD valign="middle"><img border="0" class="tb" onMouseOut="this.className='tb';" onMouseOver="this.className='button';" onMouseDown="this.className='buttonDown';"  onMouseUp="this.className='button';" src="/bitrix/images/fileman/htmledit/browse.gif" onclick="FilesBrowse();" alt="<?echo GetMessage("FILEMAN_ADDURL_FINDDOC")?>"></TD>
   <TD valign="middle"><img border="0" class="tb" onMouseOut="this.className='tb';" onMouseOver="this.className='button';" onMouseDown="this.className='buttonDown';"  onMouseUp="this.className='button';" src="/bitrix/images/fileman/htmledit/enc.gif" onclick="var s = document.selection.createRange();s.text = escape(s.text);" alt="<?echo GetMessage("FILEMAN_D_ADDURL_REPLACE")?>"></TD>
   <TD valign="middle"><img border="0" class="tb" onMouseOut="this.className='tb';" onMouseOver="this.className='button';" onMouseDown="this.className='buttonDown';"  onMouseUp="this.className='button';" src="/bitrix/images/fileman/htmledit/redirl.gif" onclick="MkAdvUrl()" alt="<?echo GetMessage("FILEMAN_D_ADDURL_TITLE")?>"></TD>
  </TR>
</TABLE>
<CENTER><br>
<BUTTON ID=Ok TYPE=SUBMIT>OK</BUTTON>&nbsp;<BUTTON ONCLICK="window.close();"><?=GetMessage("FILEMAN_CANCEL")?></BUTTON>
</CENTER>
