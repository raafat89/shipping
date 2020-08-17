<?php

class Atlas_Model_AsnHeader {

    protected $_header_id;
    protected $_order_no;
    protected $_ship_date;
    protected $_pack_medium;
    protected $_pack_material;
    protected $_landing_qty;
    protected $_gross_weight;
    protected $_carrier;
    protected $_tracking_number;
    protected $_trans_code;
    protected $_alpha_code;
    protected $_volume;
    protected $_number_of_pallets;
    protected $_reference_no;
    protected $_shipment_method;
    protected $_seal_no;

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

    public function setShip_date($ship_date) {
        $this->_ship_date = $ship_date;
        return $this;
    }

    public function getShip_date() {
        return $this->_ship_date;
    }

    public function setPack_medium($pack_medium) {
        $this->_pack_medium = $pack_medium;
        return $this;
    }

    public function getPack_medium() {
        return $this->_pack_medium;
    }

    public function setPack_material($pack_material) {
        $this->_pack_material = $pack_material;
        return $this;
    }

    public function getPack_material() {
        return $this->_pack_material;
    }

    public function setLanding_qty($landing_qty) {
        $this->_landing_qty = $landing_qty;
        return $this;
    }

    public function getLanding_qty() {
        return $this->_landing_qty;
    }

    public function setGross_weight($gross_weight) {
        $this->_gross_weight = $gross_weight;
        return $this;
    }

    public function getGross_weight() {
        return $this->_gross_weight;
    }

    public function setCarrier($carrier) {
        $this->_carrier = $carrier;
        return $this;
    }

    public function getCarrier() {
        return $this->_carrier;
    }

    public function setTracking_number($tracking_number) {
        $this->_tracking_number = $tracking_number;
        return $this;
    }

    public function getTracking_number() {
        return $this->_tracking_number;
    }

    public function setTrans_code($trans_code) {
        $this->_trans_code = $trans_code;
        return $this;
    }

    public function getTrans_code() {
        return $this->_trans_code;
    }

    public function setAlpha_code($alpha_code) {
        $this->_alpha_code = $alpha_code;
        return $this;
    }

    public function getAlpha_code() {
        return $this->_alpha_code;
    }

    public function setVolume($volume) {
        $this->_volume = $volume;
        return $this;
    }

    public function getVolume() {
        return $this->_volume;
    }

    public function setNumber_of_pallets($number_of_pallets) {
        $this->_number_of_pallets = $number_of_pallets;
        return $this;
    }

    public function getNumber_of_pallets() {
        return $this->_number_of_pallets;
    }

    public function setReference_no($reference_no) {
        $this->_reference_no = $reference_no;
        return $this;
    }

    public function getReference_no() {
        return $this->_reference_no;
    }

    public function setShipment_method($shipment_method) {
        $this->_shipment_method = $shipment_method;
        return $this;
    }

    public function getShipment_method() {
        return $this->_shipment_method;
    }

    public function setSeal_no($seal_no) {
        $this->_seal_no = $seal_no;
        return $this;
    }

    public function getSeal_no() {
        return $this->_seal_no;
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