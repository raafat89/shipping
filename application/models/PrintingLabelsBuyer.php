<?php

class Atlas_Model_PrintingLabelsBuyer {

    protected $_id;
    protected $_upc;
    protected $_asin;
    protected $_sku;

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

    public function setId($id) {
        $this->_id = $id;
        return $this;
    }

#end setId function

    public function getId() {
        return $this->_id;
    }

#end getId function

    public function setUpc($upc) {
        $this->_upc = $upc;
        return $this;
    }

#end setUpc function

    public function getUpc() {
        return $this->_upc;
    }

#end getUpc function

    public function setAsin($asin) {
        $this->_asin = $asin;
        return $this;
    }

#end setAsin function

    public function getAsin() {
        return $this->_asin;
    }

#end getAsin function

    public function setSku($sku) {
        $this->_sku = $sku;
        return $this;
    }

#end setSku function

    public function getSku() {
        return $this->_sku;
    }

#end getSku function
}

?>