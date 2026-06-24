<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\ErrorHandler;

use Ge;
use Throwable;
use Ge\Exception\ErrorException;
use Ge\Exception\BaseException;

/**
 * Класс обработчика ошибок и исключений при HTTP запросах.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ErrorHandler
 * @since 2.0
 */
class WebErrorHandler extends ErrorHandler
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
     * @param Throwable|mixed $exception Исключение.
     * 
     * @return void
     */
    protected function _renderException($exception): void
    {
        if (Ge::hasObject('response')) {
            /** @var \Ge\Http\Response $response */
            $response = Ge::$app->response;
            $response->defineFormat();
        } else {
            /** @var \Ge\Http\Response $response */
            $response = Ge::createObject('response');
            $response->defineFormat();
        }
        // если указан формат ответа
        if (isset($exception->responseFormat) && $exception->responseFormat) {
            $response->setFormat($exception->responseFormat);
        }
        $isPlain = $response->isPlain();

        // если режим "production" (GE_MODE_PRO), попытка отобразить красиво
        if (GE_MODE_PRO) {
            // если не HTML-формат
            if ($isPlain) {
                // если наше исключение
                if ($exception instanceof BaseException) {
                    $content = $exception->getPlainDispatch();
                } else {
                    $content = $this->renderFile($this->plainFatalView, ['exception' => $exception]);
                }
            // если HTML-формат
            } else {
                $content = $this->renderFile($this->fatalView, ['exception' => $exception]);
            }
            // попытка по исключению определить шаблон (если он есть) и загрузить его
            // если загрузка не получится, ответом будет $content
            $response->setByException($exception, $content);
            $response->send(true);
        // если режим "development" (GE_MODE_DEV), отобразить как есть
        } else {
            die($this->renderFile($isPlain ? $this->plainFatalView : $this->fatalView, ['exception' => $exception]));
        }
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
            $exception->render(Ge::$app->request->isAjax());
            return;
        }
        // все ошибки handleError будут собраны в $exceptions
        // и обработаны в renderShutdownExceptions
        if ($exception instanceof ErrorException) {
            return;
        }
        if (Ge::hasObject('response')) {
            /** @var \Ge\Http\Response $response */ 
            $response = Ge::$app->response;
            $response->defineFormat();
        } else {
            /** @var \Ge\Http\Response $response */ 
            $response = Ge::createObject('response');
            $response->defineFormat();
        }

        $isPlain = $response->isPlain();
        $content = $this->renderFile(
            $isPlain ? $this->plainExceptionView : $this->exceptionView,
            ['exception' => $exception]
        );
        $response->setByException($exception, $content);
        $response->send(true);
    }

    /**
     * {@inheritdoc}
     */
    public function renderShutdownExceptions(): void
    {
        if (Ge::hasObject('response')) {
            /** @var \Ge\Http\Response $response */
            $response = Ge::$app->response;
            $isPlain = $response->isPlain();
            if (!$response->isSent) {
                if ($this->cleanExistingOutput) {
                    $this->cleanOutputBuffer();
                    if (!$isPlain)
                        $response->setContent('');
                }
                $content = $this->renderFile(
                    $isPlain ? $this->plainErrorsView : $this->errorsView,
                    [
                        'exceptions'  => $this->exceptions,
                        'withContent' => $response->hasContent()
                    ]
                );
                $response->exceptionContent($content);
                $response->send(true);
            }
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
        $this->logException($exception);
    }

    /**
     * {@inheritdoc}
     */
    public function logException($exception): void
    {
        if (isset(Ge::$app->logger)) {
            Ge::$services->getAs('logger')->error($exception);
        }
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
     * Выполняет преобразование аргументов.
     * 
     * @param mixed $args Аргументы.
     * 
     * @return string
     */
    public function argumentsToString(mixed $args): string
    {
        $args = (array) $args;

        $count = 0;
        $isAssoc = $args !== array_values($args);
        foreach ($args as $key => $value) {
            $count++;
            if ($count >= 5) {
                if ($count > 5)
                    unset($args[$key]);
                else
                    $args[$key] = '...';
                continue;
            }

            if (is_object($value))
                $args[$key] = '<span class="title">' . $this->htmlEncode(get_class($value)) . '</span>';
            else
            if (is_bool($value))
                $args[$key] = '<span class="keyword">' . ($value ? 'true' : 'false') . '</span>';
            else
            if (is_string($value)) {
                $fullValue = $this->htmlEncode($value);
                if (mb_strlen($value, 'UTF-8') > 32) {
                    $displayValue = $this->htmlEncode(mb_substr($value, 0, 32, 'UTF-8')) . '...';
                    $args[$key] = "<span class=\"string\" title=\"$fullValue\">'$displayValue'</span>";
                } else {
                    $args[$key] = "<span class=\"string\">'$fullValue'</span>";
                }
            } else
            if (is_array($value))
                $args[$key] = '[' . $this->argumentsToString($value) . ']';
            else
            if ($value === null)
                $args[$key] = '<span class="keyword">null</span>';
            else
            if (is_resource($value))
                $args[$key] = '<span class="keyword">resource</span>';
            else
                $args[$key] = '<span class="number">' . $value . '</span>';

            if (is_string($key))
                $args[$key] = '<span class="string">\'' . $this->htmlEncode($key) . "'</span> => $args[$key]";
            else
            if ($isAssoc)
                $args[$key] = "<span class=\"number\">$key</span> => $args[$key]";
        }
        return implode(', ', $args);
    }
}