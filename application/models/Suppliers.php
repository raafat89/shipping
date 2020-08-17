<?php

class Atlas_Model_Suppliers {

    protected $_supplier_id;
    protected $_supplier_name;
    protected $_supplier_code;
    protected $_supplier_active;

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

    public function setSupplier_id($supplier_id) {
        $this->_supplier_id = $supplier_id;
        return $this;
    }

#end setSupplier_id function

    public function getSupplier_id() {
        return $this->_supplier_id;
    }

#end getSupplier_id function

    public function setSupplier_name($supplier_name) {
        $this->_supplier_name = $supplier_name;
        return $this;
    }

#end setSupplier_name function

    public function getSupplier_name() {
        return $this->_supplier_name;
    }

#end getSupplier_name function

    public function setSupplier_code($supplier_code) {
        $this->_supplier_code = $supplier_code;
        return $this;
    }

#end setSupplier_code function

    public function getSupplier_code() {
        return $this->_supplier_code;
    }

#end getSupplier_code function

    public function setSupplier_active($supplier_active) {
        $this->_supplier_active = $supplier_active;
        return $this;
    }

#end setSupplier_active function

    public function getSupplier_active() {
        return $this->_supplier_active;
    }

#end getSupplier_active function

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