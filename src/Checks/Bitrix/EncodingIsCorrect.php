<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;
use Bitrix\Main\Config\Configuration;

/**
 * Класс для проверки параметров настройки UTF кодировки
 * Class EncodingIsCorrect
 * @package Arrilot\BitrixSystemCheck\Checks\Custom
 */
class EncodingIsCorrect extends Check
{
    /** @var bool $result - Результат проверки */
    private $result = true;

    /** @var string $encoding - Кодировка, которую мы проверяем */
    private $encoding;

    /**
     * EncodingIsCorrect constructor.
     *
     * @param string $encoding
     */
    public function __construct(string $encoding = 'UTF-8')
    {
        $this->encoding = strtoupper($encoding);
    }

    /**
     * Проверяем настройки для кодировки utf-8
     *
     * @return void
     */
    private function checkUtfSettings()
    {
        /** @var array $parameters - Параметры, которые необходимо проверить */
        $parameters = [
            [
                'name' => 'mbstring.func_overload',
                'value' => 2,
                'action' => 'checkIniSettings'
            ],
            [
                'name' => 'utf_mode',
                'value' => true,
                'action' => 'checkBitrixConfiguration'
            ],
            [
                'name' => 'BX_UTF',
                'value' => true,
                'action' => 'checkConstants'
            ]
        ];

        $this->checkEncodingSettings($parameters);
    }

    /**
     * Проверяем настройки для кодировки cp1251
     *
     * @return void
     */
    private function checkCp1251Settings()
    {
        /** @var array $parameters - Параметры, которые необходимо проверить */
        $parameters = [
            [
                'name' => 'mbstring.func_overload',
                'value' => 0,
                'action' => 'checkIniSettings'
            ],
            [
                'name' => 'mbstring.internal_encoding',
                'value' => 'cp1251',
                'action' => 'checkIniSettings'
            ]
        ];

        $this->checkEncodingSettings($parameters);
    }

    /**
     * Проверяем настройки кодировки
     *
     * @param array $parameters - Массив параметров, который необходимо проверить
     * @return void
     */
    private function checkEncodingSettings($parameters)
    {
        /** @var array $commonParameters - Параметры, общие для всех кодировок */
        $commonParameters = [
            [
                'name' => 'default_charset',
                'value' => $this->encoding,
                'action' => 'checkIniSettings'
            ]
        ];

        /** @var array $parameters - Массив параметров, которые необходимо проверить */
        $parameters = array_merge($parameters, $commonParameters);
        foreach ($parameters as $parameter) {
            $action = $parameter['action'];
            $this->$action($parameter['name'], $parameter['value']);
        }
    }

    /**
     * Выполняем проверки параметров в php.ini
     *
     * @param $name - Название параметра
     * @param $value - Значение, которому должен соответствовать параметр
     * @return void
     */
    private function checkIniSettings($name, $value)
    {
        if (ini_get($name) != $value) {
            $this->logError('Неверное значение ' . $name);
            $this->result = false;
        }
    }

    /**
     * Проверяем конфигурацию
     *
     * @param $name - Название параметра
     * @param $value - Значение, которому должен соответствовать параметр
     * @return void
     */
    private function checkBitrixConfiguration($name, $value)
    {
        if (Configuration::getInstance()->get($name) != $value) {
            $this->logError('Неверное значение ' . $name . ' в .settings.php');
            $this->result = false;
        }
    }

    /**
     * Проверяем константы
     *
     * @param $name - Название параметра
     * @param $value - Значение, которому должен соответствовать параметр
     * @return void
     */
    private function checkConstants($name, $value)
    {
        if ($name != $value) {
            $this->logError('Неверное значение ' . $name . ' в dbconn.php');
            $this->result = false;
        }
    }

    /**
     * Выполняем проверку
     *
     * @return boolean
     */
    public function run()
    {
        $action = null;
        switch ($this->encoding) {
            case 'UTF-8':
                $action = 'checkUtfSettings';
                break;
            case 'CP1251':
                $action = 'checkCp1251Settings';
                break;
        }

        $this->$action();
        return $this->result;
    }

    /**
     * Получаем название проверки
     *
     * @return string
     */
    public function name()
    {
        return 'Параметры настройки кодировки';
    }
}
