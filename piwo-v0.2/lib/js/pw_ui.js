function findPosX(obj) {
  var curleft = 0;

  if(obj.offsetParent) {
    while(1) {
      curleft += obj.offsetLeft;
      if(!obj.offsetParent)
        break;
      obj = obj.offsetParent;
    }
  } else if(obj.x) {
    curleft += obj.x;
  }

  obj.style.position = "static";

  return curleft;
}

function findPosY(obj) {
  var curtop = 0;

  if(obj.offsetParent) {
    while(1) {
      curtop += obj.offsetTop;
      if(!obj.offsetParent)
        break;
      obj = obj.offsetParent;
    }
  } else if(obj.y) {
    curtop += obj.y;
  }

  return curtop;
}

function findPos(obj) {
  var left = findPosX(obj);
  var top = findPosY(obj);

  return [left , top];
}

function findPosition( oElement ) {
  if( typeof( oElement.offsetParent ) != 'undefined' ) {
    for( var posX = 0, posY = 0; oElement; oElement = oElement.offsetParent ) {
      posX += oElement.offsetLeft;
      posY += oElement.offsetTop;
    }
    return [ posX, posY ];
  } else {
    return [ oElement.x, oElement.y ];
  }
}







function f_clientWidth() {
	return f_filterResults (
		window.innerWidth ? window.innerWidth : 0,
		document.documentElement ? document.documentElement.clientWidth : 0,
		document.body ? document.body.clientWidth : 0
	);
}
function f_clientHeight() {
	return f_filterResults (
		window.innerHeight ? window.innerHeight : 0,
		document.documentElement ? document.documentElement.clientHeight : 0,
		document.body ? document.body.clientHeight : 0
	);
}
function f_scrollLeft() {
	return f_filterResults (
		window.pageXOffset ? window.pageXOffset : 0,
		document.documentElement ? document.documentElement.scrollLeft : 0,
		document.body ? document.body.scrollLeft : 0
	);
}
function f_scrollTop() {
	return f_filterResults (
		window.pageYOffset ? window.pageYOffset : 0,
		document.documentElement ? document.documentElement.scrollTop : 0,
		document.body ? document.body.scrollTop : 0
	);
}
function f_filterResults(n_win, n_docel, n_body) {
	var n_result = n_win ? n_win : 0;
	if (n_docel && (!n_result || (n_result > n_docel)))
		n_result = n_docel;
	return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
}
