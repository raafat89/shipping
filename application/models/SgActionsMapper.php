<?php

class Atlas_Model_SgActionsMapper {

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
            $this->setDbTable("Atlas_Model_DbTable_SgActions");
        }

        return $this->_dbTable;
    }

#end getDbTable function

    public function save(Atlas_Model_SgActions $entry) {
        // push the data into an array
        $data = $entry->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($action_id = $entry->getAction_id()) || $action_id == 0) {
            unset($data["action_id"]);
            $action_id = $this->getDbTable()->insert($data);
            return $action_id;
        } else {
            $this->getDbTable()->update($data, array("action_id = ?" => $action_id));
            return $action_id;
        }
    }

#end save function

    public function remove($action_id) {
        $this->getDbTable()->delete("action_id='$action_id'");
    }

#end remove function

    public function find($action_id) {
        $entry = new Atlas_Model_SgActions();

        // attempt to locate the row in the database
        // if it doesn"t exist return NULL
        $result = $this->getDbTable()->find($action_id);
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
            $entry = new Atlas_Model_SgActions();
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
        $select->from(array("t" => "sg_actions"), array('t.action_id', 't.order_id', 't.action', 't.user_id', 't.action_datetime'));

        // return the select statement	
        return $select;
    }

#end selectAll function

    public function buildOrderActions($order_id) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();

        $select->setIntegrityCheck(false)
                ->from(array("t" => "sg_actions"), array('t.*', 'u.name as uploaded_by'))
                ->joinLeft(array("u" => "users"), "t.user_id=u.user_id", array())
                ->where('t.order_id = ?', $order_id);

        // return the results
        $results = $select->query()->fetchAll();
        $final_results = array();
        foreach ($results as $result) {
            $final_results[$result['action']] = $result;
        }
        return $final_results;
    }

#end buildOrderActions function

    public function buildOrdersActions($orders) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();

        $select->setIntegrityCheck(false)
                ->from(array("t" => "sg_actions"), array('t.action_id', 't.order_id', 't.action', 't.order_no'))
                ->where("t.order_id IN($orders)");

        // return the results
        $results = $select->query()->fetchAll();

        $final_results = array();
        foreach ($results as $result) {
            if ($result['order_no'] != '')
                $index = $result['order_no'];
            else
                $index = 1;
            $final_results[$result['order_id']][$index][$result['action']] = $result;
        }
        return $final_results;
    }

#end buildOrdersActions function

    public function buildActionXml($action_id) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();

        $select->setIntegrityCheck(false)
                ->from(array("t" => "sg_actions"), array('t.file'))
                ->where('t.action_id = ?', $action_id)
                ->limit(1);

        // return the results
        $results = $select->query()->fetchAll();
        return $results[0];
    }

#end buildLastBatch function

    public function buildActionFile($action_id) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();

        $select->setIntegrityCheck(false)
                ->from(array("t" => "sg_actions"), array('t.file'))
                ->where('t.action_id = ?', $action_id)
                ->limit(1);

        // return the results
        $results = $select->query()->fetch();
        return $results['file'];
    }

#end buildLastBatch function

    public function processForm($form_data = NULL) {
        if ($form_data == NULL) {
            throw new Exception("No data given to the model for processing.");
        }

        if ((int) $form_data["action_id"] > 0) {
            $entry = $this->find($form_data["action_id"]);
            $entry->setOptions($form_data);
            $action_id = $this->save($entry);
        } else {
            unset($form_data["action_id"]);
            $entry = new Atlas_Model_SgActions();
            $entry->setOptions($form_data);
            $action_id = $this->save($entry);
        }

        return $action_id;
    }

#end processForm function
}

?>