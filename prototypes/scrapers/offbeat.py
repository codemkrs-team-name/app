from bs4 import BeautifulSoup
import codecs
import urllib
import datetime
import ftplib

url = urllib.urlopen("http://www.offbeat.com/new-orleans-concert-listings/")
soup = BeautifulSoup(url)
table = soup.find("table",{"class": "calendar events-calendar-list clubs-events tablesorter"})
print table

# get the list of events
records = []
# for row in table.findAll("tr")[1:]:
#	col=row.findAll('td')
#	date = col[0].p.string # need to figure out how to make this date format mysql-friendly
#	startdate = datetime.date.today()
#	place = col[1].a.string
#	eventname = col[2].string.lstrip("\r\n").strip()
#	starttime = col[6].string.lstrip("\r\n").strip()
#	category = 'Music'
#	enddate = datetime.date.today() 
#	record = "%s|%s|%s|%s|%s|%s" % (eventname,place,category,startdate,starttime,enddate)
#	records.append(record)

# write the list to a text file
# offbeat = urllib.urlopen("http://moblnola.com/offbeat_list.txt", "w","utf8")
# offbeat = codecs.open('offbeat_list.txt', 'w', 'utf8')
# line = '\r\n'.join(records)
# offbeat.write(line)
# offbeat.close
# print('wrote file')

# simple ftp upload
# fp = open('offbeat_list.txt','rb') # file to send
# sftp.storbinary('STOR offbeat_list.txt', fp) # Send the file
# fp.close() # Close file and FTP
# sftp.quit()

# run the php script on the server that puts the contents of the file into the database
# urllib.urlopen("http://moblnola.com/loadIt.php"). This doesn't seem to work.