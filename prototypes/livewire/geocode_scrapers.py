from geopy import geocoders
import json
import time

venues = json.load(open('venues.json','r'))
g = geocoders.Google()
for v in venues['venues']:
  addr = v['street-address']
  city = v['locality']
  region = v['region']
  try:
    location = ','.join([addr,city,region])
    if not v.get('geo'):
      time.sleep(1)
      v['geo'] = g.geocode(location)
  except:
    print "error for %@",addr


print json.dumps(venues, sort_keys=True, indent=4)
