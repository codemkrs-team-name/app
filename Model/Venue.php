<?php
class Venue extends AppModel {

	public $name = 'Venue';
	
	public $belogsTo = array('Event');
	public $hasMany = array('LinksVenue');
	public $hasAndBelongsToMany = array('Link');
	
	function importCsv()
	{
		$lines = file('files/livewire_venues.csv');
		
		//debug($lines);
		
		$counter = 1;	
		
		foreach ($lines as $key => $line)
		{
			if (($key > 0) && (!empty($line)))
			{
				$fields = explode(",", $line);
				
				if (count($fields) == 12)
				{
					$venue = array();
					$venue['name'] = $fields[0];
					
					$links = array();
					$links[0]['label'] = 'wwoz venue link';
					$links[0]['href'] = $fields[1];
					if ((!empty($fields[2])) && (!empty($fields[3])))
					{
						$links[1]['label'] = $fields[2];
						$links[1]['href'] = $fields[3];
					}			
					if (!empty($fields[4])) $venue['street_address'] = $fields[4];
					if (!empty($fields[5])) $venue['city'] = $fields[5];
					if (!empty($fields[6])) $venue['state'] = $fields[6];
					if (!empty($fields[7]))	$venue['phone'] = $fields[7];
					if (!empty($fields[8]))	$venue['email'] = $fields[8];
					if (!empty($fields[9]))	$venue['postal_code'] = $fields[9];
					if (!empty($fields[10])) $venue['latitude'] = $fields[10];
					if (!empty($fields[11])) $venue['longitude'] = $fields[11];

				}
				
				$this->create();
				$this->save($venue);
				
				$venueId = $this->getLastInsertId();
				
				foreach ($links as $link)
				{
					$existingLink = $this->Link->find("all", array("conditions" => $link));
					
					if (empty($existingLink))
					{
						$this->Link->insert($link);				
						$linkId = $this->Link->getLastInsertId();
					}
					else
					{
						//debug($existingLink);
						$linkId = $existingLink[0]['Link']['id'];	
					}
					
					//$query = 
					
					//$this->query("INSERT INTO links_venues ('id', 'venue_id', 'link_id') VALUES (NULL, '" . $venueId . "', '" . $linkId . "')");
					
					$joinData = array('venue_id' => $venueId, 'link_id' => $linkId);	
					//$linksVenue = $this->LinksVenue->find("all", array("conditions" => $joinData));
					
					//if (empty($linksVenue))	
					$this->LinksVenue->insert($joinData);
						
					/*
					try {
						
						
						
						$query = "INSERT INTO links_venues (
`id` ,
`link_id` ,
`venue_id`
)
VALUES (
NULL ,  '" . $venueId . "',  '" . $linkId . "'
);";						

						$this->query($query);

					} catch (Exception $e) {
						
					}*/

					

					
				}
				
				debug($venue);
				debug($links);
				
			}
		}
		
	
		
	}


	function importJson()
	{
		$json = json_decode(file_get_contents('files/venues_geo.json'));
		//debug($records);
		foreach ($json->venues as $record)
		{
			//debug($record);
		
			$data['Venue'] = array();
			$data['Venue']['name'] = $record->name;
			if (!empty($record->mail))	$data['Venue']['email'] = $record->mail;
			if (!empty($record->phone))	$data['Venue']['phone'] = $record->phone;
			$data['Venue']['street_address'] = $record->{'street-address'};
			$data['Venue']['city'] = $record->locality;
			$data['Venue']['state'] = $record->region;
			if (!empty($record->{'postal-code'})) $data['Venue']['postal_code'] = $record->{'postal-code'};
			if (!empty($record->geo))
			{
				$data['Venue']['latitude'] = $record->geo[1][0];
				$data['Venue']['longitude'] = $record->geo[1][1];
			}
		
			$this->create();
			$this->save($data['Venue']);
				
				
			/*
			if (!empty($record->links))
			{
				$data['Link'] = array();
				foreach ($record->links as $recordLink)
				{
					$link = array();	
					if (!empty($recordLink->label))	$link['label'] = $recordLink->label;
					$link['href'] = $recordLink->href;
					
					$this->Link->create();
					$this->Link->save($link);
					
					//$this->query("INSERT INTO links_venues ('venue_id', 'link_id') VALUES ('" . $this->getLastInsertId(). "', '" . $this->Link->getLastInsertId() . "')";	
						
				}
			}
			*/

			
			
			debug($data);
			
		}
	}
	
	
	
	

}
	
	