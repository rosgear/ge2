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
use Exception;
use Ge\Log\Logger;
use Ge\Helper\Browser;
use Ge\Filesystem\Filesystem;

/**
 * Писатель отладочной информации в журнал.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Debug
 * @since 2.0
 */
class DebugWriter extends BaseWriter
{
    /**
     * Путь к каталогу файлов журнала.
     * 
     * Путь может содержать псевдонимы, пример "@runtime::/example".
     * 
     * @var string
     */
    public string $path = '@runtime/log';

    /**
     * Максимальное количество стиков в журнале.
     * Если 0, ограничений нет.
     * 
     * @var int
     */
    public int $historySize = 10;

    /**
     * Идентификатор стика.
     * 
     * @see DebugWriter::initStick()
     * 
     * @var string
     */
    public string $stickId;

    /**
     * Разрешение, которое будет установлено для созданного файла журнала.
     * 
     * Это значение будет установлено функцией PHP chmod().
     * Если разрешение не установлено, тогда будет определяться текущей средой.
     * 
     * @var string|int
     */
    public string|int $fileMode;

    /**
     * Разрешение, которое будет установлено для созданного каталога.
     * 
     * Это значение будет установлено функцией PHP chmod().
     * По умолчанию 0775, означает, что каталог доступен для чтения владельцу и группе, 
     * но для чтения только другими пользователей.
     * 
     * Устанавливается в опциях ($options) конструктора класса {@see AbstractWriter}.
     * 
     * @var string|int
     */
    public string|int $dirMode = 0775;

    /**
     * {@inheritdoc}
     */
    protected bool $closed = false;

    /**
     * Журнал стиков.
     * 
     * @see DebugWriter::getIndexSticks()
     * 
     * @var array
     */
    protected array $indexSticks;

    /**
     * {@inheritdoc}
     */
    public array $excludeRoutes = ['debugtoolbar'];

    /**
     * Определение пути к каталогу файлов журнала.
     * 
     * @return $this
     */
    public function initPath(): static
    {
        if (!isset($this->path))
            $this->path = Ge::$app->getRuntimePath() . '/log';
        else
            $this->path = Ge::getAlias($this->path);
        return $this;
    }

    /**
     * Определяет идентификатора стика.
     * 
     * @return $this
     */
    public function initStick(): static
    {
        $this->stickId = uniqid();
        return $this;
    }

