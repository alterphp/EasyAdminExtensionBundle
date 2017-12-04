<?php

namespace AlterPHP\EasyAdminExtensionBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;

/**
 * Adds templates :
 *     - Embedded lists in show view
 *
 * @author Pierre-Charles Bertineau <pc.bertineau@alterphp.com>
 */
class TemplateConfigPass implements ConfigPassInterface
{
    private $twigLoader;
    private $defaultBackendTemplates = array(
        'field_embedded_list' => '@EasyAdmin/default/field_embedded_list.html.twig',
    );

    public function __construct(\Twig_Loader_Filesystem $twigLoader)
    {
        $this->twigLoader = $twigLoader;
    }

    public function process(array $backendConfig)
    {
        $backendConfig = $this->processEntityTemplates($backendConfig);

        return $backendConfig;
    }

    /**
     * Determines the template used to render each backend element. This is not
     * trivial because templates can depend on the entity displayed and they
     * define an advanced override mechanism.
     *
     * @param array $backendConfig
     *
     * @return array
     */
    private function processEntityTemplates(array $backendConfig)
    {
        foreach ($backendConfig['entities'] as $entityName => $entityConfig) {
            foreach (array('list', 'show') as $view) {
                foreach ($entityConfig[$view]['fields'] as $fieldName => $fieldMetadata) {
                    // if the field defines its own template, resolve its location
                    if (isset($fieldMetadata['template'])) {
                        $templatePath = $fieldMetadata['template'];

                        if (isset($this->defaultBackendTemplates[$templatePath])) {
                            $templatePath = $this->defaultBackendTemplates[$templatePath];
                        }

                        // template path should contain the .html.twig extension
                        // however, for usability reasons, we silently fix this issue if needed
                        if ('.html.twig' !== substr($templatePath, -10)) {
                            $templatePath .= '.html.twig';
                            @trigger_error(sprintf('Passing a template path without the ".html.twig" extension is deprecated since version 1.11.7 and will be removed in 2.0. Use "%s" as the value of the "template" option for the "%s" field in the "%s" view of the "%s" entity.', $templatePath, $fieldName, $view, $entityName), E_USER_DEPRECATED);
                        }

                        // before considering $templatePath a regular Symfony template
                        // path, check if the given template exists in any of these directories:
                        // * app/Resources/views/easy_admin/<entityName>/<templatePath>
                        // * app/Resources/views/easy_admin/<templatePath>
                        $templatePath = $this->findFirstExistingTemplate(array(
                            'easy_admin/'.$entityName.'/'.$templatePath,
                            'easy_admin/'.$templatePath,
                            $templatePath,
                        ));
                    } else {
                        // At this point, we don't know the exact data type associated with each field.
                        // The template is initialized to null and it will be resolved at runtime in the Configurator class
                        $templatePath = null;
                    }

                    $entityConfig[$view]['fields'][$fieldName]['template'] = $templatePath;
                }
            }

            $backendConfig['entities'][$entityName] = $entityConfig;
        }

        return $backendConfig;
    }

    private function findFirstExistingTemplate(array $templatePaths)
    {
        foreach ($templatePaths as $templatePath) {
            if (null !== $templatePath && $this->twigLoader->exists($templatePath)) {
                return $templatePath;
            }
        }
    }
}
