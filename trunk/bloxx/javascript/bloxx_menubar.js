function Browser( browsers ) 
{
	this.browsers = browsers;	// browser detection array
	this.createBooleans();
}

Browser.prototype.createBooleans = function() 
{
	var name = navigator.appName;
	var cname = navigator.appCodeName;
	var usragt = navigator.userAgent;
	var ver = navigator.appVersion;
	for ( i = 0; i < this.browsers.length; i++ ) 
	{
		var browserArray = this.browsers[ i ]; // browsers-array

		var sCheck = browserArray[ 1 ]; // 'logical expr' that detects the browser
		var sCurrentVersion = browserArray[ 2 ]; // 'regexp' that gets current version
		var sBrand = browserArray[ 0 ]; // browser-obj 'property' (is.xx)
		var availableVersions = browserArray[ 3 ]; // 'versions' to check for

		if ( eval( sCheck ) )
		{ // browser recognized
			eval( "this." + sBrand + " = true" ); // browser-obj property (is.xx)
			var regexp, ver, sMinorVersion, sMajorVersion;
			regexp = new RegExp( sCurrentVersion );
			regexp.exec( usragt ); // parse navigator.userAgent
			var sMajorVersion = RegExp.$1;
			var sMinorVersion = RegExp.$2;

			for ( j = 0; j < availableVersions.length; j++ )
			{
				if ( parseFloat(availableVersions[ j ]) <= eval( sMajorVersion + "." + sMinorVersion ) )
				{ // upper versions
					eval( "this." + sBrand + availableVersions[ j ].substr( 0, 1 ) + availableVersions[ j ].substr( 2, 1 ) + "up = true" );
				}
				if ( parseFloat(availableVersions[ j ]) == eval( sMajorVersion + "." + sMinorVersion ) ) 
				{ /// current version
					eval( "this." + sBrand + availableVersions[ j ].substr( 0, 1 ) + availableVersions[ j ].substr( 2, 1 ) + "= true" );
				}
			}
		}
	}
}

is = new Browser ( 
[
	// Internet Explorer Windows ---
	[ "iewin",
		"cname.indexOf( 'Mozilla' ) >= 0 && name.indexOf( 'Microsoft Internet Explorer' ) >= 0 && usragt.indexOf( 'MSIE' ) >= 0 && usragt.indexOf( 'Opera' ) < 0 && usragt.indexOf( 'Windows' ) >= 0", // IE detection expression
		"MSIE.([0-9]).([0-9])",	// regexpr for version (in navigator.userAgent)
		[ "5", "5.5", "6" ] ],	// published versions
	// Internet Explorer Macintosh ---
	[ "iemac",
		"cname.indexOf( 'Mozilla' ) >= 0 && name.indexOf( 'Microsoft Internet Explorer' ) >= 0 && usragt.indexOf( 'MSIE' ) >= 0 && usragt.indexOf('Opera') < 0 && usragt.indexOf('Mac') >= 0",
		"MSIE.([0-9]).([0-9])",
		[ "5", "5.1", "5.2" ] ],
	// Gecko (Mozilla, Galeon, Firebird, Netscape >=6.x) ---
	[ "gk", 
		"cname.indexOf( 'Mozilla' ) >= 0 && name.indexOf( 'Netscape' ) >= 0 && usragt.indexOf( 'Gecko' ) >= 0 && usragt.indexOf( 'Safari' ) < 0",
		"[rv[:| ]*([0-9]).([0-9])|Galeon\/([0-9]).([0-9])]",
		[ "0.7", "0.8", "0.9", "1.0", "1.1", "1.2", "1.3", "1.4", "1.5", "1.6" ] ],
	// Netscape Navigator ---
	[ "nn",
		"cname.indexOf( 'Mozilla' ) >= 0 && name.indexOf( 'Netscape' ) >=0 && parseInt( ver ) <= 4",
		"([0-9]).([0-9])",
		[ "4", "4.5", "4.7", "4.8" ] ],
	// Opera ---
	[ "op",
		"cname.indexOf( 'Mozilla' ) >= 0 && ( name.indexOf( 'Microsoft Internet Explorer' ) >=0 || name.indexOf( 'Opera' ) >= 0 ) && usragt.indexOf( 'Opera' ) >= 0",
		"Opera.([0-9]).([0-9])",
		[ "5", "5.1", "6", "7", "7.1", "7.2" ] ],
	// Safari ---
	[ "sf",
		"cname.indexOf( 'Mozilla' ) >= 0 && name.indexOf( 'Netscape' ) >=0 && usragt.indexOf('AppleWebKit' ) >= 0 && usragt.indexOf('Safari') >= 0",
		"AppleWebKit\/([0-9])", 
		"Konqueror\/([0-9]\.[0-9])",
		[ "48", "85" ] ],
	// Konqueror ---
	[ "kq",
		"cname.indexOf( 'Mozilla' ) >= 0 && name.indexOf( 'Konqueror' ) >= 0 && usragt.indexOf( 'Konqueror' ) >= 0",
		"Konqueror\/([0-9]).([0-9]*)",
		[ "2.2", "3", "3.1" ] ]
] );

function Debug()
{	
	this.outputElementName = "debug";
	this.sText = "";
}

debug = new Debug();

Debug.prototype.writeHtml = function( iColumns, iRows )
{	
	sTextAreaHtml =
		"<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">" +
			"<tr>" +
				"<td colspan=\"3\">" +
					"<form>" +
						"<textarea type=\"text\" name=\"" + this.outputElementName + "\" id=\"debug\" cols=\"" + iColumns + "\" rows=\"" + iRows + "\">" +
						"</textarea><br>" +
						"<input type=\"button\" value=\"select all\" onClick=\"javascript:document.getElementById( 'debug' ).select()\">" +
						"<input type=\"reset\" value=\"clear\">" +
					"</form>" +
				"</td>" +
			"</tr>" +
		"</table>";	
		
	document.write( sTextAreaHtml );
}

Debug.prototype.flushBuffer = function()
{
	var outputElement = this.getOutput();
	if ( outputElement )
	{
		outputElement.value = this.sText + "\n" + outputElement.value ;
	}
}

Debug.prototype.bufferedWrite = function( sText )
{
	this.sText = sText + "\n" + this.sText;
}

Debug.prototype.write = function( sText )
{
	var outputElement = this.getOutput(); 
	if ( outputElement )
	{
		outputElement.value = sText + "\n" + outputElement.value;
	}
}

Debug.prototype.getOutput = function()
{
	var outputElement = null;
	if ( is.nn4up )
	{
		outputElement = document.forms[ "\"" + this.outputElementName + "\"" ];
	}
	else if ( is.gk || is.iewin5up || is.iemac5up || is.sf || is.op || is.kq )
	{
		outputElement = document.getElementById( this.outputElementName );
	}
	return outputElement;
}

