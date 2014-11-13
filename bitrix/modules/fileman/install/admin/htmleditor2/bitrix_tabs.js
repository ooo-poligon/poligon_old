window.bBitrixTabs = false;
arButtons['tabsection'] = ['BXButton',
	{
		id : 'tabsection',
		codeEditorMode : false,
		src : '/bitrix/images/fileman/htmledit2/insert_tabsection.gif',
		name : 'Вставить область закладок',
		handler : function ()
		{
			this.bNotFocus = true;
			this.pMainObj.insertHTML(
				'<img src="/bitrix/images/1.gif" style="background-image: url(/bitrix/images/fileman/htmledit2/tab_section_begin.gif); height: 17px; width: 100%" __bxtagname="begin_tabsection" __bxcontainer="" /><div _moz_editor_bogus_node="on"></div>' +
				'<br />' +
				'<br />' +
				'<img src="/bitrix/images/1.gif" style="background-image: url(/bitrix/images/fileman/htmledit2/tab_section_end.gif); height: 17px; width: 100%" __bxtagname="end_tabsection" __bxcontainer="" /><div _moz_editor_bogus_node="on"></div>'
			);
			window.bBitrixTabs = true;
		}
	}
];
arButtons['tab'] = ['BXButton',
	{
		id : 'tab',
		codeEditorMode : false,
		src : '/bitrix/images/fileman/htmledit2/insert_tab.gif',
		name : 'Вставить закладку',
		handler : function ()
		{
			this.bNotFocus = true;
			this.pMainObj.OpenEditorDialog("tab", false, 400, {window: window, document: document});
		},
		OnSelectionChange: function ()
		{
			if (!window.bBitrixTabs)
				return this.Disable(true);

			var oRange = BXGetSelectionRange(this.pMainObj.pEditorDocument, this.pMainObj.pEditorWindow);
			var currentElement = this.pMainObj.GetSelectionObject();
			this.Disable(!isInTabSection(currentElement, 'begin_tabsection', 'end_tabsection'));
		}
	}
];

if (!window.lightMode)
{
	oBXEditorUtils.appendButton('tabsection', arButtons['tabsection'], 'standart');
	oBXEditorUtils.appendButton('tab', arButtons['tab'], 'standart');
}
else
{
	for(var bxi = 0, bxl = arGlobalToolbar.length; bxi < bxl; bxi++)
	{
		if (arGlobalToolbar[bxi +1] == 'line_end')
			break;
	}
	arGlobalToolbar = arGlobalToolbar.slice(0, bxi).concat([arButtons['tabsection'], arButtons['tab']], arGlobalToolbar.slice(bxi + 1));
}

arEditorFastDialogs['tab'] = function(pObj)
{
	var str = '<table height="100%" width="100%" border="0" style="margin-top:10px">' +
	'<tr>' +
		'<td align="right">' +
			'Идентификатор закладки' + ':' +
		'</td>' +
		'<td>' +
			'<input id="bx_fd_tab_id">' +
		'</td>' +
	'</tr>' +
	'<tr>' +
		'<td align="right">' +
			'Название закладки' + ':' +
		'</td>' +
		'<td>' +
			'<input id="bx_fd_tab_name">' +
		'</td>' +
	'</tr>' +
	'<tr valign="top">' +
		'<td align="right" valign="middle" style="height:40px"><input type="button" id="bx_tabsection_save" value="' + BX_MESS.TBSave + '"></td>' +
		'<td align="left" valign="middle" style="height:40px"><input type="button" id="bx_tabsection_close" value="' + BX_MESS.TBCancel + '"></td>' +
	'</tr>' +
'</table>';
	var OnClose = function(){pObj.Close();};
	var OnSave = function(t)
	{
		var pTId = document.getElementById("bx_fd_tab_id");
		var pTName = document.getElementById("bx_fd_tab_name");
		var id = pTId.value || '';
		var name = pTName.value || '';
		if (name.length <= 0)
			return alert('Поле название закладки не может быть пустым');
		BXSelectRange(oPrevRange, pObj.pMainObj.pEditorDocument, pObj.pMainObj.pEditorWindow);
		pObj.pMainObj.insertHTML('<img src="/bitrix/images/1.gif" style="background-image: url(/bitrix/images/fileman/htmledit2/tab.gif); height: 20px; width: 100%" __bxtagname="tab" __bxcontainer="'+bxhtmlspecialchars(BXSerialize({name : name, id : id}))+'" /><div _moz_editor_bogus_node="on"></div>');
		
		OnClose();
	};

	return {
		title: "Вставить закладку",
		innerHTML : str,
		OnLoad: function()
		{
			window.oPrevRange = BXGetSelectionRange(pObj.pMainObj.pEditorDocument, pObj.pMainObj.pEditorWindow);
			var tn = document.getElementById("bx_fd_tab_name");
			tn.focus();
			var bs = document.getElementById("bx_tabsection_save");
			bs.onclick = OnSave;
			document.getElementById("bx_tabsection_close").onclick = OnClose;
		}
	};
}


