<?php

class Atlas_Model_TestMethods {

    protected $_test_method_id;
    protected $_test_method;
    protected $_active;

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
            throw new Exception('Invalid test methods property used');
        }
        $this->$method($value);
    }

#end __set function

    public function __get($name) {
        // if an unknown variable is used throw exception
        $method = 'get' . $name;
        if (!method_exists($this, $method)) {
            throw new Exception('Invalid test methods property used');
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

    public function setTest_method_id($test_method_id) {
        $this->_test_method_id = $test_method_id;
        return $this;
    }

#end setTest_method_id function

    public function getTest_method_id() {
        return $this->_test_method_id;
    }

#end getTest_method_id function

    public function setTest_method($test_method) {
        $this->_test_method = $test_method;
        return $this;
    }

#end setTest_method function

    public function getTest_method() {
        return $this->_test_method;
    }

#end getTest_method function

    public function setActive($active) {
        $this->_active = $active;
        return $this;
    }

#end setActive function

    public function getActive() {
        return $this->_active;
    }

#end getActive function

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