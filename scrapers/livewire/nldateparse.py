import parsedatetime.parsedatetime as pdt 
import parsedatetime.parsedatetime_consts as pdc
import sys

def parse(date_str):
  # create an instance of Constants class so we can override some of the defaults
  c = pdc.Constants()
  # create an instance of the Calendar class and pass in our Constants # object instead of letting it create a default
  p = pdt.Calendar(c)
  # 0 = not parsed at all
  # 1 = parsed as a C{date}
  # 2 = parsed as a C{time}
  # 3 = parsed as a C{datetime}
  date,result = p.parse(date_str)
  if result == 0:
    return None
  return date

if __name__ == '__main__':
  print parse(sys.argv[1])
