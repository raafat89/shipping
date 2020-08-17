<?php

class Atlas_Model_SleevingTypesMapper {

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
            $this->setDbTable("Atlas_Model_DbTable_SleevingTypes");
        }

        return $this->_dbTable;
    }

#end getDbTable function

    public function save(Atlas_Model_SleevingTypes $entry) {
        // push the data into an array
        $data = $entry->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($sleeving_id = $entry->getSleeving_id()) || $sleeving_id == 0) {
            unset($data["sleeving_id"]);
            $sleeving_id = $this->getDbTable()->insert($data);
            return $sleeving_id;
        } else {
            $this->getDbTable()->update($data, array("sleeving_id = ?" => $sleeving_id));
            return $sleeving_id;
        }
    }

#end save function

    public function remove($sleeving_id) {
        $this->getDbTable()->delete("sleeving_id='$sleeving_id'");
    }

#end remove function

    public function find($sleeving_id) {
        $entry = new Atlas_Model_SleevingTypes();

        // attempt to locate the row in the database
        // if it doesn"t exist return NULL
        $result = $this->getDbTable()->find($sleeving_id);
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
            $entry = new Atlas_Model_SleevingTypes();
            $entry->setOptions($row);
            $entries[] = $entry;
        }

        // return the results
        return $entries;
    }

#end fetchAll function

    public function selectAll($search = "") {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "sleeving_types"), array('t.sleeving_id', 't.sleeving_types'));
        if ($search != "") {
            $select->where("t.sleeving_types LIKE '%" . $search . "%' ");
        }
        // return the select statement	
        return $select;
    }

#end selectAll function

    public function buildSleevings() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "sleeving_types"), array('t.sleeving_id', 't.sleeving_types'))
                ->order(array("t.sleeving_id ASC"));

        // return the select statement	
        return $select->query()->fetchAll();
    }

#end buildSleevings function

    public function processForm($form_data = NULL) {
        if ($form_data == NULL) {
            throw new Exception("No data given to the model for processing.");
        }

        if ((int) $form_data["sleeving_id"] > 0) {
            $entry = $this->find($form_data["sleeving_id"]);
            $entry->setOptions($form_data);
            $sleeving_id = $this->save($entry);
        } else {
            unset($form_data["sleeving_id"]);
            $entry = new Atlas_Model_SleevingTypes();
            $entry->setOptions($form_data);
            $sleeving_id = $this->save($entry);
        }

        return $sleeving_id;
    }

#end processForm function
}

?>