     /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this
            ->initStick()
            ->initPath();
    }

    /**
     * Предварительная обработка стиков перед сохранением.
     * 
     * @param array $sticks Стики.
     * 
     * @return void
     */
    public function checkHistorySticks(array &$sticks): void
    {
        if (sizeof($sticks) < $this->historySize) {
            return;
        }
        // удаляем лишнее
        $splice = array_splice($sticks, $this->historySize);
        foreach ($splice as $stick) {
            $this->removeStickFile($stick['stick']);
        }
    }

    /**
     * Чтение стика из файла.
     * 
     * @param string $filename Имя файла стика с указанием пути.
     * 
     * @return false|array Возвращает значение `false`, если невозможно загрузить 
     *    файл стика.
     */
    public function loadStickFile(string $filename): false|array
    {
        $content = file_get_contents($filename, FILE_USE_INCLUDE_PATH);
        if ($content !== false) {
            return unserialize($content);
        }
        return false;
    }

    /**
     * Сохранение текста в файл журнала.
     * 
     * @param string $filename Имя файла с путем.
     * @param mixed $content Содержимое файла.
     * 
     * @return void
     */
    public function saveStickFile(string $filename, mixed $content): void
    {
        file_put_contents($filename, serialize($content));
        if ($this->fileMode) {
            @chmod($filename, $this->fileMode);
        }
    }

    /**
     * Сохранение журнала стиков в файл.
     * 
     * @param string $filename Имя файла с путем.
     * @param mixed $content Контент.
     * 
     * @return void
     * 
     * @throws Exception Невозможно открыть основной файл отладки.
     */
    public function saveIndexFile(string $filename, mixed $content): void
    {
        if (!@touch($filename) || ($file = @fopen($filename, 'r+')) === false) {
            throw new Exception(sprintf('Unable to open main debug file "%s"', $filename));
        }

        @flock($file, LOCK_EX);
        $sticks = '';
        while (($buffer = fgets($file)) !== false) {
            $sticks .= $buffer;
        }

        if (!feof($file) || empty($sticks))
            $sticks = [];
        else
            $sticks = unserialize($sticks);
        $sticks = [$this->stickId => $content] + $sticks;
        $this->checkHistorySticks($sticks);

        ftruncate($file, 0);
        rewind($file);
        fwrite($file, serialize($sticks));
        @flock($file, LOCK_UN);
        @fclose($file);
        if ($this->fileMode) {
            @chmod($filename, $this->fileMode);
        }
    }

    /**
     * Чтение журнала стиков из файла.
     * 
     * @param string $filename Имя файла с путем.
     * 
     * @return array
     * 
     * @throws Exception Невозможно открыть файл для чтения.
     */
    public function loadIndexFile(string $filename): array
    {
        if (!@touch($filename) || ($file = @fopen($filename, 'r+')) === false) {
            throw new Exception(sprintf('Unable to open main debug file "%s"', $filename));
        }

        @flock($file, LOCK_EX);
        $sticks = '';
        while (($buffer = fgets($file)) !== false) {
            $sticks .= $buffer;
        }

        if (!feof($file) || empty($sticks))
            $sticks = [];
        else
            $sticks = unserialize($sticks);
        return $sticks;
    }

    /**
     * Возвращает журнал стиков.
     * 
     * @return array
     * 
     * @throws Exception Невозможно открыть файл для чтения.
     */
    public function getIndexSticks(): array
    {
        if (!isset($this->indexSticks)) {
            $this->indexSticks = $this->loadIndexFile($this->getIndexFilename());
        }
        return $this->indexSticks;
    }

    /**
     * Возвращает количество стиков в журнале.
     * 
     * @return int
     * 
     * @throws Exception Невозможно открыть файл для чтения.
     */
    public function getCountSticks(): int
    {
        $sticks = $this->getIndexSticks();
        return sizeof($sticks);
    }

    /**
     * Удаляет журнал стиков.
     * 
     * @return bool|string Если значение `false`, файл не существует или невозможно 
     *    его удалить. Если `true` файл удален, иначе имя файла.
     */
    public function removeIndexFile(): bool|string
    {
        $filename = $this->getIndexFilename();
        return file_exists($filename) && @unlink($filename) ? true : $filename;
    }

    /**
     * Удаляет файлы стиков из журнала.
     * 
     * @return bool|array Если значение `true`, файлы удалены, иначе имена файлов, 
     *     которые невозможно удалить.
     */
    public function removeStickFiles(): bool|array
    {
        $sticks = $this->getIndexSticks();
        $result = array();
        foreach($sticks as $stick) {
            $filename = $this->getStickFilename($stick['stick']);
            if (file_exists($filename) && !@unlink($filename)) {
                $result[] = $filename;
            }
        }
        return $result ? $result : true;
    }

    /**
     * Удаляет файл стика.
     * 
     * @param string $stickId Идентификатор стика.
     * 
     * @return bool Если значение `false`, то файл невозможно удалить или он не 
     *    существует.
     */
    public function removeStickFile(string $stickId): bool
    {
        $filename = $this->getStickFilename($stickId);
        return file_exists($filename) && @unlink($filename);
    }

    /**
     * Собирает информацию для стика.
     * 
     * @return array
     */
    protected function collectStickData(): array
    {
        $export = [];
        foreach($this->messages as $message) {
            $category = isset($message['category']) ? strtolower($message['category']) : null;
            if (!$category) {
                continue;
            }
            if (!isset($export[$category])) {
                $export[$category] = [];
            }
            $export[$category][] = $message;
        }
        return $export;
    }

    /**
     * Собирает информацию о стиках для формирования журнала.
     * 
     * @return array
     */
    protected function collectIndexData(): array
    {
        $response = Ge::$app->response ?? null;
        $content = [
            'time'           => time(),
            'stick'          => $this->stickId,
            'route'          => Ge::getAlias('@route'),
            'method'         => Ge::$app->request->getMethod(),
            'ipaddress'      => Ge::$app->request->getUserIp(),
            'browserName'    => Browser::browserName(),
            'browserFamily'  => Browser::browserFamily(),
            'osName'         => Browser::platformName(),
            'osFamily'       => Browser::platformFamily(),
            'ajax'           => (int) Ge::$app->request->isAjax(),
            'statusCode'     => $response ? $response->getStatusCode() : '',
            'statusCategory' => $response ? $response->getStatusCategory() : '',
            'amountMemory'   => memory_get_usage(true),
            'peakMemory'     => memory_get_peak_usage(true),
        ];
        // текущий модуль
        if ($module = Ge::$app->module) {
            $content['module'] = $module->id;
            // текущий контроллер
            if ($controller = $module->controller()) {
                $queryId = Ge::getAlias('@match:id');
                $content['controller'] = $controller->getShortClass();
                $action = $controller->getActionName();
                if (is_string($action))
                    $content['action'] = $action;
                else
                    $content['action'] = '{' . gettype($action) . '}';
                $content['queryId'] = $queryId ? $queryId : '';
            }
        }
        return $content;
    }

    /**
     * Возвращает имя файла журнала с путем.
     * 
     * @return string
     */
    public function getIndexFilename(): string
    {
        return $this->path . '/index.dat';
    }

    /**
     * Возвращает имя файла (включая путь) стика.
     * 
     * @param null|string $stickId Идентификатор стика. Если значение `null`, то используется 
     *     {@see DebugWriter::$stickId} (по умолчанию `null`).
     * 
     * @return string
     */
    public function getStickFilename(?string $stickId = null): string
    {
        if ($stickId === null) {
            $stickId = $this->stickId;
        }
        return $this->path . '/' . $stickId . '.dat';
    }

    /**
     * Проверяет, существует ли файла указанного стика.
     * 
     * @see DebugWriter::getStickFilename()
     * 
     * @param null|string $stickId Идентификатор стика. Если значение `null`, то используется 
     *     {@see DebugWriter::$stickId} (по умолчанию `null`).
     * 
     * @return bool
     */
    public function existsStickFile(?string $stickId = null): bool
    {
        return file_exists($this->getStickFilename($stickId));
    }

    /**
     * Добавление сообщения в стек сообщений.
     * 
     * @param mixed $message Сообщение.
     * 
     * @return void
     */
    public function writeInfo(mixed $message, array $extra, string $category)
    {
        $seconds = explode('.', microtime(true));
        $this->write([
            'category'     => $category,
            'timestamp'    => $seconds[0],
            'microsecond'  => $seconds[1] ?? 0,
            'priority'     => Logger::INFO,
            'priorityName' => Logger::$priorityNames[Logger::INFO],
            'message'      => $message,
            'extra'        => $extra,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function writeAll(): void
    {
        if (empty($this->messages)) {
            return;
        }

        if (!Filesystem::exists($this->path)) {
            Filesystem::makeDirectory($this->path, $this->dirMode, true, true);
        }

        $messages = $this->collectStickData();
        $content  = $this->collectIndexData();

        $this->saveStickFile($this->getStickFilename(), $messages);
        $this->saveIndexFile($this->getIndexFilename(), $content);
    }

    /**
     * Запись стека сообщений в журнал.
     * 
     * @return void
     */
    public function close(): void
    {
        if (!$this->closed && $this->enabled && $this->allowed) {
            $this->writeRequest();
            $this->writeResponse();
            $this->writeServer();
            $this->writeProfiling();
        }

        parent::close();
    }

    /**
     * Добавление отладочной информации о запросе пользователя перед окончанием записи 
     * писателем.
     * 
     * @return void
     */
    protected function writeRequest(): void
    {
        // сессия
        if (isset($_SESSION))
            $this->writeInfo('Session', $_SESSION, 'Request');

        // маршрут
        $route = [
            'URL'    => \Ge\Helper\Url::home(true),
            'Route'  => Ge::$app->urlManager->route,
            'Request' => Ge::$app->urlManager->isBackendRoute() ? BACKEND : FRONTEND,
            'Method' => Ge::$app->request->getMethod(),
            'AJAX'   => Ge::$app->request->isAjax() ? 'true' : 'false',
            'PJAX'   => Ge::$app->request->IsPjax() ? 'true' : 'false',
        ];
        if ($module = Ge::$app->module) {
            $route['Module'] = $module->id;
            if ($controller = $module->controller()) {
                $query = Ge::getAlias('@match:id');
                $route['Controller'] = $controller->getName();
                $action = $controller->action();
                if (is_object($action))
                    $route['Action'] = @get_class($action);
                else
                    $route['Action'] = is_string($action) ?: gettype($action) . '[...]';
                $route['Query action'] = $query ? $query : '';
                if (Ge::$app->router->getRouteMatch())
                    $route['Parameters'] = Ge::$app->router->getRouteMatch()->getAll();
                else
                    $route['Parameters'] = [];
            }
        }
        $route['Script file'] = Ge::$app->request->getScriptFile();
        $route['Script name'] = Ge::$app->request->getScriptName();
        $route['Script URL'] = Ge::$app->request->getScriptUrl();
        $this->writeInfo('Route', $route, 'Request');

        // GET
        if (!empty($_GET))
            $this->writeInfo('$_GET', $_GET, 'Request');
        // POST
        if (!empty($_POST))
            $this->writeInfo('$_POST', $_POST, 'Request');
        // COOKIE
        if (!empty($_COOKIE))
            $this->writeInfo('$_COOKIE', $_COOKIE, 'Request');
        // FILES
        if (!empty($_FILES))
            $this->writeInfo('$_FILES', $_FILES, 'Request');
        // SERVER
        $this->writeInfo('$_SERVER', $_SERVER, 'Request');

        // заголовки
        $this->writeInfo('Headers', Ge::$app->request->getHeaders()->toArray(), 'Request');
    }

    /**
     * Добавление отладочной информации о статусе 
     * перед окончанием записи писателем.
     * 
     * @return void
     */
    protected function writeServer(): void
    {
        $this->writeInfo('Aliases', Ge::$aliases, 'Server');

        // какие службы были задействованы
        $rows = [];
        $services = Ge::$services->config->getAll();
        foreach ($services as $name => $options) {
            if (is_string($options)) {
                $className  = $options;
            } else {
                $className  = isset($options['class']) ? $options['class'] : '';
            }
            if (Ge::$services->hasInvokableClass($name)) {
                $rows[$name] = $className;
            }
        }
        $this->writeInfo('Services', $rows, 'Server');
    }

    /**
     * Добавление отладочной информации о ответе пользователю 
     * перед окончанием записи писателем.
     * 
     * @return void
     */
    protected function writeResponse(): void
    {
        if (isset(Ge::$app->response)) {
            // контент
            $content = [
                'format'     => Ge::$app->response->format,
                'content'    => Ge::$app->response->getSentContent(),
                'rawContent' =>  Ge::$app->response->getRawContent()
            ];
             $this->writeInfo('Content', $content, 'Response');
            // ответ
             $this->writeInfo('Format response', array(
                'Service name' => Ge::$app->response->getObjectName(),
                'Status code'  => Ge::$app->response->getStatusCode(),
                'Format'       => Ge::$app->response->format
            ), 'Response');
            // заголовки
            $params = array();
            if ($headers = Ge::$app->response->getHeaders()->toArray()) {
                foreach($headers as $name => $value) {
                    $params['[' . $name . ']'] = $value;
                }
            }
            $this->writeInfo('Headers', $params, 'Response');
        }


        // если задействованы скрипты клиента
        if (isset(Ge::$app->clientScript)) {
            // пакеты скриптов клиента
            $params = Ge::$app->clientScript->getPackages();
            if ($params) {
                 $this->writeInfo('Script packages', $params, 'Response');
            }
            // зарегистрированные пакеты скриптов клиента
            $params = Ge::$app->clientScript->getRegisterPackages();
            if ($params) {
                 $this->writeInfo('Registered script packages', $params, 'Response');
            }
            // мета теги
            $params = Ge::$app->clientScript->meta->toArray();
            if ($params) {
                 $this->writeInfo('Meta tags', $params, 'Response');
            }
        }
    }

    /**
     * @return void
     */
    protected function writeProfiling(): void
    {
    }
}
