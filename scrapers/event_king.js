var exec = require('child_process').exec;
var fs = require('fs');
var moment = require('moment');
var _ = require('underscore');

var refresh =  true;

var eventList = [];

var fuzzyMatches = [];

var uselessWords = ['of', 'and', '&', '+'];

var removeUselessWords = function(arr) {
	return _.reject(arr, function(elem) { return _.contains(uselessWords, elem); });
}

var fuzzyMatch = function(a, b) {
	a = removeUselessWords(a.toLowerCase().replace(/[^a-zA-Z0-9 ]+/g, "").match(/\S+/g)).slice(0,2).join('');
	b = removeUselessWords(b.toLowerCase().replace(/[^a-zA-Z0-9 ]+/g, "").match(/\S+/g)).slice(0,2).join('');
	if (fuzzyMatches.indexOf(a) == -1) {
		fuzzyMatches.push(a);
	}
	return a === b;
}

var addEvent = function(ev) {
	ev.originalDateTimeString = ev.time
	ev.time = parseDateTime(ev.originalDateTimeString);
	ev.time.year(moment().year());
	if (ev.time.month() == 11 || ev.time.unix() < moment().unix()) return;
	ev.venue = ev.venue.trim().replace(new RegExp("\u2019"), "'").replace(/\s+/, " ");
	for (var i = 0; i < eventList.length; i++) {
		var match = eventList[i];
		if (fuzzyMatch(ev.venue, match.venue) && ev.time.unix() === match.time.unix()) {
			return;
		}
	}
	eventList.push(ev);
};

var scrape = function(outfile) {
    var eventsToProcess = JSON.parse(fs.readFileSync(outfile));
    for (var i = 0; i < eventsToProcess.length; i++) {
      addEvent(eventsToProcess[i]);
    }
}

scrape('target/lw.json');
scrape('target/of.json');
scrape('target/bf.json');
var outfile = process.argv[2]
eventList.sort(function(a, b) {
  var diff = a.time.unix() - b.time.unix();
  if (diff === 0) {
    diff = a.venue.localeCompare(b.venue);
  }
  return diff;
});
eventList.forEach(function(e) {
  e.time = e.time.unix();
});
fs.writeFileSync(outfile, JSON.stringify(eventList,null,'  '));


function parseDateTime(timeString) {
	var m;

	if( /\w{3} \w+ \d\d?, \d\d?:\d\d \w\w/.test(timeString) )  
		// Mon February 4, 1:00 PM
		return moment(new Date(moment(timeString, "ddd MMMM D, hh:mm a").toDate().setYear(moment().year())))
	if( /\w{3} \w{3} \d\d?, \d{4} \d\d?:\d\d\w\w/.test(timeString) )
		// Sat Mar 2, 2013 10:30pm to 10:30pm CST
		return moment(timeString, "ddd MMM D, YYYY hh:mma");
	if((m = moment(timeString, "ddd MMM D, YYYY hha")).isValid()) 
		// Sat Mar 2, 2013 10pm to 10pm CST
		return m;
	if((m = moment(timeString, "YYYY-MM-DD HH:mm") ).isValid())
		// 2013-02-08 17:00
		return m;
	return moment(timeString); //catchall
}