function Xlayer( sParent, xlayerParent, x, y, offsetX, offsetY, w, h,  iClipTop, iClipRight, iClipBottom, iClipLeft, iZindex, bVisibility, sBgcolor, fading, events, sText, bBold, sAlign, iTopTextBorder, iRightTextBorder, iBottomTextBorder, iLeftTextBorder, sFgcolor, sHref, sIcon, iIconWidth, iIconHeight, iconBorder, sFontface, iFontsize, src , sSpacer )
{
	if ( !Xlayer.prototype.instances )
		Xlayer.prototype.instances = new Array();
	Xlayer.prototype.instances[ Xlayer.prototype.instances.length ] = this; // Store this Instance In static array
	this.index = Xlayer.prototype.instances.length - 1;

	this.sParent = sParent;
	this.parent = null;
	this.xlayerParent = xlayerParent;
	this.lyr = null;
	this.id = this.id || "Xlayer" + this.index;
	this.x = x || 0;
	this.y = y || 0;
	this.offsetX = offsetX ||	0;
	this.offsetY = offsetY ||	0;
	this.w = w ||	0;
	this.h = h || 0;
	this.iClipTop = iClipTop || 0;
	this.iClipRight = iClipRight || w;
	this.iClipBottom = iClipBottom || h;
	this.iClipLeft = iClipLeft || 0;
	this.iZindex = iZindex || 0;
	this.bVisibility = bVisibility;
	this.sBgcolor = sBgcolor || "black";
	this.iOpacity = 0;
	this.sSpacer = sSpacer;

	// caption ---
	this.sText = sText || null;
	this.bBold = bBold || false;
	this.sAlign = sAlign || "center";
	this.iTopTextBorder = iTopTextBorder;
	this.iRightTextBorder = iRightTextBorder;
	this.iBottomTextBorder = iBottomTextBorder;
	this.iLeftTextBorder = iLeftTextBorder;
	this.sFgcolor = sFgcolor || "white";
	this.sHref = ( is.nn4up && !sHref )? "#" : sHref; // nn4 always need a href to process clicks
	this.sFontface = sFontface || "Helvetica";
	this.iFontsize = iFontsize || 2;
	this.sIcon = sIcon ||	null;
	this.iIconWidth = iIconWidth || 0;
	this.iIconHeight = iIconHeight || 0;
	this.iconBorder = iconBorder || 0;

	// iframe ----
	this.iframe = null;
	this.scrollbars = null;
	this.src = src ||	null;
	this.events = events || null; // array: event, func, event, func, ...
	this.fading =	fading || null; // array: start, stop, steps, delay

	if ( is.op6up && !is.op7up ) // opera can't create dynamically
		this.writeDiv();
}

Xlayer.prototype.MOUSEOVER = "onmouseover";
Xlayer.prototype.MOUSEOUT = "onmouseout";
Xlayer.prototype.CLICK = "onclick";


Xlayer.prototype.create = function() 
{
	this.parent = XlayerParent.prototype.getLayer( this.sParent ); // parent = another layer or document.body
	this.parentCoordsOnly = XlayerParent.prototype.getLayer( this.xlayerParent.sId );
	if ( is.nn4up )
	{
		if ( this.w == "100%" )
			this.lyr = new Layer( this.parent.innerWidth, this.parent );
		else
			this.lyr = new Layer( this.w, this.parent );
	}
	else if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.kq3up || is.op7up )
	{
		this.lyr = document.createElement( "DIV" ); // create layer
		this.lyr.style.position = "absolute";
		this.lyr.style.overflow = "hidden";
		this.lyr.id = this.id;
		this.parent.appendChild( this.lyr ); // insert into DOM
	}
	else if ( is.op6up && !is.op7up )
	{ // already created on instanciation (no dynamic creation possible)
		this.lyr = document.getElementById( this.id );
	}
	
	this.setVisibility( this.bVisibility );
	this.setSize( this.w, this.h );
	this.setEvents( this.events );
	if ( !( is.op6up && !is.op7up ) ) 
		this.setBody( this.getCaption( this.sText, this.bBold, this.sIcon, this.iIconWidth, this.iIconHeight, this.iconBorder ) );
	this.setBgColor( this.sBgcolor );
	this.setFgColor( this.sFgcolor );
	this.setPos( this.x, this.y, this.offsetX, this.offsetY );
	this.setZindex( this.iZindex );
	this.fade( this.fading );
}

Xlayer.prototype.writeDiv = function()
{
	document.writeln( '<div id="' + this.id + '" style="position:absolute;">' + this.getCaption( this.sText, this.bBold, this.sIcon, this.iIconWidth, this.iIconHeight, this.iconBorder ) + '</div>' );
}

Xlayer.prototype.kill = function()
{
	if ( is.nn4up )
	{
		for ( i = 0; i < document.layers.length ; i++ ) // scan trough layers-array in NN-DOM
		{
			this.setVisibility( false );
			if ( document.layers[ i ].id == this.lyr.id )	
			{
				index = i;
				//document.layers.splice(i, 1)
				//delete document.layers[i]
				document.layers[ i ] = null;
				break;
			}
		}
	}
	else if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.kq3up || is.op7up )
	{
		var lyr;
		lyr = document.getElementById( this.lyr.id );
		document.body.removeChild( lyr );
	}
	this.iOpacity = 0;
}

Xlayer.prototype.setFgColor = function( color )
{
	if ( this.sText )
	{
		this.sFgcolor = color;

		if ( is.nn4up )
			this.setBody( this.getCaption( this.sText, this.bBold, this.sIcon, this.iIconWidth, this.iIconHeight, this.iconBorder ) );
		else if ( is.iewin5up || is.iemac5up || is.gk || is.sf  || is.kq3up || is.op6up )
		{
			if ( this.sText )
			{
				document.getElementById( this.id+"d" ).style.color = color;
				//this.setCaption( this.sText, this.bBold, this.sIcon, this.iIconWidth, this.iIconHeight, this.iconBorder );
			}
		}
	}
}

Xlayer.prototype.setBgColor = function( color )
{
	this.sBgcolor = color;

	if ( is.nn4up )
		this.lyr.document.bgColor = color;
	else if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.kq3up || is.op6up )
		this.lyr.style.backgroundColor = color;
}

Xlayer.prototype.setSize = function( w, h )
{
	var iOldWidth = this.w;
	var iOldHeight = this.h;

	this.w = w;
	this.h = h;

	if ( is.nn4up )
	{
		if ( w == "100%" )
			this.lyr.resizeTo( window.innerWidth, h );
		else 
			this.lyr.resizeTo( w, h );
	}
	else if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.kq3up || is.op7up )
	{
		if ( w == "100%" )
		{
			this.lyr.style.width = "100%";
			this.lyr.style.height = h + 'px';
		}
		else
		{
			this.lyr.style.width = w + 'px';
			this.lyr.style.height = h + 'px';
		}

		this.setClipping( this.iClipTop, ( this.iClipRight + w - iOldWidth ),  ( this.iClipBottom + h - iOldHeight ), this.iClipLeft );

		if ( is.iewin5up && this.iframe ) // recreate iframe on resize
			this.setIframe( this.src );
	}
	else if ( is.op6up && !is.op7up )
	{
		if ( w == "100%" )
		{
			this.lyr.style.pixelWidth = "100%";
			this.lyr.style.pixelHeight = h;
		}
		else
		{
			this.lyr.style.pixelWidth = w;
			this.lyr.style.pixelHeight = h;
		}
	}
}

Xlayer.prototype.setPos = function( x, y, offsetX, offsetY )
{
	if ( this.sParent )
	{ // parent is normal layer
		parentX = XlayerParent.prototype.getLayerX( this.sParent );
		parentY = XlayerParent.prototype.getLayerY( this.sParent );
		parentW = XlayerParent.prototype.getLayerW( this.sParent );
		parentH = XlayerParent.prototype.getLayerH( this.sParent );
	}
	else
	{ // parent is XlayerParent
		if ( is.iemac5 )
		{
			parentX = XlayerParent.prototype.getLayerX( this.parentCoordsOnly );
			parentY = XlayerParent.prototype.getLayerY( this.parentCoordsOnly );
			parentW = XlayerParent.prototype.getLayerW( this.parentCoordsOnly );
			parentH = XlayerParent.prototype.getLayerH( this.parentCoordsOnly );
		}
		else
		{
			parentX = this.xlayerParent.getX();
			parentY = this.xlayerParent.getY();
			parentW = this.xlayerParent.getW();
			parentH = this.xlayerParent.getH();
		}
	}
		
	if ( x == "centered" )
		x = parentX + ( parentW / 2 ) - this.w / 2;
	else if ( x == "left" )
		x = parentX;
	else if ( x == "right" )
		x = parentX + parentW - this.w;

	if ( y == "centered" )
		y = parentY + ( parentH / 2 ) - this.h / 2;
	else if ( y == "top" )
		y = parentY; 
	else if ( y == "bottom" )
		y = parentY + parentH - this.h;

	if ( offsetX )
		x += offsetX;
	if ( offsetY )
		y += offsetY;

	if ( is.nn4up )
	{
		this.lyr.moveTo( x, y );
	}
	else if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.kq3up || is.op6up )
	{
		this.lyr.style.top = y + "px";
		this.lyr.style.left = x + "px";
	}
	this.x = x;
	this.y = y;

}

