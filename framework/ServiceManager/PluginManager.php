<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\ServiceManager;

/**
 * Менеджер плагинов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ServiceManager
 * @since 2.0
 */
class PluginManager extends AbstractManager
{
    /**
     * Параметры конфигурации менеджера.
     *
     * @var array
     */
    protected array $config = [];

    /**
     * Конструктор класса.
     * 
     * @param array $config Параметры конфигурации менеджера.
     * 
     * @return void
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->invokableClassesFromMap();
    }

    /**
     * Добавляет имена плагинов с указанными им классами.
     * 
     * @return $this
     */
    protected function invokableClassesFromMap(): static
    {
        if ($pluginMap = $this->getPluginMap()) {
            foreach ($pluginMap as $name => $params) {
                $this->setInvokableClass($name, $params);
            }
        }
        return $this;
    }

    /**
     * Возвращает параметры указанного плагина.
     * 
     * @param string $pluginName Имя плагина.
     * 
     * @return null|array Если null, плагин не существует. Иначе, параметры плагина.
     */
    public function getPluginParams(string $pluginName)
    {
        return $this->config['pluginMap'][$pluginName] ?? null;
    }

    /**
     * Возвращает параметр указанного плагина.
     * 
     * @param string $pluginName Имя плагина.
     * @param string $param Имя параметра плагина.
     * 
     * @return mixed Возвращает значение `null`, если параметр плагина не существует.
     */
    public function getPluginParam(string $pluginName, string $param): mixed
    {
        $params = $this->getPluginParams($pluginName);
        return $params ? ($params[$param] ?? null) : null;
    }

    /**
     * Возвращает заголовок указанного плагина.
     * 
     * @param string $pluginName Имя плагина.
     * @param string $param Имя параметра заголовка плагина.
     * 
     * @return mixed Если параметр заголовка отсутствует, возврашает имя плагина.
     */
    public function getPluginTitle(string $pluginName, string $param = 'title'): mixed
    {
        $params = $this->getPluginParams($pluginName);
        return $params ? ($params[$param] ?? $pluginName) : $pluginName;
    }

    /**
     * Возвращает имена плагинов с их параметрами.
     * 
     * @return null|array Возвращает значение `null`, если карта плагинов отсутствует.
     */
    public function getPluginMap(): ?array
    {
        return $this->config['pluginMap'] ?? null;
    }

    /**
     * Возвращает список имён плагинов.
     * 
     * @param string $param Имя параметра, отображающий имя или заголовок плагина.
     * @param null|array $translator Транслятор параметра `$param` палагина.
     * 
     * @return array Список плагинов имеет вид:
     * ```php
     * [
     *      ['plugin1', 'Plugin 1'],
     *      ['plugin2', 'Plugin 2'],
     * ]
     * ```
     */
    public function getPluginRows(string $param = 'title', ?array $translator = null): array
    {
        $pluginMap = $this->getPluginMap();
        $rows = [];
        if ($pluginMap) {
            foreach ($pluginMap as $name => $params) {
                $rows[] = [$name, $translator ? call_user_func($translator, $params[$param]) : $param];
            }
        }
        return $rows;
    }

    /**
     * Возвращает параметры конфигурации менеджера.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
