<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;
use CSiteCheckerTest;

/**
 * Класс для проверки доступа к серверу обновлений
 * Class UpdateServerCheck
 * @package Arrilot\BitrixSystemCheck\Checks\Bitrix
 */
class UpdateServerCheck extends Check
{
    /**
     * Запускаем проверку
     *
     * @return boolean
     */
    public function run()
    {
        return (new CSiteCheckerTest)->check_update();
    }

    /**
     * Получаем название проверки
     *
     * @return string
     */
    public function name()
    {
        return 'Проверка доступа к серверу обновлений...';
    }
}
