<?php
class Contact extends AppModel {

	public $name = 'Contact';
	public $useTable = false;
	
    public $_schema = array(
        'name' => array('type' => 'string' , 'null' => false, 'default' => '', 'length' => '50'),
        'from' => array('type' => 'string' , 'null' => false, 'default' => '', 'length' => '80'),
		'subject' => array('type' => 'string' , 'null' => true, 'default' => "website stuff", 'length' => '80'),
        'message' => array('type' => 'text' , 'null' => false, 'default' => ''),
    );

    public $validate = array(
	'name' => array(
	    'notempty' => array(
		'rule' => array('notempty'),
		'required' => true
	    )
	),
	'from' => array(
	    'email' => array(
		'rule' => array('email'),
		'required' => true
	    )
	),
	'message' => array(
	    'notempty' => array(
		'rule' => array('notempty'),
		'required' => true
	    )
	),
    );

    public function beforeValidate($options = array()) {
		parent::beforeValidate($options);

		$this->validate['name']['notempty']['message'] = __d('contactform', 'please enter your name');
		$this->validate['from']['email']['message'] = __d('contactform', 'please enter a valid email address');
		$this->validate['message']['notempty']['message'] = __d('contactform', 'please enter your message');
    }	
	
}//end class
?>