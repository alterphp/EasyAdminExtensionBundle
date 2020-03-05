<?php

namespace AlterPHP\EasyAdminExtensionBundle\Controller;

use AlterPHP\EasyAdminExtensionBundle\Security\AdminAuthorizationChecker;
use AlterPHP\EasyAdminExtensionBundle\Controller\AdminExtensionControllerTrait;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController as BaseEasyAdminControler;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use League\Uri\Modifiers\RemoveQueryParams;
use League\Uri\Schemes\Http;
use Symfony\Component\HttpFoundation\JsonResponse;

class EasyAdminController extends BaseEasyAdminControler
{
    use AdminExtensionControllerTrait;
}
