<?php

namespace AssetsBundle\Service;

class ToolsService
{

    /**
     * @var \Laminas\Console\Adapter\AdapterInterface
     */
    protected $console;

    /**
     * @var \Laminas\Mvc\MvcEvent
     */
    protected $mvcEvent;

    /**
     * @var \AssetsBundle\Service\Service
     */
    protected $assetsBundleService;

    /**
     * @return \AssetsBundle\Service\ToolsService
     */
    public function renderAllAssets() : \AssetsBundle\Service\ToolsService
    {

        // Initialize AssetsBundle service
        $oAssetsBundleService = $this->getAssetsBundleService();
        $oAssetsBundleService->getOptions()->setRenderer(new \Laminas\View\Renderer\PhpRenderer());

        // Start process
        $oConsole = $this->getConsole();
        $oConsole->writeLine('');
        $oConsole->writeLine('======================================================================', \Laminas\Console\ColorInterface::WHITE);
        $oConsole->writeLine('Render all assets for ' . ($oAssetsBundleService->getOptions()->isProduction() ? 'production' : 'development'), \Laminas\Console\ColorInterface::GREEN);
        $oConsole->writeLine('======================================================================', \Laminas\Console\ColorInterface::WHITE);
        $oConsole->writeLine('');

        // Empty cache directory
        $this->emptyCache();

        $oConsole->writeLine('');
        $oConsole->writeLine('Start rendering assets : ', \Laminas\Console\ColorInterface::GREEN);
        $oConsole->writeLine('-------------------------', \Laminas\Console\ColorInterface::WHITE);
        $oConsole->writeLine('');
        $aUnwantedKeys = array_fill_keys(\AssetsBundle\AssetFile\AssetFile::ALL_ASSET_TYPES, true);
        
        // Retrieve MvcEvent
        $oMvcEvent = $this->getMvcEvent();

        // Reset route match and request
        $oMvcEvent->setRouteMatch(new \Laminas\Router\RouteMatch(array()))->setRequest(new \Laminas\Http\Request());

        // Retrieve AssetsBundle service options
        $oOptions = $oAssetsBundleService->getOptions();

        $aAssetsConfiguration = $oOptions->getAssets();

        // Render all assets
        foreach (array_diff_key($aAssetsConfiguration, $aUnwantedKeys) as $sModuleName => $aModuleConfig) {
            // Render module assets
            $oOptions->setModuleName($sModuleName);

            // If module has global assets
            if (array_intersect_key($aModuleConfig, $aUnwantedKeys)) {
                $oConsole->write(' * ', \Laminas\Console\ColorInterface::WHITE);
                $oConsole->write('[' . $sModuleName . ']', \Laminas\Console\ColorInterface::LIGHT_CYAN);
                $oConsole->write('[No controller]', \Laminas\Console\ColorInterface::LIGHT_BLUE);
                $oConsole->write('[No action]' . PHP_EOL, \Laminas\Console\ColorInterface::LIGHT_WHITE);

                // Render assets for no_controller and no_action
                $oOptions->setControllerName(\AssetsBundle\Service\ServiceOptions::NO_CONTROLLER)
                        ->setActionName(\AssetsBundle\Service\ServiceOptions::NO_ACTION);
                $oAssetsBundleService->renderAssets($oMvcEvent);
            }

            foreach (array_diff_key($aAssetsConfiguration[$sModuleName], $aUnwantedKeys) as $sControllerName => $aControllerConfig) {
                // Render controller assets
                $oOptions->setControllerName($sControllerName);

                // If controller has global assets
                if (array_intersect_key($aControllerConfig, $aUnwantedKeys)) {
                    $oConsole->write(' * ', \Laminas\Console\ColorInterface::WHITE);
                    $oConsole->write('[' . $sModuleName . ']', \Laminas\Console\ColorInterface::LIGHT_CYAN);
                    $oConsole->write('[' . $sControllerName . ']', \Laminas\Console\ColorInterface::LIGHT_BLUE);
                    $oConsole->write('[No action]' . PHP_EOL, \Laminas\Console\ColorInterface::LIGHT_WHITE);

                    // Render assets for no_action
                    $oOptions->setActionName(\AssetsBundle\Service\ServiceOptions::NO_ACTION);
                    $oAssetsBundleService->renderAssets($oMvcEvent);
                }

                foreach (array_diff_key($aAssetsConfiguration[$sModuleName][$sControllerName], $aUnwantedKeys) as $sActionName => $aActionConfig) {
                    // Render assets for action
                    if (array_intersect_key($aActionConfig, $aUnwantedKeys)) {
                        $oConsole->write(' * ', \Laminas\Console\ColorInterface::WHITE);
                        $oConsole->write('[' . $sModuleName . ']', \Laminas\Console\ColorInterface::LIGHT_CYAN);
                        $oConsole->write('[' . $sControllerName . ']', \Laminas\Console\ColorInterface::LIGHT_BLUE);
                        $oConsole->write('[' . $sActionName . ']' . PHP_EOL, \Laminas\Console\ColorInterface::LIGHT_WHITE);

                        $oAssetsBundleService->getOptions()->setActionName($sActionName);
                        $oAssetsBundleService->renderAssets($oMvcEvent);
                    }
                }
            }
        }
        //Render global assets
        $oConsole->write(' * ', \Laminas\Console\ColorInterface::WHITE);
        $oConsole->write('[No module]', \Laminas\Console\ColorInterface::LIGHT_CYAN);
        $oConsole->write('[No controller]', \Laminas\Console\ColorInterface::LIGHT_BLUE);
        $oConsole->write('[No action]' . PHP_EOL, \Laminas\Console\ColorInterface::LIGHT_WHITE);
        $oAssetsBundleService->getOptions()
                ->setModuleName(\AssetsBundle\Service\ServiceOptions::NO_MODULE)
                ->setControllerName(\AssetsBundle\Service\ServiceOptions::NO_CONTROLLER)
                ->setActionName(\AssetsBundle\Service\ServiceOptions::NO_ACTION);
        $oAssetsBundleService->renderAssets($oMvcEvent);

        $oConsole->writeLine('');
        $oConsole->writeLine('---------------', \Laminas\Console\ColorInterface::WHITE);
        $oConsole->writeLine('Assets rendered', \Laminas\Console\ColorInterface::GREEN);
        $oConsole->writeLine('');

        return $this;
    }

