<?php

class Atlas_Model_AsnLinesMapper {

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
            $this->setDbTable('Atlas_Model_DbTable_AsnLines');
        }
        return $this->_dbTable;
    }

#end getDbTable() function
    // save the attributes of a given db object

    public function save(Atlas_Model_AsnLines $line) {
        // push the data into an array
        $data = $line->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($line_id = $line->getLine_id()) || $line_id == 0) {
            unset($data['line_id']);
            $line_id = $this->getDbTable()->insert($data);
            return $line_id;
        } else {
            $this->getDbTable()->update($data, array('line_id = ?' => $line_id));
            return $line_id;
        }
    }

#end save() function
    // remove a row from the database that matches the id given

    public function remove($line_id) {
        $this->getDbTable()->delete("line_id='$line_id'");
    }

#end remove() function
    // find a row in the database based on the primary key and set the values
    // in the db object given by the user

    public function find($line_id) {
        $line = new Atlas_Model_AsnLines();

        // attempt to locate the row in the database
        // if it doesn't exist throw an exception
        $result = $this->getDbTable()->find($line_id);
        if (0 == count($result)) {
            throw new Exception("Given entry doesn't exist");
        }

        // get the data and push it to the object
        $row = $result->current();
        $line->setOptions($row->toArray());

        return $line;
    }

#end find() function
    // find all entries from the database for the given table

    public function fetchAll() {
        // gather all of the entries in the database
        // and push their values into an array
        $resultSet = $this->selectAll()->query()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Atlas_Model_AsnLines();
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
                ->from(array("al" => "asn_lines"), array("al.line_id", "al.order_no", "al.item_key", "al.pack_type", "al.qty_ship", "al.qty_ship", "al.lot_no", "al.lot_exp_date"));

        return $select;
    }

#end selectAll() function

    public function buildByOrderNo($order_no) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("al" => "asn_lines"), array("al.line_id", "al.item_key as Item", "al.pack_type", "al.qty_ship as Qty",
                    "al.qty_asc as QtyPei", "al.lot_no as Lot_No", "al.lot_exp_date as Expiration", "al.line_no", "al.track_no"))
                ->where("al.order_no =?", $order_no);

        $results = $select->query()->fetchAll();
        $final_results = array();
        $items = array();
        foreach ($results as $result) {
            if (!array_key_exists($result['Item'], $items)) {
                $i = 1;
                $items[$result['Item']] = 1;
            } else {
                $i = $items[$result['Item']] + 1;
                $items[$result['Item']] = $i;
            }
            $final_results[$result['Item']][$i] = $result;
        }

        return $final_results;
    }

#end buildByOrderNo() function

    public function buildByOrderLines($order_no) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("al" => "asn_lines"), array("al.line_id", "al.item_key as Item", "al.pack_type", "al.qty_ship as Qty",
                    "al.qty_asc as QtyPei", "al.lot_no as Lot_No", "al.lot_exp_date as Expiration", "al.line_no", "al.track_no"))
                ->where("al.order_no =?", $order_no);

        $results = $select->query()->fetchAll();
        return $results;
    }

