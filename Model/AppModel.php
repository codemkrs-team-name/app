<?php
//todo: research correct HABTM data
//todo: put alphabet as global


class AppModel extends Model{

	var $last = array();	
	var $actsAs = array('Containable');
	
	var $recursive = -1;//how deep it fetches (by default, if specified in find, will fetch that far)
	

   	function __construct($id = false, $table = null, $ds = null) {
	
       parent::__construct($id, $table, $ds);
	   
	   App::uses('Sanitize', 'Utility');
	   	   
	   $this->last['year'] = date('Y-m-d H:i:s', strtotime('-1 year'));
	   $this->last['day'] = date('Y-m-d H:i:s', strtotime('-1 day'));
	   $this->last['week'] = date('Y-m-d H:i:s', strtotime('-1 week'));
	 
	}//end constructor


  	function unbindModelAll() 
  	{ 
    	$unbind = array(); 
    	foreach ($this->belongsTo as $model=>$info) 
    	{ 
    	 	$unbind['belongsTo'][] = $model; 
    	} 
    	foreach ($this->hasOne as $model=>$info) 
    	{ 
      		$unbind['hasOne'][] = $model; 
    	} 
    	foreach ($this->hasMany as $model=>$info) 
    	{ 
      		$unbind['hasMany'][] = $model; 
    	} 
    	foreach ($this->hasAndBelongsToMany as $model=>$info) 
    	{ 
      		$unbind['hasAndBelongsToMany'][] = $model; 
    	} 
    	parent::unbindModel($unbind); 
  	} 


	/*
	* Replicates app_controller flash function, so that flash messages can be set in models
	* Add a message to the messages array in the session like this:
	* $this->flash( 'You are logged in.', 'success' );
	* possible types:  alert, success, error
	*/ 
	function flash( $message, $type = 'alert' ){
	
		App::import('Model', 'CakeSession');
		$session = new CakeSession(); 
	
    	$old = CakeSession::read('messages');
    	$old[$type][] = $message;
		CakeSession::write('messages', $old );	
	}	
	
	function timestamp($field){
		
		$value = array_values($field);
      	$value = $value[0];	
		
		if ($value == 'CURRENT_TIMESTAMP')	return true;
		if (strtotime($value))	return true;
		
		return false;
	}

	//inserts and caches
	function insert ($data = null, $validate = true, $fields = null, $cache = false, $stripTags = true)
	{
		//echo "INSERT<br/>\n";
		//debug($data);	
		
		if ($stripTags) $data = trimAndStripTagsFromArray($data);	
		
		$this->id = null;
		$this->create(false); //passing false is necessary, for some reason.  otherwise, save's fail
		$data = $this->save($data, $validate, $fields);	

		//debug($data);
		
		if (empty($data)){		
			if ($data !== false){
				$id = $this->getLastInsertId();
				$data = $this->findById($id);
			}
		}
		
		if (!empty($data)){			
						
			if (isset($data[$this->name])){//key data by model name and add id, if not already keyed
				$data[$this->name]['id'] = $this->id;				
				$revisedData = $data;
			} else {
				$data['id'] = $this->id;		
				$revisedData[$this->name] = $data;				
			}		

			if (($cache)&&(!empty($this->name))){						
				Cache::write($this->name . "_" . $this->id, $revisedData);
			}
			
			//debug($revisedData);
			return $revisedData;
		} else
			return false;
		
		
	}//end function

	//updates and caches
	function update ($dataId, $data = null, $validate = true, $fields = null, $cache = false, $stripTags = true){
	
		//echo "UPDATE";
		//debug($dataId);
		//debug($data);
	
		if (empty($dataId))		return false;

		if ($stripTags) $data = trimAndStripTagsFromArray($data);	

		$this->id = $dataId;
		if (isset($data[$this->name]))	$data[$this->name]['id'] = $this->id;			
		
		$data = $this->save ($data, $validate, $fields);
		if (!empty($data)){	
	
			if (isset($data[$this->name])){//key data by model name and add id, if not already keyed
				$data[$this->name]['id'] = $this->id;				
				$revisedData = $data;
			} else {
				$data['id'] = $this->id;		
				$revisedData[$this->name] = $data;				
			}			

			if (($cache)&&(!empty($this->name))){						
				Cache::write($this->name . "_" . $this->id, $revisedData);
			}
			
			return $revisedData;
		} else
			return false;        	
   	}//end function
	
