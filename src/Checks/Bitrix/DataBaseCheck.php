<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Configuration;
use CSite;
use DateTime;

/**
 * Класс для тестирования базы данных
 * Class DataBaseCheck
 * @package Arrilot\BitrixSystemCheck\Checks\Bitrix
 */
class DataBaseCheck extends Check
{
    /** @var bool $result - Результат проверки */
    private $result = true;

    /** @var Application $connection - Объект подключения к БД */
    private $connection;

    /**
     * DataBaseCheck constructor.
     */
    public function __construct()
    {
        $this->connection = Application::getConnection();
    }

    /**
     * Запускаем проверку
     *
     * @return boolean
     */
    public function run()
    {
        $this->mysqlVersionCheck();
        $this->compareServerAndMysqlTimestamps();
        $this->checkSqlMode();
        $this->checkCharset();

        return $this->result;
    }

    /**
     * Проверяем версию MySQL
     *
     * @return void
     */
    private function mysqlVersionCheck()
    {
        /** @var string $currentDatabaseVersion - Текущая версия MySQL на сервере */
        $currentMysqlVersion = $this->connection->query('SELECT @@version')->fetch()['@@version'];
        if (strstr($currentMysqlVersion, '5.0.41') || strstr($currentMysqlVersion, '5.1.34')) {
            $this->logError('В текущей версии mysql возможны ошибки. Необходимо обновить версию');
            $this->result = false;
        }
    }

    /**
     * Сравниваем время, которое отдает веб-сервер, со временем, отдаваемым MySQL
     *
     * @return void
     */
    private function compareServerAndMysqlTimestamps()
    {
        /** @var string $serverTimestamp - Время, отдаваемое веб-сервером */
        $serverTimestamp = (new DateTime)->getTimestamp();
        /** @var string $mysqlTimestamp - Время, отдаваемое mysql */
        $mysqlTimestamp = strtotime($this->connection->query('SELECT NOW()')->fetch()['NOW()']);

        if (($serverTimestamp - $mysqlTimestamp) > 1) {
            $this->logError('Время, отдаваемое веб-сервером, отличается от времени, отдаваемым MySQL');
            $this->result = false;
        }
    }

    /**
     * Проверяем параметр mysql_mode
     *
     * @return void
     */
    private function checkSqlMode()
    {
        if ($this->connection->query('SELECT @@sql_mode')->fetch()['@@sql_mode']) {
            $this->logError('Необходимо установить параметру sql_mode значение ""');
            $this->result = false;
        }
    }

    /**
     * Проверяем кодировку и сравнение БД
     *
     * @return void
     */
    private function checkCharset()
    {
        /** @var \stdClass $charsetInfo - Объект, описывающий кодировку БД */
        $charsetInfo = $this->connection->query('SELECT *FROM information_schema.SCHEMATA
            WHERE schema_name = "' . $this->connection->getDatabase() . '"')->fetch();

        $sortField = 'sort';
        $sortOrder = 'asc';
        $sitesQuery = CSite::GetList($sortField, $sortOrder, []);
        while ($siteInfo = $sitesQuery->GetNext()) {
            if ($siteInfo['CHARSET'] == 'UTF-8') {
                if ($charsetInfo['DEFAULT_CHARACTER_SET_NAME'] != 'utf8') {
                    $this->logError('Кодировка сайта не совпадает с кодировкой БД');
                    $this->result = false;
                }
                if ($charsetInfo['DEFAULT_COLLATION_NAME'] != 'utf8_unicode_ci') {
                    $this->logError('Для сайта с кодировкой UTF-8 необходимо сравнение utf8_unicode_ci');
                    $this->result = false;
                }
            }
        }
    }

    /**
     * Получаем название проверки
     *
     * @return string
     */
    public function name()
    {
        return 'Проверка базы данных...';
    }
}
