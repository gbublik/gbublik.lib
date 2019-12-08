<?php
namespace GBublik\Lib\Orm\Decorators;


use Bitrix\Main\ORM\Data\UpdateResult;
use CAllFile;

/**
 * Поля картинок
 * @package GBublik\Lib\Orm
 */
class PictureDecorator extends FileDecorator
{
    /** @var int  */
    protected $maxWidth;

    /** @var int  */
    protected $maxHeight;

    protected $resizeType;

    /** @var string */
    protected $field;

    public function setResize(int $width, int $height, int $resizeType = 1)
    {
        $this->maxWidth = $width;
        $this->maxHeight = $height;
        $this->resizeType = $resizeType;
    }

    public function add(array $arFields)
    {
        if (isset($arFields[$this->field]) && is_array($arFields[$this->field])) {
            $arFields[$this->field] = $this->resizeImage($arFields[$this->field]);
        }
        return parent::add($arFields);
    }

    /**
     * @param int $primary
     * @param array $arFields
     * @return UpdateResult
     * @throws Exception
     * @throws DecoratorException
     */
    public function update(int $primary, array $arFields)
    {
        if (isset($arFields[$this->field]) && is_array($arFields[$this->field])) {
            $arFields[$this->field] = $this->resizeImage($arFields[$this->field]);
        }
        return parent::update($primary, $arFields);
    }

    protected function resizeImage(array $arFile)
    {
        if (isset($this->maxWidth) && isset($this->maxHeight) && isset($this->resizeType)) {
            CAllFile::ResizeImage(
                $arFile,
                array(
                    'width' => $this->maxWidth,
                    'height' => $this->maxHeight
                ),
                $this->resizeType
            );
        }
        return $arFile;
    }
}