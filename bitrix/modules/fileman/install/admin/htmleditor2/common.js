if (!window.debug_mode)
	debug_mode = false;
function _alert(str) {if (debug_mode) alert("* * * * * * * * debug_mode * * * * * * * * * *\n"+str);}

GLOBAL_pMainObj = [];
var ar_EVENTS = [];
//Array, contains editor objects for manual cleaning and removing than editor unloads: UnloadTrigger()
var ar_PROP_ELEMENTS = [];
var ar_BXButtonS = [];
var ar_BXListS = [];
var ar_BXStyleListS = [];
var ar_BXColorPickerS = [];
var ar_BXTaskbarSetS = [];
var ar_BXToolbarSetS = [];
var ar_BXToolbarS = [];
var ar_CustomElementS = [];
var ar_BXComponentsTaskbarS = [];
var ar_BXPropertiesTaskbarS = [];
var ar_BXTaskbarS = [];
var ar_BXPopupWindowS = [];
var ar_EVENTS_DE = [];
//Properties of comp2
window.as_arComp2Params = {};
window.as_arComp2Templates = {};
window.as_arComp2TemplParams = {};
window.arComp2ParamsGroups = {};
window.arComp2Tooltips = {}; // Array with tooltips
var pPropertybarHandlers = []; //PropertyBarHandlers ....
var arUnParsers = [];//Array of unparsers....
var arContentUnParsers = [];//Array of content unparsers....
var arNodeUnParsers = [];
var SETTINGS = {}; //Array of settings
var arEditorFastDialogs = [];

//	TOOLBARS SETTINGS
var arToolbarSettings_default = [];
arToolbarSettings_default['manage'] = {
	show : true,
	docked : true,
	position : [0,0,0]
};
arToolbarSettings_default['standart'] = {
	show : true,
	docked : true,
	position : [0,0,1]
};
arToolbarSettings_default['style'] = {
	show : true,
	docked : true,
	position	: [0,1,0]
};
arToolbarSettings_default['formating'] = {
	show : true,
	docked : true,
	position : [0,2,0]
};
arToolbarSettings_default['source'] = {
	show : true,
	docked : true,
	position : [1,0,0]
};
arToolbarSettings_default['template'] = {
	show : true,
	docked : true,
	position : [0,1,2]
};

//	TASKBARS SETTINGS
var arTaskbarSettings_default = [];
arTaskbarSettings_default["BXPropertiesTaskbar"] = {
	show : true,
	docked : true,
	position : [3,0,0]
};
arTaskbarSettings_default["BXComponents2Taskbar"] = {
	show : true,
	docked : true,
	position : [2,0,0]
};
arTaskbarSettings_default["BXComponentsTaskbar"] = {
	show : false,
	docked : true,
	position : [2,0,0]
};
arTaskbarSettings_default["BXFormElementsTaskbar"] = {
	show : true,
	docked : true,
	position : [2,0,0]
};
if (!window.arTaskbarSettings)
	window.arTaskbarSettings = arTaskbarSettings_default;

//	TASKBARSETS SETTINGS
var arTBSetsSettings_default = [];
arTBSetsSettings_default[0] = {show : false, width : "100%", height : "100%"};
arTBSetsSettings_default[1] = {show : false, width : "100%", height : "100%"};
arTBSetsSettings_default[2] = {show : false, width : "200px", height : "100%"};
arTBSetsSettings_default[3] = {show : false, width : "100%", height : "140px"};

arComp2PropGroups = [];
arComponents2 = [];
arComponents2_d = [];

function BXSearchInd(ar, wf)
{
	if (typeof ar != 'object')
		return -1;
	if (ar.length)
	{
		for(var i = 0, l= ar.length; i < l; i++)
			if(ar[i].toString() == wf.toString())
				return i;
	}
	else
	{
		for(var i in ar)
			if (ar[i].toString() == wf.toString())
				return i;
	}
	return -1;
}

if(!String.prototype.trim)
{
    String.prototype.trim = function()
    {
	   var r, re;
	   re = /^\s+/g;
	   r = this.replace(re, "");
	   re = /\s+$/g;
	   r = r.replace(re, "");
	   return r;
    }
}

function BXCreateElement(sTagname, arParams, arStyles, pDocument)
{
	if (!pDocument)
		pDocument = this.pDocument;

	var pEl = pDocument.createElement(sTagname);
	var sParamName;
	if(arParams)
	{
		for(sParamName in arParams)
		{
			if(sParamName.substring(0, 1) == '_' && sParamName != '__exp')
				pEl.setAttribute(sParamName, arParams[sParamName]);
			else
				pEl[sParamName] = arParams[sParamName];
		}
	}

	if(arStyles)
	{
		for(sParamName in arStyles)
			pEl["style"][sParamName] = arStyles[sParamName];
	}
	return pEl;
}

function GAttr(pElement, attr)
{
	if(attr=='className' && !BXIsIE())
		attr = 'class';
	var v = pElement.getAttribute(attr, 2);
	if(v && v!='-1')
		return v;
	return "";
}

function SAttr(pElement, attr, val)
{
	if(attr=='className' && !BXIsIE())
		attr = 'class';

	if(val.length <= 0)
		pElement.removeAttribute(attr);
	else
		pElement.setAttribute(attr, val);
}


function _BXStyleParser()
{
	_BXStyleParser.prototype.Create = function()
	{
		if(this.pFrame)
			return;

		this.pFrame = document.body.appendChild(BXCreateElement('IFRAME', {src : 'javascript:void(0)', className : 'bxedpopupframe', frameBorder : 'no', scrolling : 'no', unselectable : 'on'}, {position : 'absolute', zIndex: '9999', left: '-1000px', top: '-1000px'}, document));

		if(this.pFrame.contentDocument)
			this.pDocument = this.pFrame.contentDocument;
		else
			this.pDocument = this.pFrame.contentWindow.document;

		this.pDocument.write("<html><head><style></style></head><body></body></html>");
		this.pDocument.close();
	}

	_BXStyleParser.prototype.Parse = function(strStyles)
	{
		try{
			if(BXIsIE())
				this.pDocument.styleSheets[0].cssText = strStyles;
			else
				this.pDocument.getElementsByTagName('STYLE')[0].innerHTML = strStyles;
		}catch(e){_alert('oBXStyleParser.Parse();');}

		var arAllSt = [], rules, cssTag, arTags, cssText = '', i, j, k, result = {}, t1, t2, l1;
		if(!this.pDocument.styleSheets)
			return result;
		var x1 = this.pDocument.styleSheets;
		for(i = 0, l1 = x1.length; i < l1; i++)
		{
			rules = (x1[i].rules ? x1[i].rules : x1[i].cssRules);
			for(j = 0, l2 = rules.length; j < l2; j++)
			{
				if (rules[j].type != rules[j].STYLE_RULE)
					continue;

				cssTag = rules[j].selectorText;
				arTags = cssTag.split(",");
				for(k = 0, l3 = arTags.length; k < l3; k++)
				{
					t1 = arTags[k].split(" ");
					t1 = t1[t1.length - 1].trim();
					if(t1.substr(0, 1) == '.')
					{
						t1 = t1.substr(1);
						t2 = 'DEFAULT';
					}
					else
					{
						t2 = t1.split(".");
						if(t2.length > 1)
							t1 = t2[1];
						else
							t1 = '';
						t2 = t2[0].toUpperCase();
					}

					if(arAllSt[t1])
						continue;
					arAllSt[t1] = true;

					if(!result[t2])
						result[t2] = [];
					result[t2].push({className: t1, original: arTags[k], cssText: rules[j].style.cssText});
				}
			}
		}
		return result;
	}
}
var BXStyleParser = new _BXStyleParser();


