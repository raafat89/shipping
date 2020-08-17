<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));  

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */

require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

/** Zend_Loader_AutoLoader */
require_once 'Zend/Loader/Autoloader.php'; 
$loader = Zend_loader_AutoLoader::getInstance();
$loader->registerNamespace('pChart_');
$loader->registerNamespace('Utility_');
$loader->registerNamespace('Utility_Emails_');
$loader->registerNamespace('Utility_Image_');
$loader->registerNamespace('Utility_Validator_');
$loader->registerNamespace('Utility_Filter_');
$loader->registerNamespace('Utility_Decorator_');

$application->bootstrap()
            ->run(); 


