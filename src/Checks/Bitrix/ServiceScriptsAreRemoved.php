<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;

/**
 * Класс для проверки отсутствия служебных скриптов в корне сайта
 * Class ServiceScriptsAreRemoved
 * @package Arrilot\BitrixSystemCheck\Checks\Bitrix
 */
class ServiceScriptsAreRemoved extends Check
{
    /** @var array $blackList - Список запрещенных файлов */
    private $blackList = [
        'restore.php',
        'bitrixsetup.php',
        '*.sql',
        '*.tar',
        '*.tar.gz',
        '*.tar.bz2'
    ];

    /** @var string $documentRoot - Директория, в которой будут просмотрены файлы */
    private $documentRoot;

    /** @var array $filesArray - Массив всех файлов в директории */
    private $filesArray;

    /**
     * ServiceScriptsAreRemoved constructor.
     */
    public function __construct()
    {
        $this->documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] . '/');
        $this->filesArray = scandir($this->documentRoot);
    }

    /**
     * Выполняем проверку
     *
     * @return boolean
     */
    public function run()
    {
        /** @var bool $result - Результат проверки */
        $result = true;
        foreach ($this->blackList as $file) {
            /** Если указано расширение файла, то смотрим все файл по регулярке, иначе по полному имени */
            if (preg_match('\'\\*.+\'', $file, $matches)) {
                /** @var string $extension - Расширение, по которому будет производиться поиск */
                $extension = substr($matches[0], 1);
                foreach ($this->filesArray as $key => $fileInDir) {
                    if (preg_match('\'' . $extension . '\'', $fileInDir)) {
                        $this->logErrorMessage($fileInDir);
                        $result = false;
                        unset($this->filesArray[$key]);
                    }
                }
            } elseif (in_array($file, $this->filesArray)) {
                $this->logErrorMessage($file);
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Логгируем ошибку
     *
     * @param $file - Название файла
     */
    private function logErrorMessage($file)
    {
        $this->logError('Необходимо удалить файл ' . $this->documentRoot . '/' . $file);
    }

    /**
     * Получаем название проверки
     *
     * @return string
     */
    public function name()
    {
        return 'Проверка наличия служебных скриптов в корне сайта...';
    }
}
