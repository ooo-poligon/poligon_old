var reBlockElements = /^(HTML|HEAD|BODY|BR|TITLE|TABLE|SCRIPT|TR|TBODY|P|DIV|H1|H2|H3|H4|H5|H6|ADDRESS|PRE|OL|UL|LI)$/i;

// Some methods of this class is in the editor_php.js/editor_aspx.js
function BXNode(parent, oParentNode)
{
	this.oParent = parent;
	this.iLevel = 0;
	if(parent)
	{
		parent.arNodes[parent.arNodes.length] = this;
		this.iLevel = parent.iLevel+1;
	}
	this.arNodes = [];
	this.arAttributes = [];
	this.type = null;
	this.text = "";
	this.arHandledUnParsers = [];
}

BXNode.prototype.GetHTML = function(bFormatted)
{
	try
	{
		var res = "", ob, good_res;
		if(this.arAttributes["__bxtagname"])
		{
			switch(this.arAttributes["__bxtagname"])
			{
				case 'anchor':
					ob = BXUnSerialize(this.arAttributes["__bxcontainer"]);
					return '<a name="'+bxhtmlspecialchars(ob['name'])+'"></a>';
				case 'component':
					if (this.pParser.pMainObj.bDotNet || this.pParser.pMainObj.limit_php_access)
						break;
					ob = BXUnSerialize(this.arAttributes["__bxcontainer"]);
					var arTemplate = this.pParser.pMainObj.FindComponentByPath(ob["SCRIPT_NAME"]);
					good_res = this.__ReturnPHPStr(ob['PARAMS'], arTemplate["FIELDS"]);

					if(ob['ADD_PARAMS'])
						return '<?$APPLICATION->IncludeFile("'+ob['SCRIPT_NAME']+'", Array(\r\n\t'+good_res+'\r\n\t), '+ob['ADD_PARAMS']+'\r\n);?>';
					return '<?$APPLICATION->IncludeFile("'+ob['SCRIPT_NAME']+'", Array(\r\n\t'+good_res+'\r\n\t));?>';
				case 'php':
					if (this.pParser.pMainObj.bDotNet || this.pParser.pMainObj.limit_php_access)
						break;
					return BXUnSerialize(this.arAttributes["__bxcontainer"]).code;
				default:
					var customRes = this.CustomUnParse();

					if (customRes)
						return customRes;

					return '\n'+BXUnSerialize(this.arAttributes["__bxcontainer"])+'\n';
			}

			return res;
		}

		if(this.arAttributes["_moz_editor_bogus_node"])
			return '';

		var f;
		this.bDontUseSpecialchars = false;
		for (var i = 0, l = arNodeUnParsers.length; i < l; i++)
		{
			f = arNodeUnParsers[i];
			if (f && typeof(f) == 'function')
				f(this, this.pParser.pMainObj);
		}

		res = this.GetHTMLLeft(bFormatted);
		var bNewLine = false;
		var sIndent = '';

		if(bFormatted && this.type!='text')
		{
			if(reBlockElements.test(this.text) && !(this.oParent && this.oParent.text && this.oParent.text.toLowerCase() == 'pre'))
			{
				for(var j = 0; j < this.iLevel - 3; j++)
					sIndent += "  ";
				bNewLine = true;
				res = "\r\n" + sIndent + res;
			}
		}

		for(var i=0; i<this.arNodes.length; i++)
			res += this.arNodes[i].GetHTML(bFormatted);

		res += this.GetHTMLRight(bFormatted);
		if(bNewLine)
			res += "\r\n" + (sIndent=='' ? '' : sIndent.substr(2));
		return res;
	}
	catch(e)
	{
		_alert("Error BXNode.prototype.GetHTML : \n type = "+this.type+"\ntext = "+this.text);
	}
};


BXNode.prototype.CustomUnParse = function(res)
{
	try
	{
		if (res == undefined)
			res = false;

		var fUnParser;

		for (var i in arBXTaskbars)
		{
			//fUnParser = eval(arBXTaskbars[i].name+'.prototype.UnParseElement');
			fUnParser = window[arBXTaskbars[i].name].prototype.UnParseElement;
			if (fUnParser)
				arUnParsers.push(fUnParser);
		}

		for (var j=0; j<arUnParsers.length; j++)
		{
			fUnParser = arUnParsers[j];
			if (fUnParser)
			{
				res = fUnParser(this, this.pParser.pMainObj);
				if (res !== false)
					break;
			}
		}

		return res;
	}
	catch(e){_alert("Error BXNode.prototype.CustomUnParse : \n type = "+this.type+"\ntext = "+this.text);}
};


