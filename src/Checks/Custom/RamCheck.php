<?php

namespace Arrilot\BitrixSystemCheck\Checks\Custom;

use Arrilot\BitrixSystemCheck\Checks\Check;

/**
 * Класс для проверки свободной оперативной памяти
 * Class RamCheck
 * @package Arrilot\BitrixSystemCheck\Checks\Custom
 */
class RamCheck extends Check
{
    /**
     * Запускаем проверку
     *
     * @return boolean
     */
    public function run()
    {
        /** @var bool $result - Результат проверки */
        $result = true;
        /** @var resource $systemFile - Файл с информацией о памяти */
        $systemFile = fopen('/proc/meminfo', 'r');

        $memory = 0;
        while ($line = fgets($systemFile)) {
            $pieces = [];
            if (preg_match('/^MemFree:\s+(\d+)\skB$/', $line, $pieces)) {
                $memory = $pieces[1];
                break;
            }
        }
        fclose($systemFile);

        if ($memory < 500000) {
            $this->logError('На сервере мало свободной оперативной памяти');
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
        return 'Проверка свободной оперативной памяти...';
    }
}
