arButtons['new']	= ['BXButton',
	{
		id : 'new',
		iconkit : '_global_iconkit.gif',
		codeEditorMode : true,
		name : BX_MESS.TBNewPage,
		handler : function ()
		{
			var pos = jsUtils.GetRealPos(this.pWnd);
			setTimeout(function(){new_doc_list.PopupShow(pos); document.getElementById("new_doc_list").style.zIndex = 2100;}, 10);
		}
	}
];

arButtons['save_and_exit'] = ['BXButton',
	{
		id : 'save_and_exit',
		iconkit : '_global_iconkit.gif',
		codeEditorMode : true,
		name : BX_MESS.TBSaveExit,
		title : BX_MESS.TBSaveExit,
		show_name : true,
		handler : function ()
		{
			if(_bEdit)
			{
				this.pMainObj.SaveContent(true);
				this.pMainObj.isSubmited = true;
				this.pMainObj.pForm.submit();
			}
			else
			{
				__bx_fd_save_as();
			}
		}
	}
];

arButtons['exit'] = ['BXButton',
	{
		id : 'exit',
		iconkit : '_global_iconkit.gif',
		codeEditorMode : true,
		name : BX_MESS.TBExit,
		handler : function ()
		{
			this.pMainObj.OnEvent("OnSelectionChange");
			var need_to_ask = (pBXEventDispatcher.arEditors[0].IsChanged() && !pBXEventDispatcher.arEditors[0].isSubmited);

			if(need_to_ask)
			{
				this.bNotFocus = true;
				this.pMainObj.OpenEditorDialog("asksave", false, 600, {window: window, savetype: _bEdit ? 'save' : 'saveas'}, true);
			}
			else if(this.pMainObj.arConfig["sBackUrl"])
				window.location = this.pMainObj.arConfig["sBackUrl"];
		}
	}
];

arButtons['saveas'] = ['BXButton',
	{
		id : 'saveas',
		iconkit : '_global_iconkit.gif',
		codeEditorMode : true,
		name : BX_MESS.TBSaveAs,
		handler : function ()
		{
			this.bNotFocus = true;
			apply = true;
			__bx_fd_save_as();
		}
	}
];


arButtons['save'] = ['BXButton',
	{
		id : 'save',
		iconkit : '_global_iconkit.gif',
		codeEditorMode : true,
		name : BX_MESS.TBSave,
		handler : function ()
		{
			if(!_bEdit)
			{
				this.bNotFocus = true;
				apply = true;
				__bx_fd_save_as();
			}
			else
			{
				this.pMainObj.SaveContent(true);
				document.getElementById("apply2").value = 'Y';
				this.pMainObj.isSubmited = true;
				this.pMainObj.pForm.submit();
			}
		}
	}
];

arButtons['pageprops'] = ['BXButton',
	{
		id : 'pageprops',
		iconkit : '_global_iconkit.gif',
		codeEditorMode : true,
		name : BX_MESS.TBProps,
		handler : function ()
		{
			this.bNotFocus = true;
			this.pMainObj.OpenEditorDialog("pageprops", false, 600, {window: window, document: document});
		}
	}
];

arToolbars['manage'] = [FE_MESS.FILEMAN_HTMLED_MANAGE_TB, [arButtons['save_and_exit'], arButtons['exit'], arButtons['new'], arButtons['save'], arButtons['saveas'], arButtons['pageprops']]];

arDefaultTBPositions['manage'] = [0, 0, 2];

window.onbeforeunload = function(e)
{
	try{
		var need_to_ask = (pBXEventDispatcher.arEditors[0].IsChanged() && !pBXEventDispatcher.arEditors[0].isSubmited);
		if (need_to_ask)
			return BX_MESS.ExitConfirm;
	} catch(e){}
}

arEditorFastDialogs['asksave'] = function(pObj)
{
	var str = '<table height="100%" width="100%" id="t1" border="0">' +
	'<tr>' +
		'<td colspan="3">' +
			'<table height="100%" width="100%" id="t1" border="0" style="font-size:14px;">' +
			'<tr>' +
			'<td></td>' +
			'<td>' + BX_MESS.DIALOG_EXIT_ACHTUNG + '</td>' +
			'</tr>' +
			'</table>' +
		'</td>' +
	'</tr>' +
	'<tr id="buttonsSec" valign="top">' +
		'<td align="center"><input style="font-size:12px; height: 25px; width: 180px;" type="button" id="asksave_b1" value="' + BX_MESS.TBSaveExit + '"></td>' +
		'<td align="center"><input style="font-size:12px; height: 25px; width: 180px;" type="button" id="asksave_b2" value="' + BX_MESS.DIALOG_EXIT_BUT + '"></td>' +
		'<td align="center"><input style="font-size:12px; height: 25px; width: 180px;" type="button" id="asksave_b3" value="' + BX_MESS.DIALOG_EDIT_BUT + '"></td>' +
	'</tr>' +
'</table>';

	var OnClose = function(){pObj.Close();};
	var OnSave = function(t)
	{
		pObj.pMainObj.isSubmited = true;
		if(pObj.params.savetype == 'saveas')
		{
			pObj.params.window.__bx_fd_save_as();
		}
		else
		{
			pObj.pMainObj.SaveContent(true);
			pObj.pMainObj.pForm.submit();
		}
		OnClose();
	};
	var OnExit = function(t)
	{
		pObj.pMainObj.isSubmited = true;
		if(pObj.pMainObj.arConfig["sBackUrl"])
			pObj.params.window.location = pObj.pMainObj.arConfig["sBackUrl"];
		OnClose();
	};

	return {
		title: BX_MESS.EDITOR,
		innerHTML : str,
		OnLoad: function()
		{
			document.getElementById("asksave_b1").focus();
			document.getElementById("asksave_b1").onclick = OnSave;
			document.getElementById("asksave_b2").onclick = OnExit;
			document.getElementById("asksave_b3").onclick = OnClose;
		}
	};
}