<?php

class Atlas_Model_PCStatusReasonsMapper {

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
            $this->setDbTable('Atlas_Model_DbTable_PCStatusReasons');
        }
        return $this->_dbTable;
    }

#end getDbTable() function
    // save the attributes of a given db object
    public function save(Atlas_Model_PCStatusReasons $entry) {
        // push the data into an array
        $data = $entry->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($reason_id = $entry->getReason_id()) || $reason_id == 0) {
            unset($data['reason_id']);
            $reason_id = $this->getDbTable()->insert($data);
            return $reason_id;
        } else {
            $this->getDbTable()->update($data, array('reason_id = ?' => $reason_id));
            return $reason_id;
        }
    }

#end save() function
    // remove a row from the database that matches the id given
    public function remove($reason_id) {
        $this->getDbTable()->delete("reason_id='$reason_id'");
    }

#end remove() function
    // find a row in the database based on the primary key and set the values
    // in the db object given by the user
    public function find($reason_id) {
        $status = new Atlas_Model_PCStatusReasons();

        // attempt to locate the row in the database
        // if it doesn't exist throw an exception
        $result = $this->getDbTable()->find($reason_id);
        if (0 == count($result)) {
            throw new Exception("Given entry doesn't exist");
        }

        // get the data and push it to the object
        $row = $result->current();
        $status->setOptions($row->toArray());

        return $status;
    }

#end find() function
    // find all entries from the database for the given table
    public function fetchAll() {
        // gather all of the entries in the database
        // and push their values into an array
        $resultSet = $this->selectAll()->query()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Atlas_Model_PCStatusReasons();
            $entry->setOptions($row->toArray());

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
                ->from(array("s" => "pc_status_reasons"), array("s.reason_id", "s.reason", "s.status"));

        return $select;
    }

#end selectAll() function

    public function buildStatuses() {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("s" => "pc_status_reasons"), array("s.reason_id", "s.reason"))
                ->where("s.status = ?", 1)
                ->order(array("s.status ASC"));

        return $select->query()->fetchAll();
    }

#end buildStatuses() function

    public function activateStatus($reason_id) {
        $entry = $this->find($reason_id);
        $entry->setStatus(1);
        $this->save($entry);
    }

#end activateStatus function

    public function deactivateStatus($reason_id) {
        $entry = $this->find($reason_id);
        $entry->setStatus(0);
        $this->save($entry);
    }

#end deactivateStatus

    //
        //
	public function checkLabel($entry_id, $label) {

        $select = $this->getDbTable()->select();
        $select->from(array("t" => "pc_status_reasons"), array('COUNT(*) AS count'))
                ->where('LOWER(t.reason) = ?', $label)
                ->where('t.reason_id != ?', $entry_id);

        $result = $select->query()->fetchAll();
        if (is_array($result) && $result[0]['count'] > 0) {
            return false;
        } else {
            return true;
        }
    }

#end checkLabel function
    // process data from the unit form
    public function processForm($form_data) {
        if ($form_data == NULL) {
            throw new Exception("No data given to the model for processing.");
        }
        if (!$this->checkLabel((int) $form_data['reason_id'], $form_data['reason'])) {
            throw new Exception("Duplicate reason not allowed.");
        }

        if ((int) $form_data['reason_id'] > 0) {
            try {
                $bsize = $this->find($form_data['reason_id']);
                $bsize->setOptions($form_data);
                $this->save($bsize);

                return true;
            } catch (Exception $e) {
                return false;
            }
        } else {
            $bsize = new Atlas_Model_PCStatusReasons();
            $bsize->setOptions($form_data);
            $this->save($bsize);

            return true;
        }
    }

#end processForm() function
}

?>