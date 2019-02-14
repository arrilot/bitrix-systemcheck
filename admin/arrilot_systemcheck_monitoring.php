<?php

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_CHECK", true);

use Bitrix\Main\Config\Configuration;

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';

global $APPLICATION;
\Bitrix\Main\Loader::includeModule('arrilot.systemcheck');

if ($APPLICATION->GetGroupRight('arrilot.systemcheck') < 'W') {
    $APPLICATION->AuthForm('Доступ запрещён');
}

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php';

$APPLICATION->SetTitle('Мониторинг');

if (empty($_GET['code'])) {
    CAdminMessage::ShowMessage(array('MESSAGE' => 'Не указан код мониторинга', 'TYPE' => 'ERROR'));
    return;
}

$config = Configuration::getInstance()->get('bitrix-systemcheck');
$monitorings = !empty($config['monitorings']) ? $config['monitorings'] : [];
$monitoringIsFound = false;
foreach ($monitorings as $monitoringClass) {
    /** @var \Arrilot\BitrixSystemCheck\Monitorings\Monitoring $monitoring */
    $monitoring = new $monitoringClass;
    if ($monitoring->code() == $_GET['code']) {
        $monitoringIsFound = true;
        break;
    }
}


if (!$monitoringIsFound) {
    CAdminMessage::ShowMessage(array('MESSAGE' => 'Мониторинг с указанным кодом не найден', 'TYPE' => 'ERROR'));
    return;
}

$checks = $monitoring->checks();

$APPLICATION->SetTitle('Мониторинг "' . $monitoring->name() . '"');

?>
    <style>
        .sc_icon {
            display: inline-block;
            height:25px;
            margin-right:10px;
            vertical-align: middle;
            width:25px;
        }

        .sc_icon_success {
            background: url("/bitrix/themes/.default/icons/status_icons.png") no-repeat scroll -14px -19px transparent;
        }

        .sc_icon_warning{
            background: url("/bitrix/themes/.default/icons/status_icons.png") no-repeat scroll -12px -212px transparent;
        }

        .sc_icon_error{
            background: url("/bitrix/themes/.default/icons/status_icons.png") no-repeat scroll -12px -73px transparent;
        }

        .sc_success {
            color:#408218 !important;
            vertical-align: middle;
        }

        .sc_warning {
            color:#000000;
            vertical-align: middle;
        }

        .sc_error {
            color:#DD0000 !important;
            vertical-align: middle;
        }
    </style>
    <p>
        <input type="button" value="Начать тестирование" id="test_start" onclick="start_monitoring()" class="adm-btn-green">
    </p>
    <table class="adm-list-table" id="arrilot_monitoring_checks_table">
        <thead>
        <tr class="adm-list-table-header">
            <td class="adm-list-table-cell">
                <div class="adm-list-table-cell-inner">Проверка</div>
            </td>
            <td class="adm-list-table-cell">
                <div class="adm-list-table-cell-inner">Результат</div>
            </td>
        </tr>
        </thead>
        <tbody>
        <? foreach ($checks as $i => $check): ?>
            <tr class="adm-list-table-row check">
                <td class="adm-list-table-cell" style="width: 40%;"><?= htmlspecialcharsbx($check->name()) ?></td>
                <td class="adm-list-table-cell adm-list-table-cell-last" id="check<?= (int) $i ?>result">&nbsp;</td>
        <? endforeach ?>
        </tbody>
    </table>
    <script>
        var totalChecks = <?= count($checks) ?>;
        var currentCheckIndex = 0;
        function start_monitoring() {
            document.getElementById('test_start').disabled = 'disabled' ;
            document.getElementById('test_start').value = 'В процессе' ;
            perform_next_check();
        }
        
        function perform_next_check() {
            if (currentCheckIndex >= totalChecks) {
                document.getElementById('test_start').disabled = '' ;
                document.getElementById('test_start').value = 'Завершено. Перезапустить?' ;
                currentCheckIndex = 0;
                return;
            }

            var resultContainer = document.getElementById('check' + currentCheckIndex + 'result');
            BX.ajax({
                url: 'arrilot_systemcheck_check.php',
                data: {
                    'monitoring': "<?= htmlspecialcharsbx($monitoring->code()) ?>",
                    'index': currentCheckIndex,
                    'sessid': "<?= bitrix_sessid() ?>"
                },
                method: 'POST',
                dataType: 'json',
                timeout: 60,
                async: true,
                onsuccess: function(data) {
                    currentCheckIndex++;
                    if (data.result === true) {
                        resultContainer.innerHTML = '<div class="sc_icon sc_icon_success"></div><span class="sc_success">' + data.message + '</span>';
                    }
                    if (data.result === null) {
                        resultContainer.innerHTML = '<div class="sc_icon sc_icon_warning"></div><span class="sc_warning">' + data.message + '</span>';
                    }
                    if (data.result === false) {
                        resultContainer.innerHTML = '<div class="sc_icon sc_icon_error"></div><span class="sc_error">' + data.message + '</span>';
                    }
                    perform_next_check()
                },
                onfailure: function() {
                    currentCheckIndex++;
                    resultContainer.innerHTML = '<div class="sc_icon sc_icon_error"></div><span class="sc_error">Аякс запрос заверщился ошибкой</span>';
                    perform_next_check();
                }
            });
        }

        <?=$_REQUEST['start_test'] ? 'window.setTimeout(\'start_monitoring\', 500);' : ''?>
    </script>

<? require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php';