	//updates and caches
	function updateField($dataId, $fieldName, $fieldValue, $validate = true, $cache = false, $stripTags = true){
	
		if ((empty($dataId))||(empty($fieldName))){		
			return false;
        } else {
			
			if ($stripTags) $fieldValue = strip_tags($fieldValue);	
			
			$this->id = $dataId;		
			$data = $this->saveField($fieldName, $fieldValue);
			
			$dataCache = array();
			if (!empty($data)){	
			
				if (!empty($this->name)){
									
					$dataCache = Cache::read($this->name . "_" . $this->id);
					$dataCache[$this->name][$fieldName] = $this->id;
					$dataCache[$this->name][$fieldName] = $fieldValue;
								
					if ($cache)	Cache::write($this->name . "_" . $this->id, $dataCache);
		
					return $dataCache;
				} else
					return $data;
			} else
				return false;        	
       	}	
	}//end function
	
	//retrieves model and caches
	/*
	no real point to this
	
	
	function getModel($modelId, $cache = false){
	
		if (empty($modelId))	return false;
		if (($model = Cache::read($this->name . "_" . $modelId)) === true) return $model;		

		$model = $this->find("first", array('conditions' => array($this->name . ".id" => $modelId)));		
			
		if (($cache) && (!empty($this->name))){		
		
			if (!isset($model[$this->name]))
				$revisedModel[$this->name] = $model;
			else
				$revisedModel = $model;	
			
			Cache::write($this->name . '_' . $modelId, $revisedModel);			
		} else
			$revisedModel = $model;
		
		return $revisedModel;	
	
	}//end function getModel
	*/
	
	
	function cacheCheck($key){
	
		$model = Cache::read($key);
		if ($model !== false) 
			return true;
		else {		
			//echo "$key <br/>";
			//debug($model);
			return false;
		}
	}//end function
	
	//not yet used
	function createSlug ($string, $id=null) {
		$slug = Inflector::slug ($string,'-');
		$slug = low ($slug);
		$i = 0;
		$params = array ();
		$params ['conditions']= array();
		$params ['conditions'][$this->name.'.slug']= $slug;
		if (!is_null($id)) {
			$params ['conditions']['not'] = array($this->name.'.id'=>$id);
		}
		while (count($this->find ('all',$params))) {
			if (!preg_match ('/-{1}[0-9]+$/', $slug )) {
				$slug .= '-' . ++$i;
			} else {
				$slug = preg_replace ('/[0-9]+$/', ++$i, $slug );
			}
			$params ['conditions'][$this->name . '.slug']= $slug;
		}
		return $slug;
	}
	
	//do I need to sanitize this?
	function paginate($conditions = array(), $fields = array(), $order = array(), $limit = null, $page = 1, $recursive = null, $extra = array()) {
	
		if (isset($extra['paginateQuery'])){
			if (!empty($extra['paginateQuery'])){ 		
				//echo $extra['paginateQuery'];	
			
				$sql = $extra['paginateQuery'];
			
				$limitClause = '';
				if ((!empty($page))&&(!empty($limit))){
					//echo "page = $page limit = $limit";
					$limitClause = " LIMIT " . (($page * $limit) - $limit) . "," . $limit;
				}
			
				$orderClause = '';
				if (!empty($order)){
					foreach ($order as $key=>$value){
						$orderClause .= $key . " " . $value . ",";	
					}				
				}
				if (strlen($orderClause) > 0){
					$orderClause = " ORDER BY " . $orderClause;
					$orderClause = substr($orderClause, 0, strlen($orderClause)-1);
				}
					
				return $this->query($sql . $orderClause . $limitClause);
			}
		}

		//if query isn't passed, get records via find
		$params['conditions'] = $conditions;
		$params['fields'] = $fields;
		$params['order'] = $order;
		$params['limit'] = $limit;
		$params['page'] = $page;
		//$params['recursive'] = $recursive;	
			
		$params = array_merge($params, $extra);
		
		//debug($params);
		return $this->find("all", $params);
	}//end function


