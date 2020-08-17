<?php

class Utility_SFTPConnectionSg {

    protected $_connection_id;

    public function __construct() {
        require_once(Zend_Registry::get("root_path") . "/library/phpseclib/Net/SFTP.php");
        $settings = Zend_Registry::get("sgftp");
        $params = $settings->ftp->params;
        $host = $params->host;      //FTP Server IP
        $user = $params->username;  //FTP Server Username
        $password = $params->password;  //FTP Server Pass

        $this->_connection_id = new Net_SFTP($host);
        if (!$this->_connection_id->login($user, $password)) {
            exit('SFTP Connection Failed');
        }
    }

    public function buildSftpConnectionSg() {
        return $this->_connection_id;
    }

}

?>