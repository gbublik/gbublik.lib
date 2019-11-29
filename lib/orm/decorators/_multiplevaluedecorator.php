<?php
namespace GBublik\Lib\Orm\Decorators;

use Bitrix\Main\Application;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\SystemException;

class MultipleValueDecorator extends DecoratorInterface
{
    /** @var string  */
    protected $field;

    protected $toField;

    protected $fromField;

    /** @var string  */
    protected $toEntity;

    protected $selectField;

    /**
     * MultipleValueDecorator constructor.
     * @param DecoratorInterface $decorator
     * @param string $field
     * @param string $toEntity
     * @param string $toField
     * @param string $fromField
     * @param string|null $selectField
     * @throws DecoratorException
     */
    public function __construct(DecoratorInterface $decorator, string $field, string $toEntity, string $toField,string $fromField = 'id', string $selectField = 'id')
    {
        if (array_key_exists($field, $decorator->getMap())) {
            throw new DecoratorException('Field '.$field.' exists in entity');
        }
        $this->field = $field;
        $this->toEntity = $toEntity;
        $this->toField = $toField;
        $this->fromField = $fromField;
        $this->selectField = $selectField;
        parent::__construct($decorator);
    }

    /**
     * @param array $params
     * @return Result
     * @throws SystemException
     */
    public function getList(array $params = [])
    {
        /*
        if (isset($params['select']) && in_array($this->field, $params['select'])) {
            $runtimeFieldsName = str_replace('.', '_', $this->field) . '_runtime';
            $params['runtime'][$runtimeFieldsName] =  [
                'data_type' => $this->toEntity,
                'reference' => [
                    '=this.' . $this->fromField => 'ref.' . $this->toField,
                ],
                'join_type' => 'inner'
            ];
            $params['runtime'][$this->field] = new ExpressionField(
                $this->field,
                'GROUP_CONCAT(%s)',
                [$runtimeFieldsName . '.' . $this->selectField]
            );
        }
        */
        return $this->decorator->getList($params);
    }

    public function update(int $primary, array $arFields)
    {
        $this->getOldData($primary);
        $rs =  $this->decorator->update($primary, $arFields);
        return $rs;
    }

    public function getOldData(int $primary)
    {
        static $data;
        if (is_null($data)) {
            /*
            $data = $this->getList([
                'select' => [
                    '*',
                    $this->field
                ]
            ]);
            */
        }
        return $data;
    }

    protected function getConnection()
    {
        return Application::getConnection();
    }
}