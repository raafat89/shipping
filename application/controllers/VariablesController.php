<?php

class VariablesController extends Zend_Controller_Action {

    public function init() {
        // set the CSS documents for the website
        $this->view->css_docs = array(
            Zend_Registry::get("global_css"),
            "/css/smoothness/jquery-ui-1.8.17.custom.css",
            "/css/smoothness/jalerts.css",
            "/css/variables.css"
        );

        // set the JS documents for the website
        $this->view->js_docs = array(
            "/js/jquery.1.6.2.js",
            "/js/jquery-ui-1.8.17.custom.min.js",
            "/js/jquery.jBreadCrumb.1.1.js",
            "/js/jquery.easing.1.3.js",
            "/js/jquery.dataTables.min.js",
            "/js/jalerts.js",
            "/js/global.js",
            "/js/variables.js");

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
            return $this->_redirect(Zend_Registry::get('full_url') . "/login");
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

    public function allergensAction() {
        $this->view->title = "Allergens List";

        // set up the mappers and the required Allergens list
        $mapper = new Atlas_Model_AllergensMapper();
        $entries = $mapper->buildAll();

        // pass the data to the view
        $this->view->entries = $entries;
    }

    public function allergenAction() {
        $this->view->title = "Allergens Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $entry_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $mapper = new Atlas_Model_AllergensMapper();
        $form = new Atlas_Form_Allergens();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            if ($form->isValid($form_data)) {
                try {
                    $mapper->processForm($form_data);
                    Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                    return $this->_redirect("/variables/allergens");
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/variables/allergens");
                }
            } else {
                $message = Utility_Error::buildErrors($form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($entry_id != 0) {
            // try to get the selected unit data and
            // redirect to the Allergens list on error 
            try {
                $entry = $mapper->find($entry_id);
                $form->populate(Utility_Filter_DBSafe::revert($entry->toArray()));
            } catch (Exception $e) {
                Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                return $this->_redirect("/variables/allergens");
            }
        }
        // pass the data to the view
        $this->view->form = $form;
    }

    public function bottleclosuresAction() {
        $this->view->title = "Bottle Closure List";

        // set up the mappers and the required Bottle Closure list
        $mapper = new Atlas_Model_BottleClosuresMapper();
        $entries = $mapper->buildAll();

        // pass the data to the view
        $this->view->entries = $entries;
    }

    public function bottleclosureAction() {
        $this->view->title = "Bottle Closure Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $entry_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $mapper = new Atlas_Model_BottleClosuresMapper();
        $form = new Atlas_Form_BottleClosures();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($form->isValid($form_data)) {
                try {
                    $mapper->processForm($form_data);

                    // clear the current BOTTLE_CLOSURES cache
                    $cache = Zend_Registry::get('cache_handler');
                    $cache->remove("BOTTLE_CLOSURES");

                    Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                    return $this->_redirect("/variables/bottleclosures");
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/variables/bottleclosures");
                }
            } else {
                $message = Utility_Error::buildErrors($form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($entry_id != 0) {
            // try to get the selected unit data and
            // redirect to the BOTTLE_CLOSURES list on error 
            try {
                $entry = $mapper->find($entry_id);
                $form->populate(Utility_Filter_DBSafe::revert($entry->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/variables/bottleclosures");
            }
        }

        // pass the data to the view
        $this->view->form = $form;
    }

    public function bottlefillersAction() {
        $this->view->title = "Bottle Filler List";

        // set up the mappers and the required Bottle Fillers list
        $mapper = new Atlas_Model_BottleFillersMapper();
        $entries = $mapper->selectAll();

        // initialize the paginator
        $adapter = new Zend_Paginator_Adapter_DbSelect($entries);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setItemCountPerPage(25)
                ->setCurrentPageNumber((int) $this->getRequest()->getParam('page', 1));
        Zend_Paginator::setDefaultScrollingStyle('Elastic');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('/partials/paginators/pagination.phtml');

        // pass the data to the view
        $this->view->entries = $paginator;
        $this->view->page = $this->getRequest()->getParam('page', 1);
    }

    public function bottlefillerAction() {
        $this->view->title = "Bottle Filler Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $entry_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $mapper = new Atlas_Model_BottleFillersMapper();
        $form = new Atlas_Form_BottleFillers();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($form->isValid($form_data)) {
                try {
                    $mapper->processForm($form_data);

                    // clear the current Bottle Filler cache
                    $cache = Zend_Registry::get('cache_handler');
                    $cache->remove("BOTTLE_FILLERS");

                    Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                    return $this->_redirect("/variables/bottlefillers");
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/variables/bottlefillers");
                }
            } else {
                $message = Utility_Error::buildErrors($form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($entry_id != 0) {
            // try to get the selected unit data and
            // redirect to the Bottle Filler list on error 
            try {
                $entry = $mapper->find($entry_id);
                $form->populate(Utility_Filter_DBSafe::revert($entry->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/variables/bottlefillers");
            }
        }

        // pass the data to the view
        $this->view->form = $form;
    }

    public function bottlesizesAction() {
        $this->view->title = "Bottle Size List";

        // set up the mappers and the required Bottle Sizes list
        $bottlesize_mapper = new Atlas_Model_BottleSizesMapper();
        $bsizes = $bottlesize_mapper->buildAll();

        // pass the data to the view
        $this->view->entries = $bsizes;
    }

    public function bottlesizeAction() {
        $this->view->title = "Bottle Size Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $bottlesize_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $bottlesize_mapper = new Atlas_Model_BottleSizesMapper();
        $bottlesize_form = new Atlas_Form_BottleSizes();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($bottlesize_form->isValid($form_data)) {
                try {
                    $bottlesize_mapper->processForm($form_data);

                    // clear the current Bottle Size cache
                    $cache = Zend_Registry::get('cache_handler');
                    $cache->remove("BOTTLE_SIZES");

                    Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                    return $this->_redirect("/variables/bottlesizes");
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/variables/bottlesizes");
                }
            } else {
                $message = Utility_Error::buildErrors($bottlesize_form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($bottlesize_id != 0) {
            // try to get the selected unit data and
            // redirect to the Bottle Size list on error 
            try {
                $bsize = $bottlesize_mapper->find($bottlesize_id);
                $bottlesize_form->populate(Utility_Filter_DBSafe::revert($bsize->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/variables/bottlesizes");
            }
        }

        // pass the data to the view
        $this->view->form = $bottlesize_form;
    }

    public function bottletypesAction() {
        $this->view->title = "Bottle Type List";

        // set up the mappers and the required Bottle Type list
        $bottletype_mapper = new Atlas_Model_BottleTypesMapper();
        $btypes = $bottletype_mapper->buildAll();

        // pass the data to the view
        $this->view->entries = $btypes;
    }

    public function bottletypeAction() {
        $this->view->title = "Bottle Type Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $bottletype_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $bottletype_mapper = new Atlas_Model_BottleTypesMapper();
        $bottletype_form = new Atlas_Form_BottleTypes();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($bottletype_form->isValid($form_data)) {
                try {
                    $bottletype_mapper->processForm($form_data);

                    // clear the current Bottle Type cache
                    $cache = Zend_Registry::get('cache_handler');
                    $cache->remove("BOTTLE_TYPES");

                    Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                    return $this->_redirect("/variables/bottletypes");
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/variables/bottletypes");
                }
            } else {
                $message = Utility_Error::buildErrors($bottletype_form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($bottletype_id != 0) {
            // try to get the selected Bottle Type data and
            // redirect to the unit list on error 
            try {
                $btype = $bottletype_mapper->find($bottletype_id);
                $bottletype_form->populate(Utility_Filter_DBSafe::revert($btype->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/variables/bottletypes");
            }
        }

        // pass the data to the view
        $this->view->form = $bottletype_form;
    }

    public function bulksizesAction() {
        $this->view->title = "Bulk Size List";

        // set up the mappers and the required Bulk Sizes list
        $bulksize_mapper = new Atlas_Model_BulkSizesMapper();
        $bsizes = $bulksize_mapper->buildAll();

        // pass the data to the view
        $this->view->bsizes = $bsizes;
    }

    public function bulksizeAction() {
        $this->view->title = "Bulk Size Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $bulksize_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $bulksize_mapper = new Atlas_Model_BulkSizesMapper();
        $bulksize_form = new Atlas_Form_BulkSizes();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($bulksize_form->isValid($form_data)) {
                $bulksize_mapper->processForm($form_data);

                // clear the current BULK SIZES cache
                $cache = Zend_Registry::get('cache_handler');
                $cache->remove("BULK_SIZES");

                Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                return $this->_redirect("/variables/bulksizes");
            } else {
                $message = Utility_Error::buildErrors($bulksize_form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($bulksize_id != 0) {
            // try to get the selected unit data and
            // redirect to the BULK SIZES list on error 
            try {
                $bsize = $bulksize_mapper->find($bulksize_id);
                $bulksize_form->populate(Utility_Filter_DBSafe::revert($bsize->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/variables/bulksizes");
            }
        }

        // pass the data to the view
        $this->view->form = $bulksize_form;
    }

    public function captabpackagesAction() {
        $this->view->title = "Cap/Tab Packages List";
        // setup the request object

        // set up the mappers and the required Cap/Tab Packages list
        $bottlesize_mapper = new Atlas_Model_CaptabPackagesMapper();
        $bsizes = $bottlesize_mapper->buildAll();

        // pass the data to the view
        $this->view->entries = $bsizes;
    }

    public function captabpackageAction() {
        $this->view->title = "Cap/Tab Package Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $capTab_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $CaptabPackages_mapper = new Atlas_Model_CaptabPackagesMapper();
        $captab_form = new Atlas_Form_CaptabPackages();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($captab_form->isValid($form_data)) {
                try {
                    $CaptabPackages_mapper->processForm($form_data);

                    // clear the current Cap/Tab Package cache
                    $cache = Zend_Registry::get('cache_handler');
                    $cache->remove("CAPTAB_PACKAGES");

                    Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                    return $this->_redirect("/variables/captabpackages");
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/variables/captabpackages");
                }
            } else {
                $message = Utility_Error::buildErrors($captab_form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($capTab_id != 0) {
            // try to get the selected Cap/Tab Package data and
            // redirect to the Cap/Tab Package list on error 
            try {
                $capTab = $CaptabPackages_mapper->find($capTab_id);
                $values = Utility_Filter_DBSafe::revert($capTab->toArray());

                $captab_form->populate($values);
            } catch (Exception $e) {
                return $this->_redirect("/variables/captabpackages");
            }
        }
        // pass the data to the view
        $mapper = new Atlas_Model_BulkSizesMapper();
        $sizes = $mapper->fetch($mapper->selectAll());

        $this->view->values = $values;
        $this->view->sizes = $sizes;
        $this->view->form = $captab_form;
    }

    public function colorsAction() {
        $this->view->title = "Colors List";
        // setup the request object
        $request = $this->getRequest();
        // check for post and redirect on data recieved
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            $search_field = str_replace(" ", "_@_", $form_data['field']);
            $search_field = str_replace("/", "_*_", $search_field);
            $this->_redirect("/variables/colors/field/" . $search_field);
        }

        // get the search string if it exists
        $search_field = Utility_Filter_DBSafe::clean($request->getParam("field", ""));
        $search_field = str_replace("_@_", " ", $search_field);
        $search_field = str_replace("_*_", "/", $search_field);
        $this->view->search = new Atlas_Form_GeneralSearch();

        // set up the mappers and the required units list
        $bottlesize_mapper = new Atlas_Model_ColorsMapper();
        $bsizes = $bottlesize_mapper->selectAll($search_field);

        // initialize the paginator
        $adapter = new Zend_Paginator_Adapter_DbSelect($bsizes);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setItemCountPerPage(25)
                ->setCurrentPageNumber((int) $this->getRequest()->getParam('page', 1));
        Zend_Paginator::setDefaultScrollingStyle('Elastic');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('/partials/paginators/pagination.phtml');

        // pass the data to the view
        $this->view->entries = $paginator;
        $this->view->page = $this->getRequest()->getParam('page', 1);
    }

    public function colorAction() {
        $this->view->title = "Color Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $entry_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $mapper = new Atlas_Model_ColorsMapper();
        $form = new Atlas_Form_Colors();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($form->isValid($form_data)) {
                try {
                    $mapper->processForm($form_data);

                    // clear the current COLORS cache
                    $cache = Zend_Registry::get('cache_handler');
                    $cache->remove("COLORS");

                    Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                    return $this->_redirect("/variables/colors");
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/variables/colors");
                }
            } else {
                $message = Utility_Error::buildErrors($form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($entry_id != 0) {
            // try to get the selected COLORS data and
            // redirect to the COLORS list on error 
            try {
                $entry = $mapper->find($entry_id);
                $form->populate(Utility_Filter_DBSafe::revert($entry->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/variables/colors");
            }
        }

        // pass the data to the view
        $this->view->form = $form;
    }

    public function desiccanttypesAction() {
        $this->view->title = "Desiccant Type List";

        // set up the mappers and the required Desiccant Type list
        $mapper = new Atlas_Model_DesiccantTypesMapper();
        $entries = $mapper->selectAll();

        // initialize the paginator
        $adapter = new Zend_Paginator_Adapter_DbSelect($entries);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setItemCountPerPage(25)
                ->setCurrentPageNumber((int) $this->getRequest()->getParam('page', 1));
        Zend_Paginator::setDefaultScrollingStyle('Elastic');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('/partials/paginators/pagination.phtml');

        // pass the data to the view
        $this->view->entries = $paginator;
        $this->view->page = $this->getRequest()->getParam('page', 1);
    }

    public function desiccanttypeAction() {
        $this->view->title = "Desiccant Type Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $entry_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $mapper = new Atlas_Model_DesiccantTypesMapper();
        $form = new Atlas_Form_DesiccantTypes();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($form->isValid($form_data)) {
                try {
                    $mapper->processForm($form_data);

                    // clear the current Desiccant Type cache
                    $cache = Zend_Registry::get('cache_handler');
                    $cache->remove("DESICCANT_TYPES");

                    Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                    return $this->_redirect("/variables/desiccanttypes");
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/variables/desiccanttypes");
                }
            } else {
                $message = Utility_Error::buildErrors($form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($entry_id != 0) {
            // try to get the selected Desiccant Type data and
            // redirect to the Desiccant Type list on error 
            try {
                $entry = $mapper->find($entry_id);
                $form->populate(Utility_Filter_DBSafe::revert($entry->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/variables/desiccanttypes");
            }
        }

        // pass the data to the view
        $this->view->form = $form;
    }

    public function disintegrationtypesAction() {
        $this->view->title = "Disintegration Types List";

        // set up the mappers and the required units list
        $mapper = new Atlas_Model_DisintegrationTypesMapper();
        $entries = $mapper->selectAll();

        // initialize the paginator
        $adapter = new Zend_Paginator_Adapter_DbSelect($entries);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setItemCountPerPage(25)
                ->setCurrentPageNumber((int) $this->getRequest()->getParam('page', 1));
        Zend_Paginator::setDefaultScrollingStyle('Elastic');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('/partials/paginators/pagination.phtml');

        // pass the data to the view
        $this->view->entries = $paginator;
        $this->view->page = $this->getRequest()->getParam('page', 1);
    }

    public function disintegrationtypeAction() {
        $this->view->title = "Disintegration Types Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $entry_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $mapper = new Atlas_Model_DisintegrationTypesMapper();
        $form = new Atlas_Form_DisintegrationTypes();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($form_data['disintegration_type'] != "") {
                try {
                    $mapper->processForm($form_data);

                    // clear the current DISINTEGRATION_TYPES cache
                    $cache = Zend_Registry::get('cache_handler');
                    $cache->remove("DISINTEGRATION_TYPES");

                    Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                    return $this->_redirect("/variables/disintegrationtypes");
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/variables/disintegrationtypes");
                }
            } else {
                Utility_FlashMessenger::addMessage('<div class="error">You can\'t submit a blank form.</div>');
                return $this->_redirect("/variables/disintegrationtype");
            }
        } else if ($entry_id != 0) {
            // try to get the selected DISINTEGRATION_TYPES data and
            // redirect to the DISINTEGRATION_TYPES list on error 
            try {
                $entry = $mapper->find($entry_id);
                $form->populate(Utility_Filter_DBSafe::revert($entry->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/variables/disintegrationtypes");
            }
        }

        // pass the data to the view
        $this->view->form = $form;
    }

    public function jiimethodsAction() {
        $this->view->title = "JII Method List";

        $page = (int) $this->getRequest()->getParam('page', 1);

        // set up the mappers and the required test method list
        $mapper = new Atlas_Model_JiiMethodsMapper();
        $data = $mapper->selectAll();

        // initialize the paginator
        $adapter = new Zend_Paginator_Adapter_DbSelect($data);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setItemCountPerPage(25)
                ->setCurrentPageNumber($page);
        Zend_Paginator::setDefaultScrollingStyle('Elastic');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('/partials/paginators/pagination.phtml');

        // pass the data to the view
        $this->view->data = $paginator;
        $this->view->page = $page;
    }

    public function jiimethodAction() {
        $this->view->title = "JII Methods Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $id = (int) $request->getParam("id", 0);
        $page = (int) $request->getParam("page", 1);

        // set up the mappers and forms
        $mapper = new Atlas_Model_JiiMethodsMapper();
        $form = new Atlas_Form_JiiMethods();

        try {
            // process or initialize the form
            if ($request->isPost()) {
                $form_data = Utility_Filter_DBSafe::clean($request->getPost());

                if ($form->isValid($form_data)) {
                    $mapper->processForm($form_data);

                    // clear the current TEST METHODS cache
                    $cache = Zend_Registry::get('cache_handler');
                    $cache->remove("JII_TEST_METHODS");

                    Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed.</div>');
                    return $this->_redirect("/variables/jiimethods/page/" . $page);
                } else {
                    $message = Utility_Error::buildErrors($form->getMessages());
                    $this->view->messages = $message;
                }
            } else if ($id != 0) {
                $entry = new Atlas_Model_JiiMethods();
                $entry = $mapper->find($id);
                $form->populate(Utility_Filter_DBSafe::revert($entry->toArray()));
            }
        } catch (Exception $e) {
            Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
            return $this->_redirect("/variables/jiimethods/page/" . $page);
        }

        // pass the data to the view
        $this->view->form = $form;
        $this->view->page = $page;
    }

    public function jiimActivationAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request = $this->getRequest();
        $method_id = (int) $request->getParam("id", 0);
        $status = (int) $request->getParam("s", 1);
        $page = (int) $request->getParam("page", 1);

        // ensure the proper variables are present
        if ($method_id <= 0) {
            return $this->_redirect("/variables/jiimethods/page/" . $page);
        }

        try {
            $mapper = new Atlas_Model_JiiMethodsMapper();
            if ($status <= 0) {
                $mapper->deactivateMethod($method_id);
            } else {
                $mapper->activateMethod($method_id);
            }

            // clear the current JII_TEST_METHODS cache
            $cache = Zend_Registry::get('cache_handler');
            $cache->remove("JII_TEST_METHODS");
        } catch (Exception $e) {
            Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
            return $this->_redirect("/variables/testmethods/page/" . $page);
        }

        Utility_FlashMessenger::addMessage('<div class="success">The selected JII Test Method was adjusted.</div>');
        return $this->_redirect("/variables/testmethods/page/" . $page);
    }

    public function labAction() {
        $this->view->title = "Lab Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $lab_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $lab_mapper = new Atlas_Model_LabsMapper();
        $lab_form = new Atlas_Form_Labs();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($lab_form->isValid($form_data)) {
                $lab_mapper->processForm($form_data);

                // clear the current LABS cache
                $cache = Zend_Registry::get('cache_handler');
                $cache->remove("LABS");

                Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                return $this->_redirect("/variables/labs");
            } else {
                $message = Utility_Error::buildErrors($lab_form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($lab_id != 0) {
            // try to get the selected lab data and
            // redirect to the lab list on error 
            try {
                $lab = new Atlas_Model_Labs();
                $lab = $lab_mapper->find($lab_id);
                $lab_form->populate(Utility_Filter_DBSafe::revert($lab->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/variables/labs");
            }
        }

        // pass the data to the view
        $this->view->form = $lab_form;
    }

    public function labsAction() {
        $this->view->title = "System Lab List";

        // set up the mappers and the required lab list
        $lab_mapper = new Atlas_Model_LabsMapper();
        $labs = $lab_mapper->buildAll();

        // pass the data to the view
        $this->view->labs = $labs;
    }

    public function labactivateAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request = $this->getRequest();
        $lab_id = (int) $request->getParam("id", 0);

        // ensure the proper variables are present
        if ($lab_id <= 0) {
            return $this->_redirect("/variables/labs/");
        }

        // attempt to activate the lab
        $mapper = new Atlas_Model_LabsMapper();
        $mapper->activateLab($lab_id);

        // clear the current LABS cache
        $cache = Zend_Registry::get('cache_handler');
        $cache->remove("LABS");

        Utility_FlashMessenger::addMessage('<div class="success">The selected Lab was successfully activated</div>');
        return $this->_redirect("/variables/labs/");
    }

    public function labdeactivateAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request = $this->getRequest();
        $lab_id = (int) $request->getParam("id", 0);

        // ensure the proper variables are present
        if ($lab_id <= 0) {
            return $this->_redirect("/variables/labs/");
        }

        // attempt to deactivate the lab
        $mapper = new Atlas_Model_LabsMapper();
        $mapper->deactivateLab($lab_id);

        // clear the current LABS cache
        $cache = Zend_Registry::get('cache_handler');
        $cache->remove("LABS");

        Utility_FlashMessenger::addMessage('<div class="success">The selected lab was successfully deactivated</div>');
        return $this->_redirect("/variables/labs/");
    }

    public function sleevingsAction() {
        $this->view->title = "Sleevings List";
        // setup the request object
        $request = $this->getRequest();
        // check for post and redirect on data recieved
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            $search_field = str_replace(" ", "_@_", $form_data['field']);
            $search_field = str_replace("/", "_*_", $search_field);
            $this->_redirect("/variables/sleevings/field/" . $search_field);
        }

        // get the search string if it exists
        $search_field = Utility_Filter_DBSafe::clean($request->getParam("field", ""));
        $search_field = str_replace("_@_", " ", $search_field);
        $search_field = str_replace("_*_", "/", $search_field);
        $this->view->search = new Atlas_Form_GeneralSearch();

        // set up the mappers and the required Sleevings list
        $bottlesize_mapper = new Atlas_Model_SleevingTypesMapper();
        $bsizes = $bottlesize_mapper->selectAll($search_field);

        // initialize the paginator
        $adapter = new Zend_Paginator_Adapter_DbSelect($bsizes);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setItemCountPerPage(25)
                ->setCurrentPageNumber((int) $this->getRequest()->getParam('page', 1));
        Zend_Paginator::setDefaultScrollingStyle('Elastic');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('/partials/paginators/pagination.phtml');

        // pass the data to the view
        $this->view->entries = $paginator;
        $this->view->page = $this->getRequest()->getParam('page', 1);
    }

    public function sleevingAction() {
        $this->view->title = "Sleeving Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $entry_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $mapper = new Atlas_Model_SleevingTypesMapper();
        $form = new Atlas_Form_SleevingTypes();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($form->isValid($form_data)) {
                try {
                    $mapper->processForm($form_data);

                    // clear the current SLEEVINGS cache
                    $cache = Zend_Registry::get('cache_handler');
                    $cache->remove("SLEEVINGS");

                    Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                    return $this->_redirect("/variables/sleevings");
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/variables/sleeving");
                }
            } else {
                $message = Utility_Error::buildErrors($form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($entry_id != 0) {
            // try to get the selected SLEEVING data and
            // redirect to the SLEEVINGS list on error 
            try {
                $entry = $mapper->find($entry_id);
                $form->populate(Utility_Filter_DBSafe::revert($entry->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/variables/sleeving");
            }
        }

        // pass the data to the view
        $this->view->form = $form;
    }

    public function statusesAction() {
        $this->view->title = "Status Reasons";

        // set up the mappers and the required units list
        $mapper = new Atlas_Model_PCStatusReasonsMapper();
        $statuses = $mapper->selectAll();

        // initialize the paginator
        $adapter = new Zend_Paginator_Adapter_DbSelect($statuses);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setItemCountPerPage(25)
                ->setCurrentPageNumber((int) $this->getRequest()->getParam('page', 1));
        Zend_Paginator::setDefaultScrollingStyle('Elastic');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('/partials/paginators/pagination.phtml');

        // pass the data to the view
        $this->view->statuses = $paginator;
        $this->view->page = $this->getRequest()->getParam('page', 1);
    }

    public function statusAction() {
        $this->view->title = "Status Reasons";

        // get user information
        $request = $this->getRequest();
        $status_id = (int) $request->getParam("id", 0);
        $page = (int) $request->getParam("page", 1);

        // set up the mappers and forms
        $mapper = new Atlas_Model_PCStatusReasonsMapper();
        $form = new Atlas_Form_PCStatusReasons();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($form->isValid($form_data)) {
                $mapper->processForm($form_data);

                // clear the current STATUS_REASONS cache
                $cache = Zend_Registry::get('cache_handler');
                $cache->remove("STATUS_REASONS");

                Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                return $this->_redirect("/variables/statuses/page/" . $page);
            } else {
                $message = Utility_Error::buildErrors($form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($status_id != 0) {
            // try to get the selected STATUS_REASON data and
            // redirect to the STATUS_REASONS list on error
            try {
                $entry = $mapper->find($status_id);
                $form->populate(Utility_Filter_DBSafe::revert($entry->toArray()));
            } catch (Exception $e) {
                Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                return $this->_redirect("/variables/statuses/page/" . $page);
            }
        }

        // pass the data to the view
        $this->view->form = $form;
        $this->view->id = $status_id;
        $this->view->page = $page;
    }

    public function statusActivationAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request = $this->getRequest();
        $status_id = (int) $request->getParam("id", 0);
        $status = (int) $request->getParam("s", 1);
        $page = (int) $request->getParam("page", 1);

        // ensure the proper variables are present
        if ($status_id <= 0) {
            return $this->_redirect("/variables/statuses/page/" . $page);
        }

        try {
            $mapper = new Atlas_Model_PCStatusReasonsMapper();
            if ($status <= 0) {
                $mapper->deactivateStatus($status_id);
            } else {
                $mapper->activateStatus($status_id);
            }

            // clear the current STATUS_REASONS cache
            $cache = Zend_Registry::get('cache_handler');
            $cache->remove("STATUS_REASONS");
        } catch (Exception $e) {
            Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
            return $this->_redirect("/variables/statuses/page/" . $page);
        }

        Utility_FlashMessenger::addMessage('<div class="success">The selected Status Reason was adjusted.</div>');
        return $this->_redirect("/variables/statuses/page/" . $page);
    }

    public function storageconditionsAction() {
        $this->view->title = "Storage Condition List";

        // set up the mappers and the required Storage Condition list
        $mapper = new Atlas_Model_StorageConditionsMapper();
        $entries = $mapper->selectAll();

        // initialize the paginator
        $adapter = new Zend_Paginator_Adapter_DbSelect($entries);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setItemCountPerPage(25)
                ->setCurrentPageNumber((int) $this->getRequest()->getParam('page', 1));
        Zend_Paginator::setDefaultScrollingStyle('Elastic');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('/partials/paginators/pagination.phtml');

        // pass the data to the view
        $this->view->entries = $paginator;
        $this->view->page = $this->getRequest()->getParam('page', 1);
    }

    public function storageconditionAction() {
        $this->view->title = "Storage Condition Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $entry_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $mapper = new Atlas_Model_StorageConditionsMapper();
        $form = new Atlas_Form_StorageConditions();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($form->isValid($form_data)) {
                try {
                    $mapper->processForm($form_data);

                    // clear the current STORAGE_CONDITIONS cache
                    $cache = Zend_Registry::get('cache_handler');
                    $cache->remove("STORAGE_CONDITIONS");

                    Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                    return $this->_redirect("/variables/storageconditions");
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/variables/storageconditions");
                }
            } else {
                $message = Utility_Error::buildErrors($form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($entry_id != 0) {
            // try to get the selected STORAGE_CONDITION data and
            // redirect to the unit STORAGE_CONDITIONS on error 
            try {
                $entry = $mapper->find($entry_id);
                $form->populate(Utility_Filter_DBSafe::revert($entry->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/variables/storageconditions");
            }
        }

        // pass the data to the view
        $this->view->form = $form;
    }

    public function suppliersAction() {
        $this->view->title = "Suppliers List";

        // set up the mappers and the required supplier list
        $supplier_mapper = new Atlas_Model_SuppliersMapper();
        $suppliers = $supplier_mapper->buildAll();

        // pass the data to the view
        $this->view->suppliers = $suppliers;
    }

    public function supplierAction() {
        $this->view->title = "Supplier Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $supplier_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $supplier_mapper = new Atlas_Model_SuppliersMapper();
        $supplier_form = new Atlas_Form_Suppliers();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($supplier_form->isValid($form_data)) {
                try {
                    $supplier_mapper->processForm($form_data);

                    // clear the current SUPPLIERS cache
                    $cache = Zend_Registry::get('cache_handler');
                    $cache->remove("SUPPLIERS");

                    Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                    return $this->_redirect("/variables/suppliers");
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/variables/supplier/id/" . $supplier_id);
                }
            } else {
                $message = Utility_Error::buildErrors($supplier_form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($supplier_id != 0) {
            // try to get the selected supplier data and
            // redirect to the supplier list on error 
            try {
                $supplier = new Atlas_Model_Units();
                $supplier = $supplier_mapper->find($supplier_id);
                $supplier_form->populate(Utility_Filter_DBSafe::revert($supplier->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/variables/suppliers");
            }
        }

        // pass the data to the view
        $this->view->form = $supplier_form;
    }

    public function supplieractivateAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request = $this->getRequest();
        $supplier_id = (int) $request->getParam("id", 0);
        $page = (int) $request->getParam("page", 1);

        // ensure the proper variables are present
        if ($supplier_id <= 0) {
            return $this->_redirect("/variables/suppliers/page/" . $page);
        }

        // attempt to activate the supplier
        $mapper = new Atlas_Model_SuppliersMapper();
        $mapper->activateSupplier($supplier_id);

        // clear the current SUPPLIERS cache
        $cache = Zend_Registry::get('cache_handler');
        $cache->remove("SUPPLIERS");

        Utility_FlashMessenger::addMessage('<div class="sucess">The selected supplier was activated</div>');
        return $this->_redirect("/variables/suppliers/page/" . $page);
    }

    public function supplierdeactivateAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request = $this->getRequest();
        $supplier_id = (int) $request->getParam("id", 0);
        $page = (int) $request->getParam("page", 1);

        // ensure the proper variables are present
        if ($supplier_id <= 0) {
            return $this->_redirect("/variables/suppliers/page/" . $page);
        }

        // attempt to deactivate the supplier
        $mapper = new Atlas_Model_SuppliersMapper();
        $mapper->deactivateSupplier($supplier_id);

        // clear the current SUPPLIERS cache
        $cache = Zend_Registry::get('cache_handler');
        $cache->remove("SUPPLIERS");

        Utility_FlashMessenger::addMessage('<div class="success">The selected supplier was deactivated</div>');
        return $this->_redirect("/variables/suppliers/page/" . $page);
    }

    public function testmethodsAction() {
        $this->view->title = "System Test Method List";

        // set up the mappers and the required test method list
        $testmethod_mapper = new Atlas_Model_TestMethodsMapper();
        $testmethods = $testmethod_mapper->buildAll();
        $cache = Zend_Registry::get('cache_handler');
        $cache->save(serialize($testmethods), "TEST_METHODS", array("misc_data"));
        // pass the data to the view
        $this->view->testmethods = $testmethods;
    }

    public function testmethodAction() {
        $this->view->title = "Test Method Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $test_method_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $testmethod_mapper = new Atlas_Model_TestMethodsMapper();
        $testmethod_form = new Atlas_Form_TestMethods();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($testmethod_form->isValid($form_data)) {
                $testmethod_mapper->processForm($form_data);

                // clear the current TEST METHODS cache
                $cache = Zend_Registry::get('cache_handler');
                $cache->remove("TEST_METHODS");

                Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed.</div>');
                return $this->_redirect("/variables/testmethods");
            } else {
                $message = Utility_Error::buildErrors($testmethod_form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($test_method_id != 0) {
            // try to get the selected test method data and
            // redirect to the test method list on error 
            try {
                $testmethod = new Atlas_Model_TestMethods();
                $testmethod = $testmethod_mapper->find($test_method_id);
                $testmethod_form->populate(Utility_Filter_DBSafe::revert($testmethod->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/variables/testmethods");
            }
        }

        // pass the data to the view
        $this->view->form = $testmethod_form;
    }

    public function tmactivateAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request = $this->getRequest();
        $test_method_id = (int) $request->getParam("id", 0);
        $page = (int) $request->getParam("page", 1);

        // ensure the proper variables are present
        if ($test_method_id <= 0) {
            return $this->_redirect("/variables/testmethods/page/" . $page);
        }

        // attempt to activate the test method
        $mapper = new Atlas_Model_TestMethodsMapper();
        $mapper->activateTestMethod($test_method_id);

        // clear the current TEST METHODS cache
        $cache = Zend_Registry::get('cache_handler');
        $cache->remove("TEST_METHODS");

        Utility_FlashMessenger::addMessage('<div class="success">The selected test method was activated</div>');
        return $this->_redirect("/variables/testmethods/page/" . $page);
    }

    public function tmdeactivateAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request = $this->getRequest();
        $test_method_id = (int) $request->getParam("id", 0);
        $page = (int) $request->getParam("page", 1);

        // ensure the proper variables are present
        if ($test_method_id <= 0) {
            return $this->_redirect("/variables/testmethods/page/" . $page);
        }

        // attempt to deactivate the test method
        $mapper = new Atlas_Model_TestMethodsMapper();
        $mapper->deactivateTestMethod($test_method_id);

        // clear the current TEST METHODS cache
        $cache = Zend_Registry::get('cache_handler');
        $cache->remove("TEST_METHODS");

        Utility_FlashMessenger::addMessage('<div class="success">The selected test method was deactivated</div>');
        return $this->_redirect("/variables/testmethods/page/" . $page);
    }

    public function unitsAction() {
        $this->view->title = "Units of Measure List";

        // set up the mappers and the required units list
        $unit_mapper = new Atlas_Model_UnitsMapper();
        $units = $unit_mapper->buildAll();
        
        // pass the data to the view
        $this->view->units = $units;
        $this->view->page = $this->getRequest()->getParam('page', 1);
    }

    public function unitAction() {
        $this->view->title = "Units of Measure Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $unit_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $unit_mapper = new Atlas_Model_UnitsMapper();
        $unit_form = new Atlas_Form_Units();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($unit_form->isValid($form_data)) {
                $unit_mapper->processForm($form_data);

                // clear the current UNITS cache
                $cache = Zend_Registry::get('cache_handler');
                $cache->remove("UNITS");

                Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                return $this->_redirect("/variables/units");
            } else {
                $message = Utility_Error::buildErrors($unit_form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($unit_id != 0) {
            // try to get the selected unit data and
            // redirect to the unit list on error 
            try {
                $unit = new Atlas_Model_Units();
                $unit = $unit_mapper->find($unit_id);
                $unit_form->populate(Utility_Filter_DBSafe::revert($unit->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/variables/units");
            }
        }

        // pass the data to the view
        $this->view->form = $unit_form;
    }

    public function unitactivateAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request = $this->getRequest();
        $unit_id = (int) $request->getParam("id", 0);
        $page = (int) $request->getParam("page", 1);

        // ensure the proper variables are present
        if ($unit_id <= 0) {
            return $this->_redirect("/variables/units/page/" . $page);
        }

        // attempt to activate the unit
        $mapper = new Atlas_Model_UnitsMapper();
        $mapper->activateUnit($unit_id);

        // clear the current UNITS cache
        $cache = Zend_Registry::get('cache_handler');
        $cache->remove("UNITS");

        Utility_FlashMessenger::addMessage('<div class="success">The selected Unit was activated</div>');
        return $this->_redirect("/variables/units/page/" . $page);
    }

    public function unitdeactivateAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request = $this->getRequest();
        $unit_id = (int) $request->getParam("id", 0);
        $page = (int) $request->getParam("page", 1);

        // ensure the proper variables are present
        if ($unit_id <= 0) {
            return $this->_redirect("/variables/units/page/" . $page);
        }

        // attempt to deactivate the unit
        $mapper = new Atlas_Model_UnitsMapper();
        $mapper->deactivateUnit($unit_id);

        // clear the current UNITS cache
        $cache = Zend_Registry::get('cache_handler');
        $cache->remove("UNITS");

        Utility_FlashMessenger::addMessage('<div class="success">The selected Unit was deactivated</div>');
        return $this->_redirect("/variables/units/page/" . $page);
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