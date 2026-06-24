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
 * Класс писателя PHP ошибок в файл журнала.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Log\Writer
 * @since 2.0
 */
class ErrorWriter extends FileWriter
{
    /**
     * Карта имен приоритетов логирования сообщений с кодами.
     * 
     * Определяется из установленных в опциях ($options) конструктора класса {@see ErrorWriter::$severities}.
     * 
     * @var int|array
     */
    protected int|array $severitiesMap = [];

    /**
     * Коды приоритета логирования сообщений.
     * Сообщения с перечисленными кодами приоритета будут добавлены в журнал.
     * Может принимать значения: '*', E_ALL, ['warning',...].
     * 
     * Устанавливается в опциях ($options) конструктора класса {@AbstractWriter}.
     * 
     * @see Logger::$priorityNames
     * 
     * @var array|string
     */
    public array|string $severities = [];

    /**
     * {@inheritdoc}
     */
    public string $formatMessage = '[%timestamp%] %severityName% (%severity%): %message% in %file% on line %line%';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this->initSeverities();
    }

    /**
     * Определение кодов приоритета логирования сообщений.
     * 
     * @see ErrorWriter::$severities
     * 
     * @return $this
     */
    public function initSeverities(): static
    {
        if ($this->severities) {
            $this->setSeverities($this->severities);
        }
        return $this;
    }

    /**
     * Установка карты  кодов приоритета логирования сообщений из параметра $severities.
     * 
     * @see ErrorWriter::severitiesMap
     * 
     * @param string|array $severities Имена кодов приоритета логирования сообщений.
     * 
     * @return void
     */
    public function setSeverities(string|array $severities): void
    {
        if ($severities === '*' || $severities === E_ALL)
            $this->severitiesMap = 1;
        else
            $this->severitiesMap = $severities;
    }

    /**
     * {@inheritdoc}
     */
    public function filterMessage(array $message): array
    {
        if ($this->severitiesMap && $this->severitiesMap !== 1) {
            return in_array($message['severity'], $this->severitiesMap) ? $message : null;
        }
        return parent::filterMessage($message);
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $message): void
    {
        $error = $message['message'];
        // если сообщение создано обработчиком ошибок
        if (is_object($error) && $error instanceof \Ge\Exception\ErrorException) {
            $severity     = $error->getCode();
            $severityName = Ge::$app->errorHandler->getSeverityName($severity);
            $message['message'] = $error->getMessage();
            $message['line']    = $error->getLine();
            $message['file']    = $error->getFile();
            $message['trace']   = $error->getTraceAsString();
            $message['severity']     = $severity;
            $message['severityName'] = $severityName === null ? 'Unknow' : $severityName;
        // если сообщение создано вручную
        } else {
            $message['line']  = $message['extra']['line'] ?? 0;
            $message['file']  = $message['extra']['file'] ?? '[file not specified]';
            $message['trace'] = $message['extra']['trace'] ?? '';
            $message['severity']     = $message['priority'];
            $message['severityName'] = $message['priorityName'];
        }

        parent::write($message);
    }
}
