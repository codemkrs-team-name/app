function confirm(message, callBack){
	
	//optional arguments
	var options = (arguments[2]) ? arguments[2] : {};
	if (!options.confirmed)	options.confirmed = false;
	if (!options.method)	options.method = 'go';//'get' or 'go'
	if (!options.callbackArguments)	options.callbackArguments = false;
	
	//console.log(options.callbackArguments);
	
	if (((typeof callBack == 'string') && (callBack.length < 1)) || (message.length < 1))
	{
		dialog("Necessary parameters missing.", {type:'error'});
		return false;
	}
	//if a string is passed, make the callBack a url redirect
	if (typeof callBack == 'string')
	{
		var url = callBack;
		callBack = function(){
			request(url, options);
		}
	}
		
	if (! options.confirmed){		
		dialog(message, { buttons: { 
			  "No": function() { $(this).dialog("close");},		   
			  "Yes": function() { $(this).dialog("close"); confirm(message, callBack, {confirmed:true, method: options.method, callbackArguments: options.callbackArguments});}
		}});
		return false;
	}	
	
	if (typeof callBack == 'function')
	{
		//console.log("confirm " + options.callbackArguments);
		if (options.callbackArguments !== false)
			callBack(options.callbackArguments);	
		else
			callBack();
	}


}//end confirm


function request(url){

	//optional arguments
	var options = (arguments[1]) ? arguments[1] : {};
	if (!options.method)	options.method = 'go';//'get' or 'go'

	if (url.length < 1){
		dialog("Necessary parameters missing.", {type:'error'});
		return false;
	}		
	
	//if url is site root relative, add domain
	if (url.indexOf('/') == 0){
		var currentUrl = window.location.href;
		var domain = currentUrl.replace('http://', '');
		domain = domain.substr(0, domain.indexOf('/'));
		url = 'http://' + domain + url;
	}		
	

	if (options.method == 'get'){
	
		loading();	
		$.ajax({
   			type: 'GET',
   			url: url,
			dataType: 'json',
   			error: function(data, status) {		
			
				loaded();			
				if (! data.statusText)	data.statusText = "There was a problem completing this action.";					
				dialog(data.statusText, {type:'error'});
		
			},
			success: function(json){
				
				loaded();				
				if (json.flashes)	showFlashes(json.flashes);
						
			}//end success	
		});
	
	} else {
		
		window.location = url;
		
	}	
	
}//end function request


function flash(message){
		
	if (message.length < 1)	return false;
		
	var output = "";
	//optional arguments
	var parameters = (arguments[1]) ? arguments[1] : {};	
		
	if (! parameters.type) parameters.type = "alert"; //possible types:  alert, success, error
	if (! parameters.quote) parameters.quote = false;
	if (! parameters.image) parameters.image = "/img/" + parameters.type + ".png";

	if (! parameters.className) parameters.className = "";

	if (parameters.quote){
		output += "<div class = 'quote' style = 'clear: both;' class = '" + parameters.className + "'>\
	<img style = 'display: block; position: absolute; margin-top: 0px; margin-left: 25px; z-index:2;' src = '/img/error_quote.png' />\n";
		parameters.className = '';
	}
		
	output += "<div class = 'flash " + parameters.type + "'>\
<table cellpadding = '0' cellspacing = '0'>\
    <tr style = 'vertical-align:middle;'>\
        <td><img class = 'margin-right' src = '" + parameters.image + "' /></td>\
        <td>" + message + "</td>\
    </tr>\
</table>\
</div>\n";
	if (parameters.quote)	output += "</div>\n";

	return output;
	
}//end function flash

