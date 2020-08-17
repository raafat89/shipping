<?php

// this class will take an a zend error array and return a usable
// array for usage in the message

class Utility_Error {

    protected function __construct() {
        return;
    }

    // return an array with the proper error messages
    public static function buildErrors($errors = NULL) {
        // ensure the given object is infact a workable array
        if ($errors === NULL || !is_array($errors)) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception('You must give the object a zend error message');
        }

        // build the error array
        $results = array();
        $results[0] = "<div class='error'><table>";
        foreach ($errors as $error_name => $error) {
            $error_num = 0;
            foreach ($errors[$error_name] as $error_type => $message) {
                // build a formatted error label by replacing the _ with
                // a space and capitalizing the first letter of each word
                $split_values = preg_split("/_/", $error_name);
                $error_name = "";
                foreach ($split_values as $value) {
                    $error_name .= " " . ucfirst($value);
                }

                // push the error message into the array
                $results[0] .= "<tr><td>" . trim($error_name) . ":</td><td>" . $message . "</td></tr>";
                ++$error_num;
            }
        }
        $results[0] .= "</table></div>";

        return $results;
    }

}

?>