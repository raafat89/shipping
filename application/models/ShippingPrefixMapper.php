<?php

class Atlas_Model_ShippingPrefixMapper {

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
            $this->setDbTable("Atlas_Model_DbTable_ShippingPrefix");
        }

        return $this->_dbTable;
    }

#end getDbTable function

    public function save(Atlas_Model_ShippingPrefix $entry) {
        // push the data into an array
        $data = $entry->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($prefix_id = $entry->getPrefix_id()) || $prefix_id == 0) {
            unset($data["prefix_id"]);
            $prefix_id = $this->getDbTable()->insert($data);
            return $prefix_id;
        } else {
            $this->getDbTable()->update($data, array("prefix_id = ?" => $prefix_id));
            return $prefix_id;
        }
    }

#end save function

    public function remove($prefix_id) {
        $this->getDbTable()->delete("prefix_id='$prefix_id'");
    }

#end remove function

    public function find($prefix_id) {
        $entry = new Atlas_Model_ShippingPrefix();

        // attempt to locate the row in the database
        // if it doesn"t exist return NULL
        $result = $this->getDbTable()->find($prefix_id);
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
            $entry = new Atlas_Model_ShippingPrefix();
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
        $select->from(array("t" => "shipping_prefix"), array('t.prefix_id', 't.trading_partner', 't.prefix', 't.pack', 't.sple_id', 't.docs'));

        // return the select statement	
        return $select;
    }

#end selectAll function

    public function buildVendorInfo($partner) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();

        $select->setIntegrityCheck(false)
                ->from(array("t" => "shipping_prefix"), array('t.prefix_id', 't.trading_partner', 't.partner_name', 't.prefix', 't.pack', 't.sple_id', 't.docs'))
                ->where("t.trading_partner = ?", $partner)
                ->limit(1);

        // return the results
        $results = $select->query()->fetchAll();
        return $results[0];
    }

#end buildVendorOrderData function

    public function buildVendors() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "shipping_prefix"), array('t.prefix_id', 't.trading_partner', 't.partner_name', 't.prefix', 't.pack', 't.sple_id', 't.docs'));

        // return the results
        $results = $select->query()->fetchAll();
        $final_results = array();
        foreach ($results as $result) {
            $final_results[str_replace('ALLJARROWFOR', '', $result['trading_partner'])] = $result['partner_name'];
        }
        return $final_results;
    }

#end buildVendors function

    public function buildVendorsDocs() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();

        $select->setIntegrityCheck(false)
                ->from(array("t" => "shipping_prefix"), array('t.trading_partner', 't.docs'));

        // return the results
        $results = $select->query()->fetchAll();
        $final_results = array();
        foreach ($results as $result) {
            $final_results[str_replace('ALLJARROWFOR', '', $result['trading_partner'])] = $result['docs'];
        }
        return $final_results;
    }

#end buildVendorOrderData function

    public function buildVendorSple($partner) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();

        $select->setIntegrityCheck(false)
                ->from(array("t" => "shipping_prefix"), array('t.sple_id'))
                ->where("t.trading_partner = ?", $partner)
                ->limit(1);

        // return the results
        $result = $select->query()->fetchAll();
        return $result[0]['sple_id'];
    }

#end buildVendorSple function

    public function processForm($form_data = NULL) {
        if ($form_data == NULL) {
            throw new Exception("No data given to the model for processing.");
        }

        if ((int) $form_data["prefix_id"] > 0) {
            $entry = $this->find($form_data["prefix_id"]);
            $entry->setOptions($form_data);
            $prefix_id = $this->save($entry);
        } else {
            unset($form_data["prefix_id"]);
            $entry = new Atlas_Model_ShippingPrefix();
            $entry->setOptions($form_data);
            $prefix_id = $this->save($entry);
        }

        return $prefix_id;
    }

#end processForm function
}

?>