<?php

class Atlas_Model_Allergens {

    protected $_allergens_id;
    protected $_allergens_item;
    protected $_allergens_data;
    protected $_allergens_organic;
    protected $_allergens_cooler;

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

    public function setAllergens_id($allergens_id) {
        $this->_allergens_id = $allergens_id;
        return $this;
    }

#end setAllergens_id function

    public function getAllergens_id() {
        return $this->_allergens_id;
    }

#end getAllergens_id function

    public function setAllergens_item($allergens_item) {
        $this->_allergens_item = $allergens_item;
        return $this;
    }

#end setAllergens_item function

    public function getAllergens_item() {
        return $this->_allergens_item;
    }

#end getAllergens_item function

    public function setAllergens_data($allergens_data) {
        $this->_allergens_data = $allergens_data;
        return $this;
    }

#end setAllergens_data function

    public function getAllergens_data() {
        return $this->_allergens_data;
    }

#end getAllergens_data function

    public function setAllergens_organic($allergens_organic) {
        $this->_allergens_organic = $allergens_organic;
        return $this;
    }

#end setAllergens_organic function

    public function getAllergens_organic() {
        return $this->_allergens_organic;
    }

#end getAllergens_organic function

    public function setAllergens_cooler($allergens_cooler) {
        $this->_allergens_cooler = $allergens_cooler;
        return $this;
    }

#end setAllergens_cooler function

    public function getAllergens_cooler() {
        return $this->_allergens_cooler;
    }

#end getAllergens_cooler function
}

?>