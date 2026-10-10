<?php

require_once __DIR__ . '/support/TestCaseBase.php';

class PsSearchbarHeaderHookTest extends PsSearchbarTestCaseBase
{
    public function testDisplayHeaderRegistersAutocompleteStylesheetAndJavascript()
    {
        $module = new Ps_Searchbar();
        $module->hookDisplayHeader();

        $controller = $module->context->controller;
        $this->assertCount(1, $controller->jqueryUiCalls);
        $this->assertSame('ui.autocomplete', $controller->jqueryUiCalls[0][0]);

        $this->assertCount(1, $controller->stylesheets);
        $this->assertSame('modules-searchbar', $controller->stylesheets[0]['id']);
        $this->assertSame('modules/ps_searchbar/ps_searchbar.css', $controller->stylesheets[0]['path']);

        $this->assertCount(1, $controller->javascripts);
        $this->assertSame('modules-searchbar', $controller->javascripts[0]['id']);
        $this->assertSame('modules/ps_searchbar/ps_searchbar.js', $controller->javascripts[0]['path']);
        $this->assertSame('bottom', $controller->javascripts[0]['params']['position']);
        $this->assertSame(150, $controller->javascripts[0]['params']['priority']);
    }
}
