<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Permissions\Rbac;

use RecursiveIteratorIterator;

/**
 * Абстрактный класс роли.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Permissions\Rbac
 * @since 2.0
 */
abstract class AbstractRole extends AbstractIterator implements RoleInterface
{
    /**
     * Родительская роль.
     * 
     * @var null|RoleInterface
     */
    protected ?RoleInterface $parent = null;

    /**
     * Название роли.
     * 
     * @var string
     */
    protected string $name = '';

    /**
     * Разрешения.
     * 
     * @var array<string>
     */
    protected array $permissions = [];

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function addPermission(string $name): static
    {
        $this->permissions[$name] = true;
        return $this;
    }

    /**
     * Возвращает дочернии роли.
     * 
     * @return array<int, array{name:string, parent:string}>
     */
    public function getChilds(): array
    {
        $roles = [];
        $it = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $leaf) {
            if ($leaf->getParent() != null)
                $parent = $leaf->getParent()->getName();
            else
                $parent = null;
            $roles[] = [
                'name'   => $leaf->getName(),
                'parent' => $parent
            ];
        }
        return $roles;
    }

    /**
     * Возвращает разрешения.
     * 
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPermission(string $name): bool
    {
        if (isset($this->permissions[$name])) {
            return true;
        }

        $it = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $leaf) {
            if ($leaf->hasPermission($name)) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(RoleInterface|string $child): static
    {
        if (is_string($child)) {
            $child = new Role($child);
        }
        if (!$child instanceof RoleInterface) {
            throw new Exception\InvalidArgumentException(
                'Child must be a string or implement Ge\Permissions\Rbac\RoleInterface'
            );
        }

        $child->setParent($this);
        $this->children[] = $child;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(RoleInterface $parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?RoleInterface
    {
        return $this->parent;
    }
}
