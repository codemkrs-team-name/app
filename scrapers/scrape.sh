#!/bin/bash
echo "Scraping..."
bin/python livewire/scrape_events.py
node offbeat/of.js
node barryfest/bf.js
echo "Merging events"
node event_king.js target/events-raw.json
echo "Amend events"
bin/python amend_metadata.py target/events-raw.json target/events.json
