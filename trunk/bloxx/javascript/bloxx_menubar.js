DOM = (document.getElementById) ? 1 : 0;
NS4 = (document.layers) ? 1 : 0;
// We need to explicitly detect Konqueror
// because Konqueror 3 sets IE = 1 ... AAAAAAAAAARGHHH!!!
Konqueror = (navigator.userAgent.indexOf("Konqueror") > -1) ? 1 : 0;
// We need to detect Konqueror 2.2 as it does not handle the window.onresize event
Konqueror22 = (navigator.userAgent.indexOf("Konqueror 2.2") > -1 || navigator.userAgent.indexOf("Konqueror/2.2") > -1) ? 1 : 0;
Opera = (navigator.userAgent.indexOf("Opera") > -1) ? 1 : 0;
Opera5 = (navigator.userAgent.indexOf("Opera 5") > -1 || navigator.userAgent.indexOf("Opera/5") > -1) ? 1 : 0;
Opera6 = (navigator.userAgent.indexOf("Opera 6") > -1 || navigator.userAgent.indexOf("Opera/6") > -1) ? 1 : 0;
Opera56 = Opera5 || Opera6;
IE = (navigator.userAgent.indexOf("MSIE") > -1) ? 1 : 0;
IE = IE && !Opera;
IE5 = IE && DOM;
IE4 = (document.all) ? 1 : 0;
IE4 = IE4 && IE && !DOM;

useTimeouts = 1;
timeoutLength = 1000;        // time in ms; not significant if useTimeouts = 0;
shutdownOnClick = 0;

loaded = 0;
layersMoved = 0;
layerPoppedUp = "";

timeoutFlag = 0;
if (Opera56 || IE4) {
        useTimeouts = 0;
}
if (NS4 || Opera56 || IE4) {
        shutdownOnClick = 1;
}

currentY = 0;
function grabMouse(e) {        // for NS4
        currentY = e.pageY;
}
if (NS4) {
        document.captureEvents(Event.MOUSEDOWN | Event.MOUSEMOVE);
        document.onmousemove = grabMouse;
}

function seeThroughElements(show) {
        if (show) {
                foobar = "visible";
        } else {
                foobar = "hidden";
        }
        for (i=0; i<toBeHidden.length; i++) {
                toBeHidden[i].style.visibility = foobar;
        }
}

function shutdown() {
        for (i=0; i<numl; i++) {
                LMPopUpL(listl[i], false);
        }
        layerPoppedUp = "";
        if (Konqueror || IE5) {
                seeThroughElements(true);
        }
}
if (shutdownOnClick) {
        if (NS4) {
                document.onmousedown = shutdown;
        } else {
                document.onclick = shutdown;
        }
}

function setLMTO() {
        if (useTimeouts) {
                timeoutFlag = setTimeout('shutdown()', timeoutLength);
        }
}

function clearLMTO() {
        if (useTimeouts) {
                clearTimeout(timeoutFlag);
        }
}

function moveLayerX(menuName) {
        if (!loaded || (isVisible(menuName) && menuName != layerPoppedUp)) {
                return;
        }
        if (father[menuName] != "") {
                if (!Opera5 && !IE4) {
                        width0 = lwidth[father[menuName]];
                        width1 = lwidth[menuName];
                } else if (Opera5) {
                        // Opera 5 stupidly and exaggeratedly overestimates layers widths
                        // hence we consider a default value equal to $abscissaStep
                        width0 = abscissaStep;
                        width1 = abscissaStep;
                } else if (IE4) {
                        width0 = getOffsetWidth(father[menuName]);
                        width1 = getOffsetWidth(menuName);
                }
                onLeft = getOffsetLeft(father[menuName]) - width1 + menuLeftShift;
                onRight = getOffsetLeft(father[menuName]) + width0 - menuRightShift;
                windowWidth = getWindowWidth();
                windowXOffset = getWindowXOffset();
//                if (NS4 && !DOM) {
//                        windowXOffset = 0;
//                }
                if (onLeft < windowXOffset && onRight + width1 > windowWidth + windowXOffset) {
                        if (onRight + width1 - windowWidth - windowXOffset > windowXOffset - onLeft) {
                                onLeft = windowXOffset;
                        } else {
                                onRight = windowWidth + windowXOffset - width1;
                        }
                }
                if (back[father[menuName]]) {
                        if (onLeft < windowXOffset) {
                                back[menuName] = 0;
                        } else {
                                back[menuName] = 1;
                        }
                } else {
//alert(onRight + " - " + width1 + " - " +  windowWidth + " - " + windowXOffset);
                        if (onRight + width1 > windowWidth + windowXOffset) {
                                back[menuName] = 1;
                        } else {
                                back[menuName] = 0;
                        }
                }
                if (back[menuName]) {
                        setLeft(menuName, onLeft);
                } else {
                        setLeft(menuName, onRight);
                }
        }
        moveLayerY(menuName);        // workaround needed for Mozilla < 1.4 for MS Windows
}

