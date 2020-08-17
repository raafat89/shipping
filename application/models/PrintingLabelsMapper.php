<?php

class Atlas_Model_PrintingLabelsMapper {

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
            $this->setDbTable("Atlas_Model_DbTable_PrintingLabels");
        }

        return $this->_dbTable;
    }

#end getDbTable function

    public function save(Atlas_Model_PrintingLabels $entry) {
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
        $entry = new Atlas_Model_PrintingLabels();

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
            $entry = new Atlas_Model_PrintingLabels();
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
        $select->from(array("t" => "printing_labels"), array('t.*'));

        // return the select statement	
        return $select;
    }

#end selectAll function

    public function buildCasiFtpConnection() {
        $settings = Zend_Registry::get("casiftp");
        $params = $settings->ftp->params;
        $host = $params->host;      //FTP Server IP
        $user = $params->username;  //FTP Server Username
        $password = $params->password;  //FTP Server Pass
        $ftpConn = ftp_connect($host);
        $login = ftp_login($ftpConn, $user, $password); //FTP connect and login
        if ((!$ftpConn) || (!$login)) { //Exit if login to FTP server fails
            $ftp_error = 'FTP connection has failed! Attempted to connect to ' . $host . ' for user ' . $user . '.';
            Utility_FlashMessenger::addMessage('<div class="error">' . $ftp_error . '</div>');
            exit();
        } else {
            return $ftpConn;
        }
    }

    public function buildLoadedOrders($order = NULL) {
        $select = $this->getDbTable()->select();
        $select->from(array('p' => 'printing_labels'), array("p.bol", "count(distinct buyer_item) as item", "count(carton_id) as label"))
                ->where("p.bol NOT IN (SELECT distinct conf_orderno FROM sg_orders where partner_code IN('BIY'))");
        if ($order != NULL)
            $select->where('p.bol = ?', $order);
        $select->group(array("p.bol"))
                ->order(array('id DESC'));
        $res = $select->query()->fetchAll();
        $numrow = count($res);
        if ($numrow == 0)
            return 0;
        else
            return $res;
    }

