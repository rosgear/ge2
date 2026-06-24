<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\View;

use Ge;
use Ge\Helper\Str;
use Ge\View\ClientScript;

/**
 * Представление макета страницы сайта.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\View
 * @since 2.0
 */
class LayoutView extends View
{
    /**
     * Выводит заголовок, метаданные и подключённые скрипты клиента тега "head" 
     * HTML-страницы.
     * 
     * @return void
     */
    public function renderHeadHtml(): void
    {
        // заголовок HTML
        echo $this->script->renderTitle(), "\n\t";
        // метаданные HTML
        echo $this->script->renderMeta();
        // скрипты и стили HTML
        echo $this->script->render(ClientScript::POS_HEAD);
    }

    /**
     * Выводит содержимое, которое будет вставлено в начало раздела "body" HTML-страницы.
     * 
     * Контент отображается с использованием зарегистрированных блоков кода скрипта, 
     * стилей и файлов.
     * 
     * @return void
     */
    public function renderBeginBodyHtml(): void
    {
        echo $this->script->render(ClientScript::POS_READY);
        echo $this->script->render(ClientScript::POS_BEGIN);
    }

    /**
     * Выводит содержимое, которое будет вставлено в конец раздела "body" HTML-страницы.
     * 
     * Контент отображается с использованием зарегистрированных блоков кода скрипта, 
     * стилей и файлов.
     * 
     * @return void
     */
    public function renderEndBodyHtml(): void
    {
        echo $this->script->render(ClientScript::POS_END);
    }

    /**
     * Выводит раздел заголовка HTML-страницы.
     * 
     * @return void
     */
    public function head(): void
    {
        // $this->trigger(self::EVENT_PAGE_HEAD);

        // регистрация тега "generator"
        $this->script->meta->generatorTag();
        // регистрация "favicon"
        $this->script->favIcon();

        $this->renderHeadHtml();
    }

    /**
     * Выводит содержимое, которое будет вставлено в начало раздела "body" HTML-страницы.
     * 
     * @return void
     */
    public function beginBody(): void
    {
        // $this->trigger(self::EVENT_BEGIN_BODY);

        $this->renderBeginBodyHtml();
    }

    /**
     * Выводит содержимое, которое будет вставлено в конец раздела "body" HTML-страницы.
     * 
     * @return void
     */
    public function endBody(): void
    {
        // $this->trigger(self::EVENT_END_BODY);

        echo $this->script->render(ClientScript::POS_END, 0);
    }

    /**
     * Возвращает язык содержимого HTML-страницы.
     * 
     * Применяется в теге "html".  
     * Пример: `<html lang="<?= $this->getLang() ?>">`.
     * 
     * @return string
     */
    public function getLang(): string
    {
        return Ge::$app->language->tag;
    }

    /**
     * Возвращает кодировку (charset) HTML-страницы.
     * 
     * Применяется в теге "meta".  
     * Пример: `<meta charset="<?= $this->getCharset() ?>">`.
     * 
     * @return string
     */
    public function getCharset(): string
    {
        $encoding = Ge::$app->config->get('encoding');
        return $encoding['external'] ?? '';
    }

