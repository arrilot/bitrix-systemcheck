<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;
use CSiteCheckerTest;

/**
 * Класс для тестирования отправки писем
 * Class Mail
 * @package Arrilot\BitrixSystemCheck\Checks\Custom
 */
class Mail extends Check
{
    /**
     * Запускаем проверку
     *
     * @return bool
     */
    public function run()
    {
        /** @var bool $defaultMailResult - Результат отправки письма */
        $defaultMailResult = false;
        /** @var bool $largeMailResult - Результат отправки большого письма (более 64кб) */
        $largeMailResult = false;
        if (function_exists('mail')) {
            $emailTo = 'hosting_test@bitrixsoft.com';
            $subject = 'testing mail server';
            $message = 'testing mail server';
            $headers = 'From: webmaster@example.com' . "\r\n" .
                'Reply-To: webmaster@example.com' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

            $defaultMailResult = mail($emailTo, $subject, $message, $headers);
            if (!$defaultMailResult) {
                $this->logError('Почтовый сервер не настроен');
            }

            $largeMailResult = (new CSiteCheckerTest)->check_mail_big();
            if (!$largeMailResult) {
                $this->logError('Отправка большого почтового сообщения не удалась');
            }
        }

        return $defaultMailResult && $largeMailResult;
    }

    /**
     * Получаем название проверки
     *
     * @return string
     */
    public function name()
    {
        return 'Проверка почтового сервера...';
    }
}