#end buildLoadedOrders function  

    public function processUpload($form_data = NULL) {
        if (isset($form_data['submitbtn'])) {
            if ($form_data == NULL) {
                throw new Exception("No data given to the model for processing.");
            }
            if (isset($form_data['line']) && (int) $form_data['line'] > 0)
                $row_pick = (int) $form_data['line'] + 1;
            else
                $row_pick = 0;
            $buyer_mapper = new Atlas_Model_PrintingLabelsBuyerMapper();
            $func = new Utility_Functions();
            $single_upcs = array();    //Array to carry item codes without duplicates
            $single_upcs1 = array();    //Array to carry the count if each item within the order
            $asn = array();    //Array to carry ASN numbers with no UPCs found
            $duplicate_upcs = array();    //Array to carry duplicate UPC codes within the order
            $single_upcs_verify = array();    //Array to carry unique UPC codes
            $scan_upc = array();    //Array to carry unique UPC codes
            $scan_upc1 = array();    //Array to carry the count if each item within the order
            $file_formats = array("csv");   //File formate allowed
            $msg = '';             //Error Messages
            $warning = '';             //Warning Messages
            $local_server_path = Zend_Registry::get('target_path') . '/uploads/barcodes/';    //Local directory for barcode ZPL files
            $local_server_csv_path = Zend_Registry::get('target_path') . '/uploads/csv/';         //Local directory for CSV files
            $filepath = $local_server_csv_path;     //Directory to store CSV files
            $name = $_FILES['imagefile']['name'];       //Name of uploaded file
            $size = $_FILES['imagefile']['size'];       //Size of uploaded file
            $file_name = explode('.', $name);                 //break the file bname
            $extension = strtolower(end($file_name));        //Get the extension
            $filename = $file_name[0] . time();               //Assign new name to the file
            $imagename = $filename . "." . $extension;        //Combine the file name with the extension
            $tmp = $_FILES['imagefile']['tmp_name'];   //Get file temp name
            $zpl = '/CASI/ZPL';    //FTP directory for ZPL files
            $lpn = '/CASI/LPN';    //FTP directory for CSV files
            $file_error = '';             //Check errors with the uploaded CSV file
            $rec_no = 0;              //Count the items in the file
            $row = 0;              //Count cases within a single item
            $item_no_file = 0;              //Case number within the order
            $scan_row = 0;              //Count rows during scanning process
            $ftp_create_error = '';             //FTP server error
            if (!strlen($name))                                          //Check if file uploaded
                $file_error = 'Please select File!';
            else if (!in_array($extension, $file_formats))               //Check file extension
                $file_error = 'Invalid file format.';
            else if ($size > (2048 * 1024))                              //Check file size
                $file_error = 'Your file size is bigger than 2MB.';
            else if (!move_uploaded_file($tmp, $filepath . $imagename))  //Check if the file is uploaded
                $file_error = 'Could not move the file.';

            if ($file_error == '') {
                if (APPLICATION_ENV == 'production')
                    $ftpConn = $this->buildCasiFtpConnection();
                //Scan the file for errors and duplicates before uploading
                if (($handle_scan = fopen($filepath . $imagename, "r")) !== FALSE) {
                    while (($data_scan = fgetcsv($handle_scan, 1000, ",")) !== FALSE) {
                        $scan_row++;
                        if ($scan_row > 1) {
                            //Get UPC number for each ASN
                            if (!is_numeric($data_scan[19]))
                                $print_upc_scan = $buyer_mapper->buildUpc($data_scan[19]);

                            //Get the count of the cases for each item within the order
                            if (!in_array($print_upc_scan, $scan_upc)) {  //If no duplicate assign the first value
                                $scan_upc[] = $print_upc_scan;
                                $scan_upc1[$print_upc_scan] = $data_scan[26];
                            } else {   //If there's duplicate add the current value to the previous one
                                $start_value = $scan_upc1[$print_upc_scan];
                                $scan_upc1[$print_upc_scan] = $scan_upc1[$print_upc_scan] + $data_scan[26];
                            }

                            //Get ASN's without UPC codes
                            if ($print_upc_scan == '' || !is_numeric($print_upc_scan))
                                $asn[] = $data_scan[19];

                            //Get duplicate UPCs
                            if (!in_array($print_upc_scan, $single_upcs_verify))
                                $single_upcs_verify[] = $print_upc_scan;
                            else {
                                if (!in_array($print_upc_scan, $duplicate_upcs))
                                    $duplicate_upcs[] = $print_upc_scan;
                            }
                        }
                    }
                }
                fclose($handle_scan);


                if (count($asn) > 0) {  //Assign missing UPC to the error message
                    $msg = "Error: Missing UPC for some ASN Codes: <br>";
                    foreach ($asn as $code) {
                        $msg .= $code . '<br>';
                    }
                } else if (count($duplicate_upcs) > 0) { //Assign duplicates to the warning messages
                    $warning = "Warning: Duplicate UPCs found: <br>";
                    foreach ($duplicate_upcs as $code) {
                        $warning .= $code . '<br>';
                    }
                }

                if ($msg != '') { //Exit if error message is not empty
                    Utility_FlashMessenger::addMessage('<div class="error">' . $msg . '</div>');
                    exit();
                } else {
                    if (($handle = fopen($filepath . $imagename, "r")) !== FALSE) {
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            $rec_no++;
                            if ($rec_no - 1 <= 1000) {
                                if ($rec_no == 2) {
                                    $so_number = strtoupper($data[3]);  //Order number upper case
                                    if ($this->isOrderExist($so_number) || trim($so_number) == '') {  //Exit if the order already exists
                                        Utility_FlashMessenger::addMessage('<div class="error">Error: You\'re trying to upload a duplicate or incorrect order number.</div>');
                                        exit();
                                    }
                                    $local_zpl_path = $local_server_path . $so_number . '_ZPL'; //ZPL Path on local server
                                    $zpl_path = '/' . $so_number . '_ZPL';                //ZPL folder name
                                    $barcode_zip_text = '(420)' . $data[11];                  //Zip code text without space
                                    $barcode_zip_text1 = '(420) ' . $data[11];                 //Zip code text with space
                                    $dir_zpl = "/CASI/ZPL/" . $so_number . '_ZPL';       //ZPL directory on the FTP server
                                    $zip_code = str_replace('(', '', str_replace(')', '', $barcode_zip_text));
                                    //Create ZPL folder on local server
                                    if (!is_dir($local_zpl_path))
                                        mkdir($local_zpl_path, 0777, true);

                                    //Create required folders on FTP server (ZPL-LPN-Order_ZPL)
                                    if (APPLICATION_ENV == 'production') {
                                        if (!$this->ftpIsDir($ftpConn, $zpl)) {
                                            if (!ftp_mkdir($ftpConn, $zpl))
                                                $ftp_create_error = "Error while creating $zpl";
                                        }

                                        if (!$this->ftpIsDir($ftpConn, $lpn)) {
                                            if (!ftp_mkdir($ftpConn, $lpn))
                                                $ftp_create_error = "Error while creating $lpn";
                                        }

                                        if (!$this->ftpIsDir($ftpConn, $dir_zpl)) {
                                            if (!ftp_mkdir($ftpConn, $dir_zpl))
                                                $ftp_create_error = "Error while creating $dir_zpl";
                                        }

                                        if ($ftp_create_error != '') { //Exit if there's an error creating FTP folders
                                            Utility_FlashMessenger::addMessage('<div class="error">' . $ftp_create_error . '</div>');
                                            exit();
                                        }
                                    }
                                    //Create CSV file to populate order data after parsing
                                    $file = fopen($local_server_csv_path . $so_number . '_ORDERS.csv', 'w');
                                    //Assign File headers
                                    fputcsv($file, array('asn', 'asn_date', 'ship_date', 'bol', 'carrier_tracking', 'carrier', 'ship_to_name', 'ship_to_location', 'ship_to_address', 'ship_to_city', 'ship_to_state', 'ship_to_zip', 'ship_from_name', 'fob_terms', 'landing_qty', 'po', 'location', 'carton_id', 'line', 'buyer_item', 'vdr_item', 'upc', 'gtin', 'descr', 'color', 'size', 'qty_ship', 'item_no', 'ctn_qty', 'uom', 'lot_number', 'expiration_date', 'barcode_zip', 'barcode_order', 'barcode_upc', 'barcode_zip_text', 'barcode_upc_text', 'printed'));
                                }

                                if ($rec_no > 1) {
                                    //Convert ASN to UPC code
                                    if (!is_numeric($data[19]))
                                        $print_upc = $buyer_mapper->buildUpc($data[19]);
                                    //Check if this UPC is a duplicate to set the start value for the ZPL files count
                                    if (!in_array($print_upc, $single_upcs)) {
                                        $single_upcs[] = $print_upc;
                                        $single_upcs1[$print_upc] = $data[26];
                                        $dup = 0;
                                    } else {
                                        $start_value = $single_upcs1[$print_upc];
                                        $single_upcs1[$print_upc] = $single_upcs1[$print_upc] + $data[26];
                                        $dup = 1;
                                    }

                                    if ($data[26] > 1) { //Item with more than one case
                                        $qty = 0;
                                        $upc = explode('-', $data[17]);
                                        $start_upc = trim($upc[0]);
                                        for ($i = 1; $i <= $data[26]; $i++) {
                                            $qty++;
                                            $row++;
                                            $item_no_file++;
                                            $UPC = $func->current_barcode($start_upc); //Parse SSCC-18
                                            $start_upc = $func->next_barcode($start_upc);    //Parse SSCC-18
                                            $barcode_upc_text = '(00)' . substr($UPC, 2);             //Barcode GS1 text
                                            //Get UPC for each ASN
                                            if (!is_numeric($data[19]))
                                                $print_upc = $buyer_mapper->buildUpc($data[19]);
                                            else
                                                $print_upc = $data[19];
                                            if ($print_upc == '')
                                                $print_upc = $data[19];

                                            $qty_ship_value = $scan_upc1[$print_upc];
                                            $barcode = str_replace('(', '', str_replace(')', '', $barcode_upc_text));

                                            if ($row_pick == 0 || ($row_pick != 0 && $rec_no == $row_pick)) {
                                                //Insert Record into Database
                                                $data_input = array(
                                                    "bol" => $data[3],
                                                    "po" => $data[15],
                                                    "ship_to_name" => $data[6],
                                                    "ship_to_address" => $data[8],
                                                    "ship_to_city" => $data[9],
                                                    "ship_to_state" => $data[10],
                                                    "ship_to_zip" => $data[11],
                                                    "landing_qty" => (int) $data[14],
                                                    "line" => (int) $data[18],
                                                    "descr" => $data[23],
                                                    "buyer_item" => $data[19],
                                                    "vdr_item" => $data[20],
                                                    "upc" => $print_upc,
                                                    "lot_number" => $data[29],
                                                    "qty_ship" => (int) $qty_ship_value,
                                                    "item_no" => (int) $item_no_file,
                                                    "ctn_qty" => (int) $data[27],
                                                    "uom" => $data[28],
                                                    "expiration_date" => date('Y-m-d', strtotime($data[30])),
                                                    "carton_id" => $UPC,
                                                    "barcode_upc_text" => $barcode_upc_text,
                                                    "barcode_zip_text" => $barcode_zip_text
                                                );
                                                $save = $this->save(new Atlas_Model_PrintingLabels($data_input));

                                                //Create ZPL Commands 
                                                $cmds = $this->zebraLabel($data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $zip_code, $barcode_zip_text1, $so_number, $data[15], $data[19], $item_no_file, $data[14], $data[29], $data[30], $barcode, $barcode_upc_text);

                                                //Create row in the Excel sheet
                                                fputcsv($file, array($data[0], $data[1], $data[2], $so_number, $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12],
                                                    $data[13], $data[14], $data[15], $data[16], $UPC, $data[18], $data[19], $data[20], $print_upc, $data[22], $data[23], $data[24], $data[25],
                                                    $qty_ship_value, $item_no_file, $data[27], $data[28], $data[29], $data[30], '0', '0', '0', $barcode_zip_text, $barcode_upc_text, 0));

                                                //Assign file name for ZPL file (file number depends on if it's duplicate or not)
                                                if ($dup == 1 && $row_pick == 0) {
                                                    $start_value++;
                                                    $zpl_path = $local_zpl_path . '/' . $print_upc . '_' . $start_value . '.zpl';
                                                    $file_no = $start_value;
                                                } else {
                                                    $zpl_path = $local_zpl_path . '/' . $print_upc . '_' . $qty . '.zpl';
                                                    $file_no = $qty;
                                                }

                                                //Save ZPL file on local server
                                                file_put_contents($zpl_path, $cmds);

                                                //Move the saved file the FTP server
                                                $remote_file = $dir_zpl . '/' . $print_upc . '_' . $file_no . '.zpl';
                                                $local_file = $zpl_path;
                                                if (!ftp_put($ftpConn, $remote_file, $local_file, FTP_ASCII) && APPLICATION_ENV == 'production') { //Exit if there's a problem moving the file
                                                    Utility_FlashMessenger::addMessage('<div class="error">There was a problem while uploading  ' . $local_file . '</div>');
                                                    exit();
                                                }
                                            }
                                        }
                                    } else if ($data[26] == 1) { //If the item count is one case only  
                                        $row++;
                                        $item_no_file++;
                                        $UPC = $data[17];
                                        $barcode_upc_text = '(00)' . substr($UPC, 2);
                                        if (!is_numeric($data[19]))
                                            $print_upc = $buyer_mapper->buildUpc($data[19]);
                                        else
                                            $print_upc = $data[19];
                                        if ($print_upc == '')
                                            $print_upc = $data[19];

                                        $qty_ship_value = $scan_upc1[$print_upc];
                                        $barcode = str_replace('(', '', str_replace(')', '', $barcode_upc_text));

                                        if ($row_pick == 0 || ($row_pick != 0 && $rec_no == $row_pick)) {
                                            //Insert Record into Database
                                            $data_input = array(
                                                "bol" => $data[3],
                                                "po" => $data[15],
                                                "ship_to_name" => $data[6],
                                                "ship_to_address" => $data[8],
                                                "ship_to_city" => $data[9],
                                                "ship_to_state" => $data[10],
                                                "ship_to_zip" => $data[11],
                                                "landing_qty" => (int) $data[14],
                                                "line" => (int) $data[18],
                                                "descr" => $data[23],
                                                "buyer_item" => $data[19],
                                                "vdr_item" => $data[20],
                                                "upc" => $print_upc,
                                                "lot_number" => $data[29],
                                                "qty_ship" => (int) $qty_ship_value,
                                                "item_no" => (int) $item_no_file,
                                                "ctn_qty" => (int) $data[27],
                                                "uom" => $data[28],
                                                "expiration_date" => date('Y-m-d', strtotime($data[30])),
                                                "carton_id" => $UPC,
                                                "barcode_upc_text" => $barcode_upc_text,
                                                "barcode_zip_text" => $barcode_zip_text
                                            );
                                            $save = $this->save(new Atlas_Model_PrintingLabels($data_input));
                                            //Create ZPL Commands
                                            $cmds = $this->zebraLabel($data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $zip_code, $barcode_zip_text1, $so_number, $data[15], $data[19], $item_no_file, $data[14], $data[29], $data[30], $barcode, $barcode_upc_text);

                                            //Create row in the Excel sheet
                                            fputcsv($file, array($data[0], $data[1], $data[2], $so_number, $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12],
                                                $data[13], $data[14], $data[15], $data[16], $UPC, $data[18], $data[19], $data[20], $print_upc, $data[22], $data[23], $data[24], $data[25],
                                                $qty_ship_value, $item_no_file, $data[27], $data[28], $data[29], $data[30], '0', '0', '0', $barcode_zip_text, $barcode_upc_text, 0));

                                            //Assign file name for ZPL file (file number depends on if it's duplicate or not)
                                            if ($dup == 1 && $row_pick == 0) {
                                                $start_value++;
                                                $qty_single = $start_value;
                                                $zpl_path = $local_zpl_path . '/' . $print_upc . '_' . $qty_single . '.zpl';
                                            } else {
                                                $qty_single = 1;
                                                $zpl_path = $local_zpl_path . '/' . $print_upc . '_' . $qty_single . '.zpl';
                                            }

                                            //Save ZPL file on local server
                                            file_put_contents($zpl_path, $cmds);

                                            //Move the saved file the FTP server
                                            $remote_file = $dir_zpl . '/' . $print_upc . '_' . $qty_single . '.zpl';
                                            $local_file = $zpl_path;
                                            if (!ftp_put($ftpConn, $remote_file, $local_file, FTP_ASCII) && APPLICATION_ENV == 'production') { //Exit if there's a problem moving the file
                                                Utility_FlashMessenger::addMessage('<div class="error">There was a problem while uploading  ' . $local_file . '</div>');
                                                exit();
                                            }
                                        }
                                    }
                                    //Display warnings if there's any with the success MSG
                                    if ($warning != '')
                                        $warning_msg = '<div class="warning">' . $warning . '</div>';
                                    Utility_FlashMessenger::addMessage($warning_msg . '<div class="success">This order has been successfully uploaded.</div>');
                                }
                            }
                        }
                        fclose($file);      //Close parsed CSV file
                        fclose($handle);    //Close order file
                        //Move the Parsed CSV to FTP server
                        $remote_file = '/CASI/LPN/' . $so_number . '_ORDERS.csv';
                        $local_file = $local_server_csv_path . $so_number . '_ORDERS.csv';
                        if (!ftp_put($ftpConn, $remote_file, $local_file, FTP_ASCII) && APPLICATION_ENV == 'production') { //Exit if you couldn't move the file
                            Utility_FlashMessenger::addMessage('<div class="error">There was a problem while uploading ' . $local_file . '</div>');
                            exit();
                        }
                        if (APPLICATION_ENV == 'production') {
                            unlink($local_file);                                //Delete Local CSV File
                            $this->recursiveRemoveDirectory($local_zpl_path);   //Delete ZPL Directory
                        }
                        unlink(Zend_Registry::get('target_path') . '/uploads/csv/' . $imagename);   //Delete Original CSV File
                    }
                }
            } else {
                Utility_FlashMessenger::addMessage('<div class="error">' . $file_error . '</div>'); //Display file error message
            }
            exit();
        }
    }