function moveLayerY(menuName) {
        if (!loaded || (isVisible(menuName) && menuName != layerPoppedUp)) {
                return;
        }
        if (!layersMoved) {
                moveLayers();
                layersMoved = 1;
        }
        if (!NS4) {
                newY = getOffsetTop("ref" + menuName);
        } else {
                newY = currentY;
        }
        newY += menuTopShift;
        layerHeight = getOffsetHeight(menuName);
        windowHeight = getWindowHeight();
        windowYOffset = getWindowYOffset();
        if (newY + layerHeight > windowHeight + windowYOffset) {
                if (layerHeight > windowHeight) {
                        newY = windowYOffset;
                } else {
                        newY = windowHeight + windowYOffset - layerHeight;
                }
        }
        if (Math.abs(getOffsetTop(menuName) - newY) > thresholdY) {
                setTop(menuName, newY);
        }
}

function moveLayerX1(menuName, father) {
        if (!lwidthDetected) {
                return;
        }
        if (!Opera5 && !IE4) {
                width1 = lwidth[menuName];
        } else if (Opera5) {
                // Opera 5 stupidly and exaggeratedly overestimates layers widths
                // hence we consider a default value equal to $abscissaStep
                width1 = abscissaStep;
        }
        foobar = getOffsetLeft(father + menuName);
if (!IE4) {
        windowWidth = getWindowWidth();
        windowXOffset = getWindowXOffset();
        if (foobar + width1 > windowWidth + windowXOffset) {
                foobar = windowWidth + windowXOffset - width1;
        }
        if (foobar < windowXOffset) {
                foobar = windowXOffset;
        }
}
        setLeft(menuName, foobar);
}

function layersOverlap(layer, i) {
        if (Konqueror22) {
                return true;
        }

//        xa1 = getOffsetLeft(layer);
//setLeft(layer, xa1);
        xa1 = layerLeft[layer];
        xa2 = xa1 + getOffsetWidth(layer);
//setWidth(layer, xa2-xa1);
//        ya1 = getOffsetTop(layer);
//setTop(layer, ya1);
        ya1 = layerTop[layer];
        ya2 = ya1 + getOffsetHeight(layer);
//setHeight(layer, ya2-ya1);
//alert(":" + xa1 + ":" + xa2 + ":" + ya1 + ":" + ya2 + ":");

        xb1 = toBeHiddenLeft[i];
        xb2 = xb1 + toBeHidden[i].offsetWidth;
        yb1 = toBeHiddenTop[i];
        yb2 = yb1 + toBeHidden[i].offsetHeight;
//alert(":" + xb1 + ":" + xb2 + ":" + yb1 + ":" + yb2 + ":");

        if(xb1>xa1) xa1=xb1; if(xb2<xa2) xa2=xb2;
        if(yb1>ya1) ya1=yb1; if(yb2<ya2) ya2=yb2;

        return (xa2>xa1 && ya2>ya1);
}

function seeThroughWorkaround(menuName, on) {
        for (i=0; i<toBeHidden.length; i++) {
                if (layersOverlap(menuName, i)) {
                        if (on) {
                                toBeHidden[i].style.visibility = "hidden";
                        } else {
                                toBeHidden[i].style.visibility = "visible";
                        }
                }
        }
}

function LMPopUpL(menuName, on) {
        if (!loaded) {
                return;
        }
        if (!layersMoved) {
                moveLayers();
                layersMoved = 1;
        }
        setVisibility(menuName, on);
}

