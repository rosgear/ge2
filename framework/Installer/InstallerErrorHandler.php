<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Installer;

use Ge\Exception\ErrorException;
use Ge\ErrorHandler\ErrorHandler;

/**
 * Класс обработчика ошибок и исключений установщика.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Installer
 * @since 1.0
 */
class InstallerErrorHandler extends ErrorHandler
{
    /**
     * Путь к файлу представления для отображения стека ошибок.
     * 
     * @var string
     */
    public string $errorsView = '/views/errorHandler/errors.php';

    /**
     * Путь к файлу представления для отображения фатальных ошибок.
     * 
     * @var string
     */
    public string $fatalView = '/views/errorHandler/fatalError.php';

    /**
     * Путь к файлу представления для отображения стека ошибок без форматирования (для консоли).
     * 
     * @var string
     */
    public string $plainErrorsView = '/views/errorHandler/plainErrors.php';

    /**
     * Путь к файлу представления для отображения фатальных ошибок без форматирования (для консоли).
     * 
     * @var string
     */
    public string $plainFatalView = '/views/errorHandler/plainFatalError.php';

    /**
     * Путь к файлу представления для отображения исключений.
     * 
     * @var string
     */
    public string $exceptionView = '/views/errorHandler/exception.php';

    /**
     * Путь к файлу представления для отображения исключений без форматирования (для консоли).
     * 
     * @var string
     */
    public string $plainExceptionView = '/views/errorHandler/plainException.php';

    /**
     * Отображает пойманное исключение.
     * 
     * @param \Exception $exception Исключение.
     * 
     * @return void
     */
    protected function _renderException($exception): void
    {
        die($this->renderFile($this->fatalView, ['exception' => $exception]));
    }

    /**
     * {@inheritdoc}
     */
    public function renderFatalException($exception): void
    {
        if ($this->cleanExistingOutput) {
            $this->cleanOutputBuffer();
        }
        $this->_renderException($exception);
    }

    /**
     * {@inheritdoc}
     */
    public function renderException($exception): void
    {
        // если ошибка в одном из начальных классов загрузки
        if ($exception instanceof \Ge\Exception\BootstrapException) {
            return;
        }
        // все ошибки handleError будут собраны в $exceptions
        // и обработаны в renderShutdownExceptions
        if ($exception instanceof ErrorException) {
            return;
        }
        $content = $this->renderFile($this->exceptionView, ['exception' => $exception]);
        die($content);
    }

    /**
     * {@inheritdoc}
     */
    public function renderShutdownExceptions(): void
    {
        if ($this->exceptions) {
            if ($this->cleanExistingOutput) {
                $this->cleanOutputBuffer();
            }
            $content = $this->renderFile(
                $this->errorsView,
                [
                    'exceptions'  => $this->exceptions,
                    'withContent' => ''
                ]
            );
            die($content);
        }
    }

    /**
     * Отображает файл представления.
     * 
     * @param string $filename Имя Файла.
     * @param array $params Переменные импортируемые в файл.
     * 
     * @return string Результат отображения файла в виде строки.
     */
    public function renderFile(string $filename, array $params): string
    {
        $params['handler'] = $this;

        ob_start();
        ob_implicit_flush(false);
        extract($params, EXTR_OVERWRITE);
        require BASE_PATH . $filename;
        return ob_get_clean();
    }

    /**
     * {@inheritdoc}
     */
    public function uncatchableException($exception): void
    {
        $this->renderFatalException($exception);
    }

    /**
     * Преобразует специальные символы в HTML сущности.
     * 
     * @param string $text Текст для преобразования.
     * 
     * @return string Закодированный оригинальный текст.
     */
    public function htmlEncode(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * @param null|array $args
     * 
     * @return string
     */
    public function argumentsToString(?array $args): string
    {
        $args = (array) $args;

        $count = 0;
        $isAssoc = $args !== array_values($args);
        foreach ($args as $key => $value) {
            $count++;
            if ($count >= 5) {
                if ($count > 5) {
                    unset($args[$key]);
                } else {
                    $args[$key] = '...';
                }
                continue;
            }
            if (is_object($value)) {
                $args[$key] = '<span class="title">' . $this->htmlEncode(get_class($value)) . '</span>';
            } elseif (is_bool($value)) {
                $args[$key] = '<span class="keyword">' . ($value ? 'true' : 'false') . '</span>';
            } elseif (is_string($value)) {
                $fullValue = $this->htmlEncode($value);
                if (mb_strlen($value, 'UTF-8') > 32) {
                    $displayValue = $this->htmlEncode(mb_substr($value, 0, 32, 'UTF-8')) . '...';
                    $args[$key] = "<span class=\"string\" title=\"$fullValue\">'$displayValue'</span>";
                } else {
                    $args[$key] = "<span class=\"string\">'$fullValue'</span>";
                }
            } elseif (is_array($value)) {
                $args[$key] = '[' . $this->argumentsToString($value) . ']';
            } elseif ($value === null) {
                $args[$key] = '<span class="keyword">null</span>';
            } elseif (is_resource($value)) {
                $args[$key] = '<span class="keyword">resource</span>';
            } else {
                $args[$key] = '<span class="number">' . $value . '</span>';
            }
            if (is_string($key)) {
                $args[$key] = '<span class="string">\'' . $this->htmlEncode($key) . "'</span> => $args[$key]";
            } elseif ($isAssoc) {
                $args[$key] = "<span class=\"number\">$key</span> => $args[$key]";
            }
        }
        return implode(', ', $args);
    }
}