var exec = require('child_process').exec;
var fs = require('fs');
var moment = require('moment');

var refresh = false;

var eventList = [];
var scrapersRunning = 6;

var addEvent = function(ev) {
	var newTime = moment(ev.time, ['ddd MMMM DD, h A']);
	ev.time = newTime;
	eventList.push(ev);
};

var scraper = function(cmd, outfile) {
	return function(done) {
		var processReturnedEvents = function(error, stdout, stderr) {
			var eventsToProcess;
			if (outfile === null) {
				eventsToProcess = JSON.parse(stdout);
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
	//scraper('python livewire/scrape_events.py'),
	scraper('node scrapers/offbeat/of.js', 'of.json'),
	scraper('node scrapers/barryfest/bf.js', 'bf.json')
];
var scrapersRunning = scrapers.length;
scrapers.forEach(function(s) {
	s(function() {
		if (--scrapersRunning === 0) {
			eventList.sort(function(a, b) {
				return a.time.unix() - b.time.unix();
			});
			var tableData = '<html><head><link rel="stylesheet" type="text/css" href="thing.css" /></head><body><table>';
				tableData += '<tr>';
				for (var key in eventList[0]) {
					tableData += '<th>' + key + '</th>';
				}
				tableData += '</tr>';
			eventList.forEach(function(e) {
				tableData += '<tr>';
				for (var key in e) {
					var value = e[key];
					if (key == 'time') {
						value = value.format("dddd, MMMM Do, h:mm:ss a");
					}
					tableData += '<td class="' + key + '">' + value + '</td>';
				}
				tableData += '</tr>';
			});
			tableData += '</table></body></html>';
			fs.writeFileSync('events.html', tableData);
		}
	});
});