function dialog(message){
	
	if (message.length < 1)	return false;
	
	//optional arguments
	var parameters = (arguments[1]) ? arguments[1] : {};	
		
	if (! parameters.type) parameters.type = ""; //possible types:  alert, success, error
	if (! parameters.image) parameters.image = "/img/" + parameters.type + ".png";	
	if (! parameters.title) parameters.title = "";
	if (! parameters.buttons) parameters.buttons = { "Ok": function() { $(this).dialog("close"); } }
	
	if (parameters.type.length > 0){
		parameters.dialogClass = "dialog" + capWords(parameters.type);
		
		content = "<div id = 'dialog' title = '" + parameters.title + "' class = 'flash " + parameters.type + "'>\
<table cellpadding = '0' cellspacing = '0'>\
    <tr style = 'vertical-align:middle;'>\
        <td><img class = 'margin-right' src = '" + parameters.image + "' /></td>\
        <td>" + message + "</td>\
    </tr>\
</table>\
</div>";
	} else 
		content = "<div id = 'dialog' title = '" + parameters.title + "'>" + message + "</div>";	
	
	$("#dialog").remove();	
	$(document.body).append(content);

	$("#dialog").dialog(parameters);

	var top = (($(window).height() - $("#dialog").height()) / 2 - 140);
	var left = (($(window).width() - $("#dialog").width()) / 2 - 50);
	$('#dialog').dialog('option', 'position', ['center', top]);	
	//$(".ui-dialog").css("top", top).css("left", left);
	

	//remove focus
	$("#dialog button").blur();
	
}//end function

//retrieve any flashes from the session and display them as dialogs (for AJAX requests)
function getFlashes(){
	
	
	dispatch.post('messages', 'session', function(json){
												  
		if (! json)	return false;
		if (! json.messages)	return false;	
	
		showFlashes(json.messages);

	});	
	
}//end function getFlashes

//display JSON messages as dialogs
function showFlashes(messages){
	
	if (! messages)	return false;	
	
	
	var message = "";
	var className = ""; 
	var count = 0;
	var counter = 0;
	var output = "";
	
	if (messages.alert){	
		count = messages.alert.length;
		for(counter = 0; counter < count; counter++){
			message = messages.alert[counter];		
			if (message.length > 0){
				className = "";
				if ((counter + 1) == count)	className = "last"; 		
				output += "<div class='message " + className + "'>" + message + "</div>\n";
			}//end if
		}//end for
		dialog(output, {type:'alert'});	
	}//end if alert
	
	if (messages.success){	
	
		//remove updated status
		$("#characteristicsUpdated").remove();
	
		count = messages.success.length;
		for(counter = 0; counter < count; counter++){
			message = messages.success[counter];		
			if (message.length > 0){
				className = "";
				if ((counter + 1) == count)	className = "last"; 		
				output += "<div class='message " + className + "'>" + message + "</div>\n";
			}//end if
		}//end for
		dialog(output, {type:'success'});	
	}//end if alert	
	
	if (messages.error){	
		count = messages.error.length;
		for(counter = 0; counter < count; counter++){
			message = messages.error[counter];		
			if (message.length > 0){
				className = "";
				if ((counter + 1) == count)	className = "last"; 		
				output += "<div class='message " + className + "'>" + message + "</div>\n";
			}//end if
		}//end for
		dialog(output, {type:'error'});	
	}//end if alert	
	
}//end function showFlashes


function loading() {

	//Optional Arguments
	var trigger = (arguments[0]) ? arguments[0] : false;
	var offset = {};
	
	$("#content").append("<div id = 'loading' align = 'center'><span><img src = '/img/loading.gif' alt = 'loading' height = '50' width = '150' /></span></div>");
		
	if (trigger){
		$("#loading").unbind("mousemove");
		//alert("hello" + trigger);
				
		if (trigger.left){//dimensions specified
			offset = trigger;
		} else {
			offset = $(trigger).offset();
			offset.left = offset.left + $(trigger).width();
			offset.top = offset.top + $(trigger).height();
		} 		
		
		//alert(offset.top + " - " + offset.left);

		$('#loading').css("top", offset.top).css("left", offset.left);			

	} else {
		/*
		$().mousemove(function(e){
			$('#loading').css("top", e.pageY + 15).css("left", e.pageX + 15);	
  		});	
		*/
		
		/*
		offset.top = ($(window).height() / 2);
		offset.left = ($(window).width() / 2) - 85;
		
		$('#loading').css("top", offset.top).css("left", offset.left);
		*/
		$('#loading').fixedCenter();
	}
	$("#loading").fadeIn("slow", function(){}); 	

	setTimeout(function(){
		loaded();
	}, (30000) );		

}//end function

function loaded() {
	if(document.getElementById("loading")){
		$("#loading").fadeOut("slow", function(){										   
			$("#loading").unbind("mousemove").remove();	
		});	
	}
}//end function


//sets site navigation
function setNav(){

	
}//end function


$(document).ready(function(){
	
});



