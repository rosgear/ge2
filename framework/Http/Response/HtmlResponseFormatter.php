<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Http\Response;

use Ge;
use Ge\Http\Exception;

/**
 * Класс Форматтера для форматирования HTTP-ответа в формат HTML.
 * 
 * Используется {@see \Ge\Http\Response} для форматирования данных ответа.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Http\Response
 * @since 2.0
 */
class HtmlResponseFormatter extends AbstractResponseFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(\Ge\Http\Response $response, mixed $content): mixed
    {
        // добавление к контенту исключений
        if ($response->exceptionContent) {
            $content .= $response->exceptionContent;
        }
        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function formatException(\Ge\Http\Response $response, $exception, mixed $content): void
    {
        if (GE_MODE_DEV) {
            $response->setContent($content);
        } else {
            // если исключение не имеет шаблон или не может его загрузить,
            // тогда исключение выводится как оно есть
            $exceptionContent = $this->loadViewByException($exception);
            $response->setContent($exceptionContent ?: $content);
        }
    }

    /**
     * Возвращает названия файла шаблона по коду состояния.
     * 
     * @see Response::isClientError()
     * @see Response::isServerError()
     * 
     * @return false|string Если значение `false`, нет ошибки на сервере, иначе, название файла 
     *     шаблона.
     */
    public function getViewFilenameByCode(): false|string
    {
        if ($this->accordanceStatusCode)
            $code = $this->accordanceStatusCode;
        else
            $code = $this->statusCode;
        if ($this->response->isClientError() || $this->response->isServerError()) {
            return '//errors/' . $code;
        }
        return false;
    }

    /**
     * Загрузка шаблона.
     * 
     * @param string $filename Имя файла шаблона.
     * @param array $params Переменные в шаблоне.
     * 
     * @return string Контент шаблона.
     * 
     * @throws Exception\ViewNotFoundException Шаблон не найден.
     */
    public function loadView(?string $filename, array $params = [])
    {
        if ($filename === null) {
            if (($filename = $this->getViewFilenameByCode()) === false) {
                // если `loadView()` был вызван через `loadViewByException()`, т.е. 
                // предназначен для вывода шаблона исключения и при этом сам
                // шаблон создал ошибку, поэтому чтобы не было коллизии делаем `die()`
                $exception = $params['exception'] ?? null;
                if ($exception) {
                    if ($exception instanceof \Ge\Exception\BaseException) {
                        die($exception->getDispatch() . ' Exception template by code "' . $filename . '" not found.');
                    } else {
                        die(get_class($exception) . ': exception template by code "' . $filename . '" not found.');
                    }
                }
                throw new Exception\ViewNotFoundException($filename);
            }
        }

        // имя шаблона должно содержать символ "@" или "//", такой символ, как "/"
        // исключается (он указывает на использование модуля, а он может быть не создан)
        if (strncmp($filename, '/', 1) === 0) {
            $filename = '//' . ltrim($filename, '/');
        } else
        if (strncmp($filename, '@', 1) !== 0) {
            $filename = '//' . $filename;
        }

        // используем представление для получения имени файла шаблона с темой и локализацией
        $view = Ge::$app->getView();
        $filename = $view->getViewFile($filename);

        if (!file_exists($filename)) {
            // если `loadView()` был вызван через `loadViewByException()`, т.е. 
            // предназначен для вывода шаблона исключения и при этом сам
            // шаблон создал ошибку, поэтому чтобы не было коллизии делаем `die()`
            $exception = $params['exception'] ?? null;
            if ($exception) {
                if ($exception instanceof \Ge\Exception\BaseException) {
                    die($exception->getDispatch() . ' Exception template "' . $filename . '" not found.');
                } else {
                    die(get_class($exception) . ': exception template "' . $filename . '" not found.');
                }
            }
            throw new Exception\ViewNotFoundException($filename);
        }
        $params['response'] = $this;

        return $view->renderPhpFile($filename, $params);
    }

    /**
     * Загрузка шаблона из параметров брошенного исключения.
     * 
     * @param \Exception $exception Исключение.
     * 
     * @return string Контент шаблона, исли исключения нет, пустая строка.
     */
    public function loadViewByException($exception)
    {
        $hasView = isset($exception->viewFile) ? $exception->viewFile !== null : false;
        if ($hasView) {
            return $this->loadView($exception->viewFile, ['exception' => $exception]);
        }
        return '';
    }
}