#end processUpload function
    //Create order count data
    public function createCsvFile($data = NULL, $so_number = '') {
        $local_server_csv_path = 'uploads/barcodes/ordercount/';                 //File path for cases count
        $pei_mapper = new Atlas_Model_ProductExtraInfoMapper();       //Product info mapper
        $file = fopen($local_server_csv_path . $so_number . '_ORDERCOUNT.csv', 'w'); //File to save order count data
        $items = 0;                                  //Items count within an order
        $qty = 0;                                  //Quantity of each item within the order
        fputcsv($file, array('OrderNo', 'upc', 'qty'));    //Create File Headers
        foreach ($data as $product) {
            $case_qty = $pei_mapper->buildCaseQty($product['UPC']);     //Generate case qty
            $cases = ((int) $product['Qty']) / ((int) $case_qty);        //Cases for an item within the order
            fputcsv($file, array($so_number, $product['UPC'], $cases));        //Add item info to the file Order no - UPC - no of cases
            $items++;                                                       //Increase Item number
            $qty = $qty + $cases;                                    //Increase case number
        }

        $mapper_key = new Atlas_Model_PrintingLabelsCountMapper();
        $entry_key = new Atlas_Model_PrintingLabelsCount();
        $entry_key->setOrderno($so_number)->setItems($items)->setQty($qty);
        $mapper_key->save($entry_key);          //Save totals
        fclose($file);

        //Set FTP Connection
        $ftpConn = $this->buildCasiFtpConnection();

        $remote_file = '/CASI/ORDERCOUNT/' . $so_number . '_ORDERS.csv';       //Remote file name path
        $local_file = $local_server_csv_path . $so_number . '_ORDERCOUNT.csv'; //Local file name path

        if (!ftp_put($ftpConn, $remote_file, $local_file, FTP_ASCII))           //Move local file to the FTP server
            echo "There was a problem while uploading $local_file\n";
        unlink($local_file);        //Delete file after uploading
    }

#End createCsvFile 
    //Check if FTP directory is already created
    public function ftpIsDir($ftp, $dir) {
        $pushd = ftp_pwd($ftp);
        if ($pushd !== false && @ftp_chdir($ftp, $dir)) {
            ftp_chdir($ftp, $pushd);
            return true;
        }
        return false;
    }

#end ftpIsDir function
    //Check If order already exists
    public function isOrderExist($orderno = '') {
        $select = $this->getDbTable()->select();
        $select->from(array('p' => 'printing_labels'), array("count(*) as count"))
                ->where('p.bol = ?', $orderno);
        $res = $select->query()->fetchAll();
        if ($res[0]['count'] == 0)
            return false;
        else
            return true;
    }

#end isOrderExist function
    //Remove Files within a directory then delete the directory itself
    public function recursiveRemoveDirectory($directory) {
        foreach (glob("{$directory}/*") as $file) {
            if (is_dir($file)) {
                recursiveRemoveDirectory($file); //Re-call function with different path
            } else {
                unlink($file);
            }
        }
        rmdir($directory); //Remove directory after removing included files
    }

#end recursiveRemoveDirectory function
    //Remove uploaded orders from CASI
    public function deleteOrder($order_no) {
        //Delete database records
        $this->getDbTable()->delete("bol='$order_no'");
        //Set FTP Connection
        if (APPLICATION_ENV == 'production') {
            $ftpConn = $this->buildCasiFtpConnection();
            $zpl = '/CASI/ZPL';    //FTP ZPL folder directory
            $lpn = '/CASI/LPN';    //FTP CSV files directory
            $file_csv = $lpn . '/' . $order_no . '_ORDERS.csv';       //CSV file path
            $file_csv_dat = $lpn . '/' . $order_no . '_ORDERS.csv.dat';   //CSV imported file path
            $zpl_path = $zpl . '/' . $order_no . '_ZPL';              //ZPL folder path
            $zpl_dat_path = $zpl . '/' . $order_no . '_ZPL.dat';          //ZPL imported folder path

            $files_csv = ftp_nlist($ftpConn, $lpn);  //Get a list of CSV files
            //Check the existing files to delete
            if (in_array($file_csv, $files_csv))
                ftp_delete($ftpConn, $file_csv);
            else if (in_array($file_csv_dat, $files_csv))
                ftp_delete($ftpConn, $file_csv_dat);

            //Check ZPL directory to delete
            if ($this->ftpIsDir($ftpConn, $zpl_path)) {
                $filelist = ftp_nlist($ftpConn, $zpl_path);
                foreach ($filelist as $file)
                    ftp_delete($ftpConn, $file);
                if (!ftp_rmdir($ftpConn, $zpl_path))
                    echo "There was a problem while deleting $zpl_path \n";
            }else if ($this->ftpIsDir($ftpConn, $zpl_dat_path)) {
                $filelist = ftp_nlist($ftpConn, $zpl_dat_path);
                foreach ($filelist as $file)
                    ftp_delete($ftpConn, $file);
                if (!ftp_rmdir($ftpConn, $zpl_dat_path))
                    echo "There was a problem while deleting $zpl_dat_path \n";
            }
            ftp_close($ftpConn);        //Close FTP connection
        }else if (APPLICATION_ENV == 'development') {
            unlink(Zend_Registry::get('target_path') . '/uploads/csv/' . $order_no . '_ORDERS.csv');                          //Delete Local CSV File
            $this->recursiveRemoveDirectory(Zend_Registry::get('target_path') . '/uploads/barcodes/' . $order_no . '_ZPL');   //Delete ZPL Directory
        }
        return $order_no;           //Return Order number
    }

