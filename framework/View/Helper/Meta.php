<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\View\Helper;

use Ge;

/**
 * Вспомогательный класс формирования метатегов HTML-страницы.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View\Helper
 * @since 2.0
 */
class Meta extends AbstractMeta
{
    /**
     * {@inheritdoc}
     */
    public function setCommon(array $names): static
    {
        $this->common = $names;
        if (isset($names['robots']))
            $this->setName('robots', $names['robots']);
        if (isset($names['author']))
            $this->setName('author', $names['author']);
        if (isset($names['keywords']))
            $this->setName('keywords', $names['keywords']);
        if (isset($names['description']))
            $this->setName('description', $names['description']);
        return $this;
    }

    /**
     * Добавление метатега c названием "robots".
     * 
     * Управляет индексацией конкретной web-страницы.
     *
     * @param string|array $content
     * 
     * @return $this
     */
    public function robotsTag(string|array $content): static
    {
        return $this->setName('robots', $content);
    }

    /**
     * Добавление метатега c названием "viewport".
     * Определяет отображение страницы сайта на устройствах.
     *
     * @param string|array $content
     * 
     * @return $this
     */
    public function viewportTag(string|array $content): static
    {
        return $this->setName('viewport', $content);
    }

    /**
     * Добавление метатега c названием "charset".
     * 
     * Определяет кодировку страницы сайта.
     *
     * @param string $content Контент метатега (по умолчанию 'utf-8').
     * 
     * @return $this
     */
    public function charsetTag(string $content = 'utf-8'): static
    {
        return $this->setName('charset', $content);
    }

    /**
     * Добавление метатега c названием "description".
     * 
     * Определяет текстовое описание (краткую аннотацию) конкретной страницы сайта.
     *
     * @param string $content Контент метатега.
     * 
     * @return $this
     */
    public function descriptionTag(string $content): static
    {
        return $this->setName('description', $content);
    }

    /**
     * Добавление метатега c названием "keywords".
     * 
     * Cписок ключевых слов, как правило, через запятую, соответствующих содержимому сайта.
     *
     * @param string $content Контент метатега.
     * 
     * @return $this
     */
    public function keywordsTag(string $content): static
    {
        return $this->setName('keywords', $content);
    }

    /**
     * Добавление метатега c названием "document-state".
     * 
     * Управление индексацией страницы для поисковых роботов. Определяет частоту индексации.
     *
     * @param string $content Контент метатега.
     * 
     * @return $this
     */
    public function docStateTag(string $content): static
    {
        return $this->setName('document-state', $content);
    }

    /**
     * Добавление метатега c названием "author".
     * 
     * Автор, создатель сайта.
     *
     * @param string $content Контент метатега.
     * 
     * @return $this
     */
    public function authorTag(string $content): static
    {
        return $this->setName('author', $content);
    }

    /**
     * Добавление метатега c названием "generator".
     *
     * @param null|string $content Контент метатега (по умолчанию `null`).
     * 
     * @return $this
     */
    public function generatorTag(?string $content = null): static
    {
        $useMeta = true;
        if ($content === null) {
            if ($params = Ge::$app->unifiedConfig->get('page')) {
                $content = !empty($params['textPowered']) ? $params['textPowered'] : Ge::$app->version->getGenerator();
                $useMeta = isset($params['useMetaGenerator']) ? $params['useMetaGenerator'] : true;
            } else
                $content = Ge::$app->version->getGenerator();
        }
        if ($useMeta && $content)
            $this->setName('generator', $content);
        return $this;
    }

    /**
     * Добавление метатега c названием "revisit".
     * 
     * Значение этого тега указывает — как часто обновляется информация на вашем сайте.
     * 
     * @param string $content Контент метатега.
     * 
     * @return $this
     */
    public function revisitTag(string $content): static
    {
        return $this->setName('revisit', $content);
    }

    /**
     * Добавление метатега c http-equiv "expires".
     * 
     * Дата устаревания. Управление кэшированием в HTTP/1.0.
     * 
     * @param string $content Контент метатега.
     * 
     * @return $this
     */
    public function expiresTag(string $content): static
    {
        return $this->setEquiv('expires', $content);
    }

    /**
     * Добавление метатега c http-equiv "pragma".
     * 
     * Эта директива показывает, что кешированная информация не должна использоваться и вместо этого запросы должны посылаться на сервер.
     *
     * @param string $content Контент метатега.
     * 
     * @return $this
     */
    public function pragmaTag(string $content): static
    {
        return $this->setEquiv('pragma', $content);
    }

    /**
     * Добавление метатега c http-equiv "content-type".
     * 
     * Указание типа документа. Может быть расширено указанием кодировки страницы (charset).
     * 
     * @param string $content Контент метатега.
     * 
     * @return $this
     */
    public function contentTypeTag(string $content): static
    {
        return $this->setEquiv('content-type', $content);
    }

    /**
     * Добавление метатега c http-equiv "content-language".
     * 
     * Указание языка документа.
     * 
     * @param string $content Контент метатега.
     * 
     * @return $this
     */
    public function contentLangugeTag(string $content): static
    {
        return $this->setEquiv('content-language', $content);
    }

    /**
     * Добавление метатега c http-equiv "refresh".
     * 
     * Определение задержки в секундах, после которой броузер автоматически обновляет документ.
     * 
     * @param string $content Контент метатега.
     * 
     * @return $this
     */
    public function refreshTag(string $content): static
    {
        return $this->setEquiv('refresh', $content);
    }

    /**
     * Установка тега c http-equiv "Cache-Control".
     * 
     * Определяет действия кэша по отношению к данному документу.
     *
     * @param string $content
     * 
     * @return $this
     */
    public function cacheControlTag(string $content): static
    {
        return $this->setEquiv('cache-control', $content);
    }

    /**
     * Устанавливает тег CSRF.
     * 
     * @param null|string $token Токен для проверки CSRF (по умолчанию `null`).
     * 
     * @return void
     */
    public function csrfTokenTag(?string $token = null)
    {
        /** @var \Ge\Http\Request $request */
        $request = Ge::$services->getAs('request');
        // если проверка CSRF
        if ($request->enableCsrfValidation) {
            if ($token === null) {
                $token = $request->getCsrfToken();
            }
            $this->setName($request->csrfHeaderName, $token);
        }
    }
}
