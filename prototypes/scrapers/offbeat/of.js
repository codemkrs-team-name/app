var  rest 			= require('restler')
	,_ 				= require('./underscoreExtensions')
	,htmlparser 	= require("htmlparser")
	;
var	 ondate 			= 20130111
	,log  				= _.bind(console.log, console)
	;
rest.get('http://www.offbeat.com/new-listings/?g=listing&d=date&t=detail&v='+ondate).on('complete', function(rawhtml) {
	var handler = new htmlparser.DefaultHandler(function (error, dom) {
		if (error) return console.error(error);

		var  html 			= _.where(dom, {name: 'html'})[0]
				,allNodes 	= _.collectTreeNodes(html)
				,pars 			= _.where(allNodes, {name: 'p'})
				;
		_.each(_.initial(pars), _.compose(log, parse));
	});

	var parser = new htmlparser.Parser(handler);
	parser.parseComplete(rawhtml);
	//sys.puts(sys.inspect(handler.dom, false, null));
});


function parse(p) {
	var  anchors = _.where(p.children, {name: 'a'})
			,texts =  _.where(p.children, {type: 'text'})
			;
	return {
		 event: text(anchors[0])
		,venue: text(anchors[1])
		,time: text(texts[0])
		,image: null
		,price: null
	};
}

function text(n){
	return 	!n ? null : 
					n.type == 'text' ? n.data :
					text(n.children[0]); 
}