#Delete Order from a database then delete the FTP directories
    //Create labels for CASI
    public function caseLabels($so_number, $data) {
        $printing_labels = new Atlas_Model_PrintingLabels();
        $ftpConn = $this->buildCasiFtpConnection();

        $dir_zpl = "/CASI/ZPL/" . $so_number . '_ZPL';     //FTP ZPL directory path
        //Create directory if it doesn't exist on the FTP server
        if (!$this->ftpIsDir($ftpConn, $dir_zpl))
            if (!ftp_mkdir($ftpConn, $dir_zpl))
                echo "Error while creating $dir_zpl";

        $upc_qty = array();        //Case quantity
        $upcs = array();        //Items UPCs
        $local_server_path = 'uploads/barcodes/' . $so_number . '_ZPL/'; //Local ZPL directory
        //Create local directory if it doesn't exist
        if (!is_dir($local_server_path))
            mkdir($local_server_path, 0777, true);

        $local_server_csv_path = 'uploads/csv/';     //Local CSV file path
        $file = fopen($local_server_csv_path . $so_number . '_ORDERS.csv', 'w');     //Open CSV file to write data
        fputcsv($file, array('asn', 'asn_date', 'ship_date', 'bol', 'carrier_tracking', 'carrier', 'ship_to_name', 'ship_to_location', 'ship_to_address', 'ship_to_city', 'ship_to_state', 'ship_to_zip', 'ship_from_name', 'fob_terms', 'landing_qty', 'po', 'location', 'carton_id', 'line', 'buyer_item', 'vdr_item', 'upc', 'gtin', 'descr', 'color', 'size', 'qty_ship', 'item_no', 'ctn_qty', 'uom', 'lot_number', 'expiration_date', 'barcode_zip', 'barcode_order', 'barcode_upc', 'barcode_zip_text', 'barcode_upc_text', 'printed')); //Create header
        $total = count($data);       //Get total count of cases
        $curr_value = mt_rand(1000, 8888); //Start value for Carton's IDs
        //Loop on order data using array indexes
        for ($z = 0; $z < $total; ++$z) {
            $file_data = $data[$z];
            $upc = $file_data["UPC"];

            //Get Case quantity to number the ZPL files
            if (!in_array($upc, $upcs)) {
                $case_upc_no = 1;
                $upcs[] = $upc;
                $upc_qty[$upc] = $case_upc_no;
            } else {
                $case_upc_no = (int) $upc_qty[$upc] + 1;
                $upc_qty[$upc] = $case_upc_no;
            }
            $curr_value = $curr_value + 15;                      //Increment for start value
            $barcode = '000078001100200' . $curr_value . '0';   //Carton ID
            //Create ZPL label
            $cmds = "^XA
^FO690,30^AQR,50,50^FDJarrow Formulas ^FS
^FO690,370^AQR,50,50^FH_^FD_A9^FS
^FO420,30^AQR,45,40^FDPurchase order(s) # {$file_data[PO_Num]}^FS
^FO260,30^AQR,45,25^FDJarrow Formulas   , Inc.^FS
^FO260,197^AQR,45,25^FH_^FD_A9^FS
^FO205,30^AQR,45,25^FD1824 S Robertson Blvd.^FS
^FO160,30^AQR,45,25^FDLos Angeles, CA 90035^FS
^FO690,470^AQR,45,25^FDTo: {$file_data[custname]}^FS
^FO640,470^AQR,45,25^FD{$file_data[shipaddr1]}^FS
^FO590,470^AQR,45,25^FD{$file_data[shipcity]}, {$file_data[shipstate]} {$file_data[shipzip]}^FS
^FO540,470^AQR,45,25^FD{$file_data[shipcntry]}^FS
^FO300,500^AQR,45,40^FD{$file_data[Prod_Code]}^FS
^FO250,500^AQR,45,25^FDTotal Qty: {$file_data[Qty]} This Box: {$file_data[box_qty]}^FS
^FO200,500^AQR,45,25^FDLot: {$file_data[Lot_Number]}^FS
^FO150,500^AQR,45,25^FDUPC: {$file_data[UPC]}^FS
^FO70,600^AQR,45,25^FDCase #: {$z} of {$total}^FS
^FO90,910^BY4^BCN,240,N,N,N,A^FD$barcode^FS
^XZ";

            $ship_qty = $file_data[Qty] / $file_data[box_qty];    //Quantity of shipped item
            //Insert data into CSV file
            fputcsv($file, array(' ', ' ', ' ', $so_number, ' ', ' ',
                str_replace(',', ' ', $file_data[custname]), ' ',
                str_replace(',', ' ', $file_data[shipaddr1]),
                str_replace(',', ' ', $file_data[shipcity]),
                str_replace(',', ' ', $file_data[shipstate]),
                str_replace(',', ' ', $file_data[shipzip]),
                'Jarrow Formulas', 'Collect', $total, $file_data[PO_Num], ' ', $barcode,
                ' ', $file_data[UPC], 0, $file_data[UPC], ' ', ' ', ' ', ' ', $ship_qty, $z + 1, 1, 'Case', $file_data[Lot_Number], date('m/d/Y', strtotime($file_data['Expires'])), ' ', ' ', ' ', ' ', ' ', ' '));

            //Insert Database record
            $printing_labels->setBol($so_number)
                    ->setShip_to_name($file_data[custname])
                    ->setShip_to_address($file_data[shipaddr1])
                    ->setShip_to_city($file_data[shipcity])
                    ->setShip_to_state($file_data[shipstate])
                    ->setShip_to_zip($file_data[shipzip])
                    ->setLanding_qty((int) $total)
                    ->setCarton_id($barcode)
                    ->setBuyer_item($file_data['UPC'])
                    ->setItem_no((int) ($z + 1));
            $this->save($printing_labels);

            $local_file_path = $local_server_path . $file_data["UPC"] . '_' . $case_upc_no . '.zpl';  //ZPL local file path
            file_put_contents($local_file_path, $cmds);      //Save ZPL file

            $remote_file = $dir_zpl . '/' . $file_data["UPC"] . '_' . $case_upc_no . '.zpl';        //ZPL remote file name path
            if (!ftp_put($ftpConn, $remote_file, $local_file_path, FTP_ASCII))
                echo "There was a problem while uploading $local_file_path"; //Move ZPL file to FTP directory
        }
        fclose($file);

        $remote_file_csv = '/CASI/LPN/' . $so_number . '_ORDERS.csv';              //FTP CSV file path
        $local_file_csv = $local_server_csv_path . $so_number . '_ORDERS.csv';    //Local CSV file path
        //Move CSV file to FTP server
        if (!ftp_put($ftpConn, $remote_file_csv, $local_file_csv, FTP_ASCII))
            echo "There was a problem while uploading $local_file_csv";
        unlink($local_file_csv);                                //Delete Local CSV File
        $this->recursiveRemoveDirectory($local_server_path);    //Delete ZPL Directory
        return $so_number;
    }

    //Create Amazon label for CASI
    public function zebraLabel($name_to, $store_id, $address, $city, $state, $zip, $zip_barcode, $zip_barcode_text, $order_no, $po_number, $buyer_item, $item_no, $landing_qty, $lot_no, $exp_date, $carton_id, $carton_id_text, $item_key = NULL) {
        $item_line = '';
        if ($item_key != NULL)
            $item_line = "^FO330,745^AQN,45,25^FDVendor #: $item_key^FS";
        $cmd_label = "^XA
^FO20,230^GB750,4,4^FS
^FO310,30^GB4,200,4^FS
^FO30,40^AQN,40,40^FDSHIP FROM:^FS
^FO30,70^AQN,25,25^FDJarrow Formulas^FS
^FO30,100^AQN,25,25^FD10715 Shoemaker Ave,^FS
^FO30,135^AQN,25,25^FDSanta Fe Springs,^FS
^FO30,170^AQN,25,25^FDCA 90670^FS
^FO350,40^AQN,40,40^FDSHIP TO:^FS
^FO350,70^AQN,25,25^FD$name_to^FS
^FO350,100^AQN,25,25^FDStore #: $store_id^FS
^FO350,130^AQN,25,25^FD$address,^FS
^FO350,160^AQN,25,25^FD$city,^FS
^FO350,195^AQN,25,25^FD$state $zip^FS
^FO490,230^GB2,205,4^FS
^FO20,435^GB750,2,4^FS
^FO25,250^BY3^BCN,122,N,N,A^FD>;>8$zip_barcode^FS    
^FO140,385^AQN,25,25^FD$zip_barcode_text^FS
^FO500,290^AQN,45,25^FDPRO #: ^FS
^FO500,350^AQN,45,25^FDB/L #: $order_no^FS
^FO20,630^GB750,2,4^FS
^FO30,450^AQN,40,40^FDPURCHASE ORDER:  $po_number^FS
^FO250,495^BY3^BCN,120,N,N,N,A^FD$po_number^FS  
^FO20,805^GB750,2,4^FS
^FO30,635^AQN,45,25^FDQUANTITY: 1^FS
^FO30,690^AQN,45,25^FDBUYER #: $buyer_item^FS
^FO30,745^AQN,45,25^FDCARTON #: $item_no of $landing_qty^FS
^FO330,635^AQN,45,25^FDLot Number: $lot_no^FS
^FO330,690^AQN,45,25^FDExp. Date: $exp_date^FS" . $item_line . "
^FO300,815^AQN,40,40^FDSSCC-18^FS
^FO70,855^BY4^BCN,280,N,N,A^FD>;>8$carton_id^FS  
^FO140,1140^AQN,45,40^FD$carton_id_text^FS
^XZ";
        return $cmd_label;
    }

    public function processiherbasn($form_data = NULL) {
        if ($_POST['submitbtn'] == "Submit") {
            if ($form_data == NULL) {
                throw new Exception("No data given to the model for processing.");
            }
            $buyer_mapper = new Atlas_Model_PrintingLabelsBuyerMapper();
            $func = new Utility_Functions();
            $single_upcs = array();    //Array to carry item codes without duplicates
            $single_upcs1 = array();    //Array to carry the count if each item within the order
            $asn = array();    //Array to carry ASN numbers with no UPCs found
            $duplicate_upcs = array();    //Array to carry duplicate UPC codes within the order
            $single_upcs_verify = array();    //Array to carry unique UPC codes
            $scan_upc = array();    //Array to carry unique UPC codes
            $scan_upc1 = array();    //Array to carry the count if each item within the order
            $file_formats = array("csv");   //File formate allowed
            $msg = '';             //Error Messages
            $warning = '';             //Warning Messages
            $local_server_path = 'uploads/barcodes/';    //Local directory for barcode ZPL files
            $local_server_csv_path = 'uploads/csv/';         //Local directory for CSV files
            $filepath = $local_server_csv_path;     //Directory to store CSV files
            $name = $_FILES['imagefile']['name'];       //Name of uploaded file
            $size = $_FILES['imagefile']['size'];       //Size of uploaded file
            $file_name = explode('.', $name);                 //break the file bname
            $extension = strtolower(end($file_name));        //Get the extension
            $filename = $file_name[0] . time();               //Assign new name to the file
            $imagename = $filename . "." . $extension;        //Combine the file name with the extension
            $tmp = $_FILES['imagefile']['tmp_name'];   //Get file temp name
            $zpl = '/CASI/ZPL';    //FTP directory for ZPL files
            $lpn = '/CASI/LPN';    //FTP directory for CSV files
            $file_error = '';             //Check errors with the uploaded CSV file
            $rec_no = 0;              //Count the items in the file
            $row = 0;              //Count cases within a single item
            $item_no_file = 0;              //Case number within the order
            $scan_row = 0;              //Count rows during scanning process
            $ftp_create_error = '';             //FTP server error
            if (!strlen($name))                                          //Check if file uploaded
                $file_error = 'Please select File!';
            else if (!in_array($extension, $file_formats))               //Check file extension
                $file_error = 'Invalid file format.';
            else if ($size > (2048 * 1024))                              //Check file size
                $file_error = 'Your file size is bigger than 2MB.';
            else if (!move_uploaded_file($tmp, $filepath . $imagename))  //Check if the file is uploaded
                $file_error = 'Could not move the file.';

            if ($file_error == '') {
                $ftpConn = $this->buildCasiFtpConnection();

                //Scan the file for errors and duplicates before uploading
                if (($handle_scan = fopen($filepath . $imagename, "r")) !== FALSE) {
                    while (($data_scan = fgetcsv($handle_scan, 1000, ",")) !== FALSE) {
                        $scan_row++;
                        if ($scan_row > 1) {
                            //Get UPC number 
                            $print_upc_scan = $data_scan[21];

                            if (!is_numeric($data_scan[27])) {
                                $pei_mapper = New Atlas_Model_ProductExtraInfoMapper();
                                $per_case_qty = $pei_mapper->buildCaseQty($print_upc_scan);
                            } else {
                                $per_case_qty = $data_scan[27];
                            }
                            $data_scan[26] = $data_scan[26] / $per_case_qty;

                            //Get the count of the cases for each item within the order
                            if (!in_array($print_upc_scan, $scan_upc)) {  //If no duplicate assign the first value
                                $scan_upc[] = $print_upc_scan;
                                $scan_upc1[$print_upc_scan] = $data_scan[26];
                            } else {   //If there's duplicate add the current value to the previous one
                                $start_value = $scan_upc1[$print_upc_scan];
                                $scan_upc1[$print_upc_scan] = $scan_upc1[$print_upc_scan] + $data_scan[26];
                            }

                            //Get ASN's without UPC codes
                            if ($print_upc_scan == '' || !is_numeric($print_upc_scan)) {
                                $asn[] = $data_scan[19];
                            }

                            //Get duplicate UPCs
                            if (!in_array($print_upc_scan, $single_upcs_verify))
                                $single_upcs_verify[] = $print_upc_scan;
                            else {
                                if (!in_array($print_upc_scan, $duplicate_upcs))
                                    $duplicate_upcs[] = $print_upc_scan;
                            }
                        }
                    }
                }
                fclose($handle_scan);

                if (count($asn) > 0) {  //Assign missing UPC to the error message
                    $msg = "Error: Missing UPC for some ASN Codes: <br>";
                    foreach ($asn as $code) {
                        $msg .= $code . '<br>';
                    }
                } else if (count($duplicate_upcs) > 0) { //Assign duplicates to the warning messages
                    $warning = "Warning: Duplicate UPCs found: <br>";
                    foreach ($duplicate_upcs as $code) {
                        $warning .= $code . '<br>';
                    }
                }

                if ($msg != '') { //Exit if error message is not empty
                    Utility_FlashMessenger::addMessage('<div class="error">' . $msg . '</div>');
                    exit();
                } else {
                    if (($handle = fopen($filepath . $imagename, "r")) !== FALSE) {
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            $rec_no++;
                            if ($rec_no - 1 <= 1000) {
                                if ($rec_no == 2) {
                                    $so_number = strtoupper($data[3]);  //Order number upper case
                                    if ($this->isOrderExist($so_number) || trim($so_number) == '') {  //Exit if the order already exists
                                        Utility_FlashMessenger::addMessage('<div class="error">Error: You\'re trying to upload a duplicate or incorrect order number.</div>');
                                        exit();
                                    }
                                    $local_zpl_path = $local_server_path . $so_number . '_ZPL'; //ZPL Path on local server
                                    $zpl_path = '/' . $so_number . '_ZPL';                //ZPL folder name
                                    $barcode_zip_text = '(420)' . $data[11];                  //Zip code text without space
                                    $barcode_zip_text1 = '(420) ' . $data[11];                 //Zip code text with space
                                    $dir_zpl = "/CASI/ZPL/" . $so_number . '_ZPL';       //ZPL directory on the FTP server
                                    //Create ZPL folder on local server
                                    if (!is_dir($local_zpl_path))
                                        mkdir($local_zpl_path, 0777, true);

                                    //Create required folders on FTP server (ZPL-LPN-Order_ZPL)
                                    if (!$this->ftpIsDir($ftpConn, $zpl)) {
                                        if (!ftp_mkdir($ftpConn, $zpl))
                                            $ftp_create_error = "Error while creating $zpl";
                                    }

                                    if (!$this->ftpIsDir($ftpConn, $lpn)) {
                                        if (!ftp_mkdir($ftpConn, $lpn))
                                            $ftp_create_error = "Error while creating $lpn";
                                    }

                                    if (!$this->ftpIsDir($ftpConn, $dir_zpl)) {
                                        if (!ftp_mkdir($ftpConn, $dir_zpl))
                                            $ftp_create_error = "Error while creating $dir_zpl";
                                    }

                                    if ($ftp_create_error != '') { //Exit if there's an error creating FTP folders
                                        Utility_FlashMessenger::addMessage('<div class="error">' . $ftp_create_error . '</div>');
                                        exit();
                                    }

                                    //Create CSV file to populate order data after parsing
                                    $file = fopen($local_server_csv_path . $so_number . '_ORDERS.csv', 'w');
                                    //Assign File headers
                                    fputcsv($file, array('asn', 'asn_date', 'ship_date', 'bol', 'carrier_tracking', 'carrier', 'ship_to_name', 'ship_to_location', 'ship_to_address', 'ship_to_city', 'ship_to_state', 'ship_to_zip', 'ship_from_name', 'fob_terms', 'landing_qty', 'po', 'location', 'carton_id', 'line', 'buyer_item', 'vdr_item', 'upc', 'gtin', 'descr', 'color', 'size', 'qty_ship', 'item_no', 'ctn_qty', 'uom', 'lot_number', 'expiration_date', 'barcode_zip', 'barcode_order', 'barcode_upc', 'barcode_zip_text', 'barcode_upc_text', 'printed'));
                                }

                                if ($rec_no > 1) {

                                    if (!is_numeric($data[27])) {
                                        $pei_mapper = New Atlas_Model_ProductExtraInfoMapper();
                                        $per_case_qty = $pei_mapper->buildCaseQty($print_upc_scan);
                                    } else {
                                        $per_case_qty = $data[27];
                                    }
                                    $case_qty = $data[26] / $per_case_qty;
                                    $data[26] = $case_qty;

                                    //Get UPC code
                                    $print_upc = trim($data[21]);

                                    //Check if this UPC is a duplicate to set the start value for the ZPL files count
                                    if (!in_array($print_upc, $single_upcs)) {
                                        $single_upcs[] = $print_upc;
                                        $single_upcs1[$print_upc] = $data[26];
                                        $dup = 0;
                                    } else {
                                        $start_value = $single_upcs1[$print_upc];
                                        $single_upcs1[$print_upc] = $single_upcs1[$print_upc] + $data[26];
                                        $dup = 1;
                                    }

                                    if ($case_qty > 1) { //Item with more than one case
                                        $qty = 0;
                                        $upc = explode('-', $data[17]);
                                        $start_upc = trim($upc[0]);
                                        for ($i = 1; $i <= $case_qty; $i++) {
                                            $qty++;
                                            $row++;
                                            $item_no_file++;
                                            $UPC = $func->current_barcode_iherb($start_upc); //Parse SSCC-18
                                            $start_upc = $func->next_barcode_iherb($start_upc);    //Parse SSCC-18
                                            $barcode_upc_text = '(00)' . substr($UPC, 2);             //Barcode GS1 text
                                            //Get UPC for each ASN
                                            $print_upc = $data[21];

                                            $qty_ship_value = $scan_upc1[$print_upc];
                                            $barcode = str_replace('(', '', str_replace(')', '', $barcode_upc_text));
                                            $zip_code = str_replace('(', '', str_replace(')', '', $barcode_zip_text));

                                            //Insert Record into Database
                                            $save = $this->save(new Atlas_Model_PrintingLabels(array(
                                                "bol" => $so_number,
                                                "po" => $data[15],
                                                "ship_to_name" => $data[6],
                                                "ship_to_address" => $data[8],
                                                "ship_to_city" => $data[9],
                                                "ship_to_state" => $data[10],
                                                "ship_to_zip" => $data[11],
                                                "landing_qty" => (int) $data[14],
                                                "line" => (int) $data[18],
                                                "descr" => $data[23],
                                                "buyer_item" => $data[19],
                                                "vdr_item" => $data[20],
                                                "upc" => $print_upc,
                                                "lot_number" => $data[29],
                                                "qty_ship" => (int) $qty_ship_value,
                                                "item_no" => (int) $item_no_file,
                                                "ctn_qty" => (int) $data[27],
                                                "uom" => $data[28],
                                                "expiration_date" => date('Y-m-d', strtotime($data[30])),
                                                "carton_id" => $UPC,
                                                "barcode_upc_text" => $barcode_upc_text,
                                                "barcode_zip_text" => $barcode_zip_text
                                            )));
                                            //Create ZPL Commands
                                            $cmds = $this->iherbasnLabel($data[6], $data[8], $data[9], $data[10], $data[11], $zip_code, $barcode_zip_text, $data[5], $data[3], $item_no_file, $data[14], $data[15], $data[27], $data[21], $data[20], substr($data[23], 0, 30), $data[29], $data[30], $barcode, $barcode_upc_text);

                                            //Create row in the Excel sheet
                                            fputcsv($file, array($data[0], $data[1], $data[2], $so_number, $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12],
                                                $data[13], $data[14], $data[15], $data[16], $UPC, $data[18], $data[19], $data[20], $print_upc, $data[22], $data[23], $data[24], $data[25],
                                                $qty_ship_value, $item_no_file, $data[27], $data[28], $data[29], $data[30], '0', '0', '0', $barcode_zip_text, $barcode_upc_text, 0));

                                            //Assign file name for ZPL file (file number depends on if it's duplicate or not)
                                            if ($dup == 1) {
                                                $start_value++;
                                                $zpl_path = $local_zpl_path . '/' . $print_upc . '_' . $start_value . '.zpl';
                                                $file_no = $start_value;
                                            } else {
                                                $zpl_path = $local_zpl_path . '/' . $print_upc . '_' . $qty . '.zpl';
                                                $file_no = $qty;
                                            }

                                            //Save ZPL file on local server
                                            file_put_contents($zpl_path, $cmds);

                                            //Move the saved file the FTP server
                                            $remote_file = $dir_zpl . '/' . $print_upc . '_' . $file_no . '.zpl';
                                            $local_file = $zpl_path;
                                            if (!ftp_put($ftpConn, $remote_file, $local_file, FTP_ASCII)) { //Exit if there's a problem moving the file
                                                Utility_FlashMessenger::addMessage('<div class="error">There was a problem while uploading  ' . $local_file . '</div>');
                                                exit();
                                            }
                                        }
                                    } else if ($case_qty == 1) { //If the item count is one case only
                                        $row++;
                                        $item_no_file++;
                                        $UPC = $data[17];
                                        $barcode_upc_text = '(00)' . substr($UPC, 2);
                                        $print_upc = $data[21];

                                        $qty_ship_value = $scan_upc1[$print_upc];
                                        $barcode = str_replace('(', '', str_replace(')', '', $barcode_upc_text));
                                        $zip_code = str_replace('(', '', str_replace(')', '', $barcode_zip_text));

                                        //Insert Record into Database
                                        $save = $this->save(new Atlas_Model_PrintingLabels(array(
                                            "bol" => $so_number,
                                            "po" => $data[15],
                                            "ship_to_name" => $data[6],
                                            "ship_to_address" => $data[8],
                                            "ship_to_city" => $data[9],
                                            "ship_to_state" => $data[10],
                                            "ship_to_zip" => $data[11],
                                            "landing_qty" => (int) $data[14],
                                            "line" => (int) $data[18],
                                            "descr" => $data[23],
                                            "buyer_item" => $data[19],
                                            "vdr_item" => $data[20],
                                            "upc" => $print_upc,
                                            "lot_number" => $data[29],
                                            "qty_ship" => (int) $qty_ship_value,
                                            "item_no" => (int) $item_no_file,
                                            "ctn_qty" => (int) $data[27],
                                            "uom" => $data[28],
                                            "expiration_date" => date('Y-m-d', strtotime($data[30])),
                                            "carton_id" => $UPC,
                                            "barcode_upc_text" => $barcode_upc_text,
                                            "barcode_zip_text" => $barcode_zip_text
                                        )));
                                        //Create ZPL Commands
                                        $cmds = $this->iherbasnLabel($data[6], $data[8], $data[9], $data[10], $data[11], $zip_code, $barcode_zip_text, $data[5], $data[3], $item_no_file, $data[14], $data[15], $data[27], $data[21], $data[20], substr($data[23], 0, 30), $data[29], $data[30], $barcode, $barcode_upc_text);

                                        //Create row in the Excel sheet
                                        fputcsv($file, array($data[0], $data[1], $data[2], $so_number, $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12],
                                            $data[13], $data[14], $data[15], $data[16], $UPC, $data[18], $data[19], $data[20], $print_upc, $data[22], $data[23], $data[24], $data[25],
                                            $qty_ship_value, $item_no_file, $data[27], $data[28], $data[29], $data[30], '0', '0', '0', $barcode_zip_text, $barcode_upc_text, 0));

                                        //Assign file name for ZPL file (file number depends on if it's duplicate or not)
                                        if ($dup == 1) {
                                            $start_value++;
                                            $qty_single = $start_value;
                                            $zpl_path = $local_zpl_path . '/' . $print_upc . '_' . $qty_single . '.zpl';
                                        } else {
                                            $qty_single = 1;
                                            $zpl_path = $local_zpl_path . '/' . $print_upc . '_' . $qty_single . '.zpl';
                                        }

                                        //Save ZPL file on local server
                                        file_put_contents($zpl_path, $cmds);

                                        //Move the saved file the FTP server
                                        $remote_file = $dir_zpl . '/' . $print_upc . '_' . $qty_single . '.zpl';
                                        $local_file = $zpl_path;

                                        if (!ftp_put($ftpConn, $remote_file, $local_file, FTP_ASCII)) { //Exit if there's a problem moving the file
                                            Utility_FlashMessenger::addMessage('<div class="error">There was a problem while uploading  ' . $local_file . '</div>');
                                            exit();
                                        }
                                    }
                                    //Display warnings if there's any with the success MSG
                                    if ($warning != '')
                                        $warning_msg = '<div class="warning">' . $warning . '</div>';
                                    Utility_FlashMessenger::addMessage($warning_msg . '<div class="success">This order has been successfully uploaded.</div>');
                                }
                            }
                        }
                        fclose($file);      //Close parsed CSV file
                        fclose($handle);    //Close order file
                        //Move the Parsed CSV to FTP server
                        $remote_file = '/CASI/LPN/' . $so_number . '_ORDERS.csv';
                        $local_file = $local_server_csv_path . $so_number . '_ORDERS.csv';

                        if (!ftp_put($ftpConn, $remote_file, $local_file, FTP_ASCII)) { //Exit if you couldn't move the file
                            Utility_FlashMessenger::addMessage('<div class="error">There was a problem while uploading ' . $local_file . '</div>');
                            exit();
                        }
                        unlink($local_file);                                //Delete Local CSV File
                        unlink($filepath . $imagename);                       //Delete Original CSV File
                        $this->recursiveRemoveDirectory($local_zpl_path);   //Delete ZPL Directory
                    }
                }
            } else
                Utility_FlashMessenger::addMessage('<div class="error">' . $file_error . '</div>'); //Display file error message
            exit();
        }
    }

