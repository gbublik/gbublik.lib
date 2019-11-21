<?php
namespace GBublik\Lib\Orm\Decorators;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\SystemException;
use Exception;

/**
 * Вчегда должен быть первым декоратором, иначе будет работать неправильно
 * @package GBublik\Lib\Orm\Decorators
 */
class OrmDecorator extends DecoratorInterface
{
    /** @var DataManager  */
    protected $ormEntity;

    public function __construct(DataManager $entity)
    {
        $this->ormEntity = $entity;
        parent::__construct($this);
    }

    /**
     * @param array $arFields
     * @return AddResult
     * @throws Exception
     */
    public function add(array $arFields)
    {
        return $this->ormEntity::add($arFields);
    }

    /**
     * @param int $primary
     * @param array $arFields
     * @return UpdateResult
     * @throws Exception
     */
    public function update(int $primary, array $arFields)
    {
        return $this->ormEntity::update($primary, $arFields);
    }

    /**
     * @param array $arField
     * @return Result
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getList(array $arField)
    {
        return $this->ormEntity::getList($arField);
    }

    public function getMap()
    {
        return $this->ormEntity::getMap();
    }

    public function getByPrimary($primary, array $params = [])
    {
        $this->ormEntity::getByPrimary($primary, $params);
    }

    /**
     * Все остальные методы мапятся через магию, IDE не затащит
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        call_user_func($this->ormEntity::{$name}, $arguments);
    }
}