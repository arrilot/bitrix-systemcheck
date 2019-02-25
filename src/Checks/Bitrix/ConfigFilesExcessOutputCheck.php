<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;
use CSiteCheckerTest;

/**
 * Класс проверяет файлы конфигурации на наличие вывода текста
 * Class ConfigFilesExcessOutputCheck
 * @package Arrilot\BitrixSystemCheck\Checks\Bitrix
 */
class ConfigFilesExcessOutputCheck extends Check
{
    /** @var bool $result - Результат проверки */
    private $result;

    /**
     * Проверяем указанный файл
     *
     * @param string $filePath - Путь до файла, который необходимо проверить
     * @return void
     */
    private function checkFile(string $filePath)
    {
        $file = file_get_contents($filePath);
        if (preg_match('/<\?php(.+?)\?>.*?[^<\?]/s', $file) || preg_match('/[\s\S]<\?php/', $file)) {
            $this->logError('В файле ' . $filePath . ' обнаружены лишние символы (пробелы, переносы и т.д.)');
            $this->result = false;
        }
    }

    /**
     * Запускаем проверку
     *
     * @return boolean
     */
    public function run()
    {
        $this->result = true;
        $this->checkFile(realpath($_SERVER['DOCUMENT_ROOT'] . '/../config/dbconn.php'));
        $this->checkFile(realpath($_SERVER['DOCUMENT_ROOT'] . '/../app/local/php_interface/init.php'));
        return $this->result;
    }

    /**
     * Получаем название проверки
     *
     * @return string
     */
    public function name()
    {
        return 'Проверка лишнего вывода в файлах конфигурации...';
    }
}
