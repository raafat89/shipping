<?php

class Utility_CURLConnection {

    protected $_connection_id;
    protected $_host_url;
    protected $_username;
    protected $_password;

    public function __construct($options = NULL) {
        $settings = Zend_Registry::get("curljarrow");
        $params = $settings->curl->params;
        $this->_host_url = $params->host;
        $this->_username = $params->username;
        $this->_password = $params->password;
    }

    public function uploadFileViaRedirect($local_file, $url) {
        if (!is_file($local_file) || !is_readable($local_file)) {
            throw new Exception("Invalid file provided");
        } $cFile = curl_file_create($local_file);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $post = array("file" => $cFile);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        if (($response = curl_exec($ch)) === false) {
            throw new Exception("Upload Failed: unable to connect to the server. URL: " . $url . ".<br />Error: [" . curl_error($ch) . "][" . curl_errno($ch) . "]");
        }

        if ($response != "SUCCESS") {
            switch (substr($response, 0, 12)) {
                case "FAILURE: [0]":
                    throw new Exception("Upload Failed: upload can only be done from Atlas server. Response: " . $response);
                    break;
                case "FAILURE: [1]":
                    throw new Exception("Upload Failed: took too long to run or you are using a script that was ran previously.");
                    break;
                case "FAILURE: [2]":
                    throw new Exception("Upload Failed: file wasn't transfered to server properly.");
                    break;
                case "FAILURE: [3]":
                    throw new Exception("Upload Failed: file could not be uploaded.");
                    break;
            }
        }
    }

    public function deleteFileViaRedirect($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $post = array();
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        if (($response = curl_exec($ch)) === false) {
            throw new Exception("Upload Failed: unable to connect to the server. URL: " . $url . ".<br />Error: " . $response);
        }

        if ($response != "SUCCESS") {
            switch (substr($response, 0, 12)) {
                case "FAILURE: [0]":
                    throw new Exception("Deletion Failed: delete can only be done from Atlas server. Response: " . $response);
                    break;
                case "FAILURE: [1]":
                    throw new Exception("Deletion Failed: took too long to run or you are using a script that was ran previously.");
                    break;
            }
        }
    }

    public function uploadFileViaSFTP($local_file, $remote_file) {
        $ch = curl_init();
        $fp = fopen($local_file, 'r');
        curl_setopt($ch, CURLOPT_URL, 'sftp://' . $this->_username . ':' . $this->_password . '@' . $this->_host_url . $remote_file);
        curl_setopt($ch, CURLOPT_UPLOAD, 1);
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($local_file));
        curl_exec($ch);

        $error_no = curl_errno($ch);
        curl_close($ch);
        if ($error_no != 0) {
            throw new Exception("File upload failed: " . $error_no);
        }
    }

    public function deleteFileViaSFTP($remote_file) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'sftp://' . $this->_username . ':' . $this->_password . '@' . $this->_host_url . $remote_file);
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
        curl_setopt($ch, CURLOPT_QUOTE, array("DELE /path/to/file.ext"));
        curl_exec($ch);

        $error_no = curl_errno($ch);
        curl_close($ch);
        if ($error_no != 0) {
            throw new Exception("File delete failed: " . $error_no);
        }
    }

}

?>