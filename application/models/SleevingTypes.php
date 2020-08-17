<?php

class Atlas_Model_SleevingTypes {

    protected $_sleeving_id;
    protected $_sleeving_types;

    public function __construct(array $options = NULL) {
        // if attributes were given set the base values
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

#end __construct function

    public function __set($name, $value) {
        // if an unknown variable is used throw exception
        $method = "set" . $name;
        if (!method_exists($this, $method)) {
            throw new Exception("Invalid property used");
        }
        $this->$method($value);
    }

#end __set function

    public function __get($name) {
        // if an unknown variable is used throw exception
        $method = "get" . $name;
        if (!method_exists($this, $method)) {
            throw new Exception("Invalid property used");
        }
        return $this->method();
    }

#end __get function

    public function setOptions(array $options) {
        // get a list of all setter methods and set each
        // value from the given array into the object
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = "set" . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

#end setOptions function

    public function toArray() {
        $class_vars = get_class_vars(__CLASS__);
        $results = array();
        foreach ($class_vars as $index => $value) {
            $results[substr($index, 1)] = $this->$index;
        }
        return $results;
    }

#end toArray function

    public function setSleeving_id($sleeving_id) {
        $this->_sleeving_id = $sleeving_id;
        return $this;
    }

#end setSleeving_id function

    public function getSleeving_id() {
        return $this->_sleeving_id;
    }

#end getSleeving_id function

    public function setSleeving_types($sleeving_types) {
        $this->_sleeving_types = $sleeving_types;
        return $this;
    }

#end setSleeving_types function

    public function getSleeving_types() {
        return $this->_sleeving_types;
    }

#end getSleeving_types function
}

?>