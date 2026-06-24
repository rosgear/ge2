# Пакет компонента Ge\Import входит в состав Ge Framework.

Пакет компонента Ge\Import предназанчен для импорта пакета данных компонентов приложения RosGear (модулей, расширений модулей).

Пакет данных (package.xml) - это файла в формате XML, где перечислены импортирыемы файлы.

Импортирыемы файлы - это файлы в формате: XML, JSON c выгруженными данными компонентов приложения RosGear (модулей, расширений модулей).

Компоненты, данные которых импортируются, должны иметь класса импорта "Import".

## Пример класса импорта данных компонента
```php
class Import extends \Ge\Import\Import
{
    protected string $modelClass = '\Ge\Backend\Articles\Model\Article';

    public function maskedAttributes(): array
    {
        return [
            'type_id' => ['field' => 'type_id', 'type' => 'int'],
            // ...
        ];
    }
}
```


## Пример схемы пакета данных в формате XML
```xml
<package>
    <title>Название пакета</title>
    <description>Описание пакета данных</description>
    <!-- язык пакета: ru-RU, en-Gb... -->
    <language>ru-RU</language>
    <!-- версия файла -->
    <version>1.0</version>
    <!-- дата и время создания файла в формате "d-m-Y H:i:s" в UTC -->
    <created>2025-01-01 11:11:11</created>
    <properties>
        <!-- дополнительные свойства пакета данных, которые могут применяться в будущем -->
        <property1>Значение 1</property1>
        <property2>Значение 2</property2>
        <!-- ... -->
    </properties>
    <components>
        <!-- описанием компонента с указанием файла -->
        <component>
            <!-- идент. установленого компонента -->
            <id>Rg.be.articles</id>
            <!-- тип компонента: "module" (модуль), "extension" (расширение модуля) -->
            <type>module</type>
            <!-- локальный путь к файлу -->
            <file>articles.xml</file>
        </component>
        <!-- ... -->
    </components>
    <files>
        <!-- копирование файлов в указанную директорию -->
        <file>
            <!-- имя файла с локальным путём относительно файла пакета данных -->
            <name>/src/dir/file.txt</name>
            <!-- локальный путь относительно приложения -->
            <path>/copy/to/dir/</path>
        </file>
        <!-- ... -->
    </files>
</package>
```

## Пример схемы импортируемого файла в формате XML
```xml
<data>
    <title>Название импортируемого файла</title>
    <description>Описание импортируемого файла</description>
    <!-- язык импортируемых данных: ru-RU, en-Gb... -->
    <language>ru-RU</language>
    <!-- версия файла -->
    <version>1.0</version>
    <!-- дата и время создания файла в формате "d-m-Y H:i:s" в UTC -->
    <created>2025-01-01 11:11:11</created>
    <!-- "1" - удалить все записи перед импортом, "0" - не удалять записи -->
    <clear>1</clear>
    <items>
        <!-- запись в виде полей со значениями -->
        <item>
            <field_1>value</field_1>
            <field_2>value</field_2>
            <!-- ... -->
        </item>
        <!-- ... -->
    <items>
</data>
```

## Пример импорта
### Импорт пакета данных
```php
$import = new \Ge\Import\Import();
$import->runPackage('/folder/package.xml');

$parser = $import->getParser();
if ($parser->hasErrors()) {
    echo $parser->getError();
}
```

### Импорт файла данных компонента
```php
$import = new \Ge\Import\Import();
$import->run('/folder/articles.xml');

$parser = $import->getParser();
if ($parser->hasErrors()) {
    echo $parser->getError();
}
```
