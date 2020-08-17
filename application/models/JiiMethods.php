<?php

class Atlas_Model_JiiMethods {

    protected $_jii_method_id;
    protected $_name;
    protected $_code;

    public function __construct(array $options = NULL) {
        // if attributes were given set the base values
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

#end __construct function

    public function __set($name, $value) {
        // if an unknown variable is used throw exception
        $method = 'set' . $name;
        if (!method_exists($this, $method)) {
            throw new Exception('Invalid scrolling banner property used');
        }
        $this->$method($value);
    }

#end __set function

    public function __get($name) {
        // if an unknown variable is used throw exception
        $method = 'get' . $name;
        if (!method_exists($this, $method)) {
            throw new Exception('Invalid scrolling banner property used');
        }
        return $this->method();
    }

#end __get function

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

    public function setJii_method_id($jii_method_id) {
        $this->_jii_method_id = $jii_method_id;
        return $this;
    }

#end setJii_method_id function

    public function getJii_method_id() {
        return $this->_jii_method_id;
    }

#end getJii_method_id function

    public function setName($name) {
        $this->_name = $name;
        return $this;
    }

#end setName function

    public function getName() {
        return $this->_name;
    }

#end getName function

    public function setCode($code) {
        $this->_code = $code;
        return $this;
    }

#end setCode function

    public function getCode() {
        return $this->_code;
    }

#end getCode function

    public function toArray() {
        $class_vars = get_class_vars(__CLASS__);
        $results = array();
        foreach ($class_vars as $index => $value) {
            $results[substr($index, 1)] = $this->$index;
        }
        return $results;
    }

#end toArray function
}

?>