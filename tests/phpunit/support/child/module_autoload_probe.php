<?php
$sentinel = isset($argv[1]) ? $argv[1] : '';
$root = getenv('PS_SEARCHBAR_MODULE_ROOT');
if ($root === false) {
    fwrite(STDERR, "missing PS_SEARCHBAR_MODULE_ROOT\n");
    exit(1);
}

$work = sys_get_temp_dir() . '/ps_searchbar_autoload_' . getmypid();
mkdir($work);
$moduleCopy = $work . '/ps_searchbar.php';
$src = file_get_contents($root . '/ps_searchbar.php');
file_put_contents($moduleCopy, $src);

$vendorDir = $work . '/vendor';
mkdir($vendorDir);
$autoload = <<<'PHP'
<?php
namespace PrestaShop\PrestaShop\Core\Module {

interface WidgetInterface {
    public function renderWidget($hookName, array $configuration);
    public function getWidgetVariables($hookName, array $configuration);
}

}

namespace {

if (!class_exists('Module', false)) {
    class Module {
        public function __construct() {}
        protected function trans($id, array $parameters = array(), $domain = null, $locale = null) { return $id; }
    }
}

$GLOBALS['ps_searchbar_autoload_ran'] = true;

}
PHP;
file_put_contents($vendorDir . '/autoload.php', $autoload);

define('_PS_VERSION_', '9.9.9.9');
chdir($work);
include $moduleCopy;

if (!isset($GLOBALS['ps_searchbar_autoload_ran']) || !$GLOBALS['ps_searchbar_autoload_ran']) {
    fwrite(STDERR, "autoload did not run\n");
    exit(2);
}
if (!class_exists('Ps_Searchbar', false)) {
    fwrite(STDERR, "Ps_Searchbar not loaded\n");
    exit(3);
}

if ($sentinel !== '') {
    file_put_contents($sentinel, '1');
}