#end zebraLabel function
    //Create Amazon label for IHERB - ASN 
    public function iherbasnLabel($name_to, $adress, $city, $state, $zip, $zip_barcode, $zip_barcode_text, $carrier, $bol, $item_no, $landing_qty, $_po_number, $_qty, $_upsnumber, $vendor, $descr, $lot, $exp_date, $carton_id, $carton_id_text) {
        $cmd_label = "^XA
^FO20,230^GB750,4,4^FS
^FO400,30^GB4,200,4^FS
^FO30,40^AQN,40,40^FDSHIP FROM:^FS
^FO30,75^AQN,25,25^FDJarrow Formulas^FS
^FO30,110^AQN,25,25^FD10715 Shoemaker Ave,^FS
^FO30,145^AQN,25,25^FDSanta Fe Springs, CA 90670^FS
^FO410,40^AQN,40,40^FDSHIP TO:^FS
^FO410,75^AQN,25,25^FD$name_to^FS
^FO410,110^AQN,25,25^FD$adress^FS
^FO410,145^AQN,25,25^FD$city $state $zip^FS
^FO400,230^GB2,205,4^FS
^FO20,435^GB750,2,4^FS
^FO60,240^AQN,25,25^FD(420) Ship To Postal Code^FS
^FO60,275^BY3^BCN,122,N,N,A^FD>;>8$zip_barcode^FS    
^FO140,400^AQN,25,25^FD$zip_barcode_text^FS
^FO410,240^AQN,25,25^FDCarrier: $carrier^FS
^FO410,280^AQN,25,25^FDB/L #: $bol^FS
^FO410,340^AQN,25,25^FDNumber Of Cartons: ^FS
^FO410,380^AQN,25,25^FD$item_no of $landing_qty^FS
^FO20,630^GB750,2,4^FS           
^FO30,460^AQN,25,25^FDPO #: $_po_number^FS
^FO30,490^AQN,25,25^FDCarton Qty: $_qty^FS
^FO30,520^AQN,25,25^FDUPC#: $_upsnumber^FS
^FO320,460^AQN,25,25^FDVendor Part #: $vendor^FS
^FO320,490^AQN,25,25^FDDescription: $descr^FS
^FO320,520^AQN,25,25^FDLot: $lot^FS
^FO320,550^AQN,25,25^FDExpiration Date: $exp_date^FS
^FO20,765^GB750,2,4^FS
^FO400,635^GB4,130,4^FS
^FO30,635^AQN,25,25^FDMark for Location Number^FS
^FO410,635^AQN,25,25^FDMark For: ^FS
^FO30,780^AQN,5,5^FDSerialized Shipping Container Number^FS
^FO70,855^BY4^BCN,280,N,N,A^FD>;>8$carton_id^FS  
^FO150,1140^AQN,45,40^FD$carton_id_text^FS
^XZ";
        return $cmd_label;
    }

    //Create Amazon label for IHERB - ASN 
    public function spsasnLabel($name_to, $adress, $city, $state, $zip, $zip_barcode, $zip_barcode_text, $carrier, $tracking, $bol, $item_no, $landing_qty, $_po_number, $_qty, $item, $_upsnumber, $vendor, $descr, $lot, $exp, $carton_id, $carton_id_text) {
        $exp_date = date("m/d/Y", strtotime($exp));
        $desc_limit = substr($descr, 0, 32);
        $cmd_label = "^XA
^FO20,230^GB750,4,4^FS
^FO400,30^GB4,200,4^FS
^FO30,40^AQN,40,40^FDSHIP FROM:^FS
^FO30,75^AQN,25,25^FDJarrow Formulas^FS
^FO30,110^AQN,25,25^FD10715 Shoemaker Ave,^FS
^FO30,145^AQN,25,25^FDSanta Fe Springs, CA 90670^FS
^FO410,40^AQN,40,40^FDSHIP TO:^FS
^FO410,75^AQN,25,25^FD$name_to^FS
^FO410,110^AQN,25,25^FD$adress^FS
^FO410,145^AQN,25,25^FD$city $state $zip^FS
^FO400,230^GB2,205,4^FS
^FO20,435^GB750,2,4^FS
^FO60,240^AQN,25,25^FD(420) Ship To Postal Code^FS
^FO60,275^BY3^BCN,122,N,N,A^FD>;>8$zip_barcode^FS    
^FO140,400^AQN,25,25^FD$zip_barcode_text^FS
^FO410,240^AQN,25,25^FDCarrier: $carrier^FS
^FO410,270^AQN,25,25^FDPRO#: $tracking^FS
^FO410,300^AQN,25,25^FDB/L #: ^FS
^FO410,330^AQN,25,25^FDPO #: $_po_number^FS
^FO410,360^AQN,25,25^FDNumber Of Cartons: ^FS
^FO410,390^AQN,25,25^FD$item_no of $landing_qty^FS
^FO20,660^GB750,2,4^FS           
^FO30,460^AQN,25,25^FDCarton Qty: $_qty^FS
^FO30,490^AQN,25,25^FDUPC#: $_upsnumber^FS
^FO30,520^AQN,45,35^FDVendor Part #: $item^FS
^FO60,580^BY3^BUN,40,A,N^FD$_upsnumber^FS  
^FO320,460^AQN,25,25^FDBuyer Part #: $vendor^FS
^FO320,490^AQN,25,25^FDDescription: $desc_limit^FS
^FO320,520^AQN,25,25^FDLot: $lot^FS
^FO320,550^AQN,25,25^FDExpiration Date: $exp_date^FS
^FO20,795^GB750,2,4^FS
^FO400,665^GB4,130,4^FS
^FO30,665^AQN,25,25^FDMark for Location Number^FS
^FO410,665^AQN,25,25^FDMark For: ^FS
^FO30,805^AQN,5,5^FDSerialized Shipping Container Number^FS
^FO70,855^BY4^BCN,280,N,N,A^FD>;>8$carton_id^FS  
^FO150,1140^AQN,45,40^FD$carton_id_text^FS
^XZ";
        return $cmd_label;
    }

    public function buildOrderLabels($order_no) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "printing_labels"), array('t.*'))
                ->where("bol = ?", $order_no);

        // return the select statement	
        $results = $select->query()->fetchAll();
        return $results;
    }

