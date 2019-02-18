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

    /**
     * Выполняем проверку
     *
     * @return boolean
     */
    public function run()
    {
        /** @var array $parameters - Параметры, которые необходимо проверить */
        $parameters = [
            [
                'name' => 'default_charset',
                'value' => 'UTF-8',
                'action' => 'checkIniSettings'
            ],
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

        foreach ($parameters as $parameter) {
            $action = $parameter['action'];
            $this->$action($parameter['name'], $parameter['value']);
        }

        return $this->result;
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
        }
    }

    /**
     * Получаем название проверки
     *
     * @return string
     */
    public function name()
    {
        return 'Параметры настройки UTF (mbstring и константа BX_UTF)';
    }
}