BXNode.prototype.IsPairNode = function()
{
	if(this.text.substr(0, 1) == 'h' || this.text == 'br' || this.text == 'img' || this.text == 'input')
		return false;
	return true;
};

BXNode.prototype.GetHTMLLeft = function(bFormatted)
{
	if(this.type == 'text')
		return bxhtmlspecialchars(this.text);//bxhtmlspecialchars(this.text);
	var val;
	if(this.type == 'element')
	{
		var res = "<"+this.text;
		for(var attrName in this.arAttributes)
		{
			if(attrName.substring(0,4).toLowerCase() == '_moz')
				continue;

			if(this.text.toUpperCase()=='BR' && attrName.toLowerCase() == 'type' && this.arAttributes[attrName] == '_moz')
				continue;
			if(attrName=='style' && this.arAttributes[attrName].length<=0)
				continue;
			
			val = (this.bDontUseSpecialchars) ? this.arAttributes[attrName] : bxhtmlspecialchars(this.arAttributes[attrName]);
			res += ' ' + attrName + '="' + val + '"';
		}
		if(this.arNodes.length<=0 && !this.IsPairNode())
			return res+" />";

		return res+">";
	}

	return "";
};

BXNode.prototype.GetHTMLRight = function(bFormatted)
{
	if(this.type == 'element' && (this.arNodes.length>0 || this.IsPairNode()))
		return "</"+this.text+">";
	return "";
};

// Some methods of this class is in the editor_php.js/editor_aspx.js
function BXParser(pMainObj) {this.pMainObj = pMainObj;}

BXParser.prototype._RecursiveParse = function (oParentNode, oBXNode)
{
	switch(oParentNode.nodeType)
	{
		case 9:
			oBXNode.type = 'document';
			break;
		case 1:
			if(oParentNode.__bxID && oParentNode.__bxID == this.__bxID)
				return;
			oParentNode.__bxID = this.__bxID;
			if(oParentNode.tagName.length<=0 || oParentNode.tagName.substring(0, 1)=="/")
				return;

			oBXNode.type = 'element';
			oBXNode.text = oParentNode.tagName.toLowerCase();

			var attr = oParentNode.attributes;
			for(var j=0; j<attr.length; j++)
			{
				if(attr[j].specified || (oBXNode.text=="input" && attr[j].nodeName.toLowerCase()=="value"))
				{
					var attrName = attr[j].nodeName.toLowerCase();
					if(attrName == '__bxid')
						continue;

					if(attrName=="style")
						oBXNode.arAttributes[attrName] = oParentNode.style.cssText;
					else if(attrName=="src" || attrName=="href"  || attrName=="width"  || attrName=="height")
						oBXNode.arAttributes[attrName] = oParentNode.getAttribute(attrName, 2);
					else
						oBXNode.arAttributes[attrName] = attr[j].nodeValue;
				}
			}
			break;
		case 3:
			oBXNode.type = 'text';
			var res = oParentNode.nodeValue;
			if(!(oBXNode.oParent && oBXNode.oParent.text && oBXNode.oParent.text.toLowerCase() == 'pre'))
			{
				res = res.replace(/\n+/g, ' ');
				res = res.replace(/ +/g, ' ');
			}

			oBXNode.text = res;
			break;
	}

	var arChilds = oParentNode.childNodes;
	var oNode, oBXChildNode;

	for(var i=0; i<arChilds.length; i++)
	{
		oNode = arChilds[i];
		oBXChildNode = new BXNode(oBXNode, oParentNode);
		oBXChildNode.pParser = this;
		this._RecursiveParse(oNode, oBXChildNode);
	}
};


BXParser.prototype.Parse = function ()
{
	// Limit Component Access: if it's not first parsing and all components was converted to html
	if (!this.pMainObj.bDotNet && lca && this.pMainObj.pComponent2Taskbar && _$arComponents !== false && _$LCAContentParser_execed)
	{
		_$arComponents = {};
		_$compLength = 0;
	}

	this.arNodeParams = {};
	this.__bxID = parseInt(Math.random()*100000)+1;
	this.pNode = new BXNode(null);
	this.pNode.pParser = this;
	this._RecursiveParse(this.pMainObj.pEditorDocument, this.pNode);
};

BXParser.prototype.GetHTML = function (bFormatted)
{
	return this.pNode.GetHTML(bFormatted);
};

BXParser.prototype.Optimize = function (){};

