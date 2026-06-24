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
use Ge\View\View;
use Ge\Http\Response;
use Ge\Stdlib\Component;
use Ge\View\ViewManager;
use Ge\Stdlib\BaseObject;
use Ge\Mvc\Module\BaseModule;

/**
 * Контроллер является базовым классом для классов, содержащих логику контроллера.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Mvc\Controller
 * @since 2.0
 */
class BaseController extends Component
{
    /**
     * @var string Событие, возникшее до выполнения действия контроллером.
     * 
     * @see Controller::doAction()
     */
    public const EVENT_BEFORE_ACTION = 'beforeAction';

    /**
     * @var string Событие, возникшее после выполнения действия контроллером.
     * 
     * @see Controller::doAction()
     */
    public const EVENT_AFTER_ACTION = 'afterAction';

    /**
     * Модуль контроллера или его расширения.
     * 
     * @var BaseModule
     */
    public BaseModule $module;

    /**
     * Имя макета или его файла.
     * 
     * @see BaseController::findLayout()
     * 
     * @var string|null
     */
    public ?string $layout = null;

    /**
     * Указывает ответу (response), необходимость создать и отправить токен для проверки 
     * CSRF (подделка межсайтовых запросов).
     * 
     * Устанавливается значение в ответе (response) в {@see BaseController::getResponse()}.
     * 
     * Внимание: если ответ был ранее вызван через {@see BaseController::getResponse()}, 
     * то повторное изменение `$sendCsrfToken` не приведёт к изменению значение в ответе.
     * 
     * @var bool
     */
    public bool $sendCsrfToken = true;

    /**
     * Последняя вызываемая модель данных.
     * 
     * @var BaseObject|null
     */
    protected ?BaseObject $lastDataModel = null;

    /**
     * HTTP-ответ.
     * 
     * @see BaseController::getResponse()
     * 
     * @var Response
     */
    protected Response $response;

    /**
     * Имя (короткое имя класса) контроллера.
     * 
     * Устанавливается из конфигурации в конструкторе класса или определяется с 
     * помощью {@see BaseController::getName()}.
     * 
     * @var string
     */
    public string $name = '';

    /**
     * Имя действия контроллера.
     * 
     * @var string
     */
    protected string $actionName = '';

    /**
     * Параметры действия контроллера.
     * 
     * @var array
     */
    protected array $actionParams = [];

    /**
     * Имя действия контроллера по умолчанию.
     * 
     * @var string
     */
    protected string $defaultAction = 'index';

    /**
     * Имя модели данных по умолчанию.
     * 
     * @var string
     */
    protected string $defaultModel = '';

    /**
     * Представление.
     * 
     * @see BaseController::getView()
     * 
     * @var View
     */
    protected View $view;

    /**
     * Конструктор класса.
     * 
     * @param BaseModule $module Модуль контроллера или его расширения.
     * @param string $action Имя действия контроллера.
     * @param array $config Параметры конфигурации контроллера в виде пар "имя - значение", 
     *     которые будут использоваться для инициализации свойств объекта.
     * 
     * @return void
     */
    public function __construct(BaseModule $module, string $action = '', array $config = [])
    {
        $this->module = $module;
        $this->actionName = $action;

        parent::__construct($config);
    }

    /**
     * Выполняет перевод (локализацию) сообщения или сообщений.
     * 
     * @param string|array<int, string> $message Текст сообщения (сообщений).
     * @param array<string, string> $params Параметры перевода (по умолчанию `[]`).
     * @param string $locale Код локализации, например: 'ru_RU', 'en_GB'. Если 
     *     значение '', то применяется текущая локализация (по умолчанию '').
     * 
     * @return string|array
     */
    public function t(
        string|array $message, 
        array $params = [], 
        string $locale = ''
    ): string|array
    {
        return Ge::$app->translator->translate($this->module->id, $message, $params, $locale);
    }

    /**
     * Возвращает локализованное имя действия контроллера в соответствии с указанными 
     * параметрами.
     * 
     * Этот метод предназначен для переопределения локализации имени действия контроллера 
     * в зависимости от входных параметров.
     * 
     * @param mixed $params Параметры, определяющие формат локализации действия контроллера.
     * 
     * @return string Локализованное имя действия контроллера.
     */
    public function translateAction(mixed $params): ?string
    {
        return $this->actionName;
    }

