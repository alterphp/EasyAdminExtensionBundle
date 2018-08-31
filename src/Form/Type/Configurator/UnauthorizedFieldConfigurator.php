<?php

namespace AlterPHP\EasyAdminExtensionBundle\Form\Type\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Form\Type\Configurator\TypeConfiguratorInterface;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Gonzalo Alonso <gonkpo@gmail.com>
 */
final class UnauthorizedFieldConfigurator implements TypeConfiguratorInterface
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function configure($name, array $options, array $metadata, FormConfigInterface $parentConfig)
    {
        if (isset($metadata['role']) && !$this->authorizationChecker->isGranted($metadata['role'])) {
            $options['disabled'] = true;
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type, array $options, array $metadata)
    {
        return true;
    }
}