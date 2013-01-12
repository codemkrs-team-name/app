var  rest 			= require('restler')
	,_ 				= require('./underscoreExtensions')
	,htmlparser 	= require("htmlparser")
    ,fs       		= require('fs')
    ,du 			= require('date-utils')
	,log  			= _.bind(console.log, console)

	,source 		= 'http://www.offbeat.com/new-listings/?g=listing&d=date&t=detail&v='
	,output 		= 'of.json'

	,next3Days 		= _.chain(_.range(3))
						.map(function(d){ return new Date().add({days: d}) })
						.map(function(d){ return d.toFormat('YYYYMMDD') })
						.value()
	results 		= []
	;

	_.each(next3Days, getEventsOn(_.after(3, function writeToFile(results) {
		fs.writeFile(output, JSON.stringify(results));
	})) );

function getEventsOn(callback) {	return function(formattedDate) {
	rest.get(source+formattedDate).on('complete', function(rawhtml) {
		var handler = new htmlparser.DefaultHandler(function (error, dom) {
			if (error) return console.error(error);

			var  html 			= _.where(dom, {name: 'html'})[0]
				,allNodes 	= _.collectTreeNodes(html)
				,pars 			= _.where(allNodes, {name: 'p'}).slice(3, -1)
				;
			results.push(_.map(pars, parse));
			callback(_.flatten(results));
		});

		var parser = new htmlparser.Parser(handler);
		parser.parseComplete(rawhtml);
	});
} }

function parse(p) {
	var  anchors = _.where(p.children, {name: 'a'})
		,texts =  _.where(p.children, {type: 'text'})
		;
	return {
		 eventName: text(anchors[0])
		,venue: text(anchors[1])
		,time: text(texts[0])
		,image: null
		,price: null
		,description: null
		,links: []
	};
}

function text(n){
	return 	!n ? null : 
					n.type == 'text' ? n.data :
					text(n.children[0]); 
}