<?php

namespace AssetsBundle;

class Module implements
\Laminas\ModuleManager\Feature\ConfigProviderInterface,
    \Laminas\ModuleManager\Feature\ConsoleUsageProviderInterface
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
     * @see \Laminas\ModuleManager\Feature\ConsoleUsageProviderInterface::getConsoleUsage()
     * @param \Laminas\Console\Adapter\AdapterInterface $oConsole
     * @return array
     */
    public function getConsoleUsage(\Laminas\Console\Adapter\AdapterInterface $oConsole)
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