Xlayer.prototype.setVisibility = function( bVisibility ) 
{
	this.bVisibility = bVisibility;
	if ( this.lyr ) 
	{
		if ( is.nn4up ) 
		{
			this.lyr.visibility = ( bVisibility )? "show" : "hide";
		}
		else if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.kq3up || is.op6up ) 
		{
			this.lyr.style.visibility = ( bVisibility )? "visible" : "hidden";
		}
	}
}

Xlayer.prototype.isVisible = function() 
{
	return this.bVisibility;
}

Xlayer.prototype.setFontsize = function( iFontsize )
{
	this.iFontsize = iFontsize;
}

Xlayer.prototype.setFontface = function( sFontface )
{
	this.sFontface = sFontface;
}

Xlayer.prototype.setClipping = function( top, right, bottom, left )
{
	if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.kq3up || is.op7up )
	{
		this.lyr.style.clip = "rect(" + top + "px " + right + "px " + bottom + "px " + left + "px)";
	}
	else if ( is.nn4up )
	{
		this.lyr.clip.top = top;
		this.lyr.clip.right = right;
		this.lyr.clip.bottom = bottom;
		this.lyr.clip.left = left;
	}
	this.iClipTop = top;
	this.iClipRight = right;
	this.iClipBottom = bottom;
	this.iClipLeft = left;
}

Xlayer.prototype.setZindex = function( iZindex )
{
	this.iZindex = iZindex;

	if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.kq3up || is.op6up )
	{
		this.lyr.style.zIndex = iZindex;
	}
	else if ( is.nn4up )
	{
		this.lyr.zIndex = iZindex;
	}
}

Xlayer.prototype.setEvents = function( events )
{
	if( events )
	{
		for ( i = 0; i < events.length; )
		{
			var e = events[ i++ ];
			var func = events[ i++ ];

			if ( is.gk || is.sf || is.kq3up || is.op7up ) this.lyr.addEventListener( e.substring( 2, e.length ), this.onEvent( e, func, this.lyr ), false );
			else if ( is.iewin5up || is.iemac5up || is.op6up ) this.lyr[ e.toLowerCase() ] = this.onEvent( e, func, this.lyr );//new Function( func );
			else if ( is.nn4up )
			{
				this.lyr.captureEvents( Event[ e.toUpperCase().substring( 2 ) ] );
				this.lyr[ e.toLowerCase() ] = new Function( func );
			}
		}
	}
}

Xlayer.prototype.onEvent = function( event, func, xlayer )
{
	return function( e )
	{
		var e = arguments[ 0 ];
		if ( event == Xlayer.prototype.MOUSEOVER || event == Xlayer.prototype.MOUSEOUT )
		{
			if ( !e ) var e = window.event; // iewin, iemac

			if ( e.target && e.relatedTarget ) // gk
			{
				var target = e.target;
				var relatedTarget = e.relatedTarget;
			}
			else if ( e.fromElement && e.toElement )
			{
				var target = e.toElement;
				var relatedTarget = e.fromElement;
			}
			if ( Xlayer.prototype.isChildNode( relatedTarget, xlayer ) && Xlayer.prototype.isChildNode( target, xlayer ) )
				return false; // ignore events of inner html-entities 
		}
		return eval( func );
	};
}

Xlayer.prototype.isChildNode = function( node, parentNode )
{
	if ( node == parentNode )
		return true;
	else if ( node && node.parentNode )
		return Xlayer.prototype.isChildNode( node.parentNode, parentNode );
	else
		return false;
}

Xlayer.prototype.setBody = function( sHtml )
{
	if ( is.iewin5up || is.iemac || is.op7up || is.kq3up )
		this.lyr.innerHTML = sHtml;
	else if ( is.gk || is.sf )
	{
		while ( this.lyr.hasChildNodes() )
				this.lyr.removeChild( this.lyr.firstChild );
		var range = this.lyr.ownerDocument.createRange();
		range.selectNodeContents( this.lyr );
		range.deleteContents();
		var contextualFrag = range.createContextualFragment( sHtml );
		this.lyr.appendChild( contextualFrag );
	}
	else if( is.nn4up )
	{
		this.lyr.document.open()
		this.lyr.document.write( sHtml );
		this.lyr.document.close();
	}
	else if ( is.op6up && !is.op7up )
		this.sBody = sHtml;
}

Xlayer.prototype.scroll = function( orientation, step )
{
	this.orientation = orientation;
	this.step = step;

	if ( ( this.iClipRight < this.w ) || ( this.iClipTop != 0 ) || ( this.iClipLeft > 0 ) || ( this.iClipBottom < this.h ) ) 
	{ // scrolling possible
		if ( orientation == "horiz" )
		{
			if ( this.iClipLeft + step > 0 && this.iClipRight  + step < this.w ) 
			{	// border reached?
				this.setPos(this.x - step, this.y);
				this.setClipping(this.iClipTop, this.iClipRight + step, this.iClipBottom, this.iClipLeft + step);
			}
		}
		else if ( orientation == "vert" )
		{
			if ( this.iClipTop + step > 0 && this.iClipBottom + step < this.h ) 
			{	// border reached?
				this.setPos( this.x, this.y - step );
				this.setClipping( this.iClipTop + step, this.iClipRight, this.iClipBottom + step, this.iClipLeft );
			}
		}
	}
}

Xlayer.prototype.setOpacity = function( iOpac )
{
	if ( is.iewin5up || is.iemac5up )
		this.lyr.style.filter = "alpha(opacity=" + iOpac + ")";

	else if ( is.gk )
	{
		this.lyr.style.MozOpacity = iOpac / 100;
	}
/*	not tested yet
	else if ( is.kq3up )
	{
		this.lyr.style.KhtmlOpacity = iOpac / 100;
	}
*/
}

Xlayer.prototype.fade = function( fading )
{
	if ( fading )
	{
		start =	fading[ 0 ]; // opacity start value
		stop =	fading[ 1 ]; // stop
		steps =	fading[ 2 ]; // number of steps
		delay =	fading[ 3 ]; // delay in ms

		this.iOpacity = this.iOpacity + parseInt( ( stop - start ) / steps );
		this.setOpacity( this.iOpacity );

		if ( this.iOpacity < stop )
			setTimeout( "Xlayer.prototype.instances[" + this.index + "].fade( Xlayer.prototype.instances[" + this.index + "].fading )", delay);

		this.fading = fading;
		return true;
	}
}

Xlayer.prototype.setIframe = function( src, scrollbars )
{
	this.src =	src;

	if ( scrollbars != null )
	{
		this.scrollbars = ( scrollbars )? "yes"	: "no";
	}
	else if ( this.scrollbars == null )
	{
		this.scrollbars = "yes";
	}

	if ( is.nn4up )
	{
		this.lyr.src = src;
	}
	else if ( is.iewin5 )
	{ // ie5 basically cannot create dynamically : frame, iframe

		this.lyr.innerHTML = "<iframe width='100%' height='100%' frameborder='0' scrolling='" + this.scrollbars + "' id='" + this.id + "_iframe" + "'></iframe>";
		this.lyr.contentWindow = new Object();
		this.lyr.contentWindow.location = new Object();
		this.iframe = document.getElementById(this.id + "_iframe"); // store iframe
		this.lyr.contentWindow.location.iframe = this.iframe;
		this.lyr.contentWindow.location.iframe.id = "";
		this.lyr.contentWindow.location.iframe.src = src
	}
	else if ( is.iewin55up || is.iemac5up || is.gk || is.sf || is.kq3up || is.op7up )
	{
		var iframe;
		iframe = document.createElement( "IFRAME" );
		iframe.src = src;
		iframe.name = this.id + "_iframe";
		iframe.scrolling = this.scrollbars;
		iframe.frameBorder = "0px";
		iframe.style.visibility = "inherit";

		if ( is.iewin55up )
		{
			iframe.style.width = this.w + "px";
			iframe.style.height = this.h + "px";
		}
		else if ( is.iemac5up || is.gk || is.sf || is.kq3up || is.op7up )
		{
			iframe.style.width = "inherit";
			iframe.style.height = "inherit";
		}

		while ( this.lyr.hasChildNodes() )
		{	
			this.lyr.removeChild( this.lyr.lastChild );
		}
		this.lyr.appendChild( iframe )

		this.iframe = iframe;
	}
}

