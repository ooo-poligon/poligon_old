var components2_js = true;

function BXComponents2Taskbar()
{
	var oTaskbar = this;

	BXComponents2Taskbar.prototype.OnTaskbarCreate = function ()
	{
		if (window.lca && !_$lca_to_output)
			this.pMainObj.AddEventHandler('SaveContentAfter', this.OnSaveLCA, this);

		this.icon_class = 'tb_icon_components2';
		this.iconDiv.className = 'tb_icon ' + this.icon_class;
		this.cellTitle.setAttribute("__bxtagname", "_taskbar_cached"); // need for correct context menu for taskbar title
		var obj = this;
		if (lca)
			_$LCAContentParser_execed = false;
		oTaskbar.pCellComp = oTaskbar.CreateScrollableArea(oTaskbar.pDataCell);
		oTaskbar.pCellComp.style.width = "100%";
		oTaskbar.pCellComp.style.height = "100%";
		this.pMainObj.pComponent2Taskbar = this;
		this.FetchArray();
		oBXEditorUtils.addPropertyBarHandler('component2', oTaskbar.ShowProps);
		emptyRow = null;
		table = null;
	}

	BXComponents2Taskbar.prototype.FetchArray = function (clear_cache)
	{
		var loadComp2 = function()
		{
			oTaskbar.BuildList(window.arComp2Elements);
			window.as_arComp2Elements = [];
			var __len = window.arComp2Elements.length;
			for (var i = 0; i < __len; i++)
				window.as_arComp2Elements[window.arComp2Elements[i].name] = window.arComp2Elements[i];

			oTaskbar.pMainObj.oBXWaitWindow.Hide();
		}

		CHttpRequest.Action = function(result)
		{
			try
			{
				setTimeout(loadComp2, 10);
			}
			catch(e)
			{
				err_text = "ERROR in BXComponents2Taskbar.FetchArray(): ";
				if ((eind = result.indexOf('Fatal error')) != -1)
					err_text += "\n PHP error: \n\n....." + result.substr(eind - 10);
				alert(err_text);
			}
		}

		if (window.arComp2Elements)
			loadComp2();
		else
			CHttpRequest.Send('/bitrix/admin/fileman_load_components2.php?lang='+BXLang+'&site='+BXSite+'&load_tree=Y'+(clear_cache === true ? '&clear_comp2_cache=Y' : ''));
	}

	BXComponents2Taskbar.prototype.BuildList = function (__arElements)
	{
		var len = __arElements.length;
		if (len == 0) // if any allowed components in the list - close taskbar
			oTaskbar.Close();

		for (var i = 0; i < len; i++)
		{
			__arElements[i].tagname = 'component2';
			__arElements[i].childElements = [];
			__arElements[i].params.name = __arElements[i].name;

			if (__arElements[i].isGroup && !__arElements[i].path)
				oTaskbar.AddElement(__arElements[i], oTaskbar.pCellComp, __arElements[i].path);
		}
		this.fullyLoaded = false;
	}

	BXComponents2Taskbar.prototype.PreBuildList = function ()
	{
		var __arElements = window.arComp2Elements;
		var len = __arElements.length;
		for (var i = 0; i < len; i++)
		{
			if (!__arElements[i].isGroup || __arElements[i].path)
				oTaskbar.AddElement(__arElements[i], oTaskbar.pCellComp, __arElements[i].path);
		}
		this.fullyLoaded = true;
	}

	BXComponents2Taskbar.prototype.ShowProps = function (_bNew, _pTaskbar, _pElement, bReloadProps)
	{
		var __arProps = oBXEditorUtils.getCustomNodeParams(_pElement);
		if (bReloadProps === true)
		{
			var str;
			for (var j in __arProps.paramvals)
			{
				_str = __arProps.paramvals[j];
				if (typeof _str == 'string' && _str.substr(0, 6).toLowerCase() == 'array(')
					__arProps.paramvals[j] = eval(__arProps.paramvals[j]);
			}
			var postData = oBXEditorUtils.ConvertArray2Post(__arProps.paramvals, 'curval');
			oTaskbar.LoadComp2Params(__arProps.name, oTaskbar.BXShowComponent2Panel, oTaskbar, [_bNew, _pTaskbar, _pElement], 'POST', postData);
		}
		else if (window._bx_reload_template_props)
		{
			oTaskbar.deleteTemplateParams(_pElement);
			var postData = oBXEditorUtils.ConvertArray2Post(__arProps.paramvals,'curval');
			oTaskbar.setCompTemplate(_pElement,this);
			__arProps = oBXEditorUtils.getCustomNodeParams(_pElement);
			oTaskbar.loadTemplateParams(__arProps.name, __arProps.template, oTaskbar.ShowProps, oTaskbar, [_bNew, _pTaskbar, _pElement, false], "POST", postData);
		}
		else if (window.as_arComp2Params[__arProps.name + __arProps.__bx_id])
			oTaskbar.BXShowComponent2Panel(_bNew, _pTaskbar,_pElement);
		else
		{
			var str;
			for (var j in __arProps.paramvals)
			{
				_str = __arProps.paramvals[j];
				if (typeof _str == 'string' && _str.substr(0, 6).toLowerCase()=='array(')
					__arProps.paramvals[j] = eval(__arProps.paramvals[j]);
			}
			var postData = oBXEditorUtils.ConvertArray2Post(__arProps.paramvals,'curval');
			oTaskbar.LoadComp2Params(__arProps.name, oTaskbar.BXShowComponent2Panel, oTaskbar, [_bNew,  _pTaskbar, _pElement], 'POST', postData);
		}
	}

	BXComponents2Taskbar.prototype.ClearCache = function ()
	{
		oTaskbar.pMainObj.oBXWaitWindow.Show();
		oTaskbar.RemoveElementList(oTaskbar.pCellComp);
		window.arComp2Elements = false;
		oTaskbar.FetchArray(true);
	}

	BXComponents2Taskbar.prototype.BXShowComponent2Panel = function (_bNew, _pTaskbar, _pElement)
	{
		var arAllProps = oBXEditorUtils.getCustomNodeParams(_pElement);
		var arProps = window.as_arComp2Params[arAllProps.name + arAllProps.__bx_id];
		_pTaskbar.arElements = [];
		if (!arProps)
			return;

		var arCurrentTooltips = arComp2Tooltips[arAllProps.name];
		var fChange_SEF_MODE;
		oBXEditorUtils.BXRemoveAllChild(_pTaskbar.pCellProps);
		__arProps = {};

		// **** Function handle changes in properties ****
		var fChange = function (e)
		{
			var arAllFields = [];
			var __k;
			function addel(arEls)
			{
				var el,__name;
				for(var i=0; i<arEls.length; i++)
				{
					if(!isYes(arEls[i]["__exp"])) continue;
					el = arEls[i];
					if(el["name"].substr(el["name"].length-2, 2) == '[]')
					{
						__name = el["name"].substr(0, el["name"].length-2);
						if (!arAllFields[__name])
							arAllFields[__name] = [];

						arAllFields[__name].push(el);
					}
					else
						arAllFields[el["name"]] = el;
				}
			}

			var propID, i, j, val;

			addel(_pTaskbar.pCellProps.getElementsByTagName("SELECT"));
			addel(_pTaskbar.pCellProps.getElementsByTagName("INPUT"));
			addel(_pTaskbar.pCellProps.getElementsByTagName("TEXTAREA"));

			__arProps = {};

			try {arVA;}catch(e){arVA = [];}
			var _len1 = "SEF_URL_TEMPLATES_".length;
			var _len2 = "VARIABLE_ALIASES_".length;

			for(i=0; i < _pTaskbar.arElements.length; i++)
			{
				propID = _pTaskbar.arElements[i];
				val = arAllFields[propID];
				if(val && val.selectedIndex == 0 && arAllFields[propID + '_alt'])
					val = arAllFields[propID+'_alt'];

				if(propID.substr(0,_len1) == "SEF_URL_TEMPLATES_")
				{
					if (fChange_SEF_MODE === true)
					{
						if (val && val.value)
							_val = val.value;
						else if(arAllProps.paramvals[propID])
							_val = arAllProps.paramvals[propID];
						else
							continue;
						__k = propID.substr(_len1);
						arVA[__k] = catchVariableAliases(_val);
					}
					else
						continue;
				}
				if(!val)
				{
					if (arAllProps.paramvals[propID])
						__arProps[propID] = arAllProps.paramvals[propID];
						continue;
				}
				if(val.tagName) // one element
				{
					//If SEF_MODE - set fChange_SEF_MODE = true
					if (propID=="SEF_MODE")
					{
						if (val.tagName.toUpperCase()=="SELECT")
							fChange_SEF_MODE = isYes(val.value);
						else if (val.tagName.toUpperCase()=="INPUT" && val.type=="checkbox")
							fChange_SEF_MODE = isYes(val.value);
					}

					if(val.tagName.toUpperCase() == "SELECT")
					{
						for(j=0; j<val.length; j++)
						{
							if(val[j].selected && val[j].value!='')
								__arProps[propID] = val[j].value;
						}
					}
					else
					{
						__arProps[propID] = val.value;
					}
				}
				else
				{
					var __arProps_temp = [];
					for(k=0; k<val.length; k++)
					{
						if(val[k].tagName.toUpperCase() == "SELECT")
						{
							for(j=0; j<val[k].length; j++)
								if(val[k][j].selected && val[k][j].value!='')
									__arProps_temp.push(val[k][j].value);
						}
						else
							__arProps_temp.push(val[k].value);
					}
					__arProps[propID] = _BXArr2Str(__arProps_temp);
					__arProps_temp = null;
				}
			}
			arAllProps.paramvals = __arProps;
			oBXEditorUtils.setCustomNodeParams(_pElement, arAllProps);
		}

		var __paramvals = [];
		// **** function display parametr ****
		var fDisplay = function(arProp, __tProp, oCont)
		{
			propertyID = arProp.param_name;
			_pTaskbar.arElements.push(propertyID);
			var refreshedByOnclick = false;

			var _len1 = "SEF_URL_TEMPLATES_".length;
			var _len2 = "VARIABLE_ALIASES_".length;
			var tProp;

			res = '';
			arUsedValues = [];
			arPropertyParams = arProp;

			if (arAllProps.paramvals && arAllProps.paramvals[propertyID] != undefined)
				arValues = arAllProps.paramvals[propertyID];
			else
				arValues = arPropertyParams.DEFAULT || '';

			__paramvals[propertyID] = arValues;
			if(!isYes(arPropertyParams.MULTIPLE))
				arPropertyParams.MULTIPLE = "N";
			if(!arPropertyParams.TYPE)
				arPropertyParams.TYPE = "STRING";
			if(!arPropertyParams.CNT)
				arPropertyParams.CNT = 0;
			if(!arPropertyParams.SIZE)
				arPropertyParams.SIZE = 0;
			if(!arPropertyParams.ADDITIONAL_VALUES)
				arPropertyParams.ADDITIONAL_VALUES = 'N';
			if(!arPropertyParams.ROWS)
				arPropertyParams.ROWS = 0;
			if(!arPropertyParams.COLS || parseInt(arPropertyParams.COLS) < 1)
				arPropertyParams.COLS = '30';

			if(isYes(arPropertyParams.MULTIPLE) && typeof(arValues) != 'object')
			{
				if(!arValues)
					arValues = [];
			}
			else if(arPropertyParams.TYPE == "LIST" && typeof(arValues) != 'object')
				arValues = Array(arValues);

			if(isYes(arPropertyParams.MULTIPLE))
			{
				arPropertyParams.CNT = parseInt(arPropertyParams.CNT);
				if (arPropertyParams.CNT<1)
					arPropertyParams.CNT = 1;
			}
			if (isYes(arPropertyParams.HIDDEN))
				return;

			// If SEF = ON : show SEF_URL_TEMPLATES and SEF_FOLDER
			//     SEF = OFF: show VARIABLE_ALIASES
			if ((propertyID.substr(0,17)=="SEF_URL_TEMPLATES" || propertyID=="SEF_FOLDER") && _pTaskbar.__SEF_MODE === false)
				return;
			else if (propertyID.substr(0,16)=="VARIABLE_ALIASES" && _pTaskbar.__SEF_MODE === true)
				return;

			if (!arPropertyParams.PARENT)
			{
				arPropertyParams.PARENT = '__bx_additional_group';
				arPropertyParams.group_title = BX_MESS.ADD_INSERT;
			}
			// If it's grouped property
			if (arPropertyParams.PARENT)
			{
				if (!arGroups[arPropertyParams.PARENT])
				{
					arGroups[arPropertyParams.PARENT] =
						{
							title : arPropertyParams.group_title,
							datacell : oTaskbar.GetPropGroupDataCell(arPropertyParams.PARENT,arPropertyParams.group_title, oCont, [arAllProps.name])
						};
					_dataCell = arGroups[arPropertyParams.PARENT].datacell;
				}
				else
				{
					_dataCell = arGroups[arPropertyParams.PARENT].datacell;
				}

				if (_dataCell.getElementsByTagName("TABLE").length>0)
					tProp = _dataCell.getElementsByTagName("TABLE")[0];
				else
					tProp =  _dataCell.appendChild(BXCreateElement('TABLE', {className : "bxtaskbarprops", cellSpacing: 0, cellPadding: 1}, {width: '100%'}, document));
			}
			else
			{
				tProp = __tProp;
				oCont.appendChild(tProp);
			}

			row = tProp.insertRow(-1);
			row.className = "bxtaskbarpropscomp";
			cell = row.insertCell(-1);
			cell.width = "50%";
			cell.align = "right";
			cell.vAlign = "top";
			cell.innerHTML = "<SPAN>"+oTaskbar.Remove__script__(arPropertyParams.NAME)+":</SPAN>";
			cell = row.insertCell(-1);
			cell.width = "50%";

			arPropertyParams.TYPE = arPropertyParams.TYPE.toUpperCase();

			if (propertyID == "SEF_MODE")
				arPropertyParams.TYPE = "CHECKBOX";
			//* * * * * * Displaying data * * * * * *
			switch(arPropertyParams.TYPE)
			{
			case "LIST":
				arPropertyParams.SIZE = (isYes(arPropertyParams.MULTIPLE) && (parseInt(arPropertyParams.SIZE)<=1 || isNaN(parseInt(arPropertyParams.SIZE))) ? '3' : arPropertyParams.SIZE);
				if(parseInt(arPropertyParams.SIZE)<=0 || isNaN(parseInt(arPropertyParams.SIZE)))
					arPropertyParams.SIZE = 1;
				pSelect = _pTaskbar.pMainObj.CreateElement("SELECT", {size: arPropertyParams.SIZE, name: propertyID + (isYes(arPropertyParams.MULTIPLE) ? '[]' : ''), __exp: 'Y', onchange: fChange, multiple: isYes(arPropertyParams.MULTIPLE)});
				cell.appendChild(pSelect);
				if(!arPropertyParams.VALUES)
					arPropertyParams.VALUES = [];
				var _arValues = [];
				if (typeof arValues == 'string')
				{
					if (arValues.substr(0, 2) == '={')
						arValues = JS_stripslashes(arValues.substr(2, arValues.length - 3));
					if (arValues.substr(0, 6).toLowerCase() == 'array(')
						_arValues = _BXStr2Arr(arValues);
				}
				bFound = false;
				for(opt_val in arPropertyParams.VALUES)
				{
					bSel = false;
					oOption = new Option(JS_stripslashes(arPropertyParams.VALUES[opt_val]), opt_val, false, false);
					pSelect.options.add(oOption);

					if(pSelect.options.length <= 1)
						setTimeout(__BXSetOptionSelected(oOption, false), 1);

					key = BXSearchInd(arValues, opt_val);
					if(key >= 0)
					{
						bFound = true;
						arUsedValues[key]=true;
						bSel = true;
						if (propertyID=="SEF_MODE")
							_pTaskbar.__SEF_MODE = isYes(opt_val);
						setTimeout(__BXSetOptionSelected(oOption, true), 1);
					}
					else if(_arValues[opt_val])
					{
						bFound = true;
						arUsedValues[opt_val]=true;
						bSel = true;
						setTimeout(__BXSetOptionSelected(oOption, true), 1);
						delete _arValues[opt_val];
					}
				}

				if(arPropertyParams.ADDITIONAL_VALUES!='N')
				{
					oOption = document.createElement("OPTION");
					oOption.value = '';
					oOption.selected = !bFound;
					oOption.text = (isYes(arPropertyParams.MULTIPLE) ? BX_MESS.TPropCompNS : BX_MESS.TPropCompOth)+' ->';
					pSelect.options.add(oOption, 0);
					oOption = null;

					if(isYes(arPropertyParams.MULTIPLE))
					{
						if (typeof(arValues)=='string')
							arValues = _arValues;

						for(k in arValues)
						{
							if(arUsedValues[k])
								continue;
							cell.appendChild(_pTaskbar.pMainObj.CreateElement("BR"));
							if(arPropertyParams.ROWS>1)
							{
								var oTextarea = _pTaskbar.pMainObj.CreateElement("TEXTAREA", {cols: (isNaN(arPropertyParams.COLS) ? '20' : arPropertyParams.COLS), value: JS_stripslashes(arValues[k]), name: propertyID+'[]', __exp: 'Y', onchange: fChange});
								cell.appendChild(oTextarea);
								oTextarea = null;
							}
							else
							{
								var oInput = _pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (isNaN(arPropertyParams.COLS)?'20':arPropertyParams.COLS), 'value': JS_stripslashes(arValues[k]), 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange});
								cell.appendChild(oInput);
								oInput = null;
							}
						}

						for(k=0; k<arPropertyParams.CNT; k++)
						{
							cell.appendChild(_pTaskbar.pMainObj.CreateElement("BR"));
							if(arPropertyParams.ROWS>1)
							{
								var oTextarea = _pTaskbar.pMainObj.CreateElement("TEXTAREA", {'cols': (isNaN(arPropertyParams.COLS)?'20':arPropertyParams.COLS), 'value': '', 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange});
								cell.appendChild(oTextarea);
								oTextarea = null;
							}
							else
							{
								var oInput = _pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (isNaN(arPropertyParams.COLS)?'20':arPropertyParams.COLS), 'value': '', 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange});
								cell.appendChild(oInput);
								oInput = null;
							}
						}

						var oInput = _pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'button', 'value': '+', 'pMainObj': _pTaskbar.pMainObj,  'arPropertyParams': arPropertyParams});
						xCell = cell.appendChild(oInput);
						oInput = null;
						var oBR = _pTaskbar.pMainObj.CreateElement("BR");
						cell.appendChild(oBR);
						oBR = null;
						xCell.propertyID = propertyID;
						xCell.fChange = fChange;
						xCell.onclick = function ()
						{
							this.parentNode.insertBefore(this.pMainObj.CreateElement("BR"), this);
							if(this.arPropertyParams['ROWS'] && this.arPropertyParams['ROWS']>1)
							{
								var oTextarea = this.pMainObj.CreateElement("TEXTAREA", {'cols': (!this.arPropertyParams['COLS'] || isNaN(this.arPropertyParams['COLS'])?'20':this.arPropertyParams['COLS']), 'value': '', 'name': this.propertyID+'[]', '__exp': 'Y', 'onchange': this.fChange});
								this.parentNode.insertBefore(oTextarea, this);
								oTextarea = null;
							}
							else
							{
								var oInput = this.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (!this.arPropertyParams['COLS'] || isNaN(this.arPropertyParams['COLS'])?'20':this.arPropertyParams['COLS']), 'value': '', 'name': this.propertyID+'[]', '__exp': 'Y', 'onchange': this.fChange});
								this.parentNode.insertBefore(oInput, this);
								oInput = null;
							}
						}
					}
					else
					{
						val = '';
						for(k=0; k<arValues.length; k++)
						{
							if(arUsedValues[k])
								continue;
							val = arValues[k];
							break;
						}

						if(arPropertyParams['ROWS'] && arPropertyParams['ROWS']>1)
							alt = cell.appendChild(_pTaskbar.pMainObj.CreateElement("TEXTAREA", {'cols': (!arPropertyParams['COLS'] || isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': val, 'disabled': bFound, 'name': propertyID+'_alt', '__exp': 'Y', 'onchange': fChange}));
						else
							alt = cell.appendChild(_pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (!arPropertyParams['COLS'] || isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': val, 'disabled': bFound, 'name': propertyID+'_alt', '__exp': 'Y', 'onchange': fChange}));

						pSelect.pAlt = alt;

						if (isYes(arPropertyParams.REFRESH))
							pSelect.onchange = function (e){fChange(e);_this.ShowProps(_bNew, _pTaskbar, _pElement,true);};
						else
							pSelect.onchange = function (e){this.pAlt.disabled = (this.selectedIndex!=0); fChange(e);};
					}
				}

				if (isYes(arPropertyParams.REFRESH))
					pSelect.onchange = function (e){fChange(e);_this.ShowProps(_bNew, _pTaskbar, _pElement,true);};

				if (propertyID=="SEF_MODE")
				{
					pSelect.onchange = function(e)
						{
							_pTaskbar.__SEF_MODE = isYes(this.value);
							fChange(e);
							_this.ShowProps(_bNew, _pTaskbar, _pElement, false);
						};
				}

				break;
			case "CHECKBOX":
				pCheckbox = _pTaskbar.pMainObj.CreateElement("INPUT", {'type':'checkbox', 'name': propertyID, '__exp': 'Y'});
				cell.appendChild(pCheckbox);

				if (arValues)
					oBXEditorUtils.setCheckbox(pCheckbox, isYes(arValues));
				else if (arPropertyParams.DEFAULT != undefined)
					oBXEditorUtils.setCheckbox(pCheckbox, isYes(arPropertyParams.DEFAULT));
				else
					oBXEditorUtils.setCheckbox(pCheckbox,false);

				if (propertyID=="SEF_MODE")
				{
					pCheckbox.onclick = function(e)
						{
							oBXEditorUtils.setCheckbox(this,this.checked);
							_pTaskbar.__SEF_MODE = this.checked;
							fChange(e);
							_this.ShowProps(_bNew, _pTaskbar, _pElement,false);
						}
					_pTaskbar.__SEF_MODE = pCheckbox.checked;
				}
				else if(isYes(arPropertyParams.REFRESH))
				{
					pCheckbox.onclick = function(e)
					{
						oBXEditorUtils.setCheckbox(this,this.checked);
						fChange(e);
						_this.ShowProps(_bNew, _pTaskbar, _pElement,true);
					}
					refreshedByOnclick = true;

				}
				else
				{
					pCheckbox.onclick = function(e)
						{
							oBXEditorUtils.setCheckbox(this,this.checked);
							fChange(e);
						}
				}

				__paramvals[propertyID] = pCheckbox.value;

				break;
			case "STYLELIST":
				_alert("STYLELIST");
				break;
			default: // 'STRING' OR 'FILE' OR 'COLORPICKER'
				if (arPropertyParams.TYPE == 'COLORPICKER' || arPropertyParams.TYPE == 'FILE')
				{
					refreshedByOnclick = true;
					arPropertyParams.ROWS = 1;
					arPropertyParams.MULTIPLE = 'N';
					arPropertyParams.COLS = (arPropertyParams.TYPE == 'COLORPICKER') ? 6 : 40;
				}
				
				if(isYes(arPropertyParams.MULTIPLE))
				{
					if (typeof arValues == 'string')
					{
						var _arValues = [];
						if (arValues.substr(0, 2) == '={')
							arValues = JS_stripslashes(arValues.substr(2, arValues.length - 3));
						if (arValues.substr(0, 6).toLowerCase() == 'array(')
							_arValues = _BXStr2Arr(arValues);
					}
					bBr = false;
					for(val in ((typeof arValues == 'object') ? arValues : _arValues))
					{
						if(bBr)
							cell.appendChild(_pTaskbar.pMainObj.CreateElement("BR"));
						else
							bBr = true;
						val = val.replace(/(\\)?\\n/g, "\n");
						if(arPropertyParams.ROWS > 1)
							cell.appendChild(_pTaskbar.pMainObj.CreateElement("TEXTAREA", {cols: parseInt(arPropertyParams.COLS) || 20, value: JS_stripslashes(val), name: propertyID+'[]', __exp: 'Y', onchange: fChange}));
						else
							cell.appendChild(_pTaskbar.pMainObj.CreateElement("INPUT", {type: 'text', size: parseInt(arPropertyParams.COLS) || 20, 'value': JS_stripslashes(val), 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange}));
					}

					for(k=0; k<arPropertyParams.CNT; k++)
					{
						if(bBr)
							cell.appendChild(_pTaskbar.pMainObj.CreateElement("BR"));
						else
							bBr = true;

						if(arPropertyParams.ROWS>1)
							cell.appendChild(_pTaskbar.pMainObj.CreateElement("TEXTAREA", {'cols': (isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': '', 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange}));
						else
							cell.appendChild(_pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': '', 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange}));
					}

					xCell = cell.appendChild(_pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'button', 'value': '+', 'pMainObj': _pTaskbar.pMainObj,  'arPropertyParams': arPropertyParams}));
					xCell.propertyID = propertyID;
					xCell.fChange = fChange;
					xCell.onclick = function ()
					{
						this.parentNode.insertBefore(this.pMainObj.CreateElement("BR"), this);
						if(this.arPropertyParams.ROWS && this.arPropertyParams.ROWS > 1)
							this.parentNode.insertBefore(this.pMainObj.CreateElement("TEXTAREA", {'cols': (!this.arPropertyParams.COLS || isNaN(this.arPropertyParams.COLS) ? '20' : this.arPropertyParams.COLS), 'value': '', 'name': this.propertyID+'[]', '__exp': 'Y', 'onchange': this.fChange}), this);
						else
							this.parentNode.insertBefore(this.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (!this.arPropertyParams.COLS || isNaN(this.arPropertyParams['COLS'])?'20':this.arPropertyParams.COLS), 'value': '', 'name': this.propertyID+'[]', '__exp': 'Y', 'onchange': this.fChange}), this);
					}
					cell.appendChild(_pTaskbar.pMainObj.CreateElement("BR"));
				}
				else
				{
					//val = arValues;
					val = arValues.replace(/(\\)?\\n/g, "\n");
					if(arPropertyParams.ROWS && arPropertyParams.ROWS > 1)
						cell.appendChild(_pTaskbar.pMainObj.CreateElement("TEXTAREA", {'cols': (!arPropertyParams.COLS || isNaN(arPropertyParams.COLS)?'20':arPropertyParams.COLS), 'value': JS_stripslashes(val), 'name': propertyID, '__exp': 'Y', 'onchange': fChange}));
					else
						cell.appendChild(_pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (!arPropertyParams.COLS || isNaN(arPropertyParams.COLS)?'20':arPropertyParams.COLS), 'value': JS_stripslashes(val), 'name': propertyID, '__exp': 'Y', 'onchange': fChange}));
				}
				if (arPropertyParams.TYPE == 'FILE')
				{
					xCell = cell.appendChild(_pTaskbar.pMainObj.CreateElement("INPUT", {type: 'button', value: '...', pMainObj: _pTaskbar.pMainObj}));
					//xCell.onclick = function(){_this.ShowProps(_bNew, _pTaskbar, _pElement,true);};
					xCell.onclick = function(){alert('!');};
				}
				break;
			}

			if(isYes(arPropertyParams.REFRESH) && !refreshedByOnclick)
			{
				xCell = cell.appendChild(_pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'button', 'value': 'ok', 'pMainObj': _pTaskbar.pMainObj,  'arPropertyParams': arPropertyParams}));
				xCell.onclick = function(){_this.ShowProps(_bNew, _pTaskbar, _pElement,true);};
			}

			// #########################   TOOLTIP   ####################################
			if (arCurrentTooltips[propertyID] && _pTaskbar.pMainObj.showTooltips4Components)
			{
				oBXHint = new BXHint(arCurrentTooltips[propertyID]);
				cell.appendChild(oBXHint.oIcon);
				oBXHint.oIcon.style.marginLeft = "5px";
			}
		}

		var BXCreateGroups = function(cn, oCont)
		{
			if (window.arComp2ParamsGroups[cn])
			{
				var groups = window.arComp2ParamsGroups[cn];
				for (var _key in groups)
				{
					arGroups[_key] =
					{
						title : groups[_key],
						datacell : oTaskbar.GetPropGroupDataCell(_key, groups[_key], oCont, [arAllProps.name])
					};
				}
			}
		};

		//****** DISPLAY TITLE *******
		var compTitle = window.as_arComp2Elements[arAllProps.name].title;
		var compDesc = window.as_arComp2Elements[arAllProps.name].params.DESCRIPTION;
		var bComplex = isYes(window.as_arComp2Elements[arAllProps.name].complex);

		var tCompTitle = document.createElement("TABLE");
		tCompTitle.className = "componentTitle";
		var row = tCompTitle.insertRow(-1);
		var cell = row.insertCell(-1);
		cell.innerHTML = "<SPAN class='title'>"+compTitle+"  ("+arAllProps.name+")</SPAN><BR /><SPAN class='description'>"+(bComplex ? BX_MESS.COMPLEX_COMPONENT : "")+compDesc+"</SPAN>";
		cell.className = "titlecell";
		cell.width = "100%";
		var _helpCell = row.insertCell(-1);
		_helpCell.className = "helpicon";
		//var _helpicon = oTaskbar.pMainObj.CreateElement("IMG", {"src": "/bitrix/images/fileman/htmledit2/comp2help.gif", "width": 16,"height":16,"title": "Help", "alt": "Help"});
		//_helpicon.onclick = function(e){};
		//_helpCell.appendChild(_helpicon);
		_pTaskbar.pCellProps.appendChild(tCompTitle);
		_helpicon = null;
		tCompTitle = null;
		var arGroups = [];

		// ***********************************************
		//DISPLAY COMPONENT TEMPLATE PARAMETERS
		// ***********************************************
		if (arAllProps.template == undefined)
			arAllProps.template = "";

		var templList = document.createElement("SELECT");
		templList.id = '__bx_comp2templ_select';

		templList.onchange = function (e)
		{
			oTaskbar.deleteTemplateParams(_pElement);
			var postData = oBXEditorUtils.ConvertArray2Post(arAllProps.paramvals,'curval');
			oTaskbar.setCompTemplate(_pElement,this);
			arAllProps = oBXEditorUtils.getCustomNodeParams(_pElement);
			oTaskbar.loadTemplateParams(arAllProps.name, arAllProps.template, oTaskbar.ShowProps,oTaskbar, [_bNew, _pTaskbar, _pElement, false], "POST", postData);
		};
		var oOption, _el, k, site_template;
		var __arTemplates = as_arComp2Templates[arAllProps.name];
		var _len = __arTemplates.length;
		var oCont = _pTaskbar.pCellProps;

		if (_len>0)
		{
			var _T_datacell = oTaskbar.GetPropGroupDataCell('templateParams', BX_MESS.COMPONENT_TEMPLATE, _pTaskbar.pCellProps, [arAllProps.name]);
			var tTProp = BXCreateElement('TABLE', {id: '__bx_tProp', className : "bxtaskbarprops", cellSpacing: 0, cellPadding: 1}, {width: '100%'}, document);

			row = tTProp.insertRow(-1);
			row.className = "bxtaskbarpropscomp";
			cell = row.insertCell(-1);
			cell.width = "50%";
			cell.align = "right";
			cell.vAlign = "top";
			var oSpan = _pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML': BX_MESS.COMPONENT_TEMPLATE+':'});
			cell.appendChild(oSpan);
			oSpan = null;
			cell = row.insertCell(-1);
			cell.width = "50%";
			cell.appendChild(templList);
			_T_datacell.appendChild(tTProp);

			//Displaying component template list
			for (var j = 0;j < _len; j++)
			{
				_el = __arTemplates[j];
				oOption = document.createElement("OPTION");
				site_template = '';
				if (_el.template != '')
					for (k in arBXTemplates)
					{
						if (arBXTemplates[k].value == _el.template)
						{
							site_template = ' (' + arBXTemplates[k].name + ')';
							break;
						}
					}
				else
					site_template = ' ('+BX_MESS.BUILD_IN_TEMPLTE+')';

				oOption.value = _el.name;
				oOption.innerHTML = ((_el.title) ? _el.title : _el.name)+site_template;
				oOption.selected = ((arAllProps.template == undefined && (_el.name == ".default" || _el.name=="")) || arAllProps.template == "" && _el.name==".default" || arAllProps.template == _el.name);
				templList.appendChild(oOption);
			}

			// **** Displaying component's template parameters ****
			var arTemplParams = window.as_arComp2TemplParams[arAllProps.name + arAllProps.__bx_id];
			var cl = arTemplParams.length;
			__paramvals = [];

			BXCreateGroups(arAllProps.name, oCont);
			for (var j = 0; j < cl; j++)
				fDisplay(arTemplParams[j], tTProp, oCont);
		}
		templList = null;
		oOption = null;


		//****************************************
		//Displaying components params
		//****************************************
		var oDiv = document.createElement('DIV');
		oDiv.style.height = '0%';
		oDiv.style.width = '100%';
		var tProp=null;

		var templateID = _pTaskbar.pMainObj.templateID;

		var __tProp = _pTaskbar.pMainObj.CreateElement("TABLE");
		__tProp.className = "bxtaskbarprops";
		__tProp.style.width = "100%";
		__tProp.cellSpacing = 0;
		__tProp.id = '__bx_tProp';
		__tProp.cellPadding = 1;
		var row, cell, arPropertyParams, bSel, arValues, res, pSelect, arUsedValues, bFound, key, oOption, val, xCell, opt_val, bBr, i, k, alt;
		var _this = this;

		if(typeof(arProps) != 'object')
			arProps = {};

		var propertyID, _dataCell;
		for(var k in arProps)
		{
			if (arProps[k].param_name == "SEF_FOLDER" && !arProps[k].DEFAULT)
				arProps[k].DEFAULT = (relPath!="/" ? relPath : "")+"/";

			fDisplay(arProps[k], __tProp, oCont);
		}

		arAllProps.paramvals = __paramvals;
		oBXEditorUtils.setCustomNodeParams(_pElement,arAllProps);

		__tProp = null;
		tProp = null;
		row = null;
		cell = null;
		arPropertyParams = null;
		pSelect = null;
		oOption = null;
	}

	BXComponents2Taskbar.prototype.LoadComp2Params = function (elementName, calbackFunc, calbackObj, calbackParams, method, data)
	{
		var loadHelp = (this.pMainObj.showTooltips4Components) ? "Y" : "N";
		arComp2Tooltips[elementName] = [];
		CHttpRequest.Action = function(result)
		{
			try{
				setTimeout(function ()
					{
						var __arProps = oBXEditorUtils.getCustomNodeParams(calbackParams[2]);
						window.as_arComp2Params[elementName + __arProps.__bx_id] = window.arComp2Props;
						window.as_arComp2Templates[elementName] = window.arComp2Templates;
						window.as_arComp2TemplParams[elementName + __arProps.__bx_id] = window.arComp2TemplateProps;
						if(calbackObj && calbackFunc)
							calbackFunc.apply(calbackObj,(calbackParams) ? calbackParams : []);
						else if(calbackFunc)
							calbackFunc();
					}, 10
				);
			}catch(e) {alert('Error >> LoadComp2Params');}
		}

		if (method == 'POST' && data)
			CHttpRequest.Post('/bitrix/admin/fileman_load_comp2_params.php?lang='+BXLang+'&site='+BXSite+'&cname='+elementName+'&stid='+((this.pMainObj.templateID) ? this.pMainObj.templateID : '')+"&loadhelp="+loadHelp, data);
		else
			CHttpRequest.Send('/bitrix/admin/fileman_load_comp2_params.php?lang='+BXLang+'&site='+BXSite+'&cname='+elementName+'&stid='+((this.pMainObj.templateID) ? this.pMainObj.templateID : '')+"&loadhelp="+loadHelp);
	};


	BXComponents2Taskbar.prototype.Remove__script__ = function (str)
	{
		str = str.replace(/\\n/ig, "\n");
		return str;
	}

	//Set template
	BXComponents2Taskbar.prototype.setCompTemplate = function(_pElement,val)
	{
		var arAllProps = oBXEditorUtils.getCustomNodeParams(_pElement);
		if (!val)
			arAllProps.template = '';
		else
		{
			for(j = 0; j < val.length; j++)
			{
				if(val[j].selected)
				{
					arAllProps.template = val[j].value;
					break;
				}
			}
		}
		oBXEditorUtils.setCustomNodeParams(_pElement,arAllProps);
	}


	BXComponents2Taskbar.prototype.loadTemplateParams = function(componentName, templateName, calbackFunc, calbackObj, calbackParams, method, data)
	{
		var _CHttpRequest = new JCHttpRequest();
		_CHttpRequest.Action = function(result)
		{
			try
			{
				setTimeout(function ()
					{
						window._bx_reload_template_props = false;
						if(calbackObj && calbackFunc)
							calbackFunc.apply(calbackObj, (calbackParams) ? calbackParams : []);
						else if(calbackFunc)
							calbackFunc();
					}, 10
				);
			}
			catch(e)
			{
				alert('ERROR can\'t load template params...');
			}
		}

		if (method == 'POST' && data)
			_CHttpRequest.Post('/bitrix/admin/fileman_load_templates.php?lang='+BXLang+'&site='+BXSite+'&cname='+componentName+'&tname='+templateName+'&mode=params&stid='+((this.pMainObj.templateID) ? this.pMainObj.templateID : ''),data);
		else
			_CHttpRequest.Send('/bitrix/admin/fileman_load_templates.php?lang='+BXLang+'&site='+BXSite+'&cname='+componentName+'&tname='+templateName+'&mode=params&stid='+((this.pMainObj.templateID) ? this.pMainObj.templateID : ''));
	}

	BXComponents2Taskbar.prototype.deleteTemplateParams = function(_pElement)
	{
		var arAllProps = oBXEditorUtils.getCustomNodeParams(_pElement);
		var arPropsVals = arAllProps.paramvals;

		var __len = window.arComp2TemplateProps.length;
		var param_name;
		for (var __i = 0; __i<__len; __i++)
		{
			param_name = window.arComp2TemplateProps[__i].param_name;
			if (arPropsVals[param_name]!=undefined)
				delete arPropsVals[param_name];
		}
		arAllProps.paramvals = arPropsVals;
		oBXEditorUtils.setCustomNodeParams(_pElement,arAllProps);

		window.arComp2TemplateProps = [];
	}

	BXComponents2Taskbar.prototype.UnParseElement = function(node)
	{
		try {arVA;}catch(e){arVA = [];}
		if (node.arAttributes["__bxtagname"] == 'component2')
		{
			var arAllProps = oBXEditorUtils.getCustomNodeParams(node);
			var arPropsVals = arAllProps.paramvals;

			var res = "<?$APPLICATION->IncludeComponent(\n";

			res += "\t\""+arAllProps.name+"\",\n";
			res += "\t\""+(arAllProps.template ? arAllProps.template : "")+"\",\n";

			if (arPropsVals)
			{
				res += "\tArray(\n";
				var _len1 = "SEF_URL_TEMPLATES_".length;
				var _len2 = "VARIABLE_ALIASES_".length;
				var _SUT, _VA, lio, templ_key, var_fey, _count;
				var params_exist = false;

				for (var i in arPropsVals)
				{
					try
					{
						if (!params_exist)
							params_exist = true;

						if (typeof(arPropsVals[i]) == 'string')
							arPropsVals[i] = JS_stripslashes(arPropsVals[i]);
						else if (typeof(arPropsVals[i]) == 'object')
						{
							var __val = 'array(';
							var __len = 0;
							for (var _i in arPropsVals[i])
							{
								__len++;
								__val += '"'+JS_stripslashes(arPropsVals[i])+'",';
							}
							if (__len > 0)
								__val = __val.substr(0,__val.length-1)+')';
							else
								__val += ')';

							arPropsVals[i] = __val;
						}

						//arPropsVals[i] = JS_addslashes(arPropsVals[i]);
						if (isYes(arPropsVals["SEF_MODE"]))
						{
							//*** Handling SEF_URL_TEMPLATES in SEF = ON***
							if(i.substr(0,_len1)=="SEF_URL_TEMPLATES_")
							{
								_val = arPropsVals[i];
								__k = i.substr(_len1);
								arVA[__k] = catchVariableAliases(_val);

								if (!_SUT)
								{
									res += "\t\t\""+i.substr(0,_len1-1)+"\" => Array(\n"
									_SUT = true;
								}
								if (arPropsVals[i].substr(0, 2)=='={')
									res += "\t\t\t\""+i.substr(_len1)+"\" => "+arPropsVals[i].substr(2, arPropsVals[i].length-3)+""+((true) ? "," : "")+" \n";
								else
									res += "\t\t\t\""+i.substr(_len1)+"\" => \""+JS_addslashes(arPropsVals[i])+"\",\n";
								continue;
							}
							else if (_SUT)
							{
								lio = res.lastIndexOf(",");
								res = res.substr(0,lio)+res.substr(lio+1);
								_SUT = false;
								res += "\t\t),\n";
							}

							//*** Handling  VARIABLE_ALIASES  in SEF = ON***
							if(i.substr(0,_len2)=="VARIABLE_ALIASES_")
								continue;

						}
						else if(arPropsVals["SEF_MODE"]=="N")
						{
							//*** Handling SEF_URL_TEMPLATES in SEF = OFF ***
							if (i.substr(0,_len1)=="SEF_URL_TEMPLATES_" || i=="SEF_FOLDER")
								continue;

							//*** Handling VARIABLE_ALIASES  in SEF = OFF ***
							if(i.substr(0,_len2)=="VARIABLE_ALIASES_")
							{
								if (!_VA)
								{
									res += "\t\t\""+i.substr(0,_len2-1)+"\" => Array(\n";
									_VA = true;
								}
								res += "\t\t\t\""+i.substr(_len2)+"\" => \""+JS_addslashes(arPropsVals[i])+"\",\n";
								continue;
							}
							else if (_VA)
							{
								lio = res.lastIndexOf(",");
								res = res.substr(0,lio)+res.substr(lio+1);
								_VA = false;
								res += "\t\t),\n";
							}
						}

						if (arPropsVals[i].substr(0, 2)=='={')
							res += "\t\t\""+i+"\" => "+arPropsVals[i].substr(2, arPropsVals[i].length-3)+""+((true) ? "," : "")+" \n";
						else if (arPropsVals[i].substr(0, 6).toLowerCase()=='array(')
							res += "\t\t\""+i+"\" => "+arPropsVals[i]+""+((true) ? "," : "")+" \n";
						else
							res += "\t\t\""+i+"\" => \""+JS_addslashes(arPropsVals[i])+"\""+((true) ? "," : "")+" \n";

					}
					catch(e)
					{
						_alert('ERROR > UnParseElement: '+i+"\n"+arPropsVals[i]);
						continue;
					}
				}

				if (_VA || _SUT)
				{
					lio = res.lastIndexOf(",");
					res = res.substr(0,lio)+res.substr(lio+1);
					_VA = false;
					_SUT = false;
					res += "\t\t),\n";
				}

				if (isYes(arPropsVals["SEF_MODE"]))
				{
					res += "\t\t\"VARIABLE_ALIASES\" => Array(\n";


					if (arVA)
					{
						for (templ_key in arVA)
						{
							res += "\t\t\t\""+templ_key+"\" => Array(";
							_count = 0;
							for (var_key in arVA[templ_key])
							{
								_count++;
								res += "\n\t\t\t\t\""+var_key+"\" => \""+arVA[templ_key][var_key]+"\",";
							}
							if (_count>0)
							{
								lio = res.lastIndexOf(",");
								res = res.substr(0,lio)+res.substr(lio+1);
								res += "\n\t\t\t),\n";
							}
							else
								res += "),\n";
						}
					}

					res += "\t\t),\n";
				}

				if (params_exist)
				{
 					lio = res.lastIndexOf(",");
					res = res.substr(0,lio)+res.substr(lio+1);
				}
				res += "\t)\n";
			}
			else
			{
				res +="Array()\r\n"
			}
			res +=');?>';

			if (lca)
			{
				var key = str_pad_left(++_$compLength, 4, '0');
				_$arComponents[key] = res;
				return '#COMPONENT'+String(key)+'#';
			}
			else
				return res;
		}
		return false;
	}

	BXComponents2Taskbar.prototype.GetPropGroupDataCell = function (name, title, oCont, arParams)
	{
		var _oTable = document.createElement('TABLE');
		_oTable.cellPadding = 0;
		_oTable.cellSpacing = 0;
		_oTable.width = '100%';
		_oTable.className = 'bxpropgroup';
		_oTable.setAttribute('__bxpropgroup', '__' + name);

		var rowTitle = _oTable.insertRow(-1);
		var c = rowTitle.insertCell(-1);
		c.style.width = '0%';
		c.appendChild(this.pMainObj.CreateElement("IMG", {src: '/bitrix/images/1.gif', className: 'tskbr_common bx_btn_tabs_plus_big'}));
		c = rowTitle.insertCell(-1);
		c.style.width = '100%';
		c.innerHTML = (title) ? BXReplaceSpaceByNbsp(title) : "";

		var rowData = _oTable.insertRow(-1);
		c = rowData.insertCell(-1);
		c.colSpan = 2;
		c.id = '__bxpropgroup_dc_'+name;

		var compName = arParams[0];
		var _this = this;
		rowTitle.__bxhidden = false;
		rowTitle.id = '__bxpropgroup_tr_'+name;
		rowTitle.className = "bxtskbrprp_title_d";
		rowTitle.onclick = function(){_this.HidePropGroup(name,!this.__bxhidden,[compName]);};
		oCont.appendChild(_oTable);

		if (!arComp2PropGroups[compName])
		{
			arComp2PropGroups[compName] = {};
			arComp2PropGroups[compName][name] = false;
			oTaskbar.HidePropGroup(name,false,arParams);
		}
		else
			oTaskbar.HidePropGroup(name,((arComp2PropGroups[compName][name]===false) ? true : false),arParams);

		return c;
	}

	BXComponents2Taskbar.prototype.HidePropGroup = function (groupName,bHide,arParams)
	{
		var compName = arParams[0];
		arComp2PropGroups[compName][groupName] = !bHide;

		if (!arParams)
			arParams = [];

		var titleRow = document.getElementById('__bxpropgroup_tr_'+groupName);
		var dataCell = document.getElementById('__bxpropgroup_dc_'+groupName);

		if (bHide)
		{
			dataCell.style.display = GetDisplStr(0);
			titleRow.__bxhidden = true;
			titleRow.className = "bxtskbrprp_title_d";
			titleRow.cells[0].firstChild.className = 'tskbr_common bx_btn_tabs_plus_big';
		}
		else
		{
			dataCell.style.display = GetDisplStr(1);
			titleRow.__bxhidden = false;
			titleRow.className = "bxtskbrprp_title_a";
			titleRow.cells[0].firstChild.className = 'tskbr_common bx_btn_tabs_minus_big';
		}
	}

	BXComponents2Taskbar.prototype.OnElementClick = function (oEl,arEl)
	{
		if (!this.pMainObj.oPropertiesTaskbar)
			return;

		if (!arEl.screenshots)
			arEl.screenshots = [];

		_pTaskbar = this.pMainObj.oPropertiesTaskbar;
		oBXEditorUtils.BXRemoveAllChild(_pTaskbar.pCellProps);

		//****** DISPLAY TITLE *******
		var compName = arEl.name;
		var compTitle = arEl.title;
		var compDesc = arEl.params.DESCRIPTION;
		var bComplex = isYes(arEl.complex);

		var tCompTitle = document.createElement("TABLE");
		tCompTitle.className = "componentTitle";
		var row = tCompTitle.insertRow(-1);
		var cell = row.insertCell(-1);
		cell.innerHTML = "<SPAN class='title'>"+compTitle+"  ("+compName+")</SPAN><BR /><SPAN class='description'>"+(bComplex ? BX_MESS.COMPLEX_COMPONENT : "")+compDesc+"</SPAN>";
		cell.className = "titlecell";
		cell.width = "100%";
		var _helpCell = row.insertCell(-1);
		_helpCell.className = "helpicon";
		//var _helpicon = oTaskbar.pMainObj.CreateElement("IMG", {"src": "/bitrix/images/fileman/htmledit2/comp2help.gif", "width": 16,"height":16,"title": "Help", "alt": "Help"});
		//_helpicon.onclick = function(e){};
		//_helpCell.appendChild(_helpicon);
		_pTaskbar.pCellProps.appendChild(tCompTitle);
		var oDivSS;
		for (var i=0; i<arEl.screenshots.length; i++)
		{
			oDivSS = document.createElement("DIV");
			oDivSS.className = "scrshot";
			var imgSS = oTaskbar.pMainObj.CreateElement("IMG", {src: arEl.screenshots[i], title: compTitle, alt: compTitle});
			oDivSS.appendChild(imgSS);
			_pTaskbar.pCellProps.appendChild(oDivSS);
			oDivSS = null;
		}

		oDivSS = null;
		_helpCell = null;
		_helpicon = null;
		tCompTitle = null;
	}

	BXComponents2Taskbar.prototype.OnSaveLCA = function()
	{
		var sContent = this.pMainObj.GetContent();
		sContent = LCAContentParser(sContent, true);
		this.pMainObj.pValue.value = sContent;
	};
}

