var BXConst =
{
	'arColor': ["000000", "993300", "333300", "003300", "003366", "000080", "333399", "333333",
		"800000", "FF6600", "808000", "008000", "008080", "0000FF", "666699", "808080",
		"FF0000", "FF9900", "99CC00", "339966", "33CCCC", "3366FF", "800080", "999999",
		"FF00FF", "FFCC00", "FFFF00", "00FF00", "00FFFF", "00CCFF", "993366", "C0C0C0",
		"FF99CC", "FFCC99", "FFFF99", "CCFFCC", "CCFFFF", "99CCFF", "CC99FF", "FFFFFF"],

	'arColorName': [BX_MESS.CPickClr40, BX_MESS.CPickClr2, BX_MESS.CPickClr3, BX_MESS.CPickClr4, BX_MESS.CPickClr5, BX_MESS.CPickClr6, BX_MESS.CPickClr7, BX_MESS.CPickClr8,
		BX_MESS.CPickClr9, BX_MESS.CPickClr10, BX_MESS.CPickClr11, BX_MESS.CPickClr12, BX_MESS.CPickClr13, BX_MESS.CPickClr14, BX_MESS.CPickClr15, BX_MESS.CPickClr16,
		BX_MESS.CPickClr17, BX_MESS.CPickClr18, BX_MESS.CPickClr19, BX_MESS.CPickClr20, BX_MESS.CPickClr21, BX_MESS.CPickClr22, BX_MESS.CPickClr23, BX_MESS.CPickClr24,
		BX_MESS.CPickClr25, BX_MESS.CPickClr26, BX_MESS.CPickClr27, BX_MESS.CPickClr28, BX_MESS.CPickClr29, BX_MESS.CPickClr30, BX_MESS.CPickClr31, BX_MESS.CPickClr32,
		BX_MESS.CPickClr33, BX_MESS.CPickClr34, BX_MESS.CPickClr35, BX_MESS.CPickClr36, BX_MESS.CPickClr37, BX_MESS.CPickClr38, BX_MESS.CPickClr39, BX_MESS.CPickClr1]
};

//Colors of borders and backgrounds for diferent button states
var borderColorNormal = "#e4e2dc";
var borderColorOver = "#4B4B6F";
var borderColorSet = "#4B4B6F";
var borderColorSetOver = "#4B4B6F";

var bgroundColorOver = "#FFC678";
var bgroundColorSet = "#FFC678";
var bgroundColorSetOver = "#FFA658";

// BXButton - class
function BXButton()
{
	ar_BXButtonS.push(this);
	this._prevDisabledState = false;
}

BXButton.prototype._Create = function ()
{
	if(this.OnCreate && this.OnCreate()==false)
		return false;

	var pElement, i, j, obj = this;
	this.className = 'BXButton';

	if (this.id && this.iconkit)
	{
		this.pWnd = this.CreateElement("IMG", {src: one_gif_src, alt: (this.title ? this.title : this.name), title: (this.title?this.title:this.name), width: '20', height: '20', id: "bx_btn_"+obj.id});
		this.pWnd.className = 'bxedtbutton';
		this.pWnd.style.backgroundImage = "url(" + image_path + "/" + this.iconkit + ")";
	}
	else
	{
		this.pWnd = this.CreateElement("IMG", {'src' : this.src, 'alt' : (this.title ? this.title : this.name), 'title': (this.title ? this.title : this.name), 'width' : '20', 'height' : '20'});
		this.pWnd.className = 'bxedtbutton';
	}


	if (this.show_name)
	{
		var _icon = this.pWnd;
		this.pWnd = this.CreateElement("TABLE", {title: (this.title ? this.title: this.name), height: '20', id: "bx_btnex_"+obj.id, cellPadding: 0, cellSpacing: 0});
		this.pWnd.className = 'bxedtbuttonex';
		this.pWnd.checked = false;
		this.pWnd.disabled = false;
		var r = this.pWnd.insertRow(-1);
		var c = r.insertCell(-1);
		c.className = 'tdbutex';
		c.appendChild(_icon);
		var c = r.insertCell(-1);
		c.className = 'tdbutex tdbutex_txt';
		c.style.paddingRight = '4px';
		c.innerHTML = this.name;
	}

	//this.pWnd.style.border = "1px sold "+borderColorNormal;
	this.pWnd.style.borderColor  = borderColorNormal;
	this.pWnd.style.borderWidth = "1px";
	this.pWnd.style.borderStyle = "solid";

	if(!this.no_actions || this.no_actions != true) // for context menu
	{
		this.pWnd.onmouseover = function(e)
		{
			if(!this.disabled)
			{
				this.style.borderColor = borderColorOver;
				this.style.border = "#4B4B6F 1px solid";
				if(this.checked)
				{
					this.style.backgroundColor = bgroundColorSetOver;
				}
				else
				{
					this.style.backgroundColor = bgroundColorOver;
					if (this.nodeName == 'TABLE')
						this.className = 'bxedtbuttonexover';
				}
			}
		};

		this.pWnd.onmouseout = function(e)
		{
			if(!this.disabled)
			{
				if(this.checked)
				{
					this.style.borderColor = borderColorSet;
					this.style.backgroundColor = bgroundColorSet;
				}
				else
				{
					this.style.backgroundColor ="";
					this.style.borderColor = borderColorNormal;
					if (this.nodeName == 'TABLE')
						this.className = 'bxedtbuttonex';
				}
			}
		};
		if (this.defaultState)
			this.Check(true);

		addCustomElementEvent(this.pWnd, 'click', this.OnClick, this);
		this.pMainObj.AddEventHandler("OnSelectionChange", this._OnSelectionChange, this);
		this.pMainObj.AddEventHandler("OnChangeView", this.OnChangeView, this);
	}
};

BXButton.prototype._OnChangeView = function (mode, split_mode)
{
	mode = (mode == 'split' ? split_mode : mode);
	if(mode == 'code' && !this.codeEditorMode || (mode=='html' && this.hideInHtmlEditorMode))
	{
		this._prevDisabledState = this.pWnd.disabled;
		this.Disable(true);
	}
	else if(mode == 'code' && this.codeEditorMode || (this.hideInHtmlEditorMode && mode != 'html'))
		this.Disable(false);
	else if(!this.codeEditorMode)
		this.Disable(this._prevDisabledState);
};

