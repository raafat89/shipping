<?php

class Atlas_Model_PrintingLabelsBuyerMapper {

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
            $this->setDbTable("Atlas_Model_DbTable_PrintingLabelsBuyer");
        }

        return $this->_dbTable;
    }

#end getDbTable function

    public function save(Atlas_Model_PrintingLabelsBuyer $entry) {
        // push the data into an array
        $data = $entry->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($id = $entry->getId()) || $id == 0) {
            unset($data["id"]);
            $id = $this->getDbTable()->insert($data);
            return $id;
        } else {
            $this->getDbTable()->update($data, array("id = ?" => $id));
            return $id;
        }
    }

#end save function

    public function remove($id) {
        $this->getDbTable()->delete("id='$id'");
    }

#end remove function

    public function find($id) {
        $entry = new Atlas_Model_PrintingLabelsBuyer();

        // attempt to locate the row in the database
        // if it doesn"t exist return NULL
        $result = $this->getDbTable()->find($id);
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
            $entry = new Atlas_Model_PrintingLabelsBuyer();
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
        $select->from(array("t" => "printing_labels_buyer"), array('t.id', 't.upc', 't.asin', 't.sku'));

        // return the select statement	
        return $select;
    }

#end selectAll function

    public function buildAll() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "printing_labels_buyer"), array('t.*'))
                ->order(array('id DESC'));

        // return the select statement	
        return $select->query()->fetchAll();
    }

#end buildAll function

    public function buildUpc($asin = '') {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "printing_labels_buyer"), array('t.upc'))
                ->where("t.asin=?", $asin)
                ->limit(1);
        $res = $select->query()->fetch();
        // return the select statement	
        return $res['upc'];
    }

#end buildUpc function

    public function buildItemkey($asin = '') {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "printing_labels_buyer"), array('t.sku'))
                ->where("t.asin=?", $asin)
                ->limit(1);
        $res = $select->query()->fetch();
        // return the select statement	
        return $res['sku'];
    }

#end buildItemkey function

    public function buildItemData($asin = '') {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "printing_labels_buyer"), array('t.sku', 't.upc'))
                ->where("t.asin=?", $asin)
                ->limit(1);
        $res = $select->query()->fetch();
        // return the select statement	
        return array('itemkey' => $res['sku'], 'upc' => $res['upc']);
    }

#end buildItemData function

    public function buildAsinData() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "printing_labels_buyer"), array('t.asin', 't.sku', 't.upc'));
        $results = $select->query()->fetchAll();
        $final_results = array();
        foreach ($results as $result) {
            $final_results[$result['asin']] = $result['sku'];
        }
        // return the select statement	
        return $final_results;
    }

#end buildAsinData function

    public function checkAsin($form_data = NULL) {
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "printing_labels_buyer"), array('COUNT(*) AS count'))
                ->where('LOWER(t.asin) = ?', $form_data['asin'])
                ->where('t.id != ?', $form_data['id']);

        $result = $select->query()->fetch();
        if (is_array($result) && $result['count'] > 0) {
            return false;
        } else {
            return true;
        }
    }

#end checkAsin function

    public function processForm($form_data = NULL) {
        if ($form_data == NULL) {
            throw new Exception("No data given to the model for processing.");
        }

        if ((int) $form_data["id"] > 0) {
            $entry = $this->find($form_data["id"]);
            $entry->setOptions($form_data);
            $id = $this->save($entry);
        } else {
            unset($form_data["id"]);
            $entry = new Atlas_Model_PrintingLabelsBuyer();
            $entry->setOptions($form_data);
            $id = $this->save($entry);
        }

        return $id;
    }

#end processForm function
}

?>