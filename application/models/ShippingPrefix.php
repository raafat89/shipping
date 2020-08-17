<?php

class Atlas_Model_ShippingPrefix {

    protected $_prefix_id;
    protected $_trading_partner;
    protected $_prefix;
    protected $_pack;
    protected $_sple_id;
    protected $_item_status;
    protected $_docs;

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

    public function setPrefix_id($prefix_id) {
        $this->_prefix_id = $prefix_id;
        return $this;
    }

#end setPrefix_id function

    public function getPrefix_id() {
        return $this->_prefix_id;
    }

#end getPrefix_id function

    public function setTrading_partner($trading_partner) {
        $this->_trading_partner = $trading_partner;
        return $this;
    }

#end setTrading_partner function

    public function getTrading_partner() {
        return $this->_trading_partner;
    }

#end getTrading_partner function

    public function setPrefix($prefix) {
        $this->_prefix = $prefix;
        return $this;
    }

#end setPrefix function

    public function getPrefix() {
        return $this->_prefix;
    }

#end getPrefix function

    public function setPack($pack) {
        $this->_pack = $pack;
        return $this;
    }

#end setPack function

    public function getPack() {
        return $this->_pack;
    }

#end getPack function

    public function setSple_id($sple_id) {
        $this->_sple_id = $sple_id;
        return $this;
    }

#end setSple_id function

    public function getSple_id() {
        return $this->_sple_id;
    }

#end getSple_id function

    public function setItem_status($item_status) {
        $this->_item_status = $item_status;
        return $this;
    }

#end setItem_status function

    public function getItem_status() {
        return $this->_item_status;
    }

#end getItem_status function

    public function setDocs($docs) {
        $this->_docs = $docs;
        return $this;
    }

#end setDocs function

    public function getDocs() {
        return $this->_docs;
    }

#end getDocs function
}

?>