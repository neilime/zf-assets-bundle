<?php

namespace AssetsBundleTest\Service;

class ServiceTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \AssetsBundle\Service\Service
     */
    protected $service;

    /**
     * @see PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        // Empty cache and processed directories
        \AssetsBundleTest\Bootstrap::getServiceManager()->get('AssetsBundleToolsService')->emptyCache(false);
        $this->service = new \AssetsBundle\Service\Service();
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetOptionsUndefined()
    {
       $this->service->getOptions();
    }

    public function testSetOptions(){
        $oOptions = new \AssetsBundle\Service\ServiceOptions();
        $this->assertSame($this->service, $this->service->setOptions($oOptions));
        $this->assertSame($oOptions, $this->service->getOptions());
    }

    public function testSetOptionsWithExistingAssetFilesManager()
    {

        $oOptions = new \AssetsBundle\Service\ServiceOptions();
        $this->service->setOptions($oOptions);
        
        $this->assertInstanceOf('\AssetsBundle\AssetFile\AssetFilesManager', $oAssetFileManager = $this->service->getAssetFilesManager());

        $this->assertSame($oOptions, $oAssetFileManager->getOptions());
    }

    public function testSetRoute()
    {
        // Set default options         
        $this->service->setOptions(new \AssetsBundle\Service\ServiceOptions());

        // Set fake route match
        $oRouteMatch = new \Zend\Router\RouteMatch(array('controller' => 'test-module\index-controller', 'action' => 'index'));

        // Module
        $this->assertInstanceOf('AssetsBundle\Service\ServiceOptions', $this->service->getOptions()->setModuleName(current(explode('\\', $oRouteMatch->getParam('controller')))));
        $this->assertEquals('test-module', $this->service->getOptions()->getModuleName());

        // Controller
        $this->assertInstanceOf('AssetsBundle\Service\ServiceOptions', $this->service->getOptions()->setControllerName($oRouteMatch->getParam('controller')));
        $this->assertEquals('test-module\index-controller', $this->service->getOptions()->getControllerName());

        // Action
        $this->assertInstanceOf('AssetsBundle\Service\ServiceOptions', $this->service->getOptions()->setActionName($oRouteMatch->getParam('action')));
        $this->assertEquals('index', $this->service->getOptions()->getActionName());
    }

    public function tearDown()
    {
        // Empty cache and processed directories
        \AssetsBundleTest\Bootstrap::getServiceManager()->get('AssetsBundleToolsService')->emptyCache(false);
        parent::tearDown();
    }
}
