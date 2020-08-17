<?php

class Atlas_Model_StorageConditionsMapper {

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
            $this->setDbTable('Atlas_Model_DbTable_StorageConditions');
        }
        return $this->_dbTable;
    }

#end getDbTable() function
    // save the attributes of a given db object
    public function save(Atlas_Model_StorageConditions $condition) {
        // push the data into an array
        $data = $condition->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($storage_condition_id = $condition->getStorage_condition_id()) || $storage_condition_id == 0) {
            unset($data['storage_condition_id']);
            $storage_condition_id = $this->getDbTable()->insert($data);
            return $storage_condition_id;
        } else {
            $this->getDbTable()->update($data, array('storage_condition_id = ?' => $storage_condition_id));
            return $storage_condition_id;
        }
    }

#end save() function
    // remove a row from the database that matches the id given
    public function remove($storage_condition_id) {
        $this->getDbTable()->delete("storage_condition_id='$storage_condition_id'");
    }

#end remove() function
    // find a row in the database based on the primary key and set the values
    // in the db object given by the user
    public function find($storage_condition_id) {
        $condition = new Atlas_Model_StorageConditions();

        // attempt to locate the row in the database
        // if it doesn't exist throw an exception
        $result = $this->getDbTable()->find($storage_condition_id);
        if (0 == count($result)) {
            throw new Exception("Given entry doesn't exist");
        }

        // get the data and push it to the object
        $row = $result->current();
        $condition->setOptions($row->toArray());

        return $condition;
    }

#end find() function
    // find all entries from the database for the given table
    public function fetchAll() {
        // gather all of the entries in the database
        // and push their values into an array
        $resultSet = $this->selectAll()->query()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Atlas_Model_StorageConditions();
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
                ->from(array("sc" => "storage_conditions"), array("sc.storage_condition_id", "sc.storage_condition"))
                ->order("sc.storage_condition ASC");

        return $select;
    }

#end selectAll() function

    public function buildIdByName($name) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("sc" => "storage_conditions"), array("sc.storage_condition_id"))
                ->where("sc.storage_condition LIKE '%" . $name . "%'");

        $result = $select->query()->fetchAll();
        return $result[0]['storage_condition_id'];
    }

#end buildIdByName() function

    public function checkLabel($entry_id, $label) {
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "storage_conditions"), array('COUNT(*) AS count'))
                ->where("LOWER(t.storage_condition) = ?", strtolower($label))
                ->where('t.storage_condition_id != ?', $entry_id);

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
        if (!$this->checkLabel((int) $form_data['storage_condition_id'], $form_data['storage_condition'])) {
            throw new Exception("Duplicate condition not allowed.");
        }

        if ((int) $form_data['storage_condition_id'] > 0) {
            try {
                $bsize = $this->find($form_data['storage_condition_id']);
                $bsize->setOptions($form_data);
                $this->save($bsize);

                return true;
            } catch (Exception $e) {
                return false;
            }
        } else {
            $bsize = new Atlas_Model_StorageConditions();
            $bsize->setOptions($form_data);
            $this->save($bsize);

            return true;
        }
    }

#end processForm() function
}

?>