<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Mvc\Controller;

use Ge;

/**
 * Контроллер является базовым классом для классов, содержащих логику контроллера.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Mvc\Controller
 * @since 2.0
 */
class Controller extends BaseController
{
    /**
     * Включает проверку CSRF (подделка межсайтовых запросов).
     * 
     * Свойство отключает или включает {@see \Ge\Http\Request::$enableCsrfValidation}.
     * 
     * Если необходимо выполнить проверку для конкретного действия, используйте
     * поведение {@see \Ge\Filter\CsrfFilter}, тогда значение должно быть `false`.
     * 
     * @see Controller::verifyCsrf()
     * @see \Ge\Http\Request::$enableCsrfValidation
     * 
     * @var bool
     */
    public bool $enableCsrfValidation = true;

    /**
     * Проверка CSRF (подделка межсайтовых запросов).
     * 
     * @return void
     */
    protected function verifyCsrf(): void
    {
        if ($this->enableCsrfValidation) {
            /** @var \Ge\Http\Request $request */
            $request = Ge::$app->request;
            // запрос и контроллер позволяют делать проверку CSRF
            if ($request->enableCsrfValidation) {
                // если !$request->enableCsrfValidation или $request->isSafeMethod() всегда будет true
                $validate = $request->validateCsrfToken();
                if (!$validate) {
                    // если пользователь ранее авторизован
                    Ge::$app->session->destroy();
                    throw new Exception\TokenMismatchException(Ge::t('app', 'CSRF token mismatch'));
                }
            }
        }
    }

    /**
     * @see \Ge\Mvc\Module\ModulePermission::checkAccess()
     * 
     * {@inheritdoc}
     */
    protected function accessAction(string $action): bool
    {
        return $this->module->getPermission()->checkAccess($action);
    }

    /**
     * {@inheritdoc}
     */
    public function onAccess(): bool
    {
        if (parent::onAccess()) {
            // проверить CSRF 
            $this->verifyCsrf();
            return true;
        }
        return false;
    }
}
