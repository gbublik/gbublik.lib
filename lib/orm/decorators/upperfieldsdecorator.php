<?php
namespace GBublik\Lib\Orm\Decorators;


class UpperFieldsDecorator extends DecoratorInterface
{
    /** @var array  */
    protected $fromField;

    /** @var string */
    protected $toField;

    public function __construct(DecoratorInterface $decorator, array $fromField, $toField)
    {
        $this->fromField = $fromField;
        $this->toField = $toField;

        parent::__construct($decorator);
    }

    public function add(array $arFields)
    {
        return parent::add($this->modifierFields($arFields));
    }

    public function update(int $primary, array $arFields)
    {
        return parent::update($primary, $this->modifierFields($arFields));
    }

    protected function modifierFields(array  $fields)
    {
        $v = [];
        foreach ($this->fromField as $f) {
            if (array_key_exists($f, $fields)) {
                $fields[$f] = trim($fields[$f]);
                if (!empty($fields[$f])) $v[] = mb_strtoupper($fields[$f]);
            }
        }
        if (count($v) === count($this->fromField))
            $fields[$this->toField] = implode(', ', $v);
        return $fields;
    }
}