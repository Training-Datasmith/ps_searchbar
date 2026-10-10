<?php

require_once __DIR__ . '/support/TestCaseBase.php';

class UpgradeProbeModule
{
    public $calls = array();

    public function unregisterHook($name)
    {
        $this->calls[] = 'unregister:' . $name;
        return true;
    }

    public function registerHook($name)
    {
        $this->calls[] = 'register:' . $name;
        return true;
    }
}

class UpgradeModule212Test extends PsSearchbarTestCaseBase
{
    protected function setUp()
    {
        parent::setUp();
        if (!is_dir(_PS_ROOT_DIR_)) {
            mkdir(_PS_ROOT_DIR_, 0777, true);
        }
    }

    public function testUpgradeRewiresHooksAndRenamesExistingClassicOverride()
    {
        $path = $this->ensureClassicOverrideDir();
        file_put_contents($path, 'classic-override-marker');
        $bak = $path . '.bak';

        $module = new UpgradeProbeModule();
        $this->assertTrue(upgrade_module_2_1_2($module));

        $this->assertTrue($this->upgradeCallContains($module->calls, 'unregister', 'top'));
        $this->assertTrue($this->upgradeCallContains($module->calls, 'register', 'displayTop'));
        $this->assertTrue($this->upgradeCallContains($module->calls, 'unregister', 'header'));
        $this->assertTrue($this->upgradeCallContains($module->calls, 'register', 'displayHeader'));

        $this->assertFalse(file_exists($path));
        $this->assertTrue(file_exists($bak));
        $this->assertSame('classic-override-marker', file_get_contents($bak));
    }

    public function testUpgradeLeavesNothingRenamedWhenClassicOverrideMissing()
    {
        $path = $this->ensureClassicOverrideDir();
        if (file_exists($path)) {
            unlink($path);
        }
        $bak = $path . '.bak';
        $this->assertFalse(file_exists($path));
        $this->assertFalse(file_exists($bak));

        $module = new UpgradeProbeModule();
        $this->assertTrue(upgrade_module_2_1_2($module));

        $this->assertFalse(file_exists($path));
        $this->assertFalse(file_exists($bak));
    }

    public function testUpgradeSkipsFilesystemWhenRootConstantIsUndefined()
    {
        $result = $this->runChildScript(__DIR__ . '/support/child/upgrade_no_root_dir.php');
        $this->assertSame(0, $result['exitCode']);
        $this->assertTrue($result['sentinelExists']);
        $data = json_decode($result['stdout'], true);
        $this->assertTrue($data['result']);
        $this->assertSame('DECOY_SHOULD_NOT_CHANGE', $data['decoy']);
        $calls = $data['calls'];
        $this->assertTrue(in_array('unregister:top', $calls));
        $this->assertTrue(in_array('register:displayTop', $calls));
        $this->assertTrue(in_array('unregister:header', $calls));
        $this->assertTrue(in_array('register:displayHeader', $calls));
    }

    private function upgradeCallContains($calls, $verb, $hook)
    {
        $needle = $verb . ':' . $hook;
        return in_array($needle, $calls);
    }
}
