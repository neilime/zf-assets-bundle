<?php

namespace AssetsBundle\Service;

class ToolsService
{

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
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
        $oConsole->writeln('');
        $oConsole->writeln('======================================================================');
        $oConsole->writeln('Render all assets for ' . ($oAssetsBundleService->getOptions()->isProduction() ? 'production' : 'development'));
        $oConsole->writeln('======================================================================');
        $oConsole->writeln('');

        // Empty cache directory
        $this->emptyCache();

        $oConsole->writeln('');
        $oConsole->writeln('Start rendering assets : ');
        $oConsole->writeln('-------------------------');
        $oConsole->writeln('');
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
                $oConsole->write(' * ');
                $oConsole->write('[' . $sModuleName . ']');
                $oConsole->write('[No controller]');
                $oConsole->write('[No action]' . PHP_EOL);

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
                    $oConsole->write(' * ');
                    $oConsole->write('[' . $sModuleName . ']');
                    $oConsole->write('[' . $sControllerName . ']');
                    $oConsole->write('[No action]' . PHP_EOL);

                    // Render assets for no_action
                    $oOptions->setActionName(\AssetsBundle\Service\ServiceOptions::NO_ACTION);
                    $oAssetsBundleService->renderAssets($oMvcEvent);
                }

                foreach (array_diff_key($aAssetsConfiguration[$sModuleName][$sControllerName], $aUnwantedKeys) as $sActionName => $aActionConfig) {
                    // Render assets for action
                    if (array_intersect_key($aActionConfig, $aUnwantedKeys)) {
                        $oConsole->write(' * ');
                        $oConsole->write('[' . $sModuleName . ']');
                        $oConsole->write('[' . $sControllerName . ']');
                        $oConsole->write('[' . $sActionName . ']' . PHP_EOL);

                        $oAssetsBundleService->getOptions()->setActionName($sActionName);
                        $oAssetsBundleService->renderAssets($oMvcEvent);
                    }
                }
            }
        }
        //Render global assets
        $oConsole->write(' * ');
        $oConsole->write('[No module]');
        $oConsole->write('[No controller]');
        $oConsole->write('[No action]' . PHP_EOL);
        $oAssetsBundleService->getOptions()
                ->setModuleName(\AssetsBundle\Service\ServiceOptions::NO_MODULE)
                ->setControllerName(\AssetsBundle\Service\ServiceOptions::NO_CONTROLLER)
                ->setActionName(\AssetsBundle\Service\ServiceOptions::NO_ACTION);
        $oAssetsBundleService->renderAssets($oMvcEvent);

        $oConsole->writeln('');
        $oConsole->writeln('---------------');
        $oConsole->writeln('Assets rendered');
        $oConsole->writeln('');

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
            $oConsole->writeln('');
            $oConsole->writeln('========================');
            $oConsole->writeln('Empty cache');
            $oConsole->writeln('========================');
            $oConsole->writeln('');
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
                $oConsole->writeln(' * "'.$sName.'" directory is empty');
            }
        }
        

        return $this;
    }

    /**
     * @return \Symfony\Component\Console\Output\OutputInterface
     * @throws \LogicException
     */
    public function getConsole() : \Symfony\Component\Console\Output\OutputInterface
    {
        if ($this->console instanceof \Symfony\Component\Console\Output\OutputInterface) {
            return $this->console;
        }
        throw new \LogicException('Console is undefined');
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $oConsole
     * @return \AssetsBundle\Service\ToolsService
     */
    public function setConsole(\Symfony\Component\Console\Output\OutputInterface $oConsole) : \AssetsBundle\Service\ToolsService
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