    /**
     * @param bool $bDisplayConsoleMessage
     * @return \AssetsBundle\Service\ToolsService
     */
    public function emptyCache(bool $bDisplayConsoleMessage = true) : \AssetsBundle\Service\ToolsService
    {
        if ($bDisplayConsoleMessage) {
            $oConsole = $this->getConsole();
            $oConsole->writeLine('');
            $oConsole->writeLine('========================', \Laminas\Console\ColorInterface::WHITE);
            $oConsole->writeLine('Empty cache', \Laminas\Console\ColorInterface::GREEN);
            $oConsole->writeLine('========================', \Laminas\Console\ColorInterface::WHITE);
            $oConsole->writeLine('');
        }

        // Initialize AssetsBundle service
        $oAssetsBundleService = $this->getAssetsBundleService();

        // List directories to be emptied
        $aDirectories = array(
            'Cache' => $oAssetsBundleService->getOptions()->getCachePath(),
            'Config cache' => dirname($oAssetsBundleService->getAssetFilesManager()->getAssetFilesConfiguration()->getConfigurationFilePath()),
            'Tmp' => $oAssetsBundleService->getOptions()->getTmpDirPath(),
        );

        // Retrieve Asset File Filters cache directories
        $oAssetFileFiltersManager = $oAssetsBundleService->getAssetFilesManager()->getAssetFileFiltersManager();
        $aRegisteredAssetFileFilters = $oAssetFileFiltersManager->getRegisteredAssetFileFilters();
        foreach ($aRegisteredAssetFileFilters as $sFilter) {
            $oFilter = $oAssetFileFiltersManager->get($sFilter);
            $aDirectories[$oFilter->getAssetFileFilterName() . ' filter cache'] = $oFilter->getAssetFileFilterProcessedDirPath();
        }

        // Empty directories except .gitignore
        foreach ($aDirectories as $sName => $sDirectoryPath) {
            if (!is_dir($sDirectoryPath)) {
                continue;
            }
            foreach (new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sDirectoryPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            ) as $oFileinfo) {
                if ($oFileinfo->isDir()) {
                    rmdir($oFileinfo->getRealPath());
                } elseif ($oFileinfo->getBasename() !== '.gitignore') {
                    unlink($oFileinfo->getRealPath());
                }
            }
            if ($bDisplayConsoleMessage) {
                $oConsole->writeLine(' * "'.$sName.'" directory is empty', \Laminas\Console\ColorInterface::WHITE);
            }
        }
        

        return $this;
    }

    /**
     * @return \Laminas\Console\Adapter\AdapterInterface
     * @throws \LogicException
     */
    public function getConsole() : \Laminas\Console\Adapter\AdapterInterface
    {
        if ($this->console instanceof \Laminas\Console\Adapter\AdapterInterface) {
            return $this->console;
        }
        throw new \LogicException('Console is undefined');
    }

    /**
     * @param \Laminas\Console\Adapter\AdapterInterface $oConsole
     * @return \AssetsBundle\Service\ToolsService
     */
    public function setConsole(\Laminas\Console\Adapter\AdapterInterface $oConsole) : \AssetsBundle\Service\ToolsService
    {
        $this->console = $oConsole;
        return $this;
    }

    /**
     * @return \Laminas\Mvc\MvcEvent
     * @throws \LogicException
     */
    public function getMvcEvent() : \Laminas\Mvc\MvcEvent
    {
        if ($this->mvcEvent instanceof \Laminas\Mvc\MvcEvent) {
            return $this->mvcEvent;
        }
        throw new \LogicException('Mvc event is undefined');
    }

    /**
     * @param \Laminas\Mvc\MvcEvent $oMvcEvent
     * @return \AssetsBundle\Service\ToolsService
     */
    public function setMvcEvent(\Laminas\Mvc\MvcEvent $oMvcEvent) : \AssetsBundle\Service\ToolsService
    {
        $this->mvcEvent = $oMvcEvent;
        return $this;
    }

    /**
     * @return \AssetsBundle\Service\Service
     * @throws \LogicException
     */
    public function getAssetsBundleService() : \AssetsBundle\Service\Service
    {
        if ($this->assetsBundleService instanceof \AssetsBundle\Service\Service) {
            return $this->assetsBundleService;
        }
        throw new \LogicException('AssetsBundle service is undefined');
    }

    /**
     * @param \AssetsBundle\Service\Service $oAssetsBundleService
     * @return \AssetsBundle\Service\ToolsService
     */
    public function setAssetsBundleService(\AssetsBundle\Service\Service $oAssetsBundleService) : \AssetsBundle\Service\ToolsService
    {
        $this->assetsBundleService = $oAssetsBundleService;
        return $this;
    }
}
