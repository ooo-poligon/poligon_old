<!DOCTYPE HTML PUBLIC "-//IETF//DAREA HTML//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Windows-1251">
<style type="text/css">
.button{HEIGHT:20px; BACKGROUND-COLOR: highlight; COLOR:highlighttext;font-family: Arial, Helvetica, sans-serif; font-size:smaller;}
.buttonDown{HEIGHT:20px; BACKGROUND-COLOR: highlight; COLOR:highlighttext;font-family: Arial, Helvetica, sans-serif; font-size:smaller;}
.tb{HEIGHT:20px;font-family: Arial, Helvetica, sans-serif; font-size:smaller;}
</style>
</head>
<body style="BACKGROUND-COLOR: menu; BORDER-BOTTOM: threeddarkshadow solid 1px; BORDER-LEFT: threedface solid 1px; BORDER-RIGHT: threeddarkshadow solid 1px; BORDER-TOP: threedface solid 1px;" text="#000000" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onload="SetSize()">
<script>
<!--
function SetSize()
{
	//alert(document.body.clientWidth+", "+document.body.clientHeight);
	window.external.raiseEvent("setsize", Array(document.body.clientWidth+2, document.body.clientHeight+2));
}

function Cl(id)
{
	window.external.raiseEvent("OnTemplateSelect", id);
}
//-->
</script>
<?$arTemplates = CFileman::GetFileTemplates(LANG, Array($_GET['template']));?>
<table width="100%" id="t" cellspacing="0" cellpading="1" border="1"><tr><td>
<table width="100%" cellspacing="1" cellpading="2" border="0">
<?for($i=0; $i<count($arTemplates); $i++):?>
	<tr><td class="tb" align="left" nowrap
	onMouseOut="this.className='tb';"
	onMouseOver="this.className='button';"
	onMouseDown="this.className='buttonDown';"
	onMouseUp="this.className='button';"
	onClick="Cl('<?echo htmlspecialchars($arTemplates[$i]["file"])?>')"><?echo htmlspecialchars($arTemplates[$i]["name"])?>
	</td></tr>
<?endfor;?>
</table>
</td></tr></table>
</body>
</html>
