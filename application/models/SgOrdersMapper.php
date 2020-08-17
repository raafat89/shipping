<?php

class Atlas_Model_SgOrdersMapper {

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
            $this->setDbTable("Atlas_Model_DbTable_SgOrders");
        }

        return $this->_dbTable;
    }

#end getDbTable function

    public function save(Atlas_Model_SgOrders $entry) {
        // push the data into an array
        $data = $entry->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($order_id = $entry->getOrder_id()) || $order_id == 0) {
            unset($data["order_id"]);
            $order_id = $this->getDbTable()->insert($data);
            return $order_id;
        } else {
            $this->getDbTable()->update($data, array("order_id = ?" => $order_id));
            return $order_id;
        }
    }

#end save function

    public function remove($order_id) {
        $this->getDbTable()->delete("order_id='$order_id'");
    }

#end remove function

    public function find($order_id) {
        $entry = new Atlas_Model_SgOrders();

        // attempt to locate the row in the database
        // if it doesn"t exist return NULL
        $result = $this->getDbTable()->find($order_id);
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
            $entry = new Atlas_Model_SgOrders();
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
        $select->from(array("t" => "sg_orders"), array('t.*'));

        // return the select statement	
        return $select;
    }

#end selectAll function

    public function buildLatestBatchNo() {
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "sg_orders"), array('MAX(batch_no) AS batch_no'));

        $result = $select->query()->fetch();
        return (int) $result['batch_no'];
    }

#end buildLatestBatchNo function   

    public function buildUserLatestBatchNo($user, $partner) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "sg_orders"), array('MAX(batch_no) AS batch_no'))
                ->where('t.user_id=?', $user)
                ->where('t.partner=?', $partner);
        $result = $select->query()->fetch();
        return (int) $result['batch_no'];
    }

#end buildUserLatestBatchNo function  

    public function buildLastBatch($user_id) {
        $partner = 'SG';
        $last_batch_no = $this->buildUserLatestBatchNo($user_id, $partner);
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "sg_orders"), array('t.*', 'u.name as uploaded_by'))
                ->joinLeft(array("u" => "users"), "t.user_id=u.user_id", array())
                ->where('t.user_id = ?', $user_id)
                ->where('t.batch_no = ?', $last_batch_no)
                ->where('t.partner=?', $partner);
        // return the results
        $results = $select->query()->fetchAll();
        return $results;
    }

#end buildLastBatch function

    public function buildLastBatchSps($user_id, $batch_no) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "sg_orders"), array('t.order_id', 't.cust_no', 't.temp_orderno', 't.conf_orderno', 't.po_no', 't.order_date',
                    't.user_id', 't.msg', 't.batch_no', 't.order_file', 't.status', 't.processed_datetime', 't.partner_code as vendor'));
        if ($batch_no != 0) {
            $select->where('t.batch_no = ?', $batch_no);
        } else {
            $select->where("t.complete != ?", 1);
        }
        $select->where("t.partner='SPS'")
                ->order(array('t.order_id DESC'));
        // return the results
        $results = $select->query()->fetchAll();
        $final_results = array();
        $sg_action_mapper = new Atlas_Model_SgActionsMapper();
        $infor_mapper = new Atlas_Model_Inform3sales();

        //Build Confirmed Order Numbers
        $temp_orders = implode(',', array_filter(array_values(array_unique(array_column($results, 'temp_orderno')))));
        $confirmed_co = $infor_mapper->buildConfCoByTempCo($temp_orders);

        //Build Invoice Numbers
        $conf_orders = array_column($results, 'conf_orderno', 'temp_orderno');
        $conf_orders_array = array();
        foreach ($conf_orders as $key => $value) {
            $split_orders = explode(',', $value);
            if (is_numeric($split_orders[0]) && $split_orders[0] != 0) {
                foreach ($split_orders as $split_order) {
                    if (is_numeric($split_order))
                        $conf_orders_array[] = $split_order;
                }
            }else if (isset($confirmed_co[$key]) && $confirmed_co[$key] != '') {
                $conf_orders_array[] = $confirmed_co[$key];
            }
        }
        $conf_orders = implode(',', $conf_orders_array);
        $invoices = $infor_mapper->buildInvNoBySos($conf_orders);

        //Build order Actions
        $orders_id = implode(',', array_filter(array_values(array_unique(array_column($results, 'order_id')))));
        $actions = $sg_action_mapper->buildOrdersActions($orders_id);

        foreach ($results as $result) {
            $result['infor_co'] = $confirmed_co[$result['temp_orderno']];

            if ($result['conf_orderno'] != '') {
                $split_orders = explode(',', $result['conf_orderno']);
                foreach ($split_orders as $split_order)
                    $result['invoice'][$split_order] = $invoices[$split_order];
            } else if ($result['infor_co'] != '') {
                $result['invoice'] = $invoices[$result['infor_co']];
                $order_info_update = $this->find($result['order_id']);
                $order_info_update->setConf_orderno($result['infor_co']);
                $this->save($order_info_update);
            } else
                $result['invoice'] = '';
            $result['actions'] = $actions[$result['order_id']];

            $final_results[] = $result;
        }
        return $final_results;
    }

#end buildLastBatchSps function

    public function buildLastBatchAtl($user_id) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();

        $select->setIntegrityCheck(false)
                ->from(array("t" => "sg_orders"), array('t.*', 'u.name as uploaded_by'))
                ->joinLeft(array("u" => "users"), "t.user_id=u.user_id", array())
                ->where('t.user_id = ?', $user_id)
                ->where("t.batch_no = (SELECT max(batch_no) FROM sg_orders WHERE user_id = $user_id AND partner='ATL')")
                ->where("t.partner='ATL'");
        // return the results
        $results = $select->query()->fetchAll();
        return $results;
    }

#end buildLastBatchAtl function

    public function buildOrderData($co_po) {
        $sg_action_mapper = new Atlas_Model_SgActionsMapper();
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();

        $select->setIntegrityCheck(false)
                ->from(array("t" => "sg_orders"), array('t.order_id', 't.cust_no', 't.po_no', 't.order_date', 't.conf_orderno', 't.partner_code', 't.order_file', 't.complete'))
                ->where("t.po_no = '$co_po' OR conf_orderno='$co_po'")
                ->where("t.partner='SPS'")
                ->limit(1);
        $result = $select->query()->fetch();
        if (isset($result['order_id'])) {
            $actions = $sg_action_mapper->buildOrderActions($result['order_id']);
            $result['actions'] = $actions;
        }
        return $result;
    }

#end buildOrderData function

    public function buildOrderInfo($id) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();

        $select->setIntegrityCheck(false)
                ->from(array("t" => "sg_orders"), array('t.order_id', 't.cust_no', 't.po_no', 't.conf_orderno', 't.order_file'))
                ->where("t.order_id = '$id'");

        // return the results
        $results = $select->query()->fetch();
        return $results;
    }

#end buildOrderInfo function

    public function buildLinesSequence($data) {
        $line_sequence = array();
        $pointer = 'continue';
        foreach ($data['Order']['LineItem'] as $line1) {
            if (!is_array($line1['OrderLine'])) {
                $line = $data['Order']['LineItem'];
                $pointer = 'break';
            } else {
                $line = $line1;
            }
            if (is_array($line['OrderLine'])) {
                $item_code = $this->buildXmlItemCode($line['OrderLine']);
                $line_sequence[$item_code] = $line['OrderLine']['LineSequenceNumber'];
            }
            if ($pointer == 'break')
                break;
        }
        return $line_sequence;
    }

#end buildLinesSequence function

    public function buildLinesSequenceDup($data) {
        $line_sequence = array();
        $pointer = 'continue';
        $item_seq = array();
        foreach ($data['Order']['LineItem'] as $line1) {
            if (!is_array($line1['OrderLine'])) {
                $line = $data['Order']['LineItem'];
                $pointer = 'break';
            } else {
                $line = $line1;
            }
            if (is_array($line['OrderLine'])) {
                $item_code = $this->buildXmlItemCode($line['OrderLine']);
                $item_seq[$item_code] = (!array_key_exists($item_code, $item_seq)) ? 0 : $item_seq[$item_code] + 1;
                $line_sequence[$item_code][$item_seq[$item_code]] = $line['OrderLine']['LineSequenceNumber'];
            }
            if ($pointer == 'break')
                break;
        }
        return $line_sequence;
    }

#end buildLinesSequenceDup function

    public function buildLinesBuyerPartNo($data) {
        $line_sequence = array();
        $pointer = 'continue';
        foreach ($data['Order']['LineItem'] as $line1) {
            if (!is_array($line1['OrderLine'])) {
                $line = $data['Order']['LineItem'];
                $pointer = 'break';
            } else {
                $line = $line1;
            }
            if (is_array($line['OrderLine'])) {
                $line['OrderLine']['VendorPartNumber'] = $this->buildXmlItemCode($line['OrderLine']);
                $line_sequence[$line['OrderLine']['VendorPartNumber']] = $line['OrderLine']['BuyerPartNumber'];
            }
            if ($pointer == 'break')
                break;
        }
        return $line_sequence;
    }

