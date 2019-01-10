<?php

namespace Arrilot\BitrixSystemCheck\Monitorings;

use Psr\Log\LoggerInterface;

abstract class Monitoring
{
    /**
     * @return array
     */
    abstract function checks();

    /**
     * @return LoggerInterface|null
     */
    abstract function logger();
}
