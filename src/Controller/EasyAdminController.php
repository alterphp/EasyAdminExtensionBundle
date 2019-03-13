<?php

namespace AlterPHP\EasyAdminExtensionBundle\Controller;

use AlterPHP\EasyAdminExtensionBundle\Security\AdminAuthorizationChecker;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController as BaseEasyAdminControler;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use League\Uri;
use Symfony\Component\HttpFoundation\JsonResponse;

class EasyAdminController extends BaseEasyAdminControler
{
    public static function getSubscribedServices(): array
    {
        return \array_merge(parent::getSubscribedServices(), [AdminAuthorizationChecker::class]);
    }

    protected function embeddedListAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_LIST);

        $fields = $this->entity['list']['fields'];
        $paginator = $this->findAll($this->entity['class'], $this->request->query->get('page', 1), $this->config['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'));

        $this->dispatch(EasyAdminEvents::POST_LIST, ['paginator' => $paginator]);

        // Removes existing referer
        $baseMasterRequestUri = !$this->request->isXmlHttpRequest()
                            ? $this->get('request_stack')->getMasterRequest()->getUri()
                            : $this->request->headers->get('referer');
        $baseMasterRequestUri = Uri\create($baseMasterRequestUri);
        $masterRequestUri = Uri\remove_pairs($baseMasterRequestUri, ['referer']);

        return $this->render('@EasyAdminExtension/default/embedded_list.html.twig', [
            'paginator' => $paginator,
            'fields' => $fields,
            'masterRequestUri' => (string) $masterRequestUri,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException
     */
    protected function isActionAllowed($actionName)
    {
        switch ($actionName) {
            // autocomplete action is mapped to list action for access permissions
            case 'autocomplete':
            // embeddedList action is mapped to list action for access permissions
            case 'embeddedList':
                $actionName = 'list';
                break;
            // newAjax action is mapped to new action for access permissions
            case 'newAjax':
                $actionName = 'new';
                break;
            default:
                break;
        }

        // Get item for edit/show or custom actions => security voters may apply
        $easyadmin = $this->request->attributes->get('easyadmin');
        $subject = $easyadmin['item'] ?? null;
        $this->get(AdminAuthorizationChecker::class)->checksUserAccess($this->entity, $actionName, $subject);

        return parent::isActionAllowed($actionName);
    }

    /**
     * The method that is executed when the user performs a 'new ajax' action on an entity.
     *
     * @return JsonResponse
     */
    protected function newAjaxAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_NEW);

        $entity = $this->executeDynamicMethod('createNew<EntityName>Entity');
        $easyadmin = \array_merge($this->request->attributes->get('easyadmin'), ['item' => $entity]);
        $this->request->attributes->set('easyadmin', $easyadmin);

        $fields = $this->entity['new']['fields'];
        $newForm = $this->executeDynamicMethod('create<EntityName>NewForm', [$entity, $fields]);
        $newForm->handleRequest($this->request);
        if ($newForm->isSubmitted() && $newForm->isValid()) {
            $this->dispatch(EasyAdminEvents::PRE_PERSIST, ['entity' => $entity]);
            $this->executeDynamicMethod('persist<EntityName>Entity', [$entity]);
            $this->dispatch(EasyAdminEvents::POST_PERSIST, ['entity' => $entity]);

            return new JsonResponse(['option' => ['id' => $entity->getId(), 'text' => (string) $entity]]);
        }

        $this->dispatch(EasyAdminEvents::POST_NEW, ['entity_fields' => $fields, 'form' => $newForm, 'entity' => $entity]);

        $parameters = ['form' => $newForm->createView(), 'entity_fields' => $fields, 'entity' => $entity];
        $templatePath = '@EasyAdminExtension/default/new_ajax.html.twig';
        if (isset($this->entity['templates']['new_ajax'])) {
            $templatePath = $this->entity['templates']['new_ajax'];
        }

        return new JsonResponse(['html' => $this->renderView($templatePath, $parameters)]);
    }
}
