This scrapes lots of data from various websites and creates a JSON file that
goes somewhere 

# Installation

To install you will need

- node
- python 
	- virtualenv
	
Then do this
	
	virtualenv scrapers
	cd scrapers
	source bin/activate
	pip install stdeb
	pip install -r requirements.txt
	mkdir target

# To run scraper:

cd scrapers/
./scrape.sh

file is in `target/events-final.json`
