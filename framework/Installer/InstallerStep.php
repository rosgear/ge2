<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Installer;

use Ge\Stdlib\BaseObject;

/**
 * Класс шага установки приложения.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Installer
 * @since 1.0
 */
class InstallerStep extends BaseObject
{
    use InstallerMessagesTrait;

    /**
     * Имя шага установки.
     * 
     * Используется установщиком для формирования карты шагов.
     * 
     * Например: 'welcome'.
     * 
     * @var string
     */
    public string $name = '';

    /**
     * Шаблон представления.
     * 
     * Например: 'choice/step-1'.
     * 
     * @var string
     */
    public string $viewName = '';

    /**
     * Название параметра в запросе $_POST, определяющий действие.
     * 
     * @var string
     */
    public string $paramAction = 'action';

    /**
     * Параметры шага установки.
     * 
     * Передаются в шаблон.
     * 
     * @var array
     */
    public array $params = [];

    /**
     * Установщик.
     * 
     * @var Installer
     */
    public Installer $installer;

    /**
     * Представление шага установки.
     * 
     * @see InstallerStep::getView()
     * 
     * @var InstallerView
     */
    protected InstallerView $view;

    /**
     * Конфигуратор установщика.
     * 
     * @var InstallerConfig
     */
    protected InstallerConfig $state;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->state = $this->installer->getConfig();
    }

    /**
     * Событие перед инициализацией шага установки.
     * 
     * @return bool Если значение `false`, шаг установки будет прерван.
     */
    public function beforeInit(): bool
    {
        return true;
    }

    /**
     * Событие после инициализации шага установки.
     * 
     * @return bool Если значение `false`, шаг установки будет прерван.
     */
    public function afterInit(): bool
    {
        return true;
    }

    /**
     * Инициализации шага установки.
     * 
     * @return void
     */
    public function init(): void
    {
        $this->initParams();
        $this->initAction();
    }

    /**
     * Инициализации параметры шага установки.
     * 
     * Параметры будут переданы в шаблон.
     * 
     * @return void
     */
    protected function initParams(): void
    {
        $this->params['assets']    = $this->installer->getAssetsUrl();
        $this->params['installer'] = $this->installer;
        $this->params['state']     = $this->state;
        $this->params['step']      = $this;
    }

    /**
     * Инициализации действия шагом установки.
     * 
     * @return void
     */
    protected function initAction(): void
    {
        if (!$this->hasError() && $this->hasAction()) {
            $action = $this->getAction();
            if ($action) {
                $this->doAction($action);
            }
        }
    }

    /**
     * Выполняет действие.
     * 
     * @param string $action Название действия.
     * 
     * @return void
     */
    protected function doAction(string $action): void
    {
        $method = $action . 'Action';
        if (method_exists($this, $method)) {
            $this->$method();
        } else
            $this->addError(sprintf('Action "%s" not exists.', $action));
    }

    /**
     * Возвращает название действия из запроса $_POST.
     * 
     * @return string|null
     */
    public function getAction(): ?string
    {
        return $_POST[$this->paramAction] ?? null;
    }

    /**
     * Проверяет, было ли вызвано текущее или указанное действие шагом установки.
     * 
     * @param null|string $action Если значение `null`, проверит, было ли действие. Если 
     *     значение отличное от `null`, то было ли вызвано указанное действие (по умолчанию `null`).
     * 
     * @return bool
     */
    public function hasAction(?string $action = null): bool
    {
        $posted = $_POST[$this->paramAction] ?? null;
        if ($posted !== null) {
            if ($action) {
                return $action === $posted;
            }
            return true;
        }
        return false;
    }

    /**
     * Ставит или убирает метку установщику о завершении установки.
     * 
     * @param bool $complete Если значение `true`, шаг установки завершен (по умолчанию `true`).
     * 
     * @return $this
     */
    public function complete(bool $complete = true)
    {
        $this->installer->complete(null, $complete);
        return $this;
    }

    /**
     * Выполняет проверку параметров запроса.
     * 
     * @return bool
     */
    protected function validate(): bool
    {
        return true;
    }

    /**
     * Выполняет перевод указанного сообщения.
     * 
     * Пример:
     * ```php
     * t('Hi %s', ['Ivan']); // Hi Ivan
     * ```
     * 
     * @see Installer::t()
     * 
     * @param string $message Сообщение перевода.
     * @param array $args Параметры сообщения.
     * 
     * @return string
     */
    public function t(string $message, array $args = []): string
    {
        return $this->installer->t($message, $args);
    }

    /**
     * Проверяет по имени.
     * 
     * @param string $stepName Имя проверяемого шага.
     * 
     * @return bool
     */
    public function is(string $stepName): bool
    {
        return $this->name == $stepName;
    }


    /**
     * Выводит сообщение.
     * 
     * @param string $message Сообщение (по умолчанию '').
     * @param string|null $title Заголовок сообщения (по умолчанию '').
     * @param string $type Тип сообщения: 'error', 'warning', 'info' (по умолчанию '').
     * 
     * @return string
     */
    public function showMessage(string $message = '', ?string $title = '', string $type = ''): string
    {
        return '';
    }

    /**
     * Выводит сообщение.
     * 
     * @param array $message Параметры сообщение.
     * 
     * @return string
     */
    public function showMessageAr(array $message): string
    {
        return $this->showMessage($message['message'] ?? '', $message['title'] ?? null, $message['type'] ?? '');
    }

    /**
     * Выводит сообщения.
     * 
     * @see InstallerStep::showMessage()
     * 
     * @param array $messages Сообщения.
     * 
     * @return void
     */
    public function showMessages(array $messages = []): void
    {
        if (empty($messages)) {
            $messages = $this->getMessages();
        }

        if ($messages) {
            foreach ($messages as $msg) {
                echo $this->showMessage($msg['message'], $msg['title'], $msg['type']);
            }
        }
    }

    /**
     * Возвращает заголовок шага установки.
     * 
     * @return string
     */
    public function getTitle(): string
    {
        return '';
    }

    /**
     * Возвращает представление шага установки.
     * 
     * @see InstallerStep::$view
     * 
     * @return InstallerView
     */
    public function getView(): InstallerView
    {
        if (!isset($this->view)) {
            $installer = $this->installer;

            $this->view = new InstallerView([
                'path' => $this->installer->getViewPath(),
                'params' => [
                    't' => function (string $message, array $args = []) use ($installer) {
                        return $installer->t($message, $args);
                    }
                ]
            ]);
        }
        return $this->view;
    }

    /**
     * Выводит шаблон шага установки.
     * 
     * @return void
     */
    public function run()
    {
        return $this->getView()->render($this->viewName, $this->params);
    }
}
