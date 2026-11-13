<?php

namespace AssetsBundle;

class Module implements
\Laminas\ModuleManager\Feature\ConfigProviderInterface
{

    /**
     * @param \Laminas\EventManager\EventInterface $oEvent
     */
    public function onBootstrap(\Laminas\EventManager\EventInterface $oEvent)
    {
        $oApplication = $oEvent->getApplication();

        // Attach AssesBundle service events
        $oApplication->getServiceManager()->get('AssetsBundleService')->attach($oApplication->getEventManager());
    }


    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . DIRECTORY_SEPARATOR . 'config/module.config.php';
    }
}
