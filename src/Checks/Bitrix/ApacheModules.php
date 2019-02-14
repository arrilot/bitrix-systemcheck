<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;

class ApacheModules extends Check
{
    protected $blacklist = [
        'mod_security',
        'mod_dav',
        'mod_dav_fs',
    ];
    
    /**
     * @return boolean
     */
    public function run()
    {
        if (!function_exists('apache_get_modules')) {
            return true;
        }

        $result = true;
        $loadedModules = apache_get_modules();
        foreach ($this->blacklist as $module) {
            if (in_array($module, $loadedModules)) {
                $this->logError("Необходимо выключить модуль $module");
                $result = false;
            }
        }

        return $result;
    }
    
    /**
     * @return string
     */
    public function name()
    {
        return "Проверка модулей apache...";
    }
}