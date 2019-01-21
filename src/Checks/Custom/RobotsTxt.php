<?php

namespace Arrilot\BitrixSystemCheck\Checks\Custom;

use Arrilot\BitrixSystemCheck\Checks\Check;

class RobotsTxt extends Check
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
     * @var bool
     */
    private $inProduction;
    
    public function __construct($mainPage, $inProduction ,$basicAuth = null)
    {
        $this->mainPage = $mainPage;
        $this->inProduction = $inProduction;
        $this->basicAuth = $basicAuth;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "Проверка содержимого robots.txt...";
    }

    /**
     * @return boolean
     */
    public function run()
    {
        $this->skip('Not implemented yet');
        $content = $this->getRobotsContent();
        if (empty($content)) {
            $this->logError('robots.txt пуст либо сломан');
            return false;
        }

        $contentAsArray = explode(PHP_EOL, $content);
        if ($contentAsArray[0] === "# Данный файл не отдается никаким роботам и служит лишь как заглушка на случай неправильной настройки веб-сервера") {
            $this->logError('Веб-сервер не настроен на корректную отдачу robots.txt');
            return false;
        }
    
        return $this->inProduction
            ? $this->checkForProductionContent($content)
            : $this->checkForDevContent($content);
    }

    /**
     * @param string $content
     * @return bool
     */
    protected function checkForProductionContent($content)
    {
        // TODO
        return true;
    }

    /**
     * @param string $content
     * @return bool
     */
    protected function checkForDevContent($content)
    {
        // TODO
        return true;
//        $index = array_search('User-agent: *', $content);
//
//        return $index !== false && !empty($content[$index + 1]) && $content[$index + 1] === 'Disallow: /';
    }
    
    /**
     * @return string|false
     */
    protected function getRobotsContent()
    {
        $context = null;
        if ($this->basicAuth) {
            $context = stream_context_create([
                'http' => [
                    'header'  => "Authorization: Basic " . base64_encode($this->basicAuth)
                ]
            ]);
        }

        return @file_get_contents($this->mainPage. 'robots.txt', false, $context);
    }
}