    /**
     * Возвращает имя события приложения, относительно текущего модуля, контроллера и его действия.
     * 
     * Например, если имя модуля 'rg.be.foobar', имя контроллера 'form', а действия 
     * 'view', то результат 'rg.be.foobar:onFormView'.
     * 
     * @param string $prefix Приставка, указывающая на событие вызываемое в действии 
     *     контроллера, например: 'Before', 'After' (по умолчанию ''). 
     * @param string $controllerName Имя контроллера. Если значение '', то текущее 
     *     имя контроллера (по умолчанию ''). 
     * @param string $actionName Имя действия контроллера Если значение '', то текущее 
     *     имя действия контроллера  (по умолчанию '').
     * 
     * @return string
     */
    public function makeAppEventName(
        string $prefix = '', 
        string $controllerName = '', 
        string $actionName = ''
    ): string
    {
        $controllerName = ucfirst($controllerName ?: $this->getName());
        $actionName = $actionName ?: $this->actionName;
        if ($actionName === 'index')
            $actionName = '';
        else
            $actionName = ucfirst($actionName);
        return $this->module->id . ':on' . $prefix . $controllerName . $actionName;
    }

    /**
     * Возвращает модель данных по указанному имени.
     * 
     * @see \Ge\Mvc\Module\BaseModule::getModel()
     * 
     * @param string|null $name Имя модели данных (по умолчанию `null`).
     * @param array $config Параметры конфигурации модели данных передаются в 
     *     конструктор (по умолчанию `[]`).
     * 
     * @return BaseObject|null Возвращает значение `null`, если невозможно 
     *     создать модель данных. Если модель данных создана, делает ёё последней 
     *     {@see BaseController::$lastDataModel} для контроллера.
     */
    public function getModel(?string $name = null, array $config = []): ?BaseObject
    {
        return $this->lastDataModel = $this->module->getModel($name, $config);
    }

    /**
     * Возвращает модуль контроллера.
     * 
     * @return BaseModule|null Модуль контроллера, иначе `null`.
     */
    public function getModule(): ?BaseModule
    {
        return $this->module;
    }

    /**
     * Возвращает последнюю созданную модель данных.
     * 
     * Модель данных создана модулем {@see BaseController::$module} контроллера и 
     * вызвана через {@see BaseController::getModel()}.
     * 
     * @see BaseController::$lastDataModel
     * 
     * @return BaseObject|null Модель данных, иначе `null`.
     */
    public function getLastDataModel(): ?BaseObject
    {
        return $this->lastDataModel;
    }

    /**
     * Возвращает последнюю созданную модель данных.
     * 
     * Модель данных создана модулем {@see BaseController::$module} контроллера и 
     * вызвана через {@see BaseController::getModel()}.
     * 
     * @see BaseController::$lastDataModel
     * 
     * @return BaseObject|null Модель данных, иначе `null`.
     */
    public function getLastModel(): ?BaseObject
    {
        return $this->lastDataModel;
    }

    /**
     * Возвращает HTTP-ответ.
     * 
     * @param string|null $format Формат ответа (по умолчанию `null`). 
     * 
     * @return Response
     */
    public function getResponse(?string $format = null): Response
    {
        if (!isset($this->response)) {
            $this->response = Ge::$app->response;
            if ($format === null) {
                $format = Response::FORMAT_HTML;
            }
            $this->response->sendCsrfToken = $this->sendCsrfToken;
            $this->response->setFormat($format);
        }
        return $this->response;
    }

    /**
     * Возвращает имя (короткое имя класса) контроллера.
     * 
     * @see BaseController::$name
     * 
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Устанавливает представление.
     * 
     * @see BaseController::$view
     * 
     * @param View $view Объект представления, который используется для 
     *     рендеринга представлений или файлов.
     */
    public function setView(View $view): void
    {
        $this->view = $view;
    }

    /**
     * Возвращает представление.
     * 
     * @see BaseController::$view
     * 
     * @param array $config Параметры конфигурации представления (по умолчанию `[]`).
     *     Если представление было создано ранее с помощью {@see \Ge\Mvc\Application::getView()}, 
     *     параметры конфигурации применяться не будут.
     * 
     * @return View
     */
    public function getView(array $config = []): View
    {
        if (!isset($this->view)) {
            $this->view = Ge::$app->getView($config);
        }
        return $this->view;
    }

