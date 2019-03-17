<?php

namespace Arrilot\BitrixSystemCheck\Checks\Custom;

use Arrilot\BitrixSystemCheck\Checks\Check;
use RuntimeException;

class WwwRedirect extends Check
{
    /**
     * @var string
     */
    protected $mainPage;
    
    /**
     * @var string|null
     */
    protected $basicAuth;

    /**
     * Проверка no_www -> www редиректа вместо www -> no_www.
     *
     * @var bool
     */
    private $reverse;
    
    public function __construct($mainPage, $basicAuth = null, $reverse = false)
    {
        $this->mainPage = $mainPage;
        $this->basicAuth = $basicAuth;
        $this->reverse = $reverse;
    }

    /**
     * @return string
     */
    public function name()
    {
        return "Проверка редиректа с www на без www...";
    }

    /**
     * @return boolean
     */
    public function run()
    {
        $url = $this->reverse
            ? str_replace(['http://www.', 'https://www.'], ['http://', 'https://'], $this->mainPage)
            : str_replace(['http://', 'https://'], ['http://www.', 'https://www.'], $this->mainPage);

        $info = $this->getCurlInfo($url, $this->basicAuth);
        if (is_null($info)) {
            return false;
        }

        if ($info['http_code'] !== 301 && $info['http_code'] !== 302) {
            $this->logError('При curl запросе к '. $url . ' получен код ответа ' . $info['http_code'] . ' вместо 301/302');
            return false;
        }

        if (rtrim($info['redirect_url'], '/') !== rtrim($this->mainPage, '/')) {
            $this->logError('При curl запросе к '. $url . ' получен редирект на  ' . $info['redirect_url'] . ' вместо ' . $this->mainPage);
            return false;
        }

        return true;
    }
}
