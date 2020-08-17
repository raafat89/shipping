<?php

class Atlas_Model_ProductExtraInfoMapper {

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
            $this->setDbTable('Atlas_Model_DbTable_ProductExtraInfo');
        }
        return $this->_dbTable;
    }

#end getDbTable() function
    // save the attributes of a given db object
    public function save(Atlas_Model_ProductExtraInfo $info) {
        // push the data into an array
        $data = $info->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($product_id = $info->getProduct_id()) || $product_id == 0) {
            unset($data['product_id']);
            $product_id = $this->getDbTable()->insert($data);
            return $product_id;
        } else {
            $this->getDbTable()->update($data, array('product_id = ?' => $product_id));
            return $product_id;
        }
    }

#end save() function
    // save the attributes of a given db object
    public function save2(Atlas_Model_ProductExtraInfo $info) {
        // push the data into an array
        $data = $info->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        $product_id = $this->getDbTable()->insert($data);
        return $product_id;
    }

#end save() function
    // remove a row from the database that matches the id given
    public function remove($product_id) {
        $this->getDbTable()->delete("product_id='$product_id'");
    }

#end remove() function
    // find a row in the database based on the primary key and set the values
    // in the db object given by the user
    public function find($product_id) {
        $info = new Atlas_Model_ProductExtraInfo();

        // attempt to locate the row in the database
        // if it doesn't exist throw an exception
        $result = $this->getDbTable()->find($product_id);
        if (0 == count($result)) {
            throw new Exception("Given entry doesn't exist");
        }

        // get the data and push it to the object
        $row = $result->current();
        $info->setOptions($row->toArray());

        return $info;
    }

#end find() function
    // find all entries from the database for the given table
    public function fetchAll() {
        // gather all of the entries in the database
        // and push their values into an array
        $resultSet = $this->selectAll()->query()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Atlas_Model_ProductExtraInfo();
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
                ->from(array("pei" => "product_extra_info"), array("pei.product_id", "pei.upc", "pei.case_upc", "pei.strength", "pei.str_uom", "pei.size", "pei.size_uom",
                    "pei.product_code", "pei.height", "pei.width", "pei.depth", "pei.weight", "pei.qty_case"))
                ->order("pei.product_code ASC");

        return $select;
    }

#end selectAll() function
    // return the associated label data for the given so number
    public function getLabelData($so_number, $num_labels = 0, $die_on_error = true) {
        $mapper = new Atlas_Model_Inform3sales();
        $so_data = $mapper->buildSOCaseLabels($so_number);
        $final_data = array();

        if ($num_labels > 0) {
            for ($i = 0; $i < $num_labels; ++$i) {
                $final_data[] = $so_data[0];
            }
        } else {
            $count = 0;

            for ($i = 0; $i < count($so_data); ++$i) {
                if ($so_number != "TEST") {
                    $prod_code = $so_data[$i]['Prod_Code'];
                    $qty_case = $this->buildQtyCase($prod_code);

                    if (isset($qty_case) && (int) $qty_case['qty_case'] > 0) {
                        $so_data[$i]['Qty_Case'] = $qty_case;
                    } else {
                        $so_data[$i]['Qty_Case'] = 0;
                        if ($die_on_error) {
                            throw new Exception("Division by 0 detected, please correct the qty per case for: " . $so_data[$i]['Prod_Code']);
                        }
                    }
                } else {
                    $qty_case = $so_data[$i]['Qty_Case'];
                }

                $total = (int) $so_data[$i]['Qty'];
                while ($total > 0) {
                    if (!is_array($qty_case) || (int) $qty_case['qty_case'] <= 0) {
                        $final_data[] = $so_data[$i];
                        break;
                    }

                    $total -= $qty_case['qty_case'];
                    $qty = ($total >= 0) ? $qty_case['qty_case'] : $so_data[$i]['Qty'] % $qty_case['qty_case'];
                    $so_data[$i]['qr_file'] = str_replace(" ", "_", $so_number . '-' . $so_data[$i]['Prod_Code'] . '-' . $count);
                    $so_data[$i]['box_qty'] = $qty;
                    $so_data[$i]['qr_label'] = "
    <table>
        <tr>
            <td colspan='2'><span style='font-weight:bold;'>" . $so_data[$i]['Prod_Code'] . "</span></td>
        </tr>
        <tr>
            <td>Total Qty: " . $so_data[$i]['Qty'] . "</td><td>This Box: " . $qty . "</td>
        </tr>
        <tr>
            <td colspan='2'>Lot: " . $so_data[$i]['Lot_Number'] . "</td>
        </tr>
        <tr>
            <td colspan='2'>Expires: " . $so_data[$i]['Expires'] . "</td>
        </tr>
        <tr>
            <td colspan='2'>UPC: " . $so_data[$i]['UPC'] . "</td>
        </tr>
        <tr>
            <td colspan='2'>Weight: " . $so_data[$i]['weight'] * $qty . " lb</td>
        </tr>
    </table>";
                    $final_data[] = $so_data[$i];
                    ++$count;
                }
            }
        }

        return $final_data;
    }

