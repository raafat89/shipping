<?php

class Atlas_Model_AccessLogMapper {

    protected $_dbTable;

    // set the default db handle
    public function setDbTable($dbTable) {
        // if a string was given return an object
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        // ensure the dbTable is of the correct instance
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data object provided');
        }

        // set the db table and return the handle
        $this->_dbTable = $dbTable;
        return $this;
    }

#end setDbTable() function
    // return the default db handle
    public function getDbTable() {
        // if the object is not set, set it and return it
        if (NULL === $this->_dbTable) {
            $this->setDbTable('Atlas_Model_DbTable_AccessLog');
        }
        return $this->_dbTable;
    }

#end getDbTable() function
    // save the attributes of a given db object
    public function save(Atlas_Model_AccessLog $log) {
        // push the data into an array
        $data = $log->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($access_log_id = $log->getAccess_log_id()) || $access_log_id == 0) {
            unset($data['access_log_id']);
            $access_log_id = $this->getDbTable()->insert($data);
            return $access_log_id;
        } else {
            $this->getDbTable()->update($data, array('access_log_id = ?' => $access_log_id));
            return $access_log_id;
        }
    }

#end save() function
    // remove a row from the database that matches the id given
    public function remove($access_log_id) {
        $this->getDbTable()->delete("access_log_id='$access_log_id'");
    }

#end remove() function
    // find a row in the database based on the primary key and set the values
    // in the db object given by the user
    public function find($access_log_id) {
        $log = new Atlas_Model_AccessLog();

        // attempt to locate the row in the database
        // if it doesn't exist throw an exception
        $result = $this->getDbTable()->find($access_log_id);
        if (0 == count($result)) {
            throw new Exception("Given entry doesn't exist");
        }

        // get the data and push it to the object
        $row = $result->current();
        $log->setOptions($row->toArray());

        return $log;
    }

#end find() function
    // find all entries from the database for the given table
    public function fetchAll() {
        // gather all of the entries in the database
        // and push their values into an array
        $resultSet = $this->selectAll()->query()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Atlas_Model_AccessLog();
            $entry->setOptions($row);

            $entries[] = $entry;
        }

        // return the results
        return $entries;
    }

#end fetchAll() function
    // transform a select statement into a result set
    public function fetch($select = NULL) {
        if ($select != NULL) {
            return $select->query()->fetchAll();
        } else {
            return array();
        }
    }

#end fetch() function
    // return a select statement for the table
    public function selectAll() {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("al" => "access_log"), array("al.access_log_id", "al.timestamp", "al.user_id", "al.ip_address", "al.message"))
                ->order("al.timestamp DESC");

        return $select;
    }

#end selectAll() function
    // return a select statement building an access log sheet
    public function getAccessLogs($search = "", $date = "") {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("al" => "access_log"), array("al.access_log_id", "al.timestamp", "al.user_id", "al.ip_address", "al.message",
                    "u.username", "u.name"))
                ->join(array("u" => "users"), "al.user_id=u.user_id", array());
        if (trim($search) != "") {
            $select->where("u.name LIKE '%" . $search . "%' OR u.username LIKE '%" . $search . "%' OR al.message LIKE '%" . $search . "%'");
        }
        if (trim($date) != "") {
            $date = date("Y-m-d", strtotime($date));
            $select->where("al.timestamp >= '" . $date . " 00:00:00' AND al.timestamp <= '" . $date . " 23:59:59'");
        }
        $select->order("al.timestamp DESC");

        return $select;
    }

#end getAccessLog() function
    // check failed attempts from this IP adress 
    public function getFailedAttempts($ip = null) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("al" => "access_log"), array("COUNT(*) as failed_attempts"))
                ->where("al.user_id = 98")
                ->where("al.ip_address = ?", $ip)
                ->where("SUBSTRING_INDEX(al.message,'user:',-1) = '' ")
                ->where("DATE(al.timestamp) = CURDATE()");

        $result = $select->query()->fetch();

        if ($result['failed_attempts'] >= 6) {
            return false;
        } else {
            return true;
        }
    }

#end getFailedAttempts() function
}

?>