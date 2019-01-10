<?php

namespace Arrilot\BitrixSystemCheck\Checks;

use Arrilot\BitrixSystemCheck\Exceptions\SkipCheckException;

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
}