<?php

class Atlas_Model_ProductExtraInfo {

    protected $_product_id;
    protected $_upc;
    protected $_case_upc;
    protected $_strength;
    protected $_str_uom;
    protected $_size;
    protected $_size_uom;
    protected $_product_code;
    protected $_height;
    protected $_width;
    protected $_depth;
    protected $_weight;
    protected $_qty_case;
    protected $_case_weight;

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
            throw new Exception('Invalid product_extra_info property used');
        }
        $this->$method($value);
    }

#end __set function

    public function __get($name) {
        // if an unknown variable is used throw exception
        $method = 'get' . $name;
        if (!method_exists($this, $method)) {
            throw new Exception('Invalid product_extra_info property used');
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

    public function setProduct_id($product_id) {
        $this->_product_id = $product_id;
        return $this;
    }

#end setProduct_id function

    public function getProduct_id() {
        return $this->_product_id;
    }

#end getProduct_id function

    public function setUpc($upc) {
        $this->_upc = $upc;
        return $this;
    }

#end setUpc function

    public function getUpc() {
        return $this->_upc;
    }

#end getUpc function

    public function setCase_upc($case_upc) {
        $this->_case_upc = $case_upc;
        return $this;
    }

#end setCase_upc function

    public function getCase_upc() {
        return $this->_case_upc;
    }

#end getCase_upc function

    public function setStrength($strength) {
        $this->_strength = $strength;
        return $this;
    }

#end setStrength function

    public function getStrength() {
        return $this->_strength;
    }

#end getStrength function

    public function setStr_uom($str_uom) {
        $this->_str_uom = $str_uom;
        return $this;
    }

#end setStr_uom function

    public function getStr_uom() {
        return $this->_str_uom;
    }

#end getStr_uom function

    public function setSize($size) {
        $this->_size = $size;
        return $this;
    }

#end setSize function

    public function getSize() {
        return $this->_size;
    }

#end getSize function

    public function setSize_uom($size_uom) {
        $this->_size_uom = $size_uom;
        return $this;
    }

#end setSize_uom function

    public function getSize_uom() {
        return $this->_size_uom;
    }

#end getSize_uom function

    public function setProduct_code($product_code) {
        $this->_product_code = $product_code;
        return $this;
    }

#end setProduct_code function

    public function getProduct_code() {
        return $this->_product_code;
    }

#end getProduct_code function

    public function setHeight($height) {
        $this->_height = $height;
        return $this;
    }

#end setHeight function

    public function getHeight() {
        return $this->_height;
    }

#end getHeight function

    public function setWidth($width) {
        $this->_width = $width;
        return $this;
    }

#end setWidth function

    public function getWidth() {
        return $this->_width;
    }

#end getWidth function

    public function setDepth($depth) {
        $this->_depth = $depth;
        return $this;
    }

#end setDepth function

    public function getDepth() {
        return $this->_depth;
    }

#end getDepth function

    public function setWeight($weight) {
        $this->_weight = $weight;
        return $this;
    }

#end setWeight function

    public function getWeight() {
        return $this->_weight;
    }

#end getWeight function

    public function setQty_case($qty_case) {
        $this->_qty_case = $qty_case;
        return $this;
    }

#end setQty_case function

    public function getQty_case() {
        return $this->_qty_case;
    }

#end getQty_case function

    public function setCase_weight($case_weight) {
        $this->_case_weight = $case_weight;
        return $this;
    }

#end setQty_case function

    public function getCase_weight() {
        return $this->_case_weight;
    }

#end getQty_case function

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