function _BXPopupWindow()
{
	ar_BXPopupWindowS.push(this);
	this.bCreated = false;

	_BXPopupWindow.prototype.Create = function()
	{
		if(this.pFrame)
			return;

		var _this = this;

		this.pFrame = document.body.appendChild(BXCreateElement('IFRAME', {src : 'javascript:void(0)', className : 'bxedpopupframe', frameBorder : 'no', scrolling : 'no', unselectable : 'on'}, {position : 'absolute', zIndex: '9999', left: '-1000px', top: '-1000px'}, document));

		if(this.pFrame.contentDocument)
		{
			this.pDocument = this.pFrame.contentDocument;
			this.pFrame.contentWindow.onblur = function (){_this.Hide();};
		}
		else
		{
			this.pDocument = this.pFrame.contentWindow.document;
			this.pFrame.onblur = function (){_this.Hide();};
		}

		this.pDocument.open();
		this.pDocument.write('<html><head></head><body class="bx_popup_frame"><table cellpadding="0" cellspacing="0"><tr><td id="__bx_iframe_cell"></td></tr></table></body></html>');
		this.pDocument.close();

		this.pDocument.body.style.margin = this.pDocument.body.style.padding = "0px";
		this.pDocument.body.style.border = "0px";
		this.pDocument.body.style.backgroundColor = "#FFFFFF";
		this.pDocument.body.style.overflow = "hidden";

		this.pDiv = this.pDocument.getElementById("__bx_iframe_cell");
		this.bCreated = true;
	}

	_BXPopupWindow.prototype.Hide = function(name)
	{
		if(!this.bShowed)
			return;

		this.pFrame.width = "0";
		this.pFrame.height = "0";
		this.bShowed = false;
	}

	_BXPopupWindow.prototype.GetDocument = function()
	{
		if(!this.pFrame)
			this.Create();

		return this.pDocument;
	}

	_BXPopupWindow.prototype.Show = function (px, py, pNode, name)
	{
		if(!this.pFrame)
			this.Create();

		while(this.pDiv.childNodes.length>0)
			this.pDiv.removeChild(this.pDiv.childNodes[0]);

		this.pDiv.appendChild(pNode);

		this.pFrame.style.left = "1px";
		this.pFrame.style.top = "1px";
		this.pFrame.width = "300px";
		this.pFrame.height = "1px";


		var dx = this.pDiv.offsetWidth, dy = this.pDiv.offsetHeight;

		if(typeof(px) == 'object')
		{
			if(parseInt(document.body.clientWidth) - (parseInt(px[0]) - parseInt(document.body.scrollLeft) + parseInt(dx))<0)
				px = parseInt(px[1]) - parseInt(dx);
			else
				px = px[0];
		}

		if(typeof(py) == 'object')
		{
			if(document.body.clientHeight - (parseInt(py[1]) - parseInt(document.body.scrollTop) + parseInt(dy)) < 0)
				py = parseInt(py[0]) - parseInt(dy);
			else
				py = py[1];
		}

		this.pFrame.style.left = px + "px";
		this.pFrame.style.top = py + "px";
		this.pFrame.width = dx + "px";
		this.pFrame.height = dy + "px";

		if(BXIsIE())
			this.pFrame.focus();
		else
			this.pFrame.contentWindow.focus();

		this.bShowed = true;
	}

	_BXPopupWindow.prototype.CreateElement = BXCreateElement;

	_BXPopupWindow.prototype.CreateCustomElement = function(sTagName, arParams)
	{
		var ob = new window[sTagName]();
		ob.pMainObj = this;
		ob.pDocument = this.pDocument;
		ob.CreateElement = BXCreateElement;
		if(arParams)
		{
			var sParamName;
			for(sParamName in arParams)
				ob[sParamName] = arParams[sParamName];
		}
		ob._Create();
		return ob;
	}

	_BXPopupWindow.prototype.SetCurStyles = function ()
	{
		var x1 = document.styleSheets;
		var rules, err=false, cssText = '', j;
		if(!x1[0].cssRules)
		{
			for(var i=x1.length-1; i>=0; i--)
			{
				if (i >= x1.length - 2 && x1[i].cssText.indexOf('bxed') != -1)
				{
					err = true;
					cssText += x1[i].cssText;
				}

			}
			if (!err)
				_alert('Doesn\'t loaded styles: \'common.js: SetCurStyles\'');
		}
		else
		{
			for(var i=x1.length-1; i>=0; i--)
			{
				try{
				rules = (x1[i].rules ? x1[i].rules : x1[i].cssRules);
				for(j = 0; j < rules.length; j++)
				{
					if(rules[j].cssText)
						cssText += rules[j].cssText + '\n';
					else
						cssText += rules[j].selectorText + '{' + rules[j].style.cssText + '}\n';
				}
				}catch(e){continue;}
			}
		}

		var cur = this.pDocument.getElementsByTagName("STYLE");
		for(i=0; i<cur.length; i++)
			cur[i].parentNode.removeChild(cur[i]);
		var xStyle = this.CreateElement("STYLE");
		this.pDocument.getElementsByTagName("HEAD")[0].appendChild(xStyle);

		if(BXIsIE())
			this.pDocument.styleSheets[0].cssText = cssText;
		else
			xStyle.appendChild(this.pDocument.createTextNode(cssText));
	}
}
var BXPopupWindow = new _BXPopupWindow();


function addEvent(el, evname, func, p)
{
	el["on" + evname] = func;
	ar_EVENTS.push([el,evname,func]);
}

function addEvent1(el, evname, func, p)
{
	if(el.addEventListener)
		el.addEventListener(evname, func, (p?false:p));
	else
		el["on" + evname] = func;
}

function addAdvEvent(el, evname, func, p)
{
	if(el.addEventListener)
		el.addEventListener(evname, func, (p?false:p));
	else if (el.attachEvent)
		el.attachEvent('on'+evname,func);
}


function removeAdvEvent(el, evname, func, p)
{
	if(el.removeEventListener)
		el.removeEventListener(evname, func, (p?false:p));
	else
		el.detachEvent('on'+evname, func);
}


function removeEvent(el, evname, func, p)
{
	el['on' + evname] = null;
	if(el.removeEventListener)
		el.removeEventListener(evname, func, (p?false:p));
	else
	{
		el.detachEvent('on'+evname,func);
	}
}

var BXCustomElementEvents = [];

function addCustomElementEvent(elEvent, sEventName, oEventHandler, oHandlerParent)
{
	elEvent.w = sEventName;
	if(!elEvent.__eventHandlers)
		elEvent.__eventHandlers = [];
	if(!elEvent.__eventHandlers[sEventName] || elEvent.__eventHandlers[sEventName].length<=0)
	{
		elEvent.__eventHandlers[sEventName] = [];

		if(elEvent.addEventListener)
			elEvent.addEventListener(sEventName, OnCustomElementEvent, false);
		else
			elEvent["on" + sEventName] = OnCustomElementEvent;
	}

	elEvent.__eventHandlers[sEventName].push([oHandlerParent, oEventHandler]);
}

function OnCustomElementEvent(e)
{
	if(!e)
		e = window.event;

	var arHandlers = this.__eventHandlers[e.type];
	for(var i=0; i<arHandlers.length; i++)
		arHandlers[i][1].call(arHandlers[i][0], e);
}

function delCustomElementEvent(elEvent, sEventName, oEventHandler)
{
	if(!elEvent.__eventHandlers || !elEvent.__eventHandlers[sEventName])
		return false;

	var arEvents = elEvent.__eventHandlers[sEventName];
	var arNewEvents = [];
	for(var i=0; i<arEvents.length; i++)
	{
		if(arEvents[i][1]!=oEventHandler)
			arNewEvents.push(arEvents[i]);
	}
	arEvents = elEvent.__eventHandlers[sEventName] = arNewEvents;

	//Deleting event handler
	if(arEvents.length<=0)
		removeEvent(elEvent, sEventName, OnCustomElementEvent);

}


function BXIsIE()
{
	return (document.all?true:false);
}

function BXIsDoctype()
{
	if (document.compatMode)
		return (document.compatMode == "CSS1Compat");

	if (document.documentElement && document.documentElement.clientHeight)
		return true;

	return false;
}


