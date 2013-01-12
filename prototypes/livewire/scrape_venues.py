import sys
import livewire
from csvutils import write_csv

# csvcut -c 3 livewire_events.csv | sort | uniq > livewire_venueid.txt
# cat livewire_venueid.txt | xargs python scrape_venues.py 

if __name__ == '__main__':
  venues = []
  for venueid in sys.argv[1:]:
    print venueid
    venue = livewire.parse_venue(venueid)
    if 'links' in venue:
      venue['venuepage_label'] = venue['links'][0]['label']
      venue['venuepage_href'] = venue['links'][0]['href']
    else:
      venue['venuepage_label'] = ''
      venue['venuepage_href'] = ''
    venues.append(venue)
  write_csv('livewire_venues.csv',venues,'name','url','venuepage_label','venuepage_href','street-address','locality','region','phone','mail','postal-code')
