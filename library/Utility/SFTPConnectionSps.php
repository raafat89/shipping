<?php

class Utility_SFTPConnectionSps {

    protected $_connection_id;
    protected $_direction;

    public function __construct() {
        require_once(Zend_Registry::get("root_path") . "/library/phpseclib/Net/SFTP.php");
        $settings = Zend_Registry::get("spsftp");
        $params = $settings->ftp->params;
        $host = $params->host;      //FTP Server IP
        $user = $params->username;  //FTP Server Username
        $password = $params->password;  //FTP Server Pass

        $this->_connection_id = new Net_SFTP($host);
        if (!$this->_connection_id->login($user, $password)) {
            print_r($this->_connection_id->errors);
            exit('SFTP Connection Failed');
        }
        $this->_connection_id->setTimeout(0);
    }

    public function buildSftpConnectionSps($direction) {
        $this->_direction = $direction;
        if ($this->_direction == 'out') {
            if (APPLICATION_ENV == 'production') {
                $this->_connection_id->chdir('out');
            } else {
                $this->_connection_id->chdir('testout');
            }
        } else if ($this->_direction == 'in') {
            if (APPLICATION_ENV == 'production') {
                $this->_connection_id->chdir('in');
            } else {
                $this->_connection_id->chdir('testin');
            }
        }
        return $this->_connection_id;
    }

}
