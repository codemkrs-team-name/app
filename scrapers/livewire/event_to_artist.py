import sys
import re

# csvcut -c 1 livewire_events.csv | python event_to_artist.py | sort | uniq > artists.txt

if __name__ == '__main__':
  for line in sys.stdin:
    line = line.strip()
    parts = re.split(r'\s+\+\s+',line)
    for part in parts:
      if part == parts[-1] and re.search(r'^more',part):
        # exclude + more...
        continue
      print part
