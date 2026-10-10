<?php

namespace PrestaShop\PrestaShop\Core\Module {

interface WidgetInterface
{
    public function renderWidget($hookName, array $configuration);

    public function getWidgetVariables($hookName, array $configuration);
}

}

namespace {

if (!class_exists('Module', false)) {
    class Module
    {
        public $name;
        public $tab;
        public $author;
        public $version;
        public $need_instance;
        public $displayName;
        public $description;
        public $ps_versions_compliancy = array();

        /** @var \PsSearchbarTestContext */
        public $context;

        /** @var \PsSearchbarTestSmartyData */
        public $smarty;

        public static $installResult = true;
        public static $hookResults = array();
        public static $installedModules = array();
        public static $moduleInstances = array();
        public static $callLog = array();

        public function __construct()
        {
            $this->context = new PsSearchbarTestContext();
            $this->smarty = new PsSearchbarTestSmartyData();
        }

        public static function resetState()
        {
            self::$installResult = true;
            self::$hookResults = array();
            self::$installedModules = array();
            self::$moduleInstances = array();
            self::$callLog = array();
            PsSearchbarTestTranslator::$log = array();
            PsSearchbarTestLink::$lastGetPageLinkArgs = null;
        }

        public static function log($method, $args)
        {
            self::$callLog[] = array('method' => $method, 'args' => $args);
        }

        public static function isInstalled($moduleName)
        {
            self::log('Module::isInstalled', func_get_args());
            return isset(self::$installedModules[$moduleName]) && self::$installedModules[$moduleName];
        }

        public static function getInstanceByName($moduleName)
        {
            self::log('Module::getInstanceByName', func_get_args());
            if (!isset(self::$moduleInstances[$moduleName])) {
                return false;
            }
            return self::$moduleInstances[$moduleName];
        }

        public function install()
        {
            self::log('parent::install', array());
            return self::$installResult;
        }

        public function registerHook($hookName)
        {
            self::log('registerHook', func_get_args());
            if (isset(self::$hookResults[$hookName])) {
                return self::$hookResults[$hookName];
            }
            return true;
        }

        public function unregisterHook($hookName)
        {
            self::log('unregisterHook', func_get_args());
            return true;
        }

        public function fetch($templatePath, $cache_id = null, $compile_id = null)
        {
            self::log('fetch', func_get_args());
            return 'SEARCHBAR_FETCH_SENTINEL';
        }

        protected function trans($id, array $parameters = array(), $domain = null, $locale = null)
        {
            PsSearchbarTestTranslator::record($id, $parameters, $domain, $locale);
            return PsSearchbarTestTranslator::translate($id);
        }
    }
}

class PsSearchbarTestTranslator
{
    public static $log = array();

    public static function record($id, $parameters, $domain, $locale)
    {
        self::$log[] = array(
            'id' => $id,
            'parameters' => $parameters,
            'domain' => $domain,
            'locale' => $locale,
        );
    }

    public static function translate($id)
    {
        return 'TRANSLATED:' . md5($id);
    }
}

class PsSearchbarTestLink
{
    public static $lastGetPageLinkArgs = null;

    public function getPageLink($controller, $ssl = null, $idLang = null, $request = null, $requestUrlEncode = false, $idShop = null, $relativeProtocol = false)
    {
        PsSearchbarTestLink::$lastGetPageLinkArgs = func_get_args();
        return 'https://shop.example/index.php?controller=search';
    }
}

class PsSearchbarTestSmarty
{
    private $templateVars = array();

    public function getTemplateVars()
    {
        return $this->templateVars;
    }

    public function setTemplateVars($vars)
    {
        $this->templateVars = $vars;
    }
}

class PsSearchbarTestSmartyData
{
    public $assigned = array();

    public function assign($vars)
    {
        $this->assigned = $vars;
    }
}

class PsSearchbarTestFrontController
{
    public $jqueryUiCalls = array();
    public $stylesheets = array();
    public $javascripts = array();

    public function addJqueryUI($component, $theme = 'base', $check_dependencies = true)
    {
        $this->jqueryUiCalls[] = func_get_args();
    }

    public function registerStylesheet($id, $relativePath, $params = array())
    {
        $this->stylesheets[] = array('id' => $id, 'path' => $relativePath, 'params' => $params);
    }

    public function registerJavascript($id, $relativePath, $params = array())
    {
        $this->javascripts[] = array('id' => $id, 'path' => $relativePath, 'params' => $params);
    }
}

class PsSearchbarTestContext
{
    /** @var PsSearchbarTestLink */
    public $link;

    /** @var PsSearchbarTestSmarty */
    public $smarty;

    /** @var PsSearchbarTestFrontController */
    public $controller;

    public function __construct()
    {
        $this->link = new PsSearchbarTestLink();
        $this->smarty = new PsSearchbarTestSmarty();
        $this->controller = new PsSearchbarTestFrontController();
    }
}

class PsSearchbarLegacyModuleStub
{
    public $uninstallCalled = false;

    public function uninstall()
    {
        $this->uninstallCalled = true;
        Module::log('blocksearch::uninstall', array());
        return true;
    }
}

}