#end buildByOrderLines() function

    public function processForm($form_data, $orderdata, $original_data, $address_info) {

        $y = 0;
        while ($y < $row) {
            if ($form_data['org_qty_ship'][$y] != $form_data['Qty'][$y]) {
                throw new Exception("QTY Ship is not equal to original. Check and try again");
            }
            $y++;
        }
        $item_seq = 1;
        $options = $item_seq_order = [];
        $cmdlist[] = '${';
        $order_no = trim($form_data['order_no']);
        $order_id = $form_data['order_id'];
        $functions = New Utility_Functions;
        $sg_mapper = New Atlas_Model_SgOrdersMapper();
        $pl_mapper = New Atlas_Model_PrintingLabelsMapper();
        $ship_serial_mapper = New Atlas_Model_ShippingSerialMapper();
        $pei_mapper = New Atlas_Model_ProductExtraInfoMapper();
        //$xml_buyer_item         =   $sg_mapper->buildLinesBuyerPartNo($original_data);
        $xml_buyer_item_dup = $sg_mapper->buildLinesBuyerPartNoDup($original_data);
        $line_sequence = $sg_mapper->buildLinesSequenceDup($original_data);
        $xml_con_pk_code = $sg_mapper->buildLinesConsumerPackageCode($original_data);
        $pei_upc = $pei_mapper->buildItemUPCs();
        $TradingPartnerId = $original_data['Order']['Header']['OrderHeader']['TradingPartnerId'];
        //Delete table records
        $pl_mapper->getDbTable()->delete("bol='$order_no'");
        $this->getDbTable()->delete("order_no='$order_no'");
        $ship_serial_mapper->getDbTable()->delete("order_id='$order_id'");
        //Create Files directory
        $local_zpl_path = Zend_Registry::get("target_path") . '/uploads/barcodes/' . $order_no . '_TXT/';
        if (!is_dir($local_zpl_path))
            mkdir($local_zpl_path, 0777, true);

        if (!empty($TradingPartnerId)) {
            $shipping_prefix_mapper = new Atlas_Model_ShippingPrefixMapper();
            $vendor_info = $shipping_prefix_mapper->buildVendorInfo($TradingPartnerId);
        } else {
            $vendor_info['prefix'] = '00007900110';
        }
        $last_assigned_serial = $ship_serial_mapper->BuildLastSerialFreight($order_id);
        $shipping_serial_id = $vendor_info['prefix'] . $last_assigned_serial;
        $po_number = $orderdata[0]['po_no'];
        $barcode_zip_text = '(420)' . $address_info['PostalCode'];
        $zip_code = str_replace('(', '', str_replace(')', '', $barcode_zip_text));
        $line_item_no = '';
        foreach ($orderdata as $key => $val) {
            $item_seq_order[$val['Item']] = (!array_key_exists($val['Item'], $item_seq_order)) ? 0 : (($val['order_line'] == $line_item_no) ? $item_seq_order[$val['Item']] : $item_seq_order[$val['Item']] + 1);
            $line_item_no = $val['order_line'];
            $buyer_item_code = $xml_buyer_item_dup[strtoupper(trim($form_data['Item'][$key]))][$item_seq_order[$form_data['Item'][$key]]];
            $line_seq_item = $line_sequence[strtoupper(trim($form_data['Item'][$key]))][$item_seq_order[$form_data['Item'][$key]]];
            $ConsumerPackageCode = (isset($xml_con_pk_code[$form_data['Item'][$key]])) ? $xml_con_pk_code[$form_data['Item'][$key]] : $pei_upc[$form_data['Item'][$key]];
            //Save Data To ASN Lines
            $options = ["header_id" => $form_data['header_id'],
                "order_no" => $order_no,
                "item_key" => $form_data['Item'][$key],
                "pack_type" => $form_data['pack_type'][$key],
                "qty_ship" => $form_data['Qty'][$key],
                "qty_asc" => $form_data['QtyPei'][$key],
                "lot_no" => $val['Lot_No'],
                "lot_exp_date" => date("Y-m-d", strtotime($val['Expiration'])),
                "line_no" => $form_data['line_no'][$key],
                "track_no" => $form_data['track_no'][$key]
            ];
            $entry = new Atlas_Model_AsnLines();
            $entry->setOptions($options);
            $this->save($entry);

            //Get Labels Count and Generate Labels
            $labels = ceil($form_data['Qty'][$key] / $form_data['QtyPei'][$key]);
            $descr = $this->buildCleanItemDesc($val['Description']);
            $y = 1;
            while ($y <= $labels) {
                $partial = ($form_data['Qty'][$key] - (($y - 1) * $form_data['QtyPei'][$key]));
                if ($partial > $form_data['QtyPei'][$key]) {
                    $partial = $form_data['QtyPei'][$key];
                }
                $shipping_serial_id = $functions->shipping_serial_new($shipping_serial_id);    //Parse SSCC-18
                $carton_id_text = substr_replace(substr_replace($shipping_serial_id, ')', 2, 0), '(', 0, 0);
                $input_data = array("bol" => $order_no,
                    "po" => $po_number,
                    "ship_to_name" => $address_info['AddressName'],
                    "ship_to_address" => $address_info['Address1'],
                    "ship_to_city" => $address_info['City'],
                    "ship_to_state" => $address_info['State'],
                    "ship_to_zip" => $address_info['PostalCode'],
                    "landing_qty" => $form_data['landing_qty'],
                    "carton_id" => $shipping_serial_id,
                    "line" => $key + 1,
                    "buyer_item" => $buyer_item_code,
                    "vdr_item" => $form_data['Item'][$key],
                    "upc" => $ConsumerPackageCode,
                    "descr" => $descr,
                    "qty_ship" => $form_data['Qty'][$key],
                    "item_no" => $item_seq,
                    "ctn_qty" => $partial,
                    "uom" => 'EACH',
                    "lot_number" => $val['Lot_No'],
                    "expiration_date" => date("y-m-d", strtotime($val['Expiration'])),
                    "barcode_zip_text" => $barcode_zip_text,
                    "barcode_upc_text" => $carton_id_text,
                    "store_id" => $address_info['AddressLocationNumber'],
                    "line_seq" => $line_seq_item
                );
                $pl_mapper->save(new Atlas_Model_PrintingLabels($input_data));

                //Save Commands to Be printed in Labels File
                if ($form_data['partner_code'] == 'BIY') {
                    $cmds = $pl_mapper->spsasnLabel($address_info['AddressName'], $address_info['Address1'], $address_info['City'], $address_info['State'], $address_info['PostalCode'], $zip_code, $barcode_zip_text, $form_data['carrier'], $form_data['tracking_number'], $order_no, $item_seq, $form_data['landing_qty'], $po_number, $partial, $form_data['Item'][$key], $ConsumerPackageCode, $buyer_item_code, $descr, $val['Lot_No'], $val['Expiration'], $shipping_serial_id, $carton_id_text);
                } else if ($form_data['partner_code'] == '080') {
                    $cmds = $pl_mapper->zebraLabel($address_info['AddressName'], $address_info['AddressLocationNumber'], $address_info['Address1'], $address_info['City'], $address_info['State'], $address_info['PostalCode'], $zip_code, $barcode_zip_text, $order_no, $po_number, $buyer_item_code, $item_seq, $form_data['landing_qty'], $val['Lot_No'], $val['Expiration'], $shipping_serial_id, $carton_id_text, $form_data['Item'][$key]);
                }
                $cmdlist[] = $cmds;
                $y++;
                $item_seq++;
            }
        }
        $cmdlist[] = '}$';
        $path = $local_zpl_path . $order_no . '_ZPL.txt';
        file_put_contents($path, $cmdlist); //Save All lables in ZPL file on local server
        //Saving Last ShiPping Serial
        $ship_serial = new Atlas_Model_ShippingSerial();
        $ship_serial->setOrder_id($order_id) //Change to order id
                ->setSerial_number(substr($shipping_serial_id, -9))
                ->setSerial_date(date('Y-m-d'));
        $ship_serial_mapper->save($ship_serial);
    }

