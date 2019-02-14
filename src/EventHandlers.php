<?php

namespace Arrilot\BitrixSystemCheck;

class EventHandlers
{
    public static function addMonitoringPageToAdminMenu(&$adminMenu, &$moduleMenu)
    {
        $toolsItems[] = array(
            "text" => GetMessage("MAIN_MENU_SYSTEM_CHECKER"),
            "url" => "site_checker.php?lang=".LANGUAGE_ID,
            "more_url" => array(),
            "title" => GetMessage("MAIN_MENU_SITE_CHECKER_ALT"),
        );
    }
}