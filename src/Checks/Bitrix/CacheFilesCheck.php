<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;
use CSiteCheckerTest;

/**
 * Класс для проверки работы с файлами кеша
 * Class CacheFilesCheck
 * @package Arrilot\BitrixSystemCheck\Checks\Bitrix
 */
class CacheFilesCheck extends Check
{
    /**
     * Запускаем проверку
     *
     * @return boolean
     */
    public function run()
    {
        return (new CSiteCheckerTest)->check_cache();
    }

    /**
     * Получаем название проверки
     *
     * @return string
     */
    public function name()
    {
        return 'Проверка работы с файлами кеша...';
    }
}
