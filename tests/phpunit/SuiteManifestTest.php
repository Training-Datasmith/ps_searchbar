<?php

require_once __DIR__ . '/support/TestCaseBase.php';

class SuiteManifestTest extends PsSearchbarTestCaseBase
{
    const EXPECTED_TEST_COUNT = 25;

    public static $expectedTestFiles = array(
        'DirectAccessGuardTest.php',
        'FrontControllerGuardTest.php',
        'PsSearchbarConstructorTest.php',
        'PsSearchbarHeaderHookTest.php',
        'PsSearchbarInstallTest.php',
        'PsSearchbarWidgetTest.php',
        'SuiteManifestTest.php',
        'UpgradeModule212Test.php',
    );

    public function testPlannedFilesAndTestCountStayInSync()
    {
        $dir = __DIR__;
        $found = array();
        foreach (glob($dir . '/*Test.php') as $path) {
            $found[] = basename($path);
        }
        sort($found);
        $expected = self::$expectedTestFiles;
        sort($expected);
        $this->assertSame($expected, $found);

        $xml = simplexml_load_file($dir . '/phpunit.xml');
        $this->assertInstanceOf('SimpleXMLElement', $xml);
        $listed = array();
        foreach ($xml->testsuites->testsuite->file as $fileNode) {
            $listed[] = basename((string) $fileNode);
        }
        sort($listed);
        $this->assertSame($expected, $listed);

        $count = 0;
        foreach ($expected as $file) {
            require_once $dir . '/' . $file;
            $class = basename($file, '.php');
            $ref = new ReflectionClass($class);
            foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (strpos($method->getName(), 'test') === 0) {
                    $count++;
                }
            }
        }
        $this->assertSame(self::EXPECTED_TEST_COUNT, $count);
    }
}
