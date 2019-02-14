<?

require(
    file_exists($_SERVER["DOCUMENT_ROOT"]."/local/modules/arrilot.systemcheck/admin/arrilot_systemcheck_monitoring.php")
    ? $_SERVER["DOCUMENT_ROOT"]."/local/modules/arrilot.systemcheck/admin/arrilot_systemcheck_monitoring.php"
    :$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/arrilot.systemcheck/admin/arrilot_systemcheck_monitoring.php"
);
