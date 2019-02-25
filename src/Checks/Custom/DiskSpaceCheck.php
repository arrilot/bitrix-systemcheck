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
    /** @var int $limitBytes - Минимальный порог для свободного места (в байтах) */
    private $limitBytes;

    /** @var int $limitMegaBytes - Минимальный порог для свободного места (в мегабайтах) */
    private $limitMegaBytes;

    /**
     * DiskSpaceCheck constructor.
     *
     * @param int $limit - Минимальный порог для свободного места (в мегабайтах)
     */
    public function __construct($limit = 500)
    {
        $this->limitMegaBytes = $limit;
        $this->limitBytes = $limit * 1000000;
    }

    /**
     * Запускаем проверку
     *
     * @return boolean
     */
    public function run()
    {
        $result = true;
        if (disk_free_space('/') < $this->limitBytes) {
            $this->logError(
                'На сервере заканчивается свободное место (осталось менее ' . $this->limitMegaBytes . 'мб)'
            );
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
