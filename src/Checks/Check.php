<?php

namespace Arrilot\BitrixSystemCheck\Checks;

use Arrilot\BitrixSystemCheck\Monitorings\DataStorage;
use Arrilot\BitrixSystemCheck\Exceptions\SkipCheckException;
use RuntimeException;

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
        $this->errorMessages[] = get_class($this) . ': ' . $message;
    }

    /**
     * Skip check.
     *
     * @param $message
     */
    protected function skip($message)
    {
        throw new SkipCheckException(get_class($this) . ': ' . $message);
    }

    /**
     * @param array|string $extensions
     * @return bool
     */
    public function checkPhpExtensionsLoaded($extensions)
    {
        foreach ((array)$extensions as $extension) {
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
        foreach ((array)$extensions as $extension) {
            if (extension_loaded($extension)) {
                $this->logError('Подключен нежелательный для данного окружения модуль php: ' . $extension);
                return false;
            }
        }

        return true;
    }

    /**
     * Setter for data storage.
     *
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
        if (!$this->dataStorage) {
            return [];
        }

        $row = (array)$this->dataStorage->getData(get_class());
        if (!$row) {
            return [];
        }

        $data = json_decode($row['DATA'], true);
        if ($data === null) {
            $data = [];
        }

        if (is_object($row['CREATED_AT'])) {
            $data['_created_at'] = $row['CREATED_AT']->getTimestamp();
        }

        return $data;
    }

    /**
     * Save current check Data to storage.
     *
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

    /**
     * @return bool
     */
    protected function inConsole()
    {
        return php_sapi_name() === 'cli';
    }

    /**
     * Attempt to run $callable $times times with $interval seconds interval until first successful attempt.
     *
     * @param callable $callable
     * @param int $times
     * @param int $interval - in seconds
     * @return mixed
     */
    protected function attempt(callable $callable, $times, $interval = 1)
    {
        $lastError = '';
        foreach (range(1, $times) as $i) {
            try {
                return $callable($i, $times);
            } catch (RuntimeException $e) {
                $lastError = $e->getMessage();
                sleep($interval);
            }
        }

        $this->logError($lastError);

        return null;
    }

    /**
     * @param string $url
     * @param null|string $basicAuth
     * @return bool|null|array
     */
    protected function getCurlInfo($url, $basicAuth = null)
    {
        return $this->attempt(function () use ($url, $basicAuth) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 10000);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            if ($basicAuth) {
                curl_setopt($ch, CURLOPT_USERPWD, $basicAuth);
            }

            $result = curl_exec($ch);
            if (!$result) {
                $this->logError('При curl запросе к ' . $url . ' произошла ошибка ' . curl_error($ch));
                curl_close($ch);
                return false;
            }
            $info = curl_getinfo($ch);
            curl_close($ch);

            return $info;
        }, 3);
    }
}
