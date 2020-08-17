<?php

class Atlas_Model_LabsMapper {

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
            $this->setDbTable('Atlas_Model_DbTable_Labs');
        }
        return $this->_dbTable;
    }

#end getDbTable() function
    // save the attributes of a given db object
    public function save(Atlas_Model_Labs $lab) {
        // push the data into an array
        $data = $lab->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($lab_id = $lab->getLab_id()) || $lab_id == 0) {
            unset($data['lab_id']);
            $lab_id = $this->getDbTable()->insert($data);
            return $lab_id;
        } else {
            $this->getDbTable()->update($data, array('lab_id = ?' => $lab_id));
            return $lab_id;
        }
    }

#end save() function
    // remove a row from the database that matches the id given
    public function remove($lab_id) {
        $this->getDbTable()->delete("lab_id='$lab_id'");
    }

#end remove() function
    // find a row in the database based on the primary key and set the values
    // in the db object given by the user
    public function find($lab_id) {
        $lab = new Atlas_Model_Labs();

        // attempt to locate the row in the database
        // if it doesn't exist throw an exception
        $result = $this->getDbTable()->find($lab_id);
        if (0 == count($result)) {
            throw new Exception("Given entry doesn't exist");
        }

        // get the data and push it to the object
        $row = $result->current();
        $lab->setOptions($row->toArray());

        return $lab;
    }

#end find() function
    // find all entries from the database for the given table
    public function fetchAll() {
        // gather all of the entries in the database
        // and push their values into an array
        $resultSet = $this->selectAll()->query()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Atlas_Model_Labs();
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
                ->from(array("l" => "labs"), array("l.lab_id", "l.lab_name", "l.lab_active"))
                ->order("l.lab_name ASC");

        return $select;
    }

#end selectAll() function
    // return a select statement for the table
    public function buildAll() {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("l" => "labs"), array("l.lab_id", "l.lab_name", "l.lab_active"))
                ->order("l.lab_name ASC");

        return $select->query()->fetchAll();
    }

#end buildAll() function
    // return a select statement for the table
    public function getActiveLabs() {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("l" => "labs"), array("l.lab_id", "l.lab_name", "l.lab_active"))
                ->where("l.lab_active = ?", 1)
                ->order("l.lab_name ASC");

        return $select;
    }

#end getActiveLabs() function

    public function buildActiveLabs() {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("l" => "labs"), array("l.lab_id", "l.lab_name", "l.lab_active"))
                ->where("l.lab_active = ?", 1)
                ->order("l.lab_name ASC");

        return $select->query()->fetchAll();
    }

#end buildActiveLabs() function
    // activate the lab
    public function activateLab($lab_id) {
        try {
            $lab = $this->find($lab_id);
            $lab->setLab_active(1);
            $this->save($lab);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

#end activateLab() function
    // deactivate the lab
    public function deactivateLab($lab_id) {
        try {
            $lab = $this->find($lab_id);
            $lab->setLab_active(0);
            $this->save($lab);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

#end deactivateLab() function

    public function checkLabel($lab_id, $label) {
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "labs"), array('COUNT(*) AS count'))
                ->where("LOWER(t.lab_name) = ?", strtolower($label))
                ->where('t.lab_id != ?', $lab_id);

        $result = $select->query()->fetchAll();
        if (is_array($result) && $result[0]['count'] > 0) {
            return false;
        } else {
            return true;
        }
    }

#end checkLabel function
    // process data from the lab form
    public function processForm($form_data) {
        if (!$this->checkLabel((int) $form_data['lab_id'], $form_data['lab_name'])) {
            throw new Exception("Duplicate lab not allowed.");
        }

        if (isset($form_data['lab_id']) && trim($form_data['lab_id']) != "") {
            try {
                $lab = $this->find($form_data['lab_id']);
                $lab->setOptions($form_data);
                $this->save($lab);

                return true;
            } catch (Exception $e) {
                return false;
            }
        } else {
            $lab = new Atlas_Model_Labs();
            $lab->setOptions($form_data);
            $this->save($lab);

            return true;
        }
    }

#end processForm() function
}

?>