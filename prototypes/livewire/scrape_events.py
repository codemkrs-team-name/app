import livewire
from bs4 import BeautifulSoup
from csvutils import write_csv
import requests
from datetime import date,timedelta
import json

def write_events(events):
  items = []
  for e in events:
    item = dict(eventName=e['name'],
        venue=e['venue'],
        time=e['date'],
        image=None,
        price=None,
        description=None,
        links=[])
    items.append(item)
  print json.dumps(items,indent=2)

if __name__ == '__main__':
  today = date.today()
  events = []
  for n in xrange(0,3):
    day = today + timedelta(n)
    events += sorted(livewire.parse_calendar(day),key=lambda x:x['name'])
  write_events(events)