function BXElementEqual(pElement1, pElement2)
{
	if(pElement1 == pElement2)
		return true;

	return false;

	if(!pElement1)
		return false;
	if(!pElement2)
		return false;
	if(pElement1.nodeType != 1)
		return false;
	if(pElement2.nodeType != 1)
		return false;
	if(pElement1.tagName != pElement2.tagName)
		return false;
	if(pElement1.id != pElement2.id)
		return false;
	if(pElement1.offsetHeight != pElement2.offsetHeight)
		return false;
	if(pElement1.offsetLeft != pElement2.offsetLeft)
		return false;
	if(pElement1.offsetTop != pElement2.offsetTop)
		return false;
	if(pElement1.clientHeight != pElement2.clientHeight)
		return false;
	if(pElement1.clientWidth != pElement2.clientWidth)
		return false;

	return true;
}

function BXFindParentElement(pElement1, pElement2)
{
	var p, arr1 = [], arr2 = [];
	while((pElement1 = pElement1.parentNode)!=null)
		arr1[arr1.length] = pElement1;
	while((pElement2 = pElement2.parentNode)!=null)
		arr2[arr2.length] = pElement2;

	var min, diff1 = 0, diff2 = 0;
	if(arr1.length<arr2.length)
	{
		min = arr1.length;
		diff2 = arr2.length - min;
	}
	else
	{
		min = arr2.length;
		diff1 = arr1.length - min;
	}

	for(var i=0; i<min-1; i++)
	{
		if(BXElementEqual(arr1[i+diff1], arr2[i+diff2]))
			return arr1[i+diff1];
	}
	return arr1[0];
}

// return position of the cursor
function getRealMousePos(e, pMainObj, bEditorFrame)
{
	if(window.event)
		e = window.event;

	if(e.pageX || e.pageY)
	{
		e.realX = e.pageX;
		e.realY = e.pageY;
	}
	else if(e.clientX || e.clientY)
	{
		e.realX = e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft) - document.documentElement.clientLeft;
		e.realY = e.clientY + (document.documentElement.scrollTop || document.body.scrollTop) - document.documentElement.clientTop;
	}

	if (bEditorFrame) // if peditorFrame
	{
		if (!(arFramePos = CACHE_DISPATCHER['pEditorFrame']))
			CACHE_DISPATCHER['pEditorFrame'] = arFramePos = GetRealPos(pMainObj.pEditorFrame);

		e.realX += arFramePos.left;
		e.realY += arFramePos.top;

		var scrollLeft, scrollTop;
		if (BXIsIE() && !pMainObj.bFullscreen)
		{
			if (IEplusDoctype)
			{
				e.realX -= document.documentElement.scrollLeft;
				e.realY -= document.documentElement.scrollTop;
			}
			else
			{
				e.realX -= document.body.scrollLeft;
				e.realY -= document.body.scrollTop;
			}
		}
		else if (!BXIsIE() && !BXIsDoctype)  // FF without doctype
		{
			e.realX -= pMainObj.pEditorDocument.body.scrollLeft;
			e.realY -= pMainObj.pEditorDocument.body.scrollTop;
		}
	}
	return e;
}


function GetRealPos(el)
{
	if(!el || !el.offsetParent)
		return false;

	var res = [];
	res.left = el.offsetLeft;
	res.top = el.offsetTop;
	var objParent = el.offsetParent;
	while(objParent.tagName.toUpperCase() !== "BODY" && objParent.tagName.toUpperCase() !== "HTML")
	{
		res.left += objParent.offsetLeft;
		res.top += objParent.offsetTop;
		objParent = objParent.offsetParent;
	}
	res.right = res.left + el.offsetWidth;
	res.bottom=res.top + el.offsetHeight;

	return res;
}


function BXAlignToPos(pos, dir_rtl)
{
	var oW = jsUtils.GetWindowInnerSize();
	if (oW.innerWidth < pos.right || dir_rtl)
	{
		var dw = pos.right - pos.left;
		pos.right -= dw;
		pos.left -= dw;
	}

	if (oW.innerHeight < pos.bottom)
	{
		var dh = pos.bottom - pos.top;
		pos.top -= dh;
		pos.bottom -= dh;
	}
	return pos;
}


function GetDisplStr(status)
{
	if(status == 0)
		return "none";
	if(status == 1 && document.all)
		return "block";
	if(status == 1)
		return null;
}