BXButton.prototype.OnChangeView = function (mode, split_mode)
{
	this._OnChangeView(mode, split_mode);
};

BXButton.prototype.Disable = function (bFlag)
{
	if(bFlag == this.pWnd.disabled)
		return false;
	this.pWnd.disabled = bFlag;
	if(bFlag)
	{
		if (this.id && this.iconkit)
		{
			this.pWnd.className = 'bxedtbuttondisabled';
			this.pWnd.style.backgroundImage = "url(" + image_path + "/" + this.iconkit + ")";
		}
		else
		{
			this.pWnd.className = 'bxedtbuttondisabled';
		}
		this.pWnd.style.filter = 'gray() alpha(opacity=30)';
	}
	else
	{
		this.pWnd.style.filter = '';
		this.pWnd.className = 'bxedtbutton';
		if(this.pWnd.checked)
		{
			//this.pWnd.className = 'bxedtbuttonset';
			this.pWnd.style.borderColor = borderColorSet;
			this.pWnd.style.backgroundColor = bgroundColorSet;
		}
		else
		{
			//this.pWnd.className = 'bxedtbutton';
			this.pWnd.style.backgroundColor ="";
			this.pWnd.style.borderColor = borderColorNormal;
		}
	}
}

BXButton.prototype.Check = function (bFlag)
{
	if(bFlag == this.pWnd.checked)
		return false;
	this.pWnd.checked = bFlag;
	if(!this.pWnd.disabled)
	{
		if(this.pWnd.checked)
		{
			//this.pWnd.className = 'bxedtbuttonset';
			this.pWnd.style.borderColor = borderColorSet;
			this.pWnd.style.backgroundColor = bgroundColorSet;
		}
		else
		{
			//this.pWnd.className = 'bxedtbutton';
			this.pWnd.style.backgroundColor ="";
			this.pWnd.style.borderColor = borderColorNormal;
		}
	}
}

BXButton.prototype.OnMouseOver = function (e)
{
	if(!this.disabled)
	{
		this.style.borderColor = borderColorOver;
		this.style.border = "#4B4B6F 1px solid";
		if(this.checked)
		{
			this.style.backgroundColor = bgroundColorSetOver;
			//this.className = 'bxedtbuttonsetover';
		}
		else
		{
			this.style.backgroundColor = bgroundColorOver;
			//this.className = 'bxedtbuttonover';
		}
	}
}

BXButton.prototype.OnMouseOut = function (e)
{
	if(!this.disabled)
	{
		if(this.checked)
		{
			this.style.borderColor = borderColorSet;
			this.style.backgroundColor = bgroundColorSet;
			//this.className = 'bxedtbuttonset';
		}
		else
		{
			this.style.backgroundColor ="";
			this.style.borderColor = borderColorNormal;
			//this.className = 'bxedtbutton';
		}
	}
}

BXButton.prototype.OnClick = function (e)
{
	if(this.pWnd.disabled) return false;
	this.pMainObj.SetFocus();
	var res = false;
	if(this.handler)
		if(this.handler() !== false)
			res = true;

	if(!res)
		res = this.pMainObj.executeCommand(this.cmd);

	if(!this.bNotFocus)
		this.pMainObj.SetFocus();

	return res;
}

BXButton.prototype._OnSelectionChange = function()
{
	if(this.OnSelectionChange)
		this.OnSelectionChange();
	else if(this.cmd)
	{
		var res;

		if(this.cmd=='Unlink' && !BXFindParentByTagName(this.pMainObj.GetSelectionObject(), 'A'))
			res = 'DISABLED';
		else
			res = this.pMainObj.queryCommandState(this.cmd);

		if(res == 'DISABLED')
			this.Disable(true);
		else if(res == 'CHECKED')
		{
			this.Disable(false);
			this.Check(true);
		}
		else
		{
			this.Disable(false);
			this.Check(false);
		}
	}
}

function BXButtonSeparator()
{
	ar_BXButtonS.push(this);
}


BXButtonSeparator.prototype._Create = function ()
{
	var pElement, i, j;
	this.className = 'BXButtonSeparator';
	this.pWnd = this.CreateElement("DIV", {className: 'bxseparator'});

	this.OnToolbarChangeDirection = function(bVertical)
	{
		if(bVertical)
		{
			this.pWnd.style.backgroundPosition = "-60px -78px";
			this.pWnd.style.width = "20px";
			this.pWnd.style.height = "2px";
		}
		else
		{
			this.pWnd.style.backgroundPosition = "-58px -60px";
			this.pWnd.style.width = "2px";
			this.pWnd.style.height = "24px";
		}
	};
}


// BXList - class
function BXList()
{
	ar_BXListS.push(this);
	this.className = 'BXList';
	this.iSelectedIndex = -1;
	this.disabled = false;
}

BXList.prototype._Create = function ()
{
	if(this.OnCreate && this.OnCreate()==false)
		return false;

	if(this.OnSelectionChange)
		this.pMainObj.AddEventHandler("OnSelectionChange", this.OnSelectionChange, this);

	if(this.disableOnCodeView)
		this.pMainObj.AddEventHandler("OnChangeView", this.OnChangeView, this);

	this._PreCreate();
	this.SetValues(this.values);

	if(this.OnInit && this.OnInit()==false)
		return false;

	return true;
}

BXList.prototype._OnChangeView = function (mode, split_mode)
{
	mode = (mode=='split'?split_mode:mode);
	this.Disable(mode=='code');
}

BXList.prototype.OnChangeView = function (mode, split_mode)
{
	this._OnChangeView(mode, split_mode);
}

BXList.prototype.Disable = function(flag)
{
	if(this.disabled==flag)
		return false;
	this.disabled=flag;
	if(flag)
	{
		this.pWnd.className = 'bxlistdisabled';
	}
	else
	{
		this.pWnd.className = 'bxlist';
	}
}