function LMPopUp(menuName, isCurrent) {
        if (!loaded || menuName == layerPoppedUp || (isVisible(menuName) && !isCurrent)) {
                return;
        }
        if (menuName == father[layerPoppedUp]) {
                LMPopUpL(layerPoppedUp, false);
//                seeThroughWorkaround(menuName, false);
        } else if (father[menuName] == layerPoppedUp) {
                LMPopUpL(menuName, true);
                seeThroughWorkaround(menuName, true);
        } else {
                shutdown();
                foobar = menuName;
                do {
                        LMPopUpL(foobar, true);
                        seeThroughWorkaround(foobar, true);
                        foobar = father[foobar];
                } while (foobar != "")
        }
/*
        if (layerPoppedUp == "") {
                seeThroughElements(false);
        }
*/
        layerPoppedUp = menuName;
}

function resizeHandler() {
        if (NS4) {
                window.location.reload();
        }
        shutdown();
        for (i=0; i<numl; i++) {
                setLeft(listl[i], 0);
                setTop(listl[i], 0);
        }
//        moveLayers();
        layersMoved = 0;
}
window.onresize = resizeHandler;

function yaresizeHandler() {
        if (window.innerWidth != origWidth || window.innerHeight != origHeight) {
                if (Konqueror22 || Opera5) {
                        window.location.reload();        // Opera 5 often fails this
                }
                origWidth  = window.innerWidth;
                origHeight = window.innerHeight;
                resizeHandler();
        }
        setTimeout('yaresizeHandler()', 500);
}
function loadHandler() {
        if (Konqueror22 || Opera56) {
                origWidth  = window.innerWidth;
                origHeight = window.innerHeight;
                yaresizeHandler();
        }
}
window.onload = loadHandler;

function fixieflm(menuName) {
        if (DOM) {
                setWidth(menuName, "100%");
        } else {        // IE4 IS SIMPLY A BASTARD !!!
                document.write("</div>");
                document.write("<div id=\"IE4" + menuName + "\" style=\"position: relative; width: 100%; visibility: visible;\">");
        }
}

layerLeft = new Array();
layerTop = new Array();

function setVisibility(layer,on) {
        if (on) {
                if (DOM) {
                        document.getElementById(layer).style.visibility = "visible";
                } else if (NS4) {
                        document.layers[layer].visibility = "show";
                } else {
                        document.all[layer].style.visibility = "visible";
                }
        } else {
                if (DOM) {
                        document.getElementById(layer).style.visibility = "hidden";
                } else if (NS4) {
                        document.layers[layer].visibility = "hide";
                } else {
                        document.all[layer].style.visibility = "hidden";
                }
        }
}

function isVisible(layer) {
        if (DOM) {
                return (document.getElementById(layer).style.visibility == "visible");
        } else if (NS4) {
                return (document.layers[layer].visibility == "show");
        } else {
                return (document.all[layer].style.visibility == "visible");
        }
}

function setLeft(layer,x) {
layerLeft[layer] = x;
        if (DOM && !Opera5) {
                document.getElementById(layer).style.left = x + "px";
        } else if (Opera5) {
                document.getElementById(layer).style.left = x;
        } else if (NS4) {
                document.layers[layer].left = x;
        } else {
                document.all[layer].style.pixelLeft = x;
        }
}

function getOffsetLeft(layer) {
        var value = 0;
        if (DOM) {        // Mozilla, Konqueror >= 2.2, Opera >= 5, IE
                object = document.getElementById(layer);
                value = object.offsetLeft;
//alert (object.tagName + " --- " + object.offsetLeft);
                while (object.tagName != "BODY" && object.offsetParent) {
                        object = object.offsetParent;
//alert (object.tagName + " --- " + object.offsetLeft);
                        value += object.offsetLeft;
                }
        } else if (NS4) {
                value = document.layers[layer].pageX;
        } else {        // IE4 IS SIMPLY A BASTARD !!!
                if (document.all["IE4" + layer]) {
                        layer = "IE4" + layer;
                }
                object = document.all[layer];
                value = object.offsetLeft;
                while (object.tagName != "BODY") {
                        object = object.offsetParent;
                        value += object.offsetLeft;
                }
        }
        return (value);
}

function setTop(layer,y) {
layerTop[layer] = y;
        if (DOM && !Opera5) {
                document.getElementById(layer).style.top = y + "px";
        } else if (Opera5) {
                document.getElementById(layer).style.top = y;
        } else if (NS4) {
                document.layers[layer].top = y;
        } else {
                document.all[layer].style.pixelTop = y;
        }
}

