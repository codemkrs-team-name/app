import requests
import sys
import json
from csvutils import write_csv

URL = 'http://ws.audioscrobbler.com/2.0/'
API_KEY = '7512b2cc7b85cbe297da13db43a40a4a'

def artist_info(artist_name):
  resp = requests.get(URL,params=dict(method='artist.getinfo',artist=artist_name,api_key=API_KEY,format='json'))
  result = resp.json()
  artist = result.get('artist')
  if artist:
    result = dict(name=artist['name'],mbid=artist['mbid'],url=artist['url'])
    result['image_small'] = artist['image'][0]['#text']
    result['image_medium'] = artist['image'][1]['#text']
    result['image_large'] = artist['image'][2]['#text']
    if 'bio' in artist:
      bio = artist['bio']
      result['bio_summary'] = bio['summary']
      result['bio_content'] = bio['content']
    if type(artist.get('tags')) == dict:
      # data is coming back funny
      tags = artist.get('tags')
      tagfield = tags.get('tag')
      if type(tagfield) == list:
        tags = [tag['name'] for tag in tagfield]
      elif type(tagfield) == dict:
        tags = list(tagfield['name'])
      result['tags'] = ','.join(tags)
    return result
  else:
    return None

if __name__ == '__main__':
  all_info = []
  infile = open('artists.txt','r')
  for name in infile:
    name = name.strip()
    print name
    info = artist_info(name)
    if info:
      all_info.append(info)
  json.dump(all_info,open('artist_info.json','w'))


