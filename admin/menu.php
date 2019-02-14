<?php

use Bitrix\Main\Config\Configuration;

$items = [];
$config = Configuration::getInstance()->get('bitrix-systemcheck');
$monitorings = !empty($config['monitorings']) ? $config['monitorings'] : [];
foreach ($monitorings as $monitoringClass) {
    /** @var \Arrilot\BitrixSystemCheck\Monitorings\Monitoring $monitoring */
    $monitoring = new $monitoringClass;
    $items[] = [
        'text' => $monitoring->name(),
        'title' =>  $monitoring->name(),
        'url' => 'arrilot_systemcheck_monitoring.php?code=' . $monitoring->code(),
    ];
}

return [
    'parent_menu' => 'global_menu_settings',
    'sort' => 2800,
    'text' => 'Мониторинги',
    'title' => 'Мониторинги',
    'icon' => 'util_menu_icon',
    'page_icon' => 'util_page_icon',
    'items_id' => 'menu_monitorings',
    'items' => $items,
];