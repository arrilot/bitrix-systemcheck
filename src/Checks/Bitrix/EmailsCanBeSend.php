<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;
use CSiteCheckerTest;

/**
 * Класс для тестирования отправки писем
 * Class Mail
 * @package Arrilot\BitrixSystemCheck\Checks\Custom
 */
class EmailsCanBeSend extends Check
{
    /**
     * Запускаем проверку
     *
     * @return bool
     */
    public function run()
    {
        $siteChecker = new CSiteCheckerTest;
        if (!$siteChecker->check_mail()) {
            $this->logError('Отправка почтового сообщения не удалась');
            return false;
        }

        if (!$siteChecker->check_mail_big()) {
            $this->logError('Отправка большого почтового сообщения не удалась');
            return false;
        }

        return true;
    }

    /**
     * Получаем название проверки
     *
     * @return string
     */
    public function name()
    {
        return 'Проверка отправки email...';
    }
}
