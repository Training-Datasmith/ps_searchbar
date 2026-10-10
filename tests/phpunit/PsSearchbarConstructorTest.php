<?php

require_once __DIR__ . '/support/TestCaseBase.php';

class PsSearchbarConstructorTest extends PsSearchbarTestCaseBase
{
    /** @var Ps_Searchbar */
    private $module;

    protected function setUp()
    {
        parent::setUp();
        $this->module = new Ps_Searchbar();
    }

    public function testConstructorPublishesIdentityCompliancyAndTranslations()
    {
        $this->assertSame('ps_searchbar', $this->module->name);
        $this->assertSame('front_office_features', $this->module->tab);
        $this->assertSame('PrestaShop', $this->module->author);
        $this->assertSame('2.1.4', $this->module->version);
        $this->assertSame(0, $this->module->need_instance);
        $this->assertSame(
            array('min' => '1.7.8.0', 'max' => '9.9.9.9'),
            $this->module->ps_versions_compliancy
        );

        $this->assertCount(2, PsSearchbarTestTranslator::$log);
        $ids = array();
        foreach (PsSearchbarTestTranslator::$log as $row) {
            $ids[] = $row['id'];
            $this->assertSame('Modules.Searchbar.Admin', $row['domain']);
            $this->assertSame(array(), $row['parameters']);
            $this->assertTrue(strlen($row['id']) > 0);
        }
        $this->assertNotSame($ids[0], $ids[1]);
        $this->assertSame(PsSearchbarTestTranslator::translate($ids[0]), $this->module->displayName);
        $this->assertSame(PsSearchbarTestTranslator::translate($ids[1]), $this->module->description);
    }

    public function testConfigXmlAgreesOnNameVersionAuthorAndDisplayName()
    {
        $xml = simplexml_load_file($this->moduleRoot . '/config.xml');
        $this->assertInstanceOf('SimpleXMLElement', $xml);
        $this->assertSame('ps_searchbar', (string) $xml->name);
        $this->assertSame('2.1.4', (string) $xml->version);
        $this->assertSame('PrestaShop', (string) $xml->author);
        $this->assertSame('0', (string) $xml->need_instance);
        $this->assertSame('0', (string) $xml->is_configurable);
        $this->assertTrue(strlen((string) $xml->description) > 0);
        $this->assertTrue(strlen((string) $xml->displayName) > 0);
        $this->assertSame('ps_searchbar', $this->module->name);
        $this->assertSame('2.1.4', $this->module->version);
    }

    public function testTemplateBindsSearchUrlAndSearchString()
    {
        $css = file_get_contents($this->moduleRoot . '/ps_searchbar.css');
        $js = file_get_contents($this->moduleRoot . '/ps_searchbar.js');
        $tpl = file_get_contents($this->moduleRoot . '/ps_searchbar.tpl');
        $this->assertTrue(strlen($css) > 0);
        $this->assertTrue(strlen($js) > 0);
        $this->assertTrue(strpos($tpl, 'id="search_widget"') !== false);
        $this->assertTrue(strpos($tpl, 'data-search-controller-url="{$search_controller_url}"') !== false);
        $this->assertTrue(strpos($tpl, 'name="s"') !== false);
        $this->assertTrue(strpos($tpl, 'value="{$search_string}"') !== false);
    }

    public function testModuleImplementsWidgetInterface()
    {
        $this->assertTrue(is_subclass_of('Ps_Searchbar', 'Module'));
        $this->assertTrue(in_array('PrestaShop\\PrestaShop\\Core\\Module\\WidgetInterface', class_implements('Ps_Searchbar')));

        $ref = new ReflectionClass('Ps_Searchbar');
        $render = $ref->getMethod('renderWidget');
        $this->assertTrue($render->isPublic());
        $vars = $ref->getMethod('getWidgetVariables');
        $this->assertTrue($vars->isPublic());

        $renderParams = $render->getParameters();
        $this->assertSame('hookName', $renderParams[0]->getName());
        $this->assertSame('configuration', $renderParams[1]->getName());
        $this->assertTrue($renderParams[1]->isArray());
        $this->assertTrue($renderParams[1]->isOptional());

        $varsParams = $vars->getParameters();
        $this->assertSame('hookName', $varsParams[0]->getName());
        $this->assertSame('configuration', $varsParams[1]->getName());
        $this->assertTrue($varsParams[1]->isArray());
        $this->assertTrue($varsParams[1]->isOptional());
    }
}
