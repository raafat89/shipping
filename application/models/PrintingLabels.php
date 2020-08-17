<?php

class Atlas_Model_PrintingLabels {

    protected $_id;
    protected $_bol;
    protected $_ship_to_name;
    protected $_ship_to_address;
    protected $_ship_to_city;
    protected $_ship_to_state;
    protected $_ship_to_zip;
    protected $_landing_qty;
    protected $_po;
    protected $_carton_id;
    protected $_line;
    protected $_buyer_item;
    protected $_vdr_item;
    protected $_upc;
    protected $_descr;
    protected $_qty_ship;
    protected $_item_no;
    protected $_ctn_qty;
    protected $_uom;
    protected $_lot_number;
    protected $_expiration_date;
    protected $_barcode_zip_text;
    protected $_barcode_upc_text;
    protected $_store_id;
    protected $_line_seq;

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

    public function setId($id) {
        $this->_id = $id;
        return $this;
    }

#end setId function

    public function getId() {
        return $this->_id;
    }

#end getId function

    public function setBol($bol) {
        $this->_bol = $bol;
        return $this;
    }

#end setBol function

    public function getBol() {
        return $this->_bol;
    }

#end getBol function

    public function setShip_to_name($ship_to_name) {
        $this->_ship_to_name = $ship_to_name;
        return $this;
    }

#end setShip_to_name function

    public function getShip_to_name() {
        return $this->_ship_to_name;
    }

#end getShip_to_name function

    public function setShip_to_address($ship_to_address) {
        $this->_ship_to_address = $ship_to_address;
        return $this;
    }

#end setShip_to_address function

    public function getShip_to_address() {
        return $this->_ship_to_address;
    }

#end getShip_to_address function

    public function setShip_to_city($ship_to_city) {
        $this->_ship_to_city = $ship_to_city;
        return $this;
    }

#end setShip_to_city function

    public function getShip_to_city() {
        return $this->_ship_to_city;
    }

#end getShip_to_city function

    public function setShip_to_state($ship_to_state) {
        $this->_ship_to_state = $ship_to_state;
        return $this;
    }

#end setShip_to_state function

    public function getShip_to_state() {
        return $this->_ship_to_state;
    }

#end getShip_to_state function

    public function setShip_to_zip($ship_to_zip) {
        $this->_ship_to_zip = $ship_to_zip;
        return $this;
    }

#end setShip_to_zip function

    public function getShip_to_zip() {
        return $this->_ship_to_zip;
    }

#end getShip_to_zip function

    public function setLanding_qty($landing_qty) {
        $this->_landing_qty = $landing_qty;
        return $this;
    }

#end setLanding_qty function

    public function getLanding_qty() {
        return $this->_landing_qty;
    }

#end getLanding_qty function

    public function setPo($po) {
        $this->_po = $po;
        return $this;
    }

#end setPo function

    public function getPo() {
        return $this->_po;
    }

#end getPo function

    public function setCarton_id($carton_id) {
        $this->_carton_id = $carton_id;
        return $this;
    }

#end setCarton_id function

    public function getCarton_id() {
        return $this->_carton_id;
    }

#end getCarton_id function

    public function setLine($line) {
        $this->_line = $line;
        return $this;
    }

#end setLine function

    public function getLine() {
        return $this->_line;
    }

#end getLine function

    public function setBuyer_item($buyer_item) {
        $this->_buyer_item = $buyer_item;
        return $this;
    }

#end setBuyer_item function

    public function getBuyer_item() {
        return $this->_buyer_item;
    }

#end getBuyer_item function

    public function setVdr_item($vdr_item) {
        $this->_vdr_item = $vdr_item;
        return $this;
    }

#end setVdr_item function

    public function getVdr_item() {
        return $this->_vdr_item;
    }

#end getVdr_item function

    public function setUpc($upc) {
        $this->_upc = $upc;
        return $this;
    }

#end setUpc function

    public function getUpc() {
        return $this->_upc;
    }

#end getUpc function

    public function setDescr($descr) {
        $this->_descr = $descr;
        return $this;
    }

#end setDescr function

    public function getDescr() {
        return $this->_descr;
    }

#end getDescr function

    public function setQty_ship($qty_ship) {
        $this->_qty_ship = $qty_ship;
        return $this;
    }

#end setQty_ship function

    public function getQty_ship() {
        return $this->_qty_ship;
    }

#end getQty_ship function

    public function setItem_no($item_no) {
        $this->_item_no = $item_no;
        return $this;
    }

#end setItem_no function

    public function getItem_no() {
        return $this->_item_no;
    }

#end getItem_no function

    public function setCtn_qty($ctn_qty) {
        $this->_ctn_qty = $ctn_qty;
        return $this;
    }

#end setCtn_qty function

    public function getCtn_qty() {
        return $this->_ctn_qty;
    }

#end getCtn_qty function

    public function setUom($uom) {
        $this->_uom = $uom;
        return $this;
    }

#end setUom function

    public function getUom() {
        return $this->_uom;
    }

#end getUom function

    public function setLot_number($lot_number) {
        $this->_lot_number = $lot_number;
        return $this;
    }

#end setLot_number function

    public function getLot_number() {
        return $this->_lot_number;
    }

#end getLot_number function

    public function setExpiration_date($expiration_date) {
        $this->_expiration_date = $expiration_date;
        return $this;
    }

#end setExpiration_date function

    public function getExpiration_date() {
        return $this->_expiration_date;
    }

#end getExpiration_date function

    public function setBarcode_zip_text($barcode_zip_text) {
        $this->_barcode_zip_text = $barcode_zip_text;
        return $this;
    }

#end setBarcode_zip_text function

    public function getBarcode_zip_text() {
        return $this->_barcode_zip_text;
    }

#end getBarcode_zip_text function

    public function setBarcode_upc_text($barcode_upc_text) {
        $this->_barcode_upc_text = $barcode_upc_text;
        return $this;
    }

#end setBarcode_upc_text function

    public function getBarcode_upc_text() {
        return $this->_barcode_upc_text;
    }

#end getBarcode_upc_text function

    public function setStore_id($store_id) {
        $this->_store_id = $store_id;
        return $this;
    }

#end setBarcode_upc_text function

    public function getStore_id() {
        return $this->_store_id;
    }

#end getBarcode_upc_text function

    public function setLine_seq($line_seq) {
        $this->_line_seq = $line_seq;
        return $this;
    }

#end setLine_seq function

    public function getLine_seq() {
        return $this->_line_seq;
    }

#end getLine_seq function
}

?>