    /**
     * Возвращает имя файла представления (с путём) из по указанному имени.
     * 
     * Если представление имеет параметр:
     * - "useLocalize" со значением `true`, то результатом будет 
     * имя файла с локализацией (если файл существует). Иначе, имя 
     * файла без локализации. Пример: `view.phtml` и `view-ru_RU.phtml`.
     *  - "useTheme" со значением `true`, то результатом будет 
     * имя файла, cодержащий путь к теме. 
     * 
     * Приоритет получения имени файла представления зависит от параметров "useLocalize",
     * "useTheme", всегда выполняется очерёдность:
     * 1. Получение имени файла представления расположенного в теме;
     * 2. Получение имени файла представления из локализации.
     * 
     * @param string $viewFile Имя шаблона или файл шаблона представления.
     *     Пример: '@app/views/backend/module-info.phtml'.
     * 
     * @return string|false Возвращает значение `false`, если невозможно получить имя 
     *     файла представления.
     */
    public function getLayoutFile(string $viewFile): string|false
    {
        if (pathinfo($viewFile, PATHINFO_EXTENSION) === '') {
            $viewFile = $viewFile . '.' . $this->defaultExtension;
        }

        $moduleThemePath = $this->module ? $this->module->getThemePath() : '';

        /**
         * Получение имени файла шаблона из псевдонима "@".
         * 
         * Например, если указано "@app:layouts/main":
         * 1) если useLocalize, то "<path>/views/<side>/layouts/main-<locale>.phtml"
         * 3) "<path>/views/<side>/layouts/main.phtml"
         */
        if (strncmp($viewFile, '@', 1) === 0) {
            $filename = Ge::getAlias($viewFile);
            if ($filename === false) {
                return false;
            }
            // 1) Получение с локализацией (если язык не по умолчанию)
            if ($this->useLocalize && !$this->isDefaultLanguage) {
                $filenameLoc = Str::localizeFilename($filename);
                // echo '1) ', $filenameLoc, '<br>';
                if (file_exists($filenameLoc)) return $filenameLoc;
            }
            // 2) Получение без локализации
            return $filename;
        }

        /**
         * Получение имени файла шаблона из каталого приложения "//".
         * 
         * Например, если указано "//main":
         * 1) если useLocalize, то "<app-path>/views/<side>/layouts/main-<locale>.phtml"
         * 2) если useTheme, то:
         * - если useLocalize, то "<theme-path>/views/layouts/main-<locale>.phtml"
         * - "<theme-path>/views/layouts/main.phtml"
         * 3) "<app-path>/views/<side>/layouts/main.phtml"
         */
        if (strncmp($viewFile, '//', 2) === 0) {
            $viewFile =  ltrim($viewFile, '/');
            // 1) Каталог приложения
            // получение с каталогом приложения
            $filename = Ge::$app->getLayoutPath() . DS . $viewFile;
            // получение с локализацией (если язык не по умолчанию)
            if ($this->useLocalize && !$this->isDefaultLanguage) {
                $filenameLoc = Str::localizeFilename($filename);
                // echo '// 1) ', $filenameLoc, '<br>';
                if (file_exists($filenameLoc)) return $filenameLoc;
            }
            // 2) Каталог темы
            if ($this->useTheme) {
                // получение с каталогом темы
                $filenameTh = Ge::$app->theme->layoutPath . DS . $viewFile;
                // получение с локализацией (если язык не по умолчанию)
                if ($this->useLocalize && !$this->isDefaultLanguage) {
                    $filenameLoc = Str::localizeFilename($filenameTh);
                    // echo '// 2) ', $filenameLoc, '<br>';
                    if (file_exists($filenameLoc)) return $filenameLoc;
                }
                // без локализации
                // echo '// 3) ', $filenameTh, '<br>';
                if (file_exists($filenameTh)) return $filenameTh;
            }
            // без локализации
            // echo '// 4) ', $filename, '<br>';
            return $filename;
        }

        // например 'main' => '/main'
        if (strncmp($viewFile, '/', 1) !== 0) {
            $viewFile = '/' . $viewFile;
        }

        /**
         * Получение имени файла шаблона из каталого модуля "/".
         * 
         * Например, если указано "/main":
         * 1) если useLocalize, то:
         * -  если useTheme, то "<theme-path>/views/layouts/main-<locale>.phtml"
         * - "<module-path>/views/layouts/main-<locale>.phtml"
         * 2) если useTheme, то "<theme-path>/views/layouts/main.phtml"
         * 3) "<module-path>/views/layouts/main.phtml"
         */
        // 1) Получение с локализацией (с темой)
        if ($this->useLocalize && !$this->isDefaultLanguage) {
            $templateLoc = Str::localizeFilename($viewFile);
            if ($this->useTheme) {
                // получение с темой и локализацией
                $filenameLoc = $this->theme->viewPath . $moduleThemePath . DS . 'layouts' . $templateLoc;
                // echo '/ 1) ', $filenameLoc, '<br>';
                if (file_exists($filenameLoc)) return $filenameLoc;
            }
            // получение с локализацией но без темы 
            $filenameLoc = $this->module->getLayoutPath() . $templateLoc;
             // echo '/ 2) ', $filenameLoc, '<br>';
            if (file_exists($filenameLoc)) return $filenameLoc;
        }
        // 2) Получение с темой но без локализации
        if ($this->useTheme) {
            $filename = $this->theme->viewPath . $moduleThemePath . DS . 'layouts' . $viewFile;
            // echo '/ 3) ', $filename, '<br>';
            if (file_exists($filename)) return $filename;
        }
        // 3) Получение без темы и без локализации
        return $this->module->getLayoutPath() . $viewFile;
    }

    /**
     * Возвращает содержимое визуализации представления.
     * 
     * @param string $viewFile Имя шаблона или файл шаблона представления. Если значение `null`, 
     *     будет использоваться имя {@see BaseView::$viewFile} (по умолчанию `null`).
     * @param array $params Параметры с их значениями в виде пар "имя - значение" 
     *     передаваемые в шаблон представления (по умолчанию `null`).
     * @param \Ge\Mvc\Module\BaseModule $module Модуль к которому относится макет 
     *     страницы. Применяется для получения файла шаблона. Если значение `null`, 
     *     тогда применяется текущий модуль {@see \Ge\Mvc\Application::$module} (по 
     *     умолчанию `null`).
     * 
     * @return mixed
     * 
     * @throws Exception\TemplateNotFoundException Невозможно получить имя файла 
     *     шаблона представления.
     */
    public function renderLayout(string $viewFile, array $params = [], $module = null): mixed
    {
        if ($module) {
            $this->module = $module;
        }

        $filename = $this->getLayoutFile($viewFile);
        if ($filename === false) {
            throw new Exception\TemplateNotFoundException(
                Ge::t('app', 'Cannot resolve view file for "{0}"', [$viewFile ?: 'unknow']),
                $filename
            );
        }

        // добавляем, чтобы последующие шаблоны знали о существовании этого шаблона
        $this->mapFiles[] = [
            // избавляемся от каталога макета (layouts) и считаем полученный  
            // каталог основным каталогом шаблонов представлений
            'path'     => dirname($filename, 2),
            'viewFile' => $viewFile,
            'filename' => $filename,
        ];

        $content = $this->renderFile($filename, $params);

        // удаляем, чтобы все последующие шаблоны о нём забыли
        array_pop($this->mapFiles);
        return $content;
    }
}
