from bs4 import BeautifulSoup
import codecs
import urllib
import datetime
import ftplib
import re

# url = urllib.urlopen("http://calendar.theadvocate.com/search?st=event&swhen=Today")
# soup = BeautifulSoup(url)
soup = BeautifulSoup(open('mynola.html'))
table=[]
table = soup.find_all("table", id="calendar-listings")
# print table
months = {'Jan': '01','Feb': '02','Mar': '03','Apr': '04','May': '05','Jun': '06','Jul': '07','Aug': '08','Sep': '09','Oct': '10','Nov': '11','Dec': '12',}

# get the list of events
records = []
for tr in table:
	col=tr.findAll('td', class_='eventlisting')
	for item in col:
		eventname=item.a.string
		col2=item.findAll('h4')
		date = col2[0].get_text(strip=True)
		month=date[0:3]
		if month in months:
			startmonth=months[month]
		startday=date[4:6]
		startyear=date[7:12]
		startdate=startyear + '-' + startmonth + '-' + startday
		enddate=date[15:27]
		month2=date[15:18]
		if month2 in months:
			endmonth=months[month2]
		endday=date[19:21]
		endyear=date[23:27]
		enddate=endyear + '-' + endmonth + '-' + endday
		print 'End: '
		print(enddate)
#		category = 'Events'
#		record = "%s|%s|%s|%s" % (eventname,category,startdate,enddate)
#		records.append(record)
print(records)


# print('processed list')

# write the list to a text file
# mynola = urllib.urlopen("mynola_list.txt", "w","utf8")
# mynola = codecs.open('mynola_list.txt', 'w', 'utf8')
# line = '\r\n'.join(records)
# mynola.write(line)
# mynola.close
# print('wrote file')

# simple ftp upload
# sftp = ftplib.FTP('ftp.moblnola.com','fsulliva1@moblnola.com','Franks!') # Connect
# fp = open('mynola_list.txt','rb') # file to send
# sftp.storbinary('STOR mynola_list.txt', fp) # Send the file
# fp.close() # Close file and FTP
# sftp.quit()
# print('uploaded file')

# run the php script on the server that puts the contents of the file into the database
# urllib.urlopen("http://moblnola.com/loadIt.php"). This doesn't seem to work.