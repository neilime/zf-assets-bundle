<?php
namespace AssetsBundleTest;

class ModuleTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \AssetsBundle\Module
     */
    protected $module;
    
    protected $requestTimeFloat;

    /**
     * @var \Zend\Mvc\MvcEvent
     */
    protected $event;

    public function setUp()
    {
        $this->requestTimeFloat = $_SERVER['REQUEST_TIME_FLOAT'];
        $this->module = new \AssetsBundle\Module();
        $aConfiguration = \AssetsBundleTest\Bootstrap::getServiceManager()->get('Config');
        $this->event = new \Zend\Mvc\MvcEvent();
        $this->event
        ->setViewModel(new \Zend\View\Model\ViewModel())
        ->setApplication(\AssetsBundleTest\Bootstrap::getServiceManager()->get('Application'))
        ->setRouter(\Zend\Router\Http\TreeRouteStack::factory(isset($aConfiguration['router'])?$aConfiguration['router']:array()))
        ->setRouteMatch(new \Zend\Router\RouteMatch(array('controller' => 'test-module','action' => 'test-module\index-controller')));
    }

    public function testGetConsoleUsage()
    {
        $this->assertTrue(is_array($this->module->getConsoleUsage(new \Zend\Console\Adapter\Virtual())));
    }

    public function testGetConfig()
    {
        $this->assertTrue(is_array($this->module->getConfig()));
	}
	    
    public function tearDown()
    {
        $_SERVER['REQUEST_TIME_FLOAT'] = $this->requestTimeFloat;
    }
}
