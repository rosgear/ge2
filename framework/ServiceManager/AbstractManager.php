<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\ServiceManager;

use Ge;
use ReflectionClass;
use ReflectionException;

/**
 * Абстрактный класс менеджера служб.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ServiceManager
 * @since 2.0
 */
abstract class AbstractManager
{
    /**
     * Контейнер объектов в виде пар "invokeName => object".
     *
     * @var array
     */
    public array $container = [];

    /**
     * Массив вызываемых служб (псевдонимов).
     * 
     * Имеет вид:
     * - `['invokeName' => 'className', ...]`
     * - `['invokeName' => ['class' => 'className', 'param1' => 'value1', ...], ...]`
     *
     * @var array
     */
    protected array $invokableClasses = [];

    /**
     * Инициализация менеджера.
     * 
     * @return void
     */
    public function init(): void
    {
    }

    /**
     * Обновляет настройки менеджера.
     * 
     * @return void
     */
    public function refresh(): void
    {
    }

    /**
     * Нормализация входных аргументов функции.
     * 
     * Входные аргументы `$arguments` функции могут иметь вид:
     * - `['className']`
     * - `['className', 'param1', 'param2'...]`
     * - `['class' => 'className', 'param1' => 'value1', 'param2' => 'value2'...]`
     * 
     * Приведение аргументов функции к виду `[0, 1]`, где:
     * - 0 (string), имя вызываемой службы (псевдоним);
     * - 1 (array), аргументы конструктора;
     * - 2 (array), конфигурация класса.
     * 
     * @param array $arguments Аргументы.
     * 
     * @return array Нормализованные аргументы функции.
     */
    public function normalizeParams(array $arguments): array
    {
        $params = $arguments[0] ?? null;
        // для ['name'] и ['name', 'param1', 'param2'...]
        if (is_string($params)) {
            array_shift($arguments);
            return [$params, $arguments, []];
        }
        // для [...]
        if (is_array($params)) {
            // для ['class' => 'name', 'construct' => ['param1', 'param2',...], 'param1' => 'value1', 'param2' => 'value2',...]
            if (isset($params['class'])) {
                $type = $params['class'];
                unset($params['class']);
                if (isset($params['construct'])) {
                    $construct = $params['construct'];
                    unset($params['construct']);
                } else
                    $construct = [];
                // т.к. ['param1' => 'value1'...] - конфигурация класса и это 1-н аргумент
                //return [$type, $construct, $params ? [$params] : []];
                return [$type, $construct, $params];
            // для ['name', 'param1', 'param2'...]
            } else {
                $type = $params[0];
                array_shift($params);
                return [$type, $params, []];
            }
        }
        return $arguments;
    }

    /**
     * Нормализация параметров конфигурации службы (файл конфигурации служб приложения).
     * 
     * Конфигурация может иметь вид:
     * - имя класса:
     * ```php
     * ['invokeName' => 'className',...]
     * ```
     * - конфигурация класса:
     * ```php
     * ['invokeName' => ['class' => 'className', 'construct' => ['param1',...], 'param1' => 'value1',...],...]
     * ```
     * Результат имеет вид `[0, 1, 2]`, где:
     * - 0 (string), имя вызываемой службы (псевдоним);
     * - 1 (array), аргументы конструктора;
     * - 2 (array), конфигурация класса.
     * 
     * @param string|array $params Параметр (имя класса, конфигурация класса) вызываемой службы (псевдонима).
     * 
     * @return array Нормализованный параметр вызываемой службы (псевдонима).
     */
    public function normalizeInvokableParams(string|array  $params): array 
    {
        // для 'invokeName' => 'className'
        if (is_string($params)) {
            return [$params, [], []];
        }
        // для 'invokeName' => ['class' => 'className', 'param1' => 'value1', ...]
        if (is_array($params) && isset($params['class'])) {
            $type = $params['class'];
            unset($params['class']);
            return [$type, [], $params];
        }
        throw new Exception\InvalidArgumentException(
            sprintf('The $params argument is incorrectly specified $s::normalizeInvokableParams', gettype($this))
        );
    }

    /**
     * Создаёт экземпляр класса с указанием аргументов его конструктора.
     * 
     * @param string $className Имя класса.
     * @param array $arguments Аргументы конструктора класса.
     * 
     * @return object|null Возвращает значение `null`, если невозможно создать экземпляр класса.
     * 
     * @throws Exception\NotInstantiableException Ошибка при создании экземпляра класса.
     */
    public function createInstance(string $className, array $arguments = []): ?object
    {
        try {
            $reflection = (new ReflectionClass($className))->newInstanceArgs($arguments);
        } catch (ReflectionException $e) {
            Ge::error('Create instance for Class "' . $className . '"', ['line' => __LINE__, 'file' => __FILE__]);
            throw new Exception\NotInstantiableException($className);
        }
        return $reflection;
    }