#end getLabelData() function
    // return the associated label data for the given so number
    public function getLabelCount($so_number) {
        $mapper = new Atlas_Model_Inform3sales();
        $so_data = $mapper->buildSOCaseQtys($so_number);
        $final_data = array();
        $count = 0;
        for ($i = 0; $i < count($so_data); ++$i) {
            $prod_code = $so_data[$i]['Prod_Code'];
            $qty_case = $this->buildQtyCase($prod_code);
            if (isset($qty_case) && (int) $qty_case['qty_case'] > 0) {
                $so_data[$i]['Qty_Case'] = $qty_case;
            } else {
                $so_data[$i]['Qty_Case'] = 0;
                if ($die_on_error) {
                    throw new Exception("Division by 0 detected, please correct the qty per case for: " . $so_data[$i]['Prod_Code']);
                }
            }
            $total = (int) $so_data[$i]['Qty'];
            while ($total > 0) {
                if (!is_array($qty_case) || (int) $qty_case['qty_case'] <= 0) {
                    $final_data[] = $so_data[$i];
                    break;
                }
                $total -= $qty_case['qty_case'];
                $qty = ($total >= 0) ? $qty_case['qty_case'] : $so_data[$i]['Qty'] % $qty_case['qty_case'];
                $so_data[$i]['qr_file'] = str_replace(" ", "_", $so_number . '-' . $so_data[$i]['Prod_Code'] . '-' . $count);
                $so_data[$i]['box_qty'] = $qty;
                $final_data[] = $so_data[$i];
                ++$count;
            }
        }
        return $final_data;
    }

#end getLabelCount() function
    // return a list of all products and their shipping info
    public function getAllProductsShippingInfo() {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->distinct()
                ->from(array('pei' => 'product_extra_info'), array('p.ProductId', 'p.Name', 'p.ProductNumber', 'p.Code', 'p.Status',
                    'pei.Qty_Case', 'pei.UPC', 'pei.weight'))
                ->joinLeft(array('p' => 'product'), 'pei.product_id=p.productid', array())
                ->where("p.status = 1")
                ->order(array('p.Code ASC'));

        return $select;
    }

#end getAllProductsShippingInfo() function
    // return the qty per case for the given item
    public function buildQtyCase($product_code) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("pei" => "product_extra_info"), array("pei.qty_case"))
                ->where("pei.product_code = ?", $product_code);

        $result = $select->query()->fetchAll();
        return $result[0];
    }

#end buildQtyCase() functions
    // return all info based on given product code
    public function buildByCode($product_code) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("pei" => "product_extra_info"), array("pei.product_id", "pei.upc", "pei.case_upc", "pei.strength", "pei.str_uom", "pei.size", "pei.size_uom",
                    "pei.product_code", "pei.height", "pei.width", "pei.depth", "pei.weight", "pei.qty_case", "pei.case_weight"))
                ->where("pei.product_code = ?", $product_code)
                ->limit(1);

        $result = $select->query()->fetchAll();
        return $result[0];
    }

