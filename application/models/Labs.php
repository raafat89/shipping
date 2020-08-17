<?php

class Atlas_Model_Labs {

    protected $_lab_id;
    protected $_lab_name;
    protected $_lab_active;

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
            throw new Exception('Invalid labs property used');
        }
        $this->$method($value);
    }

#end __set function

    public function __get($name) {
        // if an unknown variable is used throw exception
        $method = 'get' . $name;
        if (!method_exists($this, $method)) {
            throw new Exception('Invalid labs property used');
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

    public function setLab_id($lab_id) {
        $this->_lab_id = $lab_id;
        return $this;
    }

#end setLab_id function

    public function getLab_id() {
        return $this->_lab_id;
    }

#end getLab_id function

    public function setLab_name($lab_name) {
        $this->_lab_name = $lab_name;
        return $this;
    }

#end setLab_name function

    public function getLab_name() {
        return $this->_lab_name;
    }

#end getLab_name function

    public function setLab_active($lab_active) {
        $this->_lab_active = $lab_active;
        return $this;
    }

#end setLab_active function

    public function getLab_active() {
        return $this->_lab_active;
    }

#end getLab_active function

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