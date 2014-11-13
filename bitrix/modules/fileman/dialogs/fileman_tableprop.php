<?
if(!defined("FROMDIALOGS") || FROMDIALOGS!==true)
	die("");
IncludeModuleLangFile(__FILE__);
?>
<SCRIPT LANGUAGE=JavaScript FOR=window EVENT=onload>
<!--
	fborder.value = window.dialogArguments["border"];
	fwidth.value = window.dialogArguments["width"];
	fcellpadding.value = window.dialogArguments["cellpadding"];
	fcellspacing.value = window.dialogArguments["cellspacing"];
	falign.value = window.dialogArguments["align"];
// -->
</SCRIPT>
<SCRIPT LANGUAGE=JavaScript FOR=Ok EVENT=onclick>
<!--
	var arr = new Array();
	arr["border"] = fborder.value;
	arr["width"] = fwidth.value;
	arr["cellpadding"] = fcellpadding.value;
	arr["cellspacing"] = fcellspacing.value;
	arr["align"] = falign.value;
	window.returnValue = arr;
	window.close();
// -->
</script>
<table cellspacing=10 align="center">
<tr>
	<td>Width:</td>
	<td><input type="text" size="6" name="fwidth" value=""></td>
	<td>&nbsp;&nbsp;</td>
	<td>Cellpadding:</td>
	<td><input type="text" size="3" name="fcellpadding" value=""></td>
	<td>&nbsp;&nbsp;</td>
	<td>Border:</td>
	<td><input type="text" size="3" name="fborder" value=""></td>
</tr>
<tr>
	<td>Align:</td>
	<td><select name="falign"><option></option><option value="left">left</option><option value="center">center</option><option value="right">right</option></select></td>
	<td>&nbsp;&nbsp;</td>
	<td>Cellspacing:</td>
	<td><input type="text" size="3" name="fcellspacing" value=""></td>
	<td>&nbsp;&nbsp;</td>
	<td>&nbsp;&nbsp;</td>
	<td>&nbsp;&nbsp;</td>
</tr>
</table>
<div align="center">
<BUTTON ID=Ok TYPE=SUBMIT>OK</BUTTON>&nbsp;<BUTTON ONCLICK="window.close();"><?=GetMessage("FILEMAN_CANCEL")?></BUTTON>
</div>
