<?php

namespace Arrilot\BitrixSystemCheck\Checks\Bitrix;

use Arrilot\BitrixSystemCheck\Checks\Check;
use CSite;

/**
 * Класс для проверки настройки сайтов
 * Class SitesCheck
 * @package Arrilot\BitrixSystemCheck\Checks\Custom
 */
class SitesCheck extends Check
{
    /**
     * Запускаем проверку
     *
     * @return boolean
     */
    public function run()
    {
        $result = true;
        $sortField = 'sort';
        $sortOrder = 'asc';
        $sitesInfoQuery = CSite::GetList($sortField, $sortOrder, []);

        $sitesEncodings = [];
        while ($site = $sitesInfoQuery->GetNext()) {
            $sitesEncodings[] = $site['CHARSET'];

            if ($site['DOC_ROOT']) {
                if (!is_dir($site['DOC_ROOT'] . '/bitrix')) {
                    $this->logError('Отсутствует директория bitrix в корне проекта ' . $site['SERVER_NAME']);
                    $result = false;
                }
            } else {
                if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/bitrix')) {
                    $this->logError('Отсутствует директория bitrix в корне проекта ' . $site['SERVER_NAME']);
                    $result = false;
                }
            }
        }

        /** @var array $utfEncodingSites - Сайты, у которых кодировки == UTF_ */
        $utfEncodingSites = array_filter($sitesEncodings, function ($siteEncoding) {
            return $siteEncoding == 'UTF-8';
        });

        if (count($utfEncodingSites) == count($sitesEncodings) && count($utfEncodingSites) != 0) {
            $this->logError('У сайтов разные кодировки');
        }

        return $result;
    }

    /**
     * Получаем название проверки
     *
     * @return string
     */
    public function name()
    {
        return 'Проверка настроек сайтов...';
    }
}
