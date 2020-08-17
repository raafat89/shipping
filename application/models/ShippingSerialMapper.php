<?php

class Atlas_Model_ShippingSerialMapper {

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
            $this->setDbTable("Atlas_Model_DbTable_ShippingSerial");
        }

        return $this->_dbTable;
    }

#end getDbTable function

    public function save(Atlas_Model_ShippingSerial $entry) {
        // push the data into an array
        $data = $entry->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($serial_id = $entry->getSerial_id()) || $serial_id == 0) {
            unset($data["serial_id"]);
            $serial_id = $this->getDbTable()->insert($data);
            return $serial_id;
        } else {
            $this->getDbTable()->update($data, array("serial_id = ?" => $serial_id));
            return $serial_id;
        }
    }

#end save function

    public function remove($serial_id) {
        $this->getDbTable()->delete("serial_id='$serial_id'");
    }

#end remove function

    public function find($serial_id) {
        $entry = new Atlas_Model_ShippingSerial();

        // attempt to locate the row in the database
        // if it doesn"t exist return NULL
        $result = $this->getDbTable()->find($serial_id);
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
            $entry = new Atlas_Model_ShippingSerial();
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
        $select->from(array("t" => "shipping_serial"), array('t.serial_id', 't.serial_number', 't.serial_date'));

        // return the select statement	
        return $select;
    }

#end selectAll function

    public function BuildLastSerial() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "shipping_serial"), array('t.serial_number'))
                ->where('t.serial_number BETWEEN 20000000 AND 200000000')
                ->order(array('t.serial_id DESC'))
                ->limit(1);

        // return the select statement	
        $result = $select->query()->fetchAll();
        return $result[0]['serial_number'];
    }

#end BuildLastSerial function

    public function BuildLastSerialFreight($order_id = 1) {
        $this->getDbTable()->delete("order_id='$order_id'");
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "shipping_serial"), array('t.serial_number'))
                ->where('t.serial_number >= 200000000')
                ->order(array('t.serial_id DESC'))
                ->limit(1);

        // return the select statement	
        $result = $select->query()->fetchAll();
        return $result[0]['serial_number'];
    }

#end BuildLastSerialFreight function

    public function processForm($form_data = NULL) {
        if ($form_data == NULL) {
            throw new Exception("No data given to the model for processing.");
        }

        if ((int) $form_data["serial_id"] > 0) {
            $entry = $this->find($form_data["serial_id"]);
            $entry->setOptions($form_data);
            $serial_id = $this->save($entry);
        } else {
            unset($form_data["serial_id"]);
            $entry = new Atlas_Model_ShippingSerial();
            $entry->setOptions($form_data);
            $serial_id = $this->save($entry);
        }

        return $serial_id;
    }

#end processForm function
}

?>