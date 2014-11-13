var one_gif_src = '/bitrix/images/1.gif';
var image_path = '/bitrix/images/fileman/htmledit2';
var global_iconkit_path = image_path + '/_global_iconkit.gif';
var settings_page_path = '/bitrix/admin/fileman_manage_settings.php';
var get_xml_page_path = '/bitrix/admin/fileman_get_xml.php';
var editor_dialog_path = '/bitrix/admin/fileman_editor_dialog_new.php';
var flash_preview_path = '/bitrix/admin/fileman_flash_preview.php';
var dxShadowImgPath = '';


// Methods for PHP version
BXHTMLEditor.prototype.LoadComponents2 = function(oCallBack)
{
	var callback = function(oCallBack)
	{
		if (!oCallBack.params)
			oCallBack.func.apply(oCallBack.obj);
		else
			oCallBack.func.apply(oCallBack.obj, oCallBack.params);
	};

	var lc = new JCHttpRequest();
	lc.Action = function(result)
	{
		setTimeout(function (){
			if (window.arComp2Elements)
			{
				window.as_arComp2Elements = [];
				var l = window.arComp2Elements.length;
				for (var i = 0; i < l; i++)
					as_arComp2Elements[window.arComp2Elements[i].name] = window.arComp2Elements[i];
			}
			else
			{
				err_text = "ERROR in pMainObj.LoadComponents2()";
				if ((eind = result.indexOf('Fatal error')) != -1)
					err_text += "\n PHP error: \n\n....." + result.substr(eind - 10);
				alert(err_text);
			}
			callback(oCallBack);
		}, 5);
	};

	if (!window.as_arComp2Elements)
		lc.Send('/bitrix/admin/fileman_load_components2.php?lang='+BXLang+'&site='+BXSite+'&load_tree=Y');
	else
		callback(oCallBack);
};

BXHTMLEditor.prototype.LoadComponents1 = function(oCallBack)
{
	var sURL = '/bitrix/admin/fileman_get_xml.php?op=getcomponents1&lang='+BXLang+'&site='+BXSite+'&templateID='+this.templateID;
	window.arComp1Elements = this.GetData(sURL);

	oCallBack.func.apply(oCallBack.obj);
};

BXHTMLEditor.prototype.OnLoad_ex = function()
{
	if((!this.arConfig["bWithoutPHP"] || this.limit_php_access) && this.arConfig["use_advanced_php_parser"] == 'Y')
	{
		this.bUseAPP = true; // APP - AdvancedPHPParser
		this.APPConfig =
		{
			arTags_before : ['tbody','thead','tfoot','tr','td','th'],
			arTags_after : ['tbody','thead','tfoot','tr','td','th'],
			arTags :
			{
				'a' : ['href','title','class','style'],
				'img' : ['src','alt','class','style']
			}
		};
	}
	else
		this.bUseAPP = false;

	if (this.limit_php_access)
	{
		var php_disabled_unparser = function(_node)
		{
			if (_node.arAttributes["__bxtagname"] == 'php_disabled')
			{
				var code = _node.arAttributes["__bx_code"]
				return '#PHP'+code+'#';
			}
			return false;
		};
		oBXEditorUtils.addUnParser(php_disabled_unparser);

		var php_disabled_propBar = function(bNew, pTaskbar, pElement)
		{
			oBXEditorUtils.BXRemoveAllChild(pTaskbar.pCellProps);
			var oSpan = document.createElement('SPAN');
			oSpan.style.padding = '10px';
			oSpan.innerHTML = BX_MESS.LPA_WARNING;
			pTaskbar.pCellProps.appendChild(oSpan);
		};
		oBXEditorUtils.addPropertyBarHandler('php_disabled', php_disabled_propBar);
	}
	
	//this.AddEventHandler(eventName, pEventHandler, pObject);
	// If it's LCA mode and but haven't  to output LCA-encoded content
	//this.AddEventHandler('SaveContentAfter', function(){alert('test');}, this);
	// console.info('1: ' + window.lca);
	// console.info('2: ' + this.pComponent2Taskbar);
	// console.info('3: ' + _$lca_to_output);
	// if (window.lca && this.pComponent2Taskbar && !_$lca_to_output)
	// {
		// this.AddEventHandler('SaveContentAfter', this.OnSaveLCA, this);
	
		// //this.AddEventHandler('SaveContentAfter', this.OnSaveLCA, this);
	// }
};


BXHTMLEditor.prototype.APP_Parse = function(sContent)
{
	if (!this.bUseAPP)
		return sContent;
	
	this.arAPPFragments = [];
	sContent = this.APP_ParseBetweenTableTags(sContent);
	sContent = this.APP_ParseInAttributes(sContent);
	return sContent;
};

