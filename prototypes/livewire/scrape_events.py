import livewire
from bs4 import BeautifulSoup
from csvutils import write_csv
import requests

def write_events(events):
  write_csv('events.csv',events,'name','venue','venue_url','date')

if __name__ == '__main__':
  events = sorted(livewire.parse_calendar(),key=lambda x:x['name'])
  write_events(events)
