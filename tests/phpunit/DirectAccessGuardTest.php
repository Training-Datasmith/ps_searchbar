<?php

require_once __DIR__ . '/support/TestCaseBase.php';

class DirectAccessGuardTest extends PsSearchbarTestCaseBase
{
    public function testModuleEntryExitsWhenVersionConstantMissing()
    {
        $result = $this->runChildScript(__DIR__ . '/support/child/module_no_version.php');
        $this->assertSame(0, $result['exitCode']);
        $this->assertSame('', $result['stdout']);
        $this->assertSame('', $result['stderr']);
        $this->assertFalse($result['sentinelExists']);
    }

    public function testUpgradeEntryExitsWhenVersionConstantMissing()
    {
        $result = $this->runChildScript(__DIR__ . '/support/child/upgrade_no_version.php');
        $this->assertSame(0, $result['exitCode']);
        $this->assertSame('', $result['stdout']);
        $this->assertSame('', $result['stderr']);
        $this->assertFalse($result['sentinelExists']);
    }

    public function testModuleRequiresAutoloadWhenVendorFileExists()
    {
        $result = $this->runChildScript(__DIR__ . '/support/child/module_autoload_probe.php');
        $this->assertSame(0, $result['exitCode']);
        $this->assertTrue($result['sentinelExists']);
    }
}