BXHTMLEditor.prototype.APP_ParseBetweenTableTags = function(str)
{
	var _this = this;
	var replacePHP_before = function(str,b1,b2,b3,b4)
	{
		_this.arAPPFragments.push(JS_addslashes(b1));
		return b2+b3+' __bx_php_before=\"#APP'+(_this.arAPPFragments.length-1)+'#\" '+b4;
	};
	var replacePHP_after = function(str,b1,b2,b3,b4)
	{
		_this.arAPPFragments.push(JS_addslashes(b4));
		return b1+'>'+b3+'<'+b2+' style="display:none;"__bx_php_after=\"#APP'+(_this.arAPPFragments.length-1)+'#\"></'+b2+'>';
	};
	var arTags_before = _this.APPConfig.arTags_before;
	var arTags_after = _this.APPConfig.arTags_after;
	var tagName,re;
	// PHP fragments before tags
	for (var i = 0,l = arTags_before.length; i<l; i++)
	{
		tagName = arTags_before[i];
		if (_this.limit_php_access)
			re = new RegExp('#(PHP(?:\\d{4}))#(\\s*)(<'+tagName+'[^>]*?)(>)',"ig");
		else
			re = new RegExp('<\\?(.*?)\\?>(\\s*)(<'+tagName+'[^>]*?)(>)',"ig");
		str = str.replace(re, replacePHP_before);
	}
	// PHP fragments after tags
	for (var i = 0,l = arTags_after.length; i<l; i++)
	{
		tagName = arTags_after[i];
		if (_this.limit_php_access)
			re = new RegExp('(</('+tagName+')[^>]*?)>(\\s*)#(PHP(?:\\d{4}))#',"ig");
		else
			re = new RegExp('(</('+tagName+')[^>]*?)>(\\s*)<\\?(.*?)\\?>',"ig");
		str = str.replace(re, replacePHP_after);
	}
	return str;
};

BXHTMLEditor.prototype.APP_ParseInAttributes = function(str)
{
	var _this = this;
	var replacePHP_inAtr = function(str,b1,b2,b3,b4,b5,b6)
	{
		_this.arAPPFragments.push(JS_addslashes(b5));
		return b1+b2+b3+'""'+' __bx_php_'+b2+b3+'\"#APP'+(_this.arAPPFragments.length-1)+'#\"'+b6;
	};
	var arTags = _this.APPConfig.arTags;
	var tagName, atrName, atr, i;
	for (tagName in arTags)
	{
		for (i = 0, cnt = arTags[tagName].length; i < cnt; i++)
		{
			atrName = arTags[tagName][i];
			re = new RegExp('(<'+tagName+'(?:[^>](?:\\?>)*?)*?)('+atrName+')(\\s*=\\s*)((?:"|\')?)<\\?(.*?)\\?>\\4((?:[^>](?:\\?>)*?)*?>)',"ig");
			str = str.replace(re, replacePHP_inAtr);
		}
	}
	return str;
};

