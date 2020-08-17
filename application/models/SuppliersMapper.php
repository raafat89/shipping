<?php

class Atlas_Model_SuppliersMapper {

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
            $this->setDbTable('Atlas_Model_DbTable_Suppliers');
        }
        return $this->_dbTable;
    }

#end getDbTable() function
    // save the attributes of a given db object
    public function save(Atlas_Model_Suppliers $supplier) {
        // push the data into an array
        $data = $supplier->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($supplier_id = $supplier->getSupplier_id()) || $supplier_id == 0) {
            unset($data['supplier_id']);
            $supplier_id = $this->getDbTable()->insert($data);
            return $supplier_id;
        } else {
            $this->getDbTable()->update($data, array('supplier_id = ?' => $supplier_id));
            return $supplier_id;
        }
    }

#end save() function
    // remove a row from the database that matches the id given
    public function remove($supplier_id) {
        $this->getDbTable()->delete("supplier_id='$supplier_id'");
    }

#end remove() function
    // find a row in the database based on the primary key and set the values
    // in the db object given by the user
    public function find($supplier_id) {
        $supplier = new Atlas_Model_Suppliers();

        // attempt to locate the row in the database
        // if it doesn't exist throw an exception
        $result = $this->getDbTable()->find($supplier_id);
        if (0 == count($result)) {
            throw new Exception("Given entry doesn't exist");
        }

        // get the data and push it to the object
        $row = $result->current();
        $supplier->setOptions($row->toArray());

        return $supplier;
    }

#end find() function
    // find all entries from the database for the given table
    public function fetchAll() {
        // gather all of the entries in the database
        // and push their values into an array
        $resultSet = $this->selectAll()->query()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Atlas_Model_Suppliers();
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
                ->from(array("s" => "suppliers"), array("s.supplier_id", "s.supplier_name", "s.supplier_code", "s.supplier_active"))
                ->order("s.supplier_code ASC");

        return $select;
    }

#end selectAll() function
    // return a select statement for the table
    public function buildAll() {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("s" => "suppliers"), array("s.supplier_id", "s.supplier_name", "s.supplier_code", "s.supplier_active"))
                ->order("s.supplier_code ASC");

        return $select->query()->fetchAll();
    }

#end buildAll() function
    // return data structure with all active suppliers
    public function buildActiveList() {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("s" => "suppliers"), array("s.supplier_id", "s.supplier_name", "s.supplier_code", "s.supplier_active"))
                ->where("s.supplier_active = ?", 1)
                ->order("s.supplier_code ASC");

        return $select->query()->fetchAll();
    }

#end buildActiveList() function

    public function checkDuplicate($supplier_id, $supplier_code) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("s" => "suppliers"), array("COUNT(*) AS total"))
                ->where("s.supplier_id != ?", $supplier_id)
                ->where("s.supplier_code = ?", $supplier_code);

        $result = $select->query()->fetchAll();
        if (is_array($result) && count($result) > 0 && $result[0]['total'] > 0) {
            return true;
        } else {
            return false;
        }
    }

#end checkDuplicate function

    public function getDuplicateSuppliers() {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("s" => "suppliers"), array("s.supplier_id", "s.supplier_name", "s.supplier_code",
                    "(SELECT COUNT(*) FROM suppliers s2 WHERE s.supplier_code=s2.supplier_code AND s.supplier_name=s2.supplier_name) AS entry_count"))
                ->having("entry_count > ?", 1)
                ->order(array("s.supplier_code ASC", "s.supplier_id ASC"));

        return $select;
    }

#end getDuplicateSuppliers function
    // activate the unit
    public function activateSupplier($supplier_id) {
        try {
            $supplier = $this->find($supplier_id);
            $supplier->setSupplier_active(1);
            $this->save($supplier);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

#end activateSupplier() function
    // deactivate the unit
    public function deactivateSupplier($supplier_id) {
        try {
            $supplier = $this->find($supplier_id);
            $supplier->setSupplier_active(0);
            $this->save($supplier);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

#end deactivateSupplier() function
    // process data from the unit form
    public function processForm($form_data) {
        // check for duplicate supplier code
        if ($this->checkDuplicate((int) $form_data['supplier_id'], $form_data['supplier_code'])) {
            throw new Exception("Duplicate supplier entry detected.");
        }

        if (isset($form_data['supplier_id']) && trim($form_data['supplier_id']) != "") {
            try {
                $supplier = $this->find($form_data['supplier_id']);
                $supplier->setOptions($form_data);
                $this->save($supplier);

                return true;
            } catch (Exception $e) {
                return false;
            }
        } else {
            $supplier = new Atlas_Model_Suppliers();
            $supplier->setOptions($form_data);
            $this->save($supplier);

            return true;
        }
    }

#end processForm() function
}

?>