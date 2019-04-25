<?php

namespace Arrilot\BitrixSystemCheck\Checks\Greensight;

use Arrilot\BitrixSystemCheck\Checks\Check;

class FrontendBuildIsProduction extends Check
{
    /**
     * @var string
     */
    protected $manifestPath;

    public function __construct($manifestPath)
    {
       $this->manifestPath = $manifestPath;
    }

    /**
     * @return string
     */
    public function name()
    {
        return "Проверка, что frontend build собран в production режиме...";
    }

    public function run()
    {
        $manifestPath = $this->manifestPath;
        $manifestData = file_get_contents($manifestPath);
        if ($manifestData === false) {
            $this->logError('Не удалось прочитать файл ' . $manifestPath);
            return false;
        }

        $manifest = json_decode($manifestData, true);
        if ($manifest === null && json_last_error() !== JSON_ERROR_NONE) {
            $this->logError('Не удалось декодировать файл ' . $manifestPath . ': ' . json_last_error());
            return false;
        }

        if (empty($manifest['mode'])) {
            $this->logError('В манифесте ' . $manifestPath . ' отсутсвует ключ mode, похоже собрана dev сборка');
            return false;
        }

        if (!in_array($manifest['mode'], ['prod', 'production'])) {
            $this->logError('mode !== prod || production');
            return false;
        }

        return true;
    }
}
