<?php

namespace AlterPHP\EasyAdminExtensionBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;

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
     * @throws AccessDeniedException
     */
    protected function isActionAllowed($actionName)
    {
        // autocomplete and embeddedList action are mapped to list action for access permissions
        if (in_array($actionName, ['autocomplete', 'embeddedList'])) {
            $actionName = 'list';
        }

        $this->get('alterphp.easyadmin_extension.admin_authorization_checker')->checksUserAccess(
            $this->entity, $actionName
        );

        return parent::isActionAllowed($actionName);
    }
}