function BXCheckForComponent2(_str, bLCA_mode)
{
	if (lca && _$lca_only && !bLCA_mode) // for LCA mode - components already parsed
		return false;

	_str = oBXEditorUtils.PHPParser.trimPHPTags(_str);
	_str = oBXEditorUtils.PHPParser.cleanCode(_str);

	var _oFunc = oBXEditorUtils.PHPParser.parseFunction(_str);
	if (!_oFunc)
		return false;

	if (_oFunc.name.toUpperCase()=='$APPLICATION->INCLUDECOMPONENT')
	{
		var arParams = oBXEditorUtils.PHPParser.parseParameters(_oFunc.params);
		var name = arParams[0];
		var template = (arParams[1]) ? arParams[1] : "";
		var params = (arParams[2]) ? arParams[2] : [];

		for (var key in params)
		{
			if (typeof params[key]=='object')
				params[key] = _BXArr2Str(params[key]);
		}

		try
		{
			var comProps = window.as_arComp2Elements[name];
			var icon = (comProps.icon) ? comProps.icon : '/bitrix/images/fileman/htmledit2/component.gif';
			var tagname = (comProps.tagname) ? comProps.tagname : 'component2';
			var allParams = copyObj(comProps.params);
			allParams.name = name;

			try{
				//Handling SEF_URL_TEMPLATES
				if (params["SEF_URL_TEMPLATES"])
				{
					var _str = params["SEF_URL_TEMPLATES"];
					var arSUT = oBXEditorUtils.PHPParser.getArray((_str.substr(0,8).toLowerCase() == "={array(") ? _str.substr(2,_str.length-3) : _str);

					for (var _key in arSUT)
						params["SEF_URL_TEMPLATES_"+_key] = arSUT[_key];

					delete params["SEF_URL_TEMPLATES"];
				}

				if (params["VARIABLE_ALIASES"])
				{
					if (params["SEF_MODE"]=="N")
					{
						var _str = params["VARIABLE_ALIASES"];
						var _arVA = oBXEditorUtils.PHPParser.getArray((_str.substr(0,8).toLowerCase() == "={array(") ? _str.substr(2,_str.length-3) : _str);

						for (var _key in _arVA)
							params["VARIABLE_ALIASES_"+_key] = _arVA[_key];

					}
					delete params["VARIABLE_ALIASES"];
				}
			}catch(e){}

			allParams.template = template;
			allParams.paramvals = params;
			var id = "__bx_c2_"+Math.random();
			allParams.__bx_id = push2Component2(id, allParams.name); // Used to cache component-params for each component
			return '<img id="'+id+'" src="'+icon+'" border="0" height="16" width="32" __bxtagname="'+tagname+'" __bxcontainer="' + bxhtmlspecialchars(BXSerialize(allParams)) + '" />';
		}
		catch(e) {_alert('Error: COMPONENTS 2.0 was not loaded correctly: '+name);}
	}
	return false;
}

