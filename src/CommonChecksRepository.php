<?php

namespace Arrilot\BitrixSystemCheck;

use Arrilot\BitrixSystemCheck\Checks\Bitrix\ApacheModules;
use Arrilot\BitrixSystemCheck\Checks\Bitrix\PhpSettings;
use Arrilot\BitrixSystemCheck\Checks\Bitrix\RequiredPhpModules;
use Arrilot\BitrixSystemCheck\Checks\Custom\BasicAuth;
use Arrilot\BitrixSystemCheck\Checks\Custom\RobotsTxt;
use Arrilot\BitrixSystemCheck\Checks\Custom\WwwRedirect;

class CommonChecksRepository
{
    public function getChecks()
    {
        return [
            RequiredPhpModules::class,
            PhpSettings::class,
            ApacheModules::class,
            BasicAuth::class,
            RobotsTxt::class,
            WwwRedirect::class,
        ];
    }
}