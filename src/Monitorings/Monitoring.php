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
     * Russian monitoring name
     *
     * @return string
     */
    abstract public function name();
    
    /**
     * Monitoring code (id)
     *
     * @return string
     */
    abstract public function code();
    
    /**
     * Array of checks.
     *
     * @return array
     */
    abstract public function checks();

    /**
     * @return LoggerInterface|null
     */
    abstract public function logger();

    /**
     * @return DataStorage
     */
    public function getDataStorage()
    {
        return $this->dataStorage;
    }
}