BXList.prototype.SetValues = function (values)
{
	this.values = values;
	while(this.pDropDownList.childNodes.length>0)
		this.pDropDownList.removeChild(this.pDropDownList.childNodes[0]);

	var r, c, t1, r1, c1;
	for(var i = 0, l = this.values.length; i < l; i++)
	{
		r = this.pDropDownList.insertRow(-1);
		c = r.insertCell(-1);

		t1 = BXPopupWindow.CreateElement("TABLE", {border: '0', cellSpacing: '0', width: '100%', cellPadding: '1', className: 'bxedlistitem'});
		r1 = t1.insertRow(-1);
		c1 = r1.insertCell(-1);
		c1.style.height = "16px";
		c1.style.cursor = "default";
		c1.noWrap = true;
		this.values[i].index = i;
		c1.title = this.values[i].name;
		c1.value = this.values[i];

		c1.style.border = '1px solid #FFFFFF';
		c1.onmouseover = function (e){this.style.border = '1px solid #4B4B6F';};
		c1.onmouseout = function (e){this.style.border = '1px solid #FFFFFF';};
		c1.obj = this;
		c1.onclick = function ()
		{
			BXPopupWindow.Hide();
			this.obj._OnChange(this.value);
			this.obj.FireChangeEvent();
		};
		c1.innerHTML = (this.OnDrawItem) ? this.OnDrawItem(this.values[i]) : this.values[i].name;
		
		ar_EVENTS.push([c1,"mouseover"]);
		ar_EVENTS.push([c1,"mouseout"]);
		ar_EVENTS.push([c1,"click"]);
		t1.unselectable = "on";
		c.appendChild(t1);
	}

	r1 = c1 = t1 = r = c = null;
}

BXList.prototype.FireChangeEvent = function()
{
	if(this.OnChange)
		this.OnChange(this.arSelected);
}

BXList.prototype._OnChange = function (selected)
{
	this.Select(selected["index"]);
}


BXList.prototype.SetValue = function(val)
{
	if(!this.pTitle)
		return;
	
	this.pTitle.innerHTML = val || this.title || '';
}

BXList.prototype.OnMouseOver = function (e)
{
	if(this.disabled) return false;
	this.pWnd.className = 'bxlist bxlistover';
}

BXList.prototype.OnMouseOut = function (e)
{
	if(this.disabled) return false;
	this.pWnd.className = 'bxlist';
}

BXList.prototype._PreCreate = function ()
{
	obj = this;
	this.pWnd = this.pMainObj.CreateElement("DIV", {'className': 'bxlist', 'border': '0'});
	this.pWnd.style.width = this.field_size;
	var pTable = this.pWnd.appendChild(this.pMainObj.CreateElement("TABLE", {'cellPadding': 0, 'cellSpacing': 0, 'border': 0}));

	var row = pTable.insertRow(-1), cell = row.insertCell(-1);
	this.pTitle = this.pMainObj.CreateElement("DIV", {'className': 'bxlisttitle', 'border': '0'});
	this.pTitle.innerHTML = (this.title?this.title:'');
	this.pTitle.unselectable = "on";
	cell.appendChild(this.pTitle);
	this.pTitleCell = cell;

	cell = row.insertCell(-1);
	cell.className = 'bxlistbutton';
	cell.innerHTML = '&nbsp;';
	cell.unselectable = "on";

	addCustomElementEvent(this.pWnd, 'mouseover', this.OnMouseOver, this);
	addCustomElementEvent(this.pWnd, 'mouseout', this.OnMouseOut, this);
	addCustomElementEvent(this.pWnd, 'click', this.OnClick, this);

	if (!BXPopupWindow.bCreated)
		BXPopupWindow.Create();

	this.pPopupNode = BXPopupWindow.CreateElement("DIV", {'border': "0"});
	this.pPopupNode.style.border = "1px solid #A0A0A0";
	this.pPopupNode.style.overflow = "auto";
	this.pPopupNode.style.width = (this.width?this.width:"150px");
	this.pPopupNode.style.overflowX = "hidden";
	this.pPopupNode.style.height = (this.height?this.height:"200px");
	this.pPopupNode.style.overflowY = "auto";
	this.pPopupNode.style.textOverflow = "ellipsis";

	this.pDropDownList = BXPopupWindow.CreateElement("TABLE", {'border': '0', 'width': '100%', 'cellSpacing': '0', 'cellPadding': '0', 'unselectable': 'on'});

	this.pPopupNode.appendChild(this.pDropDownList);

	row = null;
	cell = null;
	pTable = null;
}


BXList.prototype.OnClick = function (e)
{
	if(this.disabled) return false;
	//this.pWnd.className = 'bxedtbutton';
	var arPos = GetRealPos(this.pWnd);
	if(this.bSetGlobalStyles)
		BXPopupWindow.SetCurStyles();
	else
		this.pMainObj.oStyles.SetToDocument(BXPopupWindow.GetDocument());
	BXPopupWindow.Show([arPos["left"], arPos["right"]], [arPos["top"], arPos["bottom"]], this.pPopupNode);
}

BXList.prototype.Select = function(v)
{
	if(this.iSelectedIndex == v || v >= this.values.length)
		return;
	var sel = this.values[v];
	this.iSelectedIndex = v;
	this.arSelected = sel;
	this.SetValue(sel["name"]);
}



BXList.prototype.SelectByVal = function(val)
{
	if(val)
	{
		for(var i=0; i < this.values.length; i++)
		{
			if(this.values[i].value == val)
			{
				this.Select(i);
				return;
			}
		}
	}
	
	this.SetValue(this.title || '');
	this.iSelectedIndex = -1;
}

BXList.prototype.OnToolbarChangeDirection = function (bVertical)
{
	if(bVertical)
	{
		this.pWnd.style.width = "18px";
		this.pTitleCell.style.visibility = "hidden";
	}
	else
	{
		this.pWnd.style.width = this.field_size;
		this.pTitleCell.style.visibility = "inherit";
	}

	this.pWnd.className = 'bxlist';
}



// BXStyleList - class
function BXStyleList()
{
	ar_BXStyleListS.push(this);
}

BXStyleList.prototype = new BXList;

