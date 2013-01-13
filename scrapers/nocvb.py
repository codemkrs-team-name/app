from bs4 import BeautifulSoup
import codecs
import urllib
import datetime
import ftplib

url = urllib.urlopen("http://www.neworleanscvb.com/calendar-events/?e_ViewBy=day&e_sDate=09-12-2012&e_sortBy=eventDate#events")
soup = BeautifulSoup(url)
# soup = BeautifulSoup(open('nocvb.html'))
_Source='New Orleans CVB'
acts=[]
places=[]
events=[]
events = soup.find_all(class_="l-info")
months_full = {'January': '01','February': '02','March': '03','April': '04','May': '05','June': '06','July': '07','August': '08','September': '09','October': '10','November': '11','December': '12',}
records = []
for items in events:
	event = items.findAll('h4')
	eventname = event[0].a.get_text()
#	get_dates = items.findAll('h5')
#	run_dates = get_dates[0].get_text(strip=True)
#	first_space = run_dates.find(' ')
#	month = run_dates[0:(first_space)]
#	print month
#	connector = run_dates.find(' -',first_space)
#	first_date = run_dates[(first_space)+1:(connector)]
#	print first_date
	for ul in items.findAll('ul'):
		venue = ul.li.get_text()
		place = venue[10:]
		place = place.strip()
	startdate=datetime.date.today()
	starttime='Call Venue'
	enddate=startdate
	category = 'Events'
	rating='3'
	act= "%s|%s" % (eventname, rating)
	acts.append(act)
	place3="%s" % (place)
	places.append(place3)
	record = "%s|%s|%s|%s|%s|%s|%s" % (eventname,place,category,startdate,starttime,enddate,_Source)
	records.append(record)
print 'Got NOCVB'

# write the list to a text file
nocvb = codecs.open('nocvb.txt', 'a', 'utf8')
line = '\r\n'.join(records)
nocvb.write(line)
nocvb.close
print('Wrote NOCVB')

# simple ftp upload
# sftp = ftplib.FTP('ftp.moblnola.com','fsulliva1@moblnola.com','Franks!') # Connect
# fp = open('big_list.txt','rb') # file to send
# sftp.storbinary('STOR big_list.txt', fp) # Send the file
# fp.close() # Close file and FTP
# sftp.quit()
# print('Uploaded NOCVB')

acts2 = codecs.open('nocvb_acts.txt', 'a', 'utf8')
actsline = '\r\n'.join(acts)
acts2.write(actsline)
acts2.close
print('wrote acts')

# simple ftp upload acts
# sftp2 = ftplib.FTP('ftp.moblnola.com','fsulliva1@moblnola.com','Franks!') # Connect
# fp2 = open('acts.txt','rb') # file to send
# sftp2.storbinary('STOR acts.txt', fp2) # Send the file
# fp2.close() # Close file and FTP
# sftp2.quit()
# print('uploaded acts')

places2 = codecs.open('nocvb_places.txt', 'a', 'utf8')
placeline = '\r\n'.join(places)
places2.write(placeline)
places2.close
print('wrote nocvb places')

# simple ftp upload place
# sftp3 = ftplib.FTP('ftp.moblnola.com','fsulliva1@moblnola.com','Franks!') # Connect
# fp3 = open('places.txt','rb') # file to send
# sftp3.storbinary('STOR places2.txt', fp3) # Send the file
# fp3.close() # Close file and FTP
# sftp3.quit()
# print('uploaded places')
