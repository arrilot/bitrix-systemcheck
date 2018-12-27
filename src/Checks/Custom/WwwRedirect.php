<?php

namespace Arrilot\BitrixSystemCheck\Checks\Custom;

use Arrilot\BitrixSystemCheck\Checks\Check;

class WwwRedirect extends Check
{
    /**
     * @return string
     */
    function getName()
    {
        return "Проверка содержимого редиректа с www на без www...";
    }

    /**
     * @return boolean
     */
    function run()
    {
        if (!in_production()) {
            $this->skip('проверка проводится только для production');
        }

        // TODO
        return true;
    }
}
