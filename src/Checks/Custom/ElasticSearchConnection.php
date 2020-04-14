<?php

namespace Arrilot\BitrixSystemCheck\Checks\Custom;

use Arrilot\BitrixSystemCheck\Checks\Check;

/**
 * Class ElasticSearchConnection
 * @package System\SystemCheck\Checks
 */
class ElasticSearchConnection extends Check
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * ElasticSearchConnection constructor.
     * @param string $host
     * @param int $port
     */
    public function __construct($host = 'localhost', $port = 9200)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function name()
    {
        return "Проверка доступности соединения с ElasticSearch...";
    }

    /**
     * @return boolean
     */
    public function run()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://{$this->host}:{$this->port}");
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 10000);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        if (!$result) {
            $this->logError('Отсутствует соединение с ElasticSearch (' . curl_error($ch) . ')');
            curl_close($ch);
            return false;
        }
        curl_close($ch);

        return true;
    }
}
