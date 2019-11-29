<?php
/**
 * @var array $FIELDS
 */
use Bitrix\Main\Loader;
use Prodvigaeff\Bilet\Core\CategoryTable;

require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/prolog.php');

$moduleId = 'prodvigaeff.bilet';

if (!Loader::IncludeModule($moduleId))
    die('Module ' . $moduleId . ' not installed');

$permissions = $GLOBALS['APPLICATION']->GetGroupRight($moduleId);

if ($permissions <= 'D')
    $GLOBALS['APPLICATION']->AuthForm(GetMessage('ACCESS_DENIED'));

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$entityId = 'category';
$sTableID = 'tbl_'.$entityId.'_list';

$arHeaders = array(
    array('id' => 'id', 'content' => 'id', 'sort' => 'id', 'default' => true),
    array('id' => 'name', 'content' => 'Наименование', 'sort' => 'name', 'default' => true),
    array('id' => 'sort', 'content' => 'Сортировка', 'sort' => 'sort', 'default' => true)
);
$arFilter = [];
$filterFields = [
    [
        'id' => 'id',
        'name' => 'ID',
        'default' => true
    ],
    [
        'id' => 'name',
        'name' => 'Наименование',
        'default' => true
    ]
];

$oSort = new CAdminSorting($sTableID, "sort", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);
$lAdmin->InitFilter($arFilterFields);
$lAdmin->AddHeaders($arHeaders);
$USER_FIELD_MANAGER->AdminListAddFilterFields($entityId, $arFilterFields);
$USER_FIELD_MANAGER->AdminListAddHeaders($entityId, $arHeaders);
$USER_FIELD_MANAGER->AdminListAddFilterFieldsV2($entityId, $filterFields);
$arSelect = $lAdmin->getVisibleHeaderColumns();
$sortField = $oSort->getField();
$lAdmin->AddFilter($filterFields, $arFilter);

$action = $request->get('action_button_' . $sTableID);
if (($lAdmin->EditAction() || !empty($action)) && $permissions >= "W") {
    $ids = !is_array($request->get('ID')) ? [$request->get('ID')] : $request->get('ID');
    try {
        switch ($action) {
            case 'edit':
                foreach ($FIELDS as $id => $field) {
                    if(!$lAdmin->EditAction($id))
                        continue;
                    CategoryTable::update($id, $field);
                }
                break;
            case 'delete':
                foreach ($ids as $id) CategoryTable::delete($id);
                break;
        }
    } catch (Exception $e) {
        $lAdmin->AddGroupError($e->getMessage());
    }

} else if ($lAdmin->EditAction() && $permissions < "W") {
    $lAdmin->AddGroupError('Доступ запрещен');
}

$filterOption = new Bitrix\Main\UI\Filter\Options($sTableID);
$filterData = $filterOption->getFilter($filterFields);
$s = is_numeric($filterData['FIND']);
if (!empty($filterData['FIND'])) {
    if (is_numeric($filterData['FIND'])) {
        $arFilter['=id'] = $filterData['FIND'];
    } else {
        $arFilter['name'] = $filterData['FIND'];
    }

}

$nav = new Bitrix\Main\UI\PageNavigation('bilet_place');
$nav->setPageSize($lAdmin->getNavSize());
$nav->initFromUri();

$query = new Bitrix\Main\ORM\Query\Query(CategoryTable::getEntity());
$query->setSelect($arSelect);
$query->setOrder(array($sortField => $oSort->getOrder()));
$query->countTotal(true);
$query->setOffset($nav->getOffset());
$query->setLimit($nav->getLimit());

$arFilter = array_map(function ($value) {
    if (is_string($value) && !is_numeric($value)) {
        $value = '%' . $value . '%';
    }
    return $value;
}, $arFilter);
$query->setFilter($arFilter);

$result = $query->exec();
$nav->setRecordCount($result->getCount());
$lAdmin->setNavigation($nav, GetMessage("MAIN_USER_ADMIN_PAGES"), false);

while ($arRes = $result->fetch()) {
    $row =& $lAdmin->AddRow($arRes['id'], $arRes);
    foreach (['id', 'name'] as $key) {
        $row->AddViewField($key, '<a href="prodvigaeff_bilet_category.php?id='.$arRes['id'].'&lang='.LANG.'">'.$arRes[$key].'</a>');
    }

    $row->AddInputField("name");
    $row->AddInputField("sort");

    $arActions = [];
    if ($permissions >= 'W') {
        $arActions[] = [
            "ICON" => "report_vote",
            "DEFAULT" => true,
            "TEXT" => 'Редактировать',
            "ACTION" => $lAdmin->ActionRedirect('prodvigaeff_bilet_category.php?id=' . $arRes['id'])
        ];
        $arActions[] = [
            "ICON" => "report_vote",
            "DEFAULT" => true,
            "TEXT" => 'Удалить',
            "ACTION" => $lAdmin->ActionDoGroup($arRes['id'], 'delete')
        ];
    }
    $row->AddActions($arActions);
    $USER_FIELD_MANAGER->AddUserFields($entity_id, $arRes, $row);
}

$lAdmin->AddAdminContextMenu([], true);

$lAdmin->AddGroupActionTable(Array(
    "delete" => "удалить", // активировать выбранные элементы
    "edit" => "редактировать"
));

$aContext = [];
$aContext[] = array(
    "TEXT"	=> 'Добавить категорию',
    "LINK"	=> "prodvigaeff_bilet_category.php",
    "TITLE"	=> 'Добавить категорию',
    "ICON"	=> "btn_new"
);
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();
$APPLICATION->SetTitle('Площадки');
require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");

__halt_compiler();