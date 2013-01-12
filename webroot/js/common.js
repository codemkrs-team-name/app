// This document contains non-site-specific javascript functions and utilities

//delays for the given number of miliseconds
function wait(millis)
{
var date = new Date();
var curDate = null;

do { curDate = new Date(); }
while(curDate-date < millis);
}


function isArray(testObject) {   
    return testObject && !(testObject.propertyIsEnumerable('length')) && typeof testObject === 'object' && typeof testObject.length === 'number';
}

function isEmpty(object) {
	for(var i in object) { 
		//alert(i);
		return false; 
	}
	return true;
}


function print_r(arr,level) {
var dumped_text = "";
if(!level) level = 0;

//The padding given at the beginning of the line.
var level_padding = "";
for(var j=0;j<level+1;j++) level_padding += "    ";

if(typeof(arr) == 'object') { //Array/Hashes/Objects
 for(var item in arr) {
  var value = arr[item];
 
  if(typeof(value) == 'object') { //If it is an array,
   dumped_text += level_padding + "'" + item + "' ...\n";
   dumped_text += dump(value,level+1);
  } else {
   dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
  }
 }
} else { //Stings/Chars/Numbers etc.
 dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
}
return dumped_text;
} 


function isSet(  ) {
    
    var a=arguments; var l=a.length; var i=0;
    
    if (l==0) { 
        throw new Error('Empty isset'); 
    }
    
    while (i!=l) {
        if (typeof(a[i])=='undefined' || a[i]===null) { 
            return false; 
        } else { 
            i++; 
        }
    }
    return true;
}


function isNumeric(sText)

{
   var ValidChars = "0123456789.";
   var IsNumber=true;
   var Char;

 
   for (i = 0; i < sText.length && IsNumber == true; i++) 
      { 
      Char = sText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1) 
         {
         IsNumber = false;
         }
      }
   return IsNumber;
   
}

function breakoutOfFrame()
{
  if (top.location != location) {
    top.location.href = document.location.href ;
  }
}

function jumpMenu(selectElement){ //v3.0
	var value = $(selectElement).val();
	
	if (value && value.length > 0)
	{
		window.location = value;	
	}	
}


function getAllChildren(e) {
  // Returns all children of element. Workaround required for IE5/Windows. Ugh.
  return e.all ? e.all : e.getElementsByTagName('*');
}//end function getAllChildren

