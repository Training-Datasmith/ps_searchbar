<?php
$sentinel = isset($argv[1]) ? $argv[1] : '';
$root = getenv('PS_SEARCHBAR_MODULE_ROOT');
if ($root === false) {
    fwrite(STDERR, "missing PS_SEARCHBAR_MODULE_ROOT\n");
    exit(1);
}

define('_PS_VERSION_', '9.9.9.9');

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

require $root . '/upgrade/upgrade-2.1.2.php';

$decoyDir = sys_get_temp_dir() . '/ps_searchbar_decoy_' . getmypid();
$decoyFile = $decoyDir . '/ps_searchbar.tpl';
mkdir($decoyDir, 0777, true);
file_put_contents($decoyFile, 'DECOY_SHOULD_NOT_CHANGE');

$module = new UpgradeProbeModule();
$result = upgrade_module_2_1_2($module);

$out = array(
    'result' => $result,
    'calls' => $module->calls,
    'decoy' => file_get_contents($decoyFile),
);
file_put_contents(sys_get_temp_dir() . '/ps_searchbar_upgrade_no_root_' . getmypid() . '.json', json_encode($out));

echo json_encode($out);

if ($sentinel !== '') {
    file_put_contents($sentinel, '1');
}
