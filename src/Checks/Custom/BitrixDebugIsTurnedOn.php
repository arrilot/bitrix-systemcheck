<?php

namespace Arrilot\BitrixSystemCheck\Checks\Custom;

use Arrilot\BitrixSystemCheck\Checks\Check;
use Bitrix\Main\Config\Configuration;

class BitrixDebugIsTurnedOn extends Check
{
    /**
     * @return string
     */
    public function getName()
    {
        return "Проверка на exception_handling.debug = true ...";
    }

    /**
     * @return boolean
     */
    public function run()
    {
        $config = Configuration::getInstance()->get('exception_handling');

        if (empty($config['debug'])) {
            $this->logError('Значение конфигурации exception_handling.debug должно быть true в данном окружении');
            return false;
        }

        return true;
    }
}
