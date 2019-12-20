<?php
namespace GBublik\Lib;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

trait Search
{
    /**
     * (Пере)индексирует запись
     * @param int $id
     * @param array arFields массив $arFields необходимый для метода CSearch::Index
     * @return bool
     * @throws LoaderException
     */
    public static function reIndex(int $id, array $arFields)
    {
        if (!Loader::includeModule('search')) return false;

        $arFields['SITE_ID'] = ['s1'];
        $arFields['DATE_CHANGE'] = date('d.m.Y H:i:s');
        $arFields['PERMISSIONS'] = [
            '2'
        ];

        \CSearch::Index(
            __CLASS__,
            $id,
            $arFields
        );
        return true;
    }

    /**
     * Удаляет запись из поискового индекса модуля search
     * @param int $id
     * @return bool
     * @throws LoaderException
     */
    public static function delIndex(int $id)
    {
        if (!Loader::includeModule('search')) return false;
        \CSearch::DeleteIndex(
            __CLASS__,
            $id
        );
        return true;
    }

    public static function deleteAllIndex()
    {
        if (!Loader::includeModule('search')) return false;
        \CSearch::DeleteIndex(__CLASS__, '%');
    }
}