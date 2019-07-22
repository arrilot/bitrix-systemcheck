<?php

namespace Arrilot\BitrixSystemCheck\Checks\Custom;

use Arrilot\BitrixSystemCheck\Checks\Check;

class HttpsRedirect extends Check
{
    /**
     * @var string
     */
    protected $mainPage;

    /**
     * @var string|null
     */
    protected $basicAuth;

    public function __construct($mainPage, $basicAuth = null)
    {
        $this->mainPage = $mainPage;
        $this->basicAuth = $basicAuth;
    }

    /**
     * @return string
     */
    public function name()
    {
        return "Проверка редиректа с http:// на https://...";
    }

    /**
     * @return boolean
     */
    public function run()
    {
        $url = str_replace('https://', 'http://', $this->mainPage);
        $info = $this->getCurlInfo($url, $this->basicAuth);

        if (is_null($info)) {
            return false;
        }

        if (!isset($info['http_code'])) {
            return false;
        }

        if ($info['http_code'] !== 301 && $info['http_code'] !== 302) {
            $this->logError('При curl запросе к ' . $url . ' получен код ответа ' . $info['http_code'] . ' вместо 301/302');
            return false;
        }

        if (rtrim($info['redirect_url'], '/') !== rtrim($this->mainPage, '/')) {
            $this->logError('При curl запросе к ' . $url . ' получен редирект на  ' . $info['redirect_url'] . ' вместо ' . $this->mainPage);
            return false;
        }

        return true;
    }
}