#end buildByCode() function
    // return all info based on given product code
    public function buildByUPC($upc) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->distinct()
                ->from(array("pei" => "product_extra_info"), array("pei.product_id", "pei.upc", "pei.case_upc", "pei.strength", "pei.str_uom", "pei.size", "pei.size_uom",
                    "pei.product_code", "pei.height", "pei.width", "pei.depth", "pei.weight", "pei.qty_case", "pei.case_weight"))
                ->joinLeft(array("p" => "product"), "p.productid=pei.product_id", array())
                ->where("pei.upc = ?", $upc)
                ->order(array("p.status DESC"))
                ->limit(1);

        $result = $select->query()->fetchAll();
        if (is_array($result) && count($result) > 0 && $result[0]['product_id'] > 0) {
            return $result[0];
        } else {
            //Check INT Product UPC
            $select = $this->getDbTable()->select();
            $select->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("pei" => "product_extra_info"), array("pei.product_id", "ip.product_upc as upc", "pei.case_upc", "pei.strength", "pei.str_uom", "pei.size", "pei.size_uom",
                        "ip.int_code as product_code", "pei.height", "pei.width", "pei.depth", "pei.weight", "pei.qty_case", "pei.case_weight"))
                    ->join(array("ip" => "international_products"), "ip.product_code=pei.product_code", array())
                    ->joinLeft(array("p" => "product"), "p.productid=pei.product_id", array())
                    ->where("ip.product_upc = ?", $upc)
                    ->order(array("p.status DESC"))
                    ->limit(1);

            $result = $select->query()->fetchAll();
            if (is_array($result) && count($result) > 0 && $result[0]['product_id'] > 0) {
                return $result[0];
            } else
                throw new Exception("UPC " . $upc . " not found in system.");
        }
    }

#end buildByUPC() function
    // return all info based on given product code
    public function buildByCaseUPC($upc) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("pei" => "product_extra_info"), array("pei.product_id", "pei.upc", "pei.case_upc", "pei.product_code", "pei.qty_case"))
                ->where("pei.case_upc = ?", $upc)
                ->limit(1);
        $result = $select->query()->fetchAll();
        if (is_array($result) && count($result) > 0 && $result[0]['product_id'] > 0) {
            return $result[0];
        } else {
            //Check INT Product UPC
            $select = $this->getDbTable()->select();
            $select->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("pei" => "product_extra_info"), array("pei.product_id", "ip.product_upc as upc", "pei.case_upc", "ip.int_code as product_code", "pei.qty_case"))
                    ->join(array("ip" => "international_products"), "ip.product_code=pei.product_code", array())
                    ->joinLeft(array("p" => "product"), "p.productid=pei.product_id", array())
                    ->where("ip.product_upc = ?", $upc)
                    ->order(array("p.status DESC"))
                    ->limit(1);

            $result = $select->query()->fetchAll();
            if (is_array($result) && count($result) > 0 && $result[0]['product_id'] > 0) {
                return $result[0];
            } else
                throw new Exception("UPC " . $upc . " not found in system.");
        }
    }

#end buildByCaseUPC() function
    // process a submitted form
    public function processForm($form_data) {
        try {
            $info = $this->find((int) $form_data['product_id']);
            $info->setOptions($form_data);
            $this->save($info);

            return true;
        } catch (Exception $e) {
            $info = new Atlas_Model_ProductExtraInfo();
            $info->setOptions($form_data);
            $this->save2($info);

            return true;
        }
    }

