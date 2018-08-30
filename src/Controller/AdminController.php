<?php

namespace AlterPHP\EasyAdminExtensionBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use AlterPHP\EasyAdminExtensionBundle\Security\AdminAuthorizationChecker;

class AdminController extends BaseAdminController
{
    protected function embeddedListAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_LIST);

        $fields = $this->entity['list']['fields'];
        $paginator = $this->findAll($this->entity['class'], $this->request->query->get('page', 1), $this->config['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'));

        $this->dispatch(EasyAdminEvents::POST_LIST, array('paginator' => $paginator));

        return $this->render('@EasyAdminExtension/default/embedded_list.html.twig', array(
            'paginator' => $paginator,
            'fields' => $fields,
            'masterRequest' => $this->get('request_stack')->getMasterRequest(),
        ));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException
     */
    protected function isActionAllowed($actionName)
    {
        // autocomplete and embeddedList action are mapped to list action for access permissions
        if (in_array($actionName, ['autocomplete', 'embeddedList'])) {
            $actionName = 'list';
        }

        // Get item for edit/show or custom actions => security voters may apply
        $easyadmin = $this->request->attributes->get('easyadmin');
        $subject = $easyadmin['item'] ?? null;
        $this->get('alterphp.easyadmin_extension.admin_authorization_checker')->checksUserAccess(
            $this->entity, $actionName, $subject
        );

        return parent::isActionAllowed($actionName);
    }

    /**
     * Creates the form object used to create or edit the given entity.
     * Control role of the field
     *
     * @param object $entity
     * @param array  $entityProperties
     * @param string $view
     *
     * @return FormInterface
     *
     * @throws \Exception
     */
    protected function createEntityForm($entity, array $entityProperties, $view)
    {
        $adminAuthorizationChecker = $this->container->get('alterphp.easyadmin_extension.admin_authorization_checker');
        $removeEntityProperties = $adminAuthorizationChecker->getRemovePropertiesRequiredRole($entityProperties);

        $entityForm = parent::createEntityForm($entity, $entityProperties, $view);

        foreach ($removeEntityProperties as $key => $value) {
            $entityForm->remove($value);
        }
        return $entityForm;
    }
}
