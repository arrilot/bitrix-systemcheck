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
        $result = true;
        if ((new CSiteCheckerTest)->check_cache()) {
            $result = false;
            $this->logError('Кеширование при помощи файлов не настроено');
        }

        return $result;
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