#end buildLinesBuyerPartNo function

    public function buildLinesBuyerPartNoDup($data) {
        $line_sequence = array();
        $pointer = 'continue';
        $item_seq = array();
        foreach ($data['Order']['LineItem'] as $line1) {
            if (!is_array($line1['OrderLine'])) {
                $line = $data['Order']['LineItem'];
                $pointer = 'break';
            } else {
                $line = $line1;
            }
            if (is_array($line['OrderLine'])) {
                $line['OrderLine']['VendorPartNumber'] = $this->buildXmlItemCode($line['OrderLine']);
                if (!array_key_exists($line['OrderLine']['VendorPartNumber'], $item_seq)) {
                    $item_seq[$line['OrderLine']['VendorPartNumber']] = 0;
                } else {
                    $item_seq[$line['OrderLine']['VendorPartNumber']] = $item_seq[$line['OrderLine']['VendorPartNumber']] + 1;
                }
                $line_sequence[$line['OrderLine']['VendorPartNumber']][$item_seq[$line['OrderLine']['VendorPartNumber']]] = $line['OrderLine']['BuyerPartNumber'];
            }
            if ($pointer == 'break')
                break;
        }
        return $line_sequence;
    }

#end buildLinesBuyerPartNoDup function

    public function buildLinesVendorPartNo($data) {
        $line_sequence = array();
        $pointer = 'continue';
        foreach ($data['Order']['LineItem'] as $line1) {
            if (!is_array($line1['OrderLine'])) {
                $line = $data['Order']['LineItem'];
                $pointer = 'break';
            } else {
                $line = $line1;
            }
            if (is_array($line['OrderLine'])) {
                $item_code = $this->buildXmlItemCode($line['OrderLine']);
                $line_sequence[$item_code] = $line['OrderLine']['VendorPartNumber'];
            }
            if ($pointer == 'break')
                break;
        }
        return $line_sequence;
    }

#end buildLinesBuyerPartNo function

    public function buildLinesConsumerPackageCode($data) {
        $line_sequence = array();
        $pointer = 'continue';
        foreach ($data['Order']['LineItem'] as $line1) {
            if (!is_array($line1['OrderLine'])) {
                $line = $data['Order']['LineItem'];
                $pointer = 'break';
            } else {
                $line = $line1;
            }
            if (is_array($line['OrderLine'])) {
                $item_code = $this->buildXmlItemCode($line['OrderLine']);
                $line_sequence[$item_code] = $line['OrderLine']['ConsumerPackageCode'];
            }
            if ($pointer == 'break')
                break;
        }
        return $line_sequence;
    }

