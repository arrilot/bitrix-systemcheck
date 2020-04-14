<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;
use Bitrix\Main\Config\Configuration;

/**
 * Класс для проверки настроек подключения к БД
 * Class DataBaseConnectionSettings
 * @package Arrilot\BitrixSystemCheck\Checks\Custom
 */
class DataBaseConfigsMatchForBothCores extends Check
{
    /** @var bool $result - Результат проверки */
    private $result = true;

    /**
     * Запускаем проверку
     *
     * @return boolean
     */
    public function run()
    {
        global $DBHost, $DBName, $DBLogin, $DBPassword;
        $connectionsSettings = Configuration::getInstance()->get('connections')['default'];

        $this->check('host', $connectionsSettings['host'], $DBHost);
        $this->check('database', $connectionsSettings['database'], $DBName);
        $this->check('login', $connectionsSettings['login'], $DBLogin);
        $this->check('password', $connectionsSettings['password'], $DBPassword);

        return $this->result;
    }

    /**
     * Производим сравнение параметров из dbconn.php и .settings.php
     *
     * @param $paramName - Название параметра
     * @param $settingsParam - Параметр в файле .settings.php
     * @param $dbconnParam - Параметр в файле dbconn.php
     */
    private function check($paramName, $settingsParam, $dbconnParam)
    {
        if ($settingsParam !== $dbconnParam) {
            $this->logError(
                'Параметр ' . $paramName
                    . ' в dbconn.php не соответствует этому же параметру в .settings.php'
            );
            $this->result = false;
        }
    }

    /**
     * Получаем название проверки
     *
     * @return string
     */
    public function name()
    {
        return 'Проверка совпадения параметров подключения к базе данных в старом и новом ядре...';
    }
}
