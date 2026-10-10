<?php

require_once __DIR__ . '/support/TestCaseBase.php';

class PsSearchbarWidgetTest extends PsSearchbarTestCaseBase
{
    /** @var Ps_Searchbar */
    private $module;

    protected function setUp()
    {
        parent::setUp();
        $this->module = new Ps_Searchbar();
    }

    public function testGetWidgetVariablesUsesSearchPageLinkAndEmptyString()
    {
        $this->module->context->smarty->setTemplateVars(array());
        $result = $this->module->getWidgetVariables('displayTop');
        $this->assertSame(
            array(
                'search_controller_url' => 'https://shop.example/index.php?controller=search',
                'search_string' => '',
            ),
            $result
        );
        $args = PsSearchbarTestLink::$lastGetPageLinkArgs;
        $this->assertSame('search', $args[0]);
        $this->assertNull($args[1]);
        $this->assertNull($args[2]);
        $this->assertNull($args[3]);
        $this->assertFalse($args[4]);
        $this->assertNull($args[5]);
        $this->assertTrue($args[6]);
    }

    public function testGetWidgetVariablesKeepsExistingSearchString()
    {
        $this->module->context->smarty->setTemplateVars(array('search_string' => 'linen shirt'));
        $result = $this->module->getWidgetVariables('displayTop');
        $this->assertSame(
            array('search_controller_url' => 'https://shop.example/index.php?controller=search'),
            $result
        );
        $this->assertArrayNotHasKey('search_string', $result);
    }

    public function testGetWidgetVariablesKeepsNullSearchString()
    {
        $this->module->context->smarty->setTemplateVars(array('search_string' => null));
        $result = $this->module->getWidgetVariables('displayTop');
        $this->assertArrayNotHasKey('search_string', $result);
    }

    public function testRenderWidgetAssignsVariablesAndReturnsFetchedTemplate()
    {
        $this->module->context->smarty->setTemplateVars(array());
        $html = $this->module->renderWidget('displayTop', array());
        $this->assertSame('SEARCHBAR_FETCH_SENTINEL', $html);
        $this->assertSame(
            array(
                'search_controller_url' => 'https://shop.example/index.php?controller=search',
                'search_string' => '',
            ),
            $this->module->smarty->assigned
        );
        $this->assertNotSame($this->module->context->smarty, $this->module->smarty);

        $fetchFound = false;
        foreach (Module::$callLog as $entry) {
            if ($entry['method'] === 'fetch' && $entry['args'] === array('module:ps_searchbar/ps_searchbar.tpl')) {
                $fetchFound = true;
            }
        }
        $this->assertTrue($fetchFound);
    }

    public function testRenderWidgetUsesTheSameTemplateOnDisplayTopAndDisplaySearch()
    {
        $this->module->context->smarty->setTemplateVars(array());
        $top = $this->module->renderWidget('displayTop', array());
        $search = $this->module->renderWidget('displaySearch', array());
        $this->assertSame('SEARCHBAR_FETCH_SENTINEL', $top);
        $this->assertSame('SEARCHBAR_FETCH_SENTINEL', $search);

        $fetches = array();
        foreach (Module::$callLog as $entry) {
            if ($entry['method'] === 'fetch') {
                $fetches[] = $entry['args'];
            }
        }
        $this->assertSame(
            array(
                array('module:ps_searchbar/ps_searchbar.tpl'),
                array('module:ps_searchbar/ps_searchbar.tpl'),
            ),
            $fetches
        );

        foreach (array('displayTop', 'displaySearch') as $hook) {
            $this->module->context->smarty->setTemplateVars(array());
            $vars = $this->module->getWidgetVariables($hook);
            $this->assertSame('https://shop.example/index.php?controller=search', $vars['search_controller_url']);
        }
    }
}
