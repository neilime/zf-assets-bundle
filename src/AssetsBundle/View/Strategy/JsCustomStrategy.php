<?php

namespace AssetsBundle\View\Strategy;

class JsCustomStrategy implements \Laminas\EventManager\ListenerAggregateInterface
{

    /**
     * @var \Laminas\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var \Laminas\Router\RouteInterface
     */
    protected $router;

    /**
     * @var \Laminas\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * @var \AssetsBundle\View\Renderer\JsCustomRenderer
     */
    protected $renderer;

    /**
     * @param \AssetsBundle\View\Renderer\JsCustomRenderer $oRenderer
     * @return \AssetsBundle\View\Strategy\JsCustomStrategy
     */
    public function setRenderer(\AssetsBundle\View\Renderer\JsCustomRenderer $oRenderer) : \AssetsBundle\View\Strategy\JsCustomStrategy
    {
        $this->renderer = $oRenderer;
        return $this;
    }

    /**
     * @throws \LogicException
     * @return \AssetsBundle\View\Renderer\JsCustomRenderer
     */
    public function getRenderer() : \AssetsBundle\View\Renderer\JsCustomRenderer
    {
        if ($this->renderer instanceof \AssetsBundle\View\Renderer\JsCustomRenderer) {
            return $this->renderer;
        }
        throw new \LogicException('Renderer is undefined');
    }

    public function setRouter(\Laminas\Router\RouteInterface $oRouter) : \AssetsBundle\View\Strategy\JsCustomStrategy
    {
        $this->router = $oRouter;
        return $this;
    }

    public function getRouter() : \Laminas\Router\RouteInterface
    {
        if ($this->router instanceof \Laminas\Router\RouteInterface) {
            return $this->router;
        }
        throw new \LogicException('Router is undefined');
    }
    
    /**
     * Attach the aggregate to the specified event manager
     * @param \Laminas\EventManager\EventManagerInterface $oEvents
     * @param int $iPriority
     * @return void
     */
    public function attach(\Laminas\EventManager\EventManagerInterface $oEvents, $iPriority = 1)
    {
        $this->listeners[] = $oEvents->attach(\Laminas\View\ViewEvent::EVENT_RENDERER, array($this, 'selectRenderer'), $iPriority);
        $this->listeners[] = $oEvents->attach(\Laminas\View\ViewEvent::EVENT_RESPONSE, array($this, 'injectResponse'), $iPriority);
    }

    /**
     * Detach aggregate listeners from the specified event manager
     * @param \Laminas\EventManager\EventManagerInterface $oEvents
     * @return void
     */
    public function detach(\Laminas\EventManager\EventManagerInterface $oEvents)
    {
        foreach ($this->listeners as $iIndex => $oListener) {
            if ($oEvents->detach($oListener)) {
                unset($this->listeners[$iIndex]);
            }
        }
    }

    /**
     * Check if JsCustomStrategy has to be used (MVC action = \AssetsBundle\Mvc\Controller\AbstractActionController::JS_CUSTOM_ACTION)
     * @param \Laminas\View\ViewEvent $oEvent
     * @throws \LogicException
     * @return void|\AssetsBundle\View\Renderer\JsCustomRenderer
     */
    public function selectRenderer(\Laminas\View\ViewEvent $oEvent)
    {
        $oRouter = $this->getRouter();
        if (
            // Retrieve request
            ($oRequest = $oEvent->getRequest()) instanceof \Laminas\Http\Request
            // Retrieve route match
            && ($oRouteMatch = $oRouter->match($oRequest)) instanceof \Laminas\Router\RouteMatch && $oRouteMatch->getParam('action') === \AssetsBundle\Mvc\Controller\AbstractActionController::JS_CUSTOM_ACTION
        ) {
            if (!($oViewModel = $oEvent->getModel()) instanceof \Laminas\View\Model\ViewModel) {
                throw new \UnexpectedValueException(sprintf(
                        'Event model expects an instance of "Laminas\View\Model\ViewModel", "%s" given',
                    is_object($oViewModel) ? get_class($oViewModel) : gettype($oViewModel)
                ));
            } elseif (($oException = $oViewModel->getVariable('exception')) instanceof \Exception) {
                return;
            }

            // jsCustomFiles is empty
            if (!is_array($aJsCustomFiles = $oEvent->getModel()->getVariable('jsCustomFiles'))) {
                $oEvent->getModel()->setVariable('jsCustomFiles', array());
            }

            return $this->getRenderer();
        }
    }

    /**
     * @param \Laminas\View\ViewEvent $oEvent
     * @throws \UnexpectedValueException
     */
    public function injectResponse(\Laminas\View\ViewEvent $oEvent)
    {
        if ($oEvent->getRenderer() !== $this->getRenderer()) {
            return;
        }
        if (!is_string($sResult = $oEvent->getResult())) {
            throw new \UnexpectedValueException('Result expects string, "' . gettype($sResult) . '" given');
        }
        // jsCustomFiles is empty
        if (!is_array($aJsCustomFiles = $oEvent->getModel()->getVariable('jsCustomFiles'))) {
            throw new \UnexpectedValueException('"jsCustomFiles" view\'s variable expects an array, "' . gettype($aJsCustomFiles) . '" given');
        }

        $sResponseContent = '';
        foreach ($aJsCustomFiles as $oAssetFile) {
            if ($oAssetFile instanceof \AssetsBundle\AssetFile\AssetFile) {
                $sResponseContent .= $oAssetFile->getAssetFileContents() . PHP_EOL;
            } else {
                throw new \UnexpectedValueException('"jsCustomFiles" view\'s variable must contains instance of \AssetsBundle\AssetFile\AssetFile, "' . (is_object($oAssetFile) ? get_class($oAssetFile) : gettype($oAssetFile)) . '" given');
            }
        }

        // Inject javascript in the response
        $oEvent->getResponse()->setContent($sResponseContent)->getHeaders()->addHeaderLine('content-type', 'text/javascript');
    }
}
