<?php
namespace GBublik\Lib\Orm\Decorators;

use Bitrix\Main\Type\DateTime;

/**
 * Автоматическое обновление даты
 * @package GBublik\Lib\Orm\Decorators
 */
class AutoDateUpdateDecorator extends DecoratorInterface
{
    /** @var string */
    protected $field;

    /**
     * AutoDateUpdateDecorator constructor.
     * @param DecoratorInterface $decorator
     * @param string $field
     * @throws DecoratorException
     */
    public function __construct(DecoratorInterface $decorator, string $field)
    {
        if (!array_key_exists($field, $decorator->getMap()))
            throw new DecoratorException(sprintf('Entity %s has no field %s', get_class($decorator), $field));

        if ($decorator->getMap()[$field]['data_type'] == 'date_time' || $decorator->getMap()[$field]['data_type'] == 'date') {
            $this->field = $field;
            parent::__construct($decorator);
        } else throw new DecoratorException(sprintf('Field %s not type date', $field));
    }

    public function add(array $arFields)
    {
        $arFields['date_update'] = new DateTime();
        return $this->decorator->add($arFields);
    }

    public function update(int $primary, array $arFields)
    {
        $arFields['date_update'] = new DateTime();
        return $this->decorator->update($primary, $arFields);
    }
}