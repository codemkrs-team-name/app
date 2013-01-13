#!/bin/bash
node event_king.js
cat events.json | bin/python amend_metadata.py
