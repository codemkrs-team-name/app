<?php


class DATABASE_CONFIG {

	public $development = array(
		'datasource' => 'Database/Mysql',
		'persistent' => true,
		'host' => 'localhost',
		'login' => 'root',
		'password' => '',
		'database' => 'nolaeventmap',
		'prefix' => ''/*,
		'encoding' => 'utf8'*/
	);

	public $production = array(
		'datasource' => 'Database/Mysql',
		'persistent' => true,
		'host' => 'ec2-50-16-133-200.compute-1.amazonaws.com',
		'port' => '3306',
		'login' => 'events',
		'password' => 'banana',
		'database' => 'nola_event_map',
		'encoding' => 'utf8'
	);

	public $developmentWordpress = array(
		'datasource' => 'Database/Mysql',
		'persistent' => true,
		'host' => 'localhost',
		'login' => 'root',
		'password' => '',
		'database' => 'nolaeventmap_wordpress',
		'prefix' => 'wp_'/*,
		'encoding' => 'utf8'*/
	);


	public $productionWordpress = array(
		'datasource' => 'Database/Mysql',
		'persistent' => true,
		'host' => 'localhost',
		'login' => '',
		'password' => '',
		'database' => '',
		'prefix' => 'wp_',
		'encoding' => 'utf8'
	);

    public $feedSource = array(
        'datasource' => 'Rss.Rss',
        'database' => false,
        'encoding' => 'UTF-8',
        'cacheTime' => '+1 day'
    );			
	
    public $default = array();
	public $wordpress = array();

    function __construct()
    {
		
		if ($_SERVER['SERVER_ADDR'] == '127.0.0.1')
		{
			$this->default = $this->production;
			$this->wordpress = $this->developmentWordpress;
			
		}
		else
		{ 
			$this->default = $this->production;
			$this->wordpress = $this->productionWordpress;			
		}  
    }
	
    function DATABASE_CONFIG()
    {
        $this->__construct();
    } 
	
	
}
