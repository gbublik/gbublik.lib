<?
use Bitrix\Main\EventManager;

includeModuleLangFile(__FILE__);
if (class_exists('gbublik_lib'))
    return;

class gbublik_lib extends CModule {

    public $MODULE_ID = "gbublik.lib";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_CSS;
    public $MODULE_GROUP_RIGHTS = "Y";
    public $eventManager = null;

    /**
     * prodvigaeff_motivation constructor.
     */
    function __construct(){

        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = GetMessage('PRODVIGAEFF_LIB_INSTALL_NAME');
        $this->MODULE_DESCRIPTION  = GetMessage('PRODVIGAEFF_LIB_INSTALL_DESCRIPTION');
        $this->PARTNER_NAME = GetMessage('PRODVIGAEFF_LIB_PARTNER_INSTALL_NAME');
        $this->PARTNER_URI = GetMessage('PRODVIGAEFF_LIB_PARTNER_INSTALL_URL');

        $this->eventManager = EventManager::getInstance();
    }

    /**
     * Установить
     */
    function DoInstall()
    {
        global $APPLICATION;
        $RIGHT = $APPLICATION->GetGroupRight($this->MODULE_ID);
        if ($RIGHT >= "W") {
            $this->InstallFiles();
            $this->InstallDB(false);
            $this->InstallEvents();
            $APPLICATION->IncludeAdminFile(GetMessage("PRODVIGAEFF_LIB_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/local/modules/".$this->MODULE_ID."/install/step.php");
        } else {
            $APPLICATION->throwException('Нет доступа');
            return false;
        }
    }

    /**
     * Удалить
     */
    function DoUninstall()
    {
        global $APPLICATION, $step;
        $RIGHT = $APPLICATION->GetGroupRight($this->MODULE_ID);
        if ($RIGHT >= "W") {
            $this->UnInstallEvents();
            $this->UnInstallDB();
            $this->UnInstallFiles();
            $APPLICATION->IncludeAdminFile(GetMessage("PRODVIGAEFF_LIB_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/local/modules/".$this->MODULE_ID."/install/unstep.php");
        }
    }

    /**
     * Установка данных
     * @param bool $install_wizard
     * @return bool
     */
    function InstallDB($install_wizard = true)
    {

        RegisterModule($this->MODULE_ID);
        return true;
    }

    /**
     * Удаление данных
     * @param array $arParams
     * @return bool
     */
    function UnInstallDB($arParams = Array())
    {

        UnRegisterModule($this->MODULE_ID);
        return true;
    }

    /**
     * Инициализация обработчиков
     * @return bool
     */
    function InstallEvents()
    {
        //$this->_eventManager->registerEventHandler("main", "OnBuildGlobalMenu", $this->MODULE_ID, '\\Prodvigaeff\\Cmd\\Handlers', "DoBuildGlobalMenu");
        //$this->_eventManager->registerEventHandler("main", "OnProlog", $this->MODULE_ID, '\\Prodvigaeff\\Cmd\\BitrixHandlers', "onProlog");
        return true;
    }

    /**
     * Удаление обработчиков
     * @return bool
     */
    function UnInstallEvents()
    {
        //$this->_eventManager->unRegisterEventHandler("main", "OnProlog", $this->MODULE_ID, '\\Prodvigaeff\\Cmd\\BitrixHandlers', "onProlog");
        return true;
    }

    /**
     * Установка файлов
     * @return bool
     */
    function InstallFiles()
    {
        //CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/local/modules/".$this->MODULE_ID."/install/files", $_SERVER['DOCUMENT_ROOT'], true, true);
        return true;
    }

    /**
     * Удаление файлов
     * @return bool
     */
    function UnInstallFiles()
    {
        //DeleteDirFiles($_SERVER['DOCUMENT_ROOT']."/local/modules/prodvigaeff.cmd/install/admin", $_SERVER['DOCUMENT_ROOT']."/bitrix/admin");
        return true;
    }


}