BXParser.prototype.ConvertTags = function ()
{
	this.arNodeParams["phpscript"] = this.arScripts;
	var sName, pElement;
	for(sName in this.arNodeParams)
		this.pMainObj.pEditorDocument.getElementById("{#"+sName+"#}");
};


BXParser.prototype._KillBogusattributes = function(sContent)
{
	sContent = sContent.replace(/(<a[^>]*?)__bxhref[^=]*?=("|\')([^>]*?)\2([^>]*? href[^=]*?=)("|\')[^>]*?\5([^>]*?>.|\s+?<\/a>)/ig, "$1$4$5$3$5$6");
	sContent = sContent.replace(/(<a[^>]*?href[^=]*?=)("|\')[^>]*?\2([^>]*?)__bxhref[^=]*?=("|\')([^>]*?)\4([^>]*?>.|\s+?<\/a>)/ig,"$1$2$5$2$3$6");

	sContent = sContent.replace(/(<img[^>]*?)__bxsrc[^=]*?=("|\')([^>]*?)\2([^>]*?src[^=]*?=)("|\')[^>]*?\5([^>]*?>)/ig, "$1$4$5$3$5$6");
	sContent = sContent.replace(/(<img[^>]*?src[^=]*?=)("|\')[^>]*?\2([^>]*?)__bxsrc[^=]*?=("|\')([^>]*?)\4([^>]*?>)/ig, "$1$2$5$2$3$6");
	return sContent;
};

BXParser.prototype.SystemParse = function(sContent)
{
	sContent = this.ClearFromHBF(sContent);
	sContent = sContent.replace(/(<td[^>]*>)\s*(<\/td>)/ig, "$1<br _moz_editor_bogus_node='on'>$2");

	// APP - Advanced PHP parcer
	if (!this.bDotNet && this.pMainObj.bUseAPP)
		sContent = this.pMainObj.APP_Parse(sContent);
	//add  tag  __bxhref to all links
	var add__bxhref = function(str, b1, b2, b3, b4, b5) { return b1+b2+b3+b2+b4+' __bxhref='+b2+b3+b2+b5;};
	sContent = sContent.replace(/(<a.*?\s{1}href\s*=\s*)("|')((?:.*?[^\\]{1})??)\2((?:.*?[^\?]{1})??)(>.+?<\/a>)/ig, add__bxhref);
	//add tag __bxsrc to all img
	var add__bxsrc = function(str,b1,b2,b3,b4) {return b1+b2+b3+b2+' __bxsrc='+b2+b3+b2+b4;};
	sContent = sContent.replace(/(<img.*?\s{1}src\s*=\s*)("|')((?:.*?[^\\]{1})??)\2((?:.*?[^\?]{1})??>)/ig, add__bxsrc);
	if (BXIsIE())
		sContent = sContent.replace(/<area([^>]*?>[^>]*?)<\/area>/ig, "<bxarea$1</bxarea>");

	sContent = sContent.replace(/<!--[\s\S]*?-->/gi, this.UnparseComments);
	sContent = sContent.replace(/<script[\s\S]*?\/script>/gi, this.UnparseScripts);
	
	sContent = sContent.replace(/<a[^>]*?name[^=]*?=([^>]+?)\/>/ig, this.UnparseAnchors);
	sContent = sContent.replace(/<a[^>]*?name[^=]*?=([^>]+?)><\/a>/ig, this.UnparseAnchors);
	sContent = sContent.replace(/<a[^>]*?name[^=]*?=([^>]+?)>/ig, this.UnparseAnchors);

	//Parse content as string...
	sContent = this.CustomParse(sContent);
	
	if (!this.pMainObj.bDotNet)
		sContent = this.pMainObj.SystemParse_ex(sContent);

	return sContent;
};

BXParser.prototype.SystemUnParse = function(sContent)
{
	//Removing temporary servise attributes: __bxhref, __bxsrc and other
	sContent = this._KillBogusattributes(sContent);
	if (BXIsIE())
		sContent = sContent.replace(/<bxarea([^>]*?>[^>]*?)<\/bxarea>/ig, "<area$1</area>");

	// APP - Advanced PHP parcer
	if (!this.bDotNet && this.pMainObj.bUseAPP)
		sContent = this.pMainObj.APP_Unparse(sContent);

	var killspaces = function(str, b1, b2) {return b1+' '+b2.replace(/(.*?)\s{2,}(.*)/ig, killspaces);}
	sContent = sContent.replace(/(<a[^>]*?)\s{2,}(.*?>.+?<\/a>)/ig, killspaces);
	sContent = sContent.replace(/(<img[^>]*?)\s{2,}(.*?>)/ig, killspaces);
	//Replace entities
	sContent = this.HTMLEntitiesReplace(sContent);
	sContent = this.CustomContentUnParse(sContent);
	return sContent;
};

BXParser.prototype.UnparseAnchors = function(sContent, name)
{
	if(sContent.toLowerCase().indexOf("href") > 0)
		return sContent;

	name = name.trim();
	if(name.substring(0, 1) == "\"")
	{
		name = name.substring(1);
		name = name.substring(0, name.indexOf("\""));
	}
	else if(name.substring(0, 1) == "'")
	{
		name = name.substring(1);
		name = name.substring(0, name.indexOf("'"));
	}
	else if(name.indexOf(" ")>0)
		name = name.substring(0, name.indexOf(" "));

	return '<img src="' + one_gif_src + '" style="background-image: url(' + global_iconkit_path + '); background-position: -260px 0; height: 20px; width: 20px"  __bxtagname="anchor" __bxcontainer="'+bxhtmlspecialchars(BXSerialize({name : name}))+'" />';
};


BXParser.prototype.UnparseScripts = function(sContent)
{
	return '<img src="' + image_path + '/script.gif" __bxtagname="script" __bxcontainer="'+bxhtmlspecialchars(BXSerialize(sContent))+'" />';
};

BXParser.prototype.UnparseComments = function(sContent)
{
	return '<img src="' + image_path + '/comments.gif" width="32" height="16" __bxtagname="comments" __bxcontainer="'+bxhtmlspecialchars(BXSerialize(sContent))+'" />';
};

BXParser.prototype.GetHBF = function(sContent, bContentWithHBF)
{
	sContent = sContent.replace(/(^[\s\S]*?)(<body.*?>)/i, "");
	this.pMainObj._head = RegExp.$1;
	this.pMainObj._body = RegExp.$2;
	sContent = sContent.replace(/(<\/body>[\s\S]*?$)/i, "");
	this.pMainObj._footer = RegExp.$1;
	if (!bContentWithHBF)
		return sContent;
	return this.AppendHBF(sContent, true);
};

BXParser.prototype.ClearHBF = function()
{
	this.pMainObj._head = this.pMainObj._body = this.pMainObj._footer = '';
};

BXParser.prototype.AppendHBF = function(sContent, bDontClear)
{
	if (!bDontClear)
		sContent = this.ClearFromHBF(sContent);
	return this.pMainObj._head + this.pMainObj._body + sContent + this.pMainObj._footer;
};

BXParser.prototype.ClearFromHBF = function(sContent)
{
	sContent = sContent.replace(/^[\s\S]*?<body.*?>/i, "");
	sContent = sContent.replace(/<\/body>[\s\S]*?$/i, "");
	return sContent;
};

BXParser.prototype.CustomParse = function(str)
{
	var str1, i, l;
	for (i = 0, l = arContentParsers.length; i < l; i++)
	{
		try{
			str1 = arContentParsers[i](str, this.pMainObj);
			if (str1 !== false && str1 !== null)
				str = str1;
		}catch(e){_alert('ERROR: '+e.message+'\n'+'BXParser.prototype.CustomParse'+'\n'+'Type: '+e.name);}
	}
	return str;
};

BXParser.prototype.CustomContentUnParse = function(str)
{
	try{
		var str1, i, l;
		for (i = 0, l = arContentUnParsers.length; i < l; i++)
			if (str1 = arContentUnParsers[i](str, this.pMainObj))
				str = str1;
	}catch(e){_alert('ERROR: '+e.message+'\n'+'BXParser.prototype.CustomContentUnParse'+'\n'+'Type: '+e.name);}
	return str;
};

BXParser.prototype.HTMLEntitiesReplace = function (str)
{
	var lEn = this.pMainObj.arEntities.length;
	for(var i_ = 0; i_ < lEn; i_++)
		str = str.replace(this.pMainObj.arEntities_h[i_], this.pMainObj.arEntities[i_], 'g');
	return str;
};

BXParser.prototype.DOMHandle = function()
{
	try{
		for (var i = 0, l = arDOMHandlers.length; i < l; i++)
			arDOMHandlers[i](this.pMainObj.pEditorDocument);
	}catch(e){_alert('ERROR: '+e.message+'\n'+'BXParser.prototype.DOMHandle'+'\n'+'Type: '+e.name);}
};