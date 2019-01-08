<?php

namespace AssetsBundle;

class Module implements
\Zend\ModuleManager\Feature\ConfigProviderInterface,
    \Zend\ModuleManager\Feature\ConsoleUsageProviderInterface
{

    /**
     * @param \Zend\EventManager\EventInterface $oEvent
     */
    public function onBootstrap(\Zend\EventManager\EventInterface $oEvent)
    {
        $oApplication = $oEvent->getApplication();

        // Attach AssesBundle service events
        $oApplication->getServiceManager()->get('AssetsBundleService')->attach($oApplication->getEventManager());
    }

    /**
     * @see \Zend\ModuleManager\Feature\ConsoleUsageProviderInterface::getConsoleUsage()
     * @param \Zend\Console\Adapter\AdapterInterface $oConsole
     * @return array
     */
    public function getConsoleUsage(\Zend\Console\Adapter\AdapterInterface $oConsole)
    {
        return array(
            'Rendering assets:',
            'render' => 'render all assets',
            'Empty cache:',
            'empty' => 'empty cache directory'
        );
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . DIRECTORY_SEPARATOR . 'config/module.config.php';
    }
}
