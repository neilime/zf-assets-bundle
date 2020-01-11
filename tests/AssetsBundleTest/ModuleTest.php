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
     * @var \Laminas\Mvc\MvcEvent
     */
    protected $event;

    public function setUp()
    {
        $this->requestTimeFloat = $_SERVER['REQUEST_TIME_FLOAT'];
        $this->module = new \AssetsBundle\Module();
        $aConfiguration = \AssetsBundleTest\Bootstrap::getServiceManager()->get('Config');
        $this->event = new \Laminas\Mvc\MvcEvent();
        $this->event
        ->setViewModel(new \Laminas\View\Model\ViewModel())
        ->setApplication(\AssetsBundleTest\Bootstrap::getServiceManager()->get('Application'))
        ->setRouter(\Laminas\Router\Http\TreeRouteStack::factory(isset($aConfiguration['router'])?$aConfiguration['router']:array()))
        ->setRouteMatch(new \Laminas\Router\RouteMatch(array('controller' => 'test-module','action' => 'test-module\index-controller')));
    }

    public function testGetConsoleUsage()
    {
        $this->assertTrue(is_array($this->module->getConsoleUsage(new \Laminas\Console\Adapter\Virtual())));
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
