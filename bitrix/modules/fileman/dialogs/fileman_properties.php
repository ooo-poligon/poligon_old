<?
if (!defined("FROMDIALOGS") || FROMDIALOGS!==true)
	die("");
IncludeModuleLangFile(__FILE__);
?>
<SCRIPT LANGUAGE=JavaScript FOR=Ok EVENT=onclick>
<!--
	var arr = new Array();

	re = /#~@/i;

	arr["props"] = "";
	for (ii = 0; ii < propslist.document.all("all_cnt_val").value; ii++)
	{
		if (propslist.document.all("CODE_"+ii).value.length>0)
		{
			if (arr["props"].length>0) arr["props"] = arr["props"] + "#~@";
			arr["props"] = arr["props"] + propslist.document.all("CODE_"+ii).value;
			arr["props"] = arr["props"] + "#~@";
			arr["props"] = arr["props"] + propslist.document.all("VALUE_"+ii).value;
		}
	}

	window.returnValue = arr;
	window.close();
// -->
</SCRIPT>

<SCRIPT LANGUAGE=JavaScript>
<!--
	function rs()
	{
		t.height = parseInt(window.dialogHeight) - 80;
	}
	window.onresize=rs;
// -->
</SCRIPT>

<TABLE CELLSPACING="5" id="t" align="center" width="100%" height="270">
  <TR>
    <TD width="100%">
		<iframe name="propslist" src="fileman_dialog.php?dtype=list_props&lang=<?=LANGUAGE_ID?>" style="width:100%;height:100%" height="300"></iframe>
	</TD>
	</TR>
</TABLE>
<br>
<CENTER>
<BUTTON ID=Ok TYPE=SUBMIT>OK</BUTTON>&nbsp;<BUTTON ONCLICK="window.close();"><?=GetMessage("FILEMAN_CANCEL")?></BUTTON>
</CENTER>
