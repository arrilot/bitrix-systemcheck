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

    /** @var string $charset - Кодировка, которую мы проверяем */
    private $charset;

    /** @var array $databaseCharsetSettings - Массив с необходимыми параметрами кодировок */
    private $databaseCharsetSettings = [
        'utf-8' => [
            'character' => 'utf8',
            'collations' => [
                'utf8_unicode_ci',
                'utf8_general_ci'
            ]
        ],
        'cp1251' => [
            'character' => 'cp1251',
            'collations' => [
                'cp1251_unicode_ci',
                'cp1251_general_ci'
            ]
        ]
    ];

    /** @var array $databaseCharsetInfo - Информация о кодировке базы данных */
    private $databaseCharsetInfo;

    /**
     * DataBaseCheck constructor.
     *
     * @param string $charset - Кодировка
     */
    public function __construct($charset = 'utf-8')
    {
        $this->charset = strtolower($charset);
        $this->connection = Application::getConnection();
        /** @var \stdClass $charsetInfo - Объект, описывающий кодировку БД */
        $this->databaseCharsetInfo = $this->connection->query('SELECT * FROM information_schema.SCHEMATA
            WHERE schema_name = "' . $this->connection->getDatabase() . '"')->fetch();
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
     * Проверяем кодировку подключения
     *
     * @return void
     */
    private function checkConnectionCharset()
    {
        $query = $this->connection->query("SHOW SESSION VARIABLES WHERE Variable_name = 'collation_connection'
            OR Variable_name = 'character_set_connection'");
        while ($info = $query->fetch()) {
            if ($info['Variable_name'] == 'character_set_connection') {
                if ($info['Value'] != $this->databaseCharsetSettings[$this->charset]['character']) {
                    $this->logError('Неверная кодировка подключения');
                    $this->result = false;
                }
            } elseif ($info['Variable_name'] == 'collation_connection') {
                if (!in_array($info['Value'], $this->databaseCharsetSettings[$this->charset]['collations'])) {
                    $this->logError('Неверное сравнение подключения');
                    $this->result = false;
                }
            }
        }
    }

    /**
     * Проверяем кодировку и сравнение БД
     *
     * @return void
     */
    private function checkDatabaseCharset()
    {
        $sortField = 'sort';
        $sortOrder = 'asc';
        $sitesQuery = CSite::GetList($sortField, $sortOrder, []);
        while ($siteInfo = $sitesQuery->GetNext()) {
            $charset = strtolower($siteInfo['CHARSET']);
            if ($this->databaseCharsetInfo['DEFAULT_CHARACTER_SET_NAME']
                != $this->databaseCharsetSettings[$charset]['character']) {
                $this->logError('Кодировка сайта не совпадает с кодировкой БД');
                $this->result = false;
            }
            if (!in_array(
                $this->databaseCharsetInfo['DEFAULT_COLLATION_NAME'],
                $this->databaseCharsetSettings[$charset]['collations']
            )) {
                $this->logError('Для сайта с кодировкой ' . $siteInfo['CHARSET']
                    . ' необходимо сравнение из списка'
                    . implode('; ', $this->databaseCharsetSettings[$charset]['collation']));
                $this->result = false;
            }
        }
    }

    /**
     * Проверяем кодировку всех таблиц в БД
     *
     * @return void
     */
    private function checkTablesCharset()
    {
        /** @var array $typesNotForCheck - Массив типов полей таблицы, у которых не надо смотреть кодировку */
        $typesNotForCheck = [
            'int',
            'timestamp',
            'datetime',
            'date',
            'blob',
            'mediumblob',
            'bigint',
            'decimal',
            'double',
            'tinyint',
            'smallint',
            'float',
            'longblob',
            'varbinary',
            'time'
        ];

        /** @var \Bitrix\Main\DB\MysqliResult $tablesQuery - Все таблицы и их поля в базе данных */
        $tablesQuery = $this->connection->query(
            'SELECT TABLE_NAME, TABLE_COLLATION, COLUMN_NAME, DATA_TYPE, COLLATION_NAME as COLUMN_COLLATION
            FROM information_schema.columns RIGHT JOIN information_schema.tables USING(TABLE_NAME)
            WHERE table_type = "base table"'
        );

        /**
         * @var array $anotherCharsetTables - Массив таблиц, у которых кодировка (или кодировка каких-либо их полей)
         * отличается от кодировки бд
         */
        $anotherCharsetTables = [];
        while ($table = $tablesQuery->fetch()) {
            if (!in_array($table['DATA_TYPE'], $typesNotForCheck)) {
                if ($table['TABLE_COLLATION'] != $this->databaseCharsetInfo['DEFAULT_COLLATION_NAME']) {
                    if (!array_key_exists($table['TABLE_NAME'], $anotherCharsetTables)) {
                        $anotherCharsetTables[$table['TABLE_NAME']] = [];
                    }
                }
                if ($table['COLUMN_COLLATION'] != $this->databaseCharsetInfo['DEFAULT_COLLATION_NAME']) {
                    $anotherCharsetTables[$table['TABLE_NAME']][] = $table['COLUMN_NAME'];
                }
            }
        }

        /** @var int $errorTablesCount - Количество таблиц, в которых кодировка отличается от кодировки БД */
        $errorTablesCount = count($anotherCharsetTables);
        if ($errorTablesCount) {
            $this->logError('Найдено ' . $errorTablesCount . ' таблиц (и полей в них) с отличными от БД кодировками: '
                . implode('; ', array_keys($anotherCharsetTables)));
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
        $this->mysqlVersionCheck();
        $this->compareServerAndMysqlTimestamps();
        $this->checkSqlMode();
        $this->checkConnectionCharset();
        $this->checkDatabaseCharset();
        $this->checkTablesCharset();

        return $this->result;
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
