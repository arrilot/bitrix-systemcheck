<?php

namespace Arrilot\BitrixSystemCheck\Checks;

use Arrilot\BitrixSystemCheck\Checks\Custom\RobotsTxt;
use Arrilot\BitrixSystemCheck\Exceptions\SkipCheckException;

abstract class Check
{
    /**
     * @var array
     */
    protected $packageConfig;
    
    /**
     * @var array
     */
    protected $errorMessages = [];
    
    public function __construct($config)
    {
        $this->packageConfig = $config;
    }

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
    function getMessages()
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
     * Check options existence in bitrix config for package and skip check if they are missing
     * @param array $options
     */
    protected function skipIfMissingConfigOptions(array $options)
    {
        foreach ($options as $option) {
            if (!isset($this->packageConfig[$option])) {
                $this->skip('не заполнено поле bitrix-systemcheck.' . $option . ' в .settings_extra.php');
            }
        }
    }

    /**
     * Is app in production.
     *
     * @return bool
     */
    protected function inProduction()
    {
        $this->skipIfMissingConfigOptions(['env']);

        return in_array($this->packageConfig['env'], ['production', 'prod']);
    }
}