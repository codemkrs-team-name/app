import requests
import sys

URL = 'http://api.flickr.com/services/rest/'
API_KEY = 'eef9f0ada83f418ad9410ada8bcd517b'

SEARCHES = [
    dict(user_id='31081764@N06'), # WWOZ
    dict(user_id='39554757@N07'), # Offbeat
    dict(group_id='361357@N23'), # WWOZ Group
    dict(tags='neworleans') 
    ]

def find_flickr_image(text):
  for search in SEARCHES:
    params = dict(method='flickr.photos.search',format='json',nojsoncallback='1',api_key=API_KEY,sort='relevance',per_page='1',extras='url_sq')
    params.update(search)
    params['text'] = text
    resp = requests.get(URL,params=params)
    result = resp.json()
    for photo in result['photos']['photo']:
      url = 'http://www.flickr.com/photos/%(owner)s/%(id)s' % photo
      return url,photo['url_sq']

if __name__ == '__main__':
  print find_flickr_image(sys.argv[1])
