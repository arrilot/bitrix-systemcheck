<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;

class RequiredPhpModules extends Check
{
    protected $requiredExtension = [
        'zlib',
        'curl',
        'json',
        'gd',
        'hash',
        'mbstring',
    ];
    
    /**
     * @return boolean
     */
    public function run()
    {
        $result = true;
        foreach ($this->requiredExtension as $extension) {
            if (!extension_loaded($extension)) {
                $this->logError("модуль $extension не подключен");
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "Наличие необходимых Битриксу модулей php...";
    }
}