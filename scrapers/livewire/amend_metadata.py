import csvutils
import json
import sys
import re

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
  return artists

def venue_for_name(name):
  global venues_index
  name = canonical(name)
  return venues_index.get(name)

if __name__ == '__main__':
  artists = csvutils.read_csv('artists.csv')
  artists_index = index(artists)
  venues = csvutils.read_csv('venues.csv')
  venues_index = index(venues)

  events = json.load(sys.stdin)

  for evt in events:
    name = evt['eventName']
    # TODO: get ranking in here
    evt['ranking'] = 0
    venue_name = evt['venue']
    evt_artists = artists_for_name(name)
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
    #print evt

f = open('events-final.json','w')
json.dump(events,f,indent=2)
