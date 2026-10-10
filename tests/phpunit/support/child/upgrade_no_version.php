<?php
$sentinel = isset($argv[1]) ? $argv[1] : '';
$root = getenv('PS_SEARCHBAR_MODULE_ROOT');
if ($root === false) {
    fwrite(STDERR, "missing PS_SEARCHBAR_MODULE_ROOT\n");
    exit(1);
}
include $root . '/upgrade/upgrade-2.1.2.php';
if ($sentinel !== '' && file_exists($sentinel)) {
    file_put_contents($sentinel, '1');
}