#end processForm() function
    // process a submitted form
    public function updateExtraInfo($form_data) {
        // build the data structure
        $form_keys = array_keys($form_data);
        $update_rows = array();
        foreach ($form_keys as $key) {
            $form_keys_split = preg_split("/_/", $key);
            if (( $form_keys_split[0] == "upc" ) || ( $form_keys_split[0] == "case_upc" ) || ( $form_keys_split[0] == "qty" ) || ( $form_keys_split[0] == "code" )) {
                $update_rows[$form_keys_split[1]][$form_keys_split[0]] = $form_data[$key];
            }
        }
        // update or insert each record
        foreach ($update_rows as $index => $element) {
            $data = array(
                "product_id" => $index,
                "strength" => 0,
                "str_uom" => "n/a",
                "size" => 0,
                "size_uom" => "n/a",
                "product_code" => $element['code'],
                "height" => 0,
                "width" => 0,
                "depth" => 0,
                "weight" => 0,
                "qty_case" => $element['qty']
            );

            if ($data['qty_case'] <= 0) {
                continue;
            }

            // check if the entry is already in the database
            $select = $this->getDbTable()->select();
            $select->setIntegrityCheck(false)
                    ->from(array("pei" => "product_extra_info"), array("count(*) AS exists"))
                    ->where("pei.product_id = ?", $index);
            $result = $select->query()->fetchAll();
            $exists = ( $result[0]['exists'] > 0 ) ? true : false;

            // if it already exists update the entry, otherwise insert it
            if ($exists) {
                unset($data['strength']);
                unset($data['str_uom']);
                unset($data['size']);
                unset($data['size_uom']);
                unset($data['height']);
                unset($data['width']);
                unset($data['depth']);
                unset($data['weight']);
                unset($data['case_upc']);
                unset($data['product_code']);
                $this->getDbTable()->update($data, array('product_id = ?' => $index));
            } else {
                $this->getDbTable()->insert($data);
            }
        }
    }

#end updateExtraInfo() function
    // return all info based on given product code
    public function buildCaseQty($upc) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("pei" => "product_extra_info"), array("pei.qty_case"))
                ->where("pei.upc = ?", $upc)
                ->limit(1);

        $result = $select->query()->fetch();
        return $result['qty_case'];
    }

#end buildCaseQty() function
    // return all info based on given product code
    public function buildCaseQty2($item_key) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("pei" => "product_extra_info"), array("pei.qty_case"))
                ->where("pei.product_code = ?", $item_key)
                ->limit(1);

        $result = $select->query()->fetch();
        return (int) $result['qty_case'];
    }

#end buildCaseQty2() function
    // return all info based on given product code
    public function buildUpc($code) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("pei" => "product_extra_info"), array("pei.UPC"))
                ->where("pei.product_code = ?", $code)
                ->limit(1);

        $result = $select->query()->fetch();
        return $result['UPC'];
    }

#end buildUpc() function

    public function buildItemCode($upc) {
        $upc_count = strlen(trim($upc));
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("pei" => "product_extra_info"), array("pei.product_code"));
        if (strlen($upc) == 12)
            $select->where("pei.UPC = ?", $upc);
        else
            $select->where("SUBSTRING(pei.UPC,1,$upc_count) = ?", $upc);
        $select->limit(1);
        $result = $select->query()->fetch();
        return $result['product_code'];
    }

#end buildItemCode() function

    public function buildItemUPCs() {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("pei" => "product_extra_info"), array("pei.product_code", "pei.UPC"));
        $results = $select->query()->fetchAll();
        $final_results = array();
        foreach ($results as $result) {
            $final_results[$result['product_code']] = $result['UPC'];
        }
        return $final_results;
    }

#end buildItemUPCs() function

    public function buildCaseQtys($index = 'item_key') {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("pei" => "product_extra_info"), array("pei.product_code", "pei.UPC", "pei.qty_case", "pei.weight"))
                ->joinLeft(array('p' => 'product'), 'pei.product_id=p.productid', array());
        $results1 = $select->query()->fetchAll();

        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->distinct()
                ->from(array("pei" => "product_extra_info"), array("ip.int_code as product_code", "ip.product_upc as UPC", "pei.qty_case", "pei.weight"))
                ->join(array("ip" => "international_products"), "ip.product_code=pei.product_code", array())
                ->joinLeft(array("p" => "product"), "p.productid=pei.product_id", array());
        $results2 = $select->query()->fetchAll();
        $results = array_merge($results1, $results2);
        $final_results = array();
        if ($index == 'item_key') {
            foreach ($results as $result) {
                $final_results[$result['product_code']] = $result['qty_case'];
                $final_results[$result['product_code'] . '_weight'] = $result['weight'];
            }
        } else if ($index == 'upc') {
            foreach ($results as $result) {
                $final_results[$result['UPC']] = $result['qty_case'];
                $final_results[$result['UPC'] . '_weight'] = $result['weight'];
            }
        }
        return $final_results;
    }

#end buildCaseQtys() function
}

?>