Xlayer.prototype.getCaption = function( sText, bBold, sIcon, iIconWidth, iIconHeight, iIconBorder )
{
	this.sText = sText;
	this.sIcon = sIcon;
	this.iIconWidth = iIconWidth;
	this.iIconHeight = iIconHeight;

	var tab_head = '<table style="table-layout:fixed;' + ( ( is.iewin5up )? 'cursor: hand;' : 'cursor: pointer;" ' ) + 'width="' + this. w + '" height="' + this.h + '" border="0" cellpadding="0" cellspacing="0">';
	var tab_foot = '</table>';

	if ( sText || sIcon )
	{
		// content ---
		var img = "", desc = "", html ="", tab_body = "", sTextCell = "";
		if ( sIcon )
			img = '<img src="' + sIcon + '" width="' + iIconWidth + '" height="' + iIconHeight + '">';
		if ( sText )
		{				
			if ( is.nn4up )
				sTextCell = '<font id="' + this.id + 'd" color="' + this.sFgcolor + '" size="' + ( parseInt( "0" + ( this.iFontsize / 5 ), 10 ) ) + '" face="' + this.sFontface + '">' + ( ( bBold )? '<b>' : '' ) + sText + ( ( bBold )? '</b>' : '' ) + '</font>';
			else if ( is.iewin5up || is.gk || is.sf || is.kq3up || is.iemac5up || is.op6up )
				sTextCell = '<span id="' + this.id + 'd" style="' + 'color:' + this.sFgcolor + ';' + 'font-size:' + this.iFontsize + 'px;' + 'font-family:' + this.sFontface + ';' + ( ( bBold )? ' font-weight:bold;' : '' ) + 'height:' + this.iFontsize + 'px">' + sText + '</span>';
		}
		if ( this.sHref && is.nn4up ) // nn4 always needs a <a href...
			sTextCell = "<a href='" + this.sHref + "' style='text-decoration: none;'>" + sTextCell + "</a>";

		// text cell -----
		var iTextCellWidth = this.w - iIconWidth - iIconBorder;
		var iTextCellHeight = this.h - this.iTopTextBorder - this.iBottomTextBorder;
		desc += '<table width="' + iTextCellWidth + '" height="' + this.h + '" cellpadding="0" cellspacing="0" border="0">';
		// top text border
		if ( this.iTopTextBorder > 0 )
		{
			desc += '<tr style="line-height: ' +  this.iTopTextBorder + 'px"><td';
			if ( is.iemac5 )
				desc += ' style ="position: absolute; top:0px; left:0px" ';
			desc += 'colspan="3" height="' + this.iTopTextBorder + '"><img src="' + this.sSpacer + '" width="1" height="' + this.iTopTextBorder + '" border="0"></td></tr>';
		}
		// left border
		if ( this.iLeftTextBorder > 0 )
		{
			desc += '<td width="' + this.iLeftTextBorder + '';
			if ( is.iemac5 )
				desc += ' style ="position: absolute; top:' + ( ( this.h - iTextCellHeight ) / 2 + this.iTopTextBorder ) + 'px; left:0px" ';
			desc += '><img src="' + this.sSpacer + '" width="' + this.iLeftTextBorder + '" height="1" border="0"></td>';
		}
		// text cell
		desc += '<td width="' + ( iTextCellWidth - this.iLeftTextBorder - this.iRightTextBorder ) + '" height="' + iTextCellHeight + '" valign="middle" align="' + this.sAlign + '"';
		if ( is.iemac5 )
			desc += ' style ="position: absolute; top:' + ( ( this.h - iTextCellHeight ) / 2 + this.iTopTextBorder ) + 'px; left:' + ( this.iLeftTextBorder ) + 'px"';
		desc += '>' + sTextCell + '</td>';
		// right border
		if ( this.iRightTextBorder > 0 )
		{
			desc += '<td width="' + this.iRightTextBorder + '"';
			if ( is.iemac5 )
				desc += ' style ="position: absolute; top:' + ( ( this.h - iTextCellHeight ) / 2 + this.iTopTextBorder ) + 'px; left:' + ( iTextCellWidth - this.iRightTextBorder ) + 'px"';
			desc += '><img src="' + this.sSpacer + '" width="' + this.iRightTextBorder + '" height="1" border="0"></td>';
		}
		desc += '</tr>';
		// bottom text border
		if ( this.iBottomTextBorder > 0 )
		{
			desc += '<tr style="line-height: ' + this.iBottomTextBorder + 'px';
			if ( is.iemac5 )
				desc += ';position: absolute; top:' + ( this.h - this.iBottomTextBorder ) + 'px; left:0px';
			desc +='"><td colspan="3" height="' + this.iBottomTextBorder + '"><img src="' + this.sSpacer + '" width="1" height="' + this.iBottomTextBorder + '" border="0"></td></tr>';
		}
		desc += '</table>';

		// text & icons ---
		if ( sText && sIcon || ( is.iemac5 && sText && !sIcon ) )
		{
			if ( ( is.iemac5 && sText && !sIcon ) )
			{ // ie mac 5.0 renders cells only if there are 2 td's
				iIconWidth = 1; iIconHeight = 1; iIconBorder = 1; img = '<img src="' + this.sSpacer + '" width="' + ( iIconWidth + iIconBorder ) + '" height="' +this.h + '" border="0">';
			}
			tab_body =
				'<tr>' +
					'<td nowrap ';
			if ( is.iemac5 )
			{
				tab_body += 'style="position: absolute; top: 0px; height: ' + this.iFontsize + 'px; width: ' + ( this.w - iIconWidth - iIconBorder ) + 'px; vertical-align: middle;" ';
			}
			tab_body +=
						'width="' + ( this.w - iIconWidth  - iIconBorder ) + '" height="' + this.h + '" align="' + this.sAlign + '" valign="middle">' +
						desc +
					'</td>' +
					'<td ';
			if ( is.iemac5 )
			{
				tab_body += 'style="position: absolute; top: ' + ( ( this.h - iIconHeight ) / 2 ) + 'px; left: ' + ( this.w - iIconWidth - iIconBorder ) + 'px; bottom: ' + iIconHeight + 'px; right:' + ( iIconWidth + iIconBorder ) + 'px;height: ' + iIconHeight + 'px; width: ' + ( iIconWidth + iIconBorder ) + 'px" ';
			}
			tab_body +=
					'width="' + ( iIconWidth + iIconBorder ) + '" height="' + this.h + '" align="center" valign="middle">' +
						img +
					'</td>' +
				'</tr>';
		}
		// text only ---
		else if ( sText && !sIcon )
		{
			tab_body =
				'<tr>' +
					'<td nowrap width="' + this.w + '" height="' + this.h + '" align="' + this.sAlign + '" valign="middle">' +
						desc +
					'</td>' +
				'</tr>';
		}
		// icon only ---
		else if ( sIcon && !sText )
		{
			tab_body = '<tr><td nowrap ';
			if ( is.iemac5 )
			{
				tab_body += 'style="position:absolute;top:0px;left:0px" ';
			}
			tab_body += 'width="' + this.w + '" height="' + this.h + '" align="' + this.sAlign + '" valign="middle">' + sIcon + '</td></tr>';
		}
		var html = tab_head + tab_body + tab_foot;
		return html;
	}
}

