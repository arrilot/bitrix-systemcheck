<?php

use Arrilot\BitrixSystemCheck\Exceptions\FailCheckException;
use Arrilot\BitrixSystemCheck\Exceptions\SkipCheckException;
use Bitrix\Main\Config\Configuration;

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';

header('Content-Type: application/json');

\Bitrix\Main\Loader::includeModule('arrilot.systemcheck');
if ($APPLICATION->GetGroupRight('arrilot.systemcheck') < 'W') {
    $APPLICATION->AuthForm('Доступ запрещён');
}

if (!check_bitrix_sessid()) {
    echo json_encode([
        'result' => false,
        'message' => 'Ошибка в check_bitrix_sessid()',
    ]);
    die();
}

$config = Configuration::getInstance()->get('bitrix-systemcheck');
$monitorings = !empty($config['monitorings']) ? $config['monitorings'] : [];
$monitoringIsFound = false;
foreach ($monitorings as $monitoringClass) {
    /** @var \Arrilot\BitrixSystemCheck\Monitorings\Monitoring $monitoring */
    $monitoring = new $monitoringClass;
    if ($monitoring->code() == $_POST['monitoring']) {
        $monitoringIsFound = true;
        break;
    }
}

if (!$monitoringIsFound) {
    echo json_encode([
        'result' => false,
        'message' => 'Не найден мониторинг',
    ]);
    die();
}

$index = (int) $_POST['index'];

if ($index === 0) {
    $monitoring->getDataStorage()->cleanOutdatedData($monitoring->dataTtlDays);
}

foreach ($monitoring->checks() as $i => $check) {
    if ($i === $index) {
        $check->setDataStorage($monitoring->getDataStorage());
    
        $messages = [];
        $result = false;
        try {
            if ($check->run()) {
                $result = true;
            } else {
                $result = false;
                foreach ($check->getMessages() as $errorMessage) {
                    $messages[] = $errorMessage;
                }
            }
        } catch (SkipCheckException $e) {
            $result = null;
            $messages[] = $e->getMessage();
        } catch (FailCheckException $e) {
            $result = false;
            $messages[] = $e->getMessage();
        } catch (Exception $e) {
            $result = false;
            $messages[] = $e->getMessage();
        }

        echo json_encode([
            'result' => $result,
            'message' => implode('<br>', $messages),
        ]);
        die();
    }
}

echo json_encode([
    'result' => false,
    'message' => 'Проверка не найдена',
]);

