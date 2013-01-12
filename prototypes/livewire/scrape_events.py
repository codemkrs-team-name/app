import livewire
from bs4 import BeautifulSoup
from csvutils import write_csv
import requests
from datetime import date,timedelta

def write_events(events):
  write_csv('livewire_events.csv',events,'name','venue','venueid','venue_url','venueid','date')

if __name__ == '__main__':
  today = date.today()
  events = []
  for n in xrange(0,45):
    day = today + timedelta(n)
    print day
    events += sorted(livewire.parse_calendar(day),key=lambda x:x['name'])
  write_events(events)