/**
* @author	Andr?? Dietisheim (dietisheim@sphere.ch)
* @version 1.4.2, 2004-04-17 (created on 2001-12-20)
* Copyright (c) 2001-2004 Andr?? Dietisheim
*/

function XlayerParent( sLayerId, sImg, sDesc, iWidth, iHeight, sContent )
{
	// static var --------
	if( !XlayerParent.prototype.instances ) XlayerParent.prototype.instances = new Array();
	XlayerParent.prototype.instances[ XlayerParent.prototype.instances.length ] = this;
	this.sId = this.create( sLayerId, sImg, sDesc, iWidth, iHeight )

	this.x = -1;
	this.y = -1;
	this.w = -1;
	this.h = -1;
}

XlayerParent.prototype.create = function( sLayerId, sImg, sDesc, iWidth, iHeight )
{
	this.sParentLayerId = sLayerId;
	this.sParentLayerXlayerId = sLayerId + "Xlayer"

	var sLayer = "";
	var content_str = '';

	if ( sImg )
		sContent = '<img src="' + sImg + '" width="' + iWidth + '" height="' + iHeight + '" border="0" >';
	else if ( sDesc )
		sContent = sDesc;

	// nn4up ----------
	if ( is.nn4up )
	{
		var sLayer = '<ilayer id="' + sLayerId + '" top=0 left=0 width=' + iWidth + ' height=' + iHeight + ' >' + ( ( sContent )? sContent : "" ) + '</ilayer>';
		document.write( sLayer );
		return sLayerId;
	}

	// iewin5up, iemac5up, gk --------
	else if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.kq3up || is.op6up )
	{
		var sLayer = '<div id="' + sLayerId + '" style="position:relative; width: ' + iWidth + 'px; height: ' + iHeight + 'px; ">'  + ( ( sContent )? sContent : "" ) + '</div>';
		document.write( sLayer );
		return sLayerId;
	}
	else
	{
		return null;
	}
}

XlayerParent.prototype.getLayer = function( sLayerId )
{
	var layer = null;

	if ( sLayerId )
	{	// id supplied
		if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.kq3up || is.op6up )
			return document.getElementById( sLayerId );
		else if ( is.nn4up )
			return document.layers[ sLayerId ];
	}
	else if ( !sLayerId )
	{	// null supplied
		if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.kq3up || is.op6up )
			return document.body;
		else if ( is.nn4up )
			return window;
	}
}


XlayerParent.prototype.getX = function()
{
	if ( this.x != -1 )
	{ // return cached value
		return this.x;
	}
	else
	{
		this.x = this.getLayerX( this.getLayer( this.sParentLayerId ) );
		return this.x;
	}
}

XlayerParent.prototype.getLayerX = function( layer )
{
	var x = 0;

	if ( is.nn4up )
	{
		if ( layer != window )			
			x = layer.pageX;
	}
	else if ( is.gk || is.iemac5up || is.iewin5up || is.sf || is.kq3up || is.op6up )
	{
		if ( layer != document.body )
		{
			currentX = 0;
			object = layer;
			while ( object )
			{
				currentX += object.offsetLeft;
				object = object.offsetParent;
			}
			x = currentX;
		}

		if ( is.iemac5up )
			x += parseInt( "0" + document.body.currentStyle.marginLeft, 10  );

	}
	return x;
}

XlayerParent.prototype.getY = function()
{
	if ( this.y != -1 )
	{ // return cached value
		return this.y;
	}
	else
	{
		this.y = this.getLayerY( this.getLayer( this.sParentLayerId ) );
		return this.y;
	}
}

XlayerParent.prototype.getLayerY = function( layer )
{
	var y = 0;

	if ( is.nn4up )
	{
		if ( layer != window )  y = layer.pageY;
	}
	else if ( is.gk || is.iewin || is.iemac5up || is.sf || is.kq3up || is.op6up )
	{
		if ( layer != document.body )
		{
			currentY = 0;
			object = layer;
			while ( object )
			{
				currentY += object.offsetTop;
				object = object.offsetParent;
			}
			y = currentY;
		}
		if ( is.iemac5up )
			y += parseInt( "0" + document.body.currentStyle.marginTop, 10  );
	}

	return y;
}

XlayerParent.prototype.getW = function()
{
	if ( this.w != -1 )
	{ // return cached value
		return this.w;
	}
	else
	{
		this.w = this.getLayerW( this.getLayer( this.sParentLayerId ) );
		return this.w;
	}
}

XlayerParent.prototype.getLayerW = function( layer )
{
	var w = 0;

	if ( is.nn4up )
	{
		if ( layer == window )
			return window.innerWidth;
		else
			return layer.clip.width;
	}
	else if ( is.gk || is.iemac5up || is.sf || is.kq3up || is.op6up )
	{
		if ( layer == document.body )
			return window.innerWidth;
		else
			return layer.offsetWidth;
	}
	else if ( is.iewin5up )
	{
		if ( layer == document.body )
			return document.body.clientWidth;
		else
			return layer.offsetWidth;
	}
}

XlayerParent.prototype.getH = function()
{
	if ( this.h != -1 )
	{ // return cached value
		return this.h;
	}
	else
	{
		this.h = this.getLayerH( this.getLayer( this.sParentLayerId ) );
		return this.h;
	}
}

XlayerParent.prototype.getLayerH = function( layer )
{
	var h = 0;

	if ( is.nn4up )
	{
		if ( layer == window )
			return window.innerHeight;
		else
			return layer.clip.height;
	}
	else if ( is.gk || is.iemac5up || is.sf || is.kq3up || is.op6up )
	{
		if ( layer == document.body )
			return window.innerHeight;
		else
			return layer.offsetHeight;
	}
	else if ( is.iewin5up )
	{
		if ( layer == document.body )
			return document.body.clientHeight;
		else
			return layer.offsetHeight;
	}
}

function Xmenu( sNavigationName, sNavigation, globals, styles, contents )
{
	if( !Xmenu.prototype.instances ) Xmenu.prototype.instances = new Array();
	Xmenu.prototype.instances[ Xmenu.prototype.instances.length ] = this; // store this instance in static Array

	this.index = Xmenu.prototype.instances.length - 1;

	this.sNavigationName = sNavigationName;
	this.sNavigation = sNavigation;
	this.iType = globals[ 0 ];
	this.iCloseDelay = globals[ 1 ] * 1000;
	this.bClick = globals[ 2 ];
	this.bMenuBelow = globals[ 3 ];
	this.bLeftAlign = globals[ 4 ];
	this.bKeepExpansionState = globals[ 5 ];
	this.bHighlightClickedNodes = globals[ 6 ];
	this.sSpacerUrl = globals[ 8 ];
	this.styles = styles;
	this.contents = contents;

	this.iContent = 0;
	this.tree = null;
	this.overNode = null;
	this.outNode = null;
	this.lastNode = null;
	this.absY = 0;
	this.timeout = null;
	this.bOpened = false;
	iParentLayerWidth = ( is.iemac5up )? 0 : globals[ 7 ][ 0 ]; // XparentLayer disturbs Xlayer-events on iemac5
	iParentLayerHeight = ( is.iemac5up )? 0 : globals[ 7 ][ 1 ];
	this.xlayerParent = new XlayerParent( "XlayerParent" + this.index, this.sSpacerUrl, null, iParentLayerWidth, iParentLayerHeight, null );

	this.tree = this.buildTree( 0, 0, false, null, "tree" );

	this.nodeFound = null;
	this.navigationNode = null;
	if ( this.findNode( this.sNavigation, this.tree ) )
	{ // node indicated in request found
		this.navigationNode = eval( "this." + this.nodeFound );
	}
}

Xmenu.prototype.VERTICAL = 0;
Xmenu.prototype.HORIZONTAL = 1;
Xmenu.prototype.COLLAPSING = 2;

