<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Terms;

use Ge;
use Ge\Stdlib\BaseObject;

/**
 * Terms применяется для формирования терминов компонентов (модуль, расширение, виджет, 
 * плагин) веб-приложения, идентификаторы которых, используются для установления связей 
 * "многие-ко-многим" в таблицах баз данных.
 * 
 * Terms - это менеджер терминов, доступ к которому можно получить через `Ge::$app->terms`.
 * 
 * Термины добавляются при установке компонентов веб-приложения.
 * 
 * Например, термин "article" применяется для обозначения компонента "Материал", благодаря 
 * чему "материалы" можно связать с таблицей меток, используя связь "многие-ко-многим".
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Terms
 * @since 2.0
 */
class Terms extends BaseObject
{
    /**
     * Возвращает термин по указанному идентификатору.
     * 
     * @param mixed $identifier Идентификатор термина.
     * 
     * @return null|Term Активная запись при успешном запросе, иначе `null`.
     */
    public function get(mixed $identifier): ?Term
    {
        return (new Term())->selectByPk($identifier);
    }

    /**
     * Возвращает идентификатор термина по указанному названию.
     * 
     * @param string $name Название термина.
     * @param string|null $componentId Идентификатор компонента, которому принадлежит 
     *     термин. Если значение `null`, то идентификатор текущего модуля (по умолчанию `null`).
     * 
     * @return null|int Идентификатор термина при успешном запросе, иначе `null`.
     */
    public function getId(string $name, ?string $componentId = null): ?int
    {
        if ($componentId === null) {
            $componentId = Ge::module()?->id;
        }

        /** @var Term|null $term */
        $term = (new Term())->getByName($name, $componentId);
        return $term ? ($term->id ?: null) : null;
    }

    /**
     * Добавляет метку.
     * 
     * @see Term::save()
     * 
     * @param array<string, mixed> $attributes Атрибуты меток в виде пар "ключ - значение".
     * 
     * @return void
     */
    public function add(array $attributes): void
    {
        $tag = new Term();
        $tag->setAttributes($attributes);
        $tag->save();
    }

    /**
     * Обновляет метку.
     * 
     * @see Tag::save()
     * 
     * @param array<string, mixed> $attributes Атрибуты меток в виде пар "ключ - значение".
     * @param int $termId Идентификатор метки.
     * 
     * @return void
     */
    public function update(array $attributes, int $termId): void
    {
        /** @var Term|null $term */
        $term = (new Term())->get($termId);
        if ($term) {
            $term->setAttributes($attributes);
            $term->save();
        }
    }

    /**
     * Удаляет метку.
     * 
     * @see Term::delete()
     * 
     * @param int $id Идентификатор метки.
     * 
     * @return bool Возвращает значение `false`, если не удалось выполнить удаление.
     */
    public function delete(int|array $id): bool
    {
        $term = new Term();
        /** @var false|int $result */
        $result = $term->deleteRecord([$term->primaryKey() => $id]);
        return $result !== false;
    }

    /**
     * Создаёт таблицу терминов.
     * 
     * @see Term::createTable()
     * 
     * @return void
     */
    public function createTermsTable(): void
    {
        (new Term())->createTable();
    }
}
