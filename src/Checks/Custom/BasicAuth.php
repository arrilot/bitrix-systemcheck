<?php

namespace Arrilot\BitrixSystemCheck\Checks\Custom;

use Arrilot\BitrixSystemCheck\Checks\Check;

class BasicAuth extends Check
{
    /**
     * @return string
     */
    function getName()
    {
        return "Проверка на наличие basic-auth...";
    }

    /**
     * @return boolean
     */
    function run()
    {
        $this->skipIfMissingConfigOptions(['basicAuth', 'domain']);
    
        $context = stream_context_create([
            'http' => [
                'header'  => "Authorization: Basic " . base64_encode($this->packageConfig['basicAuth'])
            ]
        ]);
        
        $page = 'https://'. $this->packageConfig['domain'] . '/';
        $test1 = file_get_contents($page, false, $context);
        if ($test1 === false) {
            $this->logError('Не удалось открыть ' . $page . ' с реквизитами ' . $this->packageConfig['basicAuth']);
            return false;
        }
    
        $test2 = file_get_contents($page);
        if ($test2 !== false) {
            $this->logError('Страница ' . $page . ' не закрыта базовой авторизацией');
            return false;
        }

        return true;
    }
}
