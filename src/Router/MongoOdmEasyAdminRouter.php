<?php

namespace AlterPHP\EasyAdminExtensionBundle\Router;

use Doctrine\Persistence\Proxy;
use AlterPHP\EasyAdminMongoOdmBundle\Configuration\ConfigManager;
use AlterPHP\EasyAdminExtensionBundle\Exception\UndefinedDocumentException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MongoOdmEasyAdminRouter
{
    private $configManager;
    private $urlGenerator;
    private $propertyAccessor;
    private $requestStack;

    public function __construct(ConfigManager $configManager, UrlGeneratorInterface $urlGenerator, PropertyAccessorInterface $propertyAccessor, RequestStack $requestStack = null)
    {
        $this->configManager = $configManager;
        $this->urlGenerator = $urlGenerator;
        $this->propertyAccessor = $propertyAccessor;
        $this->requestStack = $requestStack;
    }

    /**
     * @param object|string $document
     * @param string        $action
     * @param array         $parameters
     *
     * @throws UndefinedDocumentException
     *
     * @return string
     */
    public function generate($document, $action, array $parameters = [])
    {
        if (\is_object($document)) {
            $config = $this->getDocumentConfigByClass(\get_class($document));

            // casting to string is needed because entities can use objects as primary keys
            $parameters['id'] = (string) $this->propertyAccessor->getValue($document, 'id');
        } else {
            $config = class_exists($document)
                ? $this->getDocumentConfigByClass($document)
                : $this->configManager->getDocumentConfig($document);
        }

        $parameters['document'] = $config['name'];
        $parameters['action'] = $action;

        $referer = $parameters['referer'] ?? null;

        $request = null;
        if (null !== $this->requestStack) {
            $request = $this->requestStack->getCurrentRequest();
        }

        if (false === $referer) {
            unset($parameters['referer']);
        } elseif (
            $request
            && !\is_string($referer)
            && (true === $referer || \in_array($action, ['new', 'edit', 'delete'], true))
        ) {
            $parameters['referer'] = urlencode($request->getUri());
        }

        return $this->urlGenerator->generate('easyadmin_mongo_odm', $parameters);
    }

    /**
     * @param string $class
     *
     * @throws UndefinedDocumentException
     *
     * @return array
     */
    private function getDocumentConfigByClass($class)
    {
        if (!$config = $this->configManager->getDocumentConfigByClass($this->getRealClass($class))) {
            throw new UndefinedDocumentException(['document_name' => $class]);
        }

        return $config;
    }

    /**
     * @param string $class
     *
     * @return string
     */
    private function getRealClass($class)
    {
        if (false === $pos = strrpos($class, '\\'.Proxy::MARKER.'\\')) {
            return $class;
        }

        return substr($class, $pos + Proxy::MARKER_LENGTH + 2);
    }
}