    /**
     * Проверяет, установлен ли объект представления контроллеру.
     * 
     * @see BaseController::$view
     * 
     * @return bool Возвращает значение `true`, если контроллеру установлен объект 
     *     представления.
     */
    public function hasView(): bool
    {
        return !isset($this->view);
    }

    /**
     * Возвращает менеджер представлений модуля.
     * 
     * @see BaseModule::getViewManager()
     * 
     * @return ViewManager
     */
    public function getViewManager(): ViewManager
    {
        return $this->module->getViewManager();
    }

    /**
     * Устанавливает имя текущему действию контроллера.
     * 
     * Перед установкой имени, будет проверка на наличие у имени псевдонимов с 
     * помощью {@see BaseController::actionMap()}.
     * 
     * @param string $name Имя текущего действия контроллера. Если значение '', то 
     *     имя текущего действия, будет именем действия по умолчанию 
     *     {@see BaseController::$defaultAction}.
     * 
     * @return $this
     */
    public function setActionName(string $name): static
    {
        if ($name === '' || $name === 'index') {
            $this->actionName = $this->defaultAction;
            return $this;
        }

        // правила формирования имени действия
        if ($actions = $this->actionMap()) {
            if (isset($actions[$name]))
                $name = $actions[$name];
            else
            if (isset($actions['*']))
                $name = $actions['*'];
        }
        $this->actionName = $name;
        return $this;
    }

    /**
     * Устанавливает текущему действию контроллера параметры вызова.
     * 
     * @param array $params Параметры вызова текущего действия контроллера в 
     *    виде пар "ключ-значение".
     * 
     * @return $this
     */
    public function setActionParams(array $params): static
    {
        $this->actionParams = $params;
        return $this;
    }

    /**
     * Возвращает имя текущего действия контроллера.
     * 
     * @see BaseController::$actionName
     * 
     * @return string
     */
    public function getActionName(): string
    {
        return $this->actionName;
    }

    /**
     * Возвращает параметры вызова текущего действия контроллера.
     * 
     * @see BaseController::$actionParams
     * 
     * @return array
     */
    public function getActionParams(): array
    {
        return $this->actionParams;
    }

    /**
     * Возвращает или устанавливает имя текущего действия контроллера.
     * 
     * @param string|null $name Имя текущего действия контроллера.
     *    Если `null`, возвратит текущее действие контроллера. Иначе, установит его 
     *    методом {@see BaseController::setActionName()} (по умолчанию `null`).
     * @param array $actionParams Параметры вызова действия в виде пары "ключ-значение". 
     *    Устанавливаются только при указании имени действия $name. В остальных случая 
     *    методом {@see BaseController::setActionParams()} (по умолчанию `[]`).
     * 
     * @return BaseController|string Возвращает имя текущего действия или указатель 
     *     на контроллер.
     */
    public function action(?string $name = null, array $actionParams = []): static|string
    {
        if ($name === null) {
            return $this->actionName;
        }

        $this->setActionName($name);
        $this->setActionParams($actionParams);
        return $this;
    }

    /**
     * Объявляет псевдонимы для имён действий контроллеров.
     *
     * Этот метод предназначен для переопределения имён действий контроллеров перед 
     * их вызовом. Он возвращает массив, где ключами массива являются вызываемые действия 
     * контроллеров, а значения массивов - новые имена (псевдонимы) действий контроллеров.
     * Например,
     * ```php
     * return [
     *     'oldAction1' => 'newAction1',
     *     'oldAction2' => 'newAction2',
     *     '*'          => 'anyAction',
     * ];
     * ```
     * Если указан ключ '*', то он определит имя действия контроллера, которое отсутствует 
     * в массиве.
     * 
     * Вызывается перед определением имени {@see BaseController::setActionName()} текущего 
     * действия контроллера.
     * 
     * @return array|null Если значение `null`, псведонимы не указаны.
     */
    protected function actionMap(): ?array
    {
        return null;
    }

    /**
     * Проверяет доступ к действию контроллера.
     * 
     * @param string $action Имя действия.
     * 
     * @return bool Возвращает значение `true`, если текущий пользователь имеет доступ 
     *     к указанному действию контроллера. Иначе, нет.
     */
    protected function accessAction(string $action): bool
    {
        return true;
    }

