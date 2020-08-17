<?php

class Atlas_Model_AsnLines {

    protected $_line_id;
    protected $_header_id;
    protected $_order_no;
    protected $_item_key;
    protected $_pack_type;
    protected $_qty_ship;
    protected $_qty_asc;
    protected $_lot_no;
    protected $_lot_exp_date;
    protected $_line_no;
    protected $_track_no;

    // set the default values if options given
    public function __construct(array $options = NULL) {
        // if attributes were given set the base values
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

#end __construct() function
    // check if a user defined variable exists
    public function __set($name, $value) {
        // if an unknown variable is used throw exception
        $method = 'set' . $name;
        if (!method_exists($this, $method)) {
            throw new Exception('invalid class variable called for write');
        }
        $this->$method($value);
    }

#end __set() function
    // check if a user defined variable exists
    public function __get($name) {
        // if an unknown variable is used throw exception
        $method = 'get' . $name;
        if (!method_exists($this, $method)) {
            throw new Exception('invalid class variable called for read');
        }
        return $this->method();
    }

#end __get() function

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

    public function setLine_id($line_id) {
        $this->_line_id = $line_id;
        return $this;
    }

    public function getLine_id() {
        return $this->_line_id;
    }

    public function setHeader_id($header_id) {
        $this->_header_id = $header_id;
        return $this;
    }

    public function getHeader_id() {
        return $this->_header_id;
    }

    public function setOrder_no($order_no) {
        $this->_order_no = $order_no;
        return $this;
    }

    public function getOrder_no() {
        return $this->_order_no;
    }

    public function setItem_key($item_key) {
        $this->_item_key = $item_key;
        return $this;
    }

    public function getItem_key() {
        return $this->_item_key;
    }

    public function setPack_type($pack_type) {
        $this->_pack_type = $pack_type;
        return $this;
    }

    public function getPack_type() {
        return $this->_pack_type;
    }

    public function setQty_ship($qty_ship) {
        $this->_qty_ship = $qty_ship;
        return $this;
    }

    public function getQty_ship() {
        return $this->_qty_ship;
    }

    public function setQty_asc($qty_asc) {
        $this->_qty_asc = $qty_asc;
        return $this;
    }

    public function getQty_asc() {
        return $this->_qty_asc;
    }

    public function setLot_no($lot_no) {
        $this->_lot_no = $lot_no;
        return $this;
    }

    public function getLot_no() {
        return $this->_lot_no;
    }

    public function setLot_exp_date($lot_exp_date) {
        $this->_lot_exp_date = $lot_exp_date;
        return $this;
    }

    public function getLot_exp_date() {
        return $this->_lot_exp_date;
    }

    public function setLine_no($line_no) {
        $this->_line_no = $line_no;
        return $this;
    }

    public function getLine_no() {
        return $this->_line_no;
    }

    public function setTrack_no($track_no) {
        $this->_track_no = $track_no;
        return $this;
    }

    public function getTrack_no() {
        return $this->_track_no;
    }

    public function toArray() {
        $class_vars = get_class_vars(__CLASS__);
        $results = array();
        foreach ($class_vars as $index => $value) {
            $results[substr($index, 1)] = $this->$index;
        }
        return $results;
    }

#end toArray() function
}

?>