<?php
/**
 * 2016 Smart Soft.
 *
 * @author    Marcin Kubiak
 * @copyright Smart Soft
 * @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

// ========== Mocks ===========
require_once __DIR__ . '/mocks/ObjectModel.php';

// ======== Classes ===========
require_once __DIR__ . '/../classes/Dslist.Class.php';

$mainDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
require_once $mainDir . 'config/defines.inc.php';
require_once _PS_CONFIG_DIR_ . 'autoload.php';
require $mainDir . 'vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    define('_PS_VERSION_', 'TEST_VERSION');
}

if (!defined('__PS_BASE_URI__')) {
    define('__PS_BASE_URI__', '__PS_BASE_URI__');
}