function bxhtmlspecialchars(str)
{
	if(typeof(str)!='string')
		return str;
	str = str.replace(/&/g, '&amp;');
	str = str.replace(/"/g, '&quot;');
	str = str.replace(/</g, '&lt;');
	str = str.replace(/>/g, '&gt;');
	return str;
}


// Global object - collect global event handlers
function BXEventDispatcher()
{
	this.eventsSet = false;
	this.arHandlers = [];
	this.arEditorHandlers = [];
	this.arEditors = [];


	BXEventDispatcher.prototype.OnEvent = function(pDoc, e)
	{
		//try
		//{
			var arFramePos;

			if(window.event)
				e = window.event;

			if(pDoc["className"] && (pDoc.className == 'pEditorDocument' || pDoc.className == 'pSourceDocument'))
			{
				if(pDoc.pMainObj.pEditorWindow.event)
					e = pDoc.pMainObj.pEditorWindow.event;
				else if (!(arFramePos = CACHE_DISPATCHER['pEditorFrame']))
					CACHE_DISPATCHER['pEditorFrame'] = arFramePos = GetRealPos(pDoc.pMainObj.pEditorFrame);
			}

			var arHandlers = pBXEventDispatcher.arHandlers[e.type];
			var arHLen = arHandlers.length;
			if (!arHLen)
				return;

			if(e.target)
				e.targetElement = e.target;
			else if(e.srcElement)
				e.targetElement = e.srcElement;

			if(e.targetElement.nodeType == 3)
				e.targetElement = e.targetElement.parentNode;

			if(e.pageX || e.pageY)
			{
				e.realX = e.pageX;
				e.realY = e.pageY;
			}
			else if(e.clientX || e.clientY)
			{
				e.realX = e.clientX + document.body.scrollLeft;
				e.realY = e.clientY + document.body.scrollTop;
			}

			if(arFramePos)
			{
				e.realX += arFramePos["left"];
				e.realY += arFramePos["top"];
			}

			var res = true;
			for(var i = 0; i < arHLen; i++)
			{
				if(!arHandlers[i](e))
					res = false;
			}
			return res;
		//}
		//catch(e){}
	}


	//Method add handler of pEventHandler for global event eventName
	BXEventDispatcher.prototype.AddHandler = function (eventName, pEventHandler)
	{
		if(!this.arHandlers[eventName])
		{
			this.arHandlers[eventName] = [];
			for(var i=0; i<this.arEditors.length; i++)
			{
				var pObject = this.arEditors[i];
				addEvent1(pObject.pDocument, eventName, function (e) {pBXEventDispatcher.OnEvent(pObject.pDocument, e);});
				addEvent1(pObject.pEditorDocument, eventName,  function (e) {pBXEventDispatcher.OnEvent(pObject.pEditorDocument, e);});
			}
		}
		this.arHandlers[eventName].push(pEventHandler);
	}

	BXEventDispatcher.prototype.SetEvents = function(pDocument)
	{
		for(var eventName in this.arHandlers)
		{
			for(var i=0; i<this.arHandlers[eventName].length; i++)
				addAdvEvent(pDocument, eventName, window['OnDispatcherEvent_pEditorDocument_' + name_cur_obj]);
		}
		this.eventsSet=true;
	}

	//Internal method for adding BXHTMLEditor-type object
	BXEventDispatcher.prototype.__Add = function (pObject)
	{
		for(var eventName in this.arHandlers)
		{
			if(this.arEditors.length <= 0)
				addAdvEvent(pObject.pDocument, eventName, window['OnDispatcherEvent_pDocument_' + name_cur_obj]);

			addAdvEvent(pObject.pEditorDocument, eventName, window['OnDispatcherEvent_pEditorDocument_'+name_cur_obj]);
		}
		this.arEditors[this.arEditors.length] = pObject;
	}

	// Setting cursor for all documents....
	BXEventDispatcher.prototype.SetCursor = function (sCursor)
	{
		for(var i=0; i<this.arEditors.length; i++)
		{
			var pObject = this.arEditors[i];
			pObject.pDocument.body.style.cursor = sCursor;
			pObject.pEditorDocument.body.style.cursor = sCursor;
		}
	}

	BXEventDispatcher.prototype.AddEditorHandler = function (eventName, pEventHandler)
	{
		if(!this.arEditorHandlers[eventName])
			this.arEditorHandlers[eventName] = [];
		this.arEditorHandlers[eventName][this.arEditorHandlers[eventName].length] = pEventHandler;
	}

	BXEventDispatcher.prototype.OnEditorEvent = function (eventName, pMainObj, arParams)
	{
		if(!this.arEditorHandlers[eventName])
			return true;

		var res = true;
		for(var i=0; i<this.arEditorHandlers[eventName].length; i++)
			if(!this.arEditorHandlers[eventName][i](pMainObj, arParams))
				res = false;

		return res;
	}
}

function BXPreventDefault(e)
{
	if(e.stopPropagation)
	{
		e.preventDefault();
		e.stopPropagation();
	}
	else
	{
		e.cancelBubble = true;
		e.returnValue = false;
	}
	return false;
}

window.BXLoadJSFiles = function(arJs, oCallBack, bFullPath)
{
	var load_js = function(ind)
	{
		if (ind >= arJs.length)
		{
			oCallBack.func.apply(oCallBack.obj);
			return;
		}

		//try{
			var oSript = document.body.appendChild(document.createElement('script'));
			oSript.src = (!bFullPath ? "/bitrix/admin/htmleditor2/" : '') + arJs[ind];
			if (BXIsIE())
			{
				oSript.onreadystatechange = function()
				{
					if (oSript.readyState == 'loaded')
						load_js(++ind);
				};
			}
			else
			{
				oSript.onload = function(){setTimeout(function (){load_js(++ind);}, 50);};
			}
		//}catch(e) {	_alert('ERROR:  BXLoadJSFiles >> name = '+arJs[ind]);}
	};
	load_js(0);
};


// BXPreloader - special object, which one after another load functions and call they by callback...
// at the end oFinalCallback will be called
function BXPreloader(arSteps, oFinalCallback)
{
	this.oFinalCallback = oFinalCallback;
	this.arSteps = arSteps;
	this.Length = arSteps.length;
	this.curInd = 0;
	this.finalLoaded = false;
}

BXPreloader.prototype.LoadStep = function()
{
	if (this.curInd >= this.Length)
	{
		if (this.finalLoaded)
			return;

		this.finalLoaded = true;
		var o = this.oFinalCallback;
		if (!o.params)
			o.params = [];

		if (o.obj)
			o.func.apply(o.obj, o.params);
		else
			o.func(o.params);
		return;
	}

	var o = this.arSteps[this.curInd];
	this.curInd++;
	if (!o.params)
		o.params = [];
	var oCallBack = {obj: this, func: this.LoadStep};

	try
	{
		if (o.obj)
			o.func.call(o.obj, oCallBack, o.params);
		else
			o.func(oCallBack, o.params);
	}
	catch(e)
	{
		this.LoadStep();
	}
}

BXPreloader.prototype.AddStep = function(oStep)
{
	this.arSteps.push(oStep);
	this.Length++;
}

BXPreloader.prototype.RemoveStep = function(ind)
{
	if (ind == -1)
	{
		delete this.arSteps[this.Length];
		this.Length--;
	}
}

// CONTEXT MENU
function BXContextMenu() {}

BXContextMenu.prototype.Create = function(zIndex, dxShadow, oPos, pElement, arParams)
{
	this.pref = this.pMainObj.name.toUpperCase()+'_';
	this.oDiv = document.body.appendChild(BXCreateElement('DIV', {className: 'bx_ed_context_menu', id: this.pref + '_BXContextMenu'}, {position: 'absolute', zIndex: 1500, left: '-1000px', top: '-1000px', visibility: 'hidden'}, document));
	this.oDiv.innerHTML = '<table cellpadding="0" cellspacing="0"><tr><td class="popupmenu"><table cellpadding="0" cellspacing="0" id="' + this.pref + '_BXContextMenu_items"><tr><td></td></tr></table></td></tr></table>';

	// Part of logic of JCFloatDiv.Show()   Prevent bogus rerendering window in IE... And SpeedUp first context menu calling
	document.body.appendChild(BXCreateElement('IFRAME',{id: this.pref + '_BXContextMenu_frame', src: "javascript:void(0)"}, {position: 'absolute', zIndex: 1495, left: '-1000px', top: '-1000px', visibility: 'hidden'}, document));

	this.menu = new PopupMenu(this.pref + '_BXContextMenu');
};


BXContextMenu.prototype.Show = function(zIndex, dxShadow, oPos, pElement, arParams, pMainObj, dir_rtl)
{
	this.pMainObj = pMainObj;
	this.oPrevRange = BXGetSelectionRange(this.pMainObj.pEditorDocument, this.pMainObj.pEditorWindow);
	this.menu.PopupHide();
	if (!this.FetchAndBuildItems(pElement, arParams))
		return;

	addEvent1(this.pMainObj.pEditorDocument, "click", BXContextMenuOnclick);

	if (!isNaN(zIndex))
		this.oDiv.style.zIndex = zIndex;

	this.oDiv.style.width = parseInt(this.oDiv.firstChild.offsetWidth) + 'px';
	var w = parseInt(this.oDiv.offsetWidth);
	var h = parseInt(this.oDiv.offsetHeight);
	oPos.right = oPos.left + w;
	oPos.bottom = oPos.top;
	this.menu.PopupShow(BXAlignToPos(oPos, dir_rtl), dxShadowImgPath);
};

BXContextMenu.prototype.FetchAndBuildItems = function(pElement, arParams)
{
	var pElementTemp, i, k, arMenuItems = [], el, el_params, arUsed = [], strPath, strPath1, __bxtagname = false;
	// Handling and creation menu elements array
	// Single custom element
	if (arParams && arParams.bxtagname)
		__bxtagname = arParams.bxtagname;
	else if (pElement && pElement.getAttribute)
		__bxtagname = pElement.getAttribute('__bxtagname');

	//if (pElement && pElement.getAttribute && (__bxtagname = pElement.getAttribute('__bxtagname')))
	if (__bxtagname)
	{
		strPath1 = __bxtagname.toUpperCase();
		if (arCMButtons[strPath1])
			for(i = 0, k = arCMButtons[strPath1].length; i < k; i++)
				arMenuItems.push(arCMButtons[strPath1][i]);
	}
	else // Elements in editor iframe
	{
		var pElement = this.pMainObj.GetSelectionObject();
		//Adding to default list
		for(i = 0; i < arCMButtons["DEFAULT"].length; i++)
			arMenuItems.push(arCMButtons["DEFAULT"][i]);
		//Adding other elements
		while(pElement && (pElementTemp = pElement.parentNode) != null)
		{
			if(pElementTemp.nodeType == 1 && pElement.tagName && (strPath = pElement.tagName.toUpperCase()) && strPath != 'TBODY' && !arUsed[strPath])
			{
				strPath1 = strPath;
				if (pElement.getAttribute && (__bxtagname = pElement.getAttribute('__bxtagname')))
					strPath1 = __bxtagname.toUpperCase();

				arUsed[strPath] = pElement;
				if(arCMButtons[strPath1])
				{
					if (arMenuItems.length > 0)
						arMenuItems.push('separator');
					for(i = 0, k = arCMButtons[strPath1].length; i < k; i++)
						arMenuItems.push(arCMButtons[strPath1][i]);
				}
			}
			else
			{
				pElement = pElementTemp;
				continue;
			}
		}
	}
	if (arMenuItems.length == 0)
		return false;
	//Cleaning menu
	var contTbl = document.getElementById(this.menu.menu_id+'_items');
	while(contTbl.rows.length>0)
		contTbl.deleteRow(0);
	return this.BuildItems(arMenuItems, arParams, contTbl);
};

BXContextMenu.prototype.BuildItems = function(arMenuItems, arParams, contTbl, parentName)
{
	var n = arMenuItems.length;
	var __this = this;
	var arSubMenu = {};
	this.subgroup_parent_id = '';
	this.current_opened_id = '';

	var _hide = function()
	{
		var cs = document.getElementById("__curent_submenu");
		if (!cs)
			return;
		_over(cs);
		__this.current_opened_id = '';
		__this.subgroup_parent_id = '';
		cs.style.display = "none";
		cs.id = "";
	};

	var _over = function(cs)
	{
		if (!cs)
			return;
		var t = cs.parentNode.nextSibling;
		t.parentNode.className = '';
	};

	var _refresh = function() {setTimeout(function() {__this.current_opened_id = '';__this.subgroup_parent_id = '';}, 400);}

	//Creation menu elements
	for(var i = 0; i < n; i++)
	{
		var row = contTbl.insertRow(-1);
		var cell = row.insertCell(-1);
		if(arMenuItems[i] == 'separator')
		{
			cell.innerHTML = '<div class="popupseparator"></div>';
		}
		else
		{
			if (arMenuItems[i].isgroup === true)
			{
				var c = (BXIsIE()) ? 'arrow_ie' : 'arrow';

				cell.innerHTML =
				'<div id="_oSubMenuDiv_'+arMenuItems[i].id+'" style="position: relative;"></div>'+
				'<table cellpadding="0" cellspacing="0" class="popupitem" id="'+arMenuItems[i].id+'">\n'+
				'	<tr>\n'+
				'		<td class="gutter"></td>\n'+
				'		<td class="item" title="'+((arMenuItems[i].title) ? arMenuItems[i].title : arMenuItems[i].name)+'">'+arMenuItems[i].name+'</td>\n'+
				'		<td class="'+c+'"></td>\n'+
				'	</tr>\n'+
				'</table>';
				var oTable = cell.childNodes[1];
				var _LOCAL_CACHE = {};
				arSubMenu[arMenuItems[i].id] = arMenuItems[i].elements;

				oTable.onmouseover = function(e)
				{
					this.className = 'popupitem popupitemover';

					var _this = this;
					_over(document.getElementById("__curent_submenu"));
					setTimeout(function()
					{
						//_this.parentNode.className = 'popup_open_cell';
						if (__this.current_opened_id && __this.current_opened_id == __this.subgroup_parent_id)
						{
							_refresh();
							return;
						}
						if (_this.className == 'popupitem')
							return;
						_hide();
						__this.current_opened_id = _this.id;

						if (!_LOCAL_CACHE[_this.id])
						{
							var _oSubMenuDiv = document.getElementById("_oSubMenuDiv_" + _this.id);
							var left = parseInt(oTable.offsetWidth) + 1 + 'px';
							var oSubMenuDiv = BXCreateElement('DIV', {'className' : 'popupmenu'}, {position: 'absolute', zIndex: 1500, left: left, top: '-1px'}, document);
							_oSubMenuDiv.appendChild(oSubMenuDiv);
							oSubMenuDiv.onmouseover = function(){_this.parentNode.className = 'popup_open_cell';};
							_LOCAL_CACHE[_this.id] = oSubMenuDiv;

							var contTbl = oSubMenuDiv.appendChild(BXCreateElement('TABLE', {cellPadding:0, cellSpacing:0}, {}, document));
							__this.BuildItems(arSubMenu[_this.id], arParams, contTbl, _this.id);
						}
						else
							oSubMenuDiv = _LOCAL_CACHE[_this.id];

						//oSubMenuDiv.style.visibility = "visible";
						oSubMenuDiv.style.display = "block";
						oSubMenuDiv.id = "__curent_submenu";
					}, 400);
				};


				oTable.onmouseout = function(e){this.className = 'popupitem';};
				continue;
			}

			var el_params = arMenuItems[i][1];
			var _atr = '';

			if(arMenuItems[i][1].iconkit)
				_atr = 'style="background-image:url(' + image_path + '/'+arMenuItems[i][1].iconkit+');" class="bxedtbutton" id="bx_btn_' + arMenuItems[i][1].id+'"';
			else if(arMenuItems[i][1].src)
				_atr = 'style="background-image:url(' + image_path + '/'+arMenuItems[i][1].src+');" ';

			var _innerHTML =
				'<table cellpadding="0" cellspacing="0" class="popupitem">\n'+
				'	<tr>\n'+
				'			<td class="gutter"><div '+ _atr+'></div></td>\n'+
				'			<td class="item" title="'+((arMenuItems[i][1].title) ? arMenuItems[i][1].title : arMenuItems[i][1].name)+'"'+'>'+arMenuItems[i][1].name+'</td>\n'+
				'		</tr>\n'+
				'	</table>';
			cell.innerHTML = _innerHTML;

			var oTable = cell.firstChild;

			var bDisable = (arMenuItems[i][1] && arMenuItems[i][1].disablecheck) ? arMenuItems[i][1].disablecheck(oTable, oBXContextMenu.pMainObj) : false;

			if (!bDisable)
			{
				oTable.pMainObj = oBXContextMenu.pMainObj;
				oTable.handler = arMenuItems[i][1].handler;
				oTable.cmd = arMenuItems[i][1].cmd;

				oTable.onmouseover = function(e)
				{
					if (parentName)
					{
						__this.subgroup_parent_id = parentName;
					}
					else
					{
						setTimeout(function()
						{
							if (__this.current_opened_id && __this.current_opened_id == __this.subgroup_parent_id)
							{
								_refresh();
								return;
							}
							_hide();
						}, 400);
					}

					this.className='popupitem popupitemover';
				}

				oTable.onmouseout = function(e){this.className = 'popupitem';};
				oTable.onclick = function(e)
				{
					__this.pMainObj.SetFocus();
					var res = false;
					if (BXIsIE()) //Restore selection for IE
						BXSelectRange(__this.oPrevRange, __this.pMainObj.pEditorDocument, __this.pMainObj.pEditorWindow);

					if (this.handler)
						if(this.handler(arParams) !== false)
							res = true;

					if (!res)
						res = this.pMainObj.executeCommand(this.cmd);

					__this.pMainObj.SetFocus();
					oBXContextMenu.menu.PopupHide();
					return res;
				};
			}
			else
			{
				oTable.className = 'popupitem popupitemdisabled';
			}

			oTable.id=null;
		}
	}

	this.oDiv.style.width = contTbl.parentNode.offsetWidth;
	return true;
};


function BXDeleteNode(pNode)
{
	while(pNode.childNodes.length>0)
		pNode.parentNode.insertBefore(pNode.childNodes[0], pNode);

	pNode.parentNode.removeChild(pNode);
}


function BXIsArrayAssoc(ob)
{
	for(var i in ob)
	{
		if(parseInt(i)!=i)
			return true;
	}
	return false;
}


function BXSerializeAttr(ob, arAttr)
{
	var new_ob = {}, sAttrName;
	for(var i=0; i<arAttr.length; i++)
	{
		sAttrName = arAttr[i];
		if(ob[sAttrName])
			new_ob[sAttrName] = ob[sAttrName];
	}
	return BXSerialize(new_ob);
}

function BXUnSerializeAttr(sOb, ob, arAttr)
{
	var new_ob = BXUnSerialize(sOb);
	for(var sAttrName in new_ob)
		ob[sAttrName] = new_ob[sAttrName];
}

function BXSerialize(ob)
{
	var res, i, key;

	if(typeof(ob)=='object')
	{
		res = [];
		if(ob instanceof Array && !BXIsArrayAssoc(ob))
		{
			for(i=0; i<ob.length; i++)
				res.push(BXSerialize(ob[i]));

			return '[' + res.join(', ', res) + ']';
		}

		for(key in ob)
			res.push("'"+key+"': "+BXSerialize(ob[key]));

		return "{" + res.join(", ", res) + "}";
	}

	if(typeof(ob)=='boolean')
	{
		if(ob)
			return "true";
		return "false";
	}

	if(typeof(ob)=='number')
		return ob;

	res = ob;
	res = res.replace(/\\/g, "\\\\");
	res = res.replace(/\n/g, "\\n");
	res = res.replace(/\r/g, "\\r");
	res = res.replace(/'/g, "\\'");

	return "'"+res+"'";
}

function BXUnSerialize(str)
{
	var res;
	eval("res = "+str);
	return res;
}


function BXPHPVal(ob, pref)
{
	var res, i, key;
	if(typeof(ob)=='object')
	{
		res = [];
		if(ob instanceof Array && !BXIsArrayAssoc(ob))
		{
			for(i=0; i<ob.length; i++)
				res.push(BXPHPVal(ob[i], (pref?pref:'undef')+'[]'));
		}
		else
		{
			for(key in ob)
				res.push(BXPHPVal(ob[key], (pref?pref+'['+key+']':key)));
		}

		return res.join("&", res);
	}

	if(typeof(ob)=='boolean')
	{
		if(ob)
			return pref+'=1';
		return pref+"=0";
	}

	return pref+'='+escape(ob);
	return pref+'='+ob;
}

function BXPHPValArray(ob)
{
	var res, i, key;
	if(typeof(ob)=='object')
	{
		res = [];
		if(ob instanceof Array && !BXIsArrayAssoc(ob))
		{
			for(i=0; i<ob.length; i++)
				res.push(BXPHPValArray(ob[i]));
			return 'Array(' + res.join(', ', res) + ')';
		}
		for(key in ob)
			res.push("'"+key+"'=> "+BXPHPValArray(ob[key]));
		return "Array(" + res.join(", ", res) + ")";
	}

	if(typeof(ob)=='boolean')
	{
		if(ob)
			return "true";
		return "false";
	}

	if(typeof(ob)=='number')
		return ob;

	res = ob;
	res = res.replace(/\\/g, "\\\\");
	res = res.replace(/'/g, "\\'");

	return "'"+res+"'";
}

// Initialization of global object
var pBXEventDispatcher = new BXEventDispatcher();

var BXEditorLoaded = false;
var arBXEditorObjects = [];
function BXEditorLoad()
{
	BXEditorLoaded = true;
	for(var i = 0; i < arBXEditorObjects.length; i++)
		arBXEditorObjects[i].OnBeforeLoad();
}

function BXEditorRegister(obj)
{
	arBXEditorObjects.push(obj);
}

window.BXFindParentByTagName = function (pElement, tagName)
{
	tagName = tagName.toUpperCase();
	while(pElement && (pElement.nodeType!=1 || pElement.tagName.toUpperCase() != tagName))
		pElement = pElement.parentNode;
	return pElement;
}

function BXGetSelection(oDoc,oWin)
{
	if (!oDoc)
		oDoc = document;
	if (!oWin)
		oWin = window;

	var oSel = false;
	if (oWin.getSelection)
		oSel = oWin.getSelection();
	else if (oDoc.getSelection)
		oSel = oDoc.getSelection();
	else if (oDoc.selection)
		oSel = oDoc.selection;
	return oSel;
}

function BXGetSelectionRange(oDoc, oWin)
{
	try
	{
		if (!oDoc)
			oDoc = document;
		if (!oWin)
			oWin = window;

		var oRange, oSel = BXGetSelection(oDoc,oWin);
		if (oSel)
		{
			if (oDoc.createRange)
				oRange = oSel.getRangeAt(0);
			else
				oRange = oSel.createRange();
		}
		else
			oRange = false;

			return oRange;
	}
	catch(e){/*_alert('ERROR: BXGetSelectionRange');*/}
}


function BXClearMozDirtyInRange(pMainObj)
{
	return;
	var oRange = BXGetSelectionRange(pMainObj.pEditorDocument, pMainObj.pEditorWindow);
	var startCont = oRange.startContainer;
	var endCont = oRange.endContainer;
	var startOffset = oRange.startOffset;
	var endOffset = oRange.endOffset;

	var arNodes = [];
}


function BXSelectRange(oRange,oDoc,oWin)
{
	if (!oDoc)
		oDoc = document;
	if (!oWin)
		oWin = window;

	BXClearSelection(oDoc,oWin);

	if (oDoc.createRange)
	{
		//FF, Opera
		var oSel = oWin.getSelection();
		oSel.removeAllRanges();
		oSel.addRange(oRange);
	}
	else
	{
		//IE
		oRange.select();
	}
}

var preventselect = function(e){return false;};

function BXClearSelection(oDoc,oWin)
{
	if (!oDoc)
		oDoc = document;
	if (!oWin)
		oWin = window;

	if (oWin.getSelection)
		oWin.getSelection().removeAllRanges();
	else
		oDoc.selection.empty();
}


function PreventEnterClosing(e)
{
	if(!e) e = window.event;
	if(e.keyCode == 13)
	{
		var target = e.target || e.srcElement;
		if (target && target.nodeName.toUpperCase() == 'TEXTAREA')
			return true;
		return BXPreventDefault(e);
	}
}

// API
function BXEditorUtils(){this.PHPParser = new __PHPParser();}
function __PHPParser(){}
BXEditorUtils.prototype.addContentParser = function(func){arContentParsers.push(func);};

BXEditorUtils.prototype.addPHPParser = function(func, pos, extra_access)
{
	if (!extra_access)
		extra_access == false;
	if (!extra_access && limit_php_access)
		return;

	if (pos==undefined || pos ===false)
		arPHPParsers.push(func);
	else
	{
		if (pos<0)
			pos = 0;
		else if (pos>arPHPParsers.length+1)
			pos = arPHPParsers.length+1;

		var newAr = arPHPParsers.slice(0,pos);
		newAr.push(func);
		newAr = newAr.concat(arPHPParsers.slice(pos));
		arPHPParsers = newAr;
		newAr = null;
	}
}

BXEditorUtils.prototype.addDOMHandler = function(func) {arDOMHandlers.push(func);};
BXEditorUtils.prototype.addUnParser = function(func) {arUnParsers.push(func);};
BXEditorUtils.prototype.addContentUnParser = function(func) {arContentUnParsers.push(func);};
BXEditorUtils.prototype.addNodeUnParser = function(func) {arNodeUnParsers.push(func);};
BXEditorUtils.prototype.addCssLinkToFrame = function(href, frame)
{
	return this.addLinkToFrame(href, 'stylesheet', 'text/css', frame);
};

BXEditorUtils.prototype.addLinkToFrame = function(href, rel, type, fo)
{
	if (!fo.contentWindow.document.getElementsByTagName) return;
	var oDoc = fo.contentWindow.document;
	var l = oDoc.createElement('LINK');
	l.href = href;
	if (rel) l.rel = rel;
	if (type) l.type = type;

	var heads = oDoc.getElementsByTagName('HEAD');
	if (heads && heads[0])
		heads[0].appendChild(l);

	oDoc = heads = null;
	return l;
};

BXEditorUtils.prototype.ResetSelectionState = function(pMainObj)
{
	if (BXIsIE())
	{
		pMainObj.pEditorDocument.body.contentEditable = false;
		pMainObj.pEditorDocument.body.contentEditable = true;
	}
	else
	{
		pMainObj.pEditorDocument.designMode='off';
		pMainObj.pEditorDocument.designMode='on';
	}
};

BXEditorUtils.prototype.CancelEvent = function(e) {return BXPreventDefault(e);};

BXEditorUtils.prototype.GetSelectionAnchor = function(pMainObj)
{
	// Get selection
	// TODO: Multiselection
	if (!BXIsIE())
		return pMainObj.pEditorFrame.contentWindow.getSelection().anchorNode;
	var r = pMainObj.pEditorDocument.selection.createRange();
	var x = null;
	if (r.parentElement)
		x = r.parentElement();
	else if (r.item)
		x = r.item(0);
	r = null;
	return x;
};


__PHPParser.prototype.trimPHPTags = function(str)
{
	if (str.substr(0, 2)!="<?")
		return str;

	if(str.substr(0, 5).toLowerCase()=="<?php")
		str = str.substr(5);
	else
		str = str.substr(2);

	str = str.substr(0, str.length-2);
	return str;
}

__PHPParser.prototype.trimQuotes = function(str, qoute)
{
	if (qoute==undefined)
	{
		f_ch = str.substr(0,1);
		l_ch = str.substr(0,1);
		if ((f_ch=='"' && l_ch=='"') || (f_ch=='\'' && l_ch=='\''))
			str = str.substring(1, str.length-1);
	}
	else
	{
		if (!qoute.length)
			return str;
		f_ch = str.substr(0,1);
		l_ch = str.substr(0,1);
		qoute = qoute.substr(0,1);
		if (f_ch==qoute && l_ch==qoute)
			str = str.substring(1, str.length-1);
	}
	return str;
}

__PHPParser.prototype.cleanCode = function(str)
{
	var bSlashed = false;
	var bInString = false;
	var new_str = "";
	var i=-1, ch, string_tmp = "", ti, quote_ch, max_i=-1;

	while(i<str.length-1)
	{
		i++;
		ch = str.substr(i, 1);
		if(!bInString)
		{
			if(ch == "/" && i+1<str.length)
			{
				ti = 0;
				if(str.substr(i+1, 1)=="*" && ((ti = str.indexOf("*/", i+2))>=0))
					ti += 2;
				else if(str.substr(i+1, 1)=="/" && ((ti = str.indexOf("\n", i+2))>=0))
					ti += 1;

				if(ti>0)
				{
					if(i>ti)
						alert('iti='+i+'='+ti);
					i = ti;
				}

				continue;
			}

			if(ch == " " || ch == "\r" || ch == "\n" || ch == "\t")
				continue;
		}

		//if(bInString && ch == "\\" && !bSlashed)
		if(bInString && ch == "\\")
		{
			bSlashed = true;
			new_str += ch;
			continue;
		}

		if(ch == "\"" || ch == "'")
		{
			if(bInString)
			{
				if(!bSlashed && quote_ch == ch)
				{
					bInString = false;
					//new_str += ch;
					//continue;
				}
			}
			else
			{
				bInString = true;
				quote_ch = ch;
				//new_str += ch;
				//continue;
			}
		}
		bSlashed = false;
		new_str += ch;
	}
	return new_str;
};


__PHPParser.prototype.parseFunction = function(str)
{
	var pos = str.indexOf("(");
	var lastPos = str.lastIndexOf(")");
	if(pos>=0 && lastPos>=0 && pos<lastPos)
		return {name:str.substr(0, pos),params:str.substring(pos+1,lastPos)};
	else
		return false;
};


__PHPParser.prototype.parseParameters = function(str)
{
	str = this.cleanCode(str);
	var prevAr = this.getParams(str);

	for (var j=0; j<prevAr.length; j++)
	{
		if (prevAr[j].substr(0, 6).toLowerCase()=='array(')
			prevAr[j] = this.getArray(prevAr[j]);
		else if(prevAr[j] != this.trimQuotes(prevAr[j]))
			prevAr[j] = this.trimQuotes(prevAr[j]);
		else
			prevAr[j] = this.wrapPHPBrackets(prevAr[j]);
	}
	return prevAr;
};


__PHPParser.prototype.getArray = function(_str)
{
	var resAr = [];
	if (_str.substr(0, 6).toLowerCase()!='array(')
		return _str;

	_str = _str.substring(6, _str.length-1);


	var tempAr = this.getParams(_str);
	var f_ch, l_ch, prop_name, prop_val;

	var len = tempAr.length;

	for (var y=0; y<len; y++)
	{
		if (tempAr[y].substr(0, 6).toLowerCase()=='array(')
		{
			resAr[y] = this.getArray(tempAr[y]);
			continue;
		}

		var p = tempAr[y].indexOf("=>");

		if (p==-1)
		{
			if (tempAr[y] == this.trimQuotes(tempAr[y]))
				resAr[y] = this.wrapPHPBrackets(tempAr[y]);
			else
				resAr[y] = this.trimQuotes(tempAr[y]);
		}
		else
		{
			prop_name = this.trimQuotes(tempAr[y].substr(0,p));
			prop_val = tempAr[y].substr(p+2);
			if (prop_val == this.trimQuotes(prop_val))
				prop_val = this.wrapPHPBrackets(prop_val);
			else
				prop_val = this.trimQuotes(prop_val);

			if (prop_val.substr(0, 6).toLowerCase()=='array(')
				prop_val = this.getArray(prop_val);

			resAr[prop_name] = prop_val;
		}
	}
	return resAr;
};


__PHPParser.prototype.wrapPHPBrackets = function(str)
{
	f_ch = str.substr(0,1);
	l_ch = str.substr(0,1);
	if ((f_ch=='"' && l_ch=='"') || (f_ch=='\'' && l_ch=='\''))
		return str;

	return "={"+str+"}";
};

__PHPParser.prototype.getParams = function(params)
{
	var arParams = [];
	var sk = 0, ch, sl, q1=1,q2=1;
	var param_tmp = "";
	for(var i=0; i<params.length; i++)
	{
		ch = params.substr(i, 1);
		if (ch=="\"" && q2==1 && !sl)
			q1 *=-1;
		else if (ch=="'" && q1==1  && !sl)
			q2 *=-1;
		else if(ch=="\\"  && !sl)
		{
			sl = true;
			param_tmp += ch;
			continue;
		}


		if (sl)
			sl = false;

		if (q2==-1 || q1==-1)
		{
			param_tmp += ch;
			continue;
		}

		if(ch=="(")
			sk++;
		else if(ch==")")
			sk--;
		else if(ch=="," && sk==0)
		{
			arParams.push(param_tmp);
			param_tmp = "";
			continue;
		}

		if(sk<0)
			break;

		param_tmp += ch;
	}
	if(param_tmp!="")
		arParams.push(param_tmp);

	return arParams;
};

// API for adding taskbars
BXEditorUtils.prototype.addTaskBar = function(taskbarClassName, iTaskbarSetPos, sTaskbarTitle, arParams, _sort)
{
	//try
	//{
		if (_sort == undefined)
			_sort = 100;

		if (!arTaskbarSettings_default[taskbarClassName])
		{
			arTaskbarSettings_default[taskbarClassName] = {
				show : true,
				docked : true,
				position : [2,0,0]
			};
		}

		window[taskbarClassName].prototype = new BXTaskbar(taskbarClassName);
		arBXTaskbars.push({name: taskbarClassName, pos:iTaskbarSetPos, title:sTaskbarTitle, arParams:arParams, sort:_sort});
	//}catch(e){}
}


BXEditorUtils.prototype.createToolbar = function(name,title,arButtons,defaultPosition)
{
	if (!name)
		name = 'untitled_'+Math.random();

	if (!title)
		title = name;

	if (!arButtons)
		arButtons = [];

	if (!defaultPosition)
		defaultPosition = {
			show 		: true,
			docked 		: true,
			position	: [0,2,0]
		};

	var res = [name,title,arButtons,defaultPosition];
	res.appendButton = function(buttonName,oButton){oBXEditorUtils.appendButton(buttonName,oButton,name);};
	return res;
}


BXEditorUtils.prototype.createButton = function(){};


BXEditorUtils.prototype.addToolbar = function(arToolbar)
{
	arToolbars = window.arToolbars || [];

	arToolbars[arToolbar[0]] = [
		arToolbar[1],
		arToolbar[2]
	];

	arToolbarSettings_default[arToolbar[0]] = arToolbar[3];
}

BXEditorUtils.prototype.appendButton = function(name,arButton,toolbarName)
{
	if (!arToolbars[toolbarName])
		return false;

	arToolbars[toolbarName][1].push(arButton);
}


BXEditorUtils.prototype.addPropertyBarHandler = function(tagname, handler)
{
	pPropertybarHandlers[tagname] = handler;
}


BXEditorUtils.prototype.BXRemoveAllChild = function(pNode)
{
	try
	{
		while(pNode.childNodes.length>0)
			pNode.removeChild(pNode.childNodes[0]);
	}
	catch(e)
	{}
}


BXEditorUtils.prototype.ConvertArray2Post = function(arr,arName)
{
	var s = '';
	for (var i in arr)
	{
		if (typeof arr[i] == 'object')
			for (var j in arr[i])
			{
				if (typeof arr[i][j] == 'object')
					for (var k in arr[i][j])
						s += '&'+arName+'['+i+']['+j+']['+k+']='+arr[i][j][k];
				else
					s += '&'+arName+'['+i+']['+j+']='+arr[i][j];
			}
		else
			s += '&'+arName+'['+i+']='+arr[i];

	}
	return s.substr(1);
}


function BXReplaceSpaceByNbsp(str)
{
	if(typeof(str)!='string')
		return str;
	str = str.replace(/\s/g, '&nbsp;');
	return str;
}


BXEditorUtils.prototype.getCustomNodeParams = function(pNode)
{
	try
	{
		var _arParams = BXUnSerialize(pNode.getAttribute("__bxcontainer"));
	}
	catch(e)
	{
		try{
			var _arParams = BXUnSerialize(pNode.arAttributes["__bxcontainer"]);
		}catch(e){_alert('getCustomNodeParams: '+rp(pNode.arAttributes));}
	}
	return _arParams;
}


BXEditorUtils.prototype.setCustomNodeParams = function(pNode,_arParams)
{
	try
	{
		pNode.setAttribute("__bxcontainer", BXSerialize(_arParams));
	}
	catch(e)
	{
		pNode.arAttributes["__bxcontainer"] = BXSerialize(_arParams);
	}
}

BXEditorUtils.prototype.setCheckbox = function(oCheckbox, mode, YNmode)
{
	mode = (mode === true);
	if (YNmode === false)
		oCheckbox.value = mode ? "True" : "False";
	else
		oCheckbox.value = mode ? "Y" : "N";
	oCheckbox.checked = oCheckbox.defaultChecked = mode;
}

function BXWaitWindow(pObjName)
{
	this.pObjName = pObjName;
	this.oDiv = false;
}

BXWaitWindow.prototype.Show = function()
{
	if (!this.oDiv)
	{
		this.oDiv = document.getElementById("editor_wait_window");
		if (!this.oDiv)
		{
			var oSize = jsUtils.GetWindowInnerSize();
			var left = (oSize.innerWidth / 2 - 150) + 'px';
			var top = (oSize.innerHeight / 2 - 50) + 'px';
			this.oDiv = BXCreateElement('DIV', {'id' : 'editor_wait_window', 'className' : 'waitwindow', 'innerHTML' : BX_MESS.Loading}, {'position' : 'absolute', 'left': left, 'top': top, 'zIndex': '3000'}, document);

			document.body.appendChild(this.oDiv);
		}
	}

	this.oDiv.style.display = 'block';
}

BXWaitWindow.prototype.Hide = function()
{
	if (!this.oDiv)
		this.oDiv = document.getElementById("editor_wait_window");
		//this.oDiv = document.getElementById("editor_wait_window_"+this.pObjName);

	if (this.oDiv)
		this.oDiv.style.display = 'none';
}


function BXSetConfiguration(pMainObj, sTarget, method, data)
{
	var setconfCHttpRequest = new JCHttpRequest();
	var urladd;
	switch(sTarget)
	{
		case "toolbars":
			urladd = "&rs_tlbrs="+(pMainObj.RS_toolbars ? "Y" : "N");
			break;
		case "taskbars":
		case "taskbarsets":
			urladd = "&rs_tskbrs="+(pMainObj.RS_taskbars ? "Y" : "N");
			break;
		case "tooltips":
			urladd = "&tooltips="+(pMainObj.showTooltips4Components ? "Y" : "N");
			break;
		case "visual_effects":
			urladd = "&visual_effects="+(pMainObj.visualEffects ? "Y" : "N");
			break;
		default:
		urladd = "";
		break;
	}
	if (method == 'POST' && data)
		setconfCHttpRequest.Post(settings_page_path + '?target=' + sTarget + urladd + "&edname="+pMainObj.name, data);
	else if(method == 'GET')
		setconfCHttpRequest.Send(settings_page_path + '?target=' + sTarget + urladd + "&edname=" + pMainObj.name);
}

function BXGetConfiguration(oCallBack, arParams)
{
	var pMainObj = arParams[1];
	//Toolbars
	pMainObj.RS_toolbars = window.RS_toolbars;
	SETTINGS[pMainObj.name].arToolbarSettings = window.arToolbarSettings;

	//Taskbars
	pMainObj.RS_taskbars = window.RS_taskbars;
	SETTINGS[pMainObj.name].arTaskbarSettings = window.arTaskbarSettings;

	//Taskbarsets
	SETTINGS[pMainObj.name].arTBSetsSettings = window.arTBSetsSettings;

	//Tooltips
	pMainObj.showTooltips4Components = window.__show_tooltips;

	// Visual effects
	pMainObj.visualEffects = window.__visual_effects;

	oCallBack.func.apply(oCallBack.obj);
}


function BXUnsetConfiguration(pMainObj, arParams)
{
	var r = new JCHttpRequest();
	r.Action = function(result) {alert(BX_MESS.RestoreSettingsMess);};
	r.Send(settings_page_path + '?target=unset&edname='+pMainObj.name);
}


oBXEditorUtils = new BXEditorUtils();

arContentParsers = [];
arPHPParsers = [];
arDOMHandlers = [];
arBXTaskbars = [];

function copyObj(obj)
{
	var res={};
	for (var i in obj)
	{
		if (typeof obj[i] == 'object')
			res[i] = copyObj(obj[i])
		else
			res[i] = obj[i];
	}
	return res;
}

function compareObj(obj1, obj2)
{
	try
	{
		for (var i in obj1)
		{
			if (typeof(obj1[i]) == 'object')
			{
				if (typeof(obj2[i]) != 'object' || !compareObj(obj1[i], obj2[i]))
					return false;
			}
			else
			{
				if (obj1[i] !== obj2[i])
					return false;
			}
		}
		return true;
	}
	catch(e)
	{
		_alert('ERROR: compareObj()');
		return false;
	}
}

function addslashes(str)
{
	return str;
	str=str.replace(/\\/g,'\\\\');
	str=str.replace(/\'/g,'\\\'');
	str=str.replace(/\"/g,'\\"');
	//str=str.replace(/\0/g,'\\0');
}

function JS_addslashes(str)
{
	str=str.replace(/\\/g,'\\\\');
	str=str.replace(/\'/g,'\\\'');
	str=str.replace(/\"/g,'\\"');
	//str=str.replace(/\0/g,'\\0');
	return str;
}

function JS_stripslashes(str)
{
	str=str.replace(/\\'/g,'\'');
	str=str.replace(/\\"/g,'"');
	str=str.replace(/\\\\/g,'\\');
	return str;
}

function rp(obj,level)
{
	try
	{
		if (level==undefined)
			level = 0;
		space = '';
		for (j=0; j<=level; j++)
			space += '  ';

		var result = "";
		for (i in obj)
		{
			if (typeof obj[i] == 'object')
			{
				if (obj[i].nodeName)
					result += space+i + " = {\n DOM Element" + obj[i].nodeName + ", \n}\n";
				else
					result += space+i + " = {\n" + rp(obj[i],level+1) + ", \n}\n";
			}
			else
				result += space+i + " = " + obj[i] + "; \n";
		}
		return result;
	}
	catch(e)
	{
		return 'returnProperties error...';
	}
}


function showProperties(obj)
{
	var result = "";
	for (i in obj)
		result += i + " = " + obj[i] + "<br>";
	document.write(result);
}

function str_pad_left (input, pad_length, pad_string)
{
	input = String (input);
	if (pad_string.length > 0)
	{
		var buffer = "";
		var padi = 0;
		pad_length = parseInt(pad_length);
		for (var i = 0, z = pad_length - input.length; i < z; i++)
			buffer += pad_string;
		input = buffer + input;
	}
	return input;
}