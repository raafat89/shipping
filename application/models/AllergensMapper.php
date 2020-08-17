<?php

class Atlas_Model_AllergensMapper {

    protected $_dbTable;

    public function setDbTable($dbTable) {
        // if a string was given return an object
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        // ensure the dbTable is of the correct instance
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception("Invalid table data object provided");
        }

        // set the db table and return the handle
        $this->_dbTable = $dbTable;
        return $this;
    }

#end setDbTable function

    public function getDbTable() {
        // if the object is not set, set it and return it
        if (NULL === $this->_dbTable) {
            $this->setDbTable("Atlas_Model_DbTable_Allergens");
        }

        return $this->_dbTable;
    }

#end getDbTable function

    public function save(Atlas_Model_Allergens $entry) {
        // push the data into an array
        $data = $entry->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($allergens_id = $entry->getAllergens_id()) || $allergens_id == 0) {
            unset($data["allergens_id"]);
            $allergens_id = $this->getDbTable()->insert($data);
            return $allergens_id;
        } else {
            $this->getDbTable()->update($data, array("allergens_id = ?" => $allergens_id));
            return $allergens_id;
        }
    }

#end save function

    public function remove($allergens_id) {
        $this->getDbTable()->delete("allergens_id='$allergens_id'");
    }

#end remove function

    public function find($allergens_id) {
        $entry = new Atlas_Model_Allergens();

        // attempt to locate the row in the database
        // if it doesn"t exist return NULL
        $result = $this->getDbTable()->find($allergens_id);
        if (0 == count($result)) {
            throw new Exception("Given entry doesn't exist in the system.");
        }

        // get the data and push it to the object
        $row = $result->current();
        $entry->setOptions($row->toArray());

        return $entry;
    }

#end find function

    public function fetchAll() {
        // gather all of the entries in the database
        // and push their values into an array
        $results = $this->selectAll()->query()->fetchAll();
        $entries = array();
        foreach ($results as $row) {
            $entry = new Atlas_Model_Allergens();
            $entry->setOptions($row);
            $entries[] = $entry;
        }

        // return the results
        return $entries;
    }

#end fetchAll function

    public function selectAll() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "allergens"), array('t.allergens_id', 't.allergens_item', 't.allergens_data'));

        // return the select statement	
        return $select;
    }

#end selectAll function

    public function buildAll() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "allergens"), array('t.*'));

        // return the select statement	
        return $select->query()->fetchAll();
    }

#end buildAll function

    public function buildItemAllergens($item_key) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "allergens"), array('t.*'))
                ->where('t.allergens_item = ?', $item_key)
                ->limit(1);
        $result = $select->query()->fetchAll();
        // return the select statement	
        return $result[0];
    }

#end buildItemAllergens function

    public function processForm($form_data = NULL) {
        if ($form_data == NULL) {
            throw new Exception("No data given to the model for processing.");
        }

        if ((int) $form_data["allergens_id"] > 0) {
            $entry = $this->find($form_data["allergens_id"]);
            $entry->setOptions($form_data);
            $allergens_id = $this->save($entry);
        } else {
            unset($form_data["allergens_id"]);
            $entry = new Atlas_Model_Allergens();
            $entry->setOptions($form_data);
            $allergens_id = $this->save($entry);
        }

        return $allergens_id;
    }

#end processForm function
}

?>