document.getElementsBySelector = function(selector) {
  // Attempt to fail gracefully in lesser browsers
  if (!document.getElementsByTagName) {
    return new Array();
  }
  // Split selector in to tokens
  var tokens = selector.split(' ');
  var currentContext = new Array(document);
  for (var i = 0; i < tokens.length; i++) {
    token = tokens[i].replace(/^\s+/,'').replace(/\s+$/,'');;
    if (token.indexOf('#') > -1) {
      // Token is an ID selector
      var bits = token.split('#');
      var tagName = bits[0];
      var id = bits[1];
      var element = document.getElementById(id);
	  
      if (tagName && element.nodeName.toLowerCase() != tagName) {
        // tag with that ID not found, return false
        return new Array();
      }
      // Set currentContext to contain just this element
      currentContext = new Array(element);
      continue; // Skip to next token
    }
    if (token.indexOf('.') > -1) {
      // Token contains a class selector
      var bits = token.split('.');
      var tagName = bits[0];
      var className = bits[1];
      if (!tagName) {
        tagName = '*';
      }
      // Get elements matching tag, filter them for class selector
      var found = new Array;
      var foundCount = 0;
      for (var h = 0; h < currentContext.length; h++) {
        var elements;
        if (tagName == '*') {
            elements = getAllChildren(currentContext[h]);
        } else {
            elements = currentContext[h].getElementsByTagName(tagName);
        }
        for (var j = 0; j < elements.length; j++) {
          found[foundCount++] = elements[j];
        }
      }
      currentContext = new Array;
      var currentContextIndex = 0;
      for (var k = 0; k < found.length; k++) {
        if (found[k].className && found[k].className.match(new RegExp('\\b'+className+'\\b'))) {
          currentContext[currentContextIndex++] = found[k];
        }
      }
      continue; // Skip to next token
    }
    // Code to deal with attribute selectors
    if (token.match(/^(\w*)\[(\w+)([=~\|\^\$\*]?)=?"?([^\]"]*)"?\]$/)) {
      var tagName = RegExp.$1;
      var attrName = RegExp.$2;
      var attrOperator = RegExp.$3;
      var attrValue = RegExp.$4;
      if (!tagName) {
        tagName = '*';
      }
      // Grab all of the tagName elements within current context
      var found = new Array;
      var foundCount = 0;
      for (var h = 0; h < currentContext.length; h++) {
        var elements;
        if (tagName == '*') {
            elements = getAllChildren(currentContext[h]);
        } else {
            elements = currentContext[h].getElementsByTagName(tagName);
        }
        for (var j = 0; j < elements.length; j++) {
          found[foundCount++] = elements[j];
        }
      }
      currentContext = new Array;
      var currentContextIndex = 0;
      var checkFunction; // This function will be used to filter the elements
      switch (attrOperator) {
        case '=': // Equality
          checkFunction = function(e) { return (e.getAttribute(attrName) == attrValue); };
          break;
        case '~': // Match one of space seperated words 
          checkFunction = function(e) { return (e.getAttribute(attrName).match(new RegExp('\\b'+attrValue+'\\b'))); };
          break;
        case '|': // Match start with value followed by optional hyphen
          checkFunction = function(e) { return (e.getAttribute(attrName).match(new RegExp('^'+attrValue+'-?'))); };
          break;
        case '^': // Match starts with value
          checkFunction = function(e) { return (e.getAttribute(attrName).indexOf(attrValue) == 0); };
          break;
        case '$': // Match ends with value - fails with "Warning" in Opera 7
          checkFunction = function(e) { return (e.getAttribute(attrName).lastIndexOf(attrValue) == e.getAttribute(attrName).length - attrValue.length); };
          break;
        case '*': // Match ends with value
          checkFunction = function(e) { return (e.getAttribute(attrName).indexOf(attrValue) > -1); };
          break;
        default :
          // Just test for existence of attribute
          checkFunction = function(e) { return e.getAttribute(attrName); };
      }
      currentContext = new Array;
      var currentContextIndex = 0;
      for (var k = 0; k < found.length; k++) {
        if (checkFunction(found[k])) {
          currentContext[currentContextIndex++] = found[k];
        }
      }
      // alert('Attribute Selector: '+tagName+' '+attrName+' '+attrOperator+' '+attrValue);
      continue; // Skip to next token
    }
    // If we get here, token is JUST an element (not a class or ID selector)
    tagName = token;
    var found = new Array;
    var foundCount = 0;
    for (var h = 0; h < currentContext.length; h++) {
      var elements = currentContext[h].getElementsByTagName(tagName);
      for (var j = 0; j < elements.length; j++) {
        found[foundCount++] = elements[j];
      }
    }
    currentContext = found;
  }
  return currentContext;
}

/* That revolting regular expression explained 
/^(\w+)\[(\w+)([=~\|\^\$\*]?)=?"?([^\]"]*)"?\]$/
  \---/  \---/\-------------/    \-------/
    |      |         |               |
    |      |         |           The value
    |      |    ~,|,^,$,* or =
    |   Attribute 
   Tag
*/

/* addEvent: simplified event attachment */
function addEvent( obj, type, fn ) {
	if (obj.addEventListener) {
		obj.addEventListener( type, fn, false );
		eventCache.add(obj, type, fn);
	}
	else if (obj.attachEvent) {
		obj["e"+type+fn] = fn;
		obj[type+fn] = function() { obj["e"+type+fn]( window.event ); }
		obj.attachEvent( "on"+type, obj[type+fn] );
		eventCache.add(obj, type, fn);
	}
	else {
		obj["on"+type] = obj["e"+type+fn];
	}
}
	
