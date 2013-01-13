# -*- coding: utf-8 -*-
import json
import sys
import re
from fuzzywuzzy import fuzz
from fuzzywuzzy import process
import HTMLParser
import codecs
import csv
import StringIO
from csvkit.unicsv import UnicodeCSVDictReader,UnicodeCSVWriter
import requests

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
  return indexed_rows
  
def single_artist_for_name(name):
  global artists_index
  name = canonical_artist(name)
  # look for exact match
  artist = artists_index.get(name)
  if artist:
    return artist
  hit,score = process.extractOne(name,artists_index.keys())
  # 90 is excellent
  # 80 is good
  # lets be optimisitic
  if score >= 80:
    return artists_index[hit]
  return None

def artists_for_name(name):
  global artists_index
  # try for a direct match
  single = single_artist_for_name(name)
  if single:
    return [single]
  # look for joined acts and try
  for sep in ['\+','featuring','feat.','&','and']:
    pattern = r'\s+'+sep+r'\s+'
    matches = []
    parts = re.split(pattern,name)
    if len(parts) > 1:
      for part in parts:
        m = single_artist_for_name(part)
        if m:
          matches.append(m)
    if len(matches):
      return matches
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
  if venue['street-address']:
    evt['address'] = ('''
%(street-address)s<br>
%(locality)s, %(region)s %(postal-code)s
        ''' % venue).strip()
  if venue.get('price'):
    evt['price'] = venue['price']
  if venue.get('phone'):
    evt['phone'] = re.sub(r'[^\d]','',venue.get('phone'))
  if venue.get('venuepage_href'):
    add_event_link(evt,'venue',venue['venuepage_label'],venue['venuepage_href'])
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
  if len(desc):
    evt['description'] = "".join(desc)
  if len(tags):
    evt['tags'] = ','.join(tags)


if __name__ == '__main__':
  artists = read_csv(ARTIST_URL)
  artists_index = index(artists,canonical_artist)
  print "%d artists" % len(artists)

  venues = read_csv(VENUE_URL)
  venues_index = index(venues,canonical_venue)
  print "%d venues" % len(venues)

  events = json.load(sys.stdin)
  html_parser = HTMLParser.HTMLParser()

  for evt in events:
    name = html_parser.unescape(evt['eventName']).strip()
    evt['eventName'] = name
    evt['ranking'] = 0
    venue_name = html_parser.unescape(evt['venue'])
    evt_artists = artists_for_name(name)
    if len(evt_artists):
      update_event_with_artists(evt,evt_artists)
    else:
      print "Missing artist: %s" % name
    venue = venue_for_name(venue_name)
    if venue:
      update_event_with_venue(evt,venue)
    else:
      print "No Venue: %s" % venue_name
    #print evt

if __name__ == '__main__':
  f = open('target/events-final.json','w')
  json.dump(events,f,indent=2)