#end processForm() function 

    public function buildCleanItemDesc($description) {
        $descr_data = explode("-", $description);
        array_pop($descr_data);
        if (is_numeric(end($descr_data)))
            array_pop($descr_data);
        $descr = trim(implode("-", $descr_data));
        return $descr;
    }

    public function updateOrderItem($order_no, $line, $exp_date, $lot_no) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("al" => "asn_lines"), array('al.*'))
                ->where("order_no = ?", $order_no)
                ->where("line_no = ?", $line);

        // return the select statement	
        $result = $select->query()->fetch();

        if ($result) {
            $entry = new Atlas_Model_AsnLines();
            $entry->setOptions($result);
            $entry->setLot_exp_date(date("Y-m-d", $exp_date))
                    ->setLot_no($lot_no);
            $this->save($entry);
        }
    }

#end updateOrderItem function

    public function updateItemTrackNo($form_data) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("al" => "asn_lines"), array('al.*'))
                ->where("order_no = ?", $form_data['order_no']);

        // return the select statement
        $results = $select->query()->fetchAll();

        foreach ($results as $res) {
            if (!empty($form_data['track_no'][$res['line_no'] - 1])) {
                $entry = new Atlas_Model_AsnLines();
                $entry->setOptions($res);
                $entry->setTrack_no($form_data['track_no'][$res['line_no'] - 1]);
                $this->save($entry);
            }
        }
    }

#end updateItemTrackNo function
}

?>