function BitrixRU_ContentParser(str)
{
	window.bBitrixTabs = false;
	str = str.replace(/<tabsection>/ig, function(str){
		window.bBitrixTabs = true;
		return '<img src="/bitrix/images/1.gif" style="background-image: url(/bitrix/images/fileman/htmledit2/tab_section_begin.gif); height: 17px; width: 100%; display:block;" __bxtagname="begin_tabsection" __bxcontainer="" /><div _moz_editor_bogus_node="on"></div>';
	});
	str = str.replace(/<\/tabsection>/ig, function(str)
		{
			return '<img src="/bitrix/images/1.gif" style="background-image: url(/bitrix/images/fileman/htmledit2/tab_section_end.gif); height: 17px; width: 100%" __bxtagname="end_tabsection" __bxcontainer="" /><div _moz_editor_bogus_node="on"></div>';
		}
	);
	str = str.replace(/<tab\s{1}(?:\s|\S)*?>/ig, function(str, b1)
		{
			var id = '';
			var name = '';
			str = str.replace(/id\s*=\s*("|')((?:\s|\S)*?)\1/i, function(str, b1, b2_id){id = b2_id; return '';});
			str = str.replace(/name\s*=\s*("|')((?:\s|\S)*?)\1/i, function(str, b1, b2_name){name = b2_name; return '';});
			return '<img src="/bitrix/images/1.gif" style="background-image: url(/bitrix/images/fileman/htmledit2/tab.gif); height: 20px; width: 100%" __bxtagname="tab" __bxcontainer="'+bxhtmlspecialchars(BXSerialize({name : name, id : id}))+'" /><div _moz_editor_bogus_node="on"></div>';
		}
	);
	return str;
}
oBXEditorUtils.addContentParser(BitrixRU_ContentParser);

function BitrixRU_UnParser(node)
{
	if (node.arAttributes["__bxtagname"] == 'begin_tabsection')
	{
		return '<tabsection>';
	}
	else if (node.arAttributes["__bxtagname"] == 'end_tabsection')
	{
		return '</tabsection>';
	}
	else if (node.arAttributes["__bxtagname"] == 'tab')
	{
		var par = BXUnSerialize(node.arAttributes["__bxcontainer"]);
		var _id = par.id ? ' id="' + par.id + '"' : '';
		var _name = ' name="' + (par.name || 'BXTab') + '"';
		return '<tab' + _id + _name + '>';
	}
	return false;
}
oBXEditorUtils.addUnParser(BitrixRU_UnParser);

pPropertybarHandlers['tab'] = function (bNew, pTaskbar, pElement)
{
	pTaskbar.pHtmlElement = pElement;
	if(bNew)
	{
		pTaskbar.arElements = [];
		var tProp;
		var arBarHandlersCache = pTaskbar.pMainObj.arBarHandlersCache;
		if(arBarHandlersCache['tab'])
		{
			tProp = arBarHandlersCache['tab'][0];
			pTaskbar.arElements = arBarHandlersCache['tab'][1];
		}
		else
		{
			tProp = pTaskbar.pMainObj.CreateElement("TABLE", {className: "bxtaskbarprops", cellSpacing: 0, cellPadding: 1}, {width: '100%'});
			var row, cell;

			row = tProp.insertRow(-1); cell = row.insertCell(-1); cell.align = 'right'; cell.width="40%";
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {innerHTML: 'Идентификатор закладки:'}));

			cell = row.insertCell(-1); cell.width="60%";
			pTaskbar.arElements.id = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {size: '40'}));

			row = tProp.insertRow(-1); cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {innerHTML: 'Название закладки:'}));
			cell = row.insertCell(-1);
			pTaskbar.arElements.name = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {size: '40'}));
			arBarHandlersCache['tab'] = [tProp, pTaskbar.arElements];
		}
		pTaskbar.pCellProps.appendChild(tProp);
	}

	var val = BXUnSerialize(pElement.getAttribute("__bxcontainer"));
	pTaskbar.arElements.id.value = val.id;
	pTaskbar.arElements.name.value = val.name;

	var fChange = function(){pElement.setAttribute("__bxcontainer", BXSerialize({name: pTaskbar.arElements.name.value, id: pTaskbar.arElements.id.value}));};

	pTaskbar.arElements.id.onchange = fChange;
	pTaskbar.arElements.name.onchange = fChange;
};

function isInTabSection(el)
{
	var i = -1, tn;
	tn = (el && el.getAttribute) ? el.getAttribute("__bxtagname") : '';
	if (tn == 'begin_tabsection' || tn == 'end_tabsection')
		return false;
	if (el && el.nodeName && el.nodeName.toUpperCase() == 'TD')
		el = el.lastChild;
	while (el && el.nodeName && el.nodeName.toUpperCase() != 'BODY' && i++ <= 500)
	{
		el = el.previousSibling || el.parentNode;
		if (el.nodeName.toUpperCase() == 'IMG' && el.getAttribute)
		{
			tn = el.getAttribute("__bxtagname");
			if (tn == 'begin_tabsection' || tn == 'tab')
				return true;
			else if (tn == 'end_tabsection')
				return false;
		}
	}
	return false;
}
