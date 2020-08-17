<?php

class Atlas_Model_BulkTypesMapper {

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
            $this->setDbTable('Atlas_Model_DbTable_BulkTypes');
        }
        return $this->_dbTable;
    }

#end getDbTable() function
    // save the attributes of a given db object
    public function save(Atlas_Model_BulkTypes $b_type) {
        // push the data into an array
        $data = $b_type->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($bulk_type_id = $b_type->getBulk_type_id()) || $bulk_type_id == 0) {
            unset($data['bulk_type_id']);
            $bulk_type_id = $this->getDbTable()->insert($data);
            return $bulk_type_id;
        } else {
            $this->getDbTable()->update($data, array('bulk_type_id = ?' => $bulk_type_id));
            return $bulk_type_id;
        }
    }

#end save() function
    // remove a row from the database that matches the id given
    public function remove($bulk_type_id) {
        $this->getDbTable()->delete("bulk_type_id='$bulk_type_id'");
    }

#end remove() function
    // find a row in the database based on the primary key and set the values
    // in the db object given by the user
    public function find($bulk_type_id) {
        $b_type = new Atlas_Model_BulkTypes();

        // attempt to locate the row in the database
        // if it doesn't exist throw an exception
        $result = $this->getDbTable()->find($bulk_type_id);
        if (0 == count($result)) {
            throw new Exception("Given entry doesn't exist");
        }

        // get the data and push it to the object
        $row = $result->current();
        $b_type->setOptions($row->toArray());

        return $b_type;
    }

#end find() function
    // find all entries from the database for the given table
    public function fetchAll() {
        // gather all of the entries in the database
        // and push their values into an array
        $resultSet = $this->selectAll()->query()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Atlas_Model_BulkTypes();
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
                ->from(array("bt" => "bulk_types"), array("bt.bulk_type_id", "bt.bulk_type"))
                ->order("bt.bulk_type ASC");

        return $select;
    }

#end selectAll() function
}

?>