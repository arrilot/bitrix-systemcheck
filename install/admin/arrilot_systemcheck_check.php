<?

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_CHECK", true);

require(
    file_exists($_SERVER["DOCUMENT_ROOT"]."/local/modules/arrilot.systemcheck/admin/arrilot_systemcheck_check.php")
    ? $_SERVER["DOCUMENT_ROOT"]."/local/modules/arrilot.systemcheck/admin/arrilot_systemcheck_check.php"
    :$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/arrilot.systemcheck/admin/arrilot_systemcheck_check.php"
);