BXHTMLEditor.prototype.SystemParse_ex = function(sContent)
{
	if(window.lca)
	{
		if (_$arComponents !== false) // _$arComponents - is not empty
		{
			_$lca_only = true;
		}
		else
		{
			_$arComponents = {};
			_$compLength = 0;
		}
	}
	if (this.limit_php_access)
		sContent = sContent.replace(/#PHP(\d{4})#/ig, "<img src='/bitrix/images/fileman/htmledit2/php.gif' __bxtagname='php_disabled' __bx_code='$1'>");
	//Replacing PHP by IMG
	if(!this.arConfig["bWithoutPHP"] || this.limit_php_access)
		sContent = this.pParser.ParsePHP(sContent);
	return sContent;
};


BXHTMLEditor.prototype.SetCodeEditorContent_ex = function(sContent)
{
	sContent = sContent.replace(/(^[\s\S]*?)(<body.*?>)/i, "");
	if (this.fullEdit)
	{
		this._head = RegExp.$1;
		if (this._body != RegExp.$2)
		{
			this._body = RegExp.$2;
			this.updateBody(this._body);
		}
	}
	sContent = sContent.replace(/(<\/body>[\s\S]*?$)/i, "");

	if (this.fullEdit)
		this._footer = RegExp.$1;

	if (this.fullEdit)
		return this._head+this._body+sContent+this._footer;

	return sContent;
};

BXHTMLEditor.prototype.APP_Unparse = function(sContent)
{
	sContent = this.APP_UnparseBetweenTableTags(sContent);
	sContent = this.APP_UnparseInAttributes(sContent);
	return sContent;
};

BXHTMLEditor.prototype.APP_UnparseBetweenTableTags = function(str)
{
	var _this = this;
	var unreplacePHP_before = function(str, b1, b2, b3)
	{
		if (_this.limit_php_access)
			return '#'+JS_stripslashes(b2)+'#'+b1+b3;
		else
			return '<?'+JS_stripslashes(_this.arAPPFragments[parseInt(b2)])+'?>'+b1+b3;
	};
	var unreplacePHP_after = function(str, b1, b2)
	{
		if (_this.limit_php_access)
			return b1+'#'+JS_stripslashes(b2)+'#';
		else
			return b1+'<?'+JS_stripslashes(_this.arAPPFragments[parseInt(b2)])+'?>';
	}

	var arTags_before = _this.APPConfig.arTags_before;
	var arTags_after = _this.APPConfig.arTags_after;
	var tagName,re;
	// PHP fragments before tags
	for (var i = 0,l = arTags_before.length; i<l; i++)
	{
		tagName = arTags_before[i];
		re = new RegExp('(<'+tagName+'[^>]*?)__bx_php_before="#APP(\\d+)#"([^>]*?>)',"ig");
		str = str.replace(re, unreplacePHP_before);
	}
	// PHP fragments after tags
	for (var i = 0,l = arTags_after.length; i<l; i++)
	{
		tagName = arTags_after[i];
		re = new RegExp('(</'+tagName+'[^>]*?>\\s*)<'+tagName+'[^>]*?__bx_php_after="#APP(\\d+)#"[^>]*?>(?:.|\\s)*?</'+tagName+'>',"ig");
		str = str.replace(re, unreplacePHP_after);
	}
	return str;
};

BXHTMLEditor.prototype.APP_UnparseInAttributes = function(str)
{
	var _this = this;
	un_replacePHP_inAtr = function(str,b1,b2,b3,b4,b5,b6,b7)
	{
		return b1+'"<?'+JS_stripslashes(_this.arAPPFragments[parseInt(b6)])+'?>" '+b3+b7;
	}
	un_replacePHP_inAtr2 = function(str,b1,b2,b3,b4,b5,b6)
	{
		return b1+b4+'"<?'+JS_stripslashes(_this.arAPPFragments[parseInt(b3)])+'?>" '+b6;
	}
	var arTags = _this.APPConfig.arTags;

	var tagName, atrName, atr, i;
	for (tagName in arTags)
	{
		for (i = 0, cnt = arTags[tagName].length; i < cnt; i++)
		{
			atrName = arTags[tagName][i];
			re = new RegExp('(<'+tagName+'(?:[^>](?:\\?>)*?)*?'+atrName+'\\s*=\\s*)("|\')[^>]*?\\2((?:[^>](?:\\?>)*?)*?)(__bx_php_'+atrName+')(?:\\s*=\\s*)("|\')#APP(\\d+)#\\5((?:[^>](?:\\?>)*?)*?>)',"ig");
			re2 = new RegExp('(<'+tagName+'(?:[^>](?:\\?>)*?)*?)__bx_php_'+atrName+'\\s*=\\s*("|\')#APP(\\d+)#\\2((?:[^>](?:\\?>)*?)*?'+atrName+'\\s*=\\s*)("|\').*?\\5((?:[^>](?:\\?>)*?)*?>)',"ig");
			str = str.replace(re, un_replacePHP_inAtr);
			str = str.replace(re2, un_replacePHP_inAtr2);
		}
	}
	return str;
};

BXNode.prototype.__ReturnPHPStr = function(arVals, arParams)
{
	var res = "";
	var un = Math.random().toString().substring(2);
	var i=0, val, comm, zn, p, j;
	for(var key in arVals)
	{
		val = arVals[key];
		i++;
		comm = (arParams && arParams[key] && arParams[key].length > 0 ? un + 'x' + i + 'x/' + '/ '+arParams[key] : '');
		res += '\r\n\t\''+key+'\'\t=>\t';

		if(typeof(val)=='object' && val.length>1)
		{
			res += "Array("+comm+"\r\n";
			zn = '';
			for(j=0; j<val.length; j++)
			{
				p = val[j];
				if(zn!='') zn+=',\r\n';
				zn += "\t\t\t\t\t"+this.__PreparePHP(p);
			}
			res += zn+"\r\n\t\t\t\t),";
		}
		else if(typeof(val)=='object' && val[0])
			res += "Array("+this.__PreparePHP(val[0])+"),"+comm;
		else
			res += this.__PreparePHP(val)+","+comm;
	}

	var max = 0;
	var lngth = [], pn, l;
	for(j=1; j<=i; j++)
	{
		p = res.indexOf(un+'|'+j+'|');
		pn = res.substr(0, p).lastIndexOf("\n");
		l = (p-pn);
		lngth[j] = l;
		if(max<l)
			max = l;
	}

	var k;
	for(j=1; j<=i; j++)
	{
		val = '';
		for(k=0; k<(max-lngth[j]+7)/8; k++)
			val += '\t';
		l = new RegExp(un+'x'+j+'x', "g")
		res = res.replace(l, val);
	}

	res = res.replace(/^[ \t,\r\n]*/g, '');
	res = res.replace(/[ \t,\r\n]*$/g, '');
	return res;
};

BXNode.prototype.__PreparePHP = function (str)
{
	str = str.toString();
	if(str.substr(0, 2)=="={" && str.substr(str.length-1, 1)=="}" && str.length>3)
		return str.substring(2, str.length-1);

	str = str.replace(/\\/g, "\\\\");
	str = str.replace(/'/g, "\\'");
	return "'"+str+"'";
};

BXParser.prototype.ParsePHP = function (str)
{
	var arScripts = [];
	var p = 0, i, bSlashed, bInString, ch, posnext, ti, quote_ch, mm=0, mm2=0;
	while((p = str.indexOf("<?", p)) >= 0)
	{
		mm=0;
		i = p + 2;
		bSlashed = false;
		bInString = false;
		while(i<str.length-1)
		{
			i++;
			ch = str.substr(i, 1);

			if(!bInString)
			{
				//if it's not comment
				if(ch == "/" && i+1<str.length)
				{
					//find end of php fragment php
					posnext = str.indexOf("?>", i);
					if(posnext==-1)
					{
						//if it's no close tag - so script is unfinished
						p = str.length;
						break;
					}
					posnext += 2;


					ti = 0;
					if(str.substr(i+1, 1)=="*" && (ti = str.indexOf("*/", i+2))>=0)
						ti += 2;
					else if(str.substr(i+1, 1)=="/" && (ti = str.indexOf("\n", i+2))>=0)
						ti += 1;

					if(ti>0)
					{
						//find begin - "i" and end - "ti" of comment
						// check: what is coming sooner: "END of COMMENT" or "END of SCRIPT"
						if(ti>posnext && str.substr(i+1, 1)!="*")
						{

							//if script is finished - CUT THE SCRIPT
							arScripts.push([p, posnext, str.substr(p, posnext-p)]);
							p = posnext;
							break;
						}
						else
							i = ti - 1; //End of comment come sooner
					}
					continue;
				}
				if(ch == "?" && i+1<str.length && str.substr(i+1, 1)==">")
				{
					i = i+2;
					arScripts.push([p, i, str.substr(p, i-p)]);
					p = i+1;
					break;
				}
			}

			//if(bInString && ch == "\\" && bSlashed)
			if(bInString && ch == "\\")
			{
				bSlashed = true;
				continue;
			}

			if(ch == "\"" || ch == "'")
			{
				if(bInString)
				{
					if(!bSlashed && quote_ch == ch)
						bInString = false;
				}
				else
				{
					bInString = true;
					quote_ch = ch;
				}
			}

			bSlashed = false;
		}

		if(i>=str.length)
			break;

		p = i;
	}
	this.arScripts = [];
	if(arScripts.length > 0)
	{	
		var newstr = "";
		var plast = 0, arPHPScript = [], arRes, arTemplate, arScript, str1, strParsed;

		arComponents2 = [];
		for(i=0; i<arScripts.length; i++)
		{
			arScript = arScripts[i];
			strParsed = false;
			try
			{
				for (var j = 0; j < arPHPParsers.length;j++)
				{
					str1 = arPHPParsers[j](arScript[2])
					if (str1 && str1.indexOf("<?") == -1)
					{
						strParsed = true;
						break;
					}
				}
			}
			catch(e) {_alert('ERROR: '+e.message+'\n'+'BXParser.prototype.ParsePHP'+'\n'+'Type: '+e.name);}

			if (strParsed)
				newstr += str.substr(plast, arScript[0]-plast) + str1;
			else if(!limit_php_access)
			{
				newstr += str.substr(plast, arScript[0]-plast) + '<img src="/bitrix/images/fileman/htmledit2/php.gif" border="0" __bxtagname="php" __bxcontainer="' + bxhtmlspecialchars(BXSerialize({'code':arScript[2]})) + '" />';
			}
			else
			{
				if (window.BS_MESS)
					alert(BS_MESS.LPA_WARNING);
				else
					setTimeout(function(){if(window.BS_MESS){alert(BS_MESS.LPA_WARNING);}}, 1000);

				newstr += str.substr(plast, arScript[0]-plast);
			}

			plast = arScript[1];
		}
		str = newstr + str.substr(plast);
	}
	return str;
};








