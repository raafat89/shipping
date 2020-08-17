<?php

class Atlas_Model_UnitsMapper {

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
            $this->setDbTable('Atlas_Model_DbTable_Units');
        }
        return $this->_dbTable;
    }

#end getDbTable() function
    // save the attributes of a given db object
    public function save(Atlas_Model_Units $unit) {
        // push the data into an array
        $data = $unit->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($unit_id = $unit->getUnit_id()) || $unit_id == 0) {
            unset($data['unit_id']);
            $unit_id = $this->getDbTable()->insert($data);
            return $unit_id;
        } else {
            $this->getDbTable()->update($data, array('unit_id = ?' => $unit_id));
            return $unit_id;
        }
    }

#end save() function
    // remove a row from the database that matches the id given
    public function remove($unit_id) {
        $this->getDbTable()->delete("unit_id='$unit_id'");
    }

#end remove() function
    // find a row in the database based on the primary key and set the values
    // in the db object given by the user
    public function find($unit_id) {
        $unit = new Atlas_Model_Units();

        // attempt to locate the row in the database
        // if it doesn't exist throw an exception
        $result = $this->getDbTable()->find($unit_id);
        if (0 == count($result)) {
            throw new Exception("Given entry doesn't exist");
        }

        // get the data and push it to the object
        $row = $result->current();
        $unit->setOptions($row->toArray());

        return $unit;
    }

#end find() function
    // find all entries from the database for the given table
    public function fetchAll() {
        // gather all of the entries in the database
        // and push their values into an array
        $resultSet = $this->selectAll()->query()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Atlas_Model_Units();
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
                ->from(array("u" => "units"), array("u.unit_id", "u.unit", "u.active"))
                ->order("u.unit ASC");

        return $select;
    }

#end selectAll() function

    public function buildIdByName($name) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("u" => "units"), array("u.unit_id"))
                ->where("u.unit LIKE '%" . $name . "%'");

        $result = $select->query()->fetchAll();
        return $result[0]['unit_id'];
    }

#end buildIdByName() function
    // activate the unit
    public function activateUnit($unit_id) {
        try {
            $unit = $this->find($unit_id);
            $unit->setActive(1);
            $this->save($unit);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

#end activateUnit() function
    // deactivate the unit
    public function deactivateUnit($unit_id) {
        try {
            $unit = $this->find($unit_id);
            $unit->setActive(0);
            $this->save($unit);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

#end deactivateUnit() function

    public function checkLabel($unit_id, $label) {

        $select = $this->getDbTable()->select();
        $select->from(array("t" => "units"), array('COUNT(*) AS count'))
                ->where('LOWER(t.unit) = ?', strtolower($label))
                ->where('t.unit_id != ?', $unit_id);

        $result = $select->query()->fetchAll();
        if (is_array($result) && $result[0]['count'] > 0) {
            return false;
        } else {
            return true;
        }
    }

#end checkLabel function
    // return a select statement for the table
    public function buildAll() {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("u" => "units"), array("u.unit_id", "u.unit", "u.active"))
                ->order("u.unit ASC");

        return $select->query()->fetchAll();
    }

#end buildAll() function
    // process data from the unit form
    public function processForm($form_data) {

        if (!$this->checkLabel((int) $form_data['unit_id'], $form_data['unit'])) {
            throw new Exception("Duplicate unit not allowed.");
        }

        if (isset($form_data['unit_id']) && trim($form_data['unit_id']) != "") {
            try {
                $unit = $this->find($form_data['unit_id']);
                $unit->setOptions($form_data);
                $this->save($unit);

                return true;
            } catch (Exception $e) {
                return false;
            }
        } else {
            $unit = new Atlas_Model_Units();
            $unit->setOptions($form_data);
            $this->save($unit);

            return true;
        }
    }

#end processForm() function
}

?>