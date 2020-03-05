<?php

namespace AlterPHP\EasyAdminExtensionBundle\Controller;

use AlterPHP\EasyAdminExtensionBundle\Security\AdminAuthorizationChecker;
use AlterPHP\EasyAdminExtensionBundle\Controller\AdminExtensionControllerTrait;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController as BaseEasyAdminControler;


class EasyAdminController extends BaseEasyAdminControler
{
    use AdminExtensionControllerTrait;
	
	public static function getSubscribedServices(): array
    {
        return \array_merge(parent::getSubscribedServices(), [AdminAuthorizationChecker::class]);
    }
}
