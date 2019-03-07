<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;
use Bitrix\Main\Config\Configuration;

/**
 * Класс для проверки настроек подключения к БД
 * Class DataBaseConnectionSettings
 * @package Arrilot\BitrixSystemCheck\Checks\Custom
 */
class DataBaseConnectionSettings extends Check
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
        /** @var null|string $DBHost - Хост */
        $DBHost = null;
        /** @var null|string $DBName - Название БД */
        $DBName = null;
        /** @var null|string $DBLogin - Логин от БД */
        $DBLogin = null;
        /** @var null|string $DBPassword - Пароль от БД */
        $DBPassword = null;

        require($_SERVER['DOCUMENT_ROOT'] . '/../config/dbconn.php');
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
        if ($settingsParam != $dbconnParam) {
            $this->logError(
                'Параметр ' . $paramName
                    . 'в config/dbconn.php не соответствует этому же параметру в bitrix/.settings.php'
            );
            $this->result = true;
        }
    }

    /**
     * Получаем название проверки
     *
     * @return string
     */
    public function name()
    {
        return 'Проверка параметров подключения к базе данных...';
    }
}
