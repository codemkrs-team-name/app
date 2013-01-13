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

def canonical(string):
  string = re.sub(r'[^\w\s]','',string)
  string = re.sub(r'\s+',' ',string.strip().lower())
  return string

def index(rows):
  indexed_rows = {}
  for r in rows:
    indexed_rows[canonical(r['name'])] = r
  return indexed_rows
  
def artists_for_name(name):
  global artists_index
  name = canonical(name)
  # look for exact match
  artist = artists_index.get(name)
  if artist:
    return [artist]
  # no match do some substring matching
  artists = []
  for artist_name,artist in artists_index.items():
    if name.find(artist_name) != -1:
      artists.append(artist)
  # TODO: split on comma (,) 
  # and/& 
  # feat. featuring
  # +
  return artists


def venue_for_name(name):
  global venues_index
  name = canonical(name)
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

if __name__ == '__main__':
  artists = read_csv(ARTIST_URL)
  artists_index = index(artists)
  print "%d artists" % len(artists)

  venues = read_csv(VENUE_URL)
  venues_index = index(venues)
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
      evt['ranking'] = max([int(a['rating']) for a in evt_artists])
    desc = []
    tags = []
    for artist in evt_artists:
      if artist.get('summary'):
        desc.append(artist['summary'])
      image = artist.get('image')
      if image and not evt['image']:
        evt['image'] = dict(src=image,href=artist['image_attrib'])
      artist_tags = artist.get('tags')
      if artist_tags:
        tags.append(artist_tags)
      artist_url = artist.get('url')
      if artist_url:
        evt['links'].append(dict(text=artist['name'],link=artist_url,type='artist'))
    if len(desc):
      evt['description'] = "".join(["<p>%s</p>" % d for d in desc])
    if len(tags):
      evt['tags'] = ','.join(tags)
    venue = venue_for_name(venue_name)
    if venue:
      if venue['street-address']:
        evt['address'] = ('''
%(street-address)s<br>
%(locality)s, %(region)s %(postal-code)s
        ''' % venue).strip()
      if venue.get('phone'):
        evt['phone'] = re.sub(r'[^\d]','',venue.get('phone'))
      if venue.get('venuepage_href'):
        evt['links'].append(dict(text=venue['venuepage_label'],link=venue['venuepage_href'],type='venue'))
      if venue['lat'] and venue['lon']:
        evt['location'] = dict(lat=float(venue['lat']),
                               lon=float(venue['lon']))
    else:
      print "No Venue: %s" % venue_name
    #print evt

f = open('target/events-final.json','w')
json.dump(events,f,indent=2)
