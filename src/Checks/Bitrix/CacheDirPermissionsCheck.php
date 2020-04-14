<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;
use CSiteCheckerTest;

/**
 * Класс для проверки работы с файлами кеша
 * Class CacheDirPermissionsCheck
 * @package Arrilot\BitrixSystemCheck\Checks\Bitrix
 */
class CacheDirPermissionsCheck extends Check
{
    /**
     * Запускаем проверку
     *
     * @return boolean
     */
    public function run()
    {
        $dir = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/cache";
        $file0 = $dir."/".md5(mt_rand());
        $file1 = $file0.".tmp";
        $file2 = $file0.".php";
        if (!file_exists($dir)) {
            mkdir($dir, BX_DIR_PERMISSIONS);
        }

        $f = fopen($file1, 'wb');
        if (!$f) {
            $this->logError("Не удалось создать файл $file1 в директории $dir");
            fclose($f);
            return false;
        }

        fclose($f);

        if (!rename($file1, $file2)) {
            $this->logError("Не удалось создать переименовать $file1 в $file2 в директории $dir");
            return false;
        }

        if (!unlink($file2)) {
            $this->logError("Не удалось удалить $file2 в директории $dir");
            return false;
        }

        return true;
    }

    /**
     * Получаем название проверки
     *
     * @return string
     */
    public function name()
    {
        return 'Проверка работы с файлами кеша...';
    }
}
