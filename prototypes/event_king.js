var exec = require('child_process').exec;
var fs = require('fs');

var eventList = [];
var scrapersRunning = 6;

var scraper = function(cmd, outfile) {
	return function(done) { exec(cmd, function(error, stdout, stderr) {
		var eventsToProcess;
		if (outfile === null) {
			eventsToProcess = JSON.parse(stdout);
		} else {
			eventsToProcess = JSON.parse(fs.readFileSync(outfile));
		}
		for (var i = 0; i < eventsToProcess.length; i++) {
			eventList.push(eventsToProcess[i]);
		}
		done();
	})};
};

var scrapers = [
	//scraper('python livewire/scrape_events.py'),
	scraper('node scrapers/offbeat/of.js', 'of.json'),
	scraper('node scrapers/barryfest/bf.js', 'bf.json')
];
var scrapersRunning = scrapers.length;
scrapers.forEach(function(s) {
	s(function() {
		if (--scrapersRunning === 0) {
			console.log(eventList);
		}
	});
});