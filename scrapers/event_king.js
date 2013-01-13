var exec = require('child_process').exec;
var fs = require('fs');
var moment = require('moment');
var _ = require('underscore');

var refresh =  true;

var eventList = [];

var fuzzyMatches = [];

var uselessWords = ['of', 'and', 'the'];

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
	ev.time = moment(ev.time, ['ddd MMMM DD, h A', 'YYYY-MM-DD HH']);
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

var scraper = function(cmd, outfile) {
	return function(done) {
		var processReturnedEvents = function(error, stdout, stderr) {
			var eventsToProcess;
			if (!outfile) {
				eventsToProcess = JSON.parse(stdout.toString());
			} else {
				eventsToProcess = JSON.parse(fs.readFileSync(outfile));
			}
			for (var i = 0; i < eventsToProcess.length; i++) {
				addEvent(eventsToProcess[i]);
			}
			done();
		};
		if (refresh) {
			exec(cmd, processReturnedEvents);
		} else {
			processReturnedEvents();
		}
	};
};

var scrapers = [
  scraper('livewire/bin/python livewire/scrape_events.py','target/lw.json'),
  scraper('node offbeat/of.js', 'target/of.json'),
  scraper('node barryfest/bf.js', 'target/bf.json')
];
var outfile = process.argv[2]
var scrapersRunning = scrapers.length;
scrapers.forEach(function(s) {
	s(function() {
		if (--scrapersRunning === 0) {
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
		}
	});
});
