<?php
/**
 * Write a shuffled phpunit.xml with absolute paths. Usage:
 *   php write-shuffled-suite.php <output.xml> <seed>
 */

if ($argc < 3) {
    fwrite(STDERR, "Usage: php write-shuffled-suite.php <output.xml> <seed>\n");
    exit(1);
}

$output = $argv[1];
$seed = (int) $argv[2];
$phpunitDir = realpath(dirname(__DIR__));
if ($phpunitDir === false) {
    fwrite(STDERR, "cannot resolve phpunit dir\n");
    exit(1);
}

$files = array(
    'DirectAccessGuardTest.php',
    'FrontControllerGuardTest.php',
    'PsSearchbarConstructorTest.php',
    'PsSearchbarHeaderHookTest.php',
    'PsSearchbarInstallTest.php',
    'PsSearchbarWidgetTest.php',
    'SuiteManifestTest.php',
    'UpgradeModule212Test.php',
);

srand($seed);
shuffle($files);

$bootstrap = $phpunitDir . '/bootstrap.php';
$lines = array();
$lines[] = '<?xml version="1.0" encoding="UTF-8"?>';
$lines[] = '<phpunit bootstrap="' . htmlspecialchars($bootstrap, ENT_QUOTES, 'UTF-8') . '"';
$lines[] = '         colors="true"';
$lines[] = '         convertErrorsToExceptions="true"';
$lines[] = '         convertNoticesToExceptions="true"';
$lines[] = '         convertWarningsToExceptions="true"';
$lines[] = '         beStrictAboutTestsThatDoNotTestAnything="true"';
$lines[] = '         beStrictAboutOutputDuringTests="true"';
$lines[] = '         beStrictAboutTestSize="true"';
$lines[] = '         beStrictAboutTodoAnnotatedTests="true">';
$lines[] = '    <testsuites>';
$lines[] = '        <testsuite name="module-tests">';
foreach ($files as $file) {
    $abs = $phpunitDir . '/' . $file;
    $lines[] = '            <file>' . htmlspecialchars($abs, ENT_QUOTES, 'UTF-8') . '</file>';
}
$lines[] = '        </testsuite>';
$lines[] = '    </testsuites>';
$lines[] = '</phpunit>';
$lines[] = '';

file_put_contents($output, implode("\n", $lines));
fwrite(STDOUT, implode(',', $files) . "\n");
