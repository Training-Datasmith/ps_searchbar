<?php
/**
 * PHPUnit bootstrap for ps_searchbar module tests (PHP 5.6+).
 */

if (!defined('_PS_VERSION_')) {
    define('_PS_VERSION_', '9.9.9.9');
}

$GLOBALS['ps_searchbar_test_root'] = dirname(dirname(__DIR__));
$GLOBALS['ps_searchbar_test_module_dir'] = $GLOBALS['ps_searchbar_test_root'];

if (!defined('_PS_ROOT_DIR_')) {
    define('_PS_ROOT_DIR_', sys_get_temp_dir() . '/ps_searchbar_test_root_' . getmypid());
}

require_once __DIR__ . '/support/Stubs.php';
require_once __DIR__ . '/support/TestCaseBase.php';

require_once $GLOBALS['ps_searchbar_test_module_dir'] . '/ps_searchbar.php';
require_once $GLOBALS['ps_searchbar_test_module_dir'] . '/upgrade/upgrade-2.1.2.php';
