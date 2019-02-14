<?php

namespace Arrilot\BitrixSystemCheck\Checks\Custom;

use Arrilot\BitrixSystemCheck\Checks\Check;

class NewRelicIsLoaded extends Check
{
    /**
     * @return string
     */
    public function name()
    {
        return "Проверка, что расширение newrelic загружено...";
    }

    /**
     * @return boolean
     */
    public function run()
    {
        return $this->checkPhpExtensionsLoaded('newrelic');
    }
}
