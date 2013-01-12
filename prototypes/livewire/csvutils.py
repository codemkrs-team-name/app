import codecs
import csv
import cStringIO
from csvkit.unicsv import UnicodeCSVDictReader,UnicodeCSVWriter


def read_csv(filename):
  f = open(filename,'r')
  reader = UnicodeCSVDictReader(f)
  return list(reader)

def write_csv(filename,data,*headers):
  f = open(filename,'w')
  writer = UnicodeCSVWriter(f)
  writer.writerow(headers)
  for datum in data:
    row = []
    for h in headers:
      row.append(datum.get(h,''))
    writer.writerow(row)
