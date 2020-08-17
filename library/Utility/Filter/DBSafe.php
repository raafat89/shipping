<?php

// this class is a zend filter that will make the given input safe
// for sql insertion.

class Utility_Filter_DBSafe implements Zend_Filter_Interface {

    public function __construct() {
        return;
    }

    public static function makeDbSafe($value) {
        $value = trim($value);
        $value = str_replace("�", "", $value);
        $value = str_replace("", "", $value);
        $value = str_replace("\\", "&#92;", $value); // remove \
        $value = str_replace("'", "&#39;", $value); // remove '
        $value = str_replace("\"", "&#34;", $value); // remove "

        return $value;
    }

    public static function revert($value) {
        $value = str_replace("&#92;", "\\", $value); // replace \
        $value = str_replace("&#39;", "'", $value); // replace '
        $value = str_replace("&#34;", "\"", $value); // replace "

        return $value;
    }

    public function filter($value) {
        if (is_array($value)) {
            foreach ($value as $key => $element) {
                $value[$key] = $this->filter($element);
            }
        } else {
            return $this->makeDbSafe($value);
        }

        return $value;
    }

    public static function clean($value) {
        if (is_array($value)) {
            foreach ($value as $key => $element) {
                $value[$key] = self::clean($element);
            }
        } else {
            return self::makeDbSafe($value);
        }

        return $value;
    }

    public static function undoClean($value) {
        if (is_array($value)) {
            foreach ($value as $key => $element) {
                $value[$key] = self::undoClean($element);
            }
        } else {
            return self::revert($value);
        }

        return $value;
    }

}

?>