#end buildOrderLabels function    

    public function buildOrderItemLabel($form_data) {
        $order_no = $form_data['order_no'];
        $item_no = $form_data['item_no'];
        $item_no_end = !empty($form_data['item_no_end']) ? $form_data['item_no_end'] : null;
        $repeat_count = !empty($form_data['repeat_count']) ? $form_data['repeat_count'] : 1;

        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "printing_labels"), array('t.*'))
                ->where("bol = ?", $order_no);
        if (empty($item_no_end)) {
            $select->where("item_no = ?", $item_no);
            $filename = $order_no . '_' . $item_no . '_ZPL.txt';
        } else {
            $select->where("item_no >= ?", $item_no)
                    ->where("item_no <= ?", $item_no_end);

            $filename = $order_no . '_' . $item_no . '_TO_' . $item_no_end . '_ZPL.txt';
        }
        // return the select statement	
        $results = $select->query()->fetchAll();

        $cmds[] = '${';
        foreach ($results as $result) {
            $i = 1;
            while ($i <= $repeat_count) {
                if ($form_data['partner_code'] == 'BIY') {
                    $cmds[] = $this->spsasnLabel($result['ship_to_name'], $result['ship_to_address'], $result['ship_to_city'], $result['ship_to_state'], $result['ship_to_zip'], '420' . $result['ship_to_zip'], $result['barcode_zip_text'], '', '', $result['bol'], $result['item_no'], $result['landing_qty'], $result['po'], $result['ctn_qty'], $result['vdr_item'], $result['upc'], $result['buyer_item'], $result['descr'], $result['lot_number'], $result['expiration_date'], $result['carton_id'], $result['barcode_upc_text']);
                } else if ($form_data['partner_code'] == '080') {
                    $cmds[] = $this->zebraLabel($result['ship_to_name'], $result['store_id'], $result['ship_to_address'], $result['ship_to_city'], $result['ship_to_state'], $result['ship_to_zip'], '420' . $result['ship_to_zip'], $result['barcode_zip_text'], $result['bol'], $result['po'], $result['buyer_item'], $result['item_no'], $result['landing_qty'], $result['lot_number'], $result['expiration_date'], $result['carton_id'], $result['barcode_upc_text']);
                }
                $i++;
            }
        }
        $path = Zend_Registry::get("target_path") . '/uploads/barcodes/' . $order_no . '_TXT/' . $filename;

        $cmds[] = '}$';
        file_put_contents($path, $cmds); //Save All lables in ZPL file on local server
        return $path;
    }