BXStyleList.prototype._Create = function ()
{
	this.className = 'BXStyleList';
	this._PreCreate();

	if(this.OnSelectionChange)
		this.pMainObj.AddEventHandler("OnSelectionChange", this.OnSelectionChange, this);

	this.pMainObj.AddEventHandler("OnTemplateChanged", this.FillList, this);

	if(this.disableOnCodeView)
		this.pMainObj.AddEventHandler("OnChangeView", this.OnChangeView, this);

	this.FillList();
}

BXStyleList.prototype.FillList = function()
{
	var i, j, arStyles, l;

	if(!this.filter)
		this._SetFilter();

	while(this.pDropDownList.rows.length>0)
		this.pDropDownList.deleteRow(0);

	this.values = [];
	if(!this.tag_name)
		this.tag_name = '';

	//"clear style" item
	this.__CreateRow('', BX_MESS.DeleteStyleOpt, {'index': this.values.length, 'value': '', 'name': BX_MESS.DeleteStyleOptTitle});

	var style_title, counter = 0, arStyleTitle;
	// other styles
	for(i = 0, l = this.filter.length; i < l;  i++)
	{
		arStyles = this.pMainObj.oStyles.GetStyles(this.filter[i]);
		for(j=0; j<arStyles.length; j++)
		{
			if(arStyles[j].className.length<=0)
				continue;
			arStyleTitle = this.pMainObj.arTemplateParams["STYLES_TITLE"];

			if(this.pMainObj.arTemplateParams && arStyleTitle && arStyleTitle[arStyles[j].className])
				style_title = arStyleTitle[arStyles[j].className];
			else if(!this.pMainObj.arConfig["bUseOnlyDefinedStyles"])
			 	style_title = arStyles[j].className;
			else
			 	continue;

			this.__CreateRow(arStyles[j].className, style_title, {'index': this.values.length, 'value': arStyles[j].className, 'name': style_title});
			counter++;
		}
	}
	if (this.deleteIfNoItems)
		this.pWnd.style.display = (counter == 0) ? "none" : "block";
}

BXStyleList.prototype.__CreateRow = function(className, Name, value)
{
	var r1, c1, t1, r, c;

	r = this.pDropDownList.insertRow(-1);
	c = r.insertCell(-1);

	t1 = BXPopupWindow.CreateElement("TABLE", {'border': '0', 'cellSpacing': '0', 'width': '100%', 'cellPadding': '1'});
	r1 = t1.insertRow(-1);
	c1 = r1.insertCell(-1);
	c1.style.height = "16px";
	c1.style.cursor = "default";
	c1.innerHTML = Name;

	if (styleList_render_style)
	{
		switch(this.tag_name.toUpperCase())
		{
			case "TD":
				c1.className = className;
				break;
			case "TABLE":
				t1.className = className;
				break;
			case "TR":
				r1.className = className;
				break;
			default:
				c1.innerHTML = '<span class="'+className+'">'+Name+'</span>';
		}
	}

	c1.style.border = '1px solid #CCCCCC';
	c1.val = className;
	c1.onmouseover = function (e){this.style.border = '1px solid #000000';};
	c1.onmouseout = function (e){this.style.border = '1px solid #CCCCCC';};
	c1.onclick = function (e){this.obj._OnChange(this.value); this.obj.FireChangeEvent(); BXPopupWindow.Hide(); this.style.border = '1px solid #CCCCCC'; if(this.value.value=='')this.obj.SelectByVal();};
	ar_EVENTS.push([c1,"mouseover"]);
	ar_EVENTS.push([c1,"mouseout"]);
	ar_EVENTS.push([c1,"click"]);
	c1.title = Name;
	t1.unselectable = "on";
	c.appendChild(t1);
	c1.value = value;
	c1.obj = this;
	this.values.push(value);

	r1=null;
	c1=null;
	r=null;
	c=null;
	t1=null;
}

BXStyleList.prototype.OnChange = function(arSelected)
{
	this.pMainObj.WrapSelectionWith("span", {"class":arSelected["value"]});
}

BXStyleList.prototype._SetFilter = function()
{
	this.filter = ["DEFAULT"];
}

BXStyleList.prototype.OnClick = function (e)
{
	//alert('BXStyleList.prototype.OnClick');
	//this.pWnd.className = 'bxedtbutton';
	if(this.disabled)
		return;
	var arPos = GetRealPos(this.pWnd);
	this.pMainObj.oStyles.SetToDocument(BXPopupWindow.GetDocument());
	BXPopupWindow.Show([arPos["left"], arPos["right"]], [arPos["top"], arPos["bottom"]], this.pPopupNode);
}



