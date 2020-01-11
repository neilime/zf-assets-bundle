<?php

namespace AssetsBundle\Service;

class Service implements \Laminas\EventManager\ListenerAggregateInterface
{

    /**
     * @var \AssetsBundle\Service\ServiceOptions
     */
    protected $options;

    /**
     * @var array
     */
    protected $listeners = array();

    /**
     * @var \AssetsBundle\AssetFile\AssetFilesManager
     */
    protected $assetFilesManager;

    /**
     * @var \Laminas\View\HelperPluginManager
     */
    protected $viewHelperPluginManager;

    /**
     * Constructor
     *
     * @param  \AssetsBundle\Service\ServiceOptions $oOptions
     * @throws \InvalidArgumentException
     */
    public function __construct(\AssetsBundle\Service\ServiceOptions $oOptions = null)
    {
        if ($oOptions) {
            $this->setOptions($oOptions);
        }
    }

    /**
     * @param \Laminas\EventManager\EventManagerInterface $oEventManager
     * @return \AssetsBundle\Service\Service
     */
    public function attach(\Laminas\EventManager\EventManagerInterface $oEventManager, $iPriority = 1) : \AssetsBundle\Service\Service
    {
        // Assets rendering
        $this->listeners[] = $oEventManager->attach(\Laminas\Mvc\MvcEvent::EVENT_RENDER, array($this, 'renderAssets'), $iPriority);

        // MVC errors
        $this->listeners[] = $oEventManager->attach(\Laminas\Mvc\MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'consoleError'), $iPriority);
        $this->listeners[] = $oEventManager->attach(\Laminas\Mvc\MvcEvent::EVENT_RENDER_ERROR, array($this, 'consoleError'), $iPriority);

