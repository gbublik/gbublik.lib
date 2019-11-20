<?php

namespace GBublik\Lib\Orm;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type;

/**
 * Класс-пример, для копирования примеров различных решений ORM классов
 * @package GBublik\Lib
 */
class ExampleOrmTable extends BaseOrm
{
    /**
     * Различные типы полей
     * @return array
     */
    static function getMap()
    {
        return [
            'id' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => 'id'
            ),
            'date_create' => array(
                'data_type' => 'date_time',
                'title' => 'Время создания',
                'default_value' => function () {
                    return new Type\DateTime();
                },
                'required' => true
            ),
            'user_id' => array(
                'data_type' => 'integer',
                'title' => 'Автор транзакции',
                'required' => true
            ),
            'user' => array(
                'data_type' => 'Bitrix\Main\UserTable',
                'reference' => array(
                    '=this.user_id' => 'ref.ID'
                ),
                'join_type' => "inner"
            ),
            'active' => array(
                'data_type' => 'boolean',
                'title' => 'Активен',
                'values' => array('N', 'Y'),
                'default_value' => 'N'
            ),
        ];
    }

    /**
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function foo()
    {
        self::getList([
            'filter' => [],
            'select' => ['sum'],
            'runtime' => [
                new ExpressionField(
                    'sum',
                    'SUM(%s)',
                    array('points')
                )
            ],
            'group' => ['user_id']
        ]);

        return true;
    }
}