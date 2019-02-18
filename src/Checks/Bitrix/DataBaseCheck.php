<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;
use Bitrix\Main\Config\Configuration;
use CSite;
use DateTime;
use mysqli;

/**
 * Класс для тестирования базы данных
 * Class DataBaseCheck
 * @package Arrilot\BitrixSystemCheck\Checks\Bitrix
 */
class DataBaseCheck extends Check
{
    /** @var bool $result - Результат проверки */
    private $result = true;

    /** @var mysqli $connection - Объект подключения к БД */
    private $connection;

    /**
     * DataBaseCheck constructor.
     */
    public function __construct()
    {
        /** @var array $databaseInfo - Информация для подключения к БД из файла .settings_extra.php */
        $databaseInfo = Configuration::getInstance()->get('connections')['default'];
        $this->connection = mysqli_connect(
            $databaseInfo['host'],
            $databaseInfo['login'],
            $databaseInfo['password'],
            $databaseInfo['database']
        );
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
        $currentMysqlVersion = mysqli_get_server_info($this->connection);
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
        $mysqlTimestamp = strtotime($this->connection->query('SELECT NOW()')->fetch_assoc()['NOW()']);

        if ($serverTimestamp != $mysqlTimestamp) {
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
        if ($this->connection->query('SELECT @@sql_mode')->fetch_assoc()['@@sql_mode"']) {
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
        $charsetInfo = $this->connection->get_charset();

        $sortField = 'sort';
        $sortOrder = 'asc';
        $sitesQuery = CSite::GetList($sortField, $sortOrder, []);
        while ($siteInfo = $sitesQuery->GetNext()) {
            if ($siteInfo['CHARSET'] == 'UTF-8') {
                if ($charsetInfo->charset != 'utf8') {
                    $this->logError('Кодировка сайта не совпадает с кодировкой БД');
                    $this->result = false;
                }
                if ($charsetInfo->collation != 'utf8_unicode_ci') {
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
