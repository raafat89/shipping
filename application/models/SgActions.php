<?php

class Atlas_Model_SgActions {

    protected $_action_id;
    protected $_order_id;
    protected $_action;
    protected $_user_id;
    protected $_action_datetime;
    protected $_order_no;
    protected $_file;

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

    public function setAction_id($action_id) {
        $this->_action_id = $action_id;
        return $this;
    }

#end setAction_id function

    public function getAction_id() {
        return $this->_action_id;
    }

#end getAction_id function

    public function setOrder_id($order_id) {
        $this->_order_id = $order_id;
        return $this;
    }

#end setOrder_id function

    public function getOrder_id() {
        return $this->_order_id;
    }

#end getOrder_id function

    public function setUser_id($user_id) {
        $this->_user_id = $user_id;
        return $this;
    }

#end setUser_id function

    public function getUser_id() {
        return $this->_user_id;
    }

#end getUser_id function

    public function setAction_datetime($action_datetime) {
        $this->_action_datetime = $action_datetime;
        return $this;
    }

#end setAction_datetime function

    public function getAction_datetime() {
        return $this->_action_datetime;
    }

#end getAction_datetime function

    public function setAction($action) {
        $this->_action = $action;
        return $this;
    }

#end setAction function

    public function getAction() {
        return $this->_action;
    }

#end getAction function

    public function setOrder_no($order_no) {
        $this->_order_no = $order_no;
        return $this;
    }

#end setOrder_no function

    public function getOrder_no() {
        return $this->_order_no;
    }

#end getOrder_no function

    public function setFile($file) {
        $this->_file = $file;
        return $this;
    }

#end setFile function

    public function getFile() {
        return $this->_file;
    }

#end getFile function
}

?>