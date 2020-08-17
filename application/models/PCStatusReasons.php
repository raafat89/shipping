<?php

class Atlas_Model_PCStatusReasons {

    protected $_reason_id;
    protected $_reason;
    protected $_status;

    // set the default values if options given
    public function __construct(array $options = NULL) {
        // if attributes were given set the base values
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

#end __construct() function
    // check if a user defined variable exists
    public function __set($name, $value) {
        // if an unknown variable is used throw exception
        $method = 'set' . $name;
        if (!method_exists($this, $method)) {
            throw new Exception('invalid class variable called for write');
        }
        $this->$method($value);
    }

#end __set() function
    // check if a user defined variable exists
    public function __get($name) {
        // if an unknown variable is used throw exception
        $method = 'get' . $name;
        if (!method_exists($this, $method)) {
            throw new Exception('invalid class variable called for read');
        }
        return $this->method();
    }

#end __get() function

    public function setOptions(array $options) {
        // get a list of all setter methods and set each
        // value from the given array into the object
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst(strtolower($key));
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

#end setOptions function

    public function setReason_id($reason_id) {
        $this->_reason_id = $reason_id;
        return $this;
    }

#end setReason_id() function

    public function getReason_id() {
        return $this->_reason_id;
    }

#end getReason_id() function

    public function setReason($reason) {
        $this->_reason = $reason;
        return $this;
    }

#end setReason() function

    public function getReason() {
        return $this->_reason;
    }

#end getReason() function

    public function setStatus($status) {
        $this->_status = $status;
        return $this;
    }

#end setStatus() function

    public function getStatus() {
        return $this->_status;
    }

#end getStatus() function

    public function toArray() {
        $class_vars = get_class_vars(__CLASS__);
        $results = array();
        foreach ($class_vars as $index => $value) {
            $results[substr($index, 1)] = $this->$index;
        }
        return $results;
    }

#end toArray() function
}

?>