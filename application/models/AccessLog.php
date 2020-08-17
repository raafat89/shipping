<?php

class Atlas_Model_AccessLog
{
	protected $_access_log_id;
	protected $_timestamp;
	protected $_user_id;
	protected $_ip_address;
	protected $_message;
	
	// set the default values if options given
	public function __construct(array $options = NULL)
	{
		// if attributes were given set the base values
		if( is_array($options) ){
			$this->setOptions($options);
		}
	} #end __construct() function
	
	// check if a user defined variable exists
	public function __set($name, $value)
	{
		// if an unknown variable is used throw exception
		$method = 'set' . $name;
		if( !method_exists($this, $method) ){
			throw new Exception('invalid class variable called for write');
		}
		$this->$method($value);
	} #end __set() function
	
	// check if a user defined variable exists
	public function __get($name)
	{
		// if an unknown variable is used throw exception
		$method = 'get' . $name;
		if( !method_exists($this, $method) ){
			throw new Exception('invalid class variable called for read');
		}
		return $this->method();
	} #end __get() function
	
	public function setOptions(array $options)
	{
		// get a list of all setter methods and set each
		// value from the given array into the object
		$methods = get_class_methods($this);
		foreach( $options as $key=>$value ){
			$method = 'set' . ucfirst(strtolower($key));
			if( in_array($method, $methods) ){
				$this->$method($value);
			}
		}
		return $this;
	} #end setOptions function
	
	public function setAccess_log_id($access_log_id)
	{
		$this->_access_log_id = $access_log_id;
		return $this;
	} #end setAccess_log_id() function
	
	public function getAccess_log_id()
	{
		return $this->_access_log_id;
	} #end getAccess_log_id() function
	
	public function setTimestamp($timestamp)
	{
		$this->_timestamp = $timestamp;
		return $this;
	} #end setTimestamp() function
	
	public function getTimestamp()
	{
		return $this->_timestamp;
	} #end getTimestamp() function
	
	public function setUser_id($user_id)
	{
		$this->_user_id = $user_id;
		return $this;
	} #end setUser_id() function
	
	public function getUser_id()
	{
		return $this->_user_id;
	} #end getUser_id() function
	
	public function setIp_address($ip_address)
	{
		$this->_ip_address = $ip_address;
		return $this;
	} #end setIp_address() function
	
	public function getIp_address()
	{
		return $this->_ip_address;
	} #end getIp_address() function
	
	public function setMessage($message)
	{
		$this->_message = $message;
		return $this;
	} #end setMessage() function
	
	public function getMessage()
	{
		return $this->_message;
	} #end getMessage() function
	
	public function toArray()
	{
		$class_vars = get_class_vars(__CLASS__);
		$results    = array();
		foreach( $class_vars as $index=>$value ){
			$results[substr($index, 1)] = $this->$index;
		}
		return $results;
	} #end toArray() function
	
}

?>