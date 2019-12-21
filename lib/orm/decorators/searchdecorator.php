<?php
namespace GBublik\Lib\Orm\Decorators;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Exception;

/**
 * Поля картинок
 * @package GBublik\Lib\Orm
 */
class SearchDecorator extends DecoratorInterface
{
    /** @var string  */
    protected $module;

    /** @var string|array */
    protected $id;

    /** @var array */
    protected $fields;

    protected $additionalFields;

    protected $urlTemplate;

    /**
     * SearchDecorator constructor.
     * @param DecoratorInterface $decorator
     * @param string $module
     * @param array $fields
     * @param string|null $urlTemplate
     * @throws DecoratorException
     * @throws LoaderException
     */
    public function __construct(DecoratorInterface $decorator, string $module, array $fields = ['TITLE' => ['name']], string $urlTemplate = null)
    {
        if (!Loader::includeModule('search')) throw new DecoratorException('Модуль search не установлен');

        $this->module = $module;
        $this->id = 'id';
        $this->fields = $fields;
        $this->urlTemplate = $urlTemplate;
        parent::__construct($decorator);
    }

    public function add(array $arFields)
    {
        $rs = $this->decorator->add($arFields);
        if ($rs->isSuccess()) {
            if ($arFields['active'] == 'Y')
                $this->reIndex($arFields);
        }
        return $rs;
    }

    public function update(int $primary, array $arFields)
    {
        $rs = parent::update($primary, $arFields);
        if ($rs->isSuccess()) {
            $fields = $this->decorator->getList([
                'filter' => ['=id' => $rs->getId()],
                'select' => $this->getKeysForSelect()
            ])->fetch();
            if ($fields['active'] == 'Y') {
                $this->reIndex($fields);
            } else
                $this->delIndex(['id' => $rs->getId()]);
        }
        return $rs;
    }

    /**
     * @param int $primary
     * @return mixed
     * @throws Exception
     */
    public function delete(int $primary)
    {
        try {
            $rs = parent::delete($primary);
            $this->delIndex(['id' => $primary]);
            return $rs;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    protected function getKeysForSelect()
    {
        $keys = ['id', 'active'];
        foreach ($this->fields as $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    if (strpos($v, '.') !== false)
                        $keys[str_replace('.', '_', $v)] = $v;
                    else
                        $keys[] = $v;
                }
            } else
                $keys[] = $value;
        }
        if (!empty($this->urlTemplate)) {
            $matches = [];
            preg_match_all('/#(.*?)#/', $this->urlTemplate, $matches, PREG_PATTERN_ORDER);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $v) {
                    if (strpos($v, '.') !== false) {
                        $k = str_replace('.', '_', $v);
                    } else {
                        $k = $v;
                    }
                    if (!in_array($k, $keys)) {
                        $keys[$k] = $v;
                    }
                }
            }
        }

        return $keys;
    }

    protected function getSearchId(array $arFields)
    {
        $out = null;
        if (is_array($this->id)) {
            $out = [];
            foreach ($this->id as $key) {
                if (isset($arFields[$key])) $out[] = $arFields[$key];
            }
            $out = implode('|', $out);
        } else
            if (isset($arFields[$this->id])) $out = $arFields[$this->id];

        return $out;
    }

    protected function getSearchFields(array $arFields)
    {
        $out = [];
        foreach ($this->fields as $key => $field) {
            if (is_array($field)) {
                foreach ($field as $value) {
                    if (strpos($value, '.') !== false)
                        $value = str_replace('.', '_', $value);

                    if (isset($arFields[$value])) {
                        $arFields[$value] = trim($arFields[$value]);
                        if (empty($arFields[$value])) continue;
                        $out[$key][] = $arFields[$value];
                    }
                }
            } else if (isset($arFields[$field])) {
                $arFields[$field] = trim($arFields[$field]);
                if (empty($arFields[$field])) continue;
                $out[$key][] = $arFields[$field];
            }
        }
        foreach ($out as $key => &$field) {
            $field = implode(', ', $field);
        }
        return $out;
    }

    public function reIndex($fields)
    {
        $arFields = $this->getSearchFields($fields);
        $id = $this->getSearchId($fields);
        $arFields['SITE_ID'] = ['s1'];
        $arFields['DATE_CHANGE'] = date('d.m.Y H:i:s');
        $arFields['PERMISSIONS'] = [2];
        //$arFields['URL'] = '/catalog/' . $id . '/';

        if (!empty($this->urlTemplate)) {
            $arFields['URL'] = $this->urlTemplate;
            $matches = [];
            preg_match_all('/#(.*?)#/', $this->urlTemplate, $matches, PREG_PATTERN_ORDER);
            foreach ($matches[1] as $match) {
                if (strpos($match, '.') === false) {
                    $arFields['URL'] = str_replace('#'.$match . '#', $fields[$match], $arFields['URL']);
                } else {
                    $arFields['URL'] = str_replace(
                        '#' . $match . '#',
                        $fields[str_replace('.', '_', $match)] ,
                        $arFields['URL']
                    );
                }
            }
        }
        $arFields['BODY'] = strip_tags($arFields['BODY']);
        \CSearch::Index(
            $this->module,
            $id,
            $arFields,
            true
        );
        return true;
    }

    public function delIndex($fields)
    {
        $id = $this->getSearchId($fields);
        \CSearch::DeleteIndex(
            $this->module,
            $id
        );
        return true;
    }

    public function reindexAll($isDeleteOldIndex = true)
    {
        if ($isDeleteOldIndex) $this->deleteAllIndex();
        foreach ($this->decorator->getList([])->fetchAll() as $value)
            $this->update($value['id'], $value);
    }

    public function deleteAllIndex()
    {
        \CSearch::DeleteIndex($this->module, '%');
    }
}