#end buildOrderItemLabel function    

    public function updateOrderItem($order_no, $line, $exp_date, $lot_no, $partner_code) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "printing_labels"), array('t.*'))
                ->where("bol = ?", $order_no)
                ->where("line = ?", $line);

        // return the select statement	
        $results = $select->query()->fetchAll();
        $cmdlist[] = '${';
        $new_exp_date = date("Y-m-d", $exp_date);
        $label_exp_date = date("m/d/Y", $exp_date);
        foreach ($results as $row) {
            $entry = new Atlas_Model_PrintingLabels();
            $entry->setOptions($row);
            $entry->setExpiration_date($new_exp_date)
                    ->setLot_number($lot_no);
            $this->save($entry);

            if ($partner_code == 'BIY') {
                $cmds = $this->spsasnLabel($row['ship_to_name'], $row['ship_to_address'], $row['ship_to_city'], $row['ship_to_state'], $row['ship_to_zip'], '(420)' . $row['ship_to_zip'], $row['barcode_zip_text'], '', '', $row['bol'], $row['item_no'], $row['landing_qty'], $row['po'], $row['ctn_qty'], $row['vdr_item'], $row['upc'], $row['buyer_item'], $row['descr'], $lot_no, $label_exp_date, $row['carton_id'], $row['barcode_upc_text']);
            } else if ($partner_code == '080') {
                $cmds = $this->zebraLabel($row['ship_to_name'], $row['store_id'], $row['ship_to_address'], $row['ship_to_city'], $row['ship_to_state'], $row['ship_to_zip'], '420' . $row['ship_to_zip'], $row['barcode_zip_text'], $row['bol'], $row['po'], $row['buyer_item'], $row['item_no'], $row['landing_qty'], $row['lot_number'], $row['expiration_date'], $row['carton_id'], $row['barcode_upc_text']);
            }
            $cmdlist[] = $cmds;
        }
        $path = Zend_Registry::get("target_path") . '/uploads/barcodes/' . $order_no . '_TXT/LINE_' . $line . '_ZPL.txt';
        $cmdlist[] = '}$';
        file_put_contents($path, $cmdlist); //Save All lables in ZPL file on local server
        return $path;
    }

