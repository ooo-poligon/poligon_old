<?
if(!defined("FROMDIALOGS") || FROMDIALOGS!==true)
	die("");
IncludeModuleLangFile(__FILE__);
?>
<SCRIPT LANGUAGE=JavaScript FOR=window EVENT=onload>
<!--
title.value = window.dialogArguments["title"];
// -->
</SCRIPT>
<SCRIPT LANGUAGE=JavaScript FOR=Ok EVENT=onclick>
<!--
  var arr = new Array();
  arr["title"] = document.all("title").value;
  window.returnValue = arr;
  window.close();
// -->
</SCRIPT>
<TABLE CELLSPACING=7 align="center" width="100%">
  <TR>
    <TD width="0%" nowrap><?echo GetMessage("FILEMAN_D_TITLE")?></td>
    <TD width="100%">
      <input type="text" style="width:100%;" name="title" value="">
	</TD>
	</TR>
</TABLE>
<CENTER>
<BUTTON ID=Ok TYPE=SUBMIT>OK</BUTTON>&nbsp;<BUTTON ONCLICK="window.close();"><?=GetMessage("FILEMAN_CANCEL")?></BUTTON>
</CENTER>
