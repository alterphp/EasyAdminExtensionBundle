<?php

namespace AlterPHP\EasyAdminExtensionBundle\Twig;

use AlterPHP\EasyAdminMongoOdmBundle\Configuration\ConfigManager as MongoOdmConfigManager;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager as EntityConfigManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EasyAdminExtensionTwigExtension extends AbstractExtension
{
    private $entityConfigManager;
    private $mongoOdmConfigManager;
    private $router;

    public function __construct(
        RouterInterface $router, EntityConfigManager $entityConfigManager, MongoOdmConfigManager $mongoOdmConfigManager = null
    ) {
        $this->entityConfigManager = $entityConfigManager;
        $this->mongoOdmConfigManager = $mongoOdmConfigManager;
        $this->router = $router;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('easyadmin_object', [$this, 'getObjectConfiguration']),
            new TwigFunction('easyadmin_object_type', [$this, 'getObjectType']),
            new TwigFunction('easyadmin_path', [$this, 'getEasyAdminPath']),
            new TwigFunction('easyadmin_object_twig_path', [$this, 'getTwigPath']),
            new TwigFunction('easyadmin_object_base_twig_path', [$this, 'getBaseTwigPath']),
            new TwigFunction('easyadmin_object_get_actions_for_*_item', [$this, 'getActionsForItem'], ['needs_environment' => true]),
            new TwigFunction('easyadmin_object_render_field_for_*_view', [$this, 'renderObjectField'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    public function getActionsForItem(
        \Twig_Environment $twig, string $view, string $objectType, string $objectName
    ) {
        if ('document' === $objectType && $twig->getFunction('easyadmin_mongo_odm_get_actions_for_*_item')) {
            $function = $twig->getFunction('easyadmin_mongo_odm_get_actions_for_*_item');
        } else {
            $function = $twig->getFunction('easyadmin_get_actions_for_*_item');
        }

        return \call_user_func($function->getCallable(), $view, $objectName);
    }

    public function renderObjectField(
        \Twig_Environment $twig, string $view, string $objectType, string $objectName, $item, array $fieldMetadata
    ) {
        if ('document' === $objectType && $twig->getFunction('easyadmin_mongo_odm_render_field_for_*_view')) {
            $function = $twig->getFunction('easyadmin_mongo_odm_render_field_for_*_view');
        } else {
            $function = $twig->getFunction('easyadmin_render_field_for_*_view');
        }

        return \call_user_func($function->getCallable(), $twig, $view, $objectName, $item, $fieldMetadata);
    }

    /**
     * Returns the namespaced Twig path.
     *
     * @param Request $request
     * @param string  $path
     *
     * @return string
     */
    public function getTwigPath(Request $request, string $path)
    {
        $requestRoute = $request->attributes->get('_route');

        if ('easyadmin' === $requestRoute && $request->query->has('entity')) {
            return \sprintf('@EasyAdmin/%s', $path);
        } elseif ('easyadmin_mongo_odm' === $requestRoute && $request->query->has('document')) {
            return \sprintf('@EasyAdminMongoOdm/%s', $path);
        }

        // Fallback not entity/document admin pages based on EasyAdmin layout ?
        return \sprintf('@EasyAdmin/%s', $path);
    }

    /**
     * Returns the namespaced base Twig path.
     *
     * @param Request $request
     * @param string  $path
     *
     * @return string
     */
    public function getBaseTwigPath(Request $request, string $path)
    {
        $requestRoute = $request->attributes->get('_route');

        if ('easyadmin' === $requestRoute && $request->query->has('entity')) {
            return \sprintf('@!EasyAdmin/%s', $path);
        } elseif ('easyadmin_mongo_odm' === $requestRoute && $request->query->has('document')) {
            return \sprintf('@!EasyAdminMongoOdm/%s', $path);
        }

        // Fallback not entity/document admin pages based on EasyAdmin layout ?
        return \sprintf('@!EasyAdmin/%s', $path);
    }

    /**
     * Returns the entire configuration of the given object.
     *
     * @param Request $request
     *
     * @return array|null
     */
    public function getObjectConfiguration(Request $request)
    {
        $requestRoute = $request->attributes->get('_route');

        if ('easyadmin' === $requestRoute && $request->query->has('entity')) {
            return $this->entityConfigManager->getEntityConfig($request->query->get('entity'));
        }

        if (null === $this->mongoOdmConfigManager) {
            return null;
        }

        if ('easyadmin_mongo_odm' === $requestRoute && $request->query->has('document')) {
            return $this->mongoOdmConfigManager->getDocumentConfig($request->query->get('document'));
        }

        return null;
    }

    /**
     * Returns the given object type.
     *
     * @param Request $request
     *
     * @return string|null
     */
    public function getObjectType(Request $request)
    {
        $requestRoute = $request->attributes->get('_route');

        if ('easyadmin' === $requestRoute && $request->query->has('entity')) {
            return 'entity';
        }

        if (null === $this->mongoOdmConfigManager) {
            return null;
        }

        if ('easyadmin_mongo_odm' === $requestRoute && $request->query->has('document')) {
            return 'document';
        }

        return null;
    }

    /**
     * Returns easyadmin path for given parameters.
     *
     * @param array $parameters
     *
     * @return string
     */
    public function getEasyAdminPath(array $parameters)
    {
        if (\array_key_exists('entity', $parameters)) {
            return $this->router->generate('easyadmin', $parameters);
        } elseif (\array_key_exists('document', $parameters)) {
            return $this->router->generate('easyadmin_mongo_odm', $parameters);
        }

        throw new \RuntimeException('Parameters must contain either "entity" or "document" key !');
    }
}