        return $this;
    }

    /**
     * @param \Laminas\EventManager\EventManagerInterface $oEventManager
     * @return \AssetsBundle\Service\Service
     */
    public function detach(\Laminas\EventManager\EventManagerInterface $oEventManager) : \AssetsBundle\Service\Service
    {
        foreach ($this->listeners as $iIndex => $oCallback) {
            if ($oEventManager->detach($oCallback)) {
                unset($this->listeners[$iIndex]);
            }
        }
        return $this;
    }

    /**
     * Render assets
     *
     * @param \Laminas\Mvc\MvcEvent $oEvent
     * @return \AssetsBundle\Service\Service
     */
    public function renderAssets(\Laminas\Mvc\MvcEvent $oEvent) : \AssetsBundle\Service\Service
    {

        // Retrieve service manager
        $oServiceManager = $oEvent->getApplication()->getServiceManager();

        // Check if asset should be rendered
        if (
            // Assert that request is an Http request
            !(($oRequest = $oEvent->getRequest()) instanceof \Laminas\Http\Request)
            // Not an Ajax request
            || $oRequest->isXmlHttpRequest()
            // Renderer is PHP
            || !($oServiceManager->get('ViewRenderer') instanceof \Laminas\View\Renderer\PhpRenderer)
        ) {
            return $this;
        }

        // Retrieve options
        $oOptions = $this->getOptions();

        // Define options from route match
        $oRouteMatch = $oEvent->getRouteMatch();
        if ($oRouteMatch instanceof \Laminas\Router\RouteMatch) {
            // Retrieve controller
            if ($sControllerName = $oRouteMatch->getParam('controller')) {
                $oControllerManager = $oServiceManager->get('ControllerManager');
                if ($oControllerManager->has($sControllerName) && ($oController = $oControllerManager->get($sControllerName))) {
                    $oOptions->setControllerName($sControllerName);
                    $sControllerClass = get_class($oController);
                    if ($sModuleName = substr($sControllerClass, 0, strpos($sControllerClass, '\\'))) {
                        $oOptions->setModuleName($sModuleName);
                    }
                }
            }

            if ($sActionName = $oRouteMatch->getParam('action')) {
                $oOptions->setActionName($sActionName);
            }
            // Assert that rendering should continue depends on route match
            if ($oOptions->isAssetsBundleDisabled()) {
                return $this;
            }
        }

        // Defined current view renderer
        $this->getOptions()->setRenderer($oServiceManager->get('ViewRenderer'));

        // Retrieve asset files manager
        $oAssetFilesManager = $this->getAssetFilesManager();

        // Retrieve cached Css & Js assets
        $aAssets = array_merge(
            $oAssetFilesManager->getCachedAssetsFiles(\AssetsBundle\AssetFile\AssetFile::ASSET_CSS),
            $oAssetFilesManager->getCachedAssetsFiles(\AssetsBundle\AssetFile\AssetFile::ASSET_JS)
        );
        
        // Render Css and Js assets
        $this->displayAssets($aAssets);

        // Save current configuration
        $this->getAssetFilesManager()->getAssetFilesConfiguration()->saveAssetFilesConfiguration();

        return $this;
    }

    /**
     * Display assets through renderer
     *
     * @param array $aAssetFiles
     * @return \AssetsBundle\Service\Service
     * @throws \InvalidArgumentException
     * @throws \DomainException
     */
    protected function displayAssets(array $aAssetFiles) : \AssetsBundle\Service\Service
    {
        // Retrieve options
        $oOptions = $this->getOptions();

        // Arbitrary last modified time in production
        $iLastModifiedTime = $oOptions->isProduction() ? $oOptions->getLastModifiedTime() : null;

        // Use to cache loaded plugins
        $aRendererPlugins = array();

        // Render asset files
        foreach ($aAssetFiles as $oAssetFile) {
            if (!($oAssetFile instanceof \AssetsBundle\AssetFile\AssetFile)) {
                throw new \InvalidArgumentException(sprintf(
                    'Asset file expects an instance of "AssetsBundle\AssetFile\AssetFile", "%s" given',
                    is_object($oAssetFile) ? get_class($oAssetFile) : gettype($oAssetFile)
                ));
            }

            switch ($sAssetFileType = $oAssetFile->getAssetFileType()) {
                case \AssetsBundle\AssetFile\AssetFile::ASSET_JS:
                    $oRendererPlugin = isset($aRendererPlugins[$sAssetFileType]) ? $aRendererPlugins[$sAssetFileType] : $aRendererPlugins[$sAssetFileType] = $oOptions->getViewHelperPluginForAssetFileType($sAssetFileType);
                    $oRendererPlugin->appendFile($oOptions->getAssetFileBaseUrl($oAssetFile, $iLastModifiedTime));
                    break;
                case \AssetsBundle\AssetFile\AssetFile::ASSET_CSS:
                    $oRendererPlugin = isset($aRendererPlugins[$sAssetFileType]) ? $aRendererPlugins[$sAssetFileType] : $aRendererPlugins[$sAssetFileType] = $oOptions->getViewHelperPluginForAssetFileType($sAssetFileType);
                    $oRendererPlugin->appendStylesheet($oOptions->getAssetFileBaseUrl($oAssetFile, $iLastModifiedTime), 'all');
                    break;
                default:
                    throw new \DomainException('Asset\'s type "' . $oAssetFile->getAssetFileType() . '" can not be rendering as asset');
            }
        }
        return $this;
    }

    /**
     * Display errors to the console, if an error appends during a ToolsController action
     *
     * @param \Laminas\Mvc\MvcEvent $oEvent
     */
    public function consoleError(\Laminas\Mvc\MvcEvent $oEvent)
    {
        if (
            ($oRequest = $oEvent->getRequest()) instanceof \Laminas\Console\Request 
            && $oRequest->getParam('controller') === 'AssetsBundle\Controller\Tools'
        ) {
            $oConsole = $oEvent->getApplication()->getServiceManager()->get('console');
            $oConsole->writeLine(PHP_EOL . '======================================================================', \Laminas\Console\ColorInterface::GRAY);
            $oConsole->writeLine('An error occured', \Laminas\Console\ColorInterface::RED);
            $oConsole->writeLine('======================================================================', \Laminas\Console\ColorInterface::GRAY);

            if (!($oException = $oEvent->getParam('exception')) instanceof \Exception) {
                $oException = new \RuntimeException($oEvent->getError());
            }
            $oConsole->writeLine($oException . PHP_EOL);
        }
    }

    /**
     * @param \AssetsBundle\Service\ServiceOptions $oOptions
     * @return \AssetsBundle\Service\Service
     */
    public function setOptions(\AssetsBundle\Service\ServiceOptions $oOptions) : \AssetsBundle\Service\Service
    {
        $this->options = $oOptions;
        if (isset($this->assetFilesManager)) {
            $this->getAssetFilesManager()->setOptions($this->options);
        }
        return $this;
    }

    /**
     * @return \AssetsBundle\Service\ServiceOptions
     * @throws \LogicException
     */
    public function getOptions() : \AssetsBundle\Service\ServiceOptions
    {
        if ($this->options instanceof \AssetsBundle\Service\ServiceOptions) {
            return $this->options;            
        }
        throw new \LogicException(
            'Property "options" expects an instance of "\AssetsBundle\Service\ServiceOptions", "'.(
                is_object($this->options)
                ? get_class($this->options)
                : gettype($this->options)
            ).'" defined'
        );
    }

    /**
     * @param \AssetsBundle\AssetFile\AssetFilesManager $oAssetFilesManager
     * @return \AssetsBundle\Service\Service
     */
    public function setAssetFilesManager(\AssetsBundle\AssetFile\AssetFilesManager $oAssetFilesManager) : \AssetsBundle\Service\Service
    {
        $this->assetFilesManager = $oAssetFilesManager->setOptions($this->getOptions());
        return $this;
    }

    /**
     * @return \AssetsBundle\AssetFile\AssetFilesManager
     */
    public function getAssetFilesManager() : \AssetsBundle\AssetFile\AssetFilesManager
    {
        if (!($this->assetFilesManager instanceof \AssetsBundle\AssetFile\AssetFilesManager)) {
            $this->setAssetFilesManager(new \AssetsBundle\AssetFile\AssetFilesManager());
        }
        return $this->assetFilesManager;
    }
}
