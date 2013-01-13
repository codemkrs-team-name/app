#!/bin/bash
node event_king.js
cat target/events.json | bin/python amend_metadata.py
