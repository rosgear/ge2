<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Db\Sql\Ddl\Column;

/**
 * Абстрактный класс timestamp столбца таблицы.
 * 
 * @link http://dev.mysql.com/doc/refman/5.6/en/timestamp-initialization.html
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Db\Sql\Ddl\Column
 * @since 2.0
 */
abstract class AbstractTimestampColumn extends Column
{
    /**
     * {@inheritdoc}
     */
    public function getExpressionData(): array
    {
        $spec   = $this->specification;
        $params = [$this->name, $this->type];
        $types  = [self::TYPE_IDENTIFIER, self::TYPE_LITERAL];

        if (!$this->isNullable) {
            $spec .= ' NOT NULL';
        }

        if ($this->default !== null) {
            $spec    .= ' DEFAULT %s';
            $params[] = $this->default;
            $types[]  = self::TYPE_VALUE;
        }

        $options = $this->getOptions();

        if (isset($options['on_update'])) {
            $spec    .= ' %s';
            $params[] = 'ON UPDATE CURRENT_TIMESTAMP';
            $types[]  = self::TYPE_LITERAL;
        }

        $data = [[$spec, $params, $types]];
        foreach ($this->constraints as $constraint) {
            $data[] = ' ';
            $data = array_merge($data, $constraint->getExpressionData());
        }
        return $data;
    }
}
