<?php

namespace Arrilot\BitrixSystemCheck\Checks\Custom;

use Arrilot\BitrixSystemCheck\Checks\Check;

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
        $result = false;
        if (function_exists('mail')) {
            $emailTo = 'test@testmail.com';
            $subject = 'testing mail server';
            $message = 'testing mail server';
            $headers = 'From: webmaster@example.com' . "\r\n" .
                'Reply-To: webmaster@example.com' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

            $result = mail($emailTo, $subject, $message, $headers);
            if (!$result) {
                $this->logError('Почтовый сервер не настроен');
            }
        }

        /** @var false|array $notSentMail - Массив идентификаторов неотправленных писем */
        $notSentMail = db()->query('SELECT ID FROM b_event WHERE SUCCESS_EXEC != "Y"');
        if ($notSentMail) {
            $notSentLetters = 0;
            while ($notSentMail->fetch()) {
                $notSentLetters++;
            }
            $this->logError('В базе имеются ' . $notSentLetters . ' неотправленных писем');
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
        return 'Проверка почтового сервера...';
    }
}
