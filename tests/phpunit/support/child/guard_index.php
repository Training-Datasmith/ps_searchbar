<?php
$sentinel = isset($argv[1]) ? $argv[1] : '';
$target = isset($argv[2]) ? $argv[2] : '';
if ($target === '' || !is_file($target)) {
    fwrite(STDERR, "missing target\n");
    exit(1);
}
include $target;
if ($sentinel !== '' && file_exists($sentinel)) {
    file_put_contents($sentinel, '1');
}
