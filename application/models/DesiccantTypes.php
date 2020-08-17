<?php

class Atlas_Model_DesiccantTypes {

    protected $_desiccant_type_id;
    protected $_desiccant_type;

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
            throw new Exception('Invalid desiccant_types property used');
        }
        $this->$method($value);
    }

#end __set function

    public function __get($name) {
        // if an unknown variable is used throw exception
        $method = 'get' . $name;
        if (!method_exists($this, $method)) {
            throw new Exception('Invalid desiccant_types property used');
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

    public function setDesiccant_type_id($desiccant_type_id) {
        $this->_desiccant_type_id = $desiccant_type_id;
        return $this;
    }

#end setDesiccant_type_id function

    public function getDesiccant_type_id() {
        return $this->_desiccant_type_id;
    }

#end getDesiccant_type_id function

    public function setDesiccant_type($desiccant_type) {
        $this->_desiccant_type = $desiccant_type;
        return $this;
    }

#end setDesiccant_type function

    public function getDesiccant_type() {
        return $this->_desiccant_type;
    }

#end getDesiccant_type function

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