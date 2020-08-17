<?php

// this module will serve as a decorator for the php session feature
// this module is implemented in order to allow custom session handlers
// that do not require cookies in order to save and retrieve
// this allows for better security
// the class will implement the singleton pattern and act as an array

class Utility_Session {

    // holds the instance of the object
    private static $_session = null;
    // holds whether the session is read-only
    private static $_option = "R";
    // this will hold the private key for encryption
    protected static $_session_salt = "76439876_SESSION_SALT_65087965";
    protected static $_host = "";
    protected static $_key = "";

    // SESSION DECORATOR FUNCTIONS --------------------------------------------------- //
    // initializes our session handler with our custom options
    protected function __construct($id = NULL, $time = 60, $option = "R", $host = "", $key = "KEY") {
        ini_set("session.use_cookies", "0");
        ini_set("session.use_trans_sid", "0");
        ini_set("session.use_only_cookies", "1");
        ini_set("session.gc_maxlifetime", (8 * 60 * 60)); // set session clean up to 8 hours

        self::$_host = $host;
        self::$_key = $key;

        // if the session doesn't exist and an id was not provided
        // return NULL to show user is not logged in
        if ($id === NULL && !self::isSession()) {
            return NULL;
        }
        // if this is the first initilization of the session
        // set our custom session id
        if (!self::isSession()) {
            $sess_id = self::setSessionId(md5($id . "_" . self::$_session_salt), $time);
        } else {
            $sess_id = self::getSessionId();
        }

        // set up the session handler functions
        session_cache_expire($time);
        session_id($sess_id);
        session_start();
        if (file_exists(Zend_Registry::get("root_path") . "/sessions/sess_" . $sess_id)) {
            $session_data = file_get_contents(Zend_Registry::get("root_path") . "/sessions/sess_" . $sess_id);
        }
        if (file_exists(Zend_Registry::get("root_path") . "/sessions/sess_" . $sess_id) && count($_SESSION) <= 0) {
            session_decode(file_get_contents(Zend_Registry::get("root_path") . "/sessions/sess_" . $sess_id));
        }
        if (count($_SESSION) <= 0) {
            $return_data = array();
            $offset = 0;
            while ($offset < strlen($session_data)) {
                if (!strstr(substr($session_data, $offset), "|")) {
                    throw new Exception("invalid data, remaining: " . substr($session_data, $offset));
                }
                $pos = strpos($session_data, "|", $offset);
                $num = $pos - $offset;
                $varname = substr($session_data, $offset, $num);
                $offset += $num + 1;
                $data = unserialize(substr($session_data, $offset));
                $return_data[$varname] = $data;
                $offset += strlen(serialize($data));
            }
            $_SESSION = $return_data;
        }
        self::$_option = strtoupper($option);

        // commit the session if it has been set as read-only
        if (self::$_option == "R") {
            session_write_close();
        }
    }

    // returns the instance of the object if the sessions has
    // already been initialized
    public static function getInstance($id = NULL, $time = 60, $option = "R", $host = "", $key = "KEY") {
        // make sure a valid option was given
        if (strtoupper($option) != "R" && strtoupper($option) != "W") {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception('Invalid option given to the session object');
        }
        // if the instance doesn't exist create one
        if (self::$_session === null) {
            self::$_session = new Utility_Session($id, $time, $option, $host, $key);
        }

        return self::$_session;
    }

    // destroy the current instance
    public static function _unsetSession() {
        // destroy the session, the cookie and the object instance
        if (self::isSession()) {
            $sess_id = self::getSessionId();
            session_id($sess_id);
            session_start();
            session_destroy();
            exec("/bin/rm -rf " . Zend_Registry::get("root_path") . "/sessions/sess_" . $sess_id);
            unset($_COOKIE[self::$_key]);
            setcookie(self::$_key, "0", time() - 3600, "/", self::$_host);
        }

        self::$_session = null;
        sleep(4);
    }

    // commit the changes to the session
    public function save() {
        if (self::$_option != "W") {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception("The session object is read only, changes can't be committed");
        }

        session_write_close();
        self::$_option = "R";
        sleep(4);
    }

    // this will return the value for the given index
    public function get($index) {
        if (!isset($_SESSION[$index])) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception("No entry is saved for key '$index'");
        }
        return $_SESSION[$index];
    }

    // return the current session id
    public static function getSessionId() {
        if (self::isSession()) {
            return $_COOKIE[self::$_key];
        } else {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception("Session doesn't exist");
        }
    }

    // this will return an associative array of all the saved values
    public function getAssocArray() {
        $results = NULL;
        if (self::isSession()) {
            $results = array();
            foreach ($_SESSION as $index => $value) {
                $results[$index] = $value;
            }
        }

        return $results;
    }

    // this will set the value of the given index 
    public function set($index, $value) {
        if (self::$_option != "W") {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception("The session object is read only, changes can't be committed");
        }

        $_SESSION[$index] = $value;
    }

    // setup a cookie to hold the session id
    protected function setSessionId($key, $time) {
        setcookie(self::$_key, $key, time() + ($time * 60), "/", self::$_host);

        return $key;
    }

    // this will return a boolean based on wheter or not the
    // index is saved in the object
    public function isSaved($index) {
        if (!isset($_SESSION[$index])) {
            return false;
        } else {
            return true;
        }
    }

    // this will return a boolean based on wheter or not the
    // session is set alreadys
    public static function extendSession($time) {
        setcookie(self::$_key, $_COOKIE[self::$_key], time() + ($time * 60), "/", self::$_host);
    }

    // this will return a boolean based on wheter or not the
    // session is set already
    public static function isSession() {
        if (!isset($_COOKIE[self::$_key]) || $_COOKIE[self::$_key] == "0") {
            return false;
        } else {
            return true;
        }
    }

}

?>