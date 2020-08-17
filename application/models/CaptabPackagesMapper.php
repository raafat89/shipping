<?php

class Atlas_Model_CaptabPackagesMapper {

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
            $this->setDbTable("Atlas_Model_DbTable_CaptabPackages");
        }

        return $this->_dbTable;
    }

#end getDbTable function

    public function save(Atlas_Model_CaptabPackages $entry) {
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
        $entry = new Atlas_Model_CaptabPackages();

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
            $entry = new Atlas_Model_CaptabPackages();
            $entry->setOptions($row);
            $entries[] = $entry;
        }

        // return the results
        return $entries;
    }

#end fetchAll function

    public function selectAll($search = '') {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "captab_packages"), array('t.id', 't.name', 't.code', 't.captab_type', 't.captab_color', 't.captab_size', 't.captab_weight', 't.captab_weight_unit', 't.capsule_shell_weight', 't.capsule_shell_weight_unit', 't.active'));
        if ($search != "") {
            $select->where("t.name LIKE '%" . $search . "%' OR t.code LIKE '%" . $search . "%' OR t.captab_type LIKE '%" . $search . "%' OR t.captab_color LIKE '%" . $search . "%' OR t.captab_size LIKE '%" . $search . "%' OR t.captab_weight LIKE '%" . $search . "%' OR t.captab_weight_unit LIKE '%" . $search . "%' OR t.capsule_shell_weight LIKE '%" . $search . "%'OR t.capsule_shell_weight_unit LIKE '%" . $search . "%'");
        }
        $select->order(array("t.captab_type ASC", "t.code ASC"));
        // return the select statement	
        return $select;
    }

#end selectAll function

    public function buildAll() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "captab_packages"), array('t.*'));
        $select->order(array("t.captab_type ASC", "t.code ASC"));
        // return the select statement	
        return $select->query()->fetchAll();
    }

#end buildAll function

    public function BuildCaptabs() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "captab_packages"), array('t.*'))
                ->order(array('captab_type ASC', 'code ASC'));
        // return the select statement	
        return $select->query()->fetchAll();
    }

#end BuildCaptabs function

    public function BuildCaptab($id = 0) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "captab_packages"), array('t.*'));
        $select->where("LOWER(t.id) = ?", $id);
        // return the select statement	
        $result = $select->query()->fetchAll();
        $values = $result[0]['captab_type'] . '*#*' . html_entity_decode($result[0]['captab_size']) . '*#*' . $result[0]['captab_color'] . '*#*' . $result[0]['captab_weight'] . '*#*' . $result[0]['captab_weight_unit'] . '*#*' . $result[0]['capsule_shell_weight'] . '*#*' . $result[0]['capsule_shell_weight_unit'] . '*#*' . $result[0]['vegan'] . '*#*' . $result[0]['gelatin_capsule'];
        return $values;
    }

#end BuildCaptab function

    public function checkCaptab($form_data) {
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "captab_packages"), array('COUNT(*) AS count'))
                ->where('LOWER(t.code) = ?', strtolower($form_data['code']))
                ->where('t.id != ?', $form_data['id']);

        $result = $select->query()->fetchAll();
        if (is_array($result) && $result[0]['count'] > 0) {
            return false;
        } else {
            return true;
        }
    }

#end checkCaptab function

    public function processForm($form_data = NULL) {
        if ($form_data == NULL) {
            throw new Exception("No data given to the model for processing.");
        }
        $form_data['captab_size'] = html_entity_decode($form_data['captab_size']);

        if (!$this->checkCaptab($form_data)) {
            throw new Exception("Duplicate Cap/Tab not allowed.");
        }
        if ($form_data["captab_weight"] == 0) {
            $form_data["capsule_shell_weight"] = "";
            $form_data["capsule_shell_weight_unit"] = "";
        }
        if ((int) $form_data["id"] > 0) {
            $entry = $this->find($form_data["id"]);
            $entry->setOptions($form_data);
            $id = $this->save($entry);
        } else {
            unset($form_data["id"]);
            $entry = new Atlas_Model_CaptabPackages();
            $entry->setOptions($form_data);
            $id = $this->save($entry);
        }

        return $id;
    }

#end processForm function
}

?>