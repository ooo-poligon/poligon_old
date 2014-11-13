///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// dhtml functions: require IE4 or later
//
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

var ie_version_start = navigator.appVersion.indexOf("MSIE");
var ie_version_end = navigator.appVersion.indexOf(".", ie_version_start);
ie_version_major = parseInt(navigator.appVersion.substring(ie_version_start+5, ie_version_end));

function toggleImageSize(imgID, labelID, urlSmall, urlLarge)
{
	var label, elem = document.getElementById(imgID);
	
	try
	{
		label = document.getElementById(labelID);
	}
	catch(e){}

	var sCurFileName = new String(elem.src);
	var iLast = sCurFileName.lastIndexOf("/");
	sCurFileName = sCurFileName.slice(iLast + 1);

	var sSmallFileName = new String(urlSmall);
	iLast = sSmallFileName.lastIndexOf("/");
	sSmallFileName = sSmallFileName.slice(iLast + 1);

	if (sCurFileName == sSmallFileName)
	{
		try
		{
			label.innerText = "Нажмите на рисунок, чтобы уменьшить";
			elem.alt = label.innerText;
		}
		catch(e){}
		elem.src = urlLarge;
	}
	else
	{
		try
		{
			label.innerText = "Нажмите на рисунок, чтобы увеличить";
			elem.alt = label.innerText;
		}
		catch(e){}
		elem.src = urlSmall;
	}
}

function copyExample(number)
{
	if (ie_version_major < 5)
	{
		alert("Example copy is not supported in this browser");
	}
	else
	{
		BodyRange = document.body.createTextRange();
		BodyRange.moveToElementText(document.all.item('xmp' + number));
		BodyRange.execCommand("Copy");
	}
}

function dxBeforePrint()
{
	var i;

	if (window.text)
		document.all.text.style.height = "auto";
			
	for (i=0; i < document.all.length; i++){
		if (document.all[i].tagName == "BODY") {
			document.all[i].scroll = "yes";
			}
		if (document.all[i].id == "pagetop") {
			document.all[i].style.margin = "0px 0px 0px 0px";
			document.all[i].style.width = "100%";
			}
		if (document.all[i].id == "pagebody") {
			document.all[i].style.overflow = "visible";
			document.all[i].style.top = "5px";
			document.all[i].style.width = "100%";
			document.all[i].style.padding = "0px 10px 0px 30px";
			}
		if (document.all[i].id == "seealsobutton" || document.all[i].id == "languagesbutton") {
			document.all[i].style.display = "none";
			}
		if (document.all[i].className == "LanguageSpecific") {
			document.all[i].style.display = "block";
			}
		}
}

function dxAfterPrint(){

	 document.location.reload();

}

function resizeFireFox()
{
	if (msieversion() > 4)
	{
		;
	}
	else
	{
		try
		{
			var oBanner= document.getElementById("pagetop");
			if (document.body.clientHeight > oBanner.offsetHeight)
			{
				var oText= document.getElementById("pagebody");
				oText.style.height= document.body.clientHeight - oBanner.offsetHeight - 20;
				
				var aPres = document.getElementsByTagName("pre");
				for (i=0; i < aPres.length; i++)
				{
					aPres[i].style.width = aPres[i].offsetWidth - 60;
				}
			}
		}
		catch(e){}
	}
}

function bodyLoad()
{
	resizeBan();
	document.body.onclick = bodyClick;
	document.body.onresize = bodyResize;
	window.onbeforeprint = dxBeforePrint;
	window.onafterprint = dxAfterPrint;	
/* 	try
	{
		bodyOnLoad();
	}
	catch(e){} */
	
	resizeFireFox();
}

function bodyResize()
{
	resizeBan();
	resizeFireFox();
}

function bodyClick()
{
	resizeBan();
}

function resizeBan()
{
	if (msieversion() > 4)
	{
		try
		{
			if (document.body.clientWidth==0)
				return;

			var oBanner= document.all.item("pagetop");
			var oText= document.all.item("pagebody");

			if (oText == null) 
				return;

			var oBannerrow1 = document.all.item("projectnamebanner");
			var oTitleRow = document.getElementById("pagetitlebanner");

			if (oBannerrow1 != null)
			{
				var iScrollWidth = dxBody.scrollWidth;
				oBannerrow1.style.marginRight = 0 - iScrollWidth;
			}

			if (oTitleRow != null)
			{
				oTitleRow.style.padding = "0px 10px 1px 22px;";
			}

			if (oBanner != null)
			{
				document.body.scroll = "no"
				oText.style.overflow= "auto";
				oBanner.style.width= document.body.offsetWidth - 1;
				oText.style.paddingRight = "40px"; // Width issue code
				oText.style.width= document.body.offsetWidth - 0;
				oText.style.top=0;  
				if (document.body.offsetHeight > oBanner.offsetHeight)
					oText.style.height= document.body.offsetHeight - (oBanner.offsetHeight) 
				else oText.style.height=0
			}

			try
			{
				nstext.setActive();
			} //allows scrolling from keyboard as soon as page is loaded. Only works in IE 5.5 and above.
			catch(e){}

		}
		catch(e){}
	}
	else
	{
		/* try
		{
			var oBanner= document.getElementById("pagetop");
			if (document.body.clientHeight > oBanner.offsetHeight)
			{
				var oText= document.getElementById("pagebody");
				oText.style.height= document.body.clientHeight - oBanner.offsetHeight - 20;
				
				var aPres = document.getElementsByTagName("pre");
				for (i=0; i < aPres.length; i++)
				{
					aPres[i].style.width = aPres[i].offsetWidth - 60;
				}
			}
		}
		catch(e){} */
	}
} 

