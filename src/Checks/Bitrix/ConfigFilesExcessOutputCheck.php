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
    /**
     * @var array extra files to check.
     */
    protected $extraFiles;

    /** @var bool $result - Результат проверки */
    private $result;

    public function __construct($extraFiles = [])
    {
        $this->extraFiles = [];
    }

    /**
     * Проверяем указанный файл
     *
     * @param string $filePath - Путь до файла, который необходимо проверить
     * @return void
     */
    private function checkFile($filePath)
    {
        if (!file_exists($filePath)) {
            return;
        }

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
        $bitrixDir = $_SERVER['DOCUMENT_ROOT'] . BX_PERSONAL_ROOT;
        $localDir = $_SERVER['DOCUMENT_ROOT'] . '/local';

        $this->checkFile($bitrixDir . '/php_interface/dbconn.php');
        $this->checkFile($bitrixDir . '/php_interface/init.php');
        $this->checkFile($bitrixDir . '/php_interface/after_connect.php');
        $this->checkFile($bitrixDir . '/php_interface/after_connect_d7.php');
        $this->checkFile($localDir . '/php_interface/init.php');
        foreach ($this->extraFiles as $extraFile) {
            $this->checkFile($extraFile);
        }

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
