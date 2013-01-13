#!/bin/bash
node event_king.js target/events-raw.json
bin/python amend_metadata.py target/events-raw.json target/events.json
