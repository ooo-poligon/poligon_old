var snippets_js = true;
// ========================
window.arSnippets = {};
// ========================

function BXSnippetsTaskbar()
{
	var pDataCell;
	var pListCell;
	var oTaskbar = this;
	oTaskbar._arSnippets = [];
	BXSnippetsTaskbar.prototype.OnTaskbarCreate = function ()
	{
		this.icon_class = 'tb_icon_snippets';
		this.iconDiv.className = 'tb_icon ' + this.icon_class;
		this.cellTitle.setAttribute("__bxtagname", "_taskbar_cached");
		this.oTaskbar = ar_BXTaskbarS["BXSnippetsTaskbar_"+this.pMainObj.name];
		this.oTaskbar.pDataCell.style.padding = "0px";
		this.oTaskbar.pCellSnipp = this.oTaskbar.CreateScrollableArea(this.oTaskbar.pDataCell);

		try
		{
			this.loadSnippets();
		}
		catch(e) { _alert('BXSnippetsTaskbar: OnTaskbarCreate');}
	};

	BXSnippetsTaskbar.prototype.loadSnippets = function(clear_cache)
	{
		var _this = this;
		var onload = function()
		{
			_this.BuildList(window.arSnippets);
			oTaskbar.pMainObj.oBXWaitWindow.Hide();
		};

		var ls_CHttpRequest = new JCHttpRequest();
		ls_CHttpRequest.Action = function(result){try{setTimeout(onload, 5);}catch(e){_alert('error: loadSnippets');};};

		try
		{
			ls_CHttpRequest.Send('/bitrix/admin/fileman_manage_snippets.php?lang='+BXLang+'&site='+BXSite+'&templateID='+oTaskbar.pMainObj.templateID+'&target=load'+(clear_cache === true ? '&clear_snippets_cache=Y' : ''));
		}
		catch(e)
		{
			onload();
		}
	};


	BXSnippetsTaskbar.prototype.BuildList = function (__arElements)
	{
		this.oTaskbar.pMainObj.arSnippetsCodes = [];
		var _this = this, oEl, i, l, _path;
		this.__arGroups = {};
		var addGroup = function(name,path)
		{
			_this.oTaskbar.AddElement({name:name,tagname:'',isGroup:true,childElements:[],icon:'',path:path,code:''},_this.oTaskbar.pCellSnipp,path);
		};

		for (var key in __arElements)
		{
			oEl = __arElements[key];
			if (oEl.path != '')
			{
				arPath = oEl.path.split("/");
				l = arPath.length;
				for (i = 0; i < l; i++)
				{
					if (!this.__arGroups[i])
						this.__arGroups[i] = {};

					if(!this.__arGroups[i][arPath[i]])
					{
						this.__arGroups[i][arPath[i]] = true;
						addGroup(arPath[i], (i == 0) ? '' : arPath[0]);
					}
				}
			}

			oEl.tagname = 'snippet';
			oEl.isGroup = false;
			oEl.icon = '/bitrix/images/fileman/htmledit2/snippet.gif';
			oEl.path = oEl.path.replace("/", ",");
			oEl.code = this.Remove__script__(oEl.code);
			oEl.description = oEl.description;
			oEl.title = oEl.title;

			var c = "sn_"+Math.round(Math.random() * 1000000);
			this.oTaskbar.pMainObj.arSnippetsCodes[c] = key;
			oEl.params = {c : c};

			this.oTaskbar.AddElement(oEl, this.oTaskbar.pCellSnipp, oEl.path);
		}
		this.oTaskbar.AddSnippet_button();
	};


	BXSnippetsTaskbar.prototype.AddSnippet_button = function()
	{
		if (!oTaskbar)
			oTaskbar = this;

		var oDiv = document.getElementById("___add_snippet___"+this.oTaskbar.pMainObj.name);
		if (oDiv)
			this.oTaskbar.pCellSnipp.removeChild(oDiv);

		oDiv = document.createElement("DIV");
		oDiv.id = "___add_snippet___"+this.oTaskbar.pMainObj.name;
		oDiv.style.padding = "3px 0px 10px 10px";
		var oLink = document.createElement("a");
		oLink.href = "";
		oLink.innerHTML = BX_MESS.AddSnippet;
		oLink.onclick = function(e)
		{
			oTaskbar.addSnippet();
			return false;
		}
		oLink.style.marginLeft = "2px";
		oDiv.appendChild(oLink);
		this.oTaskbar.pCellSnipp.appendChild(oDiv);
	};


	BXSnippetsTaskbar.prototype.Remove__script__ = function (str)
	{
		str = str.replace(/&lt;script/ig, "<script");
		str = str.replace(/&lt;\/script/ig, "</script");
		str = str.replace(/\\n/ig, "\n");
		return str;
	};


	BXSnippetsTaskbar.prototype.OnElementClick = function (oEl, arEl)
	{
		_pTaskbar = this.pMainObj.oPropertiesTaskbar;
		var _this = this;
		oBXEditorUtils.BXRemoveAllChild(_pTaskbar.pCellProps);

		//****** DISPLAY TITLE *******
		var snippetName = arEl.name;
		var snippetTitle = arEl.title;
		var key = (arEl.path == '' ? '' : arEl.path.replace(',', '/') + '/')+arEl.name;
		var snippetCode = arSnippets[key].code;
		var snippetDesc = arSnippets[key].description;

		var tCompTitle = document.createElement("TABLE");
		tCompTitle.className = "componentTitle";
		tCompTitle.style.height = "96%";
		var row = tCompTitle.insertRow(-1);
		var cell = row.insertCell(-1);
		cell.innerHTML = "<table style='width:100%'><tr><td style='width:85%'><SPAN class='title'>"+snippetTitle+"  ("+snippetName+")</SPAN><BR /><SPAN class='description'>"+snippetDesc+"</SPAN></td><td style='width:15%; padding-right: 20px' align='right'><div style='width: 62px'><div id='__edit_snip_but' class= 'iconkit_c' style='width: 29px; height: 17px; background-position: -29px -62px; float:left;' title='"+BX_MESS.EditSnippet+"'></div><div id='__del_snip_but' class= 'iconkit_c' style='width: 29px; height: 17px; background-position: 0px -62px;' title='"+BX_MESS.DeleteSnippet+"'></div></td></tr></table>";

		cell.className = "titlecell";
		cell.width = "100%";
		cell.align = "left";
		var row = tCompTitle.insertRow(-1);
		var cell = row.insertCell(-1);
		cell.className = "bxcontentcell";

		_tbl = cell.appendChild(document.createElement("TABLE"));
		_tbl.style.width = "100%";
		_tbl.style.height = "100%";
		_r = _tbl.insertRow(-1);

		_c = _r.insertCell(-1);
		_c.className = 'bx_snip_preview bx_snip_valign';

		var thumb = arSnippets[key].thumb;
		if (thumb)
		{
			var thumb_src = BX_PERSONAL_ROOT + "/templates/"+arSnippets[key].template+"/snippets/images/"+(arEl.path == '' ? '' : (arEl.path.replace(',', '/')+'/'))+thumb;
			var img = document.createElement("IMG");
			img.src = thumb_src;
			img.onerror = function(){this.parentNode.removeChild(this);};
			_c.appendChild(img);
		}

		_c = _r.insertCell(-1);
		_c.style.width = "100%";
		_c.className = "bx_snip_valign";

		var _repl_tags = function(str)
		{
			str = str.replace(/</g,'&lt;');
			str = str.replace(/>/g,'&gt;');
			return str;
		};

		var _d = BXCreateElement('DIV', {className: 'bx_snip_code_preview'}, {}, document);
		_d.innerHTML = "<pre>"+_repl_tags(snippetCode)+"</pre>";
		_c.appendChild(_d);
		setTimeout(function ()
		{
			_pTaskbar.pCellProps.appendChild(tCompTitle);
			//tCompTitle.style.width = (parseInt(_pTaskbar.pCellProps.offsetWidth) - 80) + 'px';
			//_d.style.width = parseInt(_d.parentNode.offsetWidth) + 'px';
			document.getElementById("__edit_snip_but").onclick = function(e){_this.editSnippet(arSnippets[key],oEl);};
			document.getElementById("__del_snip_but").onclick = function(e){_this.delSnippet(arSnippets[key],oEl);};
		}, 10);
	};


	BXSnippetsTaskbar.prototype.OnElementDragEnd = function(oEl)
	{
		if (oEl.getAttribute('__bxtagname') == 'snippet')
		{
			var allParams = oBXEditorUtils.getCustomNodeParams(oEl);
			var html = oTaskbar.pMainObj.pParser.SystemParse(arSnippets[this.pMainObj.arSnippetsCodes[allParams.c]].code);
			if (BXIsIE())
			{
				this.pMainObj.pEditorDocument.selection.clear();
				this.pMainObj.insertHTML(html);
			}
			else
			{
				this.pMainObj.insertHTML(html);
				oEl.parentNode.removeChild(oEl);
			}
			this.pMainObj.oPropertiesTaskbar.OnSelectionChange('always');
		}
	};


	BXSnippetsTaskbar.prototype.addSnippet = function()
	{
		oTaskbar.pMainObj.CreateCustomElement("BXDialog",
		{
			width : "450px",
			height : "400px",
			name : "snippets",
			params : {'pMainObj': oTaskbar.pMainObj, 'BXSnippetsTaskbar': oTaskbar,'mode':'add'},
			handler : "fileman_snippets_dialog.php"
		});
	};


	BXSnippetsTaskbar.prototype.editSnippet = function(oEl,elNode)
	{
		oTaskbar.pMainObj.CreateCustomElement("BXDialog",
		{
			width:"450px",
			height:"400px",
			name:"snippets",
			params:{'pMainObj':oTaskbar.pMainObj,'BXSnippetsTaskbar':oTaskbar,'mode':'edit','oEl':oEl,'elNode':elNode,'prop_taskbar':_pTaskbar.pCellProps},
			handler:"fileman_snippets_dialog.php"
		});
	};


	BXSnippetsTaskbar.prototype.delSnippet = function(oEl, elNode)
	{
		var _this = this;
		var _ds = new JCHttpRequest();
		_ds.Action = function(result)
		{
			setTimeout(function ()
				{
					if (window.operation_success)
					{
						//Clean properties panel
						oBXEditorUtils.BXRemoveAllChild(_pTaskbar.pCellProps);
						//Remove Element from list
						var _id = elNode.parentNode.id;
						var elTable = elNode.parentNode.parentNode.parentNode.parentNode;
						elTable.parentNode.removeChild(elTable);
						while (el = document.getElementById(_id))
						{
							elTable = el.parentNode.parentNode.parentNode;
							elTable.parentNode.removeChild(elTable);
						}
						elTable = null;
					}
				}, 5
			);
		}
		if (confirm(BX_MESS.DEL_SNIPPET_CONFIRM+' "'+oEl.title+'"?'))
		{
			window.operation_success = false;
			_ds.Send('/bitrix/admin/fileman_manage_snippets.php?lang='+BXLang+'&site='+BXSite+'&templateID='+escape(oEl.template)+'&target=delete&name='+escape(oEl.name)+'&path='+escape(oEl.path.replace(',', '/'))+'&thumb='+escape(oEl.thumb));
		}
	};

	BXSnippetsTaskbar.prototype.ClearCache = function(oEl, elNode)
	{
		oTaskbar.pMainObj.oBXWaitWindow.Show();
		oTaskbar.RemoveElementList(oTaskbar.pCellSnipp);
		oBXEditorUtils.BXRemoveAllChild(oTaskbar.rootElementsCont);
		window.arSnippets = {};
		oTaskbar.loadSnippets(true);
	};
}

oBXEditorUtils.addTaskBar('BXSnippetsTaskbar', 2, BX_MESS.SnippetsTB, []);