Xmenu.prototype.buildTree = function( iAbsX, iAbsY, bSibling, sParent, sPath )
{	
		var node = this.buildNode( iAbsX, iAbsY, bSibling, sParent, sPath );
		this.iContent++;
		if ( this.iContent < this.contents.length && node.iLevel < this.contents[ this.iContent ][ 2 ] )
		{ // child
			node.child = this.buildTree(  node.absX, node.absY, false, "this." + node.sPath, node.sPath + ".child" );
		}
		if ( this.iContent < this.contents.length && node.iLevel == this.contents[ this.iContent ][ 2 ] )
		{ // sibling
			node.sibling = this.buildTree( node.absX, node.absY, true, node.sParent, node.sPath + ".sibling" );
		}
		node.xlayer = this.addXlayer( this.xlayerParent, node, this.styles )
		return node;
}

Xmenu.prototype.buildNode = function( iAbsX, iAbsY, bSibling, sParent, sPath )
{
	var node = new Object();
	node.child = null;
	node.sibling = null;
	node.sParent = sParent;
	node.sPath = sPath;

	node.sText = this.contents[ this.iContent ][ 0 ];
	node.target = this.contents[ this.iContent ][ 1 ];
	node.iLevel = this.contents[ this.iContent ][ 2 ];

	if ( this.iType == this.VERTICAL )
	{
		if ( !bSibling )
		{ // child
			if ( node.iLevel > 1 || ( node.iLevel == 1 && !this.bMenuBelow ) ) // menu below: level 2,3,... || menu right: 1, 2, ...
				node.absX = iAbsX + this.styles[ node.iLevel ][ 2 ] + this.styles[ node.iLevel + 1 ][ 0 ];
			else // menu below: level 0, 1 || menu right: level 0
				node.absX = iAbsX + this.styles[ node.iLevel + 1 ][ 0 ];

			if ( node.iLevel != 1 || ( node.iLevel == 1 && !this.bMenuBelow ) ) // level 0, 2, 3, ... : add yOffset
				node.absY = iAbsY + this.styles[ node.iLevel + 1 ][ 1 ];
			else // level 1: add height of last node + yOffset
				node.absY = iAbsY + this.styles[ node.iLevel + 1 ][ 1 ] + this.styles[ node.iLevel ][ 3 ];
		}
		else
		{ // sibling
			node.absX = iAbsX;
			node.absY = iAbsY + this.styles[ node.iLevel + 1 ][ 3 ];
		}
	}
	else if ( this.iType == this.HORIZONTAL )
	{
		if ( !bSibling )
		{ // child
			if ( node.iLevel > 1 || ( this.bMenuBelow && node.iLevel == 1 ) )
			{ // ( level 1 && menu below ), level 2, 3, 4, ...
				node.absY = iAbsY + this.styles[ node.iLevel + 1 ][ 1 ] + this.styles[ node.iLevel ][ 3 ];
				if ( !this.bLeftAlign ) // add height of last + yOffset, add xOffset
					node.absX = iAbsX + this.styles[ node.iLevel + 1 ][ 0 ];
				else
					node.absX = this.styles[ node.iLevel + 1 ][ 0 ] + this.cumulateOffsets( 0, node.iLevel ) + ( ( node.iLevel > 0 && !this.bMenuBelow )? this.styles[ 1 ][ 2 ] : 0 );
			}
			else
			{ // level 0, ( level 1 && menu on the right ) 
				node.absY = iAbsY + this.styles[ node.iLevel + 1 ][ 1 ];
				if ( !this.bLeftAlign ) // add yOffset, add width of last + xOffset
					node.absX = iAbsX + this.styles[ node.iLevel + 1 ][ 0 ] + ( ( node.iLevel > 0 )? this.styles[ node.iLevel + 1 ][ 2 ] : 0 );
				else
					node.absX = this.styles[ node.iLevel + 1 ][ 0 ] + this.cumulateOffsets( 0, node.iLevel ) + ( ( node.iLevel > 0 && !this.bMenuBelow )? this.styles[ 1 ][ 2 ] : 0 );
			}
		}
		else
		{ // sibling
			node.absY = iAbsY;
			node.absX = iAbsX + this.styles[ node.iLevel + 1 ][ 2 ];
		}
	}
	else if ( this.iType == this.COLLAPSING )
	{
		if ( !bSibling )
		{ // child
			node.absX = iAbsX + this.styles[ node.iLevel + 1 ][ 0 ];
			node.absY = iAbsY + this.styles[ node.iLevel + 1 ][ 1 ];
		}
		else
		{ // sibling
			node.absX = iAbsX;
			node.absY = iAbsY + this.styles[ node.iLevel + 1 ][ 3 ];
		}
	}
	return node;
}

Xmenu.prototype.cumulateOffsets = function( iStyleIndex, iMaxLevel )
{
	var iOffset = 0;
	for ( i = 0; i < iMaxLevel; i++ )
	{
		iOffset += this.styles[ i + 1 ][ iStyleIndex ];
	}
	return iOffset;
}

Xmenu.prototype.addXlayer = function( xparentLayer, node, styles )
{
	var parent =	null;
	var x =	"left";
	var y =	"top";
	var offsetX = node.absX;
	var offsetY = node.absY;
	var w =	styles[ node.iLevel + 1 ][ 2 ];
	var h = styles[ node.iLevel + 1 ][ 3 ];
	var clipTop = 0;
	var clipRight = w;
	var clipBottom = h;
	var clipLeft = 0;
	var zIndex =	node.iLevel;
	var visibility = false;
	var fading =	styles[ node.iLevel + 1 ][ 4 ];
	var events =	
	[ 
		Xlayer.prototype.MOUSEOVER, "Xmenu.prototype.instances[" + this.index + "].onmouseover( Xmenu.prototype.instances[" + this.index + "]." + node.sPath + ")",
		Xlayer.prototype.MOUSEOUT, "Xmenu.prototype.instances[" + this.index + "].onmouseout( Xmenu.prototype.instances[" + this.index + "]." + node.sPath + ")",
		Xlayer.prototype.CLICK, "Xmenu.prototype.instances[" + this.index + "].onclick( Xmenu.prototype.instances[" + this.index + "]." + node.sPath + ")"
	];						
	var sText =  node.sText;
	var bgcolor = styles[ node.iLevel + 1 ][ 5 ][ 0 ];
	var fgcolor =  styles[ node.iLevel + 1 ][ 5 ][ 1 ];
	var align =  styles[ node.iLevel + 1 ][ 5 ][ 2 ];
	var iTopTextBorder = styles[ node.iLevel + 1 ][ 5 ][ 3 ]
	var iRightTextBorder = styles[ node.iLevel + 1 ][ 5 ][ 4 ]
	var iBottomTextBorder = styles[ node.iLevel + 1 ][ 5 ][ 5 ]
	var iLeftTextBorder = styles[ node.iLevel + 1 ][ 5 ][ 6 ]
	var href = null;
	var bold =  styles[ node.iLevel + 1 ][ 5 ][ 7 ];
	var fontface =  styles[ node.iLevel + 1 ][ 5 ][ 8 ];
	var fontsize =  styles[ node.iLevel + 1 ][ 5 ][ 9 ];
	if ( styles[ node.iLevel + 1 ][ 5 ][ 11 ] )
	{	// icon defined
		var icon = ( node.child || styles[ node.iLevel + 1 ][ 5 ][ 10 ] )? styles[ node.iLevel + 1 ][ 5 ][ 11 ] : this.sSpacerUrl;
		var icon_w = styles[ node.iLevel + 1 ][ 5 ][ 12 ];
		var icon_h = styles[ node.iLevel + 1 ][ 5 ][ 13 ];
		var iconBorder = styles[ node.iLevel + 1 ][ 5 ][ 14 ];
	}
	else
	{	// icon not defined
		var icon = null;
		var icon_w = 0;
		var icon_h = 0;
		var iconBorder = 0;
	}
	var src = null; // iframe: src

	return new Xlayer( parent, xparentLayer, x, y, offsetX, offsetY, w, h, clipTop, clipRight, clipBottom, clipLeft, zIndex, visibility, bgcolor, fading, events, sText, bold, align, iTopTextBorder, iRightTextBorder, iBottomTextBorder, iLeftTextBorder, fgcolor, href, icon, icon_w, icon_h, iconBorder, fontface, fontsize, src, this.sSpacerUrl );
}

