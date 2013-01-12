<?php
class RssFeed extends AppModel {

	public $name = 'RssFeed';
    public $useDbConfig = 'feedSource';
	public $feedUrl = "";

	public function __construct($feedUrl)
	{
		parent::__construct();
		$this->feedUrl = $feedUrl;
	}
}//end class
?>