function msieversion()
// Return Microsoft Internet Explorer (major) version number, or 0 for others.
// This function works by finding the "MSIE " string and extracting the version number
// following the space, up to the decimal point for the minor version, which is ignored.
{
	var ua = window.navigator.userAgent;
	var msie = ua.indexOf("MSIE ");
	var moz = ua.indexOf("Mozilla/5.0");
	var fox = ua.indexOf("Firefox");

	if (msie > 0)		// is Microsoft Internet Explorer; return version number
		return parseInt(ua.substring(msie+5, ua.indexOf(".", msie)));
	else
	if (moz != -1 || fox != -1)
		return 0;	// is mozilla or firefox
	else
		return 0;	// is other browser
}


var POPUP_COLOR = 0xffffe0;

function dhtml_popup(url)
{
	var pop, main, body, x, y;

	// no url? then hide the popup
	if (url == null || url.length == 0)
	{
		pop = document.all["popupFrame"];
		if (pop != null)
			pop.style.display = "none";
		return;
	}

	// if the popup frame is already open, close it first
	if (dhtml_popup_is_open())
	{
		// the main window is the parent of the popup frame
		main = window.parent;
		body = main.document.body;
		pop = main.document.all["popupFrame"];

		// add the popup origin to the event coordinates
		x = pop.offsetLeft + window.event.offsetX;
		y = pop.offsetTop + window.event.offsetY;

		// hide the popup frame
		pop.style.display = "none";
	}
	else
	{
		// the main window is the current window
		main = window;
		body = document.body;
		pop = document.all["popupFrame"];

		// use the event coordinates for positioning the popup
		x = window.event.x;
		y = window.event.y;

		// account for the scrolling text region, if present
		var nstx = document.all["nstext"];
		if (nstx != null)
			y += nstx.scrollTop - nstx.offsetTop;

		// get the popup frame, creating it if needed
		if (pop == null)
		{
			var div = document.all["popupDiv"];
			if (div == null)
				return;

			div.innerHTML = "<iframe id=\"popupFrame\" frameborder=\"none\" scrolling=\"none\" style=\"display:none\"></iframe>";
			pop = document.all["popupFrame"];
		}
	}

	// get frame style
	var sty = pop.style;

	// load url into frame
	pop.src = url;

	// initialize frame size/position
	sty.position  = "absolute";
	sty.border    = "1px solid #cccccc";
	sty.posLeft   = x + body.scrollLeft     - 30000;
	sty.posTop    = y + body.scrollTop + 15 - 30000;
	var wid       = body.clientWidth;
	sty.posWidth  = (wid > 500)? wid * 0.6: wid - 20;
	sty.posHeight = 0;

	// wait until the document is loaded to finish positioning
	main.setTimeout("dhtml_popup_position()", 100);
}
	
function dhtml_popup_is_open()
{
	return window.location.href != window.parent.location.href;
}

function dhtml_popup_position()
{
	// get frame
	var pop = document.all["popupFrame"];
	var frm = document.frames["popupFrame"];
	var sty = pop.style;

	// get containing element (scrolling text region or document body)
	var body = document.all["nstext"];
	if (body == null)
		body = document.body;

	// hide navigation/nonscrolling elements, if present
	dhtml_popup_elements(frm.self.document);

	// get content size
	sty.display = "block";
	frm.scrollTo(0,1000);
	sty.posHeight = frm.self.document.body.scrollHeight + 20;

	// make content visible
	sty.posLeft  += 30000;
	sty.posTop   += 30000;

	// adjust x position
	if (sty.posLeft + sty.posWidth + 10 - body.scrollLeft > body.clientWidth)
		sty.posLeft = body.clientWidth  - sty.posWidth - 10 + body.scrollLeft;

	// if the frame fits below the link, we're done
	if (sty.posTop + sty.posHeight - body.scrollTop < body.clientHeight)
		return;

	// calculate how much room we have above and below the link
	var space_above = sty.posTop - body.scrollTop;
	var space_below = body.clientHeight - space_above;
	space_above -= 35;
	space_below -= 20;
	if (space_above < 50) space_above = 50;
	if (space_below < 50) space_below = 50;

	// if the frame fits above or we have a lot more room there, move it up and be done
	if (sty.posHeight < space_above || space_above > 2 * space_below)
	{
		if (sty.posHeight > space_above)
			sty.posHeight = space_above;
		sty.posTop = sty.posTop - sty.posHeight - 30;
		return;
	}

	// adjust frame height to fit below the link
	sty.posHeight = space_below;
}

function dhtml_popup_elements(doc)
{
	// hide navigation bar, if present
	var nav = doc.all["ienav"];
	if (nav != null)
		nav.style.display = "none";

	// set popup color and remove background image
//	doc.body.style.backgroundColor = POPUP_COLOR;
//	doc.body.style.backgroundImage = "none";

	// reset popup color of title row, if present
	var trow = doc.all["TitleRow"];
	if (trow != null)
		trow.style.backgroundColor = POPUP_COLOR;

	// reset border/color of nonscrolling banner, if present
	var nsb = doc.all["nsbanner"];
	if (nsb != null)
	{
		nsb.style.borderBottom = "0px";
		nsb.style.backgroundColor = POPUP_COLOR;
	}

	// reset background image/color of scrolling text region, if present
	var nstx = doc.all["nstext"];
	if (nstx != null)
	{
		nstx.style.backgroundColor = POPUP_COLOR;
		nstx.style.backgroundImage = "none";
	}
}

