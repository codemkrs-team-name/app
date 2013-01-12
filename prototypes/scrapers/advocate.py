from bs4 import BeautifulSoup
import codecs
import urllib
import datetime
import ftplib
import re

url = urllib.urlopen("http://calendar.theadvocate.com/search?st=event&swhen=Today")
soup = BeautifulSoup(url)
# soup = BeautifulSoup(open('advocate.html'))
table = soup.find_all("table", class_="search_result_table")

# get the list of events
acts=[]
places=[]
records = []
for row in table:
	col=row.find('td', class_='title_content')
	eventname=col.a.string
	col2=row.find_all('div')[1:]
	date = col2[0].get_text(strip=True)
	startdate=date[0:9]
	startdate=datetime.date.today()
	tomorrow=datetime.timedelta(days=1)
	enddate=startdate
	time1=date[10:-17]
	starttime=time1[time1.find('y')+1:time1.find('to')] #from here to the end of starttime
	starttime=starttime.lstrip().rstrip()
	place = col2[1].get_text(strip=True)
	category = 'Events'
	_Source = 'The Advocate'
	rating='3'
	act= "%s|%s" % (eventname, rating)
	acts.append(act)
	place3="%s" % (place)
	places.append(place3)
	record = "%s|%s|%s|%s|%s|%s|%s" % (eventname,place,category,startdate,starttime,enddate,_Source)
	records.append(record)
print('processed advocate')

# write the list to a text file
# advocate = urllib.urlopen("advocate_list.txt", "w","utf8")
advocate = codecs.open('big_list.txt', 'a', 'utf8')
line = '\r\n'.join(records)
advocate.write(line)
advocate.close
print('wrote advocate')

# simple ftp upload
sftp = ftplib.FTP('ftp.moblnola.com','fsulliva1@moblnola.com','Franks!') # Connect
fp = open('big_list.txt','rb') # file to send
sftp.storbinary('STOR big_list.txt', fp) # Send the file
fp.close() # Close file and FTP
sftp.quit()
print('uploaded advocate')

acts2 = codecs.open('acts.txt', 'a', 'utf8')
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

places2 = codecs.open('places.txt', 'a', 'utf8')
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

# run the php script on the server that puts the contents of the file into the database
# urllib.urlopen("http://moblnola.com/loadIt.php"). This doesn't seem to work.