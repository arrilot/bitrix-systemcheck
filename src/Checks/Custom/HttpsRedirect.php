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
        $ch = curl_init();
        $url = str_replace('https://', 'http://', $this->mainPage);
    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 10000);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        if ($this->basicAuth) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->basicAuth);
        }

        $result = curl_exec($ch);
        if (!$result) {
            $this->logError('При curl запросе к '. $url . ' произошла ошибка ' . curl_error($ch));
            curl_close($ch);
            return false;
        }
        $info = curl_getinfo($ch);
        curl_close($ch);

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
