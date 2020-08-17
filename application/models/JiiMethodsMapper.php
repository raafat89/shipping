<?php

class Atlas_Model_JiiMethodsMapper {

    protected $_dbTable;

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

#end setDbTable function

    public function getDbTable() {
        // if the object is not set, set it and return it
        if (NULL === $this->_dbTable) {
            $this->setDbTable('Atlas_Model_DbTable_JiiMethods');
        }

        return $this->_dbTable;
    }

#end getDbTable function

    public function save(Atlas_Model_JiiMethods $entry) {
        // push the data into an array
        $data = $entry->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($jii_method_id = $entry->getJii_method_id()) || $jii_method_id == 0) {
            unset($data["jii_method_id"]);
            $jii_method_id = $this->getDbTable()->insert($data);
            return $jii_method_id;
        } else {
            $this->getDbTable()->update($data, array("jii_method_id = ?" => $jii_method_id));
            return $jii_method_id;
        }
    }

#end save function

    public function remove($jii_method_id) {
        $this->getDbTable()->delete("jii_method_id='$jii_method_id'");
    }

#end remove function

    public function fetchAll() {
        // gather all of the entries in the database
        // and push their values into an array
        $resultSet = $this->selectAll()->query()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Atlas_Model_JiiMethods();
            $entry->setOptions($row);

            $entries[] = $entry;
        }

        // return the results
        return $entries;
    }

#end fetchAll function

    public function find($id) {
        $entry = new Atlas_Model_JiiMethods();

        // attempt to locate the row in the database
        // if it doesn't exist throw an exception
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            throw new Exception("Given entry doesn't exist");
        }

        // get the data and push it to the object
        $row = $result->current();
        $entry->setOptions($row->toArray());

        return $entry;
    }

#end find() function

    public function selectAll() {
        // create a select statement for gathering all account keys
        $select = $this->getDbTable()->select();
        $select->from(array('t' => 'jii_methods'), array('t.jii_method_id', 't.name', 't.code'))
                ->order(array('t.name ASC'));

        // return the select statement	
        return $select;
    }

#end selectAll function

    public function buildIdByName($name) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "jii_methods"), array("t.jii_method_id", "t.name", "t.code"))
                ->where("t.name LIKE '%" . $name . "%'");

        $result = $select->query()->fetchAll();
        return $result[0]['jii_method_id'];
    }

#end buildIdByName() function

    public function activateMethod($jii_method_id) {
        try {
            $data = $this->find($jii_method_id);
            $data->setActive(1);
            $this->save($data);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

#end activateJiiMethod() function

    public function deactivateMethod($jii_method_id) {
        try {
            $data = $this->find($jii_method_id);
            $data->setActive(0);
            $this->save($data);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

#end deactivateJiiMethod() function
    // process data from the jii method form
    public function processForm($form_data) {
        if (isset($form_data['jii_method_id']) && trim($form_data['jii_method_id']) != "") {
            try {
                $data = $this->find($form_data['jii_method_id']);
                $data->setOptions($form_data);
                $this->save($data);

                return true;
            } catch (Exception $e) {
                return false;
            }
        } else {
            $data = new Atlas_Model_JiiMethods();
            $data->setOptions($form_data);
            $this->save($data);

            return true;
        }
    }

#end processForm() function
}

?>