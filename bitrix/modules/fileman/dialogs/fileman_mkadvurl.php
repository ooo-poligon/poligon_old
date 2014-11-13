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
var url = window.dialogArguments["url"];
function FndParam(str)
{
	var p1 = url.indexOf(str+"=");
	if(p1<=0)
		return "";
	var p2 = url.indexOf("&", p1);
	if(p2<=0)
		p2 = url.length;
	return unescape(url.substring(p1+str.length+1, p2));
}
event1.value = FndParam("event1");
event2.value = FndParam("event2");
event3.value = FndParam("event3");
goto.value = FndParam("goto");
// -->
</SCRIPT>
<SCRIPT LANGUAGE=JavaScript FOR=Ok EVENT=onclick>
<!--
  var arr = new Array();
  arr["url"] = "/bitrix/redirect.php?event1="+escape(event1.value)+"&event2="+escape(event2.value)+"&event3="+escape(event3.value)+"&goto="+escape(goto.value);
  window.returnValue = arr;
  window.close();
// -->
</SCRIPT>
<TABLE CELLPADDING=3 align="center" border="0">
  <TR>
    <TD>Event1:</td>
    <TD><input type="text" size="20" name="event1" value=""></TD>
  </TR>
  <TR>
    <TD>Event2:</td>
    <TD><input type="text" size="20" name="event2" value=""></TD>
  </TR>
  <TR>
    <TD>Event3:</td>
    <TD><input type="text" size="20" name="event3" value=""></TD>
  </TR>
  <TR>
    <TD><?echo GetMessage("FILEMAN_D_MKADVURL_EXT_LINK")?></td>
    <TD><input type="text" size="30" name="goto" value=""></TD>
  </TR>
</TABLE>
<CENTER><br>

<BUTTON ID=Ok TYPE=SUBMIT>OK</BUTTON>&nbsp;<BUTTON ONCLICK="window.close();"><?=GetMessage("FILEMAN_CANCEL")?></BUTTON>
</CENTER>
