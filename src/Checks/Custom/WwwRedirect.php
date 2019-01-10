<?php

namespace Arrilot\BitrixSystemCheck\Checks\Custom;

use Arrilot\BitrixSystemCheck\Checks\Check;

class WwwRedirect extends Check
{
    /**
     * @return string
     */
    public function getName()
    {
        return "Проверка редиректа с www на без www...";
    }

    /**
     * @return boolean
     */
    public function run()
    {

        // TODO
        return true;
    }
}