function LCAContentParser(str, returnCode)
{
	returnCode = (returnCode === true);
	var replaceLCA = function(str, key)
	{
		var cCode = _$arComponents[key]; // Code of component: $APLICATION->IncludeComponent( .........
		if (!cCode)
			return '';
		if (returnCode)
			return cCode;
		return BXCheckForComponent2(cCode, true);
	};
	str = str.replace(/#COMPONENT(\d{4})#/ig, replaceLCA);
	_$LCAContentParser_execed = true;
	return str;
}

if (lca) //limit component access
	oBXEditorUtils.addContentParser(LCAContentParser);
	//oBXEditorUtils.addContentUnParser(LCAContentParser);

oBXEditorUtils.addPHPParser(BXCheckForComponent2, 0, true);



function checkComp2Template(pMainObj)
{
	var compList, len, i, compName, arCompNames = [];
	for (compName in arComponents2)
		arCompNames.push(compName);

	var postData = oBXEditorUtils.ConvertArray2Post(arCompNames,'complist');
	var params = [];
	loadComp2TemplateLists(pMainObj.templateID,__checkComp2Template, [pMainObj],"POST",postData);
}

function __checkComp2Template(params)
{
	var pMainObj = params[0];

	var template,oEl,allParams,name;
	for (compName in arComponents2)
	{
		compList = arComponents2[compName];
		len = compList.length;

		for (i =0; i<len; i++)
		{
			id = compList[i].id;
			oEl = pMainObj.pEditorDocument.getElementById(id);
			if (!oEl)
				continue;
			allParams = oBXEditorUtils.getCustomNodeParams(oEl);
			template = allParams.template;
			name = allParams.name;

			if (!arComp2TemplateLists[name][template] || ((template=="" || template==".default") && !(arComp2TemplateLists[name][''] || arComp2TemplateLists[name]['.default'])))
			{
				allParams.template = (arComp2TemplateLists[name]['']) ? "" : ".default";
				oBXEditorUtils.setCustomNodeParams(oEl,allParams);
			}
			as_arComp2Templates[name] = [];
			for (__i in arComp2TemplateLists[name])
				as_arComp2Templates[name].push(arComp2TemplateLists[name][__i]);

			window._bx_reload_template_props = true;
		}
	}
	setTimeout(function (){pMainObj.OnEvent("OnSelectionChange");}, 5);
}

function loadComp2TemplateLists(siteTemplate,calbackFunc, calbackParams,method,data)
{
	var _CHttpRequest = new JCHttpRequest();
	_CHttpRequest.Action = function(result)
	{
		try
		{
			setTimeout(function ()
				{
					if(calbackFunc)
						calbackFunc(calbackParams);
				}, 5
			);
		}
		catch(e)
		{
			alert('ERROR can\'t load template params...');
		}
	}
	if (method == 'POST' && data)
		_CHttpRequest.Post('/bitrix/admin/fileman_load_templates.php?lang='+BXLang+'&site='+BXSite+'&mode=list&stid='+siteTemplate,data);
	else
		_CHttpRequest.Send('/bitrix/admin/fileman_load_templates.php?lang='+BXLang+'&site='+BXSite+'&mode=list&stid='+siteTemplate);
}

function push2Component2(id, name)
{
	if (!arComponents2[name])
		arComponents2[name] = [];

	arComponents2[name].push({'id':id});
	return arComponents2[name].length;
}

function _BXArr2Str(arObj)
{
	try
	{
		var _arObj = [];
		var str = 'Array(';
		for (var _key in arObj)
		{
			if (parseInt(_key).toString()=='NaN')
				_arObj.push('"'+_key+'"=>"'+arObj[_key]+'"');
			else
				_arObj[_key] = '"'+arObj[_key]+'"';
		}

		str += _arObj.join(",");
		str += ')';
	}
	catch(e)
	{
		str = 'Array()';
	}
	return str;
}


function _BXStr2Arr(str)
{
	var arObj = oBXEditorUtils.PHPParser.getArray(str);
	var len = arObj.length, res={};
	for (var i=0;i<len; i++)
		res[arObj[i]] = arObj[i];

	return res;
}

function catchVariableAliases(str)
{
	var arRes = [];
	var res = str.match(/(\?|&)(.+?)=#([^#]+?)#/ig);
	if (!res)
		return arRes;

	for (var l=0;l<res.length; l++)
	{
		var _res = res[l].match(/(\?|&)(.+?)=#([^#]+?)#/i);
		arRes[_res[3]] = _res[2];
	}
	return arRes;
}

function isYes(val)
{
	return val && val.toUpperCase() == "Y";
}

oBXEditorUtils.addTaskBar('BXComponents2Taskbar', 2, BX_MESS.CompTBTitle+" 2.0", [], 10);