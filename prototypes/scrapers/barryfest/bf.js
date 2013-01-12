var  sys 			= require('util')
		,rest 		= require('restler')
		,xml2js		= require('xml2js')
		,_ 				= require('underscore')
    ,fs       = require('fs')
		
    ,source   = 'http://www.google.com/calendar/feeds/matthew.r.rosenthal%40gmail.com/public/basic'
    ,output   = 'bf.json'

		,time 		= /When:\s*([^<]*)<.*/
		,location = /Where:\s*([^<]*)<.*/
		;
rest.get(source).on('complete', function(result) {
	var  parser = new xml2js.Parser()
			;
  if (result instanceof Error) 
    return sys.puts('Error: ' + result.message);		

  parser.parseString(result, function parse(err, result) {

  	var entries = _.map(result.feed.entry, function(x){
  		return {
  			 eventName: x.title[0]._
  			,venue: fix(x.content[0]._.match(location)[1])
  			,time: fix(x.content[0]._.match(time)[1])
  			,image: null
  			,price: null
        ,description: null
        ,links: x.link[0] && [
          {
             type: 'gcal'
            ,link: x.link[0].$.href
          }
        ] || []
  		}
  	} );	
		fs.writeFile(output, JSON.stringify(entries));
  });
});

function fix(v) {
	if(!v) return;
	return v.trim().replace('\n', '');
}