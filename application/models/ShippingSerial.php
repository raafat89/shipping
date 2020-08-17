<?php

class Atlas_Model_ShippingSerial {

    protected $_serial_id;
    protected $_order_id;
    protected $_serial_number;
    protected $_serial_date;

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

    public function setSerial_id($serial_id) {
        $this->_serial_id = $serial_id;
        return $this;
    }

#end setSerial_id function

    public function getSerial_id() {
        return $this->_serial_id;
    }

#end getSerial_id function

    public function setOrder_id($order_id) {
        $this->_order_id = $order_id;
        return $this;
    }

#end setSerial_id function

    public function getOrder_id() {
        return $this->_order_id;
    }

#end getSerial_id function

    public function setSerial_number($serial_number) {
        $this->_serial_number = $serial_number;
        return $this;
    }

#end setSerial_number function

    public function getSerial_number() {
        return $this->_serial_number;
    }

#end getSerial_number function

    public function setSerial_date($serial_date) {
        $this->_serial_date = $serial_date;
        return $this;
    }

#end setSerial_date function

    public function getSerial_date() {
        return $this->_serial_date;
    }

#end getSerial_date function
}

?>