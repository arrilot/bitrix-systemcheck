<?php

namespace Arrilot\BitrixSystemCheck\Checks\Custom;

use Arrilot\BitrixSystemCheck\Checks\Check;

class BasicAuthIsTurnedOn extends Check
{
    /**
     * @var string
     */
    protected $mainPage;

    /**
     * @var string
     */
    protected $basicAuth;

    public function __construct($mainPage, $basicAuth)
    {
        $this->mainPage = $mainPage;
        $this->basicAuth = $basicAuth;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return "Проверка на наличие basic-auth...";
    }

    /**
     * @return boolean
     */
    public function run()
    {
        $context = stream_context_create([
            'http' => [
                'header'  => "Authorization: Basic " . base64_encode($this->basicAuth)
            ]
        ]);

        $page = $this->mainPage;
        $test1 = file_get_contents($page, false, $context);
        if ($test1 === false) {
            $this->logError('Не удалось открыть ' . $page . ' с реквизитами ' . $this->basicAuth);
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
