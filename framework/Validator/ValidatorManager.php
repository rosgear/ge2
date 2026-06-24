<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Validator;

use Ge;

/**
 * Менеджер валидации входных данных.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Validator
 * @since 2.0
 */
class ValidatorManager
{
    /**
     * Имена валидаторов с их классами.
     *
     * @var array
     */
    public array $aliases = [
        'compare'  => '\Ge\Validator\Compare',
        'between'  => '\Ge\Validator\Between',
        'notEmpty' => '\Ge\Validator\NotEmpty',
        'select'   => '\Ge\Validator\Select',
        'match'    => '\Ge\Validator\PregMatch',
        'filename' => '\Ge\Validator\Filename',
        'filter'   => '\Ge\Validator\Filter',
        'enum'     => '\Ge\Validator\Enum',
        'kcaptcha' => '\Ge\Validator\KCaptcha',
    ];

    /**
     * Сообщения (ошибки).
     *
     * @var array
     */
    protected array $messages = [];

    /**
     * Возвращает валидатор.
     * 
     * @param string $name Имя валидатора.
     * @param array $options Параметры настроек валидатора.
     * 
     * @return AbstractValidator
     * 
     * @throws \Ge\Exception\NotInstantiableException Ошибка при создании экземпляра класса валидатора.
     */
    public function getValidator(string $name, array $options = []): AbstractValidator
    {
        $alias = $name . 'Validator';
        if (!Ge::$services->has($alias)) {
            Ge::$services->setInvokableClass($alias, $this->aliases[$name]);
        }
        /** @var AbstractValidator $validator */
        $validator = Ge::$services->getAs($alias);
        $validator->setOptions($options);
        return $validator;
    }

    /**
     * Проверяет значение.
     * 
     * @param mixed $value Проверяемое значение.
     * @param string $name Имя валидатора.
     * @param array $options Параметры настроек валидатора.
     * 
     * @return bool|array Если значение `array`, то возвратит сообщения об ошибках проверки.
     * 
     * @throws \Ge\Exception\NotInstantiableException Ошибка при создании экземпляра класса валидатора.
     */
    public function isValid(mixed $value, string $name, array $options = []): bool|array
    {
        /** @var AbstractValidator $validator */
        $validator = $this->getValidator($name, $options);
        if ($validator->isValid($value)) return true;

        return $validator->getMessages();
    }

    /**
     * Выполняет проверку значения.
     * 
     * @param array $rules Правила проверки.
     * @param array $attributes Название атрибутов с их значениями, которые необходимо проверить.
     * 
     * @return bool
     */
    public function validate(array $rules, array $attributes): bool
    {
        foreach ($rules as $ruleName => $rule) {
            $names   = (array) $rule[0];
            $name    = $rule[1];
            $options = array_slice($rule, 2);
            foreach ($names as $attributeName) {
                try {
                    $value = $attributes[$attributeName] ?? null;

                    /** @var AbstractValidator $validator */
                    $validator = $this->getValidator($name, $options);
                    if (!$validator->isValid($value)) {
                        $messages = $validator->getMessages();
                        if (sizeof($messages) > 0) {
                            $this->messages[] = array($messages[0], $attributeName);
                        }
                    }
                } catch(\Exception $e) {
                    $this->messages[] = array($e->getMessage(), $attributeName);
                }
            }
        }
        return sizeof($this->messages) == 0;
    }

    /**
     * Возвращает сообщения (ошибки) полученные при проверки атрибутов.
     * 
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
