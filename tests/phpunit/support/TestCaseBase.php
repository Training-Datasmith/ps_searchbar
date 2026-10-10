<?php

abstract class PsSearchbarTestCaseBase extends PHPUnit_Framework_TestCase
{
    /** @var string */
    protected $moduleRoot;

    protected function setUp()
    {
        parent::setUp();
        $this->moduleRoot = $GLOBALS['ps_searchbar_test_module_dir'];
        Module::resetState();
        PsSearchbarTestLink::$lastGetPageLinkArgs = null;
        if (is_dir(_PS_ROOT_DIR_)) {
            $this->removeDirectory(_PS_ROOT_DIR_);
        }
    }

    protected function tearDown()
    {
        if (is_dir(_PS_ROOT_DIR_)) {
            $this->removeDirectory(_PS_ROOT_DIR_);
        }
        parent::tearDown();
    }

    protected function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    protected function getProtectedProperty($object, $name)
    {
        $ref = new ReflectionObject($object);
        $prop = $ref->getProperty($name);
        $prop->setAccessible(true);
        return $prop->getValue($object);
    }

    protected function classicOverridePath()
    {
        return _PS_ROOT_DIR_ . '/themes/classic/modules/ps_searchbar/ps_searchbar.tpl';
    }

    protected function ensureClassicOverrideDir()
    {
        $path = $this->classicOverridePath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return $path;
    }

    /**
     * @param string $scriptPath absolute path to child script
     * @param array $env key => value exported as PS_SEARCHBAR_* or custom
     * @return array stdout, stderr, exitCode, sentinelExists
     */
    protected function runChildScript($scriptPath, array $env = array())
    {
        $sentinel = sys_get_temp_dir() . '/ps_searchbar_sentinel_' . uniqid('', true);
        $php = defined('PHP_BINARY') ? PHP_BINARY : 'php';
        $cmd = escapeshellarg($php)
            . ' -d error_reporting=-1 -d display_errors=1 -d display_startup_errors=1 -d log_errors=0'
            . ' ' . escapeshellarg($scriptPath)
            . ' ' . escapeshellarg($sentinel);

        $descriptorSpec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );

        $fullEnv = array();
        foreach (array_merge($_ENV, $_SERVER) as $key => $value) {
            if (is_string($value)) {
                $fullEnv[$key] = $value;
            }
        }
        $fullEnv['PS_SEARCHBAR_MODULE_ROOT'] = $this->moduleRoot;
        $fullEnv['PS_SEARCHBAR_SENTINEL'] = $sentinel;
        foreach ($env as $key => $value) {
            $fullEnv[$key] = $value;
        }

        $proc = proc_open($cmd, $descriptorSpec, $pipes, $this->moduleRoot, $fullEnv);
        $this->assertTrue(is_resource($proc));
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($proc);

        $this->assertNoPhpDiagnostics($stdout, $stderr);

        return array(
            'stdout' => $stdout,
            'stderr' => $stderr,
            'exitCode' => $exitCode,
            'sentinelExists' => file_exists($sentinel),
        );
    }

    protected function assertNoPhpDiagnostics($stdout, $stderr)
    {
        $combined = $stdout . "\n" . $stderr;
        $patterns = array(
            '/\bNotice\b/',
            '/\bWarning\b/',
            '/\bDeprecated\b/',
            '/Strict Standards/',
            '/\bParse error\b/',
            '/\bFatal error\b/',
            '/Catchable fatal error/',
        );
        foreach ($patterns as $pattern) {
            $this->assertNotRegExp($pattern, $combined, 'PHP diagnostic in child output: ' . $combined);
        }
    }

    protected function callLogMethods()
    {
        $methods = array();
        foreach (Module::$callLog as $entry) {
            $methods[] = $entry['method'];
        }
        return $methods;
    }

    protected function callLogContains($method, $args = null)
    {
        foreach (Module::$callLog as $entry) {
            if ($entry['method'] !== $method) {
                continue;
            }
            if ($args === null) {
                return true;
            }
            if ($entry['args'] == $args) {
                return true;
            }
        }
        return false;
    }

    protected function countCallLog($method)
    {
        $n = 0;
        foreach (Module::$callLog as $entry) {
            if ($entry['method'] === $method) {
                $n++;
            }
        }
        return $n;
    }
}
