<?php

use Bitrix\Main\Loader;
use Prodvigaeff\Bilet\Core\AgentTable;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$moduleId = 'prodvigaeff.bilet';

if (!Loader::IncludeModule($moduleId))
    die('Module ' . $moduleId . ' not installed');

$permissions = $GLOBALS['APPLICATION']->GetGroupRight($moduleId);

if ($permissions <= 'D')
    $GLOBALS['APPLICATION']->AuthForm(GetMessage('ACCESS_DENIED'));

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();

if((strlen($request->get('Update')) > 0 || strlen($request->get('Apply')) > 0) && check_bitrix_sessid() && $permissions >= 'W') {


} elseif ((strlen($request->get('Update')) > 0 || strlen($request->get('Apply')) > 0) && check_bitrix_sessid() && $permissions < 'W') {
    $message = new CAdminMessage('Недостаточно прав для изменения');
}

if ($request->get('id') > 0) {
    $values = AgentTable::getById($request->get('id'))->fetch();
}
$aTabs = array(
    array(
        "DIV" => "edit1",
        "TAB" => "Агент",
        "ICON" => "pull_path",
        "TITLE" => ""
    )
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$contextMenu[] = [
    "TEXT"	=> "К списку",
    "LINK"	=> "prodvigaeff_bilet_agents_list.php",
    "TITLE"	=> "К списку",
    "ICON"	=> "btn_list"
];
$context = new CAdminContextMenu($contextMenu);

require_once ($DOCUMENT_ROOT.BX_ROOT."/modules/main/include/prolog_admin_after.php");
$APPLICATION->SetTitle('Агент: ' . (empty($values) ? 'добавление' : 'редактирование'));
$context->Show();
if (isset($message)) {
    echo $message->Show();
}
?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>">
    <?php echo bitrix_sessid_post()?>
    <input type="hidden" name="id" value="<?=$values['id']?>">
    <?
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    ?>
    <?if ($values['id'] > 0) :?>
        <tr>
            <td width="40%"><label>Дата создания:</label></td>
            <td width="60%">
                <?=$values['date_create']?>
            </td>
        </tr>
        <tr>
            <td width="40%"><label>Дата изменения:</label></td>
            <td width="60%">
                <?=$values['date_update']?>
            </td>
        </tr>
    <?endif?>
    <tr>
        <td width="40%"><label for="active">Активность:</label></td>
        <td width="60%">
            <input type="checkbox" id="active" value="Y" name="active"<?if ($values['active'] == 'Y' || isset($values['id']) === false):?> checked<?endif?>>
        </td>
    </tr>
    <?$tabControl->Buttons();?>
    <input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" class="adm-btn-save">
    <?=bitrix_sessid_post();?>
    <?$tabControl->End();?>
</form>
<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>
