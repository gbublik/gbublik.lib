<?php
namespace GBublik\Lib\Orm\Decorators;

use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\ORM\Query\Result;

abstract class DecoratorInterface
{

    /** @var DecoratorInterface */
    protected $decorator;

    protected $field;

    public function __construct(DecoratorInterface $decorator){
        $this->decorator = $decorator;
    }

    /**
     * @param array $arFields
     * @return AddResult
     */
    public function add(array $arFields){
        return $this->decorator->add($arFields);
    }

    /**
     * @param int $primary
     * @param array $arFields
     * @return UpdateResult
     */
    public function update(int $primary, array $arFields){
        return $this->decorator->update($primary, $arFields);
    }

    public function delete(int $primary){
        return $this->decorator->delete($primary);
    }

    /**
     * @param array $arFields
     * @return Result
     */
    public function getList(array $arFields)
    {
        return $this->decorator->getList($arFields);
    }

    public function getMap()
    {
        return $this->decorator->getMap();
    }

    /**
     * @param $primary
     * @param array $params
     * @return Result
     */
    public function getByPrimary($primary, array $params = [])
    {
        return $this->decorator->getByPrimary($primary, $params);
    }

    public function getById(int $id)
    {
        return $this->decorator->getById($id);
    }

    public function getOldData(int $primary)
    {
        return $this->decorator->getOldData($primary);
    }
}