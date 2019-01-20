<?php

namespace Arrilot\BitrixSystemCheck\Checks;

use Arrilot\BitrixSystemCheck\Exceptions\SkipCheckException;
use Bitrix\Main\Application;

abstract class Check
{
    /**
     * @var array
     */
    protected $errorMessages = [];

    /**
     * @return boolean
     */
    abstract public function run();
    
    /**
     * @return string
     */
    abstract public function getName();
    
    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->errorMessages;
    }

    /**
     * @param $message
     * @return void
     */
    protected function logError($message)
    {
        $this->errorMessages[] =  get_class($this) . ': ' . $message;
    }
    
    /**
     * Skip check.
     *
     * @param $message
     */
    protected function skip($message)
    {
        throw new SkipCheckException(get_class($this) . ': '. $message);
    }
    
    /**
     * @param array|string $extensions
     * @return bool
     */
    public function checkPhpExtensionsLoaded($extensions)
    {
        foreach ((array) $extensions as $extension) {
            if (!extension_loaded($extension)) {
                $this->logError('Не подключен модуль php: ' . $extension);
                return false;
            }
        }

        return true;
    }

    /**
     * @param array|string $extensions
     * @return bool
     */
    public function checkPhpExtensionsNotLoaded($extensions)
    {
        foreach ((array) $extensions as $extension) {
            if (extension_loaded($extension)) {
                $this->logError('Подключен нежелательный для данного окружения модуль php: ' . $extension);
                return false;
            }
        }

        return true;
    }
}