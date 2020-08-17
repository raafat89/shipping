<?php

class Atlas_Model_AsnHeaderMapper {

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
            $this->setDbTable('Atlas_Model_DbTable_AsnHeader');
        }
        return $this->_dbTable;
    }

#end getDbTable() function
    // save the attributes of a given db object

    public function save(Atlas_Model_AsnHeader $header) {
        // push the data into an array
        $data = $header->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($header_id = $header->getHeader_id()) || $header_id == 0) {
            unset($data['header_id']);
            $header_id = $this->getDbTable()->insert($data);
            return $header_id;
        } else {
            $this->getDbTable()->update($data, array('header_id = ?' => $header_id));
            return $header_id;
        }
    }

#end save() function
    // remove a row from the database that matches the id given

    public function remove($header_id) {
        $this->getDbTable()->delete("header_id='$header_id'");
    }

#end remove() function
    // find a row in the database based on the primary key and set the values
    // in the db object given by the user

    public function find($header_id) {
        $header = new Atlas_Model_AsnHeader();

        // attempt to locate the row in the database
        // if it doesn't exist throw an exception
        $result = $this->getDbTable()->find($header_id);
        if (0 == count($result)) {
            throw new Exception("Given entry doesn't exist");
        }

        // get the data and push it to the object
        $row = $result->current();
        $header->setOptions($row->toArray());

        return $header;
    }

#end find() function
    // find all entries from the database for the given table

    public function fetchAll() {
        // gather all of the entries in the database
        // and push their values into an array
        $resultSet = $this->selectAll()->query()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Atlas_Model_AsnHeader();
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

    public function findByOrderno($order_no) {

        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("ah" => "asn_header"), array("ah.header_id"))
                ->where("order_no = ?", $order_no);

        $result = $select->query()->fetchAll();
        if (count($result) > 0)
            $header = $this->find($result[0]["header_id"]);
        else
            $header = null;

        return $header;
    }

    // return a select statement for the table

    public function selectAll() {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("ah" => "asn_header"), array("ah.*"));

        return $select;
    }

#end selectAll() function
    // return a select statement for the table

    public function buildOrder($order_no) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("ah" => "asn_header"), array("ah.header_id", "ah.order_no as Order_No", "ah.ship_date", "ah.pack_medium",
                    "ah.pack_material", "ah.landing_qty", "ah.gross_weight", "ah.tracking_number",
                    "ah.carrier", "ah.tracking_number", "ah.trans_code", "ah.alpha_code",
                    "ah.volume", "ah.number_of_pallets", "ah.reference_no", "ah.shipment_method", "ah.seal_no")
                )
                ->where("ah.order_no = ?", $order_no);
        $results["header"] = $select->query()->fetch();

        $line_mapper = new Atlas_Model_AsnLinesMapper();
        $results["lines"] = $line_mapper->buildByOrderNo($order_no);

        return $results;
    }

#end buildOrder() function

    public function buildOrderDetails($order_no) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("ah" => "asn_header"), array("ah.header_id", "ah.order_no as Order_No", "ah.ship_date", "ah.pack_medium",
                    "ah.pack_material", "ah.landing_qty", "ah.gross_weight", "ah.tracking_number",
                    "ah.carrier", "ah.tracking_number", "ah.trans_code", "ah.alpha_code",
                    "ah.volume", "ah.number_of_pallets", "ah.reference_no", "ah.shipment_method", "ah.seal_no")
                )
                ->where("ah.order_no = ?", $order_no);
        $results["header"] = $select->query()->fetch();

        $line_mapper = new Atlas_Model_AsnLinesMapper();
        $results["lines"] = $line_mapper->buildByOrderLines($order_no);

        return $results;
    }

#end buildOrderDetails() function

    public function reBuildHeaderInfo($form_data, $so_number) {
        $header_info = $this->find($form_data['header_id']);
        $header_info->setPack_material($form_data['pack_material'])
                ->setPack_medium($form_data['pack_medium'])
                ->setShip_date(date('Y-m-d', strtotime($form_data['ship_date'])))
                ->setTracking_number($form_data['tracking_number'])
                ->setCarrier($form_data['carrier'])
                ->setTrans_code($form_data['trans_code'])
                ->setAlpha_code($form_data['alpha_code'])
                ->setVolume($form_data['volume'])
                ->setNumber_of_pallets($form_data['number_of_pallets'])
                ->setReference_no($form_data['reference_no'])
                ->setShipment_method($form_data['shipment_method'])
                ->setSeal_no($form_data['seal_no']);
        $this->save($header_info);
        return $this->buildOrderDetails($so_number);
    }

#end reBuildHeaderInfo() function

    public function processForm($form_data) {
        $row = count($form_data['pack_type']);
        unset($form_data["header_id"]);
        $i = 0;
        while ($i < $row) {
            $gros_qty += ceil($form_data['Qty'][$i] / $form_data['QtyPei'][$i]);
            $i++;
        }
        $entry = $this->findByOrderno($form_data["order_no"]);

        if (empty($entry)) {
            $entry = new Atlas_Model_AsnHeader();
        }
        $entry->setOptions($form_data)
                ->setShip_date(date("Y-m-d", strtotime($form_data['ship_date'])))
                ->setLanding_qty($gros_qty);

        $header_id = $this->save($entry);
        return $header_id;
    }

#end processForm() function

    public function reBuildOrderInfo($so_number, $form_data, $order_info) {

        foreach ($order_info["header"] as $key => $val) {
            $order_info["header"][$key] = $form_data[$key];
        }
        $order_info["header"]["ship_date"] = date('Y-m-d', strtotime($form_data['ship_date']));
        $order_info["header"]["Order_No"] = $so_number . '_' . $form_data['partial_shipment_no'];

        return $order_info;
    }

#end reBuildOrderInfo() function
}