Xmenu.prototype.create = function()
{
	this.createXlayers( null );
	this.setVisibSiblings( this.tree, true );
}

Xmenu.prototype.createXlayers = function( tree )
{
	if ( !tree ) 
	{ // call without param -> take root node
		tree = this.tree;
	}
	if ( tree.child )
	{
		this.createXlayers( tree.child );
	}
	if ( tree.sibling )
	{
		 this.createXlayers( tree.sibling );
	}

	tree.xlayer.create();
}

Xmenu.prototype.setOpenListener = function( openListener )
{
	this.openListener = openListener;
}

Xmenu.prototype.setCloseListener = function( closeListener )
{
	this.closeListener = closeListener;
}

Xmenu.prototype.setLinkClickListener = function( linkClickListener )
{
	this.linkClickListener = linkClickListener;
}

Xmenu.prototype.open = function()
{	
	if ( this.navigationNode != null )
	{
		this.openLastClicked();
	}
	else
	{
		this.setVisibSiblings( this.tree, true );
	}
	this.bOpened = true;
	this.openListener.onMenuOpen( this );
}

Xmenu.prototype.openLastClicked = function()
{
	node = this.navigationNode;
	this.lastNode = node;

	if ( node.child != null )
		this.setVisibSiblings( node.child, true );

	while ( node != null )
	{
		this.highlightClickedNode( node );
		if ( node.sParent != null )
		{
			this.setVisibSiblings( eval( node.sParent ).child, true );
			node = eval( node.sParent );
		}
		else
		{
			this.setVisibSiblings( this.tree, true );
			node = null;
		}
	}
}

Xmenu.prototype.findNode = function( sText, node )
{
	if ( this.nodeFound )
		return true;

	if ( node.child )
		this.findNode( sText, node.child );

	if ( node.sibling )
		this.findNode( sText, node.sibling );

	if ( sText == node.sText )
		this.nodeFound = node.sPath;

	if ( this.nodeFound ) 
		return true;
	else 
		return false;
}

Xmenu.prototype.close = function()
{
	if ( this.bOpened && !this.bKeepExpansionState )
	{
		this.setVisibChildren( this.tree, false );
		this.setVisibSiblings( this.tree, true );
		if ( this.iType == this.COLLAPSING )
			this.setCollapsePos( this.tree );
//		if ( this.bClick && this.lastNode )
//		{
			this.clearHighlightChildren( this.tree );
			this.lastNode = null;
//		}

		this.bOpened = false;
		this.closeListener.onMenuClose( this );
	}
}

Xmenu.prototype.onmouseover = function( node )
{
	this.overNode = node;
	if ( ( this.iType == this.VERTICAL || this.iType == this.HORIZONTAL ) && !this.bClick )
	{
		if ( !this.bOpened )
		{ // this menu will open
			this.bOpened = true;
			this.openListener.onMenuOpen( this );
		}
		if ( this.outNode )
			var outNode = this.outNode;
		else
			var outNode = this.tree;
		if ( outNode.iLevel > node.iLevel )
		{
			this.showBranch( node, this.outNode );
//			this.setVisibSiblings( eval( outNode.sParent + ".child" ), false );
//			this.setVisibSiblings( outNode.child, false );
		}
		else if ( outNode.iLevel == node.iLevel )
		{
			this.setVisibSiblings( outNode.child, false );
		}
		this.setVisibSiblings( node.child, true );
	}
	if ( this.checkClickPath( node ) )
	{ // current node is not the node that was clicked (or its parents)
		this.highlight( node, true );
	}
	return false;
}

Xmenu.prototype.onmouseout = function( node )
{
	if ( this.checkClickPath( node ) )
		this.highlight( node, false );

	var timeout = this.timeout;
	if ( ( this.iType == this.VERTICAL || this.iType == this.HORIZONTAL ) && !this.bClick ) // close menu if no onmouseover until timeout
	{
		this.timeout = setTimeout( "Xmenu.prototype.instances[" + this.index + "].checkOnmouseout()", this.iCloseDelay );
	}

	this.outNode = node;
	clearTimeout( timeout );
	return false;
}

Xmenu.prototype.checkClickPath = function( node )
{
	if ( this.bHighlightClickedNodes )
	{
		lastNode = this.lastNode;
		while ( lastNode != null )
		{
			if ( lastNode == node ) // node clicked found
				return false;
			else // continue looking for it
				lastNode = eval( lastNode.sParent );
		}
		return true;
	}
	else
	{
		return true;
	}
}

Xmenu.prototype.checkOnmouseout = function()
{
	if ( this.overNode == this.outNode && !( this.bKeepExpansionState && this.bClick ) )
	{ // onmouseover executed since delay?
		this.close();
	}
}

Xmenu.prototype.onclick = function( node )
{	
	if ( node.target )
	{ // follow href
		node.target.open( node.sText, this.sNavigationName, this.sNavigation );
		this.sNavigation = node.sText;
		this.navigationNode = node; // store navigation node
		this.clearHighlightChildren( this.tree );
		this.linkClickListener.onLinkClick( this ); // inform controller
	}
	else if (
		( ( this.iType == this.VERTICAL || this.iType == this.HORIZONTAL ) && this.bClick ) || 
		this.iType == this.COLLAPSING )
	{
		this.highlight( node, true );
		if ( !this.bOpened )
		{ // this menu will open
			this.bOpened = true;
			this.openListener.onMenuOpen( this );
		}
			
		if ( this.iType == this.COLLAPSING )
			this.collapse( node );
		else if ( ( this.iType == this.VERTICAL || this.iType == this.HORIZONTAL ) && this.bClick )
			this.showBranch( node, this.lastNode );
		this.lastNode = node;
	}
	return false;
}

Xmenu.prototype.showBranch = function( node, hideNode )
{
//	if ( this.bClick && hideNode == node && node.child && node.child.xlayer.isVisible() )
	if ( this.bClick && node.child && node.child.xlayer.isVisible() )
	{ // reclose branch
		this.setVisibChildren( node.child, false );
		this.clearHighlightChildren( node, false );
	}
	else
	{
		if ( hideNode )
		{ // hide old nodes
			this.setVisibChildren( this.tree, false );
			this.clearHighlightChildren( this.tree, false );
		}
		if ( node.child ) this.setVisibSiblings( node.child, true );
		while ( node )
		{ // show new nodes
			if ( this.bClick )
				this.highlightClickedNode( node, true );
			if ( node.sParent ) 
				this.setVisibSiblings( eval( node.sParent ).child, true );
			else
				this.setVisibSiblings( this.tree, true );
			node = eval( node.sParent );
		}
	}
}

Xmenu.prototype.clearHighlightChildren = function( node )
{
	if ( node )
	{
		if	( node.child )
			 this.clearHighlightChildren( node.child );
		if ( node.sibling )
			 this.clearHighlightChildren( node.sibling );
		this.highlight( node, false );
	}
}

Xmenu.prototype.collapse = function( node )
{
	this.showBranch( node, this.lastNode );
	this.setCollapsePos( this.tree );
}

Xmenu.prototype.setCollapsePos = function( node )
{
	if ( node == this.tree ) // start looping
		this.absY = this.tree.xlayer.y;
			
	if ( node.xlayer.isVisible() )
	{
		node.xlayer.setPos( node.xlayer.x, this.absY );
		this.absY += node.xlayer.h;
	}

	if ( node.child ) 
		this.setCollapsePos( node.child );
	if ( node.sibling ) 
		this.setCollapsePos( node.sibling );
}

