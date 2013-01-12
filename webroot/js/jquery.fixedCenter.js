/***
@title:
Fixed Center

@version:
1.2

@author:
David Tang

@date:
2010-06-27 - updated plugin to use fixed positioning instead of absolute
2010-06-17 - released version 1 of the plugin

@url
www.david-tang.net

@copyright:
2010 David Tang

@requires:
jquery

@does:
This plugin centers an element on the page using fixed positioning and keeps the element centered 
if you scroll horizontally or vertically.

@howto:
jQuery('#my-element').absoluteCenter(); would center the element with ID 'my-element' using absolute positioning 

*/

jQuery.fn.fixedCenter = function(){
	return this.each(function(){
		var element = jQuery(this);
		centerElement();
		jQuery(window).bind('resize',function(){
			centerElement();
		});
			
		function centerElement(){
			var elementWidth = jQuery(element).outerWidth();
			var elementHeight = jQuery(element).outerHeight();
			var windowWidth = jQuery(window).width();
			var windowHeight = jQuery(window).height();	
			
			var X2 = windowWidth/2 - elementWidth/2;
			var Y2 = windowHeight/2 - elementHeight/2;
	 
			jQuery(element).css({
				'left':X2,
				'top':Y2,
				'position':'fixed'
			});						
		} //end of centerElement function
					
	}); //end of return this.each
}