var eventCache = function(){
	var listEvents = [];
	return {
		listEvents : listEvents,
		add : function(node, sEventName, fHandler){
			listEvents.push(arguments);
		},
		flush : function(){
			var i, item;
			for(i = listEvents.length - 1; i >= 0; i = i - 1){
				item = listEvents[i];
				if(item[0].removeEventListener){
					item[0].removeEventListener(item[1], item[2], item[3]);
				};
				if(item[1].substring(0, 2) != "on"){
					item[1] = "on" + item[1];
				};
				if(item[0].detachEvent){
					item[0].detachEvent(item[1], item[2]);
				};
				item[0][item[1]] = null;
			};
		}
	};
}();
addEvent(window,'unload',eventCache.flush);

/* window 'load' attachment */
function addLoadEvent(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	}
	else {
		window.onload = function() {
			oldonload();
			func();
		}
	}
}

/* grab Elements from the DOM by className */
function getElementsByClass(searchClass,node,tag) {
	var classElements = new Array();
	if ( node == null )
		node = document;
	if ( tag == null )
		tag = '*';
	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;
	var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
	for (i = 0, j = 0; i < elsLen; i++) {
		if ( pattern.test(els[i].className) ) {
			classElements[j] = els[i];
			j++;
		}
	}
	return classElements;
}

/* toggle an element's display */
function toggle(obj) {
	var el = document.getElementById(obj);
	if ( el.style.display != 'none' ) {
		el.style.display = 'none';
	}
	else {
		el.style.display = '';
	}
}

/* insert an element after a particular node */
function insertAfter(parent, node, referenceNode) {
	parent.insertBefore(node, referenceNode.nextSibling);
}

var internetConnection = false;
function checkInternetConnection(){
	
	var url = window.location.href;

	//if it's local and navigator.onLine isn't available, assume no connection
	
	if (navigator.onLine){
		internetConnection = true;
		return true;
	} else {	
		if (url.indexOf('localhost') !== -1){
			internetConnection = false;
			return false;			
		}
	}
	
	//if it's not local and navigator.onLine isn't available, try request to own domain
	
	var xmlhttp=false;
	/*@cc_on @*/
	/*@if (@_jscript_version >= 5)
	// JScript gives us Conditional compilation, we can cope with old IE versions.
	// and security blocked creation of the objects.

	 try {
	  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	 } catch (e) {
	  try {
	   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	  } catch (E) {
	   xmlhttp = false;
	  }
	 }
	@end @*/
	
	if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
		try {
			xmlhttp = new XMLHttpRequest();
		} catch (e) {
			xmlhttp=false;
		}
	}
	if (!xmlhttp && window.createRequest) {
		try {
			xmlhttp = window.createRequest();
		} catch (e) {
			xmlhttp=false;
		}
	}

	
	xmlhttp.open("HEAD", url,true);
 	xmlhttp.onreadystatechange=function() {
		internetConnection = false;	
  		if (xmlhttp.readyState==4) {
			//if (xmlhttp.status==200) alert("URL Exists!")
    		//else if (xmlhttp.status==404) alert("URL doesn't exist!")
     		//else alert("Status is "+xmlhttp.status)
			if (xmlhttp.status==404){
				internetConnection = false;
			} else {
				internetConnection = true;
			}
  		}
 	}//end function
	xmlhttp.send(null);

}//end function


function checkCookies(){
	
	var cookiesEnabled = true;
	
	if (getCookie('test')){
		cookiesEnabled = true;
		deleteCookie('test', '/', 'thlinx.com');// name, path, domain
	} else {		
		setCookie( 'test', 'none', '', '/', '', '' );// name, value, expires, path, domain, secure		
		if (getCookie('test')){
			cookiesEnabled = true;
			deleteCookie('test', '/', 'thlinx.com');// name, path, domain
		} else {		
			cookiesEnabled = false;
			var redirectUrl = (arguments[0]) ? arguments[0] : false;
		
			if (redirectUrl !== false){
				if (window.location.toString().indexOf(redirectUrl) == -1)	window.location = redirectUrl;
			}		
		}
	}
	
	return cookiesEnabled;
	
}//end function