Xmenu.prototype.highlight = function( node, bHighlight )
{
	var index = ( bHighlight )? 6 : 5;	// style for mouseover or mouseout ?
	node.xlayer.setBgColor( this.styles[ node.iLevel + 1 ][ index ][ 0 ] );
	// nn4 crashes, iemac stops rendering
	if ( !is.nn4up && !is.iemac5up ) node.xlayer.setFgColor( this.styles[ node.iLevel + 1 ][ index ][ 1 ] );
}

Xmenu.prototype.highlightClickedNode = function( node )
{
	if ( node && this.bHighlightClickedNodes )
	{
		node.xlayer.setBgColor( this.styles[ 0 ][ 0 ] );
		if ( !is.nn4up && !is.iemac5up ) 
			node.xlayer.setFgColor( this.styles[ 0 ][ 1 ] );
	}
}

Xmenu.prototype.setVisibSiblings = function( node, bVisibility )
{
	if ( node )
	{
		if ( node.sibling )
			 this.setVisibSiblings( node.sibling, bVisibility );
		node.xlayer.setVisibility( bVisibility );
	}
}

Xmenu.prototype.setVisibChildren = function( node, bVisibility )
{
	if ( node )
	{
		if	( node.child )
			 this.setVisibChildren( node.child, bVisibility );
		if	( node.sibling )
			 this.setVisibChildren( node.sibling, bVisibility );
		node.xlayer.setVisibility( bVisibility );
	}
}

Xmenu.prototype.isNavigationNodeFound = function()
{
	return this.navigationNode != null;
}

function WinTarget( sSrc )
{
	this.sSrc = sSrc;
}

WinTarget.prototype.createHref = function( sSrc, sText, sNavigationName, sNavigation ) 
{
	if ( sSrc == "#" )
	{ // create link to same page poping up current menu-entry
		sSrc = location + ""; // window.document.URL
		sSrc = sSrc.replace( new RegExp( sNavigationName + "=[^&]*", "" ), sNavigationName + "=" + escape( sText ) );
		if ( sSrc.indexOf( sNavigationName + "=" ) < 0 )
			sSrc = sSrc + "?" + sNavigationName + "=" + escape( sText );
	}
	return sSrc;
}

WinTarget.prototype.open = function( sText, sNavigationName, sNavigation )
{
	window.location = this.createHref( this.sSrc, sText, sNavigationName, sNavigation );
}

function NewWinTarget( sSrc, iX, iY, iWidth, iHeight )
{
	this.win = null;
	this.sSrc = sSrc;
	this.iX = iX;
	this.iY = iY;
	this.iWidth = iWidth;
	this.iHeight = iHeight;
}

NewWinTarget.prototype.open = function()
{
	var sOpts = "toolbar=yes,location=yes,status=yes,menubar=yes,resizable=yes,scrollbars=yes";

	if ( document.body && document.body.offsetWidth )
		sOpts += ",width=" + this.iWidth;
	else if ( window.innerWidth )
		sOpts += ",innerWidth=" + this.iWidth + ",";

	if ( document.body && document.body.offsetHeight )
		sOpts += ",height=" + this.iHeight
	else if ( window.innerHeight )
		sOpts += ",innerHeight=" + this.iHeight

	sOpts +=",top=" + this.iY;
	sOpts += ",left=" + this.iX;

	this.win = top.open( this.sSrc, "", sOpts );
}

function FrameTarget( sSrc, sId )
{
	this.sSrc = sSrc;
	this.sId = sId;
}

FrameTarget.prototype.open = function()
{
	var target = top.frames[ this.sId ];
	target = this.findFrame( this.sId, top.frames );
	if ( target )
	{
		target.document.location.href = this.sSrc;
	}
}

FrameTarget.prototype.findFrame = function( sId, frameArray )
{
	if ( frameArray[ sId ] )
	{
		return frameArray[ sId ];
	}
	for ( i = 0; i < frameArray.length; i++ )
	{
		return this.findFrame( sId, frameArray[ i ] );
	}
	return null; // fix nn4.0: necessar inutile return value
}

function FunctionTarget( func )
{
	this.func = func;
}

FunctionTarget.prototype.open = function()
{
	if ( this.func )
	{
		this.func();
	}
}

function Xmenus( sNavigationName, sNavigation )
{
	if( !Xmenus.prototype.instances ) Xmenus.prototype.instances = new Array();
	Xmenus.prototype.instances[ Xmenus.prototype.instances.length ] = this;

	this.index = Xmenus.prototype.instances.length - 1;
	this.bCompatibleBrowser = ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.kq3up || is.nn4up || is.op6up );
	this.iCloseDelay = 1;
	this.xmenus = new Array();

	this.sNavigationName = sNavigationName;
	this.sNavigation = sNavigation;
	this.navigationMenu = null;

	this.lastMenu = null;
	this.timeout = null;
	this.bReopenDisabled = false;
}

Xmenus.prototype.add = function( xmenuConfig )
{
	if ( this.bCompatibleBrowser )
		this.xmenus[ this.xmenus.length ] = new Xmenu( this.sNavigationName, this.sNavigation, xmenuConfig[ 0 ], xmenuConfig[ 1 ], xmenuConfig[ 2 ] );
	else
		this.writeDegradationHtml( xmenuConfig );
}

Xmenus.prototype.create = function()
{
	if ( !this.bCompatibleBrowser ) return;

	for ( j = 0; j < this.xmenus.length; j++ )
	{
		this.xmenus[ j ].setOpenListener( this );
		this.xmenus[ j ].setCloseListener( this );
		this.xmenus[ j ].setLinkClickListener( this );
		this.xmenus[ j ].create();
		if ( this.xmenus[ j ].isNavigationNodeFound() )
		{
			this.openNavigationMenu( this.xmenus[ j ] );
		}
	}
}

Xmenus.prototype.openNavigationMenu = function( xmenu )
{
			this.navigationMenu = xmenu;
			this.lastMenu = xmenu;
			xmenu.open();
}

Xmenus.prototype.onMenuOpen = function( xmenu )
{ // fired by Xmenu on menu open
	if ( this.lastMenu != null && this.lastMenu != xmenu )
	{
		this.bReopenDisabled = true;
		this.lastMenu.close();
		this.bReopenDisabled = false;
	}
	this.bOpened = true;
	this.lastMenu = xmenu;
}

Xmenus.prototype.onMenuClose = function( xmenu )
{  // fired by Xmenu on menu close
	if ( !this.bReopenDisabled )
	{
		this.timeout = setTimeout( "Xmenus.prototype.instances[" + this.index + "].reopenAfterClose()", this.iCloseDelay * 1000 );
	}
	this.bOpened = false;
}

Xmenus.prototype.reopenAfterClose = function()
{
	if ( !this.bOpened && this.navigationMenu != null )
	{ //no other menu is opened -> open navigation menu
		this.navigationMenu.open();
	}
}

Xmenus.prototype.onLinkClick = function( xmenu )
{  // fired by Xmenu on click on a link
//	this.navigationMenu.clearHighlightChildren( this.navigationMenu.tree );
	this.openNavigationMenu( xmenu );
}

Xmenus.prototype.writeDegradationHtml = function( xmenuConfig )
{
	var content = xmenuConfig[ 2 ];
	for ( i = 0; i < content.length; i++ )
	{
		if ( content[ i ][ 2 ] == 0 && content[ i ][ 1 ] )
			document.write( '<a href="' + content[ i ][ 1 ].sSrc + '">' + content[ i ][ 0 ] + '</a><br>' );;
	}
}

var menus = new Xmenus( '', '' );

function onLoad()
{
	menus.create()
	debug.flushBuffer();
}

function onResize()
{
	window.location.reload();
}
			
function helloAlert()
{
	alert( "hello! I'm hungry" );
}
