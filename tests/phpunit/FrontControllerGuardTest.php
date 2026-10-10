<?php

require_once __DIR__ . '/support/TestCaseBase.php';

class FrontControllerGuardTest extends PsSearchbarTestCaseBase
{
    public function testIndexPhpFilesExitWithoutBodyOrDiagnostics()
    {
        $expected = array(
            'index.php',
            'tests/index.php',
            'tests/phpstan/index.php',
            'translations/index.php',
        );
        sort($expected);

        $found = array();
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->moduleRoot, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->getFilename() !== 'index.php') {
                continue;
            }
            $path = str_replace('\\', '/', $file->getPathname());
            if (strpos($path, '/vendor/') !== false) {
                continue;
            }
            $rel = substr($path, strlen($this->moduleRoot) + 1);
            $found[] = $rel;
        }
        sort($found);
        $this->assertSame($expected, $found);

        $child = __DIR__ . '/support/child/guard_index.php';
        foreach ($expected as $rel) {
            $target = $this->moduleRoot . '/' . $rel;
            $sentinel = sys_get_temp_dir() . '/ps_searchbar_guard_' . uniqid('', true);
            $php = defined('PHP_BINARY') ? PHP_BINARY : 'php';
            $cmd = escapeshellarg($php)
                . ' -d error_reporting=-1 -d display_errors=1 -d display_startup_errors=1 -d log_errors=0'
                . ' ' . escapeshellarg($child)
                . ' ' . escapeshellarg($sentinel)
                . ' ' . escapeshellarg($target);
            $descriptorSpec = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
            $proc = proc_open($cmd, $descriptorSpec, $pipes, $this->moduleRoot);
            fclose($pipes[0]);
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exitCode = proc_close($proc);
            $this->assertNoPhpDiagnostics($stdout, $stderr);
            $this->assertSame(0, $exitCode);
            $this->assertSame('', $stdout);
            $this->assertSame('', $stderr);
            $this->assertFalse(file_exists($sentinel));
        }
    }
}
