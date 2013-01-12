var  sys 			= require('util')
		,rest 		= require('restler')
		,xml2js		= require('xml2js')
		,_ 				= require('underscore')
		
		,time 		= /When:\s*([^<]*)<.*/
		,location = /Where:\s*([^<]*)<.*/
		;
rest.get('http://www.google.com/calendar/feeds/matthew.r.rosenthal%40gmail.com/public/basic').on('complete', function(result) {
	var  parser = new xml2js.Parser()
			;
  if (result instanceof Error) 
    return sys.puts('Error: ' + result.message);		

  parser.parseString(result, function (err, result) {
  	var entries = _.first(_.map(result.feed.entry, function(x){
  		return {
  			 event: x.title[0]._
  			,location: fix(x.content[0]._.match(location)[1])
  			,time: fix(x.content[0]._.match(time)[1])
  			,image: "?"
  			,price: "?"
  		}
  	} ), 5);	
		console.log(entries);
  });
});

function fix(v) {
	if(!v) return;
	return v.trim().replace('\n', '');
}