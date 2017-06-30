<?php

namespace AlterPHP\EasyAdminExtensionBundle\EventListener;

use JavierEguiluz\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class CustomFormTypesSubscriber implements EventSubscriberInterface
{
    private $customFormTypes = array();
    private static $configWithFormKeys = array('form', 'edit', 'new');

    public function __construct(array $customFormTypes = array())
    {
        $this->customFormTypes = $customFormTypes;
    }

    public static function getSubscribedEvents()
    {
        return array(
            EasyAdminEvents::POST_INITIALIZE => array('onPostInitialize'),
        );
    }

    public function onPostInitialize(GenericEvent $event)
    {
        if (empty($this->customFormTypes)) {
            return;
        }

        if ($event->hasArgument('config')) {
            $config = $event->getArgument('config');

            if (isset($config['entities']) && is_array($config['entities'])) {
                foreach ($config['entities'] as &$entity) {
                    $entity = $this->replaceCustomTypesInEntityConfig($entity);
                }
            }

            $event->setArgument('config', $config);
        }

        if ($event->hasArgument('entity')) {
            $entity = $event->getArgument('entity');
            $event->setArgument('entity', $this->replaceCustomTypesInEntityConfig($entity));
        }
    }

    protected function replaceCustomTypesInEntityConfig(array $entity)
    {
        foreach (static::$configWithFormKeys as $configWithFormKey) {
            if (
                isset($entity[$configWithFormKey])
                && isset($entity[$configWithFormKey]['fields'])
                && is_array($entity[$configWithFormKey]['fields'])
            ) {
                foreach ($entity[$configWithFormKey]['fields'] as $name => $field) {
                    if (!isset($field['type'])) {
                        continue;
                    }

                    if (in_array($field['type'], array_keys($this->customFormTypes))) {
                        $entity[$configWithFormKey]['fields'][$name]['type'] = $this->customFormTypes[$field['type']];
                    }
                }            
            }
        }

        return $entity;
    }
}
