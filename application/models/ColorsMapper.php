<?php

class Atlas_Model_ColorsMapper {

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
            $this->setDbTable("Atlas_Model_DbTable_Colors");
        }

        return $this->_dbTable;
    }

#end getDbTable function

    public function save(Atlas_Model_Colors $entry) {
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
        $entry = new Atlas_Model_Colors();

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
            $entry = new Atlas_Model_Colors();
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
        $select->from(array("t" => "colors"), array('t.id', 't.color'));
        if ($search != "") {
            $select->where("t.color LIKE '%" . $search . "%'");
        }
        // return the select statement	
        return $select;
    }

#end selectAll function

    public function buildColors() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "colors"), array('t.id', 't.color'))->order(array('t.color ASC'));
        // return the select statement	
        return $select->query()->fetchAll();
    }

#end buildColors function

    public function checkColor($form_data) {
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "colors"), array('COUNT(*) AS count'))
                ->where('LOWER(t.color) = ?', strtolower($form_data['color']))
                ->where('t.id != ?', $form_data['id']);

        $result = $select->query()->fetchAll();
        if (is_array($result) && $result[0]['count'] > 0) {
            return false;
        } else {
            return true;
        }
    }

#end checkColor function

    public function processForm($form_data = NULL) {
        if ($form_data == NULL) {
            throw new Exception("No data given to the model for processing.");
        }

        if (!$this->checkColor($form_data)) {
            throw new Exception("Duplicate Color not allowed.");
        }

        if ((int) $form_data['id'] > 0) {
            try {
                $bsize = $this->find($form_data['id']);
                $bsize->setOptions($form_data);
                $this->save($bsize);

                return true;
            } catch (Exception $e) {
                return false;
            }
        } else {
            unset($form_data["id"]);
            $entry = new Atlas_Model_Colors();
            $entry->setOptions($form_data);
            $id = $this->save($entry);
        }

        return $id;
    }

#end processForm function
}

?>