    /**
     * Проверяет, является ли текущее имя контроллера, именем контроллера по 
     * умолчанию.
     * 
     * Имя контроллера {@see BaseController::$name} сапоставляется с именем контроллера 
     * по умолчанию {@see \Ge\Mvc\Module\BaseModule::$defaultController}.
     * 
     * @return bool Возвращает значение `true`, если текущее имя контроллера - это 
     *     имя контроллера по умолчанию.
     */
    public function isDefault(): bool
    {
        return $this->getShortClass() === $this->module->defaultController;
    }

    /**
     * Проверяет, является ли текущее действие контроллера, действием контроллера по 
     * умолчанию.
     * 
     * Имя действия контроллера {@see BaseController::$actionName} сапоставляется с 
     * действием контроллера по умолчанию {@see BaseController::$defaultAction}.
     * 
     * @return bool  Возвращает значение `true`, если текущее действие контроллера - это 
     *     действие контроллера по умолчанию.
     */
    public function isDefaultAction(): bool
    {
        return $this->actionName === $this->defaultAction;
    }

    /**
     * Событие возникшее перед действием контроллера.
     * 
     * @param string $action Имя действия контроллера.
     * 
     * @return mixed Возвращает результат события. Если значение `true`, событие 
     *     выполнено успешно. Иначе, результат ответа или содержимое, определяющие ошибку.
     */
    protected function beforeAction(string $action): mixed
    {
        $result = true;
        $this->trigger(
            self::EVENT_BEFORE_ACTION, 
            [
                'controller' => $this, 
                'action'     => $action, 
                'result'     => &$result
            ]
        );
        return $result;
    }

    /**
     * Событие возникшее после действия контроллера.
     * 
     * @param string $action Имя действия контроллера.
     * @param mixed $result Результат действия контроллера.
     * 
     * @return void
     */
    protected function afterAction(string $action, $result): void
    {
        $this->trigger(
            self::EVENT_AFTER_ACTION, 
            [
                'controller' => $this, 
                'action'     => $action, 
                'result'     => $result
            ]
        );
    }

    /**
     * Выполняет действие контроллера.
     * 
     * Действие - это выполнение контроллером своего метода, где действие указывается, 
     * как часть имени метода контроллера в виде '<имя действия>Action'.
     * Пример:
     * ```php
     * $controller->doAction('foo'); // выполнение метода `$controller->fooAction()`
     * ```
     * Если контроллер не имеет метода, будет исключение `Exception\ActionNotFoundException`.
     * 
     * Перед выполнением метода, будет вызов события {@see BaseController::EVENT_BEFORE_ACTION}.
     * После выполнением метода, будет вызов события {@see BaseController::EVENT_AFTER_ACTION}, 
     * где одним из параметров - его результат.
     * 
     * @param string $name Имя действия контроллера.
     * @param array $params Параметры вызова текущего действия контроллера в 
     *    виде пар "ключ-значение" (по умолчанию `[]`).
     * 
     * @return mixed
     * 
     * @throws Exception\ActionNotFoundException Контроллер не имеет указанное действие.
     */
    protected function doAction(string $name, array $params = []): mixed
    {
        $method = $name . 'Action';
        if (!method_exists($this, $method)) {
            throw new Exception\ActionNotFoundException(
                Ge::t('app', 'Action "{0}" at controller "{1}" not exists', [$name, $this->getName()]),
                $this->getName()
            );
        }

        $result = $this->beforeAction($name);

        if ($result === true) {
            $result = call_user_func_array([$this, $method], $this->actionParams);
        }

        // если результат запроса - `\Ge\Http\Response`
        if ($result instanceof \Ge\Http\Response) {
            $result = $result->getContent();
        } else {
            $response = $this->getResponse();
            $response->setContent($result);
        }

        $this->afterAction($name, $result);
        return $result;
    }

