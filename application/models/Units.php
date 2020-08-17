<?php

class Atlas_Model_Units {

    protected $_unit_id;
    protected $_unit;
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
            throw new Exception('Invalid units property used');
        }
        $this->$method($value);
    }

#end __set function

    public function __get($name) {
        // if an unknown variable is used throw exception
        $method = 'get' . $name;
        if (!method_exists($this, $method)) {
            throw new Exception('Invalid units property used');
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

    public function setUnit_id($unit_id) {
        $this->_unit_id = $unit_id;
        return $this;
    }

#end setUnit_id function

    public function getUnit_id() {
        return $this->_unit_id;
    }

#end getUnit_id function

    public function setUnit($unit) {
        $this->_unit = $unit;
        return $this;
    }

#end setUnit function

    public function getUnit() {
        return $this->_unit;
    }

#end getUnit function

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