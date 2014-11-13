// ################################      BXFileDialog  javascript class    ############################### //
// PHP static class - /bitrix/modules/main/interface/admin_lib.php
// PHP & JS scripts - /bitrix/modules/main/tools/file_dialog_new
var BXFileDialog = function()
{
	this.name = 'BXFileDialog';
	this.height = 476;
	this.width = 750;
}

BXFileDialog.prototype.Open = function(oConfig, UserConfig)
{
	if (!oConfig || !UserConfig)
		return alert('Error: Wrong params!');
	this.oConfig = oConfig;
	this.UserConfig = UserConfig;
	this.LastSavedConfig =
	{
		site : this.UserConfig.site,
		path : this.UserConfig.path,
		view : this.UserConfig.view,
		sort : this.UserConfig.sort,
		sort_order : this.UserConfig.sort_order
	}

	__BXFileDialog = this;
	var div;
	var bCached = (window.fd_float_div_cached && this.CheckReConfig());
	if (bCached)
	{
		div = document.body.appendChild(window.fd_float_div_cached);
	}
	else
	{
		if(document.getElementById("BX_file_dialog"))
			this.Close();

		div = document.body.appendChild(document.createElement("DIV"));
		div.id = "BX_file_dialog";
		div.className = "editor_dialog";
		div.style.position = 'absolute';
		div.style.zIndex = 2100;
		div.style.overflow = 'hidden';

		div.innerHTML =
			'<div class="title">'+
			'<table cellspacing="0" width="100%" border="0">'+
			'	<tr>'+
			'		<td width="100%" class="title-text" onmousedown="jsFloatDiv.StartDrag(arguments[0], document.getElementById(\'BX_file_dialog\'));" id="BX_file_dialog_title">Title</td>'+
			'		<td width="0%"><a id="BX_file_dialog_close" class="close" href="javascript:__BXFileDialog.Close();" onclick="__BXFileDialog.Close(); return false;"></a></td></tr>'+
			'</table>'+
			'</div>'+
			'<div class="content">'+
			'</div>';
	}
	div.style.width = parseInt(this.width) + 'px';
	div.style.height = parseInt(this.height) + 'px';
	this.floatDiv = div;
	this.content = jsUtils.FindChildObject(this.floatDiv, 'div', 'content');

	oDialogTitle = document.getElementById('BX_editor_dialog_title');
	var ShowDialog = function(innerHTML)
	{
		CloseWaitWindow();
		if (innerHTML)
			__BXFileDialog.content.innerHTML = innerHTML;
		var w = jsUtils.GetWindowSize();
		var left = parseInt(w.scrollLeft + w.innerWidth / 2 - div.offsetWidth / 2);
		var top = parseInt(w.scrollTop + w.innerHeight / 2 - div.offsetHeight / 2);
		jsFloatDiv.Show(div, left, top);
	};
	ShowWaitWindow();
	if (bCached)
	{
		this.reConfigDialog();
		return ShowDialog();
	}

	CHttpRequest.Action = ShowDialog;
	CHttpRequest.Send('/bitrix/admin/file_dialog.php?lang='+this.oConfig.lang+'&site='+this.oConfig.site+'&path='+this.oConfig.path);
}

BXFileDialog.prototype.CheckReConfig = function()
{
	if (
		jsUtils.IsIE() || 
		this.oConfig.operation != window.fd_config_cached.operation || 
		this.oConfig.allowAllFiles != window.fd_config_cached.allowAllFiles || 
		this.oConfig.select != window.fd_config_cached.select || 
		this.oConfig.lang != window.fd_config_cached.lang || 
		this.oConfig.showAddToMenuTab != window.fd_config_cached.showAddToMenuTab || 
		this.oConfig.showUploadTab != window.fd_config_cached.showUploadTab || 
		this.oConfig.site != window.fd_config_cached.site
	)
		return false;
	return true;
}

BXFileDialog.prototype.reConfigDialog = function()
{
	if (this.oConfig.fileFilter != window.fd_config_cached.fileFilter)
		oBXDialogControls.Filter = new __FileFilter();
	var path = this.oConfig.path || this.UserConfig.path || '';
	oBXFileDialog.SubmitFileDialog = SubmitFileDialog;
	oBXDialogWindow.loadFolderContent(path);
	oBXDialogTree.focusOnSelectedElment();
}

BXFileDialog.prototype.Close = function()
{
	if (oBXFDContextMenu)
		oBXFDContextMenu.menu.PopupHide();
	var oDiv = document.getElementById("BX_file_dialog");
	jsFloatDiv.Close(oDiv);
	jsFloatDiv.Close(this.floatDiv);
	oDiv.parentNode.removeChild(oDiv);
	window.fd_float_div_cached = this.floatDiv;
	window.fd_config_cached = this.oConfig;
	if (window.fd_site_list && window.fd_site_list.PopupHide)
		window.fd_site_list.PopupHide();
}

