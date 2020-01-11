<?php

namespace AssetsBundleTest\View\Strategy;

class JsCustomStrategyTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \AssetsBundle\View\Strategy\JsCustomStrategy
     */
    protected $jsCustomStrategy;

    /**
     * @see PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        // Empty cache and processed directories
        \AssetsBundleTest\Bootstrap::getServiceManager()->get('AssetsBundleToolsService')->emptyCache(false);
        $this->jsCustomStrategy = new \AssetsBundle\View\Strategy\JsCustomStrategy();
    }

    /**
     * @expectedException LogicException
     */
    public function testGetRendererUnset()
    {
        $this->jsCustomStrategy->getRenderer();
    }

    public function testAttachDetach()
    {
        $oEventManager = \AssetsBundleTest\Bootstrap::getServiceManager()->get('EventManager');
        $oReflectedClass = new \ReflectionClass($oEventManager);
        $oReflectedProperty = $oReflectedClass->getProperty('events');
        $oReflectedProperty->setAccessible(true);

        $this->jsCustomStrategy->attach($oEventManager);
        $this->assertEquals(array('renderer', 'response'), array_keys($oReflectedProperty->getValue($oEventManager)));

        $this->jsCustomStrategy->detach($oEventManager);
        $this->assertEquals(array(), array_keys($oReflectedProperty->getValue($oEventManager)));
    }

    /**
     * @expectedException LogicException
     */
    public function testGetRouterUnset()
    {
        $this->jsCustomStrategy->getRouter();
    }

    public function testSelectRendererReturnNullByDefault()
    {
        $this->assertNull(
            $this->jsCustomStrategy->setRouter(\AssetsBundleTest\Bootstrap::getServiceManager()->get('router'))->selectRenderer(new \Laminas\View\ViewEvent())
        );
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testSelectRendererWithWrongModel()
    {

        // Reset server datas
        $_SESSION = array();
        $_GET = array();
        $_POST = array();
        $_COOKIE = array();

        // Do not cache module config on testing environment
        $aApplicationConfig = \AssetsBundleTest\Bootstrap::getConfig();
        if (isset($aApplicationConfig['module_listener_options']['config_cache_enabled'])) {
            $aApplicationConfig['module_listener_options']['config_cache_enabled'] = false;
        }
        \Laminas\Console\Console::overrideIsConsole(false);
        $oApplication = \Laminas\Mvc\Application::init($aApplicationConfig);
        $oApplication->getServiceManager()->get('SendResponseListener')->detach($oApplication->getEventManager());

        $oRequest = $oApplication->getRequest();
        $oUri = new \Laminas\Uri\Http('/jscustom/AssetsBundleTest\\Controller\\Test/test');

        $oRequest->setMethod(\Laminas\Http\Request::METHOD_GET)
                ->setUri($oUri)
                ->setRequestUri($oUri->getPath());

        $oApplication->run();

        $oViewEvent = new \Laminas\View\ViewEvent();
        $this->jsCustomStrategy
                ->setRouter($oApplication->getServiceManager()->get('router'))
                ->selectRenderer($oViewEvent->setRequest($oRequest));
    }

    public function tearDown()
    {
        //Empty cache and processed directories
        \AssetsBundleTest\Bootstrap::getServiceManager()->get('AssetsBundleToolsService')->emptyCache(false);
        parent::tearDown();
    }
}
