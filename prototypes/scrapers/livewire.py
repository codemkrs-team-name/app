from bs4 import BeautifulSoup
import codecs
import urllib
import datetime
import ftplib
import re
import cgi

url = urllib.urlopen("http://www.wwoz.org/new-orleans-community/music-calendar")
_Source = "WWOZ Livewire"
soup = BeautifulSoup(url)
# soup = BeautifulSoup(open('livewire.html'))

list=[]
list=soup.div.find_all(class_='music-event')
months_CAP = {'JAN': '01','FEB': '02','MAR': '03','APR': '04','MAY': '05','JUN': '06','JUL': '07','AUG': '08','SEP': '09','OCT': '10','NOV': '11','DEC': '12',}
acts=[]
places=[]
records=[]
for items in list:
	event = items.findAll(class_='event-name')
	eventname = event[0].a.get_text()
	eventname = cgi.escape(eventname, True)
	if items.findAll(class_='venue-name'):
		venue = items.findAll(class_='venue-name')
		place = venue[0].a.get_text()
	for span in items.findAll(class_='cal-date'):
		if span.find(class_='month', text=True):
			date_month=span.find(class_='month').get_text(strip=True)
			if date_month in months_CAP:
				startmonth=months_CAP[date_month]
			else:
				print('not in dict')
			startday=span.find(class_='day', text=True).get_text(strip=True)
		else:
			print('No Month')
		year='2013'
	startdate = str(year) + '-' + startmonth + '-' + startday
#	d=datetime.date(year,startmonth,startday)
	for get_time in items.findAll('div', class_='event-details first'):
		time_a = get_time.find('div', class_='full-date').get_text(strip=True).lstrip('\n\t')
		starttime = time_a[time_a.find(' at ')+3:]
#		t=datetime.time(starttime)
		starttime=starttime.lstrip()
	enddate=startdate
	
#	when1=startdate
	from datetime import datetime
#	when = datetime.strptime(when1, '%Y %m %d')
#	when=startdate+startime
	category = 'Music'
	listing = eventname, place, startdate, enddate
	rating='3'
	act= "%s|%s" % (eventname, rating)
	acts.append(act)
	place3="%s" % (place)
	places.append(place3)
	record = "%s|%s|%s|%s|%s|%s|%s" % (eventname,place,category,startdate,starttime,enddate,_Source)
	records.append(record)
print('Got Livewire')


livewire = codecs.open('big_list.txt', 'w', 'utf8')
line = '\r\n'.join(records)
livewire.write(line)
livewire.close
print('wrote livewire')

# simple ftp upload
sftp = ftplib.FTP('ftp.moblnola.com','fsulliva1@moblnola.com','Franks!') # Connect
fp = open('big_list.txt','rb') # file to send
sftp.storbinary('STOR big_list.txt', fp) # Send the file
fp.close() # Close file and FTP
sftp.quit()
print('uploaded list')

acts2 = codecs.open('acts.txt', 'w', 'utf8')
actsline = '\r\n'.join(acts)
acts2.write(actsline)
acts2.close
print('wrote acts')

# simple ftp upload acts
sftp2 = ftplib.FTP('ftp.moblnola.com','fsulliva1@moblnola.com','Franks!') # Connect
fp2 = open('acts.txt','rb') # file to send
sftp2.storbinary('STOR acts.txt', fp2) # Send the file
fp2.close() # Close file and FTP
sftp2.quit()
print('uploaded acts')

places2 = codecs.open('places.txt', 'w', 'utf8')
placeline = '\r\n'.join(places)
places2.write(placeline)
places2.close
print('wrote places')

# simple ftp upload place
sftp3 = ftplib.FTP('ftp.moblnola.com','fsulliva1@moblnola.com','Franks!') # Connect
fp3 = open('places.txt','rb') # file to send
sftp3.storbinary('STOR places2.txt', fp3) # Send the file
fp3.close() # Close file and FTP
sftp3.quit()
print('uploaded places')
