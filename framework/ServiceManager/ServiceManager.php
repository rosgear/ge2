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
use Ge\Exception;
use Ge\Config\Config;

/**
 * Менеджер служб.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\ServiceManager
 * @since 2.0
 */
class ServiceManager extends AbstractManager
{
    /**
     * {@inheritdoc}
     */
    protected array $invokableClasses = [
        'config' => '\Ge\Config\Config'
    ];

    /**
     * Конфигуратор.
     * 
     * Инициализация конфигуратор в {@see \Ge\Mvc\Application::bootstrap()}.
     * 
     * @var Config
     */
    public Config $config;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $classes = $this->config->getAll();
        if (empty($classes)) {
            throw new Exception\BootstrapException('No aliases for service manager found in configuration file.');
        }
        $this->invokableClasses = $classes;
    }

    /**
     * {@inheritdoc}
     */
    public function refresh(): void
    {
        $this->invokableClasses = $this->config->getAll();
    }

    /**
     * Устанавливает имя вызываемой службы (псевдоним) в унифицированный конфигуратор 
     * приложения, в раздел автозагрузки (bootstrap).
     * 
     * Если в автозагрузке указано имя вызываемой службы, то служба будет создана при 
     * инициализации приложения {@see \Ge\Mvc\Application::initServices()}, и будет 
     * вызван метод службы {@see \Ge\Stdlib\Service::bootstrap()}.
     * 
     * @param string $invokeName Имя вызываемой службы (псевдоним).
     * @param array $destination Сторона, вызывающая службу.
     *     Имеет вид: `[BACKEND_NAME => true|false, FRONTEND_NAME => true|false]`.
     *     Если сторона не указана `[]`, служба будет удалена из автозагрузки.
     * 
     * @return $this
     */
    public function setBootstrap(string $invokeName, array $destination): static
    {
        /** @var \Ge\Config\Config $config */
        $config = Ge::$app->unifiedConfig;
        // если конфигуратор не имеет раздел автозагрузки (bootstrap), то
        // наследует его от конфигуратора приложения
        if (!isset($config->bootstrap)) {
            $config->bootstrap = Ge::$app->config->bootstrap;
        }
        $temp = $config->bootstrap;
        $isBackend  = $destination[BACKEND] ?? false;
        $isFrontend = $destination[FRONTEND] ?? false;
        if (!$isBackend && !$isFrontend)
            unset($temp[$invokeName]);
        else
            $temp[$invokeName] = [
                BACKEND  => $isBackend,
                FRONTEND => $isFrontend
            ];
        $config->bootstrap = $temp;
        $config->save();
        return $this;
    }

    /**
     * Сбрасывает (удаляет) все имена (псевдонимы) вызываемых служб в унифицированном 
     * конфигураторе приложения, раздел автозагрузка (bootstrap).
     * 
     * Те службы, которые удалены, не будут создаваться при  инициализации приложения 
     * {@see \Ge\Mvc\Application::initServices()}.
     * 
     * @return $this
     */
    public function resetBootstrap(): static
    {
        /** @var \Ge\Config\Config $config */
        $config = Ge::$app->unifiedConfig;
        $config->bootstrap = Ge::$app->config->bootstrap;
        $config->save();
        return $this;
    }

    /**
     * Добавляет имя вызываемой службы (псевдоним) в унифицированный конфигуратор 
     * приложения, в раздел автозагрузки (bootstrap).
     * 
     * Если в автозагрузке указано имя вызываемой службы, то служба будет создана при 
     * инициализации приложения {@see \Ge\Mvc\Application::initServices()}, и будет 
     * вызван метод службы {@see \Ge\Stdlib\Service::bootstrap()}.
     * 
     * Если служба ранее добавлена в автозагрузку (bootstrap), она будет заменена.
     * 
     * @param string $invokeName Имя вызываемой службы (псевдоним).
     * @param array $destination Сторона, вызывающая службу.
     *     Имеет вид: `[BACKEND_NAME => true|false, FRONTEND_NAME => true|false]`.
     * 
     * @return $this
     */
    public function addToBootstrap(string $invokeName, array $destination): static
    {
        /** @var \Ge\Config\Config $config */
        $config = Ge::$app->unifiedConfig;
        // если конфигуратор не имеет раздел автозагрузки (bootstrap), то
        // наследует его от конфигуратора приложения
        if (!isset($config->bootstrap)) {
            $config->bootstrap = Ge::$app->config->bootstrap;
        }
        $temp = $config->bootstrap;
        $temp[$invokeName] = [
            BACKEND_NAME  => $destination[BACKEND_NAME] ?? false,
            FRONTEND_NAME => $destination[FRONTEND_NAME] ?? false
        ];
        $config->bootstrap = $temp;
        $config->save();
        return $this;
    }

    /**
     * Удаляет имя вызываемой службы (псевдоним) из раздела автозагрузки (bootstrap) 
     * унифицированного конфигуратора приложения.
     * 
     * @param string $invokeName Имя вызываемой службы (псевдоним).
     * 
     * @return $this
     */
    public function removeFromBootstrap(string $invokeName): static
    {
        /** @var \Ge\Config\Config $config */
        $config = Ge::$app->unifiedConfig;
        //  если раздел конфигуратора bootstrap имеет службу
        if (isset($config->bootstrap[$invokeName])) {
            $temp = $config->bootstrap;
            unset($temp[$invokeName]);
            if (empty($temp))
                unset($config->bootstrap);
            else
                $config->bootstrap = $temp;
            $config->save();
        }
        return $this;
    }

    /**
     * Проверяет, имеет ли раздела автозагрузки (bootstrap) унифицированного конфигуратора 
     * приложения имя вызываемой службы (псевдоним).
     * 
     * @param string $invokeName Имя вызываемой службы (псевдоним).
     * 
     * @return bool
     */
    public function hasBootstrap(string $invokeName): bool
    {
        return isset(Ge::$app->unifiedConfig->bootstrap[$invokeName]);
    }
}
