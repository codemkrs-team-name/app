<?php
class Status extends AppModel {

	public $name = 'Status';
	public $order = array('date' => 'DESC');
	public $belongsTo = array('User');	
	
	public function get()
	{		
	
		App::uses('Post', 'Model');
		App::uses('User', 'Model');
		App::uses('RssFeed', 'Model');	
		
		$feeds = array(
			'twitter' => 'http://api.twitter.com/1/statuses/user_timeline.rss?screen_name=basedonreallife',
			'thlinx' => 'http://thlinx.com/wordpress/feed',
			'tumblr' => 'http://basedonreallife.net/rss'/*,
			'specialgoodstuff' => 'http://specialgoodstuff.com/wordpress/feed'*/
		);
		
		$statuses = array();
		foreach($feeds as $source => $url)
		{
			$records = array();
			
			try {
				$feed = new RssFeed($url);
				$records = $feed->find('all');
			} catch (Exception $e) {
				debug($e->getMessage());	
			}
			
			//debug($records);
			
			foreach ($records as $record)
			{
				$record = $record['RssFeed'];
				$status = array();
				
				//debug($record);
				
				if ((isset($record['title']) && isset($record['pubDate'])) &&
				(($source != 'twitter') || (! stristr($record['title'], "basedonreallife") )))
				{
					$status['title'] = preg_replace("/[^a-z0-9 '-:\"()]/i", "", $record['title']);
					$status['date'] = date('Y-m-d H:i:s', strtotime($record['pubDate']));
					if (!empty($record['link']))
					{
						$status['link'] = $record['link'];
						if ($source == 'thlinx')
						{
							$status['link'] = preg_replace("/wordpress\/[0-9]*\/[0-9]*/i", "posts/view", $status['link']);
						}
							
					}
										
					$status['source'] = $source;
					
					try {
						$this->insert($status);
					} catch (Exception $e) {}
					//debug($status);
				}
			}
		}//end foreach
				
		$userModel = new User();
		$postModel = new Post();
		
		$user = $userModel->findById('1');	
		$conditions = array("post_type = 'post'", "post_status = 'publish'");
		if (!empty($user['User']['last_status_check']))
		{
			$conditions[] = "post_date > '" . $user['User']['last_status_check'] . "'";
		}

		$posts = $postModel->find("all", array(
			"conditions" => $conditions,
			"order" => array("post_date" => "DESC")
		));		
		
		foreach ($posts as $post)
		{		
			$status = array();
			$status['title'] = $post['Post']['post_title'];
			$status['date'] = $post['Post']['post_date'];
			$status['link'] = "/posts/view/" . $post['Post']['post_name'];					
			$status['source'] = 'specialgoodstuff';
			
			try {
				$this->insert($status);
			} catch (Exception $e) {}
		}
		
		$userModel->updateField("1", "last_status_check", date('Y-m-d H:i:s') );
		
		/*
		$thlinxFeed = new RssFeed('http://thlinx.com/wordpress/feed');
		$data = $thlinxFeed->find('all');
		
		debug($data);
		$tumblrFeed = new RssFeed('http://basedonreallife.net/rss');
		$data = $tumblrFeed->find('all');

		debug($data);
		
		$twitterFeed = new RssFeed('http://api.twitter.com/1/statuses/user_timeline.rss?screen_name=basedonreallife');
		$data = $twitterFeed->find('all');

		debug($data);
		*/
		
		/*
		$facebookUser = $this->Connect->user();
		$facebookId = '769129120';
		$feed = FB::api("/" . $facebookId . "/feed?limit=100&status_type=mobile_status_update");
		
		debug($feed);
		if (!empty($feed['data']))
		{
			foreach($feed['data'] as $record)
			{
				debug($record);
				if ($record['type'] == 'status_update' || $record['type'] == 'mobile_status_update')
				{
					debug($record);
				}
			}
		}//end if feed		
		*/
	}
	
	
}//end class
?>