<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;

/**
 * Класс для проверки выполнения агентов на cron
 * Class AgentsUsingCronCheck
 * @package Arrilot\BitrixSystemCheck\Checks\Bitrix
 */
class AgentsUsingCronCheck extends Check
{
    /**
     * Запускаем проверку
     *
     * @return boolean
     */
    public function run()
    {
        $result = true;
        if (!BX_CRONTAB_SUPPORT) {
            $this->logError('Агенты не выполняются на cron');
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
        return 'Проверка выполнения агентов на cron...';
    }
}
