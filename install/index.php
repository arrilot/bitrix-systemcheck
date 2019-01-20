<?php

IncludeModuleLangFile(__FILE__);

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

class arrilot_bitrix_systemcheck extends CModule
{
    var $MODULE_ID = 'arrilot.bitrix-systemcheck';
    var $MODULE_DESCRIPTION = '';
    function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . '/version.php');
        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = 'Bitrix System Checks';
        $this->MODULE_DESCRIPTION = 'Производит мониторинг приложения';
        $this->MODULE_GROUP_RIGHTS = 'N';
    }
    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallFiles();
    }
    public function DoUninstall()
    {
        $this->UnInstallFiles();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }
    public function InstallFiles()
    {
        //создаем симлинк для того чтобы обносления после composer update сразу же отображались на сайте
        $this->_symlink('../modules/notagency.base/install/components/notagency', $_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/notagency');
        return true;
    }
    public function UnInstallFiles()
    {
        //удаляем симлинк
        $this->_unlink($_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/notagency');
        return true;
    }
    /**
     * A function to emulate symbolic links on Windows.
     * Uses the junction utility available at:
     * http://www.sysinternals.com
     * Note that this will only work on NTFS volumes.
     *
     * The syntax of the junction utility is:
     * junction <junction directory> <junction target>
     *
     * Note that the parameter order of the Junction command
     * is the reverse of the symlink function!
     *
     * @link http://php.net/manual/ru/function.symlink.php#70927
     *
     * @param string $target
     * @param string $link
     */
    private function _symlink($target, $link)
    {
        if ($_SERVER['WINDIR'] || $_SERVER['windir']) {
            exec('junction "' . $link . '" "' . $target . '"');
        } else {
            symlink($target, $link);
        }
    }
    private function _unlink($link)
    {
        if ($_SERVER['WINDIR'] || $_SERVER['windir']) {
            exec('junction -d "' . $link . '"');
        } else {
            @unlink($link);
        }
    }
}