    /**
     * Возвращает визуализацию представления и применяет макет, если он доступен.
     *
     * - например '@app:views/site/index';
     * - абсолютный путь в приложении, например '//site/index'. 
     * Здесь имя представления начинается с двойной косой черты. Файл представления 
     * будет иметь аболютный путь {@see \Ge\Mvc\Application::$viewPath} приложения.
     * - абсолютный путь внутри модуля, например '/site/index'.
     * Здесь имя представления начинается с одной косой черты. Файл представления будет 
     * иметь аболютный путь {@see \Ge\Mvc\Module\BaseModule::$viewPath} модуля.
     * - относительный путь, например 'index'. 
     * Файл представления будет иметь аболютный путь {@see BaseController::$viewPath}.
     *
     * Чтобы определить, какой макет будет использован, необходимо:
     *
     * a). Определение имени макета:
     *
     * - если {@see BaseController::$layout} указан в виде строки, то используется как 
     * имя макета, а если массив, то как параметры конфигурации макета;
     * - если {@see BaseController::$layout} имеет значение `null`, то определяется из
     * {@see \Ge\Mvc\Module\BaseModule::$layout} или из {@see \Ge\Mvc\Application::$layout}. 
     * Если результирующие значение `null`, то макет не будет применятся.
     *
     * б). Определение файл макета в соответствии с ранее найденным именем макета. 
     * Имя макета может быть:
     *
     * - например '@app:backend:layouts/main' или '@app:frontend:layouts/main';
     * - абсолютный путь, например '/main'.  
     * Здесь имя макета начинается с косой черты. Файл макета будет иметь абсолютный 
     * путь {@see \Ge\Mvc\Application::$layoutPath} приложения;
     * - относительный путь, например 'main'.
     * Файл макета будет иметь аболютный путь {@see \Ge\Mvc\Module\BaseModule::$layoutPath} 
     * модуля.
     *
     * Если имя макета не содержит расширения файла, будет использоваться расширение 
     * по умолчанию `.phtml`.
     *
     * @param string $viewFile Имя представления или имя файла.
     * @param array $params Параметры в виде пары "имя-значение", которые будут 
     *     переданы в представление. Эти параметры не будут доступны в макете (по 
     *     умолчанию `[]`).
     * @param array $config Параметры конфигурации представления (по умолчанию `[]`).
     *     Если представление было создано ранее с помощью {@see BaseController::getView()}, 
     *     параметры конфигурации применяться не будут.
     * 
     * @return string Результат визуализации представления.
     * 
     * @throws Ge\View\Exception\TemplateNotFoundException Невозможно получить имя 
     *     файла шаблона представления или шаблон не существует.
     */
    public function render(string $viewFile, array $params = [], array $config = []): string
    {
        $content = $this->getView($config)->render($viewFile, $params, $this->module);
        return $this->renderContent($content);
    }

    /**
     * Возвращает визуализацию представления без применения макета.
     * 
     * @see BaseController::getView()
     * 
     * @param string $viewFile Имя представления или его файла.
     * @param array $params Параметры в виде пары "имя-значение", которые будут переданы 
     *     в представление (по умолчанию `[]`).
     * @param array $config Параметры конфигурации представления (по умолчанию `[]`).
     *     Если представление было создано ранее с помощью {@see BaseController::getView()}, 
     *     параметры конфигурации применяться не будут.
     * 
     * @return string Результат визуализации представления.
     * 
     * @throws Ge\View\Exception\TemplateNotFoundException Невозможно получить имя 
     *     файла шаблона представления или шаблон не существует.
     */
    public function renderPartial(string $viewFile, array $params = [], array $config = []): string
    {
        return $this->getView($config)->render($viewFile, $params, $this->module);
    }

    /**
     * Возвращает визуализацию макета страницы.
     * 
     * @see \Ge\Mvc\Application::getLayoutView()
     * 
     * @param string $viewFile Имя макета или его файла (по умолчанию `null`).
     * @param array $params Параметры в виде пары "имя-значение", которые будут 
     *     переданы в макет (по умолчанию `[]`).
     * @param array $config Параметры конфигурации макета (по умолчанию `[]`).
     *     Если представление было создано ранее с помощью {@see \Ge\Mvc\Application::getLayoutView()}, 
     *     параметры конфигурации применяться не будут.
     * 
     * @return mixed Результат визуализации макета страницы.
     * 
     * @throws Ge\View\Exception\TemplateNotFoundException Невозможно получить имя 
     *     файла макета или макет не существует.
     */
    public function renderLayout(?string $viewFile = null, array $params = [], array $config = []): mixed
    {
        if ($viewFile === null) {
            $viewFile = $this->findLayout();
            if ($viewFile === null) {
                return null;
            }
        }
        return Ge::$app->getLayoutView($config)->renderLayout($viewFile, $params, $this->module);
    }

    /**
     * Возвращает визуализацию макета страницы.
     * 
     * Если имя макета или его файл не найдены {@see BaseController::findLayout()}, 
     * возвратит аргумент `$content`.
     * 
     * @param mixed $content Содержимое (результат визуализации представления).
     * 
     * @return mixed
     */
    public function renderContent(mixed $content): mixed
    {
        $result = $this->renderLayout(null, ['content' => $content]);
        return $result ?: $content;
    }

