<?php

namespace AlterPHP\EasyAdminExtensionBundle\Controller;

use AlterPHP\EasyAdminExtensionBundle\Security\AdminAuthorizationChecker;
use AlterPHP\EasyAdminMongoOdmBundle\Controller\EasyAdminController as BaseEasyAdminController;
use AlterPHP\EasyAdminMongoOdmBundle\Event\EasyAdminMongoOdmEvents;
use League\Uri\Components\Query;
use League\Uri\QueryString;
use League\Uri\Uri;

class MongoOdmEasyAdminController extends BaseEasyAdminController
{
    public static function getSubscribedServices(): array
    {
        return \array_merge(parent::getSubscribedServices(), [AdminAuthorizationChecker::class]);
    }

    protected function embeddedListAction()
    {
        $this->dispatch(EasyAdminMongoOdmEvents::PRE_LIST);

        $fields = $this->document['list']['fields'];
        $paginator = $this->mongoOdmFindAll(
            $this->document['class'],
            $this->request->query->get('page', 1),
            $this->document['list']['max_results'],
            $this->request->query->get('sortField'),
            $this->request->query->get('sortDirection')
        );

        $this->dispatch(EasyAdminMongoOdmEvents::POST_LIST, ['paginator' => $paginator]);

        // Filter displaid columns
        $hiddenFields = $this->request->query->get('hidden-fields', []);
        $fields = \array_filter(
            $this->document['list']['fields'],
            function ($name) use ($hiddenFields) {
                return !\in_array($name, $hiddenFields);
            },
            ARRAY_FILTER_USE_KEY
        );

        $requestParameters = $this->request->query->all();
        // Removes existing referer
        $baseMasterRequestUri = !$this->request->isXmlHttpRequest()
                            ? $this->get('request_stack')->getMasterRequest()->getUri()
                            : $this->request->headers->get('referer');
        $requestParameters['referer'] = $this->removeRefererFromUri($baseMasterRequestUri);

        return $this->render('@EasyAdminExtension/default/embedded_list.html.twig', [
            'objectType' => 'document',
            'paginator' => $paginator,
            'fields' => $fields,
            '_request_parameters' => $requestParameters,
        ]);
    }

    protected function removeRefererFromUri(string $originalUri): string
    {
        // Adds stmuid param to URL with MoveboxUID
        $parsedUrl = Uri::createFromString($originalUri);
        $uriQueryPairs = QueryString::parse($parsedUrl->getQuery());
        $urlQuery = Query::createFromPairs($uriQueryPairs);
        $newUrl = $parsedUrl->withQuery(
            $urlQuery->withoutParam('referer')->__toString()
        );

        return $newUrl->__toString();
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
            default:
                break;
        }

        // Get item for edit/show or custom actions => security voters may apply
        $easyadminMongoOdm = $this->request->attributes->get('easyadmin_mongo_odm');
        $subject = $easyadminMongoOdm['item'] ?? null;
        $this->get(AdminAuthorizationChecker::class)->checksUserAccess($this->document, $actionName, $subject);

        return parent::isActionAllowed($actionName);
    }
}
