<?php

require_once __DIR__ . '/support/TestCaseBase.php';

class PsSearchbarInstallTest extends PsSearchbarTestCaseBase
{
    public function testInstallRegistersThreeHooksWhenBlocksearchIsAbsent()
    {
        Module::$installedModules['blocksearch'] = false;
        $module = new Ps_Searchbar();
        $this->assertTrue($module->install());
        $this->assertSame('blocksearch', Ps_Searchbar::PS_16_EQUIVALENT_MODULE);
        $this->assertFalse($this->callLogContains('Module::getInstanceByName', array('blocksearch')));
        $this->assertTrue($this->callLogContains('parent::install'));
        $this->assertTrue($this->callLogContains('registerHook', array('displayTop')));
        $this->assertTrue($this->callLogContains('registerHook', array('displaySearch')));
        $this->assertTrue($this->callLogContains('registerHook', array('displayHeader')));
        $this->assertSame(3, $this->countCallLog('registerHook'));
    }

    public function testInstallUninstallsBlocksearchThenRegistersHooks()
    {
        Module::$installedModules['blocksearch'] = true;
        $stub = new PsSearchbarLegacyModuleStub();
        Module::$moduleInstances['blocksearch'] = $stub;
        $module = new Ps_Searchbar();
        $this->assertTrue($module->install());
        $this->assertTrue($stub->uninstallCalled);
        $this->assertTrue($this->callLogContains('registerHook', array('displayTop')));
        $this->assertTrue($this->callLogContains('registerHook', array('displaySearch')));
        $this->assertTrue($this->callLogContains('registerHook', array('displayHeader')));
    }

    public function testInstallSkipsUninstallWhenBlocksearchInstanceIsFalse()
    {
        Module::$installedModules['blocksearch'] = true;
        Module::$moduleInstances['blocksearch'] = false;
        $module = new Ps_Searchbar();
        $this->assertTrue($module->install());
        $this->assertFalse($this->callLogContains('blocksearch::uninstall'));
        $this->assertSame(3, $this->countCallLog('registerHook'));
    }

    public function testInstallReturnsFalseWithoutHooksWhenParentInstallFails()
    {
        Module::$installResult = false;
        $module = new Ps_Searchbar();
        $this->assertFalse($module->install());
        $this->assertSame(0, $this->countCallLog('registerHook'));
    }

    public function testInstallReturnsFalseAfterDisplayTopRegistrationFails()
    {
        Module::$hookResults['displayTop'] = false;
        $module = new Ps_Searchbar();
        $this->assertFalse($module->install());
        $this->assertFalse($this->callLogContains('registerHook', array('displaySearch')));
        $this->assertFalse($this->callLogContains('registerHook', array('displayHeader')));
    }

    public function testInstallReturnsFalseAfterDisplaySearchRegistrationFails()
    {
        Module::$hookResults['displaySearch'] = false;
        $module = new Ps_Searchbar();
        $this->assertFalse($module->install());
        $this->assertTrue($this->callLogContains('registerHook', array('displayTop')));
        $this->assertFalse($this->callLogContains('registerHook', array('displayHeader')));
    }

    public function testInstallReturnsFalseWhenDisplayHeaderRegistrationFails()
    {
        Module::$hookResults['displayHeader'] = false;
        $module = new Ps_Searchbar();
        $this->assertFalse($module->install());
        $this->assertTrue($this->callLogContains('registerHook', array('displayTop')));
        $this->assertTrue($this->callLogContains('registerHook', array('displaySearch')));
    }
}
