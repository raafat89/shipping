<?php
class ShippingController extends Zend_Controller_Action {
    public function init() {
        // set the CSS documents for the website
        $this->view->css_docs = array(
            Zend_Registry::get("global_css"),
            "/css/shipping.css",
            "/css/smoothness/jquery-ui-1.8.17.custom.css",
            "/css/smoothness/jalerts.css"
        );
        // set the JS documents for the website
        $this->view->js_docs = array(
            "/js/jquery.1.6.2.js", 
            "/js/jquery-ui-1.8.17.custom.min.js", 
            "/js/jquery.jBreadCrumb.1.1.js",
            "/js/jquery.easing.1.3.js", 
            "/js/jquery.dataTables.min.js",
            "/js/jalerts.js",
            "/js/jquery.form.js",
            "/js/global.js", 
            "/js/shipping.js");

        // set the default layout
        $this->_helper->layout->setLayout('layout');

        // check if user is logged in and if they can access the current page
        $uri = $this->getRequest()->getRequestUri();
        $session = Utility_Session::getInstance(null, Zend_Registry::get("session_length"), 'R', (strtolower(APPLICATION_ENV) == "production") ? Zend_Registry::get("domain") : (Zend_Registry::get("server_domain")), Zend_Registry::get("cur_server"));
        if (!Utility_Session::isSession()) { // MAKE SURE SESSION IS LIVE
            return $this->_redirect(Zend_Registry::get('full_url') . "/login");
        }

        try { // TRY TO GET SESSION DATA
            Utility_Session::extendSession(Zend_Registry::get("session_length"));
            Zend_Registry::set("user_id", $session->get('user_id'));
            Zend_Registry::set("username", $session->get('username'));
            Zend_Registry::set("name", $session->get('name'));
            Zend_Registry::set("email", $session->get('email'));
            Zend_Registry::set("permission_group_ids", $session->get('permission_group_ids'));
            Zend_Registry::set("user_info", $session->get('ticket_info'));
            Zend_Registry::set("admin_nav", $session->get("admin_nav"));
        } catch (Exception $e) { // KILL SESSION AND REDIRECT TO LOGIN ON FAILURE
            Utility_Session::_unsetSession();
            sleep(4);
            Utility_FlashMessenger::addMessage(
                    '<div class="error">Your session has timed out, please log in again. Sorry for the inconvenience.</div>'
            );
            return $this->_redirect(Zend_Registry::get('full_url')."/login");
        }

        if (!Utility_Functions::canUserAccess($uri)) { // MAKE SURE USER HAS PERMISSION
            // log the failed access
            $admin = Zend_Registry::get('admin');
            $mapper = new Atlas_Model_AccessLogMapper();
            $log = new Atlas_Model_AccessLog();
            $log->setTimestamp(date("Y-m-d H:i:s"))
                    ->setUser_id($session->get('user_id'))
                    ->setIp_address(Zend_Registry::get("ip_add"))
                    ->setMessage("User attempted to access: " . $uri);
            $mapper->save($log);

            Utility_FlashMessenger::addMessage(
                    '<div class="error">You don\'t have permission to view this page. This attempt has been logged and if the attempt resembles an intrusion you will be contacted by your supervisors. However, if you feel you got this message in error please contact ' . $admin['email'] . '</div>'
            );
            return $this->_redirect('/dashboard');
        }

        // initialize a cache object
        $frontend_options = array(
            'lifetime' => 86400, // cache lifetime of 24 hours
            'automatic_serialization' => false, // manual serialization for optimization
            'cache_id_prefix' => "Atlas_", // atlas system prefix
            'ignore_user_abort' => true      // attempt to prevent corruption
        );
        $backend_options = array(
            'cache_dir' => '../cache/' // Directory where to put the cache files
        );
        $cache = Zend_Cache::factory(
                        'Core', 'File', $frontend_options, $backend_options
        );
        Zend_Registry::set("cache_handler", $cache);

        // pop all pending messages
        $this->view->messages = Utility_FlashMessenger::popMessage();
    }

    //Generate QR Code to be Placed on PDF label
    public function qrcodeAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        $request = $this->getRequest();
        $so_number = Utility_Filter_DBSafe::clean($request->getParam("so", ""));
        $i = (int) $request->getParam("i", 0);

        $cache = Zend_Registry::get('cache_handler');
        $shipping = new Atlas_Model_ProductExtraInfoMapper();
        if (($data = $cache->load("LABELS_" . $so_number)) == false) {
            $data = $shipping->getLabelData($so_number);
            $cache->save(serialize($data), "LABELS_" . $so_number, array("label_data"));
        } else {
            $data = unserialize($data);
        }

