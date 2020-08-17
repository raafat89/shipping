<?php

class Utility_FTPConnection {

    protected $_connection_id;
    protected $_host_url;
    protected $_username;
    protected $_password;
    protected $_port;

    public function __construct($options = NULL) {
        $settings = Zend_Registry::get("ftpjarrow");
        $params = $settings->ftp->params;
        $this->_host_url = $params->host;      //FTP Server IP
        $this->_username = $params->username;  //FTP Server Username
        $this->_password = $params->password;  //FTP Server Pass
        $this->_port = $params->port;      //FTP Port

        $this->_connection_id = ftp_connect($this->_host_url, $this->_port);
        if ($this->_connection_id === false) {
            throw new Exception("Could not connect to the FTP Server.");
        }
        $login_result = ftp_login($this->_connection_id, $this->_username, $this->_password);
        if ($this->_connection_id === false) {
            throw new Exception("Could not log into the FTP Server.");
        }
    }

    public function __destruct() {
        $this->kill();
    }

    public function uploadFile($local_file, $remote_file) {
        $upload = ftp_put($this->_connection_id, $remote_file, $local_file, FTP_BINARY);
        if (!$upload) {
            throw new Exception("File was not uploaded to the FTP server.");
        }
        if (ftp_chmod($this->_connection_id, 0666, $remote_file) == false) {
            throw new Exception("File was not properly modified");
        }
    }

    public function deleteFile($remote_file) {
        $delete_result = ftp_delete($this->_connection_id, $remote_file);
    }

    public function kill() {
        ftp_close($this->_connection_id);
    }

}

?>