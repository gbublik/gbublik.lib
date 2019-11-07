# Библиотеки для разработки других модулей (alfa version)


## Установка
```console
cd local/modules/
git clone https://github.com/gbublik/gbublik.lib.git
```

### GBublik\Lib\BaseOrm
Абстракнтый класс с набором вспомогательных функций для работы с orm классами. 
Если orm сущность имеет поле date_update, то значение этого поле будет обновляться автоматически.

Пример использования
```php
namespace Vendor\Foo;

use GBublik\Lib\BaseOrm;

class MyOrm extend BaseOrm
{
    static function getTableName()
    {
        return 'my_orm';
    }
    
    static function getMap()
    {
      return [
        'id' => [
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => 'id'
            ],
            'date_create' => [
                'data_type' => 'date_time',
                'title' => 'Время создания',
                'default_value' => function () {
                    return new Type\DateTime();
                },
                'required' => true
            ],
            'date_update' => [
                'data_type' => 'date_time',
                'title' => 'Время обновления',
                'default_value' => function () {
                    return new Type\DateTime();
                },
                'required' => true
            ],
            'ip' => [
              'data_type' => 'string',
              'title' => 'IP пользователя',
              'required' => true
            ]
      ];
    }
}
```
#### GBublik\Lib\BaseOrm::install
Установить таблицу в db
```php
Vendor\Foo\MyOrm::install();
```

#### GBublik\Lib\BaseOrm::uninstall
Удалить таблицу в db
```php
Vendor\Foo\MyOrm::uninstall();
```

#### GBublik\Lib\BaseOrm::reinstall
Переустановить таблицу в db
```php
Vendor\Foo\MyOrm::reinstall();
```

#### GBublik\Lib\BaseOrm::querySql(string $sql, array $arFields) : Bitrix\Main\DB\Result | bool | null
Выполняет sql запрос(ы). Если запросов несколько, они должны быть разделены символом ;   
Ключи начинающиеся на table_ не будут экранироваться кавычками.
```php
    ...
    public static function install()
    {
        parent::install();
        $sql = '
            ALTER TABLE :table_name ADD INDEX( date_update ); 
            ALTER TABLE :table_name ADD UNIQUE(ip);
        ';
        self::querySql($sql, [
            'table_name' => self::getTableName()
        ]);
    }
```
```php
      static function addRow()
      {
          self::querySql(
              'INSERT INTO :table_name (date, ip) 
               VALUES(now(), :ip) 
               ON DUPLICATE KEY UPDATE date_create=now(), ip = :ip;',
              [
                  'table_name' => self::getTableName(),
                  'ip' => $_SERVER['REMOTE_ADDR']
              ]
          );
          // Будет сгенерирован запрос
          // INSERT INTO my_orm (date, ip) 
          // VALUES(now(), '127.0.0.1') 
          // ON DUPLICATE KEY UPDATE date_create=now(), ip = '127.0.0.1';
      }
        
```