        require_once(Zend_Registry::get("root_path") . "/library/phpqrcode/qrlib.php");
        QRcode::png('AMZN,PO:' . $data[$i]['PO_Num']);
    }

    //Check labels count for a specific order
    public function checklabelsAction() {
        $this->view->title = "DEBUG: Labels";
        $request = $this->getRequest();
        $form = new Atlas_Form_Shipping();
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            if ($form->isValid($form_data)) {
                $so_number = $form_data['so_number'];
                $shipping = new Atlas_Model_ProductExtraInfoMapper();
                $data = $shipping->getLabelCount($so_number);
                $this->view->data   =   $data;
                $this->so_number    =   $so_number;
            }
        } else {
            $this->view->data = NULL;
        }
        $this->view->form = $form;
    }

    //Generate Shipping Labels for cases for a specific order
    public function labelsAction() {
        $this->view->title = "Case Labels";

        $request = $this->getRequest();
        $page = (int) $request->getParam("page", 0);
        $so_num = Utility_Filter_DBSafe::clean($request->getParam("so", ""));
        $qr_code = (int) $request->getParam("qr", 0);
        $labels = (int) $request->getParam("tot", 0);
        $max = 28;
        $form = new Atlas_Form_Shipping();
        $cur_set = $max * $page;

        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            if ((int) $form_data['amazon'] > 0) {
                $form_data['num_labels'] = 0;
            }
            if ($form->isValid($form_data)) {
                $so_number = $form_data['so_number'];
                $shipping = new Atlas_Model_ProductExtraInfoMapper();
                $printing_labels_mapper = new Atlas_Model_PrintingLabelsMapper();
                try {
                    $data = $shipping->getLabelData($so_number, (int) $form_data['num_labels']);
                    if ($form_data['casi'] == 1 && !$printing_labels_mapper->isOrderExist($so_number)) {
                        $casi_labels = $printing_labels_mapper->caseLabels($so_number, $data);
                    }
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect('/shipping/labels');
                }

                if ($form_data['amazon'] == 1) {
                    require_once(Zend_Registry::get("root_path") . "/library/phpqrcode/qrlib.php");
                    for ($i = $cur_set; $i - $cur_set < $max; ++$i) {
                        $total_qty = 0;
                        $cur_qty = 0;
                        while ($total_qty < $data[$i]['Qty']) {
                            $total_qty += $data[$i]['Qty_Case']['qty_case'];
                            $cur_qty = ($total_qty <= $data[$i]['qty']) ? $data[$i]['Qty_Case']['qty_case'] : ($total_qty - $data[$i]['qty']);
                            if (!is_file(Zend_Registry::get("root_path") . '/public/images/uploads/qrcode/case_' . $so_number . '-' . $data[$i]['Prod_Code'] . '-' . $i . '.png')) {
                                $file_name  =   'case_' . str_replace(" ", "_", $so_number . '-' . $data[$i]['Prod_Code'] . '-' . $i . '.png');
                                $file_path  =   Zend_Registry::get("root_path") . '/public/images/uploads/qrcode/'.$file_name;
                                QRcode::png('AMZN,PO:' . $data[$i]['PO_Num'] . ',UPC:' . $data[$i]['UPC'] . ',QTY:' . $cur_qty . ',EXP:' . date("ymd", strtotime($data[$i]['Expires'])) . ',LOT:' . $data[$i]['Lot_Number'], Zend_Registry::get("root_path") . '/public/images/uploads/qrcode/'.$file_name);
                                $curl_connect = new Utility_CURLConnection();
                                $curl_connect->uploadFileViaRedirect(
                                        $file_path, Zend_Registry::get("jw")."/curl/upload/type/pdfimages/filename/".$file_name."/pass/".time().".".md5(time()."_09q87543_SALTY_9q8er-")
                                );
                            }
                        }
                    }
                }

                $file_name = $so_number . "_" . $page . "_cases.pdf";
                $mapper = new Atlas_Model_PDFMapper('utf-8', array(101.6, 152.4), "L");
                $mapper->addCSSFile(Zend_Registry::get("root_path") . "/public/css/AVERY-5168.css");
                $mapper->addContent(
                        $this->view->partial('/partials/shipping/case_labels_full.phtml', array(
                            "so_number" => $so_number,
                            "data" => $data,
                            "form" => NULL,
                            "amazon" => $form_data['amazon'],
                            "total" => count($data),
                            "page" => $page,
                            "max" => $max
                        ))
                );
                $mapper->outputPDFtoFile(Zend_Registry::get("target_path") . "/images/uploads/qrcode/" . $file_name);

                $this->view->file = $file_name;
                $this->view->page = $page;
                $this->view->so_number = $so_number;
                $this->view->amazon = $form_data['amazon'];
                $this->view->total = $labels;
                $this->view->max = $max;
                $this->view->data = $data;
            }
        } else if (trim($so_num) != "") {
            $so_number = $so_num;
            $shipping = new Atlas_Model_ProductExtraInfoMapper();

            try {
                $data = $shipping->getLabelData($so_number, $labels);
            } catch (Exception $e) {
                Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                return $this->_redirect('/shipping/labels');
            }

            if ($qr_code == 1) {
                require_once(Zend_Registry::get("root_path") . "/library/phpqrcode/qrlib.php");
                for ($i = $cur_set; $i - $cur_set < $max; ++$i) {
                    $total_qty = 0;
                    $cur_qty = 0;
                    while ($total_qty < $data[$i]['Qty']) {
                        $total_qty += $data[$i]['Qty_Case']['qty_case'];
                        $cur_qty = ($total_qty <= $data[$i]['qty']) ? $data[$i]['Qty_Case']['qty_case'] : ($total_qty - $data[$i]['qty']);
                        if (!is_file(Zend_Registry::get("root_path") . '/public/images/uploads/qrcode/case_' . $so_number . '-' . $data[$i]['Prod_Code'] . '-' . $i . '.png')) {
                            $file_name  =   'case_' . str_replace(" ", "_", $so_number . '-' . $data[$i]['Prod_Code'] . '-' . $i . '.png');
                            $file_path  =   Zend_Registry::get("root_path") . '/public/images/uploads/qrcode/'.$file_name;
                            QRcode::png('AMZN,PO:' . $data[$i]['PO_Num'] . ',UPC:' . $data[$i]['UPC'] . ',QTY:' . $cur_qty . ',EXP:' . date("ymd", strtotime($data[$i]['Expires'])) . ',LOT:' . $data[$i]['Lot_Number'], $file_path);
                            $curl_connect = new Utility_CURLConnection();
                            $curl_connect->uploadFileViaRedirect(
                                    $file_path, Zend_Registry::get("jw")."/curl/upload/type/pdfimages/filename/".$file_name."/pass/".time().".".md5(time()."_09q87543_SALTY_9q8er-")
                            );
                        }
                    }
                }
            }

            $file_name = $so_number . "_" . $page . "_cases.pdf";
            $mapper = new Atlas_Model_PDFMapper('utf-8', array(101.6, 152.4), "L");
            $mapper->addCSSFile(Zend_Registry::get("root_path") . "/public/css/AVERY-5168.css");
            $mapper->addContent(
                    $this->view->partial('/partials/shipping/case_labels_full.phtml', array(
                        "so_number" => $so_number,
                        "data" => $data,
                        "form" => NULL,
                        "amazon" => $qr_code,
                        "total" => count($data),
                        "page" => $page,
                        "max" => $max
                    ))
            );
            $mapper->outputPDFtoFile(Zend_Registry::get("target_path") . "/images/uploads/qrcode/" . $file_name);

            $this->view->file = $file_name;
            $this->view->page = $page;
            $this->view->so_number = $so_number;
            $this->view->amazon = $qr_code;
            $this->view->total = $labels;
            $this->view->max = $max;
            $this->view->data = $data;
        } else {
            $this->view->form = $form;
            $this->view->data = NULL;
        }
    }

    //Generate Standard labels for a specific order
    public function polabelsAction() {
        $this->view->title = "Carton Labels";

        $request = $this->getRequest();
        $page = (int) $request->getParam("page", 0);
        $so_num = Utility_Filter_DBSafe::clean($request->getParam("so", ""));
        $qr_code = (int) $request->getParam("qr", 0);
        $labels = (int) $request->getParam("tot", 0);
        $sku = str_replace("_", " ", Utility_Filter_DBSafe::clean($request->getParam("sku", "")));
        $max = 28;
        $cur_set = $max * $page;
        $form = new Atlas_Form_POLabels();
        $this->view->form   =   $form;
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            if ((int) $form_data['total_labels'] <= 0) {
                Utility_FlashMessenger::addMessage(
                        '<div class="error">You need to tell the system how many labels to create.</div>'
                );
                return $this->_redirect('/shipping/polabels');
            }
            if ($form->isValid($form_data)) {
                $so_number = $form_data['so_number'];
                $mapper = new Atlas_Model_Inform3sales();
                $data = $mapper->buildSOData($so_number);
                $final_data = array();
                for ($i = 0; $i < $form_data['total_labels']; ++$i) {
                    $final_data[] = array(
                        "name" => $data[0]['custname'],
                        "address" => $data[0]['shipaddr1'],
                        "address_2" => $data[0]['shipaddr2'],
                        "city" => $data[0]['shipcity'],
                        "state" => $data[0]['shipstate'],
                        "zip" => $data[0]['shipzip'],
                        "country" => $data[0]['shipcntry'],
                        "sku_num" => $form_data['sku_number'],
                        "so_num" => $form_data['so_number'],
                        "po_num" => $data[0]['PO_Num'],
                        "cur" => ($i + 1),
                        "total" => $form_data['total_labels']
                    );
                }

                $this->view->amazon = $form_data['amazon'];
                if ($form_data['amazon'] == 1) {
                    require_once(Zend_Registry::get("root_path") . "/library/phpqrcode/qrlib.php");
                    $file_name  =   'carton_' . str_replace(" ", "_", $form_data['so_number']) . '.png';
                    $file_path  =   Zend_Registry::get("root_path") . '/public/images/uploads/qrcode/'.$file_name;
                    QRcode::png('AMZN,SO:' . $form_data['so_number'] . ',SKU:' . $form_data['sku_number'] . ',PO:' . $data[0]['PO_Num'], $file_path);
                    $curl_connect = new Utility_CURLConnection();
                    $curl_connect->uploadFileViaRedirect(
                            $file_path, Zend_Registry::get("jw")."/curl/upload/type/pdfimages/filename/".$file_name."/pass/".time().".".md5(time()."_09q87543_SALTY_9q8er-")
                    );
                    sleep(1);
                }

                $file_name = $so_number . "_" . $page . "_cartons.pdf";
                $mapper = new Atlas_Model_PDFMapper('utf-8', array(101.6, 152.4), "L");
                $mapper->addCSSFile(Zend_Registry::get("root_path") . "/public/css/AVERY-5168.css");
                $mapper->addContent(
                        $this->view->partial('/partials/shipping/carton_labels_full.phtml', array(
                            "data" => $final_data,
                            "form" => NULL,
                            "amazon" => $this->view->amazon,
                            "total" => $form_data['total_labels'],
                            "page" => $page,
                            "max" => $max
                        ))
                );

                $mapper->outputPDFtoFile(Zend_Registry::get("target_path") . "/images/uploads/qrcode/" . $file_name);

                $this->view->file = $file_name;
                $this->view->page = $page;
                $this->view->so_number = $so_number;
                $this->view->amazon = $form_data['amazon'];
                $this->view->total = $form_data['total_labels'];
                $this->view->sku = $form_data['sku_number'];
                $this->view->max = $max;
                $this->view->data = $data;
            }else {
                $message = Utility_Error::buildErrors($this->view->form->getMessages());
                $this->view->messages = $message;
            }
        } else if (trim($so_num) != "") {
            $so_number = $so_num;
            $mapper = new Atlas_Model_Inform3sales();
            $data = $mapper->buildSOData($so_number);
            $final_data = array();
            for ($i = 0; $i < $labels; ++$i) {
                $final_data[] = array(
                    "name" => $data[0]['custname'],
                    "address" => $data[0]['shipaddr1'],
                    "address_2" => $data[0]['shipaddr2'],
                    "city" => $data[0]['shipcity'],
                    "state" => $data[0]['shipstate'],
                    "zip" => $data[0]['shipzip'],
                    "country" => $data[0]['shipcntry'],
                    "sku_num" => $sku,
                    "so_num" => $so_number,
                    "po_num" => $data[0]['PO_Num'],
                    "cur" => ($i + 1),
                    "total" => $labels
                );
            }

            $this->view->amazon = $qr_code;
            if ($qr_code == 1) {
                require_once(Zend_Registry::get("root_path") . "/library/phpqrcode/qrlib.php");
                $file_name  =   'carton_' . str_replace(" ", "_", $form_data['so_number']) . '.png';
                $file_path  =   Zend_Registry::get("root_path") . '/public/images/uploads/qrcode/'.$file_name;
                QRcode::png('AMZN,SO:' . $so_number . ',SKU:' . $sku . ',PO:' . $data[0]['PO_Num'], $file_path);
                $curl_connect = new Utility_CURLConnection();
                $curl_connect->uploadFileViaRedirect(
                        $file_path, Zend_Registry::get("jw")."/curl/upload/type/pdfimages/filename/".$file_name."/pass/".time().".".md5(time()."_09q87543_SALTY_9q8er-")
                );
                sleep(1);
            }

            $file_name = $so_number . "_" . $page . "_cartons.pdf";
            $mapper = new Atlas_Model_PDFMapper('utf-8', array(101.6, 152.4), "L");
            $mapper->addCSSFile(Zend_Registry::get("root_path") . "/public/css/AVERY-5168.css");
            $mapper->addContent(
                    $this->view->partial('/partials/shipping/carton_labels_full.phtml', array(
                        "data" => $final_data,
                        "form" => NULL,
                        "amazon" => $this->view->amazon,
                        "total" => $labels,
                        "page" => $page,
                        "max" => $max
                    ))
            );

            $mapper->outputPDFtoFile(Zend_Registry::get("target_path") . "/images/uploads/qrcode/" . $file_name);

            $this->view->file = $file_name;
            $this->view->page = $page;
            $this->view->so_number = $so_number;
            $this->view->sku = $sku;
            $this->view->amazon = $qr_code;
            $this->view->total = $labels;
            $this->view->max = $max;
            $this->view->data = $data;
        } else {
            $this->view->form = $form;
            $this->view->data = NULL;
        }
    }

    // View\Edit Label settings CASE QTY\Weight
    public function settingsAction() {
        $this->view->title = "Label Settings";

        $request = $this->getRequest();
        $pei_mapper = new Atlas_Model_ProductExtraInfoMapper();
        $this->view->error = "";

        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            $pei_mapper->updateExtraInfo($form_data);
        }

        $product_info = $pei_mapper->getAllProductsShippingInfo()->query()->fetchAll();
        $form = new Atlas_Form_ShippingSettings($product_info);
        $this->view->paginator = $product_info;
        $this->view->form = $form;
    }
    
    //Display orders that require ASN labels
    public function asnshipmentsAction() {
        $this->view->title = "ASN Shipments";
        $sg_mapper  =   New Atlas_Model_SgOrdersMapper();
        $asnlist    =   $sg_mapper->buildASNOreders();
        $this->view->asnheader  =   $asnlist;
    }
    
    //Generate Zebra labels - Create electronic INVOICE/SHIPPMENT docs to be transferred VIA SFTP server
    //Send Labels to conveyor to get ready to be printed
    //Genarate unique shipping serial sequence for each order
    public function asnshipmentAction() {
        $this->view->title = "ASN Shipment";
        $request    =   $this->getRequest();
        $so_number  =   $request->getParam("orderno", 0);
        $header_mapper  =   new Atlas_Model_AsnHeaderMapper();        
        $sg_mapper      =   new Atlas_Model_SgOrdersMapper();
        $m3_mapper      =   new Atlas_Model_Inform3jiipicklist(); 
        $pl_mapper      =   new Atlas_Model_PrintingLabelsMapper();
        $functions      =   new Utility_Functions();
        $user_id        =   Zend_Registry::get("user_id");
        if($so_number != 0 || $request->isPost()){
            if ($request->isPost()){
                $form_data      =   Utility_Filter_DBSafe::clean($request->getPost());
                $so_number      =   $form_data['order_no'];
            }
            try{               
                $headerdata     =   $header_mapper->buildOrder($so_number);
                $pei_mapper     =   new Atlas_Model_ProductExtraInfoMapper();
                $pei_qty        =   $pei_mapper->buildCaseQtys();
                $orderdata      =   $m3_mapper->orderLotDataNew($so_number);
                if(count($orderdata) == 0){
                    Utility_FlashMessenger::addMessage('<div class="error">Order No Not Found.</div>');
                    return $this->_redirect("/shipping/asnshipment");
                }
                $items_desc     =   $sg_mapper->buildItemsDesc($orderdata);
                $cust_no        =   $orderdata[0]['cust_no'];
                $this->view->items_desc =   $items_desc;
                $this->view->pei_qty    =   $pei_qty;
                $this->view->headerdata  =   $headerdata;
            }catch( Exception $e ) {
                    Utility_FlashMessenger::addMessage('<div class="error">'.$e->getMessage().'</div>');
                    return $this->_redirect("/shipping/asnshipment");
            }

            //Build Order Information
            $sps_orderdata  =   $sg_mapper->buildOrderData($so_number);
            $original_data  =   $functions->buildXmlOrderFile($sps_orderdata['order_file'],'SPS','order');
            $line_sequence  =   $sg_mapper->buildLinesSequenceDup($original_data);
            $infor_address  =   $m3_mapper->buildInforCustInfo($cust_no);
            $address_info   =   $sg_mapper->buildXmlShippingAddress($original_data['Order']['Header']['Address'],$infor_address);
            $order_id       =   (isset($sps_orderdata['order_id'])) ? $sps_orderdata['order_id'] : 1;
            $partner_code   =   (isset($sps_orderdata['partner_code'])) ? $sps_orderdata['partner_code'] : "080";
            $this->view->line_sequence  =   $line_sequence;
            $this->view->address_info   =   $address_info;
            $this->view->infor_address  =   $infor_address;
            $this->view->order_no       =   $so_number;
            $this->view->sps_data       =   $sps_orderdata;
            $this->view->orderdata      =   $orderdata;
        }
        if ($request->isPost()) {
            $form_data['partner_code'] = $partner_code;
            if(isset($form_data['save'])){
                try{
                    $form_data['order_id']  =   $order_id;
                    $form_data['header_id'] =   $header_mapper->processForm($form_data);
                    $lines_mapper   =   new Atlas_Model_AsnLinesMapper(); 
                    $lines_mapper->processForm($form_data,$orderdata,$original_data, $address_info);
                    Utility_FlashMessenger::addMessage('<div class="success">Labels Have been Created.</div>');
                }catch( Exception $e ) {
                    Utility_FlashMessenger::addMessage('<div class="error">'.$e->getMessage().'</div>');
                }
                return $this->_redirect("/shipping/asnshipment/orderno/".$so_number);
            }else if (isset($form_data['XML_sep'])){
                if (isset($form_data['tosend'])){
                    //Build labels to create to populate XML item lines
                    $printing_lables    =   $pl_mapper->buildOrderChoosedLabels($form_data['order_no'],$form_data['tosend']);
                    $order_info     =  $header_mapper->reBuildOrderInfo($so_number, $form_data,$header_mapper->buildOrderDetails($so_number));

                    $build_xml_data =   $sg_mapper->buildXmlShipment1($original_data, $order_info,$printing_lables,$cust_no);
                    $xml_data       =   Utility_Functions::xml_entities($build_xml_data);
                    $file_name      =   'SH_'.$order_id.'_'.date("YmdHisv").'.xml';
                    $upload_file    =   Zend_Registry::get('target_path').'/uploads/sps_xml/'.$file_name;

                    file_put_contents($upload_file, $xml_data);

                    Utility_FlashMessenger::addMessage('<div class="success">File was created.</div>');
                } else {
                    Utility_FlashMessenger::addMessage('<div class="error">One or more checkbox should be checked in Check ASN column</div>');
                }
                return $this->_redirect('/shipping/asnshipment/orderno/'.$so_number);
            }else if (isset($form_data['XML'])){
                //Build labels to create to populate XML item lines
                $printing_lables    =   $pl_mapper->buildOrderLabels($form_data['order_no']);

                //Save Track No if exist
                $lines_mapper   =   new Atlas_Model_AsnLinesMapper();
                $lines_mapper->updateItemTrackNo($form_data);

                //Save and Build New Form Information To Submit To ASN
                $order_info     =   $header_mapper->reBuildHeaderInfo($form_data,$so_number);

                //Parse Data and Create XML File
                $build_xml_data =   $sg_mapper->buildXmlShipment1($original_data, $order_info,$printing_lables,$cust_no);
                $xml_data       =   Utility_Functions::xml_entities($build_xml_data);
                $file_name      =   'SH_'.$order_id.'_'.date("YmdHisv").'.xml';
                $upload_file    =   Zend_Registry::get('target_path').'/uploads/sps_xml/'.$file_name;
                file_put_contents($upload_file, $xml_data);
                
                //Push XML File to SFTP Server
                $sftp_conn      =   new Utility_SFTPConnectionSps();
                $sftp           =   $sftp_conn->buildSftpConnectionSps('in');
                $sftp->put($file_name,$upload_file, NET_SFTP_LOCAL_FILE);
                
                //Save Action Taken
                $sg_action  =   new Atlas_Model_SgActions();
                $sg_action->setAction('shipment')->setOrder_id($order_id)->setOrder_no($so_number)->setFile($file_name)->setUser_id($user_id)->setAction_datetime(date("Y-m-d H:i:s"));
                $sg_action_mapper   =   new Atlas_Model_SgActionsMapper();
                $sg_action_mapper->save($sg_action);
                
                Utility_FlashMessenger::addMessage('<div class="success">ASN has been sent.</div>');
                return $this->_redirect('/shipping/asnshipment/orderno/'.$so_number);
            }else if (isset($form_data['CASI'])){
                //Build Labels To create ZPL Files
                $printing_lables    =   $pl_mapper->buildOrderLabels($form_data['order_no']);
                $check_upload       =   $pl_mapper->createZPLFiles($printing_lables, $so_number, $partner_code);
                $msg    =   ($check_upload) ? '<div class="success">Order Has Been Successfully Pushed To CASI</div>':'<div class="error">Failed to upload the file, please contact support or try again..</div>';
                Utility_FlashMessenger::addMessage($msg);
                return $this->_redirect('/shipping/asnshipment/orderno/'.$so_number);
            }else if (isset($form_data['PRINT'])){
                //Download Zpl.txt
                $file = Zend_Registry::get("target_path").'/uploads/barcodes/'.$so_number.'_TXT/'.$so_number.'_ZPL.txt';
                header('Content-Type: application/force-download');
                header('Content-Disposition: attachment; filename='.basename($file));
                header('Content-Length: ' . filesize($file));
                readfile($file);
                die();
            }else if (isset($form_data['download'])){
                $printing_lables    =   $pl_mapper->buildOrderLabels($form_data['order_no']);
                $file_path          =   $pl_mapper->createCsvFileDownload($printing_lables, $so_number);
                //Download CSV
                header('Content-Type: application/force-download');
                header('Content-Disposition: attachment; filename='.basename($file_path));
                header('Content-Length: ' . filesize($file_path));
                readfile($file_path);
                die();
            }else if (isset($form_data['download_zip'])){                
                //Build labels to create files for ZIP
                $printing_lables    =   $pl_mapper->buildOrderLabels($form_data['order_no']);
                $file_name = $pl_mapper->createZIP($so_number, $printing_lables, $partner_code);
                
                //Download ZIP 
                header('Pragma: public');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Cache-Control: private', false);
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: ' . filesize($file_name));
                header('Connection: close');
                readfile($file_name);
                die();
            }else if (isset($form_data['invoice'])){
                //Build Order Info and Create XML string
                $inv_data       =   $m3_mapper->buildOrderInvoice($so_number);
                $inv_lines      =   $m3_mapper->buildOrderInvoicelines($so_number);
                $build_xml_data =   $sg_mapper->buildXmlInvoice($original_data,$inv_data,$inv_lines);
                $xml_data       =   Utility_Functions::xml_entities($build_xml_data);
                
                //Upload File to SFTP
                $file_name      =   'IN_'.$order_id.'_'.date("YmdHisv").'.xml';
                $upload_file    =   Zend_Registry::get('target_path').'/uploads/sps_xml/'.$file_name;
                file_put_contents($upload_file, $xml_data);
                
                //Send File To SFTP Server
                $sftp_conn  =   new Utility_SFTPConnectionSps();
                $sftp       =   $sftp_conn->buildSftpConnectionSps('in');
                $sftp->put($file_name,$upload_file, NET_SFTP_LOCAL_FILE);
                
                //Save Action Taken
                $sg_action  =   new Atlas_Model_SgActions();
                $sg_action->setAction('invoice')->setOrder_no($so_number)->setOrder_id($order_id)->setFile($file_name)->setUser_id($user_id)->setAction_datetime(date("Y-m-d H:i:s"));
                $sg_action_mapper   =   new Atlas_Model_SgActionsMapper();
                $sg_action_mapper->save($sg_action);
                
                //Return Success Message
                Utility_FlashMessenger::addMessage('<div class="success">Invoice has been sent.</div>');
                return $this->_redirect('/shipping/asnshipment/orderno/'.$so_number);
            }else if (isset($form_data['reprint'])){
                //Create File
                $pl_mapper  =   New Atlas_Model_PrintingLabelsMapper();
                $file_path  =   $pl_mapper->buildOrderItemLabel($form_data);
                
                //Download File
                header('Content-Type: application/force-download');
                header('Content-Disposition: attachment; filename='.basename($file_path));
                header('Content-Length: ' . filesize($file_path));
                readfile($file_path);
                die();
            }else if (isset($form_data['complete'])){
                $sg_order = $sg_mapper->find($order_id);
                $sg_order->setComplete(1);
                $sg_mapper->save($sg_order);
                Utility_FlashMessenger::addMessage('<div class="success">Order Has Been Set To Complete.</div>');
                return $this->_redirect('/shipping/asnshipment/orderno/'.$so_number);
            }else if (isset($form_data['delete_labels'])){
                //Remove Tables Records
                $lines_mapper   =   new Atlas_Model_AsnLinesMapper();
                $pl_mapper->getDbTable()->delete("bol='".$so_number."'");
                $header_mapper->getDbTable()->delete("order_no='".$so_number."'");
                $lines_mapper->getDbTable()->delete("order_no='".$so_number."'");
                $shippinng_ser = new Atlas_Model_ShippingSerialMapper();
                $shippinng_ser->getDbTable()->delete("order_id='".$order_id."'");
                
                //Delete Related Folders And Files
                $pl_mapper->recursiveRemoveDirectory(Zend_Registry::get("target_path").'/uploads/barcodes/'.$so_number.'_TXT/');
                $pl_mapper->recursiveRemoveDirectory(Zend_Registry::get("target_path").'/uploads/barcodes/'.$so_number.'_ZPL/');
                unlink(Zend_Registry::get("target_path")."/uploads/csv/".$so_number."_ASNORDERS.csv");
                unlink(Zend_Registry::get("target_path")."/uploads/csv/".$so_number."_ORDERS.csv");
                //Return Success Message
                Utility_FlashMessenger::addMessage('<div class="success">Labels Have Been Deleted.</div>');
                return $this->_redirect('/shipping/asnshipment/orderno/'.$form_data['order_no']);
            }else if(isset($form_data['generate'])){
                Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                return $this->_redirect('/shipping/asnshipment/orderno/'.$form_data['order_no']);
            }else{
                Utility_FlashMessenger::addMessage('<div class="error">Unkown Action Taken</div>');
                return $this->_redirect('/shipping/asnshipment/orderno/'.$form_data['order_no']);   
            }
        }
    }
    
    //Edit Line Info like LOT NO, EXP DATE to reprint labels
    public function edititemAction (){        
        $request    =   $this->getRequest();
        $order_no   =   $request->getParam("orderno", 0);
        $line       =   $request->getParam("line", 0);
        $exp_date   =   $request->getParam("exp_date", 0);
        $lot_no     =   $request->getParam("lot_no", 0);
        $partner_code=  $request->getParam("p_code", 0);
        $pl_mapper  =   New Atlas_Model_PrintingLabelsMapper();
        $file       =   $pl_mapper->updateOrderItem($order_no,$line,$exp_date,$lot_no,$partner_code);
        
        $asnline_mapper = New Atlas_Model_AsnLinesMapper();
        $asnline_mapper->updateOrderItem($order_no,$line,$exp_date,$lot_no);

        header('Content-Type: application/force-download');
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Content-Length: ' . filesize($file));
        readfile($file);
        die();
    }
    
    //Uploading labels to the Conveyor to get ready to be printed
    public function indexAction() {
        $this->view->title = "CASI Upload";
        $mapper = new Atlas_Model_PrintingLabelsMapper();
        $orders = $mapper->buildLoadedOrders();
        $this->view->orders = $orders;
    }

    //Creating ZPL files with zebra commands for big size orders
    public function uploadAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();
        $request = $this->getRequest();
        $mapper = new Atlas_Model_PrintingLabelsMapper();
        try {
            if ($request->isPost()) {
                $form_data = Utility_Filter_DBSafe::clean($request->getPost());
                if ($form_data['type'] == 'iherb') {
                    $mapper->processiherbasn($form_data);
                } else if ($form_data['type'] == 'amazon'){
                    $mapper->processUpload($form_data);
                }
            } else {
                throw new Exception("No data provided.");
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            die();
        }
    }

    //Delete Order From Conveyor and FTP Server
    public function deleteorderAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();
        // get the parameters
        $request = $this->getRequest();
        $order_no = $request->getParam("id", 0);

        // ensure the proper variables are present
        if (empty($order_no)) {
            return $this->_redirect("/shipping/index");
        }

        // attempt to activate the user's account
        $mapper = new Atlas_Model_PrintingLabelsMapper();
        $data = $mapper->deleteOrder($order_no);

        Utility_FlashMessenger::addMessage('<div class="success">Order ' . $data . ' has been successfully deleted.</div>');
        return $this->_redirect("/shipping/index");
    }
    
    //Generate UPC labels to be scanned in case of shortage 
    public function upclabelAction() {
        $this->view->title = "UPC Labels";
        // disable layout as this action is not for viewing
        $request = $this->getRequest();
        if ($request->isPost()) {
            $pei_mapper = new Atlas_Model_ProductExtraInfoMapper();
            $form_data = $request->getPost();
            $item_code = $form_data['item_code'];
            $upc = $form_data['upc'];
            $copies = (int) $form_data['copy'];


            if ($item_code != '')
                $upc = $pei_mapper->buildUpc($item_code);
            else if ($upc != '')
                $item_code = $pei_mapper->buildItemCode($upc);


            if ($copies != '' && $item_code != '' && $upc != '' && $copies > 0) {
                $cmds = '${  ';
                for ($i = 1; $i <= $copies; $i++) {
                    $cmds .= "
^XA
^FO570,550^AQR,50,50^FD$item_code^FS
^FO400,350^BY5^BCR,130,Y,N,N,A^FD$upc^FS
^XZ
";
                }
                $cmds .= " }$ ";
                $file_name = 'upc_labels_' . time() . '.txt';
                $path = Zend_Registry::get('target_path').'/uploads/upc_labels/';
                file_put_contents($path . $file_name, $cmds);
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . basename($path . $file_name));
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($path . $file_name));
                readfile($path . $file_name);
                unlink($path . $file_name);
                exit;
            } else {
                Utility_FlashMessenger::addMessage('<div class="error">Please enter missing fields.</div>');
                return $this->_redirect('/shipping/upclabel');
            }
        }
    }

    //Display Amazon Item codes with relevant products
    public function asinsAction() {
        $this->view->title = "ASIN Codes";
        $plb_mapper = new Atlas_Model_PrintingLabelsBuyerMapper();
        $codes = $plb_mapper->buildAll();
        $this->view->codes = $codes;
    }

    //Add\Edit Amazon Identification codes
    public function asinAction() {
        $this->view->title = "ASIN Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $entry_id = (int) $request->getParam("id", 0);
        // set up the mappers and forms
        $mapper = new Atlas_Model_PrintingLabelsBuyerMapper();
        $form = new Atlas_Form_PrintingLabelsBuyer();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            if ($form->isValid($form_data)) {
                if ($mapper->checkAsin($form_data)) {
                    try {
                        $mapper->processForm($form_data);
                        Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                        return $this->_redirect("/shipping/asins");
                    } catch (Exception $e) {
                        Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                        return $this->_redirect("/shipping/asins");
                    }
                } else {
                    Utility_FlashMessenger::addMessage('<div class="error">This ASIN already ' . $form_data['asin'] . ' exists.</div>');
                    return $this->_redirect("/shipping/asins");
                }
            } else {
                $message = Utility_Error::buildErrors($form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($entry_id != 0) {
            // try to get the selected unit data and
            // redirect to the unit list on error 
            try {
                $entry = $mapper->find($entry_id);
                $data = Utility_Filter_DBSafe::revert($entry->toArray());
                $form->populate(Utility_Filter_DBSafe::revert($entry->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/shipping/asins");
            }
        }

        // pass the data to the view
        $this->view->form = $form;
        $this->view->data = $data;
    }
    
    public function __call($methodName, $args) {
        $uri = $this->getRequest()->getRequestUri();
        $admin = Zend_Registry::get('admin');
        // log the failed access
        $mapper = new Atlas_Model_AccessLogMapper();
        $log = new Atlas_Model_AccessLog();
        $log->setTimestamp(date("Y-m-d H:i:s", time()))
                ->setUser_id(Zend_Registry::get('user_id'))
                ->setIp_address(Zend_Registry::get("ip_add"))
                ->setMessage("User attempted to access: " . $uri);
        $mapper->save($log);

        Utility_FlashMessenger::addMessage(
                '<div class="error">The page you requested doesn\'t exist. This attempt has been logged and if the attempt resembles an intrusion you will be contacted by your supervisors. However, if you feel you got this message in error please contact ' . $admin['email'] . '</div>'
        );
        return $this->_redirect('/dashboard');
    }

}

?>