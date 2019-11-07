<?php
namespace GBublik\Lib;

use Bitrix\Main\FileTable;
use Bitrix\Main\UserTable;

class UserFacadeTable extends UserTable
{
    static function getMap()
    {
        $map = parent::getMap();

        $map['GROUP'] = [
            'data_type' => 'Bitrix\Main\UserGroupTable',
            'reference' => array(
                '=this.ID' => 'ref.USER_ID'
            ),
            'join_type' => "left"
        ];
        $map['PERSONAL_PHOTO_FILE'] = [
            'data_type' => 'Bitrix\Main\FileTable',
            'reference' => array(
                '=this.PERSONAL_PHOTO' => 'ref.ID'
            ),
            'join_type' => "left"
        ];
        return $map;
    }
}