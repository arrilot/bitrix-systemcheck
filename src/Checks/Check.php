<?php

namespace Arrilot\BitrixSystemCheck\Checks;

use Arrilot\BitrixSystemCheck\Monitorings\DataStorage;
use Arrilot\BitrixSystemCheck\Exceptions\SkipCheckException;

abstract class Check
{
    /**
     * @var array
     */
    protected $errorMessages = [];

    /**
     * @var DataStorage|null
     */
    protected $dataStorage = null;

    /**
     * @return boolean
     */
    abstract public function run();
    
    /**
     * @return string
     */
    abstract public function name();

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

    /**
     * Setter for data storage.
     * @param DataStorage $dataStorage
     * @return Check
     */
    public function setDataStorage(DataStorage $dataStorage)
    {
        $this->dataStorage = $dataStorage;

        return $this;
    }

    /**
     * Get Data from lastCheck.
     *
     * @return array
     */
    public function getPreviousData()
    {
        if (! $this->dataStorage) {
            return [];
        }

        $row = (array) $this->dataStorage->getData(get_class());
        if (!$row) {
            return [];
        }

        $data = json_decode($row['DATA'], true);
        if ($data === null) {
            $data =  [];
        }

        if (is_object($row['CREATED_AT'])) {
            $data['_created_at'] = $row['CREATED_AT']->getTimestamp();
        }

        return $data;
    }

    /**
     * Save current check Data to storage.
     * @param array $data
     * @return $this
     */
    public function saveData($data)
    {
        if ($this->dataStorage) {
            $this->dataStorage->saveData(get_class(), $data);
        }

        return $this;
    }
}
