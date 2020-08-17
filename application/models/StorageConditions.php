<?php

class Atlas_Model_StorageConditions {

    protected $_storage_condition_id;
    protected $_storage_condition;

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
            throw new Exception('Invalid storage_condition property used');
        }
        $this->$method($value);
    }

#end __set function

    public function __get($name) {
        // if an unknown variable is used throw exception
        $method = 'get' . $name;
        if (!method_exists($this, $method)) {
            throw new Exception('Invalid storage_condition property used');
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

    public function setStorage_condition_id($storage_condition_id) {
        $this->_storage_condition_id = $storage_condition_id;
        return $this;
    }

#end setStorage_condition_id function

    public function getStorage_condition_id() {
        return $this->_storage_condition_id;
    }

#end getStorage_condition_id function

    public function setStorage_condition($storage_condition) {
        $this->_storage_condition = $storage_condition;
        return $this;
    }

#end setStorage_condition function

    public function getStorage_condition() {
        return $this->_storage_condition;
    }

#end getStorage_condition function

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