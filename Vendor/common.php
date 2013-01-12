<?php
//common library of functions to be included everywhere


function trimAndStripTagsFromArray($data, $allowableTags = null)
{
	if (!is_array($data))
	{
		if(is_string($data))	
			return trim(strip_tags($data, $allowableTags));	
		else
			return $data;
	}

    foreach ($data as $key => $value)
    {
        if (is_array($value))
        {
            $data[$key] = trimAndStripTagsFromArray($value, $allowableTags);
        }
        else if (is_string($value))
        {
            $data[$key] = trim(strip_tags($value, $allowableTags));
        } 
    }
    return $data;
}

function googleSpellCheck($str, $language = 'en') {
		$url = 'https://www.google.com';
		$path = '/tbproxy/spell?lang='.$language.'&hl=en';

		// setup XML request
		$xml = '<?xml version="1.0" encoding="utf-8" ?>';
		$xml .= '<spellrequest textalreadyclipped="0" ignoredups="0" ignoredigits="1" ignoreallcaps="1">';
		$xml .= '<text>'.$str.'</text></spellrequest>';

		// setup headers to be sent
		$header  = "POST {$path} HTTP/1.0 \r\n";
		$header .= "MIME-Version: 1.0 \r\n";
		$header .= "Content-type: text/xml; charset=utf-8 \r\n";
		$header .= "Content-length: ".strlen($xml)." \r\n";
		$header .= "Request-number: 1 \r\n";
		$header .= "Document-type: Request \r\n";
		$header .= "Connection: close \r\n\r\n";
		$header .= $xml;

		// response data
		$xml_response = '';

		// use curl if it exists
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $header);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$xml_response = curl_exec($ch);
			curl_close($ch);
		} else {
			exit;
		}

		// grab and parse content, remove google XML formatting
		$matches = array();
		preg_match_all('/<c o="([^"]*)" l="([^"]*)" s="([^"]*)">([^<]*)<\/c>/', $xml_response, $matches, PREG_SET_ORDER);

		// note: google will return encoded data, no need to encode ut8 characters

		
		
		if (is_array($matches)){
			$revisedMatches = array();
			foreach($matches as $match){
				if (isset($match[1]) && isset($match[2]) && isset($match[4])){
					$matchBits = explode("\t", $match[4]);
					if (is_array($matchBits)){
						foreach ($matchBits as $key => $bit){
							$matchBits[$key] = strtolower($bit);	
						}
						//match[1] = start of misspelled word, match[2] = length of misspelled worg											
						$revisedMatch['submission'] = substr($str, $match[1], $match[2]);
						$revisedMatch['start'] = $match[1];
						$revisedMatch['length'] = $match[2];
						$revisedMatch['confidence'] = $match[3];
						$revisedMatch['suggestions'] = $matchBits;
						$revisedMatches[] = $revisedMatch;
					}
				}
			}	
			
			return $revisedMatches;
		} else
			return false;
			
}//end function 

function urlExists($url){
  $parts=parse_url($url);
  if(!$parts) return false; /* the URL was seriously wrong */
 
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
 
  /* set the user agent - might help, doesn't hurt */
  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
 
  /* try to follow redirects */
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
 
  /* timeout after the specified number of seconds. assuming that this script runs 
    on a server, 20 seconds should be plenty of time to verify a valid URL.  */
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
  curl_setopt($ch, CURLOPT_TIMEOUT, 20);
 
  /* don't download the page, just the header (much faster in this case) */
  curl_setopt($ch, CURLOPT_NOBODY, true);
  curl_setopt($ch, CURLOPT_HEADER, true);
 
  /* handle HTTPS links */
  if($parts['scheme']=='https'){
  	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  1);
  	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  }
 
  $response = curl_exec($ch);
  curl_close($ch);
 
  /*  get the status code from HTTP headers */
  if(preg_match('/HTTP\/1\.\d+\s+(\d+)/', $response, $matches)){
  	$code=intval($matches[1]);
  } else {
  	return false;
  };
 
  /* see if code indicates success */
  return (($code>=200) && ($code<400));	
}

//returns string describing the number of days, hours, and minutes since a given start time
function getElapsedTime($start){

	$seconds =  time() - strtotime($start);		
	$days = 0;
	if ($seconds > (60*60*24))	$days = floor($seconds / (60*60*24));
	$seconds = $seconds - ($days * (60*60*24));		
	$hours = 0;
	if ($seconds > (60*60))	$hours = floor($seconds / (60*60));
	$seconds = $seconds - ($hours * (60*60));		
	$minutes = 0;
	if ($seconds > (60))	$minutes = floor($seconds / (60));
	$seconds = $seconds - ($minutes * (60));	
	$elapsed = $seconds . "s";
	if ($minutes > 0)	$elapsed = $minutes . "m " . $elapsed;
	if ($hours > 0)	$elapsed = $hours . "h " . $elapsed;		
	if ($days > 0)	$elapsed = $days . "d " . $elapsed;
	
	return $elapsed;
	
}//end function

function getIpAddress(){
	
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		if (stristr($ip, ","))	$ip = array_pop(explode(",", $ip));
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];  
    }

    return trim($ip);	
}

final class ip2location_lite{
	protected $errors = array();
	protected $service = 'api.ipinfodb.com';
	protected $version = 'v3';
	protected $apiKey = '86269baeb9e2c5ce8fb613c0c02369b4608ea9078cd541aac4078065088e29d8';

	public function __construct(){}

	public function __destruct(){}

	public function setKey($key){
		if(!empty($key)) $this->apiKey = $key;
	}

	public function getError(){
		return implode("\n", $this->errors);
	}

	public function getCountry($host){
		return $this->getResult($host, 'ip-country');
	}

	public function getCity($host){
		return $this->getResult($host, 'ip-city');
	}

	private function getResult($host, $name){
		$ip = @gethostbyname($host);

		if(preg_match('/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/', $ip)){
			$xml = @file_get_contents('http://' . $this->service . '/' . $this->version . '/' . $name . '/?key=' . $this->apiKey . '&ip=' . $ip . '&format=xml');

			try{
				$response = @new SimpleXMLElement($xml);

				foreach($response as $field=>$value){
					$result[(string)$field] = (string)$value;
				}

				return $result;
			}
			catch(Exception $e){
				$this->errors[] = $e->getMessage();
				return;
			}
		}

		$this->errors[] = '"' . $host . '" is not a valid IP address or hostname.';
		return;
	}
}

function distance($lat1, $lon1, $lat2, $lon2, $unit = "m") {

	$theta = $lon1 - $lon2;
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	$dist = acos($dist);
	$dist = rad2deg($dist);
	$miles = $dist * 60 * 1.1515;
	$unit = strtoupper($unit);

	if ($unit == "K") {
		return round(($miles * 1.609344), 2);
	} else if ($unit == "N") {
		return round(($miles * 0.8684), 2);
	} else {
		return round($miles, 2);
	}
} 


?>