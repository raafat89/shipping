<?php

class Atlas_Model_TestMethodsMapper {

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
            $this->setDbTable('Atlas_Model_DbTable_TestMethods');
        }
        return $this->_dbTable;
    }

#end getDbTable() function
    // save the attributes of a given db object
    public function save(Atlas_Model_TestMethods $testmethod) {
        // push the data into an array
        $data = $testmethod->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($test_method_id = $testmethod->getTest_method_id()) || $test_method_id == 0) {
            unset($data['test_method_id']);
            $test_method_id = $this->getDbTable()->insert($data);
            return $test_method_id;
        } else {
            $this->getDbTable()->update($data, array('test_method_id = ?' => $test_method_id));
            return $test_method_id;
        }
    }

#end save() function
    // remove a row from the database that matches the id given
    public function remove($test_method_id) {
        $this->getDbTable()->delete("test_method_id='$test_method_id'");
    }

#end remove() function
    // find a row in the database based on the primary key and set the values
    // in the db object given by the user
    public function find($test_method_id) {
        $testmethod = new Atlas_Model_TestMethods();

        // attempt to locate the row in the database
        // if it doesn't exist throw an exception
        $result = $this->getDbTable()->find($test_method_id);
        if (0 == count($result)) {
            throw new Exception("Given entry doesn't exist");
        }

        // get the data and push it to the object
        $row = $result->current();
        $testmethod->setOptions($row->toArray());

        return $testmethod;
    }

#end find() function
    // find all entries from the database for the given table
    public function fetchAll() {
        // gather all of the entries in the database
        // and push their values into an array
        $resultSet = $this->selectAll()->query()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Atlas_Model_TestMethods();
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
                ->from(array("tm" => "test_methods"), array("tm.test_method_id", "tm.test_method", "tm.active"))
                ->order("tm.test_method ASC");

        return $select;
    }

#end selectAll() function
    // return a select statement for the table
    public function buildAll() {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("tm" => "test_methods"), array("tm.test_method_id", "tm.test_method", "tm.active"))
                ->order("tm.test_method ASC");

        return $select->query()->fetchAll();
    }

#end buildAll() function

    public function buildIdByName($name) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("tm" => "test_methods"), array("tm.test_method_id"))
                ->where("tm.test_method LIKE '%" . $name . "%'");

        $result = $select->query()->fetchAll();
        return $result[0]['test_method_id'];
    }

#end buildIdByName() function
    // activate the test method
    public function activateTestMethod($test_method_id) {
        try {
            $testmethod = $this->find($test_method_id);
            $testmethod->setActive(1);
            $this->save($testmethod);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

#end activateTestMethod() function
    // deactivate the test method
    public function deactivateTestMethod($test_method_id) {
        try {
            $testmethod = $this->find($test_method_id);
            $testmethod->setActive(0);
            $this->save($testmethod);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

#end deactivateTestMethod() function

    public function checkLabel($test_method_id, $label) {
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "test_methods"), array('COUNT(*) AS count'))
                ->where("LOWER(t.test_method) = ?", strtolower($label))
                ->where('t.test_method_id != ?', $test_method_id);

        $result = $select->query()->fetchAll();
        if (is_array($result) && $result[0]['count'] > 0) {
            return false;
        } else {
            return true;
        }
    }

#end checkLabel function
    // process data from the test method form
    public function processForm($form_data) {
        if (!$this->checkLabel((int) $form_data['test_method_id'], $form_data['test_method'])) {
            throw new Exception("Duplicate test method not allowed.");
        }

        if (isset($form_data['test_method_id']) && trim($form_data['test_method_id']) != "") {
            try {
                $testmethod = $this->find($form_data['test_method_id']);
                $testmethod->setOptions($form_data);
                $this->save($testmethod);

                return true;
            } catch (Exception $e) {
                return false;
            }
        } else {
            $testmethod = new Atlas_Model_TestMethods();
            $testmethod->setOptions($form_data);
            $this->save($testmethod);

            return true;
        }
    }

#end processForm() function
}

?>