//BXColorPicker - class
function BXColorPicker()
{
	this._bx_id = ar_BXColorPickerS.length;
	ar_BXColorPickerS.push(this);

	BXColorPicker.prototype._Create = function ()
	{
		var pElement, i, j, obj = this;
		this.className = 'BXColorPicker';

		this.pWnd = this.pMainObj.CreateElement("TABLE", {'cellPadding': 0, 'cellSpacing': 0, 'border': 0});
		var row = this.pWnd.insertRow(-1);
		cell = row.insertCell(-1);

		if(this.OnSelectionChange)
			this.pMainObj.AddEventHandler("OnSelectionChange", this.OnSelectionChange, this);

		if(this.disableOnCodeView)
			this.pMainObj.AddEventHandler("OnChangeView", this.OnChangeView, this);

		if(this.with_input)
		{
			this.pInput = this.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': 7});
			cell.appendChild(this.pInput);
			cell = row.insertCell(-1);
			this.pInput.onchange = function (){obj._OnChange(this.value);};
			ar_EVENTS.push([this.pInput,"change"]);
		}

		var id = this.id ? this.id : 'BackColor';
		var iconkit = this.iconkit ? this.iconkit : '_global_iconkit.gif';
		this.pIcon = this.pMainObj.CreateElement("IMG", {id: 'bx_btn_' + id, title: this.title, src: one_gif_src}, {width: '20px', height: '20px', border: '1px solid '+borderColorNormal, backgroundImage: "url(" + image_path + "/" + iconkit + ")"});
		cell.appendChild(this.pIcon);
		addCustomElementEvent(this.pIcon, 'mouseover', this.OnMouseOver, this);
		addCustomElementEvent(this.pIcon, 'mouseout', this.OnMouseOut, this);
		addCustomElementEvent(this.pIcon, 'click', this.OnClick, this);

		if (!BXPopupWindow.bCreated)
			BXPopupWindow.Create();

		this.pPopupNode = BXPopupWindow.CreateElement("DIV", {'border': "0"});
		this.pPopupNode.style.border = "1px solid #A0A0A0";

		if (!CACHE_DISPATCHER['colorPickerTable'])
		{
			var t = BXPopupWindow.CreateElement("TABLE", {border: '0', width: '160', cellSpacing: '1', cellPadding: '2'});
			t.onclick = function (e){BXPopupWindow.Hide();};
			ar_EVENTS.push([t,"click"]);
			var r = t.insertRow(-1);
			var c = r.insertCell(-1);
			t.className = 'bxedcolorpicker';
			c.style.height = "0%";
			c.innerHTML = BX_MESS.CPickDef;
			c.style.border = '1px solid #C0C0C0';
			c.onmouseover = function (e){this.style.border = '1px solid #000000'; this.style.backgroundColor = '#FFC678';};
			c.onmouseout = function (e){this.style.border = '1px solid #C0C0C0'; this.style.backgroundColor = 'transparent';};
			c.onclick = function (e)
			{
				obj = ar_BXColorPickerS[_bx_id]; //_bx_id - is a global variable - sets when user click on colorpicker icon
				obj._OnChange(''); BXPopupWindow.Hide();
			};
			ar_EVENTS.push([c,"mouseover"]);
			ar_EVENTS.push([c,"mouseout"]);
			ar_EVENTS.push([c,"click"]);

			r = t.insertRow(-1);
			c = r.insertCell(-1);
			c.style.height = "100%";

			var iColumnCount = 8;

			var r1, c1, l, colId;
			var t1 = BXPopupWindow.CreateElement("TABLE", {'border': '0', 'cellSpacing': '3', 'cellPadding': '0'});

			for(i = 0, l = BXConst.arColor.length/iColumnCount; i < l; i++)
			{
				r1 = t1.insertRow(-1);
				for(j = 0; j < iColumnCount; j++)
				{
					c1 = r1.insertCell(-1);
					c1.className = 'bx_colorpicker_cell';
					colId = i * iColumnCount + j;
					c1.style.backgroundColor = "#" + BXConst.arColor[colId];
					c1.val = "#" + BXConst.arColor[i * iColumnCount + j];
					c1.onmouseover = function (e){this.className = 'bx_colorpicker_cell_over';};
					c1.onmouseout = function (e){this.className = 'bx_colorpicker_cell';};
					c1.onclick = function (e)
					{
						BXPopupWindow.Hide();
						obj = ar_BXColorPickerS[_bx_id]; //_bx_id - is a global variable - sets when user click on colorpicker icon
						obj._OnChange(this.val);
						this.style.border = '1px solid #C0C0C0';
					};
					ar_EVENTS.push([c1, "mouseover"]);
					ar_EVENTS.push([c1, "mouseout"]);
					ar_EVENTS.push([c1, "click"]);

					c1.appendChild(BXPopupWindow.CreateElement("IMG", {src: one_gif_src, width: '1', height: '1'}));
					c1.title = BXConst.arColorName[colId];
				}
			}
			c.appendChild(t1);
			CACHE_DISPATCHER['colorPickerTable'] = t;
		}
	};

	BXColorPicker.prototype._OnChange = function (color)
	{
		if(this.with_input)
			this.pInput.value = color;

		if(this.OnChange)
			this.OnChange(color);
	};

	BXColorPicker.prototype.OnChangeView = function (mode, split_mode)
	{
		mode = (mode == 'split' ? split_mode : mode);
		this.Disable(mode == 'code');
	};

	BXColorPicker.prototype.Disable = function (bFlag)
	{
		if(bFlag == this.pIcon.disabled)
			return false;

		this.pIcon.disabled = bFlag;
		if(bFlag)
		{
			this.pIcon.className = 'bxedtbuttondisabled';
			this.pWnd.style.filter = 'gray() alpha(opacity=30)';
		}
		else
		{
			this.pIcon.className = 'bxedtbutton';
			this.pIcon.style.backgroundColor ="";
			this.pIcon.style.borderColor = borderColorNormal;
			this.pWnd.style.filter = '';
		}
	};

	BXColorPicker.prototype.SetValue = function(val)
	{
		if(this.pInput)
			this.pInput.value = val;
	};

	BXColorPicker.prototype.OnMouseOver = function (e)
	{
		this.pIcon.style.borderColor = borderColorOver;
		this.pIcon.style.border = "#4B4B6F 1px solid";
		this.pIcon.style.backgroundColor = bgroundColorOver;
	};

	BXColorPicker.prototype.OnMouseOut = function (e)
	{
		this.pIcon.style.backgroundColor ="";
		this.pIcon.style.borderColor = borderColorNormal;
	};

	BXColorPicker.prototype.OnClick = function (e)
	{
		_bx_id = this._bx_id; //_bx_id - global JS var which can determine BXColorPicker-object in the onclick handler
		this.pPopupNode.appendChild(CACHE_DISPATCHER['colorPickerTable']);
		var arPos = GetRealPos(this.pIcon);
		BXPopupWindow.SetCurStyles();

		if(!this.bNotFocus)
			this.pMainObj.SetFocus();

		BXPopupWindow.Show([arPos["left"], arPos["right"]], [arPos["top"], arPos["bottom"]], this.pPopupNode);
	};
}


