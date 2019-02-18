<?php

namespace Arrilot\BitrixSystemCheck\Checks\Custom;

use Arrilot\BitrixSystemCheck\Checks\Check;

/**
 * Класс для проверки свободного места на сервере
 * Class DiskSpaceCheck
 * @package Arrilot\BitrixSystemCheck\Checks\Custom
 */
class DiskSpaceCheck extends Check
{
    /**
     * Запускаем проверку
     *
     * @return boolean
     */
    public function run()
    {
        $result = true;
        if (disk_free_space('/') < 500000000) {
            $this->logError('На сервере заканчивается свободное место (осталось менее 500мб)');
            $result = false;
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
        return 'Проверка свободного места в файловой системе...';
    }
}
