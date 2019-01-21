<?php

namespace Arrilot\BitrixSystemCheck\Monitorings;

use Psr\Log\LoggerInterface;

abstract class Monitoring
{
    /**
     * @var DataStorage
     */
    protected $dataStorage;
    
    public $dataTtlDays = 7;
    
    public function __construct()
    {
        $this->dataStorage = new DataStorage(get_class());
    }

    /**
     * @return array
     */
    abstract function checks();

    /**
     * @return LoggerInterface|null
     */
    abstract function logger();

    /**
     * @return DataStorage
     */
    public function getDataStorage()
    {
        return $this->dataStorage;
    }
}