// BXTAlignPicker - class
function BXTAlignPicker()
{
	ar_BXColorPickerS.push(this);
	this.arIcon = ["tl", "tc", "tr", "cl", "cc", "cr", "bl", "bc", "br"];
	this.arIconH = ["left", "center", "right"];
	this.arIconV = ["top", "middle", "bottom"];
	this.arIconName = [
		BX_MESS.TAlign1, BX_MESS.TAlign2, BX_MESS.TAlign3,
		BX_MESS.TAlign4, BX_MESS.TAlign5, BX_MESS.TAlign6,
		BX_MESS.TAlign7, BX_MESS.TAlign8, BX_MESS.TAlign9];

	BXTAlignPicker.prototype._Create = function ()
	{
		var pElement, i, j, obj = this;
		this.className = 'BXTAlignPicker';
		this.pWnd = this.pMainObj.CreateElement("TABLE", {cellPadding: 0, cellSpacing: 0, border: 0});
		var row = this.pWnd.insertRow(-1), cell = row.insertCell(-1);

		this.pIcon = this.pMainObj.CreateElement("DIV", {id: 'bx_btn_align_tl', className: '', title: this.title}, {width: '20px', height: '20px', border: '1px solid '+borderColorNormal, backgroundImage: "url(" + global_iconkit_path + ")"});
		cell.appendChild(this.pIcon);
		addCustomElementEvent(this.pIcon, 'mouseover', this.OnMouseOver, this);
		addCustomElementEvent(this.pIcon, 'mouseout', this.OnMouseOut, this);
		addCustomElementEvent(this.pIcon, 'click', this.OnClick, this);

		if (!BXPopupWindow.bCreated)
			BXPopupWindow.Create();

		this.pPopupNode = BXPopupWindow.CreateElement("DIV", {'border': "0"});
		this.pPopupNode.style.border = "1px solid #A0A0A0";
		//this.onclick = function (e){BXPopupWindow.Hide();};
		//ar_EVENTS.push([this,"click"]);
		var t = BXPopupWindow.CreateElement("TABLE", {cellSpacing:1, className: 'bxedtalignpicker'});
		t.onclick = function (e){BXPopupWindow.Hide();};
		ar_EVENTS.push([t,"click"]);
		var r = t.insertRow(-1);
		var c = r.insertCell(-1);
		c.innerHTML = '<nobr>'+BX_MESS.TAlignDef+'</nobr>';
		c.className = 'bxedtbutton';
		c.style.border = '1px solid ' + borderColorNormal;
		c.noWrap = true;
		c.onmouseover = function (e)
		{
			this.className = 'bxedtbuttonover';
			this.style.borderColor = borderColorOver;
		};
		c.onmouseout = function (e)
		{
			this.className = 'bxedtbutton';
			this.style.borderColor = borderColorNormal;
		};
		c.onclick = function (e){obj._OnChange('', ''); BXPopupWindow.Hide();};
		ar_EVENTS.push([c,"mouseover"]);
		ar_EVENTS.push([c,"mouseout"]);
		ar_EVENTS.push([c,"click"]);

		r = t.insertRow(-1);
		c = r.insertCell(-1);
		c.style.height = "100%";
		if(!this.type)
			this.type = "default";

		var r1, c1, t1 = BXPopupWindow.CreateElement("TABLE", {'border': '0', 'cellSpacing': '3', 'cellPadding': '0'});
		for(i = 0; i < 3; i++)
		{
			r1 = t1.insertRow(-1);
			if(this.type == 'table')
				i = 1;
			for(j = 0; j < 3; j++)
			{
				c1 = r1.insertCell(-1);
				if(this.type == 'image' && i!=1 && j!=1)
				{
					c1 = c1.appendChild(BXPopupWindow.CreateElement("DIV", {className: 'bxedtbutton'}, {border: '1px solid '+borderColorNormal, backgroundImage: "url(" + global_iconkit_path + ")"}));
				}
				else
				{
					c1 = c1.appendChild(BXPopupWindow.CreateElement("DIV", {id: 'bx_btn_align_'+this.arIcon[i * 3 + j], className: 'bxedtbutton', title: this.arIconName[i * 3 + j]}, {border: '1px solid '+borderColorNormal, backgroundImage: "url(" + global_iconkit_path + ")"}));

					if(this.type == 'image')
					{
						if(j==1)
							c1.val = this.arIconV[i];
						else
							c1.val = this.arIconH[j];
						c1.onclick = function (e){obj._OnChangeI(this.val); BXPopupWindow.Hide(); this.className = 'bxedtbutton';};
					}
					else
					{
						c1.valH = this.arIconH[j];
						c1.valV = this.arIconV[i];
						c1.onclick = function (e){obj._OnChange(this.valH, this.valV); BXPopupWindow.Hide(); this.className = 'bxedtbutton';};
					}
					c1.onmouseover = function (e){
						this.style.borderColor = borderColorOver;
						this.style.border = "#4B4B6F 1px solid";
						this.style.backgroundColor = bgroundColorOver;
					};
					c1.onmouseout = function (e){
						this.style.backgroundColor ="";
						this.style.borderColor = borderColorNormal;
					};
					ar_EVENTS.push([c1,"mouseover"]);
					ar_EVENTS.push([c1,"mouseout"]);
					ar_EVENTS.push([c1,"click"]);
				}
			}
			if(this.type == 'table')
				break;
		}

		c.appendChild(t1);
		this.pPopupNode.appendChild(t);

		c = r = t = c1 = r1 = t1 = row = cell = null;
	}

	BXTAlignPicker.prototype._OnChange = function (valH, valV)
	{
		if(this.OnChange)
			this.OnChange(valH, valV);

		this.SetValue(valH, valV);
	}

	BXTAlignPicker.prototype._OnChangeI = function (val)
	{
		if(this.OnChange)
			this.OnChange(val);

		this.SetValueI(val);
	}

	BXTAlignPicker.prototype.SetValue = function(valH, valV)
	{
		if(this.type == 'image')
			return this.SetValueI(valH);

		for(var j = 0; j < 3; j++)
			if(this.arIconH[j] == valH)
				break;

		for(var i = 0; i < 3; i++)
			if(this.arIconV[i] == valV)
				break;

		if(i > 2) i = 1;
		if(j > 2) j=0;

		this.pIcon.id = "bx_btn_align_"+this.arIcon[i * 3 + j];
		this.pIcon.title = this.arIconName[i * 3 + j];
		return i * 3 + j;
	}

	BXTAlignPicker.prototype.SetValueI = function(val)
	{
		var i, j = 0;
		for(i = 0; i < 3; i++)
			if(this.arIconV[i] == val)
			{
				j = 1;
				break;
			}
		if(j != 1)
			for(j = 0; j < 3; j++)
				if(this.arIconH[j] == val)
				{
					i = 1;
					break;
				}

		if(i > 2) i=1;
		if(j > 2) j=0;

		this.pIcon.id = "bx_btn_align_"+this.arIcon[i * 3 + j];
		this.pIcon.title = this.arIconName[i * 3 + j];
		return i * 3 + j;
	}

	BXTAlignPicker.prototype.OnMouseOver = function (e)
	{
		this.pIcon.style.borderColor = borderColorOver;
		this.pIcon.style.border = "#4B4B6F 1px solid";
		this.pIcon.style.backgroundColor = bgroundColorOver;
	}

	BXTAlignPicker.prototype.OnMouseOut = function (e)
	{
		this.pIcon.style.backgroundColor ="";
		this.pIcon.style.borderColor = borderColorNormal;
	}

	BXTAlignPicker.prototype.OnClick = function (e)
	{
		var arPos = GetRealPos(this.pIcon);
		BXPopupWindow.SetCurStyles();
		BXPopupWindow.Show([arPos["left"], arPos["right"]], [arPos["top"], arPos["bottom"]], this.pPopupNode);
	}
}