    /**
     * Проверяет, существует ли экземпляра класса в контейнере.
     * 
     * @param string $invokeName Имя вызываемой службы (псевдоним).
     * 
     * @return bool Если true, экземпляр класса существует в контейнере.
     */
    public function has(string $invokeName): bool
    {
        return isset($this->container[$invokeName]);
    }

    /**
     * Удлаяет экземпляр класса с указанным именем службы (псевдонимом) из контейнера.
     * 
     * @param string $invokeName Имя вызываемой службы (псевдоним).
     * 
     * @return $this
     */
    public function remove(string $invokeName): static
    {
        if (isset($this->container[$invokeName])) {
            unset($this->container[$invokeName]);
        }
        return $this;
    }

    /**
     * Добавляет экземпляр класса с указанным именем службы (псевдонимом) в контейнер.
     * 
     * @param string $invokeName Имя вызываемой службы (псевдоним).
     * @param array|string|object $object Если объект `string` или `array`, то имени 
     *     службы (псевдониму) устанавливается имя класса или конфигурация. Иначе, объект.
     * 
     * @return $this
     */
    public function set(string $invokeName, array|string|object $object): static
    {
        // если объект - имя класса.
        if (is_string($object) || is_array($object)) {
            $this->invokableClasses[$invokeName] = $object;
        } else {
            $this->container[$invokeName] = $object;
        }
        return $this;
    }

    /**
     * Возвращает указатель на экземпляр класса по указанному имени службы (псевдониму).
     * 
     * @param array $params Имя вызываемой службы (псевдоним) c указанием аргументов 
     *     конструктора или конфигурация класса.
     * 
     * @return object|null Возвращает значение `null`, если невозможно создать экземпляр класса.
     * 
     * @throws Exception\NotInstantiableException Ошибка при создании экземпляра класса.
     */
    public function get(...$params): ?object
    {
        /**
         * @var string $invokeName имя службы (псевдоним) или имя класса
         * @var array $construct аргументы конструктора 
         * @var array $config конфигурация класса
         */
        list($invokeName, $construct, $config) = $this->normalizeParams($params);
        return $this->getAs($invokeName, $construct, $config);
    }

    /**
     * Возвращает указатель на экземпляр класса c указанием аргументов его конструктора.
     * 
     * Если экземпляр класса не создан, создаёт его.
     * 
     * @param string $invokeName Имя вызываемой службы (псевдоним).
     * @param array $construct Аргументы конструктора класса или конфигурация класса.
     * 
     * @return object|null Возвращает значение `null`, если невозможно создать экземпляр класса.
     * 
     * @throws Exception\NotInstantiableException Ошибка при создании экземпляра класса.
     */
    public function getAs(string $invokeName, array $construct = [], array $config = []): ?object
    {
        // если контейнер имеет объект
        if (isset($this->container[$invokeName])) {
            return $this->container[$invokeName];
        }
        $object = $this->createAs($invokeName, $construct, $config);
        $this->set($invokeName, $object);
        return $object;
    }

    /**
     * Создаёт указатель на экземпляр класса по указанному параметрам (имя службы (псевдоним)
     * 
     * Аргументы функции являются аргументами конструктора класса.
     * 
     * @param array $params Имя вызываемой службы (псевдоним) c указанием аргументов 
     *     конструктора или конфигурация класса.
     * 
     * @return object|null Возвращает значение `null`, если невозможно создать экземпляр класса.
     * 
     * @throws Exception\NotInstantiableException Ошибка при создании экземпляра класса.
     */
    public function create(...$params): ?object
    {
        /**
         * @var string $invokeName имя службы (псевдоним) или имя класса
         * @var array $construct аргументы конструктора 
         * @var array $config конфигурация класса
         */
        list($invokeName, $construct, $config) = $this->normalizeParams($params);
        return $this->createAs($invokeName, $construct, $config);
    }

    /**
     * Возвращает указатель на экземпляр класса c указанием аргументов его конструктора.
     * 
     * Если экземпляр класса не создан, создаёт его.
     * 
     * @param string $invokeName Имя вызываемой службы (псевдоним).
     * @param array $construct Аргументы конструктора класса или конфигурация класса.
     * @param array $config Конфигурация класса.
     * 
     * @return object|null Возвращает значение `null`, если невозможно создать экземпляр класса.
     * 
     * @throws Exception\NotInstantiableException Ошибка при создании экземпляра класса.
     */
    public function createAs(string $invokeName, array $construct = [], array $config = []): ?object
    {
        $arguments = $construct;
        if (isset($this->invokableClasses[$invokeName])) {
            list($className, $nconstruct, $nconfig) = $this->normalizeInvokableParams($this->invokableClasses[$invokeName]);
            if ($nconfig) {
                $config = $config ? array_merge($nconfig, $config) : $nconfig;
            }
            if ($config)
                $arguments[] = $config;
            return $this->createInstance($className, $arguments);
        } else {
            if ($config) {
                $arguments[] = $config;
            }
            return $this->createInstance($invokeName, $arguments);
        }
    }

