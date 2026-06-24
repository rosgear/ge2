<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Log\Writer;

use Ge;

/**
 * Базовый класс писателя журнала.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Log\Writer
 * @since 2.0
 */
class BaseWriter extends AbstractWriter
{
    /**
     * Спецификатор формата для сообщений журнала.
     * 
     * Устанавливается в опциях ($options) конструктора класса {@AbstractWriter}.
     * 
     * @var string
     */
    public string $formatMessage = '[%timestamp%] %priorityName% (%priority%): %message% %extra%';

    /**
     * Формат даты и времени сообщения.
     * 
     * Устанавливается в опциях ($options) конструктора класса {@AbstractWriter}.
     * 
     * @var string
     */
    public string $formatDateTime = 'php:d-m-Y H:i:s';

    /**
     * {@inheritdoc}
     */
    public function filterMessage(array $message): array
    {
        if ($this->prioritiesMap && $this->prioritiesMap !== 1) {
            return in_array($message['priority'], $this->prioritiesMap) ? $message : null;
        }
        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function formatValue(string $key, mixed $value): mixed 
    {
        if ($key === 'timestamp') {
            return Ge::$app->formatter->toDateTime($value, $this->formatDateTime);
        }
        return parent::formatValue($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function formatMessage(array $message): string|array
    {
        $output  = $this->formatMessage;

        foreach ($message as $key => $value) {
            $value = $this->formatValue($key, $value);
            $output = str_replace("%$key%", (string) $value, $output);
        }
        return $output;
    }
}
