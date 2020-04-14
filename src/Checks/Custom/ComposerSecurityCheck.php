<?php

namespace Arrilot\BitrixSystemCheck\Checks\Custom;

use Arrilot\BitrixSystemCheck\Checks\Check;
use SensioLabs\Security\SecurityChecker;

class ComposerSecurityCheck extends Check
{
    /**
     * @var string
     */
    protected $pathToLockFile = '';

    public function __construct($pathToLockFile)
    {
        $this->pathToLockFile = $pathToLockFile;
    }
    
    /**
     * @return string
     */
    public function name()
    {
        return "Проверка зависимостей, подключенных через composer на предмет известных уязвимостей...";
    }

    /**
     * @return boolean
     */
    public function run()
    {
        if (!class_exists(SecurityChecker::class)) {
            $this->skip('Для выполнения данной проверки необходимо установить sensiolabs/security-checker');
        }

        $checker = new SecurityChecker();
        $result = $checker->check($this->pathToLockFile, 'json');
        $alerts = json_decode((string) $result, true);

        if ($alerts) {
            $this->logError('Найдены пакеты с известными уязвимостями ' . $result);
            return false;
        }

        return true;
    }
}