#end buildLinesBuyerPartNo function

    public function buildXmlAddress($original_data, $cust_no = '') {
        $addresses = $original_data['Order']['Header']['Address'];
        $TradingPartnerId = $original_data['Order']['Header']['OrderHeader']['TradingPartnerId'];
        $xml_address = '<Address>
                                    <AddressTypeCode>SF</AddressTypeCode>
                                    <LocationCodeQualifier>1</LocationCodeQualifier>
                                    <AddressLocationNumber>1616040460000</AddressLocationNumber>
                                    <AddressName>Jarrow Formulas</AddressName>
                                    <AddressAlternateName2>Jarrow Formulas</AddressAlternateName2>
                                    <Address1>10715 Shoemaker ave</Address1>
                                    <City>Santa Fe Springs</City>
                                    <State>CA</State>
                                    <PostalCode>90670</PostalCode>
                                </Address>';
        if (in_array($TradingPartnerId, array('080ALLJARROWFOR'))) {
            $xml_address .= '<Address>
                                        <AddressTypeCode>RI</AddressTypeCode>
                                        <AddressName>Jarrow Formulas</AddressName>
                                        <AddressAlternateName2>Jarrow Formulas</AddressAlternateName2>
                                        <Address1>1824 S Robertson</Address1>
                                        <City>Los Angeles</City>
                                        <State>CA</State>
                                        <PostalCode>90035</PostalCode>
                                    </Address>';
        }
        if (is_array($addresses[0]) && count($addresses[0]) > 0) {
            foreach ($addresses as $address) {
                if ($address['AddressTypeCode'] != 'SF') {
                    if ($address['AddressTypeCode'] == 'ST' && !isset($address['Address1']) && !empty($cust_no)) {
                        $infor_mapper = new Atlas_Model_Inform3jiipicklist();
                        $cust_address = $infor_mapper->buildInforRetailerDetail($cust_no);
                        $address['AddressName'] = $cust_address['customer_name'];
                        $address['Address1'] = $cust_address['address_1'];
                        $address['City'] = $cust_address['city'];
                        $address['State'] = $cust_address['state'];
                        $address['PostalCode'] = $cust_address['zip_code'];
                    }
                    if (strpos($address['PostalCode'], '-') !== false) {
                        $zipcode = explode('-', $address['PostalCode']);
                        $address['PostalCode'] = trim($zipcode[0]);
                    }
                    $AddressAlternateName2 = (!empty(trim($address['AddressAlternateName2']))) ? '<AddressAlternateName2>' . $address['AddressAlternateName2'] . '</AddressAlternateName2>' : '';
                    $xml_address .= '
                                <Address>
                                    <AddressTypeCode>' . $address['AddressTypeCode'] . '</AddressTypeCode>
                                    <LocationCodeQualifier>' . $address['LocationCodeQualifier'] . '</LocationCodeQualifier>
                                    <AddressLocationNumber>' . $address['AddressLocationNumber'] . '</AddressLocationNumber>
                                    <AddressName>' . $address['AddressName'] . '</AddressName>' . $AddressAlternateName2 . '
                                    <Address1>' . $address['Address1'] . '</Address1>
                                    <Address2>' . $address['Address2'] . '</Address2>
                                    <City>' . $address['City'] . '</City>
                                    <State>' . $address['State'] . '</State>
                                    <PostalCode>' . $address['PostalCode'] . '</PostalCode>
                                </Address>';
                }
            }
        } else {
            if ($$addresses['AddressTypeCode'] != 'SF') {
                if ($addresses['AddressTypeCode'] == 'ST' && !isset($addresses['Address1'])) {
                    $infor_mapper = new Atlas_Model_Inform3jiipicklist();
                    $cust_address = $infor_mapper->buildInforRetailerDetail($cust_no);
                    $addresses['AddressName'] = $cust_address['customer_name'];
                    $addresses['Address1'] = $cust_address['address_1'];
                    $addresses['City'] = $cust_address['city'];
                    $addresses['State'] = $cust_address['state'];
                    $addresses['PostalCode'] = $cust_address['zip_code'];
                }
                if (strpos($addresses['PostalCode'], '-') !== false) {
                    $zipcode = explode('-', $addresses['PostalCode']);
                    $addresses['PostalCode'] = trim($zipcode[0]);
                }
                $AddressAlternateName2 = (!empty(trim($addresses['AddressAlternateName2']))) ? '<AddressAlternateName2>' . $addresses['AddressAlternateName2'] . '</AddressAlternateName2>' : '';
                $xml_address .= '
                                <Address>
                                    <AddressTypeCode>' . $addresses['AddressTypeCode'] . '</AddressTypeCode>
                                    <LocationCodeQualifier>' . $addresses['LocationCodeQualifier'] . '</LocationCodeQualifier>
                                    <AddressLocationNumber>' . $addresses['AddressLocationNumber'] . '</AddressLocationNumber>
                                    <AddressName>' . $addresses['AddressName'] . '</AddressName>' . $AddressAlternateName2 . '
                                    <Address1>' . $addresses['Address1'] . '</Address1>
                                    <City>' . $addresses['City'] . '</City>
                                    <State>' . $addresses['State'] . '</State>
                                    <PostalCode>' . $addresses['PostalCode'] . '</PostalCode>
                                </Address>';
            }
        }
        return $xml_address;
    }

    public function buildXmlVendor($original_data) {
        $address_location = '';
        if (is_array($original_data['Order']['Header']['Address'][0]) && count($original_data['Order']['Header']['Address'][0]) > 0) {
            foreach ($original_data['Order']['Header']['Address'] as $address) {
                if ($address['AddressTypeCode'] == 'ST' || $address['AddressTypeCode'] == 'BS') {
                    $address_location = $address['AddressLocationNumber'];
                }
            }
        } else {
            $address_location = $original_data['Order']['Header']['Address']['AddressLocationNumber'];
        }
        return ltrim(strtoupper(trim($address_location)), '0');
    }

    public function buildXmlShippingAddress($addresses, $infor_address) {
        if (is_array($addresses[0]) && count($addresses[0]) > 0) {
            $address_key = array_search('ST', array_column($addresses, 'AddressTypeCode'));
            $address_info = $addresses[$address_key];
        } else {
            $address_info = $addresses;
        }
        if (count($address_info) == 0 || !isset($address_info['Address1'])) {
            $infor_address['AddressLocationNumber'] = $address_info['AddressLocationNumber'];
            return $infor_address;
        } else {
            return $address_info;
        }
    }

    public function buildXmlDataVendor($addresses) {
        $address_location = '';
        foreach ($addresses as $address) {
            if ($address->AddressTypeCode == 'ST' || $address->AddressTypeCode == 'BS') {
                $address_location = $address->AddressLocationNumber;
            }
        }
        if ($address_location == '') {
            $address_location = $addresses->AddressLocationNumber;
        }
        return ltrim(strtoupper(trim($address_location)), '0');
    }

    public function buildXmlItemCode($line) {
        $pei_mapper = new Atlas_Model_ProductExtraInfoMapper();
        $asin_mapper = new Atlas_Model_PrintingLabelsBuyerMapper();
        if (isset($line['VendorPartNumber']) && !is_numeric($line['VendorPartNumber'])) {
            $item_code = trim($line['VendorPartNumber']);
        }
        if (!isset($line['VendorPartNumber']) && isset($line['ConsumerPackageCode'])) {
            $item_code = $pei_mapper->buildItemCode(ltrim($line['ConsumerPackageCode'], '0'));
        }
        if (!isset($line['VendorPartNumber']) && isset($line['NatlDrugCode'])) {
            $item_code = $pei_mapper->buildItemCode(ltrim($line['NatlDrugCode'], '0'));
        }
        if (isset($line['VendorPartNumber']) && is_numeric($line['VendorPartNumber'])) {
            $item_code = $pei_mapper->buildItemCode(ltrim($line['VendorPartNumber'], '0'));
        }
        if (!isset($line['VendorPartNumber']) && isset($line['BuyerPartNumber']) && empty($item_code)) {
            $item_code = $asin_mapper->buildItemkey(trim($line['BuyerPartNumber']));
        }
        return trim($item_code);
    }

    public function buildXmlNotes($notes_data) {
        $notes = '';
        if (isset($notes_data) && is_object($notes_data)) {
            foreach ($notes_data as $note) {
                $notes .= $note->Note[0] . ' ';
            }
        }
        return trim($notes);
    }

    public function buildXmlShipDate($xml_dates) {
        $arr = (array) $xml_dates;
        if (isset($arr) && !empty($arr)) {
            unset($date_data);
            $date_data = array();
            foreach ($xml_dates as $date) {
                $qualifier = $date->DateTimeQualifier;
                if ($qualifier == '010') {
                    $date_data[] = 'RS: ' . date('m/d/Y', strtotime($date->Date));
                } else if ($qualifier == '002') {
                    $date_data[] = 'RD: ' . date('m/d/Y', strtotime($date->Date));
                } else if ($qualifier == '064') {
                    $date_data[] = 'ED: ' . date('m/d/Y', strtotime($date->Date));
                } else if ($qualifier == '063') {
                    $date_data[] = 'LD: ' . date('m/d/Y', strtotime($date->Date));
                }
            }
        }
        return $date_data;
    }

    public function buildXmlDate($dates) {
        $date_info = array();
        unset($date_info);
        if (!is_array($dates[0]) && count($dates[0]) < 1) {
            $dates_info[0] = $dates;
        } else {
            $dates_info = $dates;
        }
        if (is_array($dates_info) && count($dates_info) > 0) {
            foreach ($dates_info as $date) {
                if ($date['DateTimeQualifier'] == '010') {
                    $date_info['ship_date'] = $date['Date'];
                } else if ($date['DateTimeQualifier'] == '002') {
                    $date_info['delivery_date'] = $date['Date'];
                } else {
                    $date_info[$date['DateTimeQualifier']] = $date['Date'];
                }
            }
        }
        return $date_info;
    }

    public function buildXmlAck($original_data, $ack_data) {
        $dates = $this->buildXmlDate($original_data['Order']['Header']['Dates']);
        $TradingPartnerId = $original_data['Order']['Header']['OrderHeader']['TradingPartnerId'];
        $shipping_prefix_mapper = new Atlas_Model_ShippingPrefixMapper();
        $vendor_info = $shipping_prefix_mapper->buildVendorInfo($TradingPartnerId);
        $pei_mapper = new Atlas_Model_ProductExtraInfoMapper();
        $xml_address = $this->buildXmlAddress($original_data, $ack_data['CustNo']);
        $xml_lines = '';
        $change = 0;
        $order_dev_date = $ack_data[0]['date'];
        $pointer = 'continue';
        $item_seq = array();
        foreach ($original_data['Order']['LineItem'] as $line1) {
            if (!is_array($line1['OrderLine'])) {
                $line = $original_data['Order']['LineItem'];
                $pointer = 'break';
            } else {
                $line = $line1;
            }
            $item_code = $this->buildXmlItemCode($line['OrderLine']);
            $item_seq[$item_code] = (!array_key_exists($item_code, $item_seq)) ? 0 : $item_seq[$item_code] + 1;
            $item_ack_data = $ack_data[$item_code][$item_seq[$item_code]];
            $item_ack_data['uom'] = ($item_ack_data['uom'] == '') ? $line['OrderLine']['OrderQtyUOM'] : $item_ack_data['uom'];
            $order_dev_date = ($item_ack_data['date'] != '') ? $item_ack_data['date'] : date('Y-m-d', strtotime("+2 day"));

            if (trim(strtoupper($line['OrderLine']['OrderQtyUOM'])) == 'CA') {
                $case_qty = (isset($line['PhysicalDetails']['PackValue'])) ? $line['PhysicalDetails']['PackValue'] : $pei_mapper->buildCaseQty2($item_code);
                $ordered_qty = $line['OrderLine']['OrderQty'] * $case_qty;
            } else if (trim(strtoupper($line['OrderLine']['OrderQtyUOM'])) == 'EA') {
                $ordered_qty = $line['OrderLine']['OrderQty'];
            }
            $notes = '';
            if ($item_ack_data['qty'] == $ordered_qty) {
                $ItemStatusCode = 'IA';
                $ItemScheduleQty = $item_ack_data['qty'];
                if (abs((($item_ack_data['price'] * $ordered_qty) - ($line['OrderLine']['PurchasePrice'] * $line['OrderLine']['OrderQty'])) > 0.00001) && isset($line['OrderLine']['PurchasePrice']) && strpos($vendor_info['item_status'], 'IP') !== false) {
                    $ItemStatusCode = 'IP';
                    $change++;
                }
            } else if ($item_ack_data['qty'] != $ordered_qty && (int) $item_ack_data['qty'] != 0) {
                $ItemStatusCode = 'IQ';
                $ItemScheduleQty = $item_ack_data['qty'];
                $change++;
            } else if ((int) $item_ack_data['qty'] == 0) {
                $ItemStatusCode = 'IR';
                $ItemScheduleQty = $line['OrderLine']['OrderQty'];
                $change++;
                $notes = "<Notes>
                                        <NoteCode>GEN</NoteCode>
                                        <Note>Item is Out Of Stock</Note>
                                    </Notes>";
            }
//                    if($item_ack_data['qty'] == $ordered_qty && $item_ack_data['date']!=$item_ack_data['item_date'] && strpos($vendor_info['item_status'], 'DR') !== false ){
//                        $ItemStatusCode = 'DR';
//                        $ItemScheduleQty = $line['OrderLine']['OrderQty'];
//                        $change++;
//                    }
            if (isset($dates['delivery_date']) || in_array($TradingPartnerId, array('BIYALLJARROWFOR', '080ALLJARROWFOR'))) {
                $date_qual_code = '067';
                $date_qual = $order_dev_date;
            } else {
                $date_qual_code = '068';
                $date_qual = $order_dev_date;
            }
            if (isset($line['PhysicalDetails']) && is_array($line['PhysicalDetails'])) {
                $pq = ($line['PhysicalDetails']['PackQualifier'] != '') ? '<PackQualifier>' . $line['PhysicalDetails']['PackQualifier'] . '</PackQualifier>' : '';
                $pv = ($line['PhysicalDetails']['PackValue'] != '') ? '<PackValue>' . $line['PhysicalDetails']['PackValue'] . '</PackValue>' : '';
                $ps = ($line['PhysicalDetails']['PackSize'] != '') ? '<PackSize>' . $line['PhysicalDetails']['PackSize'] . '</PackSize>' : '';
                $pu = ($line['PhysicalDetails']['PackUOM'] != '') ? '<PackUOM>' . $line['PhysicalDetails']['PackUOM'] . '</PackUOM>' : '';
                $physical_details = '<PhysicalDetails>' . $pq . $pv . $ps . $pu . '</PhysicalDetails>';
            } else {
                $physical_details = '';
            }
            $ConsumerPackageCode = (isset($line['OrderLine']['ConsumerPackageCode'])) ? $line['OrderLine']['ConsumerPackageCode'] : $item_ack_data['UPC'];
            $VendorPartNumber = (isset($line['OrderLine']['VendorPartNumber'])) ? $line['OrderLine']['VendorPartNumber'] : $item_code;
            $NatlDrugCode = (isset($line['OrderLine']['NatlDrugCode'])) ? '<NatlDrugCode>' . $line['OrderLine']['NatlDrugCode'] . '</NatlDrugCode>' : '';
            $BuyerPartNumber = (isset($line['OrderLine']['BuyerPartNumber'])) ? '<BuyerPartNumber>' . $line['OrderLine']['BuyerPartNumber'] . '</BuyerPartNumber>' : '';
            $PurchasePrice = (isset($line['OrderLine']['PurchasePrice'])) ? '<PurchasePrice>' . $line['OrderLine']['PurchasePrice'] . '</PurchasePrice>' : '';
            $LineSequenceNumber = (isset($line['OrderLine']['LineSequenceNumber'])) ? '<LineSequenceNumber>' . $line['OrderLine']['LineSequenceNumber'] . '</LineSequenceNumber>' : '';
            $ProductDescription = (isset($line['ProductOrItemDescription']['ProductDescription'])) ? $line['ProductOrItemDescription']['ProductDescription'] : $item_ack_data['descr'];
            $xml_lines .= '
                    <LineItem>
                        <OrderLine>
                            ' . $LineSequenceNumber . '
                            ' . $BuyerPartNumber . '
                            <VendorPartNumber>' . $VendorPartNumber . '</VendorPartNumber>
                            <ConsumerPackageCode>' . $ConsumerPackageCode . '</ConsumerPackageCode>
                            ' . $NatlDrugCode . '
                            <OrderQty>' . $line['OrderLine']['OrderQty'] . '</OrderQty>
                            <OrderQtyUOM>' . $line['OrderLine']['OrderQtyUOM'] . '</OrderQtyUOM>
                            ' . $PurchasePrice . '
                        </OrderLine>
                        <LineItemAcknowledgement>
                            <ItemStatusCode>' . $ItemStatusCode . '</ItemStatusCode>
                            <ItemScheduleQty>' . $ItemScheduleQty . '</ItemScheduleQty>
                            <ItemScheduleUOM>' . $item_ack_data['uom'] . '</ItemScheduleUOM>
                            <ItemScheduleQualifier>' . $date_qual_code . '</ItemScheduleQualifier>
                            <ItemScheduleDate>' . $date_qual . '</ItemScheduleDate>
                        </LineItemAcknowledgement>
                        <ProductOrItemDescription>
                            <ProductCharacteristicCode>08</ProductCharacteristicCode>
                            <ProductDescription>' . $ProductDescription . '</ProductDescription>
                        </ProductOrItemDescription>
                        ' . $physical_details . '
                        ' . $notes . '
                    </LineItem>';
            if ($pointer == 'break')
                break;
        }
        $AcknowledgementType = ($change > 0) ? 'AC' : 'AD';
        $ref_section = '';
        if (isset($original_data['Order']['Header']['References']) && is_array($original_data['Order']['Header']['References'])) {
            $ref_section = '<References>';
            if ($original_data['Order']['Header']['References']['ReferenceQual'] != '')
                $ref_section .= '<ReferenceQual>' . $original_data['Order']['Header']['References']['ReferenceQual'] . '</ReferenceQual>';
            if ($original_data['Order']['Header']['References']['ReferenceID'] != '')
                $ref_section .= '<ReferenceID>' . $original_data['Order']['Header']['References']['ReferenceID'] . '</ReferenceID>';
            $ref_section .= '</References>';
        }else {
            $ref_section = '';
        }
        $BuyersCurrency = (isset($original_data['Order']['Header']['OrderHeader']['BuyersCurrency'])) ? '<BuyersCurrency>' . $original_data['Order']['Header']['OrderHeader']['BuyersCurrency'] . '</BuyersCurrency>' : '';
        $Department = (isset($original_data['Order']['Header']['OrderHeader']['Department'])) ? '<Department>' . $original_data['Order']['Header']['OrderHeader']['Department'] . '</Department>' : '';
        $Vendor_head = (isset($original_data['Order']['Header']['OrderHeader']['Vendor'])) ? '<Vendor>' . $original_data['Order']['Header']['OrderHeader']['Vendor'] . '</Vendor>' : '';
        $xml_data = '<?xml version="1.0" encoding="utf-8"?>
<OrderAcks xmlns="http://www.spscommerce.com/RSX">
    <OrderAck>
        <Header>
            <OrderHeader>
                <TradingPartnerId>' . $original_data['Order']['Header']['OrderHeader']['TradingPartnerId'] . '</TradingPartnerId>
                <PurchaseOrderNumber>' . $original_data['Order']['Header']['OrderHeader']['PurchaseOrderNumber'] . '</PurchaseOrderNumber>
                <TsetPurposeCode>00</TsetPurposeCode>
                <PurchaseOrderDate>' . $original_data['Order']['Header']['OrderHeader']['PurchaseOrderDate'] . '</PurchaseOrderDate>
                <PrimaryPOTypeCode>' . $original_data['Order']['Header']['OrderHeader']['PrimaryPOTypeCode'] . '</PrimaryPOTypeCode>
                <AcknowledgementType>' . $AcknowledgementType . '</AcknowledgementType>
                <AcknowledgementDate>' . date('Y-m-d') . '</AcknowledgementDate>
                ' . $BuyersCurrency . '
                ' . $Department . '
                ' . $Vendor_head . '
            </OrderHeader>
            <Dates>
                <DateTimeQualifier>' . $date_qual_code . '</DateTimeQualifier>
                <Date>' . $date_qual . '</Date>
            </Dates>
            ' . $xml_address . '
            ' . $ref_section . '
        </Header>
        ' . $xml_lines . '
        <Summary>
            <TotalLineItemNumber>' . $original_data['Order']['Summary']['TotalLineItemNumber'] . '</TotalLineItemNumber>
        </Summary>
    </OrderAck>
</OrderAcks>';
        return $xml_data;
    }

    public function buildXmlInvoice($original_data, $inv_data, $inv_lines) {
        $line_sequence = $this->buildLinesSequenceDup($original_data);
        $xml_address = $this->buildXmlAddress($original_data, $inv_data['Vendor']);
        $xml_buyer_item = $this->buildLinesBuyerPartNoDup($original_data);
        $xml_vendor_item = $this->buildLinesVendorPartNo($original_data);
        $xml_lines = '';
        $total_qty = 0;
        $item_seq = array();
        foreach ($inv_lines as $line) {
            $total_qty += $line['InvoiceQty'];
            $item_code = (isset($xml_vendor_item[$line['VendorPartNumber']])) ? $xml_vendor_item[$line['VendorPartNumber']] : $line['VendorPartNumber'];
            $item_seq[$item_code] = (!array_key_exists($item_code, $item_seq)) ? 0 : $item_seq[$item_code] + 1;
            $LineSequenceNumber = (isset($line_sequence[trim($line['VendorPartNumber'])][$item_seq[$item_code]])) ? '<LineSequenceNumber>' . $line_sequence[trim($line['VendorPartNumber'])][$item_seq[$item_code]] . '</LineSequenceNumber>' : '';
            $BuyerPartNumber = (isset($xml_buyer_item[strtoupper(trim($line['VendorPartNumber']))][$item_seq[$item_code]])) ? '<BuyerPartNumber>' . $xml_buyer_item[strtoupper(trim($line['VendorPartNumber']))][$item_seq[$item_code]] . '</BuyerPartNumber>' : '';
            $xml_lines .= '
                    <LineItem>
                        <InvoiceLine>
                            ' . $LineSequenceNumber . '
                            ' . $BuyerPartNumber . '
                            <VendorPartNumber>' . $item_code . '</VendorPartNumber>
                            <UPCCaseCode>' . $line['UPCCaseCode'] . '</UPCCaseCode>
                            <InvoiceQty>' . $line['InvoiceQty'] . '</InvoiceQty>
                            <InvoiceQtyUOM>' . trim($line['InvoiceQtyUOM']) . '</InvoiceQtyUOM>
                            <PurchasePrice>' . round($line['PurchasePrice'], 3) . '</PurchasePrice>
                            <ExtendedItemTotal>' . round($line['ExtendedItemTotal'], 2) . '</ExtendedItemTotal>
                        </InvoiceLine>
                        <ProductOrItemDescription>
                            <ProductCharacteristicCode>08</ProductCharacteristicCode>
                            <ProductDescription>' . $line['ProductDescription'] . '</ProductDescription>
                        </ProductOrItemDescription>
                        <PhysicalDetails>
                            <PackQualifier>OU</PackQualifier>
                            <PackValue>1</PackValue>
                        </PhysicalDetails>
                    </LineItem>';
        }
        $terms_desc = (isset($original_data['Order']['Header']['PaymentTerms']['TermsDescription'])) ? $original_data['Order']['Header']['PaymentTerms']['TermsDescription'] : $inv_data['TermsDescription'];
        $FOBLocationQualifier = (isset($original_data['Order']['Header']['FOBRelatedInstruction']['FOBLocationQualifier'])) ? '<FOBLocationQualifier>' . $original_data['Order']['Header']['FOBRelatedInstruction']['FOBLocationQualifier'] . '</FOBLocationQualifier>' : '';
        $org_CarrierTransMethodCode = $original_data['Order']['Header']['CarrierInformation']['CarrierTransMethodCode'];
        $CarrierInformation = (!empty(trim($org_CarrierTransMethodCode))) ? '<CarrierInformation><CarrierTransMethodCode>' . $org_CarrierTransMethodCode . '</CarrierTransMethodCode></CarrierInformation>' : '';
        $ref_section = '';
        if (isset($original_data['Order']['Header']['References']) && is_array($original_data['Order']['Header']['References'])) {
            $ref_section = '<References>';
            if ($original_data['Order']['Header']['References']['ReferenceQual'] != '')
                $ref_section .= '<ReferenceQual>' . $original_data['Order']['Header']['References']['ReferenceQual'] . '</ReferenceQual>';
            if ($original_data['Order']['Header']['References']['ReferenceID'] != '')
                $ref_section .= '<ReferenceID>' . $original_data['Order']['Header']['References']['ReferenceID'] . '</ReferenceID>';
            $ref_section .= '</References>';
        }else {
            $ref_section = '';
        }
        $amt_diff = round($inv_data['TotalAmount'], 2) - round($inv_data['TotalSalesAmount'], 2);
        if ($amt_diff >= 0.5) {
            $inv_charges = "<ChargesAllowances>
                                        <AllowChrgIndicator>C</AllowChrgIndicator>
                                        <AllowChrgCode>H090</AllowChrgCode>
                                        <AllowChrgAmt>$amt_diff</AllowChrgAmt>
                                        <AllowChrgHandlingCode>06</AllowChrgHandlingCode>
                                        <AllowChrgHandlingDescription>QTY_MIN_CHARGE</AllowChrgHandlingDescription>
                                    </ChargesAllowances>";
        } else {
            $inv_charges = '';
        }
        $Vendor = (isset($original_data['Order']['Header']['OrderHeader']['Vendor'])) ? '<Vendor>' . $original_data['Order']['Header']['OrderHeader']['Vendor'] . '</Vendor>' : '';
        $TermsType = (in_array($original_data['Order']['Header']['OrderHeader']['TradingPartnerId'], array('080ALLJARROWFOR'))) ? '01' : '14';
        $xml_invoice_no = (in_array($original_data['Order']['Header']['OrderHeader']['TradingPartnerId'], array('2YAALLJARROWFOR'))) ? trim(str_replace('ZZZZ', 'SALES', $inv_data['InvoiceNumber'])) : trim($inv_data['InvoiceNumber']);
        $xml_data = '<?xml version="1.0" encoding="utf-8"?>
<Invoices xmlns="http://www.spscommerce.com/RSX">
    <Invoice>
        <Header>
            <InvoiceHeader>
                <TradingPartnerId>' . $original_data['Order']['Header']['OrderHeader']['TradingPartnerId'] . '</TradingPartnerId>
                <InvoiceNumber>' . $xml_invoice_no . '</InvoiceNumber>
                <InvoiceDate>' . date('Y-m-d', strtotime($inv_data['InvoiceDate'])) . '</InvoiceDate>
                <PurchaseOrderDate>' . date('Y-m-d', strtotime($inv_data['PurchaseOrderDate'])) . '</PurchaseOrderDate>
                <PurchaseOrderNumber>' . $original_data['Order']['Header']['OrderHeader']['PurchaseOrderNumber'] . '</PurchaseOrderNumber>
                <PrimaryPOTypeCode>' . $original_data['Order']['Header']['OrderHeader']['PrimaryPOTypeCode'] . '</PrimaryPOTypeCode>
                <InvoiceTypeCode>' . $inv_data['InvoiceTypeCode'] . '</InvoiceTypeCode>' . $Vendor . '
                <ShipDate>' . date('Y-m-d', strtotime($inv_data['ShipDate'])) . '</ShipDate>
            </InvoiceHeader>
            <PaymentTerms>
                <TermsType>' . $TermsType . '</TermsType>
                <TermsBasisDateCode>3</TermsBasisDateCode>
                <TermsDiscountPercentage>' . $inv_data['TermsDiscountPercentage'] . '</TermsDiscountPercentage>
                <TermsDiscountDueDays>' . $inv_data['TermsDiscountDueDays'] . '</TermsDiscountDueDays>
                <TermsNetDueDate>' . date('Y-m-d', strtotime($inv_data['TermsNetDueDate'])) . '</TermsNetDueDate>
                <TermsNetDueDays>' . $inv_data['TermsNetDueDays'] . '</TermsNetDueDays>
                <TermsDiscountAmount>' . $inv_data['TermsDiscountAmount'] . '</TermsDiscountAmount>
                <TermsDeferredDueDate>' . date('Y-m-d', strtotime($inv_data['TermsDeferredDueDate'])) . '</TermsDeferredDueDate>
                <TermsDeferredAmountDue>' . round($inv_data['TermsDeferredAmountDue'], 2) . '</TermsDeferredAmountDue>
                <TermsDescription>' . $terms_desc . '</TermsDescription>
                <TermsDueDay>' . $inv_data['TermsDueDay'] . '</TermsDueDay>
                <PaymentMethodCode>' . $inv_data['PaymentMethodCode'] . '</PaymentMethodCode>
            </PaymentTerms>
            <Dates>
                <DateTimeQualifier>002</DateTimeQualifier>
                <Date>' . date('Y-m-d') . '</Date>
            </Dates>
            ' . $xml_address . '
            ' . $ref_section . '
            ' . $inv_charges . '
            <FOBRelatedInstruction>
                <FOBPayCode>PP</FOBPayCode>
                ' . $FOBLocationQualifier . '
            </FOBRelatedInstruction>
            ' . $CarrierInformation . '
            <QuantityTotals>
                <QuantityTotalsQualifier>SQT</QuantityTotalsQualifier>
                <Quantity>' . $total_qty . '</Quantity>
                <QuantityUOM>EA</QuantityUOM>
            </QuantityTotals>
        </Header>
        ' . $xml_lines . '
        <Summary>
            <TotalAmount>' . round($inv_data['TotalAmount'], 2) . '</TotalAmount>
            <TotalSalesAmount>' . round($inv_data['TotalSalesAmount'], 2) . '</TotalSalesAmount>
            <TotalTermsDiscountAmount>' . $inv_data['TotalTermsDiscountAmount'] . '</TotalTermsDiscountAmount>
            <TotalLineItemNumber>' . $inv_data['TotalLineItemNumber'] . '</TotalLineItemNumber>
            <InvoiceAmtDueByTermsDate>' . round($inv_data['InvoiceAmtDueByTermsDate'], 2) . '</InvoiceAmtDueByTermsDate>
        </Summary>
    </Invoice>
</Invoices>';
        return $xml_data;
    }

    public function buildXmlShipment($order_info, $original_data, $inv_data, $inv_lines) {
        $xml_vendor_item = $this->buildLinesVendorPartNo($original_data);
        $xml_con_pk_code = $this->buildLinesConsumerPackageCode($original_data);
        $fedex_mapper = new Atlas_Model_FEWriteBackMapper();
        $shipping_data = $fedex_mapper->buildShippingData($inv_data['OrderNo']);
        $line_sequence = $this->buildLinesSequence($original_data);
        $xml_address = $this->buildXmlAddress($original_data, $inv_data['Vendor']);
        $xml_lines = '';
        $total_qty = 0;
        $total_weight = 0;
        $functions = new Utility_Functions;
        $ship_serial = new Atlas_Model_ShippingSerial();
        $ship_serial_mapper = new Atlas_Model_ShippingSerialMapper();
        $last_assigned_serial = $ship_serial_mapper->BuildLastSerial();
        $TradingPartnerId = $original_data['Order']['Header']['OrderHeader']['TradingPartnerId'];
        $shipping_prefix_mapper = new Atlas_Model_ShippingPrefixMapper();
        $vendor_info = $shipping_prefix_mapper->buildVendorInfo($TradingPartnerId);
        $shipping_serial_id = $vendor_info['prefix'] . $last_assigned_serial;
        if ($vendor_info['pack'] == 'single') {
            $shipping_serial_id = $functions->shipping_serial($shipping_serial_id);    //Parse SSCC-18
            foreach ($inv_lines as $line) {
                $total_qty += $line['InvoiceQty'];
                $total_weight += $line['TotalLineWeight'];
                $item_code = (isset($xml_vendor_item[$line['VendorPartNumber']])) ? $xml_vendor_item[$line['VendorPartNumber']] : $line['VendorPartNumber'];
                $LineSequenceNumber = (isset($line_sequence[$line['VendorPartNumber']])) ? '<LineSequenceNumber>' . $line_sequence[trim($line['VendorPartNumber'])] . '</LineSequenceNumber>' : '';
                $ConsumerPackageCode = (isset($xml_con_pk_code[$line['VendorPartNumber']])) ? $xml_con_pk_code[$line['VendorPartNumber']] : $line['UPCCaseCode'];
                $xml_sub_lines .= '
                            <ItemLevel>
                                <ShipmentLine>
                                    ' . $LineSequenceNumber . '
                                    <VendorPartNumber>' . $item_code . '</VendorPartNumber>
                                    <ConsumerPackageCode>' . $ConsumerPackageCode . '</ConsumerPackageCode>
                                    <ShipQty>' . $line['InvoiceQty'] . '</ShipQty>
                                    <ShipQtyUOM>' . $line['InvoiceQtyUOM'] . '</ShipQtyUOM>
                                </ShipmentLine>
                                <PhysicalDetails>
                                    <PackQualifier>IN</PackQualifier>
                                    <PackValue>' . $line['InvoiceQty'] . '</PackValue>
                                </PhysicalDetails>
                                <ProductOrItemDescription>
                                    <ProductCharacteristicCode>08</ProductCharacteristicCode>
                                    <ProductDescription>' . $line['ProductDescription'] . '</ProductDescription>
                                </ProductOrItemDescription>
                            </ItemLevel>';
            }
            $xml_lines .= '
                        <PackLevel>
                            <Pack>
                                <PackLevelType>P</PackLevelType>
                                <ShippingSerialID>' . $shipping_serial_id . '</ShippingSerialID>
                            </Pack>
                            ' . $xml_sub_lines . '
                        </PackLevel>';
            $ship_serial->setOrder_id($order_info['order_id'])
                    ->setSerial_number(substr($shipping_serial_id, -8))
                    ->setSerial_date(date('Y-m-d'));
            $ship_serial_mapper->save($ship_serial);
        } else {
            foreach ($inv_lines as $line) {
                $shipping_serial_id = $functions->shipping_serial($shipping_serial_id);    //Parse SSCC-18
                $total_qty += $line['InvoiceQty'];
                $total_weight += $line['TotalLineWeight'];
                $item_code = (isset($xml_vendor_item[$line['VendorPartNumber']])) ? $xml_vendor_item[$line['VendorPartNumber']] : $line['VendorPartNumber'];
                $LineSequenceNumber = (isset($line_sequence[$line['VendorPartNumber']])) ? '<LineSequenceNumber>' . $line_sequence[trim($line['VendorPartNumber'])] . '</LineSequenceNumber>' : '';
                $ConsumerPackageCode = (isset($xml_con_pk_code[$line['VendorPartNumber']])) ? $xml_con_pk_code[$line['VendorPartNumber']] : $line['UPCCaseCode'];
                $xml_lines .= '
                        <PackLevel>
                            <Pack>
                                <PackLevelType>P</PackLevelType>
                                <ShippingSerialID>' . $shipping_serial_id . '</ShippingSerialID>
                            </Pack>
                            <ItemLevel>
                                <ShipmentLine>
                                    ' . $LineSequenceNumber . '
                                    <VendorPartNumber>' . $item_code . '</VendorPartNumber>
                                    <ConsumerPackageCode>' . $ConsumerPackageCode . '</ConsumerPackageCode>
                                    <ShipQty>' . $line['InvoiceQty'] . '</ShipQty>
                                    <ShipQtyUOM>' . $line['InvoiceQtyUOM'] . '</ShipQtyUOM>
                                </ShipmentLine>
                                <PhysicalDetails>
                                    <PackQualifier>IN</PackQualifier>
                                    <PackValue>' . $line['InvoiceQty'] . '</PackValue>
                                </PhysicalDetails>
                                <ProductOrItemDescription>
                                    <ProductCharacteristicCode>08</ProductCharacteristicCode>
                                    <ProductCharacteristicCode>' . $line['ProductDescription'] . '</ProductCharacteristicCode>
                                </ProductOrItemDescription>
                            </ItemLevel>
                        </PackLevel>';
                $ship_serial->setOrder_id($order_info['order_id'])
                        ->setSerial_number(substr($shipping_serial_id, -8))
                        ->setSerial_date(date('Y-m-d'));
                $ship_serial_mapper->save($ship_serial);
            }
        }
        $order_weight = (isset($shipping_data['totalweightinuom']) && (int) $shipping_data['totalweightinuom'] > 0) ? $shipping_data['totalweightinuom'] : $total_weight;
        $CarrierProNumber = (isset($shipping_data['trackingnumber'])) ? $shipping_data['trackingnumber'] : $inv_data['OrderNo'];
        $carrier_routing = (isset($original_data['Order']['Header']['CarrierInformation']['CarrierRouting'])) ? $original_data['Order']['Header']['CarrierInformation']['CarrierRouting'] : $inv_data['carrier_routing'];
        $xml_data = '<?xml version="1.0" encoding="utf-8"?>
<Shipments xmlns="http://www.spscommerce.com/RSX">
    <Shipment>
        <Header>
            <ShipmentHeader>
                <TradingPartnerId>' . $TradingPartnerId . '</TradingPartnerId>
                <ShipmentIdentification>Sh' . $inv_data['OrderNo'] . '</ShipmentIdentification>
                <ShipDate>' . date('Y-m-d') . '</ShipDate>
                <TsetPurposeCode>00</TsetPurposeCode>
                <ShipNoticeDate>' . date('Y-m-d') . '</ShipNoticeDate>
                <ShipNoticeTime>' . date('H:i:s') . '</ShipNoticeTime>
                <ASNStructureCode>0001</ASNStructureCode>
                <BillOfLadingNumber>' . $inv_data['OrderNo'] . '</BillOfLadingNumber>
                <CarrierProNumber>' . $CarrierProNumber . '</CarrierProNumber>
                <CurrentScheduledDeliveryDate>' . date("Y-m-d", strtotime("+2 day")) . '</CurrentScheduledDeliveryDate>
                <CurrentScheduledDeliveryTime>' . date("H:i:s", strtotime("+2 day")) . '</CurrentScheduledDeliveryTime>
            </ShipmentHeader>
            <Dates>
                <DateTimeQualifier>017</DateTimeQualifier>
                <Date>' . date("Y-m-d", strtotime("+2 day")) . '</Date>
            </Dates>
            <References>
                <ReferenceQual>BM</ReferenceQual>
                <ReferenceID>' . $inv_data['OrderNo'] . '</ReferenceID>
            </References>
            ' . $xml_address . '
            <CarrierInformation>
                <StatusCode>CL</StatusCode>
                <CarrierTransMethodCode>M</CarrierTransMethodCode>
                <CarrierAlphaCode>FDEX</CarrierAlphaCode>
                <CarrierRouting>' . $carrier_routing . '</CarrierRouting>
                <EquipmentDescriptionCode>' . $original_data['Order']['Header']['CarrierInformation']['EquipmentDescriptionCode'] . '</EquipmentDescriptionCode>
                <CarrierEquipmentNumber>' . $original_data['Order']['Header']['CarrierInformation']['CarrierEquipmentNumber'] . '</CarrierEquipmentNumber>
            </CarrierInformation>
            <FOBRelatedInstruction>
                <FOBPayCode>PP</FOBPayCode>
            </FOBRelatedInstruction>
            <QuantityAndWeight>
                <PackingMedium>CTN</PackingMedium>
                <LadingQuantity>' . $total_qty . '</LadingQuantity>
                <Weight>' . round($order_weight, 2) . '</Weight>
                <WeightUOM>LB</WeightUOM>
            </QuantityAndWeight>
        </Header>
        <OrderLevel>
            <OrderHeader>
                <PurchaseOrderNumber>' . $original_data['Order']['Header']['OrderHeader']['PurchaseOrderNumber'] . '</PurchaseOrderNumber>
                <Vendor>' . $original_data['Order']['Header']['OrderHeader']['Vendor'] . '</Vendor>
            </OrderHeader>
            ' . $xml_lines . '
        </OrderLevel>
        <Summary>
            <TotalLineItemNumber>' . count($inv_lines) . '</TotalLineItemNumber>
        </Summary>
    </Shipment>
</Shipments>';
        return $xml_data;
    }

    public function buildXmlShipment1($original_data, $asn_data, $printing_lables, $cust_no) {
        $line_sequence = $this->buildLinesSequence($original_data);
        $xml_address = $this->buildXmlAddress($original_data, $cust_no);
        $po_number = $original_data['Order']['Header']['OrderHeader']['PurchaseOrderNumber'];
        $asn_header = $asn_data['header'];
        $asn_lines = $asn_data['lines'];
        $xml_lines = '';
        $lines_no = array();
        $carrier_pro_sec = '';
        $TradingPartnerId = $original_data['Order']['Header']['OrderHeader']['TradingPartnerId'];
        foreach ($printing_lables as $line) {
            //Build Carrier Pro Number
            $line_track_no = $asn_lines[array_search($line['line'], array_column($asn_lines, 'line_no'))]['track_no'];
            if (in_array($TradingPartnerId, array('080ALLJARROWFOR')) && !empty(trim($line_track_no)) && (int) $asn_header['number_of_pallets'] == 0) {
                if (array_key_exists($line['line'], $lines_no)) {
                    $lines_no[$line['line']] = $lines_no[$line['line']] + 1;
                } else {
                    $c_pros = $this->buildXmlCarrierPro($line_track_no);
                    $lines_no[$line['line']] = 0;
                }
                $carrier_pro_no = $this->buildCarrierPro($c_pros, $lines_no[$line['line']]);
                $carrier_pro_sec = (isset($carrier_pro_no) && !empty(trim($carrier_pro_no))) ? '<References><ReferenceQual>CN</ReferenceQual><ReferenceID>' . $carrier_pro_no . '</ReferenceID></References>' : '';
            }
            //Buyer Part Number
            $BuyerPartNumber = (isset($line['buyer_item'])) ? '<BuyerPartNumber>' . $line['buyer_item'] . '</BuyerPartNumber>' : '';
            //Line Sequence Number
            $line_seq = (isset($line['line_seq']) && !empty(trim($line['line_seq']))) ? $line['line_seq'] : $line_sequence[trim($line['vdr_item'])];
            $LineSequenceNumber = (isset($line_seq) && !empty(trim($line_seq))) ? '<LineSequenceNumber>' . $line_seq . '</LineSequenceNumber>' : '';

            //Line Data
            $xml_lines .= '
                        <PackLevel>
                            <Pack>
                                <PackLevelType>' . $asn_lines[array_search($line['line'], array_column($asn_lines, 'line_no'))]['pack_type'] . '</PackLevelType>
                                <ShippingSerialID>' . $line['carton_id'] . '</ShippingSerialID>
                            </Pack>' . $carrier_pro_sec . '
                            <ItemLevel>
                                <ShipmentLine>
                                    ' . $LineSequenceNumber . '
                                    ' . $BuyerPartNumber . '
                                    <VendorPartNumber>' . trim($line['vdr_item']) . '</VendorPartNumber>
                                    <ConsumerPackageCode>' . trim($line['upc']) . '</ConsumerPackageCode>
                                    <ShipQty>' . $line['ctn_qty'] . '</ShipQty>
                                    <ShipQtyUOM>EA</ShipQtyUOM>
                                </ShipmentLine>
                                <PhysicalDetails>
                                    <PackQualifier>IN</PackQualifier>
                                    <PackValue>' . $line['ctn_qty'] . '</PackValue>
                                </PhysicalDetails>
                                <ProductOrItemDescription>
                                    <ProductCharacteristicCode>08</ProductCharacteristicCode>
                                    <ProductDescription>' . $line['descr'] . '</ProductDescription>
                                </ProductOrItemDescription>
                                <Dates>
                                    <DateTimeQualifier>036</DateTimeQualifier>
                                    <Date>' . date('Y-m-d', strtotime($line['expiration_date'])) . '</Date>
                                </Dates>
                                <References>
                                    <ReferenceQual>LT</ReferenceQual>
                                    <ReferenceID>' . $line['lot_number'] . '</ReferenceID>
                                </References>
                            </ItemLevel>
                        </PackLevel>';
        }
        //Vendor Number On PO
        $org_vendor = $original_data['Order']['Header']['OrderHeader']['Vendor'];
        $Vendor = (isset($org_vendor)) ? '<Vendor>' . $org_vendor . '</Vendor>' : '';
        if (in_array($TradingPartnerId, array('080ALLJARROWFOR'))) {
            $FOBRelatedInstruction = '<FOBRelatedInstruction>
                                                    <FOBPayCode>' . $asn_header['shipment_method'] . '</FOBPayCode>
                                                </FOBRelatedInstruction>
                                                <CarrierInformation>
                                                    <CarrierTransMethodCode>' . $asn_header['trans_code'] . '</CarrierTransMethodCode>
                                                    <CarrierAlphaCode>' . $asn_header['alpha_code'] . '</CarrierAlphaCode>
                                                </CarrierInformation>';
        } else {
            $FOBRelatedInstruction = '<FOBRelatedInstruction>
                                                    <FOBPayCode>' . $asn_header['shipment_method'] . '</FOBPayCode>
                                                 </FOBRelatedInstruction>';
        }
        if (in_array($TradingPartnerId, array('080ALLJARROWFOR'))) {
            $ReferenceQual = 'TN';
            $ReferenceID = $asn_header['reference_no'];
            $ReferenceDesc = 'Amazon Reference Number';
        } else if (isset($original_data['Order']['Header']['References']['ReferenceQual']) && isset($original_data['Order']['Header']['References']['ReferenceID'])) {
            $ReferenceQual = $original_data['Order']['Header']['References']['ReferenceQual'];
            $ReferenceID = $original_data['Order']['Header']['References']['ReferenceID'];
            $ReferenceDesc = (!empty($original_data['Order']['Header']['References']['Description'])) ? $original_data['Order']['Header']['References']['Description'] : 'References From PO';
        } else {
            $ReferenceQual = 'BM';
            $ReferenceID = $asn_header['Order_No'];
            $ReferenceDesc = 'Bill Of Lading Number';
        }
        $pallet_section = ((int) $asn_header['number_of_pallets'] > 0) ? '<QuantityAndWeight><PackingMedium>PLT</PackingMedium><PackingMaterial>94</PackingMaterial><LadingQuantity>' . (int) $asn_header['number_of_pallets'] . '</LadingQuantity></QuantityAndWeight>' : '';
        $volume_data = ((int) $asn_header['volume'] > 0) ? '<Volume>' . $asn_header['volume'] . '</Volume><VolumeUOM>CF</VolumeUOM>' : '';
        $seal_no = (!empty(trim($asn_header['seal_no']))) ? '<SealNumbers><SealNumber>' . trim($asn_header['seal_no']) . '</SealNumber></SealNumbers>' : '';
        $carrier_pro_no = (!empty(trim($asn_header['tracking_number']))) ? '<CarrierProNumber>' . $asn_header['tracking_number'] . '</CarrierProNumber>' : '';
        $xml_data = '<?xml version="1.0" encoding="utf-8"?>
<Shipments xmlns="http://www.spscommerce.com/RSX">
    <Shipment>
        <Header>
            <ShipmentHeader>
                <TradingPartnerId>' . $TradingPartnerId . '</TradingPartnerId>
                <ShipmentIdentification>Sh' . $asn_header['Order_No'] . '</ShipmentIdentification>
                <ShipDate>' . date('Y-m-d') . '</ShipDate>
                <TsetPurposeCode>00</TsetPurposeCode>
                <ShipNoticeDate>' . date('Y-m-d') . '</ShipNoticeDate>
                <ShipNoticeTime>' . date('H:i:s') . '</ShipNoticeTime>
                <ASNStructureCode>0001</ASNStructureCode>
                <BillOfLadingNumber>' . $asn_header['Order_No'] . '</BillOfLadingNumber>
                <Carrier>' . $asn_header['carrier'] . '</Carrier>
                ' . $carrier_pro_no . '
                <CurrentScheduledDeliveryDate>' . date("Y-m-d", strtotime($asn_header['ship_date'])) . '</CurrentScheduledDeliveryDate>
                <CurrentScheduledDeliveryTime>17:00:00</CurrentScheduledDeliveryTime>
            </ShipmentHeader>
            <Dates>
                <DateTimeQualifier>017</DateTimeQualifier>
                <Date>' . date("Y-m-d", strtotime($asn_header['ship_date'])) . '</Date>
                <Time>17:00:00</Time>
            </Dates>
            <References>
                <ReferenceQual>' . $ReferenceQual . '</ReferenceQual>
                <ReferenceID>' . $ReferenceID . '</ReferenceID>
                <Description>' . $ReferenceDesc . '</Description>
            </References>
            ' . $xml_address . '
            ' . $FOBRelatedInstruction . $seal_no . '
            <QuantityAndWeight>
                <PackingMedium>' . $asn_header['pack_medium'] . '</PackingMedium>
                <PackingMaterial>' . $asn_header['pack_material'] . '</PackingMaterial>
                <LadingQuantity>' . $asn_header['landing_qty'] . '</LadingQuantity>
                <Weight>' . round($asn_header['gross_weight'], 2) . '</Weight>
                <WeightUOM>LB</WeightUOM>' . $volume_data . '
            </QuantityAndWeight>' . $pallet_section . '
        </Header>
        <OrderLevel>
            <OrderHeader>
                <PurchaseOrderNumber>' . $po_number . '</PurchaseOrderNumber>' . $Vendor . '
            </OrderHeader>
            ' . $xml_lines . '
        </OrderLevel>
        <Summary>
            <TotalLineItemNumber>' . count($asn_lines) . '</TotalLineItemNumber>
        </Summary>
    </Shipment>
</Shipments>';
        return $xml_data;
    }

    public function buildXmlCarrierPro($carrier_pro) {
        if (strpos($carrier_pro, ':') !== false && strpos($carrier_pro, "\n") !== false) {
            $carrier_array = explode("\n", rtrim($carrier_pro, "\n"));
            $c_pros = array();
            foreach ($carrier_array as $crr_data) {
                $pro_nos = explode(':', $crr_data);
                for ($zz = 0; $zz < $pro_nos[1]; $zz++) {
                    $c_pros[] = $pro_nos[0];
                }
            }
        } else {
            $c_pros = $carrier_pro;
        }
        return $c_pros;
    }

    public function buildCarrierPro($c_pros, $line_no) {
        if (is_array($c_pros) && count($c_pros) > 0) {
            $carrier_pro_no = $c_pros[$line_no];
        } else if (!empty($c_pros)) {
            $carrier_pro_no = $c_pros;
        } else {
            unset($carrier_pro_no);
        }
        return $carrier_pro_no;
    }

    public function buildOrderInfoBySo($so_number) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();

        $select->setIntegrityCheck(false)
                ->from(array("t" => "sg_orders"), array('t.order_id', 't.cust_no', 't.po_no', 't.conf_orderno', 't.order_data', 't.order_file'))
                ->where("t.conf_orderno = '$so_number'");

        // return the results
        $results = $select->query()->fetchAll();
        return $results[0];
    }

