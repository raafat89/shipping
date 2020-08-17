<?php

// this module will serve as a decorator for the php cookie feature
// this module is implemented in order to allow custom flash messenger
// functionality that will allow for carrying messages over from one
// action to the other
// the class will implement the singleton pattern and act as an array

class Utility_FlashMessenger {

    private static $_name = "FLASH_MESSENGER";
    private static $_path = "/";
    private static $_time = 3600;

    // COOKIE DECORATOR FUNCTIONS --------------------------------------------------- //
    // destroy the current instance
    public static function _unsetMessenger() {
        // destroy the cookie if it is already set
        setcookie(self::$_name, "", time() - self::$_time, self::$_path);
        sleep(4);
    }

    // set the flash messenger value
    public static function addMessage($message) {
        $messages = self::popMessage();
        if (is_array($messages)) {
            $messages[] = $message;
        } else {
            $messages = array();
            $messages[] = $message;
        }

        setcookie(self::$_name, serialize($messages), time() + self::$_time, self::$_path);
    }

    // set the flash messenger values
    public static function addMessages($additions) {
        $messages = self::popMessage();
        if (is_array($messages)) {
            foreach ($additions as $message) {
                $messages[] = $message;
            }
        } else {
            $messages = $additions;
        }

        setcookie(self::$_name, serialize($messages), time() + self::$_time, self::$_path);
    }

    // return the flash messenger value
    public static function getMessage() {
        return unserialize($_COOKIE[self::$_name]);
    }

    // return the flash messenger value and reset the variable
    public static function popMessage() {
        if (isset($_COOKIE[self::$_name])) {
            $message = unserialize($_COOKIE[self::$_name]);
            setcookie(self::$_name, "", time() - self::$_time, self::$_path);
        } else {
            $message = NULL;
        }
        return $message;
    }

}

?>