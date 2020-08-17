<?php

class Atlas_Model_BulkSizes {

    protected $_bulk_size_id;
    protected $_bulk_size;

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
            throw new Exception('Invalid bulk_sizes property used');
        }
        $this->$method($value);
    }

#end __set function

    public function __get($name) {
        // if an unknown variable is used throw exception
        $method = 'get' . $name;
        if (!method_exists($this, $method)) {
            throw new Exception('Invalid bulk_sizes property used');
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

    public function setBulk_size_id($bulk_size_id) {
        $this->_bulk_size_id = $bulk_size_id;
        return $this;
    }

#end setBulk_size_id function

    public function getBulk_size_id() {
        return $this->_bulk_size_id;
    }

#end getBulk_size_id function

    public function setBulk_size($bulk_size) {
        $this->_bulk_size = $bulk_size;
        return $this;
    }

#end setBulk_size function

    public function getBulk_size() {
        return $this->_bulk_size;
    }

#end getBulk_size function

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