function BXCombo(pMainObj, id, pHandler)
{
	this.className = "BXCombo";
	this.id = id;
	this.items = [];
	this.pHandler = pHandler;
	this.pMainObj = pMainObj;

	BXCombo.prototype.AddRow = function(id, value, title, handler)
	{
		this.items[id] = new Object;
		this.items[id].id = id;
		this.items[id].value = value;
		if(handler)
			this.items[id].handler = handler;
		if(title)
			this.items[id].title = title;
		else
			this.items[id].title = value;
	}

	BXCombo.prototype.Show = function()
	{
		var obj = this;
		this.dx = this.params[1];
		this.dy = this.params[2];
		this.title_size = this.params[3];
		this.title_name = this.params[4];

		this.pWnd = this.pMainObj.pDocument.createElement("TABLE");
		this.pWnd.cellPadding = 0;
		this.pWnd.cellSpacing = 0;
		this.pWnd.className = 'bxcombo';
		this.pWnd.unselectable = "on";
		var obwnd = this.pWnd;
		this.pWnd.onmouseover = function (e){obj.pWnd.className = 'bxcomboover'; return false;};
		this.pWnd.onmouseout = function (e){obj.pWnd.className = 'bxcombo';return false;};
		ar_EVENTS.push([this.pWnd,"mouseover"]);
		ar_EVENTS.push([this.pWnd,"mouseout"]);
		var r  = this.pWnd.insertRow(0);
		var c = r.insertCell(0);
		this.title = c.appendChild(this.pMainObj.pDocument.createElement("DIV"));
		this.title.className = 'bxcombotitle';
		this.title.style.width = this.title_size;
		this.title.innerHTML = this.title_name;
		this.title.unselectable = "on";

		var c2 = r.insertCell(1);
		c2.appendChild(this.pMainObj.CreateElement("DIV", {}, {width: '11px', height: '17px', backgroundImage: 'url(' + global_iconkit_path + ')', backgroundPosition: "-64px -63px"}));

		this.pWnd.onclick = function e(){obj.Drop(true)};
		ar_EVENTS.push([this.pWnd,"click"]);
		this.pDropDown = new BXPopup(this.pMainObj, this.pWnd, this.pMainObj.pDocument);
		//ar_OBJECTS.push(this.pDropDown);

		var itemstable = this.pDropDown.pDocument.createElement("TABLE");
		itemstable.style.width = "100%";
		for(var it_id in this.items)
		{
			var item = itemstable.insertRow(-1).insertCell(0);
			item.unselectable = "on";
			item.style.border = "1px #CCCCCC solid";
			item.style.cursor = "default";
			item.onmousemove = function (e)
			{
				this.style.border = "1px #0000FF solid";
			}
			item.onmouseout = function (e)
			{
				this.style.border = "1px #CCCCCC solid";
			}
			item.onclick = function (e)
			{
				this.style.border = "1px #CCCCCC solid";
				obj.title.innerText = this.title;
				obj.Drop(false);
				var id = it_id;
				obj.selectedItem = this.id;
				if(item.handler)
					item.handler();
				else
					obj.OnSelect();
			}
			ar_EVENTS.push([item,"mouseover"]);
			ar_EVENTS.push([item,"mouseout"]);
			ar_EVENTS.push([item,"click"]);
			item.innerHTML = this.items[it_id].value;
			item.title = this.items[it_id].title;
			item.id = this.items[it_id].id;
			item.handler = this.items[it_id].handler;
		}
		this.pDropDown.AddContent(itemstable);

		c2 = null;
		c1 = null;
		r = null;
		item = null;
		itemstable = null;
	}

	BXCombo.prototype.Drop = function(bDrop)
	{
		if(bDrop)
		{
			var pos = GetRealPos(this.pWnd);
			this.pDropDown.Show(pos["left"], pos["bottom"], this.dx, this.dy)
		}
		else
			this.pDropDown.Hide();
		this.pMainObj.SetFocus();
	}

	BXCombo.prototype.OnToolbarChangeDirection = function (bVertical)
	{
		if(bVertical)
			this.title.style.width = "0px";
		else
			this.title.style.width = this.title_size;
		this.pWnd.className = 'bxcombo';
	}
}

function BXTBSelect()
{
	//alert('BXTBSelect');
}

//BXCombo.prototype = new BXToolbarItem;
BXTBSelect.prototype = new BXCombo;
//ar_OBJECTS.push(BXTBSelect.prototype);
//BXTBSelect.prototype = new BXCombo;

function FontStyleListSelect()
{
	var obj = this;
	obj.className = 'FontStyleListSelect';
	obj.items = [];
}
FontStyleListSelect.prototype = new BXTBSelect;
//ar_OBJECTS.push(FontStyleListSelect.prototype);

