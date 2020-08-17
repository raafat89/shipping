<?php

class Atlas_Model_CaptabPackages {

    protected $_id;
    protected $_name;
    protected $_code;
    protected $_captab_type;
    protected $_captab_color;
    protected $_captab_size;
    protected $_captab_weight;
    protected $_captab_weight_unit;
    protected $_capsule_shell_weight;
    protected $_capsule_shell_weight_unit;
    protected $_vegan;
    protected $_gelatin_capsule;
    protected $_active;

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

    public function setName($name) {
        $this->_name = $name;
        return $this;
    }

#end setName function

    public function getName() {
        return $this->_name;
    }

#end getName function

    public function setCode($code) {
        $this->_code = $code;
        return $this;
    }

#end setCode function

    public function getCode() {
        return $this->_code;
    }

#end getCode function

    public function setCaptab_type($captab_type) {
        $this->_captab_type = $captab_type;
        return $this;
    }

#end setCaptab_type function

    public function getCaptab_type() {
        return $this->_captab_type;
    }

#end getCaptab_type function

    public function setCaptab_color($captab_color) {
        $this->_captab_color = $captab_color;
        return $this;
    }

#end setCaptab_color function

    public function getCaptab_color() {
        return $this->_captab_color;
    }

#end getCaptab_color function

    public function setCaptab_size($captab_size) {
        $this->_captab_size = $captab_size;
        return $this;
    }

#end setCaptab_size function

    public function getCaptab_size() {
        return $this->_captab_size;
    }

#end getCaptab_size function

    public function setCaptab_weight($captab_weight) {
        $this->_captab_weight = $captab_weight;
        return $this;
    }

#end setCaptab_weight function

    public function getCaptab_weight() {
        return $this->_captab_weight;
    }

#end getCaptab_weight function

    public function setCaptab_weight_unit($captab_weight_unit) {
        $this->_captab_weight_unit = $captab_weight_unit;
        return $this;
    }

#end setCaptab_weight_unit function

    public function getCaptab_weight_unit() {
        return $this->_captab_weight_unit;
    }

#end getCaptab_weight_unit function

    public function setCapsule_shell_weight($capsule_shell_weight) {
        $this->_capsule_shell_weight = $capsule_shell_weight;
        return $this;
    }

#end setCapsule_shell_weight function

    public function getCapsule_shell_weight() {
        return $this->_capsule_shell_weight;
    }

#end getCapsule_shell_weight function

    public function setCapsule_shell_weight_unit($capsule_shell_weight_unit) {
        $this->_capsule_shell_weight_unit = $capsule_shell_weight_unit;
        return $this;
    }

#end setCapsule_shell_weight_unit function

    public function getCapsule_shell_weight_unit() {
        return $this->_capsule_shell_weight_unit;
    }

#end getCapsule_shell_weight_unit function

    public function setVegan($vegan) {
        $this->_vegan = $vegan;
        return $this;
    }

#end setActive function

    public function getVegan() {
        return $this->_vegan;
    }

#end getActive function

    public function setActive($active) {
        $this->_active = $active;
        return $this;
    }

#end setActive function

    public function getActive() {
        return $this->_active;
    }

#end getActive function

    public function setGelatin_capsule($gelatin_capsule) {
        $this->_gelatin_capsule = $gelatin_capsule;
        return $this;
    }

#end setGelatin_capsule function

    public function getGelatin_capsule() {
        return $this->_gelatin_capsule;
    }

#end getGelatin_capsule function
}

?>