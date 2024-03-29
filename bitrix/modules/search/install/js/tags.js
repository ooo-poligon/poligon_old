window.oObject = {};
function JsTc(oHandler, arSites)
{
	var
		t = this,
		tmp = 0;
	t.oObj = typeof oHandler == 'object' ? oHandler : document.getElementById("TAGS");
	t.arSites = arSites;
	t.sExp = new RegExp("[\040]*[^" + STEMMING_TOK + "]+?[\040]*", "i");
	t.oLast = {"str":false, "arr":false};
	t.oThis = {"str":false, "arr":false};
	t.oEl = {"start":false, "end":false};
	t.oUnfinedWords = {};

	t.bReady = true, t.eFocus = true;
	t.aDiv = null, t.oDiv = null;
	t.oActive = null, t.oPointer = Array(), t.oPointer_default = Array(), t.oPointer_this = 'input_field';

	t.oObj.onblur = function(){t.eFocus = false;}
	t.oObj.onfocus = function(){if (!t.eFocus){t.eFocus = true; setTimeout(function(){t.CheckModif('focus')}, 500);}}

	t.CHttpRequest = new JCHttpRequest();

	t.oLast["arr"] = t.oObj.value.split(t.sExp);
	t.oLast["str"] = t.oLast["arr"].join(":");

	setTimeout(function(){t.CheckModif('this')}, 500);

	this.CheckModif = function(__data)
	{
		var
			sThis = false, tmp = 0,
			bUnfined = false, word = "",
			cursor = {};

		if (!t.eFocus)
			return;

		if (t.bReady && t.oObj.value.length > 0)
		{
			// Preparing input data
			t.oThis["arr"] = t.oObj.value.split(t.sExp);
			t.oThis["str"] = t.oThis["arr"].join(":");

			// Getting modificated element
			if (t.oThis["str"] && (t.oThis["str"] != t.oLast["str"]))
			{
				cursor['position'] = TCJsUtils.getCursorPosition(t.oObj);
				if (cursor['position']['end'] > 0 && !t.sExp.test(t.oObj.value.substr(cursor['position']['end']-1, 1)))
				{
					cursor['arr'] = t.oObj.value.substr(0, cursor['position']['end']).split(t.sExp);
					sThis = t.oThis["arr"][cursor['arr'].length - 1];

					t.oEl['start'] = cursor['position']['end'] - cursor['arr'][cursor['arr'].length - 1].length;
					t.oEl['end'] = t.oEl['start'] + sThis.length;
					t.oEl['content'] = sThis;

					t.oLast["arr"] = t.oThis["arr"];
					t.oLast["str"] = t.oThis["str"];
				}
			}
			if (sThis)
			{
				// Checking for UnfinedWords
				for (tmp = 2; tmp <= sThis.length; tmp++)
				{
					word = sThis.substr(0, tmp);
					if (t.oUnfinedWords[word] == '!fined')
					{
						bUnfined = true;
						break;
					}
				}
				if (!bUnfined)
					t.Send(sThis);
			}
		}
		setTimeout(function(){t.CheckModif('this')}, 500);
	},

	t.Send = function(sSearch)
	{
		if (!sSearch)
			return false;
		var oError = {};

		t.CHttpRequest.Action
			= function(data)
			{
				var result = {};
				t.bReady = true;
				try
				{
					eval("result = " + data + ";");
				}
				catch(e)
				{
					oError['result_unval'] = e;
				}

				if (TCJsUtils.empty(result))
					oError['result_empty'] = Error['result_empty'];

				try
				{
					if (TCJsUtils.empty(oError) && (typeof result == 'object'))
					{
						if (!(result.length == 1 && result[0]['NAME'] == t.oEl['content']))
						{
							t.Show(result);
							return;
						}
					}
					else
					{
						t.oUnfinedWords[t.oEl['content']] = '!fined';
					}
				}
				catch(e)
				{
					oError['unknown_error'] = e;
				}
				return;
			};
		//alert(t.arSites);
		var queryString = '/bitrix/admin/search_tags.php?search='+encodeURIComponent(sSearch);
		try
		{
			if (t.arSites && t.arSites.constructor.toString().indexOf("Array") != -1)
			{
				for (var i = 0, length = t.arSites.length; i < length; i++)
					queryString += '&site_id[]='+encodeURIComponent(t.arSites[i]);
			}
			var ck_box = document.getElementById('ck_'+oHandler.id);
			if (ck_box)
			{
				if(ck_box.checked)
					queryString += '&order_by=NAME';
			}
		} catch (e) {}
		t.CHttpRequest.Send(queryString);
	},

	t.Show = function(result)
	{
		t.Destroy();

		var pos = jsUtils.GetRealPos(t.oObj);

		t.oDiv = document.body.appendChild(document.createElement("DIV"));
		t.oDiv.id = t.oObj.id+'_div';
		t.oDiv.className = "bx-popup-menu";
		t.oDiv.style.position = 'absolute';
		t.aDiv = t.Print(result, ['NAME', 'CNT']);
		if (t.oDiv.offsetWidth < 300)
			t.oDiv.style.width = t.oDiv.offsetWidth + "px";
		else
			t.oDiv.style.width = "300px";
		t.oDiv.style.zIndex = 5000;

		jsFloatDiv.Show(t.oDiv, pos["left"], pos["bottom"]);

		jsUtils.addEvent(document, "click", t.CheckMouse);
		jsUtils.addEvent(document, "keydown", t.CheckKeyword);
	},

	t.Print = function(aArr, aColumn)
	{
		var
			aEl = null, sPrefix = '', sColumn = '',
			aResult = Array(), aRes = Array(),
			iCnt = 0, tmp = 0,

		sPrefix = t.oDiv.id;
		str = '<table cellspacing="0" cellpadding="0" border="0"><tr><td class="popupmenu">'+
			'<table cellspacing="0" cellpadding="0" border="0" width="100%">';
		for (var i = 0, length = aArr.length; i < length; i++)
		{
			// Math
			aEl = aArr[i];
			aRes = Array();
			aRes['ID'] = (aEl['ID'] && aEl['ID'].length > 0) ? aEl['ID'] : iCnt++;
			aRes['GID'] = sPrefix + '_' + aRes['ID'];
			aRes['NAME'] = aEl['NAME'];
			aRes['CNT'] = aEl['CNT'];
			aResult[aRes['GID']] = aRes;
			t.oPointer.push(aRes['GID']);
			// Graph
			str += '<tr><td>'+
			'<table cellspacing="0" cellpadding="0" border="0" class="popupitem" '+
				'onmouseout="window.oObject.' + t.oObj.id + '.Init(); this.className=\'popupitem\';" '+
				'onmouseover="window.oObject.' + t.oObj.id + '.Init(); this.className=\'popupitem popupitemover\'" '+
				'onclick="window.oObject.' + t.oObj.id + '.oActive=this.id;" '+
				'id="' + aRes['GID'] + '" name="' + sPrefix + '_table">'+
				'<tr><td class="gutter"><div></div></td>'+
				'<td class="item" id="' + aRes['GID'] + '_NAME" width="90%">' + aRes['NAME'] + '</td>'+
				'<td class="item" id="' + aRes['GID'] + '_CNT" width="10%" align="right">' + aRes['CNT'] + '</td>'+
			'</tr></table></td></tr>';
		}
		str += '</table></td></tr></table>';
		t.oPointer.push('input_field');
		t.oPointer_default = t.oPointer;
		t.oDiv.innerHTML = str;
		return aResult;
	},

	t.Init = function()
	{
		t.oActive = false;
		t.oPointer = t.oPointer_default;
		t.oPointer_this	= 'input_pointer';
		t.Clear();
	},

	t.Clear = function()
	{
		var oEl = {}, ii = '', oE = null;
		oEl = t.oDiv.getElementsByTagName("table");
		if (oEl.length > 0 && typeof oEl == 'object')
		{
			for (ii in oEl)
			{
				oE = oEl[ii];
				if (oE.name == (t.oDiv.id + '_table') || (t.aDiv[oE.id]))
				{
					oE.className = "popupitem";
				}
			}
		}
		return;
	},

	t.Destroy = function()
	{
		try
		{
			jsFloatDiv.Close(t.oDiv);
			t.oDiv.parentNode.removeChild(t.oDiv);
		}
		catch(e)
		{}

		jsUtils.removeEvent(document, "click", t.CheckMouse);
		jsUtils.removeEvent(document, "keydown", t.CheckKeyword);


		t.oDiv = null, t.aDiv = null, t.oActive = null;
		t.oPointer = Array(), t.oPointer_default = Array(), t.oPointer_this = 'input_field';
	},

	t.Replace = function()
	{
		if (typeof t.oActive == 'string')
		{
			var tmp = t.aDiv[t.oActive];
			var tmp1 = '';
			if (typeof tmp == 'object')
			{
				tmp1 = tmp['NAME'];
			}
			tmp = t.oObj.value.substring(0, t.oEl['start']) + tmp1;
			t.oObj.value = t.oObj.value.substring(0, t.oEl['start']) + tmp1 + t.oObj.value.substr(t.oEl['end']);
			TCJsUtils.setCursorPosition(t.oObj, tmp.length);
		}
		return;
	},

	t.CheckMouse = function()
	{
		t.Replace();
		t.Destroy();
	},

	t.CheckKeyword = function(e)
	{
		if (!e)
		{
			e = window.event;
		}
		var
			oP = null,
			oEl = null,
			ii = null;
		if ((37 < e.keyCode && e.keyCode <41) || (e.keyCode == 13))
		 {
			t.Clear();

			switch (e.keyCode)
			{
				case 38:
					oP = t.oPointer.pop();
					if (t.oPointer_this == oP)
					{
						t.oPointer.unshift(oP);
						oP = t.oPointer.pop();
					}

					if (oP != 'input_field')
					{
						t.oActive = oP;
						oEl = document.getElementById(oP);
						if (typeof oEl == 'object')
						{
							oEl.className = "popupitem popupitemover";
						}
					}
					t.oPointer.unshift(oP);
					break;
				case 40:
					oP = t.oPointer.shift();
					if (t.oPointer_this == oP)
					{
						t.oPointer.push(oP);
						oP = t.oPointer.shift();
					}
					if (oP != 'input_field')
					{
						t.oActive = oP;
						oEl = document.getElementById(oP);
						if (typeof oEl == 'object')
						{
							oEl.className = "popupitem popupitemover";
						}
					}
					t.oPointer.push(oP);
					break;
				case 39:
					t.Replace();
					t.Destroy();
					break;
				case 13:
					t.Replace();
					t.Destroy();
					if (jsUtils.IsIE())
					{
						e.returnValue = false;
						e.cancelBubble = true;
					}
					else
					{
						e.preventDefault();
						e.stopPropagation();
					}
					break;
				}
			t.oPointer_this	= oP;
		}
		else
		{
			t.Destroy();
		}
		return true;
	}
}
var TCJsUtils =
{
	getCursorPosition: function(oObj)
	{
		var result = {'start': 0, 'end': 0};
		if (!oObj || (typeof oObj != 'object'))
			return result;
		try
		{
			if (document.selection != null && oObj.selectionStart == null)
			{
				oObj.focus();
				var
					oRange = document.selection.createRange(),
					oParent = oRange.parentElement(),
					sBookmark = oRange.getBookmark(),
					sContents = sContents_ = oObj.value,
					sMarker = '__' + Math.random() + '__';

				while(sContents.indexOf(sMarker) != -1)
				{
					sMarker = '__' + Math.random() + '__';
				}

				if (!oParent || oParent == null || (oParent.type != "textarea" && oParent.type != "text"))
				{
					return result;
				}

				oRange.text = sMarker + oRange.text + sMarker;
				sContents = oObj.value;
				result['start'] = sContents.indexOf(sMarker);
				sContents = sContents.replace(sMarker, "");
				result['end'] = sContents.indexOf(sMarker);
				oObj.value = sContents_;
				oRange.moveToBookmark(sBookmark);
				oRange.select();
				return result;
			}
			else
			{
				return {
				 	'start': oObj.selectionStart,
					'end': oObj.selectionEnd
				};
			}
		}
		catch(e){}
		return result;
	},

	setCursorPosition: function(oObj, iPosition)
	{
		var result = false;
		if (typeof oObj != 'object')
			return false;

		oObj.focus();

		try
		{
			if (document.selection != null && oObj.selectionStart == null)
			{
				var oRange = document.selection.createRange();
				oRange.select();
			}
			else
			{
				oObj.selectionStart = iPosition;
				oObj.selectionEnd = iPosition;
			}
			return true;
		}
		catch(e)
		{
			return false;
		}

	},

	empty: function(oObj)
	{
		var result = true;
		if (oObj)
		{
		    for (i in oObj)
		    {
		    	 result = false;
		    	 break;
		    }
		}
		return result;
	}
}