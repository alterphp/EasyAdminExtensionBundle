<?php

namespace AlterPHP\EasyAdminExtensionBundle\Form\Type\Transformer;

use AlterPHP\EasyAdminExtensionBundle\Security\EditableRolesBuilder;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transformer to restore unreachable admin roles.
 *
 * Copied from SonataUserBundle RestoreRolesTransformer.
 *
 * @see https://github.com/sonata-project/SonataUserBundle/blob/master/src/Form/Transformer/RestoreRolesTransformer.php
 */
class RestoreRolesTransformer implements DataTransformerInterface
{
    /**
     * @var array|null
     */
    protected $originalRoles = null;

    /**
     * @var EditableRolesBuilder|null
     */
    protected $rolesBuilder = null;

    /**
     * @param EditableRolesBuilder $rolesBuilder
     */
    public function __construct(EditableRolesBuilder $rolesBuilder)
    {
        $this->rolesBuilder = $rolesBuilder;
    }

    /**
     * @param array|null $originalRoles
     */
    public function setOriginalRoles(array $originalRoles = null)
    {
        $this->originalRoles = $originalRoles ?: array();
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return $value;
        }
        if (null === $this->originalRoles) {
            throw new \RuntimeException('Invalid state, originalRoles array is not set');
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($selectedRoles)
    {
        if (null === $this->originalRoles) {
            throw new \RuntimeException('Invalid state, originalRoles array is not set');
        }
        $availableRoles = $this->rolesBuilder->getRoles();
        $hiddenRoles = array_diff($this->originalRoles, array_keys($availableRoles));

        return array_merge($selectedRoles, $hiddenRoles);
    }
}
