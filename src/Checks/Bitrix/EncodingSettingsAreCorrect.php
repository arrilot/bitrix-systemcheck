<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;
use Bitrix\Main\Config\Configuration;

/**
 * Класс для проверки параметров настройки UTF кодировки
 * Class EncodingIsCorrect
 * @package Arrilot\BitrixSystemCheck\Checks\Custom
 */
class EncodingSettingsAreCorrect extends Check
{
    /**
     * @var bool пропускать проверку на func_overload для консоли.
     */
    protected $skipFuncOverloadForConsole;

    /** @var bool $result - Результат проверки */
    private $result = true;

    /** @var string $encoding - Кодировка, которую мы проверяем */
    private $encoding;

    /**
     * EncodingIsCorrect constructor.
     *
     * @param string $encoding
     * @param bool $skipFuncOverloadForConsole
     */
    public function __construct($encoding = 'UTF-8', $skipFuncOverloadForConsole = false)
    {
        $this->encoding = strtoupper($encoding);
        $this->skipFuncOverloadForConsole = $skipFuncOverloadForConsole;
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

        if (!$this->inConsole() || !$this->skipFuncOverloadForConsole) {
            $parameters[] = [
                'name' => 'mbstring.func_overload',
                'value' => 2,
                'action' => 'checkIniSettings'
            ];
        }

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
                'name' => 'utf_mode',
                'value' => false,
                'action' => 'checkBitrixConfiguration'
            ],
        ];

        if (!$this->inConsole() || !$this->skipFuncOverloadForConsole) {
            $parameters[] = [
                'name' => 'mbstring.func_overload',
                'value' => 0,
                'action' => 'checkIniSettings'
            ];
        }

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
        $commonParameters = [];
//        $commonParameters = [
//            [
//                'name' => 'default_charset',
//                'value' => $this->encoding,
//                'action' => 'checkIniSettings'
//            ]
//        ];

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
            $valueString = $this->formatValuesAsString($value);
            $this->logError("В php.ini параметр $name должен иметь значение $valueString");
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
            $valueString = $this->formatValuesAsString($value);
            $this->logError("В .settings.php параметр $name должен иметь значение $valueString");
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
        if (constant($name) != $value) {
            $valueString = $this->formatValuesAsString($value);
            $this->logError("В dbconn.php константа $name должна иметь значение $valueString");
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
        $this->encoding === 'UTF-8' ? $this->checkUtfSettings() : $this->checkCp1251Settings();

        return $this->result;
    }

    /**
     * Получаем название проверки
     *
     * @return string
     */
    public function name()
    {
        return 'Проверка параметров зависящих от кодировки сайта...';
    }

    /**
     * @param $value
     * @return string
     */
    protected function formatValuesAsString($value)
    {
        if ($value === false) {
            return "false";
        }

        if ($value === null) {
            return "null";
        }

        if ($value === true) {
            return "true";
        }

        return (string) $value;
    }
}
