<?php
/**
  * Get an api_key and secret from facebook and fill in this content.
  * save the file to app/Config/facebook.php
  */
  
  if ($_SERVER['SERVER_ADDR'] == '127.0.0.1')
  {
  	$config = array(
  		'Facebook' => array(
  			'appId' => '548038201880724',
  			'apiKey' => '548038201880724',
  			'secret' => '66b29b273e92f200e4b1c197821b17ba',
			'namespace' => 'specialgoodstuffdev',
  			'cookie' => true,
  			'locale' => 'en_US',
  		)
  	);  
  } 
  else
  {
  	$config = array(
  		'Facebook' => array(
  			'appId' => '531123700239179',
  			'apiKey' => '531123700239179',
  			'secret' => '005e04d402a549a8e4fb503880c2eb5e',
			'namespace' => 'specialgoodstuff',			
  			'cookie' => true,
  			'locale' => 'en_US',
  		)
  	);
  }

  
  
  Configure::write('facebookAppId', $config['Facebook']['appId']);
  Configure::write('facebookNamespace', $config['Facebook']['namespace']);
  
?>