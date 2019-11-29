<?php
namespace GBublik\Lib\Orm\Decorators;

use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\UpdateResult;
use CFile;
use Protobuf\Exception;

/**
 * Поле файл
 * @package GBublik\Lib\Orm\Decorators
 */
class FileDecorator extends DecoratorInterface
{
    protected $field;

    /**
     * @param DecoratorInterface $decorator
     * @param string $field
     * @throws DecoratorException
     */
    public function __construct(DecoratorInterface $decorator, string $field)
    {
        if (!array_key_exists($field, $decorator->getMap()))
            throw new DecoratorException(sprintf('Entity %s has no field %s', get_class($decorator), $field));

        if ($decorator->getMap()[$field]['data_type'] == 'integer') {
            $this->field = $field;
            parent::__construct($decorator);
        } else throw new DecoratorException(sprintf('Field %s not type date', $field));
    }

    /**
     * @param array $arFields
     * @return AddResult|null
     * @throws Exception
     * @throws DecoratorException
     */
    public function add(array $arFields)
    {
        $arFields = $this->uploadFile($arFields);
        $rs = null;
        try {
            $rs = $this->decorator->add($arFields);
            if (!$rs->isSuccess()) {
                CFile::Delete($arFields[$this->field]);
            }
        } catch (Exception $exception) {
            CFile::Delete($arFields[$this->field]);
            throw $exception;
        }
        return $rs;
    }


    /**
     * @param int $primary
     * @param array $arFields
     * @return UpdateResult
     * @throws DecoratorException
     * @throws Exception
     */
    public function update(int $primary, array $arFields)
    {
        $oldData = $this->decorator->getOldData($primary);
        if (array_key_exists($this->field, $arFields) && $oldData[$this->field] != $arFields[$this->field]) {
            CFile::Delete($oldData[$this->field]);
        }
        if (isset($arFields[$this->field])) {
            $arFields = $this->uploadFile($arFields);
            try {
                $rs = $this->decorator->update($primary, $arFields);
                if (!$rs->isSuccess()) {
                    CFile::Delete($arFields[$this->field]);
                }
                return $rs;
            } catch (Exception $exception) {
                CFile::Delete($arFields[$this->field]);
                throw $exception;
            }
        } else return $this->decorator->update($primary, $arFields);
    }

    public function delete(int $primary)
    {
        $oldData = $this->decorator->getOldData($primary);
        if ($oldData[$this->field] > 0) CFile::Delete($oldData[$this->field]);
        return $this->decorator->delete($primary);
    }

    /**
     * @param array $arFields
     * @return array
     * @throws DecoratorException
     */
    protected function uploadFile(array $arFields)
    {
        if (isset($arFields[$this->field]) && is_array($arFields[$this->field])) {
            if (empty($arFields[$this->field]['MODULE_ID'])) {
                throw new DecoratorException('On file field '.$this->field.' no set module_id. See documentation CFile::SaveFile');
            }
            $arFields[$this->field] = CFile::SaveFile($arFields[$this->field], $arFields[$this->field]['MODULE_ID']);
        }
        return $arFields;
    }
}