#end buildOrderInfo function

    public function buildItemsDesc($orderdata) {
        $final_data = array();
        foreach ($orderdata as $item) {
            $descr = explode("-", $item['Description']);
            if (is_numeric(end($descr)))
                array_pop($descr);
            $descr = implode("-", $descr);
            $final_data[$item['Item']] = $descr;
        }
        return $final_data;
    }

    public function buildASNOreders() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "sg_orders"), array('t.order_id', 't.cust_no', 't.temp_orderno', 't.conf_orderno', 't.po_no', 't.order_date',
                    't.partner_code', 't.order_file', 't.complete', 't.lines', 't.processed_datetime', 'u.name',
                    '(SELECT count(*) from printing_labels where bol=t.conf_orderno) as labels_count'))
                ->joinLeft(array("u" => "users"), "t.user_id=u.user_id", array())
                ->where("t.partner_code IN(?)", array('BIY', '080'))
                ->where("t.conf_orderno != ''")
                ->order("t.processed_datetime DESC");
        // return the results
        $results = $select->query()->fetchAll();
        return $results;
    }

    public function buildOrders($form_data) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $start_date = date('Y-m-d', strtotime($form_data['start_date']));
        $end_date = date('Y-m-d', strtotime($form_data['end_date']));
        $cust_no = trim($form_data['cust_no']);
        $po_no = trim($form_data['po_no']);
        $temp_ord_no = trim($form_data['temp_ord_no']);
        $conf_ord_no = trim($form_data['conf_ord_no']);
        $partner = $form_data['order_type'];
        $conditions = '';
        if ($cust_no != '')
            $conditions .= " AND t.cust_no LIKE '%$cust_no%'";
        if ($po_no != '')
            $conditions .= " AND t.po_no LIKE '%$po_no%'";
        if ($temp_ord_no != '')
            $conditions .= " AND t.temp_orderno LIKE '%$temp_ord_no%'";
        if ($conf_ord_no != '')
            $conditions .= " AND t.conf_orderno LIKE '%$conf_ord_no%'";

        $select->setIntegrityCheck(false)
                ->from(array("t" => "sg_orders"), array('t.*', 'u.name as uploaded_by'))
                ->joinLeft(array("u" => "users"), "t.user_id=u.user_id", array())
                ->where("t.order_date BETWEEN  '$start_date' AND '$end_date' $conditions")
                ->where("t.partner='$partner'")
                ->order(array('t.order_date ASC'));

        // return the results
        $results = $select->query()->fetchAll();
        if ($partner == 'SPS') {
            $sg_action_mapper = new Atlas_Model_SgActionsMapper();
            $final_results = array();
            foreach ($results as $result) {
                $result['actions'] = $sg_action_mapper->buildOrderActions($result['order_id']);
                $final_results[] = $result;
            }
            return $final_results;
        } else
            return $results;
    }

