<?php
namespace GBublik\Lib\Orm;

use GBublik\Lib\Orm\Decorators\DecoratorException;
use GBublik\Lib\Orm\Decorators\DecoratorInterface;

/**
 * Поля картинок
 * @package GBublik\Lib\Orm
 */
class PictureDecorator extends DecoratorInterface
{
    /** @var int  */
    protected $maxWidth;

    /** @var int  */
    protected $maxHeight;

    /** @var string */
    protected $field;

    /**
     * ПОле картинок
     * @param DecoratorInterface $decorator
     * @param string $field
     * @param int|null $maxWidth
     * @param int|null $maxHeight
     * @throws DecoratorException
     */
    public function __construct(DecoratorInterface $decorator, string $field, int $maxWidth = null, int $maxHeight = null)
    {
        $fields = $this->getMap();
        if (!array_key_exists($field, $fields))
            throw new DecoratorException(sprintf('Entity %s has no field %s', get_class($decorator), $field));

        if ($fields[$field]['data_type'] != 'integer' && !isset($fields[$field]['serialized']))
            throw new DecoratorException(sprintf('%s field must be a integer type', $field));
        elseif (($fields[$field]['data_type'] == 'string' || $fields[$field]['data_type'] == 'text') && !isset($fields[$field]['serialized'])) {
            throw new DecoratorException(sprintf('%s field must be a serialized', $field));
        }

        $this->field = $field;
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
        parent::__construct($decorator);
    }

    public function add(array $arFields)
    {
        //$fields = $this->getMap();
        return parent::add($arFields);
    }

    protected function getOldDataRow(int $id)
    {
        $rs = $this->decorator->getList([
            'filter' => [
                '=id' => $id
            ],
            'select' => [$this->field]
        ])->fetch();
    }
}