<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;
use CSiteCheckerTest;

class PhpSettings extends Check
{
    /**
     * @return boolean
     */
    public function run()
    {
        $result = true;
        $minVersion = '5.3.0';
        if (version_compare($v = phpversion(), $minVersion, '<')) {
            $result = false;
            $this->logError('Версия PHP должна быть >=' . $minVersion);
        }

        $arRequiredParams = array(
            'safe_mode' => 0,
            'file_uploads' => 1,
            //			'session.cookie_httponly' => 0, # 14.0.1:main/include.php:ini_set("session.cookie_httponly", "1");
            'wincache.chkinterval' => 0,
            'session.auto_start' => 0,
            'magic_quotes_runtime' => 0,
            'magic_quotes_sybase' => 0,
            'magic_quotes_gpc' => 0,
            'arg_separator.output' => '&',
            'register_globals' => 0,
            'zend.multibyte' => 0
        );

        foreach($arRequiredParams as $param => $val) {
            if ($param === 'session.auto_start' && $this->inConsole()) {
                continue;
            }

            $cur = ini_get($param);
            if (strtolower($cur) == 'on')
                $cur = 1;
            elseif (strtolower($cur) == 'off') {
                $cur = 0;
            }

            if ($cur != $val) {
                $result = false;
                $this->logError(sprintf('Параметр %s = %s, требуется %s', $param, $cur ? htmlspecialcharsbx($cur) : 'off', $val ? 'on' : 'off'));
            }
        }

        $param = 'default_socket_timeout';
        if (($cur = ini_get($param)) < 60) {
            $result = false;
            $this->logError(sprintf('Параметр %s = %s, требуется %s', $param, $cur ? htmlspecialcharsbx($cur) : 'off', '60'));
        }

        if (!$this->inConsole() && ($m = ini_get('max_input_vars')) && $m < 10000) {
            $result = false;
            $this->logError(sprintf('Значение max_input_vars должно быть не ниже %s. Текущее значение: %s', 10000, $m));
        }

        if (($vm = getenv('BITRIX_VA_VER')) && version_compare($vm, '4.2.0','<')) {
            $result = false;
            $this->logError('Вы используете Битрикс веб-окружение старой версии, установите актуальную версию чтобы не было проблем с настройкой сервера.');
        }

        // check_divider
        $locale_info = localeconv();
        $delimiter = $locale_info['decimal_point'];
        if ($delimiter != '.') {
            $result = false;
            $this->logError(sprintf('Текущий разделитель: %s, требуется .', $delimiter));
        }

        // check_precision
        if (1234567891 != (string) doubleval(1234567891)) {
            $result = false;
            $this->logError('Параметр precision имеет неверное значение');
        }

        // check_suhosin
        if (in_array('suhosin',get_loaded_extensions()) && !ini_get('suhosin.simulation')) {
            $result = false;
            $this->logError(sprintf('Загружен модуль suhosin, возможны проблемы в работе административной части (suhosin.simulation=%s)', ini_get('suhosin.simulation') ? 1 : 0));
        }

        // check_backtrack_limit
        $param = 'pcre.backtrack_limit';
        $cur = CSiteCheckerTest::Unformat(ini_get($param));
        ini_set($param,$cur + 1);
        $new = ini_get($param);
        if ($new != $cur + 1) {
            $result = false;
            $this->logError('Нет возможности изменить значение pcre.backtrack_limit через ini_set');
        }

        return $result;
    }
    
    /**
     * @return string
     */
    public function name()
    {
        return "Корректность необходимых Битриксу настроек php...";
    }
}