#end updateOrderItem function

    public function createCsvFileDownload($printing_lables, $so_number) {
        //Create CSV file to populate order data after parsing
        $asn_file_path = Zend_Registry::get("target_path") . '/uploads/csv/' . $so_number . '_ASNORDERS.csv';
        $asn_file = fopen($asn_file_path, 'w');
        fputcsv($asn_file, array('ASN#', 'ASN Date', 'Ship Date', 'BOL', 'Carrier Tracking', 'Carrier', 'Ship To Name', 'Ship To Location', 'Ship To Address', 'Ship To City', 'Ship To State', 'Ship To Zip',
            'Ship From Name', 'FOB Terms', 'Lading Qty', 'PO #', 'Location #', 'Carton ID', 'Line #', 'Buyer Item #', 'Vdr Item #', 'UPC', 'GTIN', 'Desc', 'Color', 'Size',
            'Qty Ship', 'CTN Qty', 'UOM', 'LOT NUMBER', 'EXPIRATION DATE', 'SCAC'));
        $lines = array_values(array_unique(array_column($printing_lables, 'line')));
        foreach ($lines as $line) {
            $line_data = array_filter($printing_lables, function ($var) use ($line) {
                return ($var['line'] == $line);
            });
            if (count($line_data) > 1) {
                $first_row = reset($line_data);
                $last_row = end($line_data);
                $serial_sequence = $first_row['carton_id'] . ' - ' . $last_row['carton_id'];
            } else {
                $first_row = reset($line_data);
                $serial_sequence = $first_row['carton_id'];
            }
            fputcsv($asn_file, array('', date('Y-m-d'), $headerdata[0]['ship_date'], $first_row['bol'], $headerdata[0]['tracking_number'], $headerdata[0]['carrier'], $first_row['ship_to_name'], '',
                $first_row['ship_to_address'], $first_row['ship_to_city'], $first_row['ship_to_state'], $first_row['ship_to_zip'], "Jarrow Formulas", '',
                $first_row['landing_qty'], $first_row['po'], $first_row['store_id'], $serial_sequence, $line, $first_row['buyer_item'], $first_row['vdr_item'], $first_row['upc'], '',
                $first_row['descr'], '', '', $first_row['qty_ship'], $first_row['ctn_qty'], 'EACH', $first_row['lot_number'], $first_row['expiration_date'], ''));
        }
        fclose($asn_file); //Close parsed CSV file
        return $asn_file_path; //Return File Path To Download
    }

    public function createZPLFiles($printing_lables, $so_number, $partner_code) {
        //Create CSV file to populate order data after parsing
        $file = fopen(Zend_Registry::get("target_path") . '/uploads/csv/' . $so_number . '_ORDERS.csv', 'w');
        //Create ZPL Folder Path
        $local_zpl_path = Zend_Registry::get("target_path") . '/uploads/barcodes/' . $so_number . '_ZPL';
        if (!is_dir($local_zpl_path))
            mkdir($local_zpl_path, 0777, true);
        //Assign CSV File headers
        fputcsv($file, array('asn', 'asn_date', 'ship_date', 'bol', 'carrier_tracking', 'carrier', 'ship_to_name', 'ship_to_location', 'ship_to_address', 'ship_to_city', 'ship_to_state',
            'ship_to_zip', 'ship_from_name', 'fob_terms', 'landing_qty', 'po', 'location', 'carton_id', 'line', 'buyer_item', 'vdr_item', 'upc', 'gtin', 'descr', 'color',
            'size', 'qty_ship', 'item_no', 'ctn_qty', 'uom', 'lot_number', 'expiration_date', 'barcode_zip', 'barcode_order', 'barcode_upc', 'barcode_zip_text',
            'barcode_upc_text', 'printed'));
        $prev_number = [];     //Array to be used to check Item Count
        $barcode_zip_text = '(420)' . $printing_lables[0]['ship_to_zip'];
        $zip_code = str_replace('(', '', str_replace(')', '', $barcode_zip_text));
        foreach ($printing_lables as $key => $line) {
            //Create row in the Excel sheet
            fputcsv($file, array('', date('Y-m-d'), $headerdata[0]['ship_date'], $line['bol'], $headerdata[0]['tracking_number'], $headerdata[0]['carrier'], $line['ship_to_name'], '',
                $line['ship_to_address'], $line['ship_to_city'], $line['ship_to_state'], $line['ship_to_zip'], "Jarrow Formulas", '', $line['landing_qty'], $line['po'], $line['store_id'],
                $line['carton_id'], $line['line'], $line['buyer_item'], $line['vdr_item'], $line['upc'], '', '', '', '', $line['qty_ship'], $key + 1, $line['ctn_qty'], 'EACH',
                $line['lot_number'], $line['expiration_date'], '0', '0', '0', $line['carton_id'], $line['barcode_upc_text'], 0));
            //Create Label Commands
            if ($partner_code == 'BIY') {
                $cmds = $this->spsasnLabel($line['ship_to_name'], $line['ship_to_address'], $line['ship_to_city'], $line['ship_to_state'], $line['ship_to_zip'], $zip_code, $barcode_zip_text, $headerdata[0]['carrier'], $headerdata[0]['tracking_number'], $line['bol'], $key + 1, $line['landing_qty'], $line['po'], $line['ctn_qty'], $line['vdr_item'], $line['upc'], $line['buyer_item'], $line['descr'], $line['lot_number'], $line['expiration_date'], $line['carton_id'], $line['barcode_upc_text']);
            } else if ($partner_code == '080') {
                $cmds = $this->zebraLabel($line['ship_to_name'], $line['store_id'], $line['ship_to_address'], $line['ship_to_city'], $line['ship_to_state'], $line['ship_to_zip'], $zip_code, $barcode_zip_text, $line['bol'], $line['po'], $line['buyer_item'], $key + 1, $line['landing_qty'], $line['lot_number'], $line['expiration_date'], $line['carton_id'], $line['barcode_upc_text']);
            }
            //File name check by upc no
            if (array_key_exists($line['upc'], $prev_number))
                $file_number = $prev_number[$line['upc']] + 1;
            else
                $file_number = 1;
            $prev_number[$line['upc']] = $file_number;
            $zpl_path = $local_zpl_path . '/' . $line['upc'] . '_' . $file_number . '.zpl';
            file_put_contents($zpl_path, $cmds);
        }
        fclose($file); //Close parsed CSV file
        //Set Local directories for file upload
        $zpl_dir = Zend_Registry::get("target_path") . '/uploads/barcodes/' . $so_number . "_ZPL";
        $csv_dir = Zend_Registry::get("target_path") . '/uploads/csv';

        //Build CASI FTP Connection to upload files
        $ftpConn = $this->buildCasiFtpConnection();
        if (strtolower(APPLICATION_ENV) == "production") {
            $zpl_folder = "/CASI/ZPL/" . $so_number . "_ZPL";
            $csv_folder = "/CASI/LPN/";
        } else {
            $zpl_folder = "/CASI/ORDERCOUNT/" . $so_number . "_ZPL";
            $csv_folder = "/CASI/PICKING/";
        }
        if (!$this->ftpIsDir($ftpConn, $zpl_folder)) {
            if (!ftp_mkdir($ftpConn, $zpl_folder))
                $ftp_create_error = "Error while creating $zpl_folder";
        }

        if (is_dir($zpl_dir)) {
            if ($dh = opendir($zpl_dir)) {
                while (($file = readdir($dh))) {
                    $filex = explode('.', $file);
                    if (end($filex) == 'zpl') {
                        $remote_file = $zpl_folder . '/' . $file;
                        $local_file = $zpl_dir . '/' . $file;
                        $zpl_check = ftp_put($ftpConn, $remote_file, $local_file, FTP_BINARY);
                    }
                }
            }
        }
        $csv_file = $so_number . "_ORDERS.csv";
        $remote_file = $csv_folder . $csv_file;
        $local_file = $csv_dir . '/' . $csv_file;
        $txt_check = ftp_put($ftpConn, $remote_file, $local_file, FTP_BINARY);
        if ($zpl_check && $txt_check)
            return true;
        else
            return false;
    }

    public function createZIP($so_number, $printing_labels, $partner_code) {
        //Create Split files
        $i = 1;
        $split[] = '${';
        foreach ($printing_labels as $lable) {
            if ($partner_code == 'BIY') {
                $split[] = $this->spsasnLabel($lable['ship_to_name'], $lable['ship_to_address'], $lable['ship_to_city'], $lable['ship_to_state'], $lable['ship_to_zip'], '420' . $lable['ship_to_zip'], $lable['barcode_zip_text'], '', '', $lable['bol'], $lable['item_no'], $lable['landing_qty'], $lable['po'], $lable['ctn_qty'], $lable['vdr_item'], $lable['upc'], $lable['buyer_item'], $lable['descr'], $lable['lot_number'], $lable['expiration_date'], $lable['carton_id'], $lable['barcode_upc_text']);
            } else if ($partner_code == '080') {
                $split[] = $this->zebraLabel($lable['ship_to_name'], $lable['store_id'], $lable['ship_to_address'], $lable['ship_to_city'], $lable['ship_to_state'], $lable['ship_to_zip'], '420' . $lable['ship_to_zip'], $lable['barcode_zip_text'], $lable['bol'], $lable['po'], $lable['buyer_item'], $lable['item_no'], $lable['landing_qty'], $lable['lot_number'], $lable['expiration_date'], $lable['carton_id'], $lable['barcode_upc_text']);
            }
            if ($i % 250 == 0 || $i == $lable['landing_qty']) {
                $split_path = Zend_Registry::get("target_path") . '/uploads/barcodes/' . $so_number . '_TXT/' . $so_number . '_SPLIT_' . ($i) . '_ZPL.txt';
                $split[] = '}$';
                file_put_contents($split_path, $split); //Save Splited files
                $split = [0 => '${'];
            }
            $i++;
        }

        $archive_file_name = Zend_Registry::get("target_path") . "/uploads/barcodes/" . $so_number . "_TXT/ZIP_" . $so_number . ".zip";
        $zip = new ZipArchive;
        if ($zip->open($archive_file_name, ZIPARCHIVE::CREATE) !== true) {
            throw new Exception("Failed to create archive.");
        }
        //Create ZIP file
        $zpl_dir = Zend_Registry::get("target_path") . '/uploads/barcodes/' . $so_number . "_TXT/";

        if (is_dir($zpl_dir)) {
            if ($dh = opendir($zpl_dir)) {
                while (($file = readdir($dh)) !== false) {
                    // If file
                    if (is_file($zpl_dir . $file)) {
                        if ($file != '' && $file != '.' && $file != '..' && strpos($file, 'SPLIT')) {
                            $zip->addFile($zpl_dir . $file, $file);
                        }
                    }
                }
                closedir($dh);
            }
        }

        $zip->close();
        return $archive_file_name;
    }

    public function buildOrderChoosedLabels($order_no, $choosed) {
        $choosed = implode(",", array_keys($choosed));

        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "printing_labels"), array('t.*'))
                ->where("bol = ?", $order_no)
                ->where("line IN (" . $choosed . ")");

        // return the select statement
        $results = $select->query()->fetchAll();
        return $results;
    }

#end buildOrderChoosedLabels function
}

?>