    /**
     * Устанавливает имя службы (псевдоним) с указанным ему классом.
     * 
     * @param string $invokeName Имя вызываемой службы (псевдоним).
     * @param string|array $className Имя класса или параметры.
     * 
     * @return $this
     */
    public function setInvokableClass(string $invokeName, string|array $className): static
    {
        $this->invokableClasses[$invokeName] = $className;
        return $this;
    }

    /**
     * Устанавливает имена служб (псевдонимов) с соответствующим им классам.
     * 
     * @param array $classes Имена вызываемых служб (псевдонимов).
     * 
     * @return $this
     */
    public function setInvokableClasses(array $classes): static
    {
        $this->invokableClasses = $classes;
        return $this;
    }

    /**
     * Добавляет имена служб (псевдонимов) с соответствующим им классам.
     * 
     * Если псевдонимы уже существуют, заменяет их.
     * 
     * @param array $classes Имена вызываемых служб (псевдонимов).
     * 
     * @return $this
     */
    public function addInvokableClasses(array $classes): static
    {
        $this->invokableClasses = array_merge($classes, $this->invokableClasses);
        return $this;
    }

    /**
     * Добавляет псевдоним с соответствующим ему классом или массив псевдонимов в виде 
     * пар "псевдоним - имя класса".
     * 
     * @param string|array $invokeName Псевдоним класса или массив псевдонимов.
     * @param string|null $className Имя класса (по умолчанию `null`).
     * 
     * @return void
     */
    public function register(string|array $invokeName, ?string $className = null): void
    {
        if ($className === null)
            $this->invokableClasses = array_merge($this->invokableClasses, (array) $invokeName);
        else
            $this->invokableClasses[$invokeName] = $className;
    }

    /**
     * Удаляет псевдонимы класса.
     * 
     * @param string|array $invokeName Псевдоним или массив псевдонимов класса.
     * 
     * @return void
     */
    public function unregister(string|array $invokeName): void
    {
        $invokeName = (array) $invokeName;
        foreach ($invokeName as $one) {
            unset($this->invokableClasses[$one]);
        }
    }

    /**
     * Проверяет, добавлен ли псевдоним класса.
     * 
     * @param string $invokeName Имя псевдонима класса.
     * 
     * @return bool Возвращает значение `true`, если псевдоним добавлен, в противном 
     *     случае `false`.
     */
    public function isRegistered(string $invokeName): bool
    {
        return isset($this->invokableClasses[$invokeName]);
    }

    /**
     * Проверяет имя вызываемой службы (псевдонима).
     * 
     * @param string $invokeName Имя вызываемой службы (псевдоним).
     * 
     * @return bool Возвращает значение `true`, если вызываемая служба (псевдоним) 
     *     существует.
     */
    public function hasInvokableClass(string $invokeName): bool
    {
        return isset($this->invokableClasses[$invokeName]);
    }

    /**
     * Удаляет имя вызываемой службы (псевдоним).
     * 
     * @param string $invokeName Имя вызываемой службы (псевдоним).
     * 
     * @return $this
     */
    public function removeInvokableClass(string $invokeName): static
    {
        if (isset($this->invokableClasses[$invokeName])) {
            unset($this->invokableClasses[$invokeName]);
        }
        return $this;
    }

    /**
     * Удаляет все имена вызываемых служб (псевдонимов).
     * 
     * @return $this
     */
    public function clearInvokableClasses(): static
    {
        $this->invokableClasses = [];
        return $this;
    }

    /**
     * Возвращает имя класса по указанному имени вызываемой службы (псевдонима).
     * 
     * @param string $invokeName Имя вызываемой службы (псевдоним).
     * 
     * @return string Если имя класса не существует, возвращает указанное имя вызываемой 
     *     службы (псевдоним).
     */
    public function getClassName(string $invokeName): string
    {
        $className = isset($this->invokableClasses[$invokeName]) ? $this->invokableClasses[$invokeName] : $invokeName;
        if (is_string($className)) {
            return $className;
        }
        if (is_array($className) && isset($className['class'])) {
            return $className['class'];
        }
        return $invokeName;
    }
}