function getOffsetTop(layer) {
// IE 5.5 and 6.0 behaviour with this function is really strange:
// in some cases, they return a really too large value...
// ... after all, IE is buggy, nothing new
        var value = 0;
        if (DOM) {
                object = document.getElementById(layer);
                value = object.offsetTop;
                while (object.tagName != "BODY" && object.offsetParent) {
                        object = object.offsetParent;
                        value += object.offsetTop;
                }
        } else if (NS4) {
                value = document.layers[layer].pageY;
        } else {        // IE4 IS SIMPLY A BASTARD !!!
                if (document.all["IE4" + layer]) {
                        layer = "IE4" + layer;
                }
                object = document.all[layer];
                value = object.offsetTop;
                while (object.tagName != "BODY") {
                        object = object.offsetParent;
                        value += object.offsetTop;
                }
        }
        return (value);
}

function setWidth(layer,w) {
        if (DOM) {
                document.getElementById(layer).style.width = w;
        } else if (NS4) {
//                document.layers[layer].width = w;
        } else {
                document.all[layer].style.pixelWidth = w;
        }
}

function getOffsetWidth(layer) {
        var value = 0;
        if (DOM && !Opera56) {
                value = document.getElementById(layer).offsetWidth;
        } else if (NS4) {
                value = document.layers[layer].document.width;
        } else if (Opera56) {
                value = document.getElementById(layer).style.pixelWidth;
        } else {        // IE4 IS SIMPLY A BASTARD !!!
                if (document.all["IE4" + layer]) {
                        layer = "IE4" + layer;
                }
                value = document.all[layer].offsetWidth;
        }
        return (value);
}

function setHeight(layer,h) {        // unused, not tested
        if (DOM) {
                document.getElementById(layer).style.height = h;
        } else if (NS4) {
//                document.layers[layer].height = h;
        } else {
                document.all[layer].style.pixelHeight = h;
        }
}

function getOffsetHeight(layer) {
        var value = 0;
        if (DOM && !Opera56) {
                value = document.getElementById(layer).offsetHeight;
        } else if (NS4) {
                value = document.layers[layer].document.height;
        } else if (Opera56) {
                value = document.getElementById(layer).style.pixelHeight;
        } else {        // IE4 IS SIMPLY A BASTARD !!!
                if (document.all["IE4" + layer]) {
                        layer = "IE4" + layer;
                }
                value = document.all[layer].offsetHeight;
        }
        return (value);
}

function getWindowWidth() {
        var value = 0;
        if ((DOM && !IE) || NS4 || Konqueror || Opera) {
                value = top.innerWidth;
//        } else if (NS4) {
//                value = document.width;
        } else {        // IE
                if (document.documentElement && document.documentElement.clientWidth) {
                        value = document.documentElement.clientWidth;
                } else if (document.body) {
                        value = document.body.clientWidth;
                }
        }
        if (isNaN(value)) {
                value = top.innerWidth;
        }
        return (value);
}

function getWindowXOffset() {
        var value = 0;
        if ((DOM && !IE) || NS4 || Konqueror || Opera) {
                value = window.pageXOffset;
        } else {        // IE
                if (document.documentElement && document.documentElement.scrollLeft) {
                        value = document.documentElement.scrollLeft;
                } else if (document.body) {
                        value = document.body.scrollLeft;
                }
        }
        return (value);
}

function getWindowHeight() {
        var value = 0;
        if ((DOM && !IE) || NS4 || Konqueror || Opera) {
                value = top.innerHeight;
        } else {        // IE
                if (document.documentElement && document.documentElement.clientHeight) {
                        value = document.documentElement.clientHeight;
                } else if (document.body) {
                        value = document.body.clientHeight;
                }
        }
        if (isNaN(value)) {
                value = top.innerHeight;
        }
        return (value);
}

function getWindowYOffset() {
        var value = 0;
        if ((DOM && !IE) || NS4 || Konqueror || Opera) {
                value = window.pageYOffset;
        } else {        // IE
                if (document.documentElement && document.documentElement.scrollTop) {
                        value = document.documentElement.scrollTop;
                } else if (document.body) {
                        value = document.body.scrollTop;
                }
        }
        return (value);
}


