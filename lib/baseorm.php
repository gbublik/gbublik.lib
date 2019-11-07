<?php
namespace GBublik\Lib;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\Result;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\EventResult;
use \Bitrix\Main\DB\SqlQueryException;

abstract class BaseOrm extends DataManager
{
    public static function onBeforeUpdate(Event $event)
    {
        $result = new EventResult();
        $fields = $event->getParameter("fields");
        if (key_exists('date_update', static::getMap()) && !isset($fields['date_update']))
        {
            $fields['date_update'] = new Type\DateTime();
        }
        $result->modifyFields($fields);
        return $result;
    }

    public static function add(array $arFields)
    {
        try{
            return parent::add($arFields);
        } catch (SqlQueryException $e) {
            $msg = $e->getMessage();
            if (strpos($msg, 'Duplicate entry') !== false)
                throw new DuplicateElementException($e);
            elseif (strpos($msg, 'foreign key') !== false)
                throw new ForeignKeyException($e);
            else
                throw $e;
        }
    }

    public static function delete($primary)
    {
        try{
            return parent::delete($primary);
        } catch (SqlQueryException $e) {
            $msg = $e->getMessage();
            if (strpos($msg, 'foreign key') !== false)
                throw new ForeignKeyException($e);
            else
                throw $e;
        }
    }

    public static function update($primary, array $data)
    {
        try{
            return parent::update($primary, $data);
        } catch (SqlQueryException $e) {
            $msg = $e->getMessage();
            if (strpos($msg, 'foreign key') !== false)
                throw new ForeignKeyException($e);
            else
                throw $e;
        }
    }

    protected static function sendEvent(string $module, string $eventName, array $arParams,int $primary = null)
    {
        $arEventParameters = ['fields' => $arParams];
        if (!empty($primary)) $arEventParameters['primary'] = $primary;
        $event = new \Bitrix\Main\Event($module, $eventName, $arEventParameters);
        $event->send();
        foreach ($event->getResults() as $eventResult)
        {
            if($eventResult->getType() == \Bitrix\Main\EventResult::ERROR)
                return null;
            $arParams = array_merge($arParams, $eventResult->getParameters());
        }
        return $arParams;
    }

    /**
     * Создает таблицу сущности
     * @throws SqlQueryException
     * @throws ArgumentException
     * @throws SystemException
     */
    public static function install()
    {
        $db = Application::getConnection();
        $sql = self::getEntity()->compileDbTableStructureDump();
        $db->query($sql[0]);
    }

    /**
     * @throws SqlQueryException
     */
    public static function uninstall()
    {
        $db = Application::getConnection();
        $db->query('DROP TABLE ' . static::getTableName());
    }

    /**
     * Очишает таблицу сущности
     */
    public static function truncate()
    {
        global $DB;
        $DB->Query('TRUNCATE TABLE ' . static::getTableName());
    }

    /**
     * @throws ArgumentException
     * @throws SqlQueryException
     * @throws SystemException
     */
    public static function reinstall()
    {
        static::uninstall();
        static::install();
    }

    /**
     * @param $sql
     * @param array $arFields
     * @return Result|bool|null
     * @throws SqlQueryException
     */
    public static function querySql($sql, array $arFields)
    {
        $db = Application::getConnection();
        $keys = array_map(function ($key){
            return ':' . $key;
        }, array_keys($arFields));

        $sql = array_filter(
            explode(';', $sql),
            function ($value){
                $value = trim($value);
                if (!empty($value)) return trim($value);
                else return null;
            }
        );

        foreach ($arFields as $key=>&$value){
            if (strpos($key, 'table_') === false && !empty($value))
                $value = '\'' . $value . '\'';
            elseif (empty($value)) $value = 'NULL';
        }

        $c = count($sql);
        if ($c == 1) {
            try{
                return $db->query(str_replace($keys, $arFields, $sql[0]));
            } catch (SqlQueryException $e) {
                echo str_replace($keys, $arFields, $sql[0]);
                throw $e;
            }

        } elseif ($c > 1) {
            $db->startTransaction();
            foreach ($sql as $q) {
                try{
                    $db->query(str_replace($keys, $arFields, $q));
                } catch (SqlQueryException $e) {
                    echo str_replace($keys, $arFields, $q);
                    $db->rollbackTransaction();
                    throw $e;
                }
            }
            $db->commitTransaction();
            return true;
        }


        return null;
    }
}