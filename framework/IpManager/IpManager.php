<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\IpManager;

use Ge\Exception;
use Ge\Stdlib\Service;
use Ge\IpManager\Adapter\AdapterInterface;

/**
 * Менеджер IP-адресов.
 * 
 * IpManager - это служба приложения, доступ к которой можно получить через `Ge::$app->ip`. 
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\BlockIpAddress
 * @since 2.0
 */
class IpManager extends Service
{
    /**
     * Классы адаптеров проверки IP-адресов.
     *
     * @var array<string, string>
     */
    protected array $adapterClasses = [
        'dbBlock'   => '\Ge\IpManager\Adapter\DbBlockAdapter',
        'fileBlock' => '\Ge\IpManager\Adapter\FileBlockAdapter',
        'dbList'    => '\Ge\IpManager\Adapter\DbListAdapter',
    ];

    /**
     * Настройки адаптеров.
     *
     * @var array<string, array{adapter:string, tableName:string}>
     */
    public array $listOptions = [
        'black'   => [
            'adapter'   => 'dbList',
            'tableName' => '{{ip_blacklist}}'
        ],
        'white'   => [
            'adapter'   => 'dbList',
            'tableName' => '{{ip_whitelist}}'
        ],
        'blocked' => [
            'adapter'   => 'dbBlock',
            'tableName' => '{{ip_blocklist}}'
        ]
    ];

    /**
     * Список созданных адаптеров.
     * 
     * @see IpManager::list()
     * 
     * @var array<string, AdapterInterface>
     */
    protected $lists = [];

    /**
     * Последний созданный адаптер.
     * 
     * @var AdapterInterface|null
     */
    protected ?AdapterInterface $list = null;

    /**
     * Возвращает адаптер по указанному имени.
     * 
     * @param string $name Имя адаптера.
     * 
     * @return AdapterInterface
     * 
     * @throws Exception\InvalidArgumentException Настройки адаптеры не найдены.
     * @throws Exception\InvalidArgumentException Адаптер не существует с указанным именем.
     */
    public function list(string $name): AdapterInterface
    {
        if (isset($this->lists[$name])) {
            return $this->lists[$name];
        }
        $this->list = $this->createList($name);
        return $this->lists[$name] = $this->list;
    }

    /**
     * Создаёт адаптера по указанному имени.
     * 
     * @see IpManager::createAdapter()
     * 
     * @param string $name Имя адаптера.
     * 
     * @return AdapterInterface
     * 
     * @throws Exception\InvalidArgumentException Настройки адаптеры не найдены.
     */
    public function createList(string $name): AdapterInterface
    {
        $options = $this->listOptions[$name] ?? null;
        if ($options) {
            return $this->createAdapter($options['adapter'], $options);
        }
        throw new Exception\InvalidArgumentException(sprintf('List with name "%s" not exists.', $name));
    }

    /**
     * Возвращает адаптера по указанному имени.
     * 
     * Если имя адаптера не указано, то возвратит последний созданный адаптер {@see IpManager::$list}.
     * 
     * @param null|string $name Имя адаптера (по умолчанию `null`).
     * 
     * @return AdapterInterface|null Значение `null`, если адаптер ещё не создан по 
     *     указанному имени.
     */
    public function getList(?string $name = null): ?AdapterInterface
    {
        if ($name) {
            return $this->lists[$name] ?? null;
        }
        return $this->list;
    }

   /**
     * Возвращает класс адаптера по указанному имени.
     * 
     * @param string $name Имя адаптера.
     * 
     * @return string|null Возвращает значение `null` если имя адаптера не найдено.
     */
    public function getAdapterClass(string $name): ?string
    {
        return $this->adapterClasses[$name] ?? null;
    }

    /**
     * Создаёт и возвращает адаптер с указанным именем.
     *
     * @param string $name Имя адаптера.
     * @param array<string, mixed> $options Настройки адаптера.
     * 
     * @return AdapterInterface
     * 
     * @throws Exception\InvalidArgumentException Адаптер не существует с указанным именем.
     */
    public function createAdapter(string $name, array $options): AdapterInterface
    {
        $className = $this->getAdapterClass($name);
        if ($className) {
            if(class_exists($className)) {
                return new $className($options);
            }
        }
        throw new Exception\InvalidArgumentException(sprintf('Adapter "%s" not exists.', $name));
    }

    /**
     * Запускается при вызове недоступных методов в контексте объект. 
     * 
     * Вызывает метод последнего созданного адаптера.
     * 
     * @param string $name Имя вызываемого метода адаптера.
     * @param array $arguments Нумерованный массив, содержащий параметры, переданные 
     *     в вызываемый метод $name. 
     * 
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        return call_user_func_array([$this->list, $name], $arguments);
    }
}
