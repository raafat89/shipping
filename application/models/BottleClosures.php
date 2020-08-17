<?php

class Atlas_Model_BottleClosures {

    protected $_bottle_closure_id;
    protected $_bottle_closure;

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
            throw new Exception('Invalid bottle_closures property used');
        }
        $this->$method($value);
    }

#end __set function

    public function __get($name) {
        // if an unknown variable is used throw exception
        $method = 'get' . $name;
        if (!method_exists($this, $method)) {
            throw new Exception('Invalid bottle_closures property used');
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

    public function setBottle_closure_id($bottle_closure_id) {
        $this->_bottle_closure_id = $bottle_closure_id;
        return $this;
    }

#end setBottle_closure_id function

    public function getBottle_closure_id() {
        return $this->_bottle_closure_id;
    }

#end getBottle_closure_id function

    public function setBottle_closure($bottle_closure) {
        $this->_bottle_closure = $bottle_closure;
        return $this;
    }

#end setBottle_closure function

    public function getBottle_closure() {
        return $this->_bottle_closure;
    }

#end getBottle_closure function

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