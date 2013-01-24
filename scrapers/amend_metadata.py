# -*- coding: utf-8 -*-
import json
import sys
import re
from fuzzywuzzy import process
import HTMLParser
import codecs
import csv
import StringIO
from csvkit.unicsv import UnicodeCSVDictReader,UnicodeCSVWriter
import requests
from datetime import datetime

ARTIST_URL = 'https://docs.google.com/spreadsheet/pub?key=0Au-GFjxq7ilLdHRnNnA2c3F1WU1hX3c4Q1lPYWRuQkE&single=true&gid=0&output=csv'
VENUE_URL = 'https://docs.google.com/spreadsheet/pub?key=0Au-GFjxq7ilLdHRnNnA2c3F1WU1hX3c4Q1lPYWRuQkE&single=true&gid=1&output=csv'

def read_csv(url):
  resp = requests.get(url)
  csvfile = StringIO.StringIO(resp.text.encode("utf-8"))
  reader = UnicodeCSVDictReader(csvfile,encoding=resp.encoding)
  return list(reader)

def canonical(string,stop_words=None):
  # remove everything that is not alphabetical or spaces
  string = re.sub(r'[^\w\s]','',string)
  # normalize whitespace
  string = re.sub(r'\s+',' ',string.strip().lower())
  if stop_words:
    words = re.split(r'\s+',string)
    words = [w for w in words if w not in stop_words]
    string = ' '.join(words)
  return string

def canonical_venue(string):
  return canonical(string,stop_words=['the','music','club','bar','restaurant','grill','and','on','by','pub','cafe'])

def canonical_artist(string):
  return canonical(string,stop_words=['and','the','&','friends'])

def index(rows,index_func=canonical):
  indexed_rows = {}
  for r in rows:
    key = index_func(r['name'])
    indexed_rows[key] = r
    if 'alt_name' in r:
      for name in re.split(r'\n',r['alt_name']):
        name = name.strip()
        if name:
          indexed_rows[index_func(name)] = r
  return indexed_rows
  
def single_artist_for_name(name,score=80):
  global artists_index
  name = canonical_artist(name)
  # look for exact match
  artist = artists_index.get(name)
  if artist:
    return artist
  if score < 100:
    hit = process.extractOne(name,artists_index.keys())
    # 90 is excellent
    # 80 is good
    # lets be optimisitic
    if hit and hit[1] >= 80:
      return artists_index[hit[0]]
  return None

def artists_for_name(name):
  global artists_index
  # try for a direct match
  exact_match = single_artist_for_name(name,110)
  if exact_match:
    return [exact_match]
  # look for joined acts and try to get all the artists
  for sep in ['\+','featuring','feat.','&','and']:
    pattern = r'\s+'+sep+r'\s+'
    matches = []
    parts = re.split(pattern,name)
    if len(parts) > 1:
      for part in parts:
        m = single_artist_for_name(part,90)
        if m:
          matches.append(m)
    if len(matches):
      return matches
  # try a fuzzier match on whole name
  last_try = single_artist_for_name(name,80)
  if last_try:
    return [last_try]
  else:
    return []


def venue_for_name(name):
  global venues_index
  name = canonical_venue(name)
  v = venues_index.get(name)
  if v:
    return v
  # no exact hit - do fuzzy match
  hit,score = process.extractOne(name,venues_index.keys())
  # 90 is excellent
  # 80 is good
  # lets be optimisitic
  if score >= 80:
    return venues_index[hit]
  return None

def update_event_with_venue(evt,venue):
  evt['venue'] = venue['name']
  if venue['address']:
    evt['address'] = ('''
%(address)s<br>
%(city)s, %(state)s %(zip)s
        ''' % venue).strip()
  if venue.get('price'):
    evt['price'] = venue['price']
  if venue.get('phone'):
    evt['phone'] = re.sub(r'[^\d]','',venue.get('phone'))
  if venue.get('venuepage_href'):
    add_event_link(evt,'venue',venue['name'],venue['url'])
  if venue['lat'] and venue['lon']:
    evt['location'] = dict(lat=float(venue['lat']),
        lon=float(venue['lon']))

def add_event_link(evt,link_type,url,text):
  evt['links'].append(dict(text=text,link=url,type=link_type))

def update_event_with_artists(evt,evt_artists):
  evt['ranking'] = max([int(a['rating']) for a in evt_artists])
  desc = ["<p>%s</p>" % a['summary'] for a in evt_artists if a.get('summary')]
  tags = [a['tags'] for a in evt_artists if a['tags']]
  for artist in evt_artists:
    image = artist.get('image')
    if image and not evt['image']:
      evt['image'] = dict(src=image,href=artist['image_attrib'])
    artist_url = artist.get('url')
    if artist_url:
      add_event_link(evt,'artist',artist_url,artist['name'])
    # integration with www.digitaltipjar.com
    tip_id = artist.get('tip_id')
    if tip_id:
      add_event_link(evt,'artist_tip',"http://www.digitaltipjar.com/%s" % tip_id,artist['name']+" (Digital Tip Jar)")
  if len(desc):
    evt['description'] = "".join(desc)
  if len(tags):
    evt['tags'] = ','.join(tags)

def clean_text(event,field):
  text = event[field]
  text = html_parser.unescape(text).strip()
  event[field] = text

def events_soon(event):
  event_ts = datetime.fromtimestamp(event['time'])
  now = datetime.now()
  delta = event_ts-now
  return delta.days <= 4

if __name__ == '__main__':
  artists = read_csv(ARTIST_URL)
  artists_index = index(artists,canonical_artist)
  print "%d artists" % len(artists)

  venues = read_csv(VENUE_URL)
  venues_index = index(venues,canonical_venue)
  print "%d venues" % len(venues)

  infile = sys.argv[1]
  events = json.load(open(infile,'r'))
  html_parser = HTMLParser.HTMLParser()

  # preamble
  # incoming events are not super strict on days
  # filter to within next four days
  events = filter(events_soon,events)
  # event name and venue have html entity codes in there
  for evt in events:
    clean_text(evt,'eventName')
    clean_text(evt,'venue')

  print "#### %d" % len(events)
  # get to work
  for evt in events:
    name = evt['eventName']
    evt['eventName'] = name
    evt['ranking'] = 0
    venue_name = evt['venue']
    evt_artists = artists_for_name(name)
    if len(evt_artists):
      update_event_with_artists(evt,evt_artists)
    else:
      print "Missing artist: %s" % name.encode("utf-8")
    venue = venue_for_name(venue_name)
    if venue:
      update_event_with_venue(evt,venue)
    else:
      print "No Venue: %s" % venue_name.encode("utf-8")

  # dedup - we may have resolved venues with different names 
  # to the same venue so we can dedup there
  unique_events = []
  event_keys = set()
  for e in events:
    key = "%s/%s" % (e['venue'],e['time'])
    if key not in event_keys:
      event_keys.add(key)
      unique_events.append(e)
    else:
      print "de-duplicated %s" % key.encode("utf-8")

  outfile = sys.argv[2]
  f = open(outfile,'w')
  json.dump(unique_events,f,indent=2)