/* get, set, and delete cookies */
function getCookie( name ) {
	var start = document.cookie.indexOf( name + "=" );
	var len = start + name.length + 1;
	if ( ( !start ) && ( name != document.cookie.substring( 0, name.length ) ) ) {
		return false;
	}
	if ( start == -1 ) return false;
	var end = document.cookie.indexOf( ";", len );
	if ( end == -1 ) end = document.cookie.length;
	return unescape( document.cookie.substring( len, end ) );
}
	
function setCookie( name, value, expires, path, domain, secure ) {
	var today = new Date();
	today.setTime( today.getTime() );
	if ( expires ) {
		expires = expires * 1000 * 60 * 60 * 24;
	}
	var expires_date = new Date( today.getTime() + (expires) );
	document.cookie = name+"="+escape( value ) +
		( ( expires ) ? ";expires="+expires_date.toGMTString() : "" ) + //expires.toGMTString()
		( ( path ) ? ";path=" + path : "" ) +
		( ( domain ) ? ";domain=" + domain : "" ) +
		( ( secure ) ? ";secure" : "" );
}
	
function deleteCookie( name, path, domain ) {
	if ( getCookie( name ) ) document.cookie = name + "=" +
			( ( path ) ? ";path=" + path : "") +
			( ( domain ) ? ";domain=" + domain : "" ) +
			";expires=Thu, 01-Jan-1970 00:00:01 GMT";
}




function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}
function ltrim(stringToTrim) {
	return stringToTrim.replace(/^\s+/,"");
}
function rtrim(stringToTrim) {
	return stringToTrim.replace(/\s+$/,"");
}

function capWords(inputString) {

var outputString;
var tmpStr, tmpChar, preString, postString, strlen;
tmpStr = inputString.toLowerCase();
stringLen = tmpStr.length;
if (stringLen > 0)
{
  for (i = 0; i < stringLen; i++)
  {
    if (i == 0)
	{
      tmpChar = tmpStr.substring(0,1).toUpperCase();
      postString = tmpStr.substring(1,stringLen);
      tmpStr = tmpChar + postString;
    }
    else
	{
      tmpChar = tmpStr.substring(i,i+1);
      if (tmpChar == " " && i < (stringLen-1))
	  {
      tmpChar = tmpStr.substring(i+1,i+2).toUpperCase();
      preString = tmpStr.substring(0,i+1);
      postString = tmpStr.substring(i+2,stringLen);
      tmpStr = preString + tmpChar + postString;
      }
    }
  }
}
return tmpStr;
}//end cap words

//used on input fields when you want the default value to be cleared on click
function clearDefault(el) {
  if (el.defaultValue==el.value) el.value = ""
}



function popUp(url, width, height)
{
	if (window.popwin) {popwin.close();}
	popwin=window.open(url,'popup', "width="+width+",height="+height+",scrollbars=1,resizable=1,toolbar=0");
	if (window.focus) {popwin.focus();}
}  

function setRollovers(selector, over, on) { //adds rollover and 'on' image behavior to all 'rollover' class images & preloads 
var images = document.getElementsBySelector(selector);
var url = String(document.location);

for (var i = 0; i < images.length; i++) { 
   href = images[i].parentNode.getAttribute('href');    
   image = images[i].src.substring(0, (images[i].src.length - 4));   
   type = images[i].src.substring(images[i].src.length - 4, images[i].src.length);      
   if ((href.length == (url.length - url.indexOf(href)))&&(url.match(href) != null)){ //if image link is contained at end of url
	 //if (href == url){ //if image links to current page, show on image
     images[i].src = image + on + type;      
   } else { // else add mouseover image event handler, and preload
	 //alert (href.length + " does not equal " + (url.length - url.indexOf(href)));	 
	 images[i].preloaded = new Image();
	 images[i].preloaded.src = image + over + type;
   images[i].onmouseover = function (){this.src = this.preloaded.src;};
	 images[i].onmouseout = function (){this.src = this.src.replace('_over', '');};         
   }   
} //end for 
} // end function