<?php

namespace Arrilot\BitrixSystemCheck\Checks\Custom;

use Arrilot\BitrixSystemCheck\Checks\Check;

class XDebugIsNotLoaded extends Check
{
    /**
     * @return string
     */
    public function name()
    {
        return "Проверка, что расширение xdebug загружено...";
    }

    /**
     * @return boolean
     */
    public function run()
    {
        return $this->checkPhpExtensionsNotLoaded('xdebug');
    }
}
