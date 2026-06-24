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
 * Класс шагов установки, определяющий очередность установки приложения.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Installer
 * @since 1.0
 */
class InstallerSteps extends BaseObject
{
    /**
     * Параметр GET, определяющий шаг установки.
     *
     * @var string
     */
    public string $stepParam = 'step';

    /**
     * Название текущего шага установки.
     * 
     * @see InstallerSteps::define()
     *
     * @var null|false|string
     */
    public false|string|null $stepName = null;

    /**
     * Шаги установки.
     *
     * @var array
     */
    public array $steps = [];

    /**
     * Установшик.
     *
     * @var Installer
     */
    public Installer $installer;

    /**
     * Текущий выбор варианта установки (изменяет выбор шагов).
     * 
     * Если вариант не указан, будет значение 'common'.
     * 
     * @see InstallerSteps::define()
     * 
     * @var string
     */
    public string $choice = 'common';

    /**
     * Карта шагов установки.
     * 
     * @see InstallerSteps::defineStepsMap()
     * 
     * @var array
     */
    protected array $map = [];

    /**
     * Карта пройденных шагов установки.
     * 
     * @see InstallerSteps::definePassedMap()
     * 
     * @var array
     */
    protected array $passedMap = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->defineStepsMap();
        $this->define();
        $this->definePassedMap();
    }

    /**
     * Определяет выбор и шаг установки.
     *
     * @return void
     */
    protected function define(): void
    {
        $value  = $_GET[$this->stepParam] ?? '';
        if ($value) {
            $params = explode(':', $value);
            if (!isset($params[1])) {
                $this->stepName = $params[0];
                
            } else {
                $this->choice   = $params[0];
                $this->stepName = $params[1];
            }
        } else
            $this->stepName = array_key_first($this->map[$this->choice]);
    }

    /**
     * Определяет карту шагов установки.
     * 
     * @return void
     */
    protected function defineStepsMap(): void
    {
        $this->createStepsMap($this->steps, $this->map);
    }

    /**
     * Определяет карту пройденных шагов установки.
     * 
     * @return void
     */
    protected function definePassedMap(): void
    {
        $this->createPassedMap($this->steps, $this->passedMap, $this->getStepName(true));
    }

    /**
     * Создаёт карту шагов установки.
     * 
     * @param array $array Массив шагов установки.
     * @param array $map Результат карты шагов установки.
     * 
     * @return void
     */
    protected function createStepsMap(array $array, array &$map): void
    {
        foreach ($array as $choice => $steps) {
            $map[$choice] = [];
            foreach ($steps as $stepName => $step) {
                if (is_array($step)) {
                    if (isset($step['class'])) {
                        $map[$choice][$stepName] = $step['class'];
                    }
                    if (isset($step['steps'])) {
                        $this->createStepsMap($step['steps'], $map);
                    }
                } else {
                    $map[$choice][$stepName] = $step;
                }
            }
        }
    }

    /**
     * Создаёт карту пройденных шагов установки относительно текущего шага.
     * 
     * @param array $array Массив шагов установки.
     * @param array $map Результат карты пройденных шагов установки.
     * @param string $currentStep Название текущего шага установки.
     * 
     * @return void
     */
    public function createPassedMap(array $array, array &$map, string $currentStep): void
    {
        foreach ($array as $choice => $steps) {
            foreach ($steps as $stepName => $step) {
                $key = $choice . ':' . $stepName;
                $map[$key] = true;
                if ($key === $currentStep) return;

                if (is_array($step)) {
                    if (isset($step['steps'])) {
                        $this->createPassedMap($step['steps'], $map, $currentStep);
                    }
                }
            }
        }
    }

    /**
     * Проверяет, пройден ли указанный шаг установки.
     * 
     * @param string $stepName Название шага установки, например 'foobar'.
     * 
     * @return bool
     */
    public function isStepPassed(string $stepName): bool
    {
        return isset($this->passedMap[$stepName]);
    }

    /**
     * Возвращает состояние шага установки.
     * 
     * Состояние шага установки: $current, $passed, $default.
     * 
     * @param string $step Название шага установки, например 'foobar'.
     * @param string $current Название состояние для текущего шага.
     * @param string $passed Название состояние для шага, который был пройден (по умолчанию '').
     * @param string $default Название состояние для шага, который не пройден (по умолчанию '').
     * 
     * @return string
     */
    public function getStepState(string $step, string $current, string $passed = '', string $default = ''): string
    {
        if ($step === $this->choice . ':' . $this->stepName)
            return $current;
        else
        if ($this->isStepPassed($step))
            return $passed;
        else
            return $default;
    }

    /**
     * Возвращает название шага.
     * 
     * @param bool $full Если значение `true`, добавляет название выбора (по умолчанию `false`).
     * 
     * @return string
     */
    public function getStepName(bool $full = false): string
    {
        return $full ? $this->choice . ':' . $this->stepName : $this->stepName;
    }

    /**
     * Проверяет, является ли указанный выбор установки текущем.
     * 
     * @param string $choice Выбор установки, например 'choice'.
     * 
     * @return bool
     */
    public function isChoice(string $choice): bool
    {
        return $this->choice === $choice;
    }

    /**
     * Проверяет, является ли указанный шаг установки текущем.
     *
     * @param string $stepName Проверяемое название шага установки, например 'foobar'.
     * 
     * @return bool
     */
    public function isStep(string $stepName): bool
    {
        return $this->stepName === $stepName;
    }

    /**
     * Проверяет, имеет карта шагов установки указаный шаг и выбор установки.
     *
     * @param string $stepName Проверяемое название шага установки, например 'foobar'.
     * @param null|string $choice Выбор установки, например 'choice'. Если значение 
     *     `null`, будет текущий выбор установки {@see InstallerSteps::$choice} (по умолчанию `null`).
     * 
     * @return bool
     */
    public function has(string $stepName, ?string $choice = null): bool
    {
        if ($choice == null) {
            $choice = $this->choice;
        }
        return isset($this->map[$choice][$stepName]);
    }

    /**
     * Устанавливает параметры шага установки для текущего выбора.
     *
     * @param string $name Имя шага установки.
     * @param array $params Параметры шага установки.
     * 
     * @return void
     */
    public function set(string $name, array $params): void
    {
        $this->steps[$this->choice][$name] = $params;
    }

    /**
     * Возвращает параметры шага установки для текущего выбора.
     *
     * @param null|string $name Имя шага установки. Если значение `true`, текущий шаг 
     *     установки (по умолчанию `null`). 
     * 
     * @return mixed
     */
    public function get(?string $name = null): mixed
    {
        if ($name === null) {
            $name = $this->stepName;
        }
        return $this->steps[$this->choice][$name] ?? null;
    }

    /**
     * Создаёт шаг установки.
     *
     * @param null|string $stepName Название шага установки, например 'foobar'. Если значение 
     *     `null`, будет создан шаг установки из текущего названия {@see InstallerSteps::$stepName} 
     *     (по умолчанию `null`).
     * @param null|string $choice Выбор установки, например 'choice'. Если значение 
     *     `null`, будет создан шаг установки из текущего выбора {@see InstallerSteps::$choice} 
     *     (по умолчанию `null`).
     * 
     * @return null|InstallerStep Если значение `null`, то невозможно создать шаг установки.
     */
    public function createStep(?string $stepName = null, ?string $choice = null): ?InstallerStep
    {
        if ($stepName === null) {
            $stepName = $this->stepName;
        }
        if ($choice === null) {
            $choice = $this->choice;
        }

        if ($stepName) {
            $step = $this->map[$choice][$stepName] ?? null;
            if ($step) {
                $step = new $step(['installer' => $this->installer]);
                if (!$step->beforeInit()) return null;
                $step->init();
                if (!$step->afterInit()) return null;
            }
        }
        return $step;
    }
}
