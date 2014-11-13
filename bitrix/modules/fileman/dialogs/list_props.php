<?
if (!defined("FROMDIALOGS") || FROMDIALOGS!==true)
	die("");
IncludeModuleLangFile(__FILE__);
$NEW_ROW_CNT = 1;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>">
<?echo '<link rel="stylesheet" type="text/css" href="/bitrix/themes/'.ADMIN_THEME_ID.'/compatible.css?'.SM_VERSION.'">'."\n";?>
<style>
BODY   {margin-left:10; font-family:Arial; font-size:12px; background:menu}
BUTTON {width:5em}
TABLE  {font-family:Arial; font-size:12px}
P      {text-align:center}
<?
if (strlen($APPLICATION->GetAdditionalCSS())>0)
{
	require($_SERVER["DOCUMENT_ROOT"].$APPLICATION->GetAdditionalCSS());
}
?>
</style>
</head>
<body leftmargin="0" topmargin="0">

<SCRIPT LANGUAGE=JavaScript FOR=More EVENT=onclick>
<!--
	var tbl = document.all("t");
	var cnt = tbl.rows.length;
	var oRow = tbl.insertRow(cnt);
	var oCell = oRow.insertCell();
	new_num = document.all("all_cnt_val").value;
	oCell.innerHTML = '<input type="text" name="CODE_'+new_num+'" value="" size="15">';
	var oCell = oRow.insertCell();
	oCell.innerHTML = '<input type="text" name="VALUE_'+new_num+'" value="" size="60">';
	document.all("all_cnt_val").value = 1 + new_num*1;
// -->
</SCRIPT>

	<table border="0" cellspacing="1" cellpadding="3" id="t">
		<tr>
			<td align="center"><font class="tableheadtext"><?echo GetMessage("FILEMAN_D_PROPSCODE")?></font></td>
			<td align="center"><font class="tableheadtext"><?echo GetMessage("FILEMAN_D_PROPSVAL")?></font></td>
		</tr>
		<SCRIPT LANGUAGE=JavaScript>
		<!--
		function htmlspecialchars(strPar)
		{
			strPar = strPar.replace(/&/i, "&amp;");
			strPar = strPar.replace(/\"/i, "&quot;");
			strPar = strPar.replace(/>/i, "&gt;");
			strPar = strPar.replace(/</i, "&lt;");
			return strPar;
		}

		arPropsTypes = new Array();
		arPropsTypesN = new Array();
		var strPropsTypes = parent.window.dialogArguments["props_types"];
		indt = -1;
		if (strPropsTypes.length > 0)
		{
			arMT = strPropsTypes.split(",");
			for (i = 0; i < arMT.length; i++)
			{
				arMI = arMT[i].split("=");
				indt++;
				arPropsTypes[indt] = arMI[0];
				arPropsTypesN[indt] = arMI[1];
				if (arPropsTypesN[indt].length<=0) arPropsTypesN[indt] = arMI[0];
			}
		}

		arPropsVals = new Array();
		i = 0;
		strProps = parent.window.dialogArguments["props"];
		if (strProps.length>0)
		{
			while (strProps.length>0)
			{
				pos = strProps.indexOf("#~@");
				if (pos<0) break;
				arPropsVals[i] = strProps.substr(0, pos);
				i = i + 1;
				strProps = strProps.substr(pos+3);
			}
			arPropsVals[i] = strProps;
		}

		i = 0;
		k = 0;
		while (i < arPropsVals.length)
		{
			// Let's delete from arPropsTypes keys which are already assigned
			bPredefinedProperty = 0;
			for (cind = 0; cind < arPropsTypes.length; cind++)
			{
				if (arPropsTypes[cind]==arPropsVals[i])
				{
					bPredefinedProperty = 1;
					PropertyName = arPropsTypesN[cind];
					if (cind < arPropsTypes.length-1)
					{
						for (cind1 = cind; cind1 < arPropsTypes.length-1; cind1++)
						{
							arPropsTypes[cind1] = arPropsTypes[cind1+1];
							arPropsTypesN[cind1] = arPropsTypesN[cind1+1];
						}
					}
					arPropsTypes.length = arPropsTypes.length - 1;
					arPropsTypesN.length = arPropsTypesN.length - 1;
					break;
				}
			}
			document.write("<tr><td>");
			if (bPredefinedProperty)
			{
				document.write("<input type=\"hidden\" name=\"CODE_"+k+"\" value=\""+htmlspecialchars(arPropsVals[i])+"\">");
				document.write("<font class=\"text\">"+PropertyName+"</font>");
				//document.write("<input type=\"text\" name=\"CODE_NAME_"+k+"\" value=\""+htmlspecialchars(PropertyName)+"\" size=\"15\" readonly style='background-color:menu;'>");
			}
			else
			{
				document.write("<input type=\"text\" name=\"CODE_"+k+"\" value=\""+htmlspecialchars(arPropsVals[i])+"\" size=\"15\">");
			}
			document.write("</td><td>");
			document.write("<input type=\"text\" name=\"VALUE_"+k+"\" value=\""+htmlspecialchars(arPropsVals[i+1])+"\" size=\"60\">");
			document.write("</td></tr>");
			i = i + 2;
			k = k + 1;
		}

		if (arPropsTypes.length)
		{
			for (j = 0; j < arPropsTypes.length; j++)
			{
				document.write("<tr><td>");
				document.write("<input type=\"hidden\" name=\"CODE_"+k+"\" value=\""+arPropsTypes[j]+"\">");
				//document.write("<input type=\"text\" name=\"CODE_NAME_"+k+"\" value=\""+htmlspecialchars(arPropsTypesN[j])+"\" size=\"15\" readonly style='background-color:menu;'>");
				document.write("<font class=\"text\">"+arPropsTypesN[j]+"</font>");
				document.write("</td><td>");
				document.write("<input type=\"text\" name=\"VALUE_"+k+"\" value=\"\" size=\"60\">");
				document.write("</td></tr>");
				k = k + 1;
			}
		}

		for (j = 1; j <= <?=$NEW_ROW_CNT?>; j++)
		{
			document.write("<tr><td>");
			document.write("<input type=\"text\" name=\"CODE_"+k+"\" value=\"\" size=\"15\">");
			document.write("</td><td>");
			document.write("<input type=\"text\" name=\"VALUE_"+k+"\" value=\"\" size=\"60\">");
			document.write("</td></tr>");
			k = k + 1;
		}
		document.write("<input type=\"hidden\" name=\"all_cnt_val\" value=\""+k+"\">");
		// -->
		</SCRIPT>
	</table>
	<table border="0" cellspacing="1" cellpadding="3" width="100%">
		<tr>
			<td align="right">
				<BUTTON ID="More" TYPE="button"><?echo GetMessage("FILEMAN_D_PROPSMORE")?></BUTTON>
			</TD>
		</TR>
	</TABLE>

</body>
</html>
