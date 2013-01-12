from bs4 import BeautifulSoup
import re
import codecs
import requests
import parsedatetime.parsedatetime as pdt 
import parsedatetime.parsedatetime_consts as pdc
import json
from geopy import geocoders
import time
from csvutils import write_csv

# create an instance of Constants class so we can override some of the defaults

c = pdc.Constants()

# create an instance of the Calendar class and pass in our Constants # object instead of letting it create a default

p = pdt.Calendar(c)

MONTHS = ['JAN','FEB','MAR','JUN','JUL','AUG','SEP','OCT','NOV','DEC']

def parse_venue_el(venue_el):
  name = venue_el.find('a').text
  href = venue_el.find('a')['href']
  return dict(name=name,url=href)

def month_index(month_name):
  return MONTHS.index(month_name)+1

def parse_calendar(day=None):
  url = 'http://www.wwoz.org/new-orleans-community/music-calendar'
  params = {}
  if day:
    params['start_date'] = day
  resp = requests.get(url,params=params)
  html = BeautifulSoup(resp.text)
  events = []
  for el in html.find_all('div',{'class':'music-event'}):
    venue_name = el.find('div',{'class':'venue-name'})
    if venue_name:
      current_venue = parse_venue_el(venue_name)
    cal_date = el.find('div',{'class':'cal-date'})
    if cal_date:
      month = cal_date.find('span',{'class':'month'}).text
      day = cal_date.find('span',{'class':'day'}).text

    full_date =  el.find('div',{'class':'full-date'}).text
    event_name = el.find('div',{'class':'event-name'}).text
    full_date = re.sub(r'\s+',' ',full_date.strip()) 
    date,idunno = p.parse(full_date)
    event = dict(name=event_name,month=month_index(month),day=day,
        venue=current_venue['name'],
        venueid=current_venue['url'].rsplit('/',2)[-1],
        venue_url='http://www.wwoz.org'+current_venue['url'],
        date="%d-%02d-%02d %02d:%02d" % (date[0],date[1],date[2],date[3],date[4]))
    events.append(event)
  return events

def write_events(events):
  write_csv('events.csv',events,'name','venue','venue_url','date')

def parse_venue(venueid):
  url = 'http://www.wwoz.org/new-orleans-community/music-venues/'+venueid
  resp = requests.get(url)
  html = BeautifulSoup(resp.text)
  location = html.find('div',{'class':'location'})
  result = dict(url=url,name=html.find('h1',{'class':'title'}).text)
  for field in ['street-address','locality','region','postal-code']:
    e = location.find('span',{'class':field})
    if e:
      result[field] = e.text
    else:
      e = location.find('div',{'class':field})
      if e:
        result[field] = e.text
  contact_info = html.find('div',{'class':'contact-info'})
  if contact_info:
    for fi in contact_info.find_all('p',{'class':'field-item'}):
      a = fi.find('a')
      if a:
        if a['href'].startswith("mailto:"):
          result['mail'] = a['href'][7:]
        else:
          if 'links' not in result:
            result['links'] = []
          result['links'].append(dict(href=a['href'],label=a.text))
      else:
        if re.match(r'\(\d+\)\s+\d{3}-\d{4}',fi.text):
          result['phone'] = fi.text
  return result

ggeocoder = None

def geocode(location):
  global ggeocoder
  if ggeocoder is None:
    ggeocoder = geocoders.Google()
  try:
    time.sleep(1)
    return  ggeocoder.geocode(location)
  except Exception as e:
    print "error for %s, %s" % (location,e)
  return None


if __name__ == '__old__':
  soup = BeautifulSoup(open("livewire.html"))
  events = sorted(parse_calendar(soup),key=lambda x:x['name'])
  write_events(events)

if __name__ == '__main__':
  soup = BeautifulSoup(open("livewire.html"))
  events = sorted(parse_calendar(soup),key=lambda x:x['name'])
  venues = []
  for evt in events:
    url = evt['venue_url']
    r = requests.get(url.strip())
    venue = parse_venue_html(BeautifulSoup(r.text))
    venue['geo'] = geocode("%(street-address)s, %(locality)s, %(region)s" % venue)
    venues.append(venue)
  outfile = open('venues.json','w')
  json.dump(dict(venues=venues), outfile,sort_keys=True, indent=4)
