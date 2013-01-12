<?php
/**
 * Email configuration class.
 * You can specify multiple configurations for production, development and testing.
 *
 * transport => The name of a supported transport; valid options are as follows:
 *		Mail 		- Send using PHP mail function
 *		Smtp		- Send using SMTP
 *		Debug		- Do not send the email, just return the result
 *
 * You can add custom transports (or override existing transports) by adding the
 * appropriate file to app/Network/Email.  Transports should be named 'YourTransport.php',
 * where 'Your' is the name of the transport.
 *
 * from =>
 * The origin email. See CakeEmail::from() about the valid values
 *
 */
class EmailConfig {

	public $default = array();

    public $gmail = array(
		'transport' => 'Smtp',
        'host' => 'ssl://smtp.gmail.com',
        'port' => 465,
        'username' => 'janders4@gmail.com',
        'password' => 'go1pthdvcx'
    );	
	
	/*
	public $mail = array(
		'transport' => 'Mail',
		'from' => 'you@localhost',
		//'charset' => 'utf-8',
		//'headerCharset' => 'utf-8',
	);		
	
	public $smtp = array(
		'transport' => 'Smtp',
		'host' => 'localhost',
		'port' => 25,
		'timeout' => 30//,
		//'username' => 'user',
		//'password' => 'secret',
		//'client' => null,
		//'log' => false
		//'charset' => 'utf-8',
		//'headerCharset' => 'utf-8',
	);

	public $allOptions = array(
		'from' => 'you@localhost',
		'sender' => null,
		'to' => null,
		'cc' => null,
		'bcc' => null,
		'replyTo' => null,
		'readReceipt' => null,
		'returnPath' => null,
		'messageId' => true,
		'subject' => null,
		'message' => null,
		'headers' => null,
		'viewRender' => null,
		'template' => false,
		'layout' => false,
		'viewVars' => null,
		'attachments' => null,
		'emailFormat' => null,
		'transport' => 'Smtp',
		'host' => 'localhost',
		'port' => 25,
		'timeout' => 30,
		'username' => 'user',
		'password' => 'secret',
		'client' => null,
		'log' => true,
		//'charset' => 'utf-8',
		//'headerCharset' => 'utf-8',
	);   
	*/
	
	public function __construct() {
		$this->default = ($_SERVER['SERVER_ADDR'] == '127.0.0.1') ?
            $this->gmail : $this->gmail;
			
		//$this->default['from'] = array('noreply@specialgoodstuff.com' => 'SpecialGoodStuff');
		$this->default['layout'] = 'default';
		$this->default['template'] = 'default';		
		$this->default['emailFormat'] = 'both';
    }
}