	function paginateCount($conditions = null, $recursive = 0, $extra = array()) {

		$this->recursive = $recursive;
		if (isset($extra['paginateQuery'])) if (!empty($extra['paginateQuery'])) $count = count($this->query($extra['paginateQuery']));
		if (!isset($count)){
					
			//if query isn't passed, get records via find
			$params['conditions'] = $conditions;
			$params = array_merge($params, $extra);
			
			//debug($params);
			$count = count($this->find("all", $params));
		}
	
		if ((!isset($extra['max']))||($count < $extra['max']))
			return $count;
		else
			return $extra['max'];	
	}//end function


	//provides autocomplete suggestions
	//source can be 'all', 'google', or 'site'
	//todo: alias functionality (try magnum, p.i / magnum, p.i.)
	function getSuggestions($text, $source = 'all', $limit = 10){
		
		if (strlen($text) < 1) 	return false;
		
		$text = strtolower(Sanitize::clean($text));
		$limit = Sanitize::clean($limit);
		$suggestions = array();
		$counter = 1;

		if (($source == 'all')||($source == 'site')){
			/*
			$query = "SELECT DISTINCT SearchIndex.association_key, SearchIndex.data, (MATCH(SearchIndex.data) AGAINST(\"" . $text . "\" IN  BOOLEAN MODE) ) AS relevance
FROM search_index AS SearchIndex 	
JOIN thoughts AS Thought ON Thought.id = SearchIndex.association_key
WHERE Thought.variant_of IS NULL AND SearchIndex.data SOUNDS LIKE \"" . $text . "\" HAVING relevance > 0.2 ORDER BY relevance DESC";
			*/
			
			$regex = $text;
			$regex = preg_replace("|[[:blank:][:punct:]]+|i", "[[:blank:][:punct:]]+", $regex) . ".*";
			foreach ($this->alphabet as $letter){
				if ($letter != '#'){
					$letter = strtolower($letter);					
					if (substr_count($regex, $letter) > 1){
						$regex = preg_replace("|($letter){2,}+|i", "$1+", $regex); //replace 2 or more with regex for 1+	
					}									 
				}
			}	

			$query = "SELECT Thought.id, Thought.name, ABS(LENGTH(Thought.name) - LENGTH('" . $text. "')) AS character_difference FROM thoughts AS Thought WHERE Thought.variant_of IS NULL AND Thought.name REGEXP '" . $regex . "' ORDER BY character_difference ASC, Thought.association_count DESC, Thought.provisional ASC LIMIT " . $limit;

			//echo $query . "<br/>";
			$results = $this->query($query);	
			//debug($results);
			if (!empty($results)){
				foreach ($results as $result){
					$result['Thought']['name'] =  trim($result['Thought']['name']);
					$key = $result['Thought']['name'];											   
					
					if (isset($suggestions[$key])){
						$suggestions[$key] = $suggestions[$key] + 1000;		   
					} else {
						$suggestions[$key] = 0 - $counter;
					}
					if ($text == $key) $suggestions[$key] =  $suggestions[$key] + 10000;	
					$counter++;

				}
			}
		}//and if
		
		//debug($suggestions);
	
		if (($source == 'all')||($source == 'google')){
			$googleSuggestions = googleSpellCheck($text);	
			//debug($googleSuggestions);
			if (!empty($googleSuggestions)){
				foreach ($googleSuggestions as $suggestion){
					if ($suggestion['confidence'] == 1){
						foreach ($suggestion['suggestions'] as $alternative){
							$suggestedText = trim(str_replace($suggestion['submission'], $alternative, $text));
							//if (!in_array($suggestedText, $suggestions))	$suggestions[] = $suggestedText;
							
							$key = $suggestedText;	
			
							if (isset($suggestions[$key])){
								$suggestions[$key] = $suggestions[$key] + 1000;		   
							} else {
								$suggestions[$key] = 0 - $counter;
							}	
							if ($text == $key) $suggestions[$key] =  $suggestions[$key] + 10000;
							$counter++;
						}
						
					}
				}
			}
		}//end if
		
		
		arsort($suggestions);
		//debug($suggestions);

		$counter = 0;
		$revisedSuggestions = array();
		foreach ($suggestions as $suggestion => $count){
			if ($counter < $limit){
				if (strtolower(trim($suggestion)) != strtolower(trim($text)))	$revisedSuggestions[] = $suggestion;
			} else
				break;
			$counter++;
		}
		//debug($revisedSuggestions);		
		return $revisedSuggestions;
		
	}//end function
	

}//end class
?>