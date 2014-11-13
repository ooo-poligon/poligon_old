<?
if(!defined("FROMDIALOGS") || FROMDIALOGS!==true)
	die("");
IncludeModuleLangFile(__FILE__);
?>
<SCRIPT LANGUAGE=JavaScript FOR=window EVENT=onload>
<!--
fsrc.value = window.dialogArguments["src"];
falt.value = window.dialogArguments["alt"];
fborder.value = window.dialogArguments["border"];
fheight.value = window.dialogArguments["height"];
fwidth.value = window.dialogArguments["width"];
fhspace.value = window.dialogArguments["hspace"];
fvspace.value = window.dialogArguments["vspace"];
falign.value = window.dialogArguments["align"];
// -->
</SCRIPT>
<SCRIPT LANGUAGE=JavaScript FOR=Ok EVENT=onclick>
<!--
	var arr = new Array();
	arr["src"] = fsrc.value;
	arr["alt"] = falt.value;
	arr["border"] = fborder.value;
	arr["height"] = fheight.value;
	arr["width"] = fwidth.value;
	arr["hspace"] = fhspace.value;
	arr["vspace"] = fvspace.value;
	arr["align"] = falign.value;
	window.returnValue = arr;
	window.close();
// -->
</script>
<table cellspacing=10 align="center">
<tr>
    <td>SRC:</td>
    <td><input type="text" size="50" name="fsrc" value=""></td>
</tr>
<tr>
    <td>ALT:</td>
    <td><input type="text" size="50" name="falt" value=""></td>
</tr>
<tr>
    <td colspan="2">
		<table cellpadding="3">
		<tr>
			<td>Width:</td>
			<td><input type="text" size="3" name="fwidth" value=""></td>
			<td>&nbsp;&nbsp;</td>
			<td>HSpace:</td>
			<td><input type="text" size="3" name="fhspace" value=""></td>
			<td>&nbsp;&nbsp;</td>
			<td>Border:</td>
			<td><input type="text" size="3" name="fborder" value=""></td>
		</tr>
		<tr>
			<td>Height:</td>
			<td><input type="text" size="3" name="fheight" value=""></td>
			<td>&nbsp;&nbsp;</td>
			<td>VSpace:</td>
			<td><input type="text" size="3" name="fvspace" value=""></td>
			<td>&nbsp;&nbsp;</td>
			<td>Align:</td>
			<td><select name="falign"><option></option><option value="left">left</option><option value="middle">middle</option><option value="right">right</option><option value="top">top</option><option value="bottom">bottom</option><option value="absBottom">absBottom</option><option value="absMiddle">absMiddle</option></select></td>
		</tr>
		</table>
	</td>
</tr>
</table>
<div align="center">
<BUTTON ID=Ok TYPE=SUBMIT>OK</BUTTON>&nbsp;<BUTTON ONCLICK="window.close();"><?=GetMessage("FILEMAN_CANCEL")?></BUTTON>
</div>
