<?
$module_id = "arrilot.systemcheck";
$RIGHT = $APPLICATION->GetGroupRight($module_id);
if ($RIGHT >= "R") :
    
    IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
    IncludeModuleLangFile(__FILE__);
    
    $aTabs = array(
        array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "perfmon_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
    );
    $tabControl = new CAdminTabControl("tabControl", $aTabs);
    
    CModule::IncludeModule($module_id);
    
    if ($REQUEST_METHOD == "POST" && strlen($Update.$Apply.$RestoreDefaults) > 0 && $RIGHT == "W" && check_bitrix_sessid())
    {
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/prolog.php");
        
        if ($_REQUEST["clear_data"] === "y")
        {
            CPerfomanceComponent::Clear();
            CPerfomanceSQL::Clear();
            CPerfomanceHit::Clear();
            CPerfomanceError::Clear();
            CPerfomanceCache::Clear();
        }
        
        if (array_key_exists("ACTIVE", $_REQUEST))
        {
            $ACTIVE = intval($_REQUEST["ACTIVE"]);
            CPerfomanceKeeper::SetActive($ACTIVE > 0, time() + $ACTIVE);
        }
        
        if (strlen($RestoreDefaults) > 0)
        {
            COption::RemoveOption("perfmon");
        }
        else
        {
            foreach ($arAllOptions as $arOption)
            {
                $name = $arOption[0];
                $val = $_REQUEST[$name];
                if ($arOption[2][0] == "checkbox" && $val != "Y")
                    $val = "N";
                COption::SetOptionString("perfmon", $name, $val, $arOption[1]);
            }
        }
        
        ob_start();
        $Update = $Update.$Apply;
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
        ob_end_clean();
        
        if (strlen($_REQUEST["back_url_settings"]) > 0)
        {
            if ((strlen($Apply) > 0) || (strlen($RestoreDefaults) > 0))
                LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
            else
                LocalRedirect($_REQUEST["back_url_settings"]);
        }
        else
        {
            LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&".$tabControl->ActiveTabParam());
        }
    }
    
    ?>
    <form method="post" action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>">
        <?
        $tabControl->Begin();?>
        <? $tabControl->BeginNextTab(); ?>
        <? require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php"); ?>
        <? $tabControl->Buttons(); ?>
        <input <? if ($RIGHT < "W")
            echo "disabled" ?> type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>"
                               title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
        <input <? if ($RIGHT < "W")
            echo "disabled" ?> type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>"
                               title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
        <? if (strlen($_REQUEST["back_url_settings"]) > 0): ?>
            <input
                <? if ($RIGHT < "W") echo "disabled" ?>
                type="button"
                name="Cancel"
                value="<?=GetMessage("MAIN_OPT_CANCEL")?>"
                title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>"
                onclick="window.location='<? echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"])) ?>'"
            >
            <input
                type="hidden"
                name="back_url_settings"
                value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>"
            >
        <? endif ?>
        <input
            type="submit"
            name="RestoreDefaults"
            title="<? echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
            onclick="return confirm('<? echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')"
            value="<? echo GetMessage("MAIN_RESTORE_DEFAULTS") ?>"
        >
        <?=bitrix_sessid_post();?>
        <? $tabControl->End(); ?>
    </form>
<? endif; ?>
