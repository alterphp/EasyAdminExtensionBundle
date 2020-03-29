<?php

namespace AlterPHP\EasyAdminExtensionBundle\Controller;

use AlterPHP\EasyAdminExtensionBundle\Security\AdminAuthorizationChecker;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\HttpFoundation\JsonResponse;

trait AdminExtensionControllerTrait
{
    protected function embeddedListAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_LIST);

        $maxResults = (int) $this->request->query->get('max-results', $this->entity['list']['max_results']);

        $paginator = $this->findAll($this->entity['class'], (int) $this->request->query->get('page', '1'), $maxResults, $this->request->query->get('sortField'), $this->request->query->get('sortDirection'), $this->entity['list']['dql_filter']);

        $this->dispatch(EasyAdminEvents::POST_LIST, ['paginator' => $paginator]);

        // Filter displaid columns
        $hiddenFields = (array) ($this->request->query->get('hidden-fields') ?? []);
        $fields = \array_filter(
            $this->entity['list']['fields'],
            function ($name) use ($hiddenFields) {
                return !\in_array($name, $hiddenFields);
            },
            ARRAY_FILTER_USE_KEY
        );

        // Removes existing referer
        $baseMasterRequestUri = !$this->request->isXmlHttpRequest()
            ? $this->get('request_stack')->getMasterRequest()->getUri()
            : $this->request->headers->get('referer');
        \parse_str(\parse_url($baseMasterRequestUri, PHP_URL_QUERY), $queryParameters);
        unset($queryParameters['referer']);
        $masterRequestUri = \sprintf('%s?%s', \strtok($baseMasterRequestUri, '?'), \http_build_query($queryParameters));

        $requestParameters = $this->request->query->all();
        $requestParameters['referer'] = $masterRequestUri;

        $viewVars = [
            'objectType' => 'entity',
            'paginator' => $paginator,
            'fields' => $fields,
            '_request_parameters' => $requestParameters,
        ];

        return $this->executeDynamicMethod(
            'render<EntityName>Template',
            ['embeddedList', $this->entity['embeddedList']['template'], $viewVars]
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    protected function isActionAllowed($actionName)
    {
        switch ($actionName) {
            // autocomplete action is mapped to list action for access permissions
            case 'autocomplete':
                // filters (EasyAdmin new list filters) action is mapped to list action for access permissions
            case 'filters':
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