    /**
     * Возвращает имя макета или его файла.
     * 
     * @return string|null Возвращает значение `null`, если контроллер, модуль 
     *     или приложение не имеют имя макета или его файла.
     */
    public function findLayout(): ?string
    {
        if ($this->layout)
            $layout = $this->layout;
        else
        if ($this->module->layout)
            $layout = $this->module->layout;
        else
            $layout = Ge::$app->layout[SIDE] ?? Ge::$app->layout;
        return $layout ?: null;
    }

    /**
     * Этот метод вызывается перед запуском контроллера.
     *
     * Метод вызовет событие {@see BaseController::EVENT_BEFORE_RUN}. Возвращаемое значение 
     * метода определит, следует ли продолжать выполнение действия.
     *
     * В случае, если действие не должно выполняться, запрос должен обрабатываться внутри 
     * метода {@see BaseController::beforeRun()}, либо путём предоставления необходимых выходных 
     * данных, либо путем перенаправления запроса. В противном случае ответ будет пустым.
     * 
     * Если вы переопределите этот метод, тогда код должен выглядеть следующим образом:
     * ```php
     * public function beforeRun(BaseController $controller, string $action)
     * {
     *     if (!parent::beforeRun($controller, $action)) {
     *         return false;
     *     }
     *
     *     // здесь ваш код
     *
     *     return true;
     * }
     * ```
     *
     * @param BaseController $controller Текущий контроллер.
     * @param string $action Действие, которое нужно выполнить.
     * 
     * @return bool Если `true`, следует продолжать выполнение действия. Иначе, нет.
     */
    public function beforeRun(BaseController $controller, string $action): bool
    {
        /** @var bool $isValid если действие над контроллером верно */
        $isValid = true;
        $this->trigger(
            self::EVENT_BEFORE_RUN,
            [
                'controller' => $controller,
                'action'     => $action,
                'isValid'    => &$isValid
            ]
        );
        return $isValid;
    }

    /**
     * Этот метод вызывается сразу после выполнения контроллером действия.
     *
     * Метод вызовет событие {@see BaseController::EVENT_AFTER_RUN}. Возвращаемое значение 
     * метода будет использоваться, как возвращаемое значение действия контроллера.
     *
     * Если вы переопределите этот метод, тогда код должен выглядеть следующим образом:
     *
     * ```php
     * public function afterRun(BaseController $controller, string $action, $result)
     * {
     *     $result = parent::afterRun($controller, $action, $result);
     * 
     *     // здесь ваш код
     * 
     *     return $result;
     * }
     * ```
     *
     * @param BaseController $controller Текущий контроллер.
     * @param string $action Действие, которое выполнено.
     * @param mixed $result Результат выполненного действия.
     * 
     * @return mixed Результат выполненного действия.
     */
    public function afterRun(BaseController $controller, string $action, mixed $result): mixed
    {
        $this->trigger(
            self::EVENT_AFTER_RUN,
            [
                'controller' => $controller,
                'action'     => $action,
                'result'     => $result
            ]
        );
        return $result;
    }

    /**
     * Проверка доступа к контроллеру.
     * 
     * Этот метод выполняется перед вызовом события {@see BaseController::EVENT_BEFORE_RUN}.
     * Для проверки доступа к контроллеру, вы можете переопределить этот метод.
     * 
     * @return bool Результат `true`, если контроллер доступен.
     */
    public function onAccess(): bool
    {
        return true;
    }

    /**
     * Запуск контроллера.
     * 
     * @return mixed Возвращает значение `false`, если нет доступа к контроллеру. 
     *     Иначе, результат действия контроллера.
     */
    public function run(): mixed
    {
        // устанавливает контроллер для приложения, который был задействован
        Ge::$app->controller = $this;
        // устанавливает для приложения действие, которое было задействовано
        Ge::$app->action = $this->actionName;
        // проверяет доступ к контроллеру
        if ($result = $this->onAccess()) {
            if ($result = $this->beforeRun($this, $this->actionName)) {
                // выполняет действие
                $result = $this->doAction($this->actionName, $this->actionParams);
                $this->afterRun($this, $this->actionName, $result);
            }
        }
        return $result;
    }
}