#end buildOrders function

    public function buildOrderTotals($date) {
        $first_of_month = date('Y-m-01', strtotime($date));
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "sg_orders"), array('t.partner',
                    "REPLACE(REPLACE(REPLACE(t.partner, 'SPS', 'SPS Commerce'), 'ATL', 'Atlas'), 'SG', 'Genius Central') as partner_name",
                    "FORMAT(count(t.order_id), 0) AS orders_count",
                    "FORMAT(SUM(t.LINES), 0) AS orders_lines"
                ))
                ->where("DATE(processed_datetime) BETWEEN  '$first_of_month' AND '$date' ")
                ->where("t.temp_orderno != ''")
                ->group('t.partner');
        $results = $select->query()->fetchAll();

        $select1 = $this->getDbTable()->select();
        $select1->setIntegrityCheck(false)
                ->from(array("t" => "sg_orders"), array('t.partner',
                    "REPLACE(REPLACE(REPLACE(t.partner, 'SPS', 'SPS Commerce'), 'ATL', 'Atlas'), 'SG', 'Genius Central') as partner_name",
                    "FORMAT(count(t.order_id), 0) AS orders_count",
                    "FORMAT(SUM(t.LINES), 0) AS orders_lines"
                ))
                ->where("DATE(processed_datetime) = '$date' ")
                ->where("t.temp_orderno != ''")
                ->group('t.partner');
        $results1 = $select1->query()->fetchAll();

        $final_results = array();
        $empty = array('orders_count' => 0, 'orders_lines' => 0);
        $final_results['end_of_day_atl'] = ($results1[array_search('ATL', array_column($results1, 'partner'))]['partner'] == 'ATL') ? $results1[array_search('ATL', array_column($results1, 'partner'))] : $empty;
        $final_results['end_of_day_sg'] = ($results1[array_search('SG', array_column($results1, 'partner'))]['partner'] == 'SG') ? $results1[array_search('SG', array_column($results1, 'partner'))] : $empty;
        $final_results['end_of_day_sps'] = ($results1[array_search('SPS', array_column($results1, 'partner'))]['partner'] == 'SPS') ? $results1[array_search('SPS', array_column($results1, 'partner'))] : $empty;
        $final_results['month_to_date_atl'] = ($results[array_search('ATL', array_column($results, 'partner'))]['partner'] == 'ATL') ? $results[array_search('ATL', array_column($results, 'partner'))] : $empty;
        $final_results['month_to_date_sg'] = ($results[array_search('SG', array_column($results, 'partner'))]['partner'] == 'SG') ? $results[array_search('SG', array_column($results, 'partner'))] : $empty;
        $final_results['month_to_date_sps'] = ($results[array_search('SPS', array_column($results, 'partner'))]['partner'] == 'SPS') ? $results[array_search('SPS', array_column($results, 'partner'))] : $empty;
        return $final_results;
    }

    public function processForm($form_data = NULL) {
        if ($form_data == NULL) {
            throw new Exception("No data given to the model for processing.");
        }

        if ((int) $form_data["order_id"] > 0) {
            $entry = $this->find($form_data["order_id"]);
            $entry->setOptions($form_data);
            $order_id = $this->save($entry);
        } else {
            unset($form_data["order_id"]);
            $entry = new Atlas_Model_SgOrders();
            $entry->setOptions($form_data);
            $order_id = $this->save($entry);
        }

        return $order_id;
    }

#end processForm function
}