function FontSizeListSelect()
{
	var obj = this;
	obj.className = 'FontSizeListSelect';
	obj.items = [];
	obj.__Show = obj.Show;
	obj.Show = function ()
	{
		for(var ix=0; ix<obj.params[5].length; ix++)
			obj.AddRow(ix, '<span style="font-size:' + obj.params[5][ix] + ';">' + obj.params[5][ix] + '</span>', obj.params[5][ix]);
		obj.__Show();
	}

	FontSizeListSelect.prototype.OnSelect = function ()
	{
		var item_id = this.selectedItem;
		this.pMainObj.WrapSelectionWith('span', {'style': 'font-size:'+this.items[item_id].title+';'});
		return true;
	}
}

// **************************************************************************************
function BXDialog()
{
	BXDialog.prototype._Create = function()
	{
		var floatDiv = document.getElementById("BX_editor_dialog");
		if(floatDiv)
			this.Close(floatDiv);

		if(!this.params || typeof(this.params) != "object")
			this.params = {};

		this.params.pMainObj = this.pMainObj;
		pObj = window.pObj = this;

		oPrevRange = BXGetSelectionRange(this.pMainObj.pEditorDocument,this.pMainObj.pEditorWindow);
		if(document.getElementById("BX_editor_dialog"))
			this.Close();

		var div = document.body.appendChild(document.createElement("DIV"));
		div.id = "BX_editor_dialog";
		div.className = "editor_dialog";
		div.style.position = 'absolute';
		div.style.width = this.width;
		//div.style.height = this.height;
		div.style.left = '-1000px';
		div.style.top = '-1000px';
		div.style.zIndex = 2006;

		div.innerHTML =
			'<div class="title">'+
			'<table cellspacing="0" width="100%" border="0">'+
			'	<tr>'+
			'		<td width="100%" class="title-text" onmousedown="jsFloatDiv.StartDrag(arguments[0], document.getElementById(\'BX_editor_dialog\'));" id="BX_editor_dialog_title">'+'Title'+'</td>'+
			'		<td width="0%"><a class="close" href="javascript:pObj.Close();" onclick="pObj.Close(); return false;" title="'+'Close'+'"></a></td></tr>'+
			'</table>'+
			'</div>'+
			'<div class="content">'+
			''+
			'</div>';

		this.floatDiv = div;
		this.content = jsUtils.FindChildObject(this.floatDiv, 'div', 'content');
		var _this_content = this.content;

		if (window.jsPopup)
			jsPopup.DenyClose();

		jsUtils.addEvent(document, "keypress", pObj.OnKeyPress);
		jsUtils.addCustomEvent('onEditorLostSession', this._Create, {}, this);

		this.bFirst = false;
		window.oDialogTitle = document.getElementById('BX_editor_dialog_title');

		var ShowResult = function(result, bFastMode)
		{
			CloseWaitWindow();
			if (!bFastMode)
				_this_content.innerHTML = result;
			var w = jsUtils.GetWindowInnerSize();
			var s = jsUtils.GetWindowScrollPos();
			var left = parseInt(s.scrollLeft + w.innerWidth / 2 - div.offsetWidth / 2);
			var top = parseInt(s.scrollTop + w.innerHeight / 2 - div.offsetHeight / 2) - 50;
			jsFloatDiv.Show(div, left, top, 5);
			jsFloatDiv.AdjustShadow(div);
		}
		ShowWaitWindow();

		var potRes = this.GetFastDialog();
		if (potRes !== false)
			return ShowResult(potRes, true);

		var r = new JCHttpRequest();
		r.Action = ShowResult;
		var _add_str = '';
		if (this.not_use_default)
			_add_str = '&not_use_default=Y';
		var handler = this.handler ? '/bitrix/admin/' + this.handler : editor_dialog_path;
		
		var addUrl = (this.params.PHPGetParams ? this.params.PHPGetParams : '') + '&mode=public';
		r.Send(handler+'?'+(debug_mode ? 'debug_mode=Y&' : '')+'lang='+BXLang+'&site='+BXSite+_add_str+'&name='+this.name+addUrl, this.params);
	};

	BXDialog.prototype.CheckFocus = function()
	{
		if(this.pWnd && !this.pWnd.closed)
		{
			this.pWnd.focus();
			return false;
		}
		else
			delCustomElementEvent(window.top.parent, 'focus', this.CheckFocus);
	};

	BXDialog.prototype.OnKeyPress = function(e)
	{
		if(!e)
			e = window.event

		if(e.keyCode == 27)
		{
			jsUtils.removeEvent(document, "keypress", pObj.OnKeyPress);
			pObj.Close();
		}
	};

	BXDialog.prototype.Close = function(floatDiv)
	{
		if (!floatDiv)
			floatDiv = this.floatDiv;
		jsUtils.removeCustomEvent('onEditorLostSession', this._Create);
		jsFloatDiv.Close(floatDiv);
		floatDiv.parentNode.removeChild(floatDiv);
		if (window.jsPopup)
			jsPopup.AllowClose();
		//floatDiv = null;
		//pObj = null;
	};

	BXDialog.prototype.GetFastDialog = function()
	{
		if (!window.arEditorFastDialogs[this.name])
			return false;
		var o = window.arEditorFastDialogs[this.name](this);
		var res = "";
		res += o.innerHTML;
		res += "";
		this.content.innerHTML = res;
		oDialogTitle.innerHTML = o.title;
		if (o.OnLoad)
			o.OnLoad();
		return res;
	};
}


function BXGetFastDialog(name, pMainObj, cont, params)
{
	if (!window.arEditorFastDialogs[name])
		return false;
	var o = window.arEditorFastDialogs[name](pMainObj, params);
	var res = "";
	res += o.innerHTML;
	res += "";
	cont.innerHTML = res;
	oDialogTitle.innerHTML = o.title;
	if (o.OnLoad)
		o.OnLoad();
	return res;
}


BXHTMLEditor.prototype.OpenEditorDialog = function(dialogName, obj, width, arParams, notUseDefaultButtons)
{
	arParams = arParams || {};
	width = (parseInt(width) || 500) + 'px';
	this.CreateCustomElement("BXDialog", {width: width, name: dialogName, params: arParams, not_use_default: notUseDefaultButtons});
}