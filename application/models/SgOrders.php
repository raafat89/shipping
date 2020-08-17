<?php

class Atlas_Model_SgOrders {

    protected $_order_id;
    protected $_cust_no;
    protected $_temp_orderno;
    protected $_conf_orderno;
    protected $_lines;
    protected $_partner_code;
    protected $_po_no;
    protected $_order_date;
    protected $_user_id;
    protected $_msg;
    protected $_ack;
    protected $_batch_no;
    protected $_status;
    protected $_order_data;
    protected $_order_file;
    protected $_partner;
    protected $_processed_datetime;
    protected $_complete;

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

    public function setOrder_id($order_id) {
        $this->_order_id = $order_id;
        return $this;
    }

#end setOrder_id function

    public function getOrder_id() {
        return $this->_order_id;
    }

#end getOrder_id function

    public function setCust_no($cust_no) {
        $this->_cust_no = $cust_no;
        return $this;
    }

#end setCust_no function

    public function getCust_no() {
        return $this->_cust_no;
    }

#end getCust_no function

    public function setTemp_orderno($temp_orderno) {
        $this->_temp_orderno = $temp_orderno;
        return $this;
    }

#end setTemp_orderno function

    public function getTemp_orderno() {
        return $this->_temp_orderno;
    }

#end getTemp_orderno function

    public function setConf_orderno($conf_orderno) {
        $this->_conf_orderno = $conf_orderno;
        return $this;
    }

#end setConf_orderno function

    public function getConf_orderno() {
        return $this->_conf_orderno;
    }

#end getConf_orderno function

    public function setLines($lines) {
        $this->_lines = $lines;
        return $this;
    }

#end setLines function

    public function getLines() {
        return $this->_lines;
    }

#end getLines function

    public function setPartner_code($partner_code) {
        $this->_partner_code = $partner_code;
        return $this;
    }

#end setPartner_code function

    public function getPartner_code() {
        return $this->_partner_code;
    }

#end getPartner_code function

    public function setPo_no($po_no) {
        $this->_po_no = $po_no;
        return $this;
    }

#end setPo_no function

    public function getPo_no() {
        return $this->_po_no;
    }

#end getPo_no function

    public function setOrder_date($order_date) {
        $this->_order_date = $order_date;
        return $this;
    }

#end setOrder_date function

    public function getOrder_date() {
        return $this->_order_date;
    }

#end getOrder_date function

    public function setOrder_file($order_file) {
        $this->_order_file = $order_file;
        return $this;
    }

#end setOrder_date function

    public function getOrder_file() {
        return $this->_order_file;
    }

#end getOrder_date function

    public function setUser_id($user_id) {
        $this->_user_id = $user_id;
        return $this;
    }

#end setUser_id function

    public function getUser_id() {
        return $this->_user_id;
    }

#end getUser_id function

    public function setMsg($msg) {
        $this->_msg = $msg;
        return $this;
    }

#end setMsg function

    public function getMsg() {
        return $this->_msg;
    }

#end getMsg function

    public function setAck($ack) {
        $this->_ack = $ack;
        return $this;
    }

#end setAck function

    public function getAck() {
        return $this->_ack;
    }

#end getAck function

    public function setBatch_no($batch_no) {
        $this->_batch_no = $batch_no;
        return $this;
    }

#end setBatch_no function

    public function getBatch_no() {
        return $this->_batch_no;
    }

#end getBatch_no function

    public function setStatus($status) {
        $this->_status = $status;
        return $this;
    }

#end setStatus function

    public function getStatus() {
        return $this->_status;
    }

#end getStatus function

    public function setPartner($partner) {
        $this->_partner = $partner;
        return $this;
    }

#end setStatus function

    public function getPartner() {
        return $this->_partner;
    }

#end getStatus function

    public function setOrder_data($order_data) {
        $this->_order_data = $order_data;
        return $this;
    }

#end setOrder_data function

    public function getOrder_data() {
        return $this->_order_data;
    }

#end getOrder_data function

    public function setProcessed_datetime($processed_datetime) {
        $this->_processed_datetime = $processed_datetime;
        return $this;
    }

#end setProcessed_datetime function

    public function getProcessed_datetime() {
        return $this->_processed_datetime;
    }

#end getProcessed_datetime function

    public function setComplete($complete) {
        $this->_complete = $complete;
        return $this;
    }

#end setComplete function

    public function getComplete() {
        return $this